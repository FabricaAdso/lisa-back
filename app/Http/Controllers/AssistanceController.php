<?php

namespace App\Http\Controllers;

use App\Models\Assistance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssistanceController extends Controller
{
    //

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


