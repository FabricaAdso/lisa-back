<?php

namespace App\Http\Controllers;

use App\Models\Assistance;
use Illuminate\Http\Request;

class AssistanceController extends Controller
{
    //
    public function editAssistance(Request $request, $assistanceId)
    {

        $assistance = Assistance::findOrFail($assistanceId);

        $session = $assistance->session;
        if (!$session) {
            return response()->json(['message' => 'Sesión no encontrada para esta asistencia.'], 404);
        }


        $request->validate([
            'assistance' => 'required|in:ASISTIO,FALTA,FALTA_JUSTIFICADA',
        ]);


        $assistance->assistance = $request->input('assistance');
        $assistance->save();

        return response()->json([
            'message' => 'Asistencia actualizada correctamente.',
            'assistance' => $assistance,
        ]);
    }

}
