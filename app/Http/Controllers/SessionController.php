<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Assistance;
use App\Models\Instructor;
use App\Models\Participant;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    //
    public function index()
    {
        //$sessions = Session::all();
        $sessions = Session::included()->get();
        

        return response()->json($sessions);
    }

    public function show($id)
    {
        $session = Session::find($id);
        return response()->json($session);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'instructor_id' => 'required|exists:users,id',
        ]);

        $session = Session::find($id);
        $session->update($request->all());
        return response()->json($session);
    }

    public function destroy($id)
    {
        $session =  Session::find($id);
        $session->assistances()->delete();
        $session->delete();
        return response()->json(['message' => 'Session deleted successfully']);
    }

    // Crear sesión
    
    public function createSession(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'instructor_id' => 'required|exists:instructors,id', // ID del instructor de la sesión
        ]);
    
        // Comprobar si ya existe una sesión en la misma fecha y con el mismo instructor
        $existingSession = Session::where('date', $request->date)
            ->where('instructor_id', $request->instructor_id)
            ->first();
    
        if ($existingSession) {
            return response()->json(['message' => 'Ya existe una sesión para esta fecha y usuario.'], 409);
        }
    
        // Obtener el instructor
        $instructor = Instructor::with('user', 'courses')->find($request->instructor_id);
    
        if (!$instructor) {
            return response()->json(['message' => 'Instructor no encontrado.'], 404);
        }
    
        // Crear la sesión con el instructor_id incluido
        $session = Session::create([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'instructor_id' => $request->instructor_id,
        ]);
    
        // Obtener los aprendices activos en el curso del instructor
        $aprendices = Apprentice::with('user')
            ->whereHas('course', function ($query) use ($instructor) {
                $query->where('id', $instructor->course_id);
            })
            ->whereHas('user.roles', function ($query) {
                $query->where('name', 'Aprendiz');
            })
            ->whereNull('end_date')
            ->get();
    
        if ($aprendices->isEmpty()) {
            return response()->json(['message' => 'No se encontraron aprendices activos para esta ficha.'], 400);
        }
    
        // Crear asistencias para cada aprendiz
        foreach ($aprendices as $aprendiz) {
            Assistance::create([
                'apprentice_id' => $aprendiz->id,
                'session_id' => $session->id,
                'assistance' => null,
            ]);
        }
    
        return response()->json([
            'message' => 'Sesión y asistencias creadas exitosamente.',
            'profesor' => $instructor,
            'aprendices' => $aprendices,
        ]);
    }
    

}
// public function createSession(Request $request)
//     {

//         $request->validate([
//             'date' => 'required|date',
//             'start_time' => 'required|date_format:H:i',
//             'end_time' => 'required|date_format:H:i|after:start_time',
//             'instructor_id' => 'required|exists:instructors,id', // El instructor de la sesión
//         ]);


//         $existingSession = Session::where('date', $request->date)
//             ->where('participant_id', $request->participant_id)
//             ->first();

//         if ($existingSession) {
//             return response()->json(['message' => 'Ya existe una sesión para esta fecha y usuario.'], 409);
//         }


//         $instructor = Participant::with('user', 'course')->find($request->participant_id);

//         if ($instructor && $instructor->user->hasRole('Instructor')) {

//             // Crear la sesión
//             $session = Session::create([
//                 'date' => $request->date,
//                 'start_time' => $request->start_time,
//                 'end_time' => $request->end_time,
//                 'participant_id' => $request->participant_id,
//                 'course_id' => $request->course_id,
//             ]);

//             // Obtener los aprendices activos en el curso del instructor
//             $aprendices = Participant::with('user', 'role')
//                 ->where('course_id', $instructor->course_id)
//                 ->whereHas('user.roles', function ($query) {
//                     $query->where('name', 'Aprendiz');
//                 })
//                 ->whereNull('end_date')
//                 ->get();

//             // Verificar si se encontraron aprendices
//             if ($aprendices->isEmpty()) {
//                 return response()->json(['message' => 'No se encontraron aprendices activos para esta ficha.'], 400);
//             }

//             // Crear asistencias para cada aprendiz
//             foreach ($aprendices as $aprendiz) {
//                 Assistance::create([
//                     'participant_id' => $aprendiz->id,
//                     'session_id' => $session->id,
//                     'assistance' => null,
//                 ]);
//             }

//             return response()->json([
//                 'message' => 'Sesión y asistencias creadas exitosamente.',
//                 'profesor' => $instructor,
//                 'aprendices' => $aprendices,
//             ]);
//         } else {
//             return response()->json(['message' => 'El participante no tiene rol de instructor.'], 403);
//         }
//     }