<?php

namespace App\Services\Implementations;

use App\Models\Assistance;
use App\Services\ApprenticeService;

class ApprenticeServiceImpl implements ApprenticeService
{
    public function UnjustifiedAbsences($apprenticeId)
        {
            // Obtener todas las asistencias del aprendiz en orden cronológico
            $assistances = Assistance::where('apprentice_id', $apprenticeId)
                ->orderBy('created_at', 'asc')
                ->get();
    
            // Contador de faltas consecutivas sin justificar
            $unjustifiedAbsences = 0;
    
            // Variable para mantener el conteo actual
            $faults = 0;
    
            // Recorrer las asistencias
            foreach ($assistances as $assistance) {
                if ($assistance->assistance === 'FALTA') {
                    $faults++;
                } elseif ($assistance->assistance === 'FALTA_JUSTIFICADA' || $assistance->assistance === 'ASISTIO') {
                    $faults = 0; // Reinicia el conteo si no es una falta sin justificar
                }
    
                // Actualizar el conteo máximo de faltas consecutivas
                $unjustifiedAbsences = max($unjustifiedAbsences, $faults);
            }
    
            return $unjustifiedAbsences;
        }
}




