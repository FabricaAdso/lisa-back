<?php

namespace App\Http\Controllers;

use App\Services\ExcelService;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    protected $excelService;
    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function excel(Request $request)
    {
       $excel = $this->excelService->excelImport($request);
       return response()->json($excel);
    }
}
