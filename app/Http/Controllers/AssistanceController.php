<?php

namespace App\Http\Controllers;

use App\Models\Assistance;
use App\Models\Participant;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssistanceController extends Controller
{

    public function createForSession($sessionId)
    {
        // Verificar que la sesión exista
        $session = Session::findOrFail($sessionId);

        // Obtener todos los aprendices activos en la ficha de la sesión (donde end_date es null)
        $activeParticipants = Participant::where('course_id', $session->course_id)
            ->whereNull('end_date') // Solo los participantes activos
            ->get();

        if (empty($activeParticipants)) {
            return response()->json(['message' => 'No se encontraron aprendices activos para esta ficha.',
        'parti'=> $activeParticipants], 400);
        }

        // Crear un registro de asistencia para cada participante activo con el estado "ASISTIO"
        // foreach ($activeParticipants as $participant) {
        //     Assistance::create([
        //         'participant_id' => $participant->id,
        //         'session_id' => $session->id,
        //         'assistance' => 'ASISTIO', // Estado inicial por defecto
        //     ]);
        // }

        return response()->json(['message' => 'Asistencias creadas para la sesión']);
    }
}


    // public function updateAssistance(Request $request, $assistanceId)
    // {
    //     $request->validate([
    //         'assistance' => 'required|in:ASISTIO,FALTA,FALTA_JUSTIFICADA',
    //     ]);

    //     $assistance = Assistance::findOrFail($assistanceId);

    //     // Verificar que el usuario autenticado es el instructor de la sesión
    //     if (Auth::user()->id !== $assistance->session->user_id) {
    //         return response()->json(['message' => 'No tienes permisos para modificar esta asistencia.'], 403);
    //     }

    //     $assistance->update($request->all());
    //     return response()->json($assistance);
    // }



