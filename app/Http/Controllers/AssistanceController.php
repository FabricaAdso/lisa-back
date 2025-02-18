<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Aprobation;
use App\Models\Assistance;
use App\Models\Instructor;
use App\Models\Justification;
use App\Models\Session;
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

    public function index(){
        $assistance = Assistance::included()->filter()->get();
        return response()->json($assistance);
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
        $apprentice = Apprentice::where('user_id', $user->id)->first();
        $assistance = Assistance::where('apprentice_id', $apprentice->id)
            ->included()
            ->filter()
            ->get();
        return response()->json([$apprentice ,$assistance]);
    }

    public function getInassitanceInstructor ()
    {
        $user = User::find(Auth::id());
        $instructor = Instructor::where('user_id', $user->id)->first();
        $session = Session::where('instructor_id', $instructor->id)
            ->included()
            ->filter()
            ->get();
        return response()->json([$instructor ,$session]);
    }

    public function getAssistanceForSession($id)
    {
        $session = Session::find($id);
        $assistance = Assistance::where('session_id', $session->id)
            ->included()
            ->get();
        return response()->json($assistance);
    }
}
