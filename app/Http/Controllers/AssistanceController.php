<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Aprobation;
use App\Models\Assistance;
use App\Models\Justification;
use App\Models\User;
use App\Services\ApprenticeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Console\Scheduling\Schedule;

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
                if($justificationDelete->aprobation){
                    $justificationDelete->aprobation->delete();
                }
                $justificationDelete->delete();
            }
        } elseif ($newAssistance == 0) {
            $Justification = Justification::firstOrCreate([
                'assistance_id' => $assistance->id,
            ], [
                'file_url' => null,  
                'description' => null,
            ]);
            Aprobation::firstOrCreate([
                'justification_id' => $Justification->id,
            ],[
                'state' => null,
                'motive' => null,
            ]);
        }
    }

    public function getInassitanceApprentice ()
    {
        $user = User::find(Auth::id());
        $apprendice = Apprentice::where('user_id', $user->id)->first();
        if(!$apprendice){
            return response()->json([]);
        }
        $assistance = Assistance::where('apprentice_id', $apprendice->id)
            ->with([
                'session',
                'justifications.aprobation'
            ])
            ->whereHas('justifications.aprobation')
            ->get();
        return response()->json($assistance);
    }
}
