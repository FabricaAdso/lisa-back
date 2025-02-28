<?php

namespace App\Http\Controllers;

use App\Imports\CoursesImport;
use App\Imports\UserImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class ExcelController extends Controller
{
    public function importCourses(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        Excel::import(new CoursesImport, $request->file('file'));

        return response()->json(['message' => 'Archivo importado correctamente'], 200);
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        $import = new UserImport();
        Excel::import($import, $request->file('file'));

        return response()->json([
            'message' => 'Importación completada',
            'errores' => $import->getErrores()
        ], 200);
    }





}
