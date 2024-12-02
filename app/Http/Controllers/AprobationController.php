<?php

namespace App\Http\Controllers;

use App\Models\Aprobation;
use App\Services\AprobationService;
use Illuminate\Http\Request;

class AprobationController extends Controller
{
    protected $aprobationService;
    public function __construct(AprobationService $aprobationService)
    {
        $this->aprobationService = $aprobationService;
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
