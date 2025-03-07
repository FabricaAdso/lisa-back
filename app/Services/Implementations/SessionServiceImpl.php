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
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'rap_id' => 'required|exists:raps,id',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:instructors,id',
            'instructor2_id' => 'nullable|exists:users,id',
            'days_of_week' => 'required|string',
            'percentage' => 'required|integer',
        ]);

        // Verificar competencia por programa
        $rapCompetencia = Rap::findOrFail($request->rap_id);
        $course = Course::findOrFail($request->course_id);

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
        // Verificar si ya existen sesiones creadas con este RAP en el curso seleccionado
        $existingSessionWithRap = Session::where('rap_id', $request->rap_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($existingSessionWithRap) {
            return response()->json(['message' => 'Ya existen sesiones creadas con este RAP en el curso seleccionado.'], 422);
        }

        // registro de cambio del porcentaje por usuario
        $user = User::find(Auth::id());
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

            $existingSession = Session::where('date', $currentDate->format('Y-m-d'))
                ->where('instructor_id', $request->instructor_id)
                ->exists();

            if ($existingSession) {
                return response()->json(['message' => 'El instructor ya tiene asignadas sesiones para estas fechas']);
            } else {

                $session = Session::create([
                    'date' => $currentDate->format('Y-m-d'),
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                    'instructor_id' => $request->instructor_id,
                    'course_id' => $request->course_id,
                    'rap_id' => $request->rap_id,
                ]);


                $aprendices = Apprentice::where('course_id', $request->course_id)->get();
                foreach ($aprendices as $aprendiz) {
                    Assistance::create([
                        'apprentice_id' => $aprendiz->id,
                        'session_id' => $session->id,
                        'assistance' => null,
                    ]);
                }

                $sessionsCreated[] = $session;
            }

            $currentDate->addDay();
            while (!in_array($currentDate->dayOfWeek, $dayOfWeek) || in_array($currentDate->format('Y-m-d'), $festivos)) {
                $currentDate->addDay();
            }
        }

        return response()->json([
            'message' => 'Sesiones y asistencias creadas exitosamente.',
            'sessions_created' => $sessionsCreated,
            'existing_sessions' => $existingSessions,
        ]);
    }


    public function updateSessions(Request $request, $sessionIds)
    {
        // Verificar si $sessionIds es un array y convertirlo a una cadena si es necesario
        if (is_array($sessionIds)) {
            // Si ya es un array, convertirlo a una cadena separada por comas
            $sessionIds = implode(',', $sessionIds);
        }

        // Convertir los sessionIds de la ruta a un array
        $sessionIds = explode(',', $sessionIds);

        // Validar los datos de entrada
        $request->validate([
            'start_date' => 'nullable|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'rap_id' => 'nullable|exists:subjects,id',
            'course_id' => 'nullable|exists:courses,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'instructor2_id' => 'nullable|exists:users,id',
        ]);

        $sessionsUpdated = [];
        foreach ($sessionIds as $sessionId) {

            $session = Session::findOrFail($sessionId);

            // Verificar si el instructor ya tiene una sesión en la nueva fecha (si se actualiza la fecha)
            if ($request->has('start_date')) {
                $existingSession = Session::where('date', $request->start_date)
                    ->where('instructor_id', $request->instructor_id ?? $session->instructor_id)
                    ->where('id', '!=', $sessionId) // Excluir la sesión actual
                    ->exists();

                if ($existingSession) {
                    return response()->json(['message' => 'El instructor ya tiene una sesión asignada para esta fecha.'], 422);
                }
            }

            // Actualizar los campos de la sesión
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
