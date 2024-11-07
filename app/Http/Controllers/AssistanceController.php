<?php

namespace App\Http\Controllers;

use App\Models\Assistance;
use App\Models\Participant;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssistanceController extends Controller
{
    public function editAssistance(Request $request, $assistanceId)
    {
        // Buscar la asistencia específica por su ID
        $assistance = Assistance::findOrFail($assistanceId);
        //$assistance->load('participant');

        // Obtener la sesión asociada a la asistencia
        $session = $assistance->session;

        if (!$session) {
            return response()->json(['message' => 'Sesión no encontrada para esta asistencia.'], 404);
        }

        // Verificar que la sesión tiene un participante (instructor)
        $instructor = $session->participant;

        if (!$instructor) {
            return response()->json(['message' => 'Instructor no encontrado en la sesión.', $session], 404);
        }

        // Verificar que el instructor tiene un usuario asociado y el rol 'Instructor'
        if (!$instructor->user || !$instructor->user->roles->contains('name', 'Instructor')) {
            return response()->json(['message' => 'El participante no tiene rol de instructor.'], 403);
        }

        // Verificar que el usuario autenticado es el instructor de la sesión
        $instructor = $session->participant;

        // Validar los datos de la solicitud
        $request->validate([
            'assistance' => 'required|in:ASISTIO,FALTA,FALTA_JUSTIFICADA',
        ]);

        // Actualizar el estado de la asistencia
        $assistance->update([
            'assistance' => $request->input('assistance'),
        ]);

        // Retornar la respuesta
        return response()->json(['message' => 'Asistencia actualizada correctamente.', 'assistance' => $assistance]);
    }
}
