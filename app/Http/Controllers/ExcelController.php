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
        ini_set('max_execution_time', 300);

        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        // Guardar el archivo temporalmente en private/temp
        $filePath = $request->file('file')->store('', 'private_temp');
        $fullPath = storage_path("app/private/temp/{$filePath}");

        // Depuración: Imprimir la ruta del archivo
        echo "Ruta del archivo temporal: {$fullPath}";

        // Ruta al intérprete de Python del entorno virtual
        $pythonPath = base_path('.venv/bin/python3');

        // Ruta al script de Python
        $scriptPath = base_path('scripts/import_courses.py');

        // Ejecutar el script de Python
        $output = [];
        $returnVar = 0;
        exec("{$pythonPath} {$scriptPath} {$fullPath} 2>&1", $output, $returnVar);

        // Eliminar el archivo temporal (si existe)
        if (file_exists($fullPath)) {
            unlink($fullPath);
            echo "Archivo temporal eliminado: {$fullPath}";
        } else {
            echo "El archivo no existe: {$fullPath}";
        }

        if ($returnVar === 0) {
            return response()->json(['message' => 'Archivo importado correctamente'], 200);
        } else {
            return response()->json(['message' => 'Error al importar el archivo', 'error' => implode("\n", $output)], 500);
        }
    }

    public function importApprentices(Request $request)
    {
        ini_set('max_execution_time', 300);

        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        // Guardar el archivo temporalmente en private/temp
        $filePath = $request->file('file')->store('', 'private_temp');
        $fullPath = storage_path("app/private/temp/{$filePath}");

        // Depuración: Imprimir la ruta del archivo
        echo "Ruta del archivo temporal: {$fullPath}";

        // Ruta al intérprete de Python del entorno virtual
        $pythonPath = base_path('.venv/bin/python3');

        // Ruta al script de Python
        $scriptPath = base_path('scripts/imports_apprentices.py');

        // Ejecutar el script de Python
        $output = [];
        $returnVar = 0;
        exec("{$pythonPath} {$scriptPath} {$fullPath} 2>&1", $output, $returnVar);

        // Eliminar el archivo temporal (si existe)
        if (file_exists($fullPath)) {
            unlink($fullPath);
            echo "Archivo temporal eliminado: {$fullPath}";
        } else {
            echo "El archivo no existe: {$fullPath}";
        }

        if ($returnVar === 0) {
            return response()->json(['message' => 'Archivo importado correctamente'], 200);
        } else {
            return response()->json(['message' => 'Error al importar el archivo', 'error' => implode("\n", $output)], 500);
        }
    }

    public function importInstructors(Request $request)
    {
        ini_set('max_execution_time', 300);

        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        // Guardar el archivo temporalmente en private/temp
        $filePath = $request->file('file')->store('', 'private_temp');
        $fullPath = storage_path("app/private/temp/{$filePath}");

        // Depuración: Imprimir la ruta del archivo
        echo "Ruta del archivo temporal: {$fullPath}\n";

        // Ruta al intérprete de Python del entorno virtual
        $pythonPath = base_path('.venv/bin/python3');

        // Ruta al script de Python
        $scriptPath = base_path('scripts/imports_instructors.py');

        // Ejecutar el script de Python
        $output = [];
        $returnVar = 0;
        exec("{$pythonPath} {$scriptPath} {$fullPath} 2>&1", $output, $returnVar);

        // Eliminar el archivo temporal (si existe)
        if (file_exists($fullPath)) {
            unlink($fullPath);
            echo "Archivo temporal eliminado: {$fullPath}\n";
        } else {
            echo "El archivo no existe: {$fullPath}\n";
        }

        if ($returnVar === 0) {
            return response()->json(['message' => 'Archivo importado correctamente'], 200);
        } else {
            return response()->json(['message' => 'Error al importar el archivo', 'error' => implode("\n", $output)], 500);
        }
    }


}
