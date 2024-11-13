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
      
        $assistance = Assistance::findOrFail($assistanceId);
       
        $session = $assistance->session;
        if (!$session) {
            return response()->json(['message' => 'Sesión no encontrada para esta asistencia.'], 404);
        }
        
        $instructor = $session->instructor;
        if (!$instructor) {
            return response()->json(['message' => 'Instructor no encontrado en la sesión.'], 404);
        }
    
        $request->validate([
            'assistance' => 'required|in:ASISTIO,FALTA,FALTA_JUSTIFICADA',
        ]);
      
        $assistance->update([
            'assistance' => $request->input('assistance'),
        ]);
    
        return response()->json(['message' => 'Asistencia actualizada correctamente.', 'assistance' => $assistance]);
    }
}
