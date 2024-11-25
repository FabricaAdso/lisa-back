<?php

namespace App\Services\Implementations;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Instructor;
use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Http\Request;

class SessionServiceImpl implements SessionService
{
    public function createSession(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'course_id' => 'required|exists:courses,id', 
            'instructor_id' => 'required|exists:instructors,id', 
            'instructor2_id' => 'nullable|exists:users,id',
        ]);
        
        $existingSession = Session::where('date', $request->date)
            ->where('instructor_id', $request->instructor_id)
            ->where('course_id', $request->course_id)
            ->first();
    
        if ($existingSession) {
            return response()->json(['message' => 'Ya existe una sesión para esta fecha, instructor y curso.'], 409);
        }
    
        $instructor = Instructor::with('user')->find($request->instructor_id);
    
        if (!$instructor) {
            return response()->json(['message' => 'Instructor no encontrado.'], 404);
        }
   
        $session = Session::create([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'instructor_id' => $request->instructor_id,
            'course_id' => $request->course_id,
        ]);
    
        $aprendices = Apprentice::with('user')
            ->where('course_id', $request->course_id) 
            ->get();
    
        if ($aprendices->isEmpty()) {
            return response()->json(['message' => 'No se encontraron aprendices activos para este curso.'], 400);
        }
 
        foreach ($aprendices as $aprendiz) {
            Assistance::create([
                'apprentice_id' => $aprendiz->id,
                'session_id' => $session->id,
                'assistance' => null,
            ]);
        }
    
        return response()->json([
            'message' => 'Sesión y asistencias creadas exitosamente.',
            'instructor' => $instructor,
            'aprendices' => $aprendices,
        ]);
    }
}