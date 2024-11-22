<?php

namespace App\Http\Controllers;

use App\Models\Assistance;
use Illuminate\Http\Request;

class AssistanceController extends Controller
{
    //
    public function editAssistance(Request $request, $assistanceId)
    {
        // Buscar la asistencia por ID
        $assistance = Assistance::findOrFail($assistanceId);
    
        // Verificar si la sesión asociada existe
        $session = $assistance->session;
        if (!$session) {
            return response()->json(['message' => 'Sesión no encontrada para esta asistencia.'], 404);
        }
    
        // Validar los datos de entrada
        $request->validate([
            'assistance' => 'required|in:ASISTIO,FALTA,FALTA_JUSTIFICADA',
        ]);
    
        // Actualizar manualmente el campo (como prueba)
        $assistance->assistance = $request->input('assistance');
        $assistance->save();
    
        return response()->json([
            'message' => 'Asistencia actualizada correctamente.',
            'assistance' => $assistance,
        ]);
    }
    
}    
