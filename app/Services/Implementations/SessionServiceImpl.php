<?php

namespace App\Services\Implementations;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Instructor;
use App\Models\Session;
use App\Services\SessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SessionServiceImpl implements SessionService
{
    public function createSession(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days_of_week' => 'required|string', 
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:instructors,id',
            'instructor2_id' => 'nullable|exists:users,id',
        ]);

        $festivos = [
            '2024-01-01', '2024-01-06', '2024-03-24', '2024-04-17', '2024-04-18',
            '2024-05-01', '2024-06-02', '2024-06-23', '2024-06-30', '2024-08-07',
            '2024-08-18', '2024-10-13', '2024-11-03', '2024-11-17', '2024-12-08', '2024-12-25'
        ];

        // Convertir los días de la semana en un arreglo
        $daysOfWeek = explode(',', $request->days_of_week);

        foreach ($daysOfWeek as $day) {
            if (!in_array($day, ['1', '2', '3', '4', '5', '6', '7'])) {
                return response()->json(['message' => 'El campo days_of_week contiene valores inválidos.'], 422);
            }
        }

        $sessionsCreated = [];
        $existingSessions = [];
        $currentDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        while ($currentDate->lte($endDate)) {
            if (in_array($currentDate->dayOfWeekIso, $daysOfWeek)) {
            
                if (!in_array($currentDate->format('Y-m-d'), $festivos)) { 
                    
                    $existingSession = Session::where('date', $currentDate->format('Y-m-d'))
                        ->where('instructor_id', $request->instructor_id)
                        ->where('course_id', $request->course_id)
                        ->first();

                    if ($existingSession) {
                        $existingSessions[] = $currentDate->format('Y-m-d');
                    } else {
                       
                        $session = Session::create([
                            'date' => $currentDate->format('Y-m-d'),
                            'start_time' => $request->start_time,
                            'end_time' => $request->end_time,
                            'instructor_id' => $request->instructor_id,
                            'course_id' => $request->course_id,
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
                }
            }

            $currentDate->addDay();
        }

        return response()->json([
            'message' => 'Sesiones y asistencias creadas exitosamente.',
            'sessions_created' => $sessionsCreated,
            'existing_sessions' => $existingSessions,
        ]);
    }
}
