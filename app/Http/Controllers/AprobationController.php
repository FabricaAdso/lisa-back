<?php

namespace App\Http\Controllers;

use App\Models\Aprobation;
use Illuminate\Http\Request;

class AprobationController extends Controller
{
    public function index()
    {
        $aprobations = Aprobation::all();
        return response()->json($aprobations);
    }

    public function show($id)
    {
        $aprobation = Aprobation::find($id);
        return response()->json($aprobation);
    }

    public function editStateOfJustification(Request $request)
    {
        
    }
}
