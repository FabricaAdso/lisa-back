<?php

namespace App\Http\Controllers;

use App\Models\Aprobation;
use App\Models\Assistance;
use App\Models\Justification;
use App\Services\ApprenticeService;
use Illuminate\Http\Request;

class AssistanceController extends Controller
{
    
    protected $apprenticeService;

    public function __construct(ApprenticeService $apprenticeService)
    {
        $this->apprenticeService = $apprenticeService;
    }

    public function editAssistance(Request $request, $assistanceId)
    {

        $assistance = Assistance::findOrFail($assistanceId);

        $session = $assistance->session;
        if (!$session) {
            return response()->json(['message' => 'Sesión no encontrada para esta asistencia.'], 404);
        }


        $request->validate([
            'assistance' => 'required|boolean',
        ]);

        $assistancePrevius = $assistance->assistance;
        $newAssistance = $request->input('assistance');
        $assistance->assistance = $newAssistance;
        $assistance->save();
        $this->JustificationAndAprobation($assistance ,$assistancePrevius, $newAssistance);

        return response()->json([
            'message' => 'Asistencia actualizada correctamente.',
            'assistance' => $assistance,
        ]);
    }

    public function UnjustifiedAbsences($apprenticeId)
    {
        $faults = $this->apprenticeService->UnjustifiedAbsences($apprenticeId);

        return response()->json(['unjustifiedAbsences' => $faults]);
    }

    public function JustificationAndAprobation($assistance, $assistancePrevius, $newAssistance)
    {
        if ($assistancePrevius == 0 && $newAssistance == 1) {
            $justificationDelete = Justification::where('assistance_id', $assistance->id)->first();
            
            if ($justificationDelete) {
                $justificationDelete->delete();
            }
        } elseif ($newAssistance == 0) {
            Justification::firstOrCreate([
                'assistance_id' => $assistance->id,
            ], [
                'file_url' => null,  
                'description' => null,
            ]);
        }
    }
}
