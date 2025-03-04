<?php

namespace App\Services\Implementations;

use App\Models\Aprobation;
use App\Models\Justification;
use App\Services\AprobationService;

class AprobationServiceImpl implements AprobationService
{
    public function editStateOfJustification($request)
    {
        $request->validate([
            'state' => 'required|in:Aprobada,Rechazada',
            'justification_id' => 'required|exists:justifications,id',
            'motive' => 'required_if:state,Rechazada',
        ]);

        $aprobationState = Aprobation::where('justification_id', $request->justification_id)->firstOrFail();
        $estadoActual = $aprobationState->state;
        $nuevoEstado = $request->input('state');

        // Si el estado actual es "Vencida", no se puede cambiar
        if ($estadoActual === 'Vencida') {
            return [
                'message' => "El estado de la justificación es 'Vencida' y no se puede cambiar.",
            ];
        }
        if ($estadoActual === 'En_espera') {
            return [
                'message' => "El estado de la justificación es 'En espera' y debe estar en estado Pendiente.",
            ];
        }

        if (is_null($estadoActual) || $estadoActual === 'Pendiente') {
            if ($nuevoEstado === 'Rechazada') {
                $motive = $request->input('motive');
                $aprobationState->update([
                    'state' => $nuevoEstado,
                    'motive' => $motive
                ]);
                $justifications = Justification::included()->findOrfail($request->justification_id);
                return $justifications;
            }

            if ($nuevoEstado === 'Aprobada') {
                $aprobationState->update(['state' => $nuevoEstado]);
                $justifications = Justification::included()->findOrfail($request->justification_id);
                return $justifications;
            }
        }
    }
}
