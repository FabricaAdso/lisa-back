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
            'user_id' => 'required|exists:users,id',
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
            'user_id' => 'required|exists:users,id',
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
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'user_id' => 'required|exists:users,id', // El instructor de la sesión
        ]);

        // Verificar si ya existe una sesión para la misma fecha
        $existingSession = Session::where('date', $request->date)
            ->where('user_id', $request->user_id) // Se relaciona con el instructor
            ->first();

        if ($existingSession) {
            return response()->json(['message' => 'Ya existe una sesión para esta fecha.'], 409);
        }

        $session = Session::create($request->all());
        
        // Crear asistencias automáticamente para los participantes activos
        $participants = Participant::where('course_id', $request->course_id) // Ajustar para buscar por curso si es necesario
            ->whereNull('end_date')
            ->get();

        foreach ($participants as $participant) {
            $session->attendances()->create(['participant_id' => $participant->id]);
        }

        return response()->json($session, 201);
    }

    public function updateAssistance(Request $request, $assistanceId)
    {
        $request->validate([
            'assistance' => 'required|in:ASISTIO,FALTA,FALTA_JUSTIFICADA',
        ]);

        $assistance = Assistance::findOrFail($assistanceId);

        // Verificar que el usuario autenticado es el instructor de la sesión
        if (Auth::user()->id !== $assistance->session->user_id) {
            return response()->json(['message' => 'No tienes permisos para modificar esta asistencia.'], 403);
        }

        $assistance->update($request->all());
        return response()->json($assistance);
    }


}

