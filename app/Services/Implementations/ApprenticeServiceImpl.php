<?php

namespace App\Services\Implementations;

use App\Models\Assistance;
use App\Services\ApprenticeService;

class ApprenticeServiceImpl implements ApprenticeService
{
    public function UnjustifiedAbsences($apprenticeId)
        {
            $assistances = Assistance::where('apprentice_id', $apprenticeId)
                ->orderBy('created_at', 'asc')
                ->get();
    
            $unjustifiedAbsences = 0;
    
            $faults = 0;
    
            foreach ($assistances as $assistance) {
                if ($assistance->assistance === 'FALTA') {
                    $faults++;
                } elseif ($assistance->assistance === 'FALTA_JUSTIFICADA' || $assistance->assistance === 'ASISTIO') {
                    $faults = 0; 
                }
    
                $unjustifiedAbsences = max($unjustifiedAbsences, $faults);
            }
    
            return $unjustifiedAbsences;
        }
}




