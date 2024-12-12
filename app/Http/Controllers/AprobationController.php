<?php

namespace App\Http\Controllers;

use App\Models\Apprentice;
use App\Models\Aprobation;
use App\Models\Assistance;
use App\Models\Instructor;
use App\Models\User;
use App\Services\AprobationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AprobationController extends Controller
{
    protected $aprobationService;
    public function __construct(AprobationService $aprobationService)
    {
        $this->aprobationService = $aprobationService;
    }
    
    
    public function verStates()
    {
        $STATES = [
            (object) ['name' => 'Pendiente', 'id' => 1],
            (object) ['name' => 'Aprobada', 'id' => 2],
            (object) ['name' => 'Rechazada', 'id' => 3],
            (object) ['name' => 'Vencida', 'id' => 4]
        ];
        
        return response()->json($STATES);
    }

    public function index()
    {
        $aprobations = Aprobation::included()->filter()->get();
        return response()->json($aprobations);

    }

    public function show($id)
    {
        $aprobation = Aprobation::find($id);
        return response()->json($aprobation);
    }

    public function editStateOfJustification(Request $request)
    {
        $updateAprobation = $this->aprobationService->editStateOfJustification($request);
        return response()->json($updateAprobation);
    }
}
