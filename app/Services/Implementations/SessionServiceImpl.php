<?php

namespace App\Services\Implementations;

use Illuminate\Support\Facades\Log;
use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Rap;
use App\Models\Session;
use App\Models\Subject;
use App\Models\User;
use App\Services\GoogleCalendarService;
use App\Services\SessionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionServiceImpl implements SessionService
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    public function createSession(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'rap_id' => 'required|exists:raps,id',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:instructors,id',
            'instructor2_id' => 'nullable|exists:users,id',
            'days_of_week' => 'required|string',
            'percentage' => 'required|integer|min:40|max:100',
        ]);

        DB::beginTransaction();

        try {
            $user = User::find(Auth::id());
            $course = Course::find($request->course_id);
            $isValid = $this->validateForCreateSessions($request, $user, $course);
            if ($isValid != null) {
                return $isValid;
            }

            // Registro de cambio del porcentaje por usuario
            $rapForUser = Rap::find($request->rap_id);
            $subject = Subject::find($rapForUser->subject_id);

            $subject->update([
                'user_id' => $user->id,
                'percentage' => $request->percentage,
                'updated_porcentage' => Carbon::now()
            ]);

            $festivos = array_map(function ($holiday) {
                return $holiday['start']['date']; // Extrae solo la fecha de inicio
            }, $this->googleCalendarService->getHolidays(date('Y')));

            // Obtener la duración total de la competencia en horas
            $rap = Rap::findOrFail($request->rap_id);
            $totalHours = $rap->number_hours;
            $percentage = $rap->subject->percentage;
            $hours = $totalHours * $percentage / 100;

            $startDate = Carbon::parse($request->start_date);
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);
            $sessionDuration = $startTime->diffInHours($endTime);

            if ($sessionDuration <= 0) {
                return response()->json(['message' => 'El tiempo de sesión debe ser mayor a 0 horas.'], 422);
            }

            $dayOfWeek = explode(',', $request->days_of_week);
            foreach ($dayOfWeek as $day) {
                if (!in_array($day, ['1', '2', '3', '4', '5', '6', '7'])) {
                    return response()->json(['message' => 'El campo dias de la semana contiene valores inválidos.'], 422);
                }
            }

            $sessionsNeeded = ceil($hours / $sessionDuration);
            $sessionsCreated = [];
            $currentDate = $startDate;
            $lastValidSession = null;

            for ($i = 0; $i < $sessionsNeeded; $i++) {

                while (!in_array($currentDate->dayOfWeek, $dayOfWeek) || in_array($currentDate->format('Y-m-d'), $festivos)) {
                    $currentDate->addDay();
                }

                // Verificar si el instructor ya tiene una sesión en la misma fecha y horario
                $existingSession = Session::where('date', $currentDate->format('Y-m-d'))
                    ->where('instructor_id', $request->instructor_id)
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime->format('H:i'))
                            ->where('end_time', '>', $startTime->format('H:i'));
                    })->get();

                if ($existingSession->isNotEmpty()) {
                    return response()->json(['message' => 'El instructor ya tiene asignadas sesiones para estas fechas', $existingSession], 409); //conflict
                } else {

                    // Verificar si otro instructor tiene una sesión en el mismo día y curso
                    $existingSessionForCourse = Session::where('date', $currentDate->format('Y-m-d'))
                        ->where('course_id', $request->course_id)
                        ->where(function ($query) use ($startTime, $endTime) {
                            $query->where('start_time', '<', $endTime->format('H:i'))
                                ->where('end_time', '>', $startTime->format('H:i'));
                        })->get();

                    if ($existingSessionForCourse->isNotEmpty()) {
                        return response()->json(['message' => 'Otro instructor ya tiene una sesión en el mismo día y curso.', $existingSessionForCourse], 409); //conflict
                    }

                    $session = Session::create([
                        'date' => $currentDate->format('Y-m-d'),
                        'start_time' => $startTime->format('H:i'),
                        'end_time' => $endTime->format('H:i'),
                        'instructor_id' => $request->instructor_id,
                        'course_id' => $request->course_id,
                        'rap_id' => $request->rap_id,
                    ]);

                    $sessionsCreated[] = $session;

                    // Crear asistencias para los aprendices
                    $aprendices = Apprentice::where('course_id', $request->course_id)->get();
                    foreach ($aprendices as $aprendiz) {
                        if ($aprendiz->state == 'Formacion') {
                            Assistance::create([
                                'apprentice_id' => $aprendiz->id,
                                'session_id' => $session->id,
                                'assistance' => null,
                            ]);
                        }
                    }

                    if ($session->date <= $course->end_date_training_stage) {
                        $lastValidSession = $session;
                    }


                    $session = Session::create([
                        'date' => $currentDate->format('Y-m-d'),
                        'start_time' => $startTime->format('H:i'),
                        'end_time' => $endTime->format('H:i'),
                        'instructor_id' => $request->instructor_id,
                        'course_id' => $request->course_id,
                        'rap_id' => $request->rap_id,
                    ]);

                    if ($session->date > $course->end_date_training_stage) {
                        Session::where('id', $session->id)->delete();
                        return response()->json(['message' => 'No se puede crear sesiones fuera de la etapa lectiva'], 200); //Unprocessable entity
                    }

                    $aprendices = Apprentice::where('course_id', $request->course_id)->get();
                    foreach ($aprendices as $aprendiz) {
                        if ($aprendiz->state == 'Formacion') {
                            Assistance::create([
                                'apprentice_id' => $aprendiz->id,
                                'session_id' => $session->id,
                                'assistance' => null,
                            ]);
                        }
                    }
                    $session = Session::create([
                        'date' => $currentDate->format('Y-m-d'),
                        'start_time' => $startTime->format('H:i'),
                        'end_time' => $endTime->format('H:i'),
                        'instructor_id' => $request->instructor_id,
                        'course_id' => $request->course_id,
                        'rap_id' => $request->rap_id,
                    ]);

                    $sessionsCreated[] = $session;

                    // Crear asistencias para los aprendices
                    $aprendices = Apprentice::where('course_id', $request->course_id)->get();
                    foreach ($aprendices as $aprendiz) {
                        if ($aprendiz->state == 'Formacion') {
                            Assistance::create([
                                'apprentice_id' => $aprendiz->id,
                                'session_id' => $session->id,
                                'assistance' => null,
                            ]);
                        }
                    }

                    $sessionsCreated[] = $session;
                }
                    if ($session->date <= $course->end_date_training_stage) {
                        $lastValidSession = $session;
                    }
                }

                $currentDate->addDay();
                while (!in_array($currentDate->dayOfWeek, $dayOfWeek) || in_array($currentDate->format('Y-m-d'), $festivos)) {
                    $currentDate->addDay();
                }
            }

            // Eliminar las sesiones fuera de la etapa lectiva solo al final
            foreach ($sessionsCreated as $session) {
                if ($session->date > $course->end_date_training_stage) {
                    // Eliminar la sesión si está fuera de la etapa lectiva
                    Session::where('id', $session->id)->delete();
                }
            }

            if ($lastValidSession) {
                Session::where('id', $lastValidSession->id)->update([
                    'end_date' => $lastValidSession->date
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Sesiones y asistencias creadas exitosamente.',
                'sessions_created' => $sessionsCreated,

            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear las sesiones: ' . $e->getMessage()], 500);
        }
    }


    public function validateForCreateSessions($request, $user, $course)
    {
        // Verificar competencia por programa
        $rapCompetencia = Rap::findOrFail($request->rap_id);

        if ($rapCompetencia->subject->program->id !== $course->program_id) {
            return response()->json(['message' => 'La competencia no pertenece al curso seleccionado.'], 422);
        }

        // Verificar si el curso está en ejecución
        if ($course->state !== 'En_ejecucion') {
            return response()->json(['message' => 'El curso no está en ejecución. No se pueden crear sesiones.'], 422);
        }


        // Verificar si el instructor está activo
        $instructor = Instructor::findOrFail($request->instructor_id);
        if ($instructor->state !== 'Activo') {
            return response()->json(['message' => 'El instructor no está activo. No se pueden crear sesiones.'], 422);
        }

        // Verificar si el usuario es el líder del curso
        $leader_course = Instructor::where('user_id', $user->id)->first();
        if (!$leader_course) {
            return response()->json([
                'message' => 'No estás registrado como instructor.'
            ], 403);
        }
        if ($course->course_leader_id != $leader_course->id) {
            return response()->json([
                'message' => 'No eres el líder de esta ficha.',
            ], 403);
        }

        // Verificar si ya existen sesiones creadas con este RAP en el curso seleccionado
        $existingSessionWithRap = Session::where('rap_id', $request->rap_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($existingSessionWithRap) {
            return response()->json(['message' => 'Ya existen sesiones creadas con este RAP en el curso seleccionado.'], 422);
        }
    }


    public function updateSessions(Request $request, $sessionIds)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'instructor_id' => 'required|exists:instructors,id',
            'instructor2_id' => 'nullable|exists:users,id',
            'confirmed' => 'nullable|boolean',
        ]);

        $festivos = array_map(function ($holiday) {
            return $holiday['start']['date'];
        }, $this->googleCalendarService->getHolidays(date('Y')));

        $sessionsUpdated = [];
        $courseId = null;

        foreach ($sessionIds as $sessionId) {
            $session = Session::findOrFail($sessionId);

            if (in_array($validated['start_date'], $festivos)) {
                return response()->json([
                    'message' => 'La fecha seleccionada es un día festivo y no se pueden asignar sesiones.'
                ], 422);
            }

            $course = Course::find($session->course_id ?? $request->course_id);
            if ($course && $course->end_date_training_stage) {
                $sessionDate = $request->start_date ?? $session->date;

                if (Carbon::parse($sessionDate)->gt(Carbon::parse($course->end_date_training_stage))) {
                    return response()->json([
                        'message' => 'No puedes actualizar una sesión fuera de la fecha fin de la etapa lectiva del curso.'
                    ], 422);
                }
            }

            // Normalizar tiempos
            $startTime = $validated['start_time'] ? $validated['start_time'] . ':00' : $session->start_time;
            $endTime = $validated['end_time'] ? $validated['end_time'] . ':00' : $session->end_time;

            // Verificar conflictos de horario
            $conflict = Session::where('instructor_id', $validated['instructor_id'] ?? $session->instructor_id)
                ->where('id', '!=', $session->id)
                ->where('date', $validated['start_date'] ?? $session->date)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
                })
                ->first();

            if ($conflict && empty($validated['confirmed'])) {
                return response()->json([
                    'message' => 'Existe una sesión previamente agendada en ese horario.',
                    'conflict_session' => $conflict
                ], 409);
            }

            // Actualizar campos
            if ($request->has('start_date')) {
                $session->date = $request->start_date;
            }
            if ($request->has('start_time')) {
                $session->start_time = $request->start_time;
            }
            if ($request->has('end_time')) {
                $session->end_time = $request->end_time;
            }
            if ($request->has('rap_id')) {
                $session->rap_id = $request->rap_id;
            }
            if ($request->has('course_id')) {
                $session->course_id = $request->course_id;
            }
            if ($request->has('instructor_id')) {
                $session->instructor_id = $request->instructor_id;
            }
            if ($request->has('instructor2_id')) {
                $session->instructor2_id = $request->instructor2_id;
            }

            if ($request->has('end_time') || $session->end_time != $endTime) {
                $session->end_date = Carbon::parse($session->date . ' ' . $endTime)->format('Y-m-d H:i:s');
            }

            $session->save();
            $sessionsUpdated[] = $session;

            $courseId = $session->course_id ?? $request->course_id;

            if ($request->has('course_id')) {
                Assistance::where('session_id', $sessionId)->delete();

                $aprendices = Apprentice::where('course_id', $request->course_id)->get();
                foreach ($aprendices as $aprendiz) {
                    Assistance::create([
                        'apprentice_id' => $aprendiz->id,
                        'session_id' => $session->id,
                        'assistance' => null,
                    ]);
                }
            }
        }

        // Actualizar última sesión del curso
        if ($courseId) {
            $lastSession = Session::where('course_id', $courseId)
                ->orderBy('date', 'desc')
                ->first();

            if ($lastSession) {
                $lastSession->end_date = $lastSession->date;
                $lastSession->save();

                Session::where('course_id', $courseId)
                    ->where('id', '!=', $lastSession->id)
                    ->whereNotNull('end_date')
                    ->update(['end_date' => null]);
            }
        }

        return response()->json([
            'message' => 'Sesiones actualizadas exitosamente.',
            'sessions' => $sessionsUpdated,
        ]);
    }


    public function destroy($id)
    {
        try {
            $session = Session::findOrFail($id);

            if ($session->date < Carbon::now()->toDateString()) {
                return response()->json(['message' => 'No se puede eliminar una sesión que ya ha pasado'], 400);
            }

            $courseId = $session->course_id;

            $session->assistances()->delete();
            $session->delete();

            // Buscar la última sesión que queda del curso
            $lastSession = Session::where('course_id', $courseId)
                ->orderBy('date', 'desc')
                ->first();

            // Guardar su propia fecha como end_date
            if ($lastSession) {
                $lastSession->end_date = $lastSession->date;
                $lastSession->save();
            }

            return response()->json([
                'message' => 'Sesión eliminada exitosamente',

            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Sesión no encontrada'], 404);
        }
    }



    public function deleteSessionsByDateRange($request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rap_id' => 'required|integer|exists:raps,id',
            'course_id' => 'required|integer|exists:courses,id',
        ]);

        $deleted = Session::whereBetween('date', [$request->start_date, $request->end_date])
            ->where('rap_id', $request->rap_id)
            ->where('course_id', $request->course_id)
            ->delete();

        if ($deleted == 0) {
            return response()->json(['message' => 'No se encontraron sesiones para eliminar']);
        }

        $lastSession = Session::where('rap_id', $request->rap_id)
            ->where('course_id', $request->course_id)
            ->orderByDesc('date')
            ->first();

        if ($lastSession) {
            $lastSession->end_date = $lastSession->date;
            $lastSession->save();
        }

        return response()->json([
            'message' => 'Sesiones eliminadas correctamente',
            'deleted_count' => $deleted,
            'last_session_updated' => $lastSession ? $lastSession->id : null
        ]);
    }




    public function updateSessionsByRange(Request $request)
    {
        $validated = $request->validate([
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'rap_id'                => 'required|integer|exists:raps,id',
            'course_id'             => 'required|integer|exists:courses,id',
            'start_time'            => 'nullable|date_format:H:i',
            'end_time'              => 'nullable|date_format:H:i|after:start_time',
            'instructor_id'         => 'nullable|exists:instructors,id',
            'new_days_of_week'      => 'required|array|min:1',
            'new_days_of_week.*'    => 'integer|between:1,7',
            'confirmed'             => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Obtener festivos del año
            $festivos = array_map(function ($holiday) {
                return $holiday['start']['date'];
            }, $this->googleCalendarService->getHolidays(date('Y')));

            //  Traer sesiones en el rango
            $sessions = Session::whereBetween('date', [$validated['start_date'], $validated['end_date']])
                ->where('rap_id', $validated['rap_id'])
                ->where('course_id', $validated['course_id'])
                ->orderBy('date')
                ->get();

            $newDays = $validated['new_days_of_week'];

            $validDates = [];
            $currentDate = Carbon::parse($validated['start_date']);
            $endDate     = Carbon::parse($validated['end_date']);

            while ($currentDate <= $endDate) {
                if (
                    in_array($currentDate->dayOfWeekIso, $newDays) &&
                    !in_array($currentDate->format('Y-m-d'), $festivos)
                ) {
                    $validDates[] = $currentDate->copy();
                }
                $currentDate->addDay();
            }

            if (count($validDates) < $sessions->count()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No hay suficientes fechas disponibles para reasignar todas las sesiones.'
                ], 400);
            }

            $updated = [];

            foreach ($sessions as $index => $session) {
                $newDate = $validDates[$index];

                $startTime    = $validated['start_time']  ?? $session->start_time;
                $endTime      = $validated['end_time']    ?? $session->end_time;
                $instructorId = $validated['instructor_id'] ?? $session->instructor_id;

                // Verificar conflictos con otras sesiones
                $conflict = Session::where('instructor_id', $instructorId)
                    ->where('id', '!=', $session->id)
                    ->where('date', $newDate->format('Y-m-d'))
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                            ->where('end_time',   '>', $startTime);
                    })
                    ->first();

                if ($conflict && empty($validated['confirmed'])) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Existe una sesión previamente agendada en ese horario.',
                        'conflict_session' => $conflict
                    ], 409);
                }

                $session->date       = $newDate->format('Y-m-d');
                $session->start_time = $startTime;
                $session->end_time   = $endTime;
                if (isset($validated['instructor_id'])) {
                    $session->instructor_id = $validated['instructor_id'];
                }
                $session->save();

                $updated[] = $session;
            }

            if (!empty($updated)) {
                $lastSession = Session::where('rap_id', $validated['rap_id'])
                    ->where('course_id', $validated['course_id'])
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastSession) {
                    Session::where('id', $lastSession->id)->update([
                        'end_date' => $lastSession->date
                    ]);
                }

                $startTime = $validated['start_time'] ?? $session->start_time;
                $endTime = $validated['end_time'] ?? $session->end_time;
                $instructorId = $validated['instructor_id'] ?? $session->instructor_id;

                // ⚠️ Validar conflicto de horarios con otras sesiones del instructor
                $conflict = Session::where('instructor_id', $instructorId)
                    ->where('id', '!=', $session->id)
                    ->where('date', $newDate->format('Y-m-d'))
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<', $endTime)
                                ->where('end_time', '>', $startTime);
                        });
                    })
                    ->first();
                // Verificar conflictos con otras sesiones
                $conflict = Session::where('instructor_id', $instructorId)
                    ->where('id', '!=', $session->id)
                    ->where('date', $newDate->format('Y-m-d'))
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                            ->where('end_time',   '>', $startTime);
                    })
                    ->first();

                if ($conflict && empty($validated['confirmed'])) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Existe una sesión previamente agendada en ese horario.',
                        'conflict_session' => $conflict
                    ], 409);
                }

                // ✅ Actualización segura
                $session->date = $newDate->format('Y-m-d');
                $session->start_time = $startTime;
                $session->end_time = $endTime;
                if (isset($validated['instructor_id'])) $session->instructor_id = $validated['instructor_id'];
                $session->save();

            $updated[] = $session;
        }

            if (!empty($updated)) {
                $lastSession = Session::where('rap_id', $validated['rap_id'])
                    ->where('course_id', $validated['course_id'])
                    ->orderBy('date', 'desc')
                    ->first();

                if ($lastSession) {
                    Session::where('id', $lastSession->id)->update([
                        'end_date' => $lastSession->date
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message'  => 'Sesiones actualizadas exitosamente.',
                'sessions' => $updated,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar las sesiones: ' . $e->getMessage()
            ], 500);
        }
    }
}
