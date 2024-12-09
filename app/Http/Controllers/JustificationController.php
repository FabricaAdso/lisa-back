<?php

namespace App\Http\Controllers;

use App\Models\Justification;
use App\Services\JustificationService;
use Illuminate\Http\Request;

class JustificationController extends Controller
{
    protected $justificationService;
    public function __construct(JustificationService $justificationService)
    {
        $this->justificationService = $justificationService;
    }
    public function checkAndUpdateExpiredJustifications()
    {
        return $this->justificationService->checkAndUpdateExpiredJustifications();
    }

    public function index()
    {
        // $justifications = Justification::included()->filter()->get();
        $justifications = Justification::included()->filter()->get();
        return response()->json($justifications);
    }

    public function show($id)
    {
        $justification = Justification::findOrFail($id)->included()->filter()->get();
        return response()->json($justification);
    }

    public function createJustification(Request $request)
    {
        $justification = $this->justificationService->createJustification($request);
        return response()->json($justification);
    }

}
