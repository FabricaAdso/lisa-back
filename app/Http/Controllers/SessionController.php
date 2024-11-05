<?php

namespace App\Http\Controllers;

use App\Models\Assistance;
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
        $sessions = Session::included()->filter()->get();
        //$sessions = Session::included()->get();

        return response()->json($sessions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'participant_id' => 'required|exists:users,id',
        ]);

        $session = Session::create($request->all());
        return response()->json($session);
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
            'participant_id' => 'required|exists:users,id',
        ]);

        $session =Session::find($id);
        $session->update($request->all());
        return response()->json($session);
    }

    public function destroy($id)
    {
        $session =  Session::find($id);
        $session->delete();
        return response()->json(['message' => 'Session deleted successfully']);
    }

    // Crear sesión
    public function createSession(Request $request)
    {
        // Validación de los datos de la sesión
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'participant_id' => 'required|exists:participants,id', // El instructor de la sesión
        ]);
    
        // Verificar que no exista otra sesión en la misma fecha y para el mismo instructor
        $existingSession = Session::where('date', $request->date)
            ->where('participant_id', $request->participant_id)
            ->first();
    
        if ($existingSession) {
            return response()->json(['message' => 'Ya existe una sesión para esta fecha y usuario.'], 409);
        }
        $participante = Participant::with('user')->find($request->participant_id);
        if($participante && $participante->user->hasRole('Instructor')){
            //$participante->load(['user','course']);
            return response()->json(['message' => 'Sesión y asistencias creadas exitosamente.','profesor'=>$participante]);
        }else{
            return response()->json('no es instructor');
        }
        // Crear la sesión
        $session = Session::create([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'participant_id' => $request->participant_id, // Instructor asignado a la sesión
        ]);
        
        //app(AssistanceController::class)->createForSession($session->id);

    
    }
}

