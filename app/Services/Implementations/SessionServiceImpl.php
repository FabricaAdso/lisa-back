<?php

namespace App\Services\Implementations;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Rap;
use App\Models\Session;
use App\Models\Subject;
use App\Models\User;
use App\Services\SessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionServiceImpl implements SessionService

{
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

        $user = User::find(Auth::id());
        $course = Course::find($request->course_id);
        $isValid = $this->validateForCreateSessions($request, $user, $course);
        if ($isValid != null) {
            return $isValid;
        }
        // registro de cambio del porcentaje por usuario
        $rapForUser = Rap::find($request->rap_id);
        $subject = Subject::find($rapForUser->subject_id);

        $subject->update([
            'user_id' => $user->id,
            'percentage' => $request->percentage,
            'updated_porcentage' => Carbon::now()
        ]);


        $festivos = [
            '2025-01-01',
            '2025-01-06',
            '2025-03-24',
            '2025-04-17',
            '2025-04-18',
            '2025-05-01',
            '2025-06-02',
            '2025-06-23',
            '2025-06-30',
            '2025-07-20',
            '2025-08-07',
            '2025-08-18',
            '2025-10-13',
            '2025-11-03',
            '2025-11-17',
            '2025-12-08',
            '2025-12-25'
        ];

        // Obtener la duración total de la competencia en horas
        $rap = Rap::findOrFail($request->rap_id);
        $totalHours = $rap->number_hours;
        $percentage = $rap->subject->percentage;
        $hours = $totalHours * $percentage / 100;

        // Convertir las fechas y horas en objetos Carbon
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
                return response()->json(['message' => 'El campo days_of_week contiene valores inválidos.'], 422);
            }
        }

        $sessionsNeeded = ceil($hours / $sessionDuration);
        $sessionsCreated = [];
        $existingSessions = [];
        $currentDate = $startDate;


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
                return response()->json(['message' => 'El instructor ya tiene asignadas sesiones para estas fechas', $existingSession]);
            } else {

                // Verificar si otro instructor tiene una sesión en el mismo día y curso
                $existingSessionForCourse = Session::where('date', $currentDate->format('Y-m-d'))
                    ->where('course_id', $request->course_id)
                    ->where(function ($query) use ($startTime, $endTime) {
                        // Verifica si el nuevo horario se solapa con algún horario existente
                        $query->where('start_time', '<', $endTime->format('H:i'))
                            ->where('end_time', '>', $startTime->format('H:i'));
                    })->get();

                if ($existingSessionForCourse->isNotEmpty()) {
                    return response()->json(['message' => 'Otro instructor ya tiene una sesión en el mismo día y curso.', $existingSessionForCourse], 422);
                }


                $session = Session::create([
                    'date' => $currentDate->format('Y-m-d'),
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                    'instructor_id' => $request->instructor_id,
                    'course_id' => $request->course_id,
                    'rap_id' => $request->rap_id,
                ]);
                if ($course->end_date_training_stage <= $currentDate->format('Y-m-d')) {
                    Session::where('id', $session->id)->delete();
                    return response()->json(['message' => 'No se puede crear sesiones fuera de la etapa lectiva'], 422);
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

                $sessionsCreated[] = $session;
            }

            $currentDate->addDay();
            while (!in_array($currentDate->dayOfWeek, $dayOfWeek) || in_array($currentDate->format('Y-m-d'), $festivos)) {
                $currentDate->addDay();
            }
        }

        $lastSession = end($sessionsCreated);
        foreach ($sessionsCreated as $session) {
            $session->end_date = $lastSession->date;
            $session->update();
        }

        return response()->json([
            'message' => 'Sesiones y asistencias creadas exitosamente.',
            'sessions_created' => $sessionsCreated,
            'existing_sessions' => $existingSessions,
        ]);
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
        // $existingSessionWithRap = Session::where('rap_id', $request->rap_id)
        //     ->where('course_id', $request->course_id)
        //     ->exists();

        // if ($existingSessionWithRap) {
        //     return response()->json(['message' => 'Ya existen sesiones creadas con este RAP en el curso seleccionado.'], 422);
        // }
    }


    public function updateSessions(Request $request, $sessionIds)
    {
        // Validar los datos de entrada
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'instructor_id' => 'required|exists:instructors,id',
            'instructor2_id' => 'nullable|exists:users,id',
            'confirmed' => 'nullable|boolean',
        ]);

        $sessionsUpdated = [];
        foreach ($sessionIds as $sessionId) {
            $session = Session::findOrFail($sessionId);

            // Normalizar tiempos
            $startTime = $validated['start_time'] ? $validated['start_time'] . ':00' : $session->start_time;
            $endTime = $validated['end_time'] ? $validated['end_time'] . ':00' : $session->end_time;

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
                ], 422);
            }

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


            $session->save();

            // Si se cambia el curso, actualizar las asistencias
            if ($request->has('course_id')) {
                // Eliminar las asistencias existentes
                Assistance::where('session_id', $sessionId)->delete();

                // Crear nuevas asistencias para los aprendices del nuevo curso
                $aprendices = Apprentice::where('course_id', $request->course_id)->get();
                foreach ($aprendices as $aprendiz) {
                    Assistance::create([
                        'apprentice_id' => $aprendiz->id,
                        'session_id' => $session->id,
                        'assistance' => null,
                    ]);
                }
            }

            $sessionsUpdated[] = $session;
        }

        return response()->json([
            'message' => 'Sesiones actualizadas exitosamente.',
            'sessions' => $sessionsUpdated,
        ]);
    }
}
