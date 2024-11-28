<?php

namespace App\Http\Controllers;

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


        $assistance->assistance = $request->input('assistance');
        $assistance->save();
        if($assistance->assistance == 0){
            $justification = Justification::firstOrcreate([
            'assistance_id' => $assistance->id,
            'file_url' => null,
            'description' => null,
            ]);
        }

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
}
