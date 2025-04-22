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
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;

class AssistanceController extends Controller
{

    protected $apprenticeService;

    public function __construct(ApprenticeService $apprenticeService)
    {
        $this->apprenticeService = $apprenticeService;
    }

    public function index()
    {
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
        $this->JustificationAndAprobation($assistance, $assistancePrevius, $newAssistance);

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

    public function JustificationAndAprobation(Request $request)
    {
        $validate = $request->validate([
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:assistances,id',
            'data.*.assistance' => 'required',
        ]);
        DB::beginTransaction();

        try {
            foreach ($validate['data'] as $item) {
                $assistance = Assistance::findOrFail($item['id']);
                $previousAssistance = $assistance->assistance;
                $newAssistance = (bool)$item['assistance'];

                if ($previousAssistance === $newAssistance) {
                    continue;
                }

                if($previousAssistance === null && $newAssistance === false){
                    $this->createJustificationAndAprobation($assistance);
                }
                if ($previousAssistance === false && $newAssistance === true) {
                    $this->handleJustificationRemoval($assistance);
                } elseif ($newAssistance === false) {
                    $this->createJustificationAndAprobation($assistance);
                }

                $assistance->assistance = $newAssistance;
                $assistance->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'asistencia tomada correctamente'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'error ' . $e->getMessage()
            ], 409);
        }
    }

    protected function handleJustificationRemoval(Assistance $assistance)
    {
        $justification = Justification::with('aprobation')
            ->where('assistance_id', $assistance->id)
            ->first();

        if ($justification) {
            if ($justification->aprobation) {
                $justification->aprobation->delete();
            }
            $justification->delete();
        }
    }

    protected function createJustificationAndAprobation(Assistance $assistance)
    {
        $justification = Justification::firstOrCreate(
            ['assistance_id' => $assistance->id],
            [
                'file_url' => null,
                'motive' => null
            ]
        );

        Aprobation::firstOrCreate(
            ['justification_id' => $justification->id],
            [
                'state' => 'En_espera',
                'motive' => null
            ]
        );
    }

    public function getInassitanceApprentice()
    {
        $user = User::find(Auth::id());
        $apprentice = Apprentice::where('user_id', $user->id)->first();
        $assistance = Assistance::where('apprentice_id', $apprentice->id)
            ->included()
            ->filter()
            ->get();
        return response()->json([$apprentice, $assistance]);
    }

    public function getInassitanceInstructor()
    {
        $user = User::find(Auth::id());
        $instructor = Instructor::where('user_id', $user->id)->first();
        $session = Session::where('instructor_id', $instructor->id)
            ->included()
            ->filter()
            ->get();
        return response()->json([$instructor, $session]);
    }

    public function getAssistanceForSession($id)
    {
        $session = Session::find($id);
        $assistance = Assistance::where('session_id', $session->id)
            ->included()
            ->get();
        return response()->json($assistance);
    }

    public function allAsisence(Request $request)
    {

        $validate = $request->validate([
            'data' => 'required|array',
            'data.*.aid' => 'required|integer|exists:assistances,id',
            'date.*.assistance' => 'required|boolean'
        ]);
    }
}
