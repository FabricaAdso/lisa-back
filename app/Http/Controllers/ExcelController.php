<?php

namespace App\Http\Controllers;

use App\Services\ExcelService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\TrainingCenter;
use App\Models\Regional;
use App\Models\Course;
use App\Models\Program;
use App\Models\EducationLevel;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Apprentice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExcelController extends Controller
{
    // protected $excelService;
    // public function __construct(ExcelService $excelService)
    // {
    //     $this->excelService = $excelService;
    // }

    // public function excel(Request $request)
    // {
    //    $excel = $this->excelService->excelImport($request);
    //    return response()->json($excel);
    // }


    public function upload(Request $request)
{

    set_time_limit(0);

    try {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => $e->errors()], 422);
    }

    // Leer el archivo
    $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
    $sheet = $spreadsheet->getActiveSheet();
    $batchSize = 1000; // Procesar en lotes de 1000 filas
    $rows = [];

    // Definir estados válidos para los campos enum
    $validCourseStates = ['Terminada_por_fecha', 'En_ejecucion', 'Terminada', 'Termindad_por_unificacion'];
    $validApprenticeStates = ['Formacion', 'Desertado', 'Etapa_productiva', 'Retiro_voluntario'];

    // Omitir la primera fila (encabezados)
    $firstRow = true;

    // Procesar filas
    foreach ($sheet->getRowIterator() as $row) {
        if ($firstRow) {
            $firstRow = false;
            continue; // Omitir la primera fila (encabezados)
        }

        $data = [];
        foreach ($row->getCellIterator() as $cell) {
            $data[] = $cell->getValue();
        }
        $rows[] = $data;

        // Procesar lote cuando se alcance el tamaño del batch
        if (count($rows) >= $batchSize) {
            $this->importBatch($rows, $validCourseStates, $validApprenticeStates);
            $rows = [];
        }
    }

    // Procesar el último lote si queda algo pendiente
    if (count($rows) > 0) {
        $this->importBatch($rows, $validCourseStates, $validApprenticeStates);
    }

    // Devolver una respuesta JSON en lugar de redirigir
    return response()->json(['success' => 'Archivo importado exitosamente.']);
}

    private function importBatch($rows, $validCourseStates, $validApprenticeStates)
    {
        collect($rows)->chunk(500)->each(function ($chunk) use ($validCourseStates, $validApprenticeStates) {
            DB::transaction(function () use ($chunk, $validCourseStates, $validApprenticeStates) {
                foreach ($chunk as $row) {
                    try {
                        $courseState = str_replace(' ', '_', $row[5]);
                        $apprenticeState = str_replace(' ', '_', $row[15]);

                        if (!in_array($courseState, $validCourseStates)) {
                            throw new \Exception("Estado de ficha no válido: {$row[5]}");
                        }
                        if (!in_array($apprenticeState, $validApprenticeStates)) {
                            throw new \Exception("Estado de aprendiz no válido: {$row[15]}");
                        }

                        $trainingCenter = TrainingCenter::updateOrCreate(
                            ['code' => $row[0]], ['name' => $row[1]]
                        );

                        $regional = Regional::updateOrCreate(
                            ['code' => $row[2]], ['name' => $row[3]]
                        );

                        $program = Program::updateOrCreate(
                            ['code' => $row[6]],
                            ['version' => $row[7], 'name' => $row[8], 'training_center_id' => $trainingCenter->id]
                        );

                        $course = Course::updateOrCreate(
                            ['code' => $row[4]], ['state' => $courseState, 'program_id' => $program->id]
                        );

                        $educationLevel = EducationLevel::updateOrCreate(['name' => $row[9]]);

                        $documentType = DocumentType::updateOrCreate(['abbreviation' => $row[10]]);

                        $user = User::updateOrCreate(
                            ['identity_document' => $row[11]],
                            ['name' => $row[12], 'last_name' => $row[13] . ' ' . $row[14], 'document_type_id' => $documentType->id]
                        );

                        $apprentice = Apprentice::updateOrCreate(
                            ['user_id' => $user->id, 'course_id' => $course->id], ['state' => $apprenticeState]
                        );
                    } catch (\Exception $e) {
                        Log::error("Error en la fila: " . json_encode($row) . " - " . $e->getMessage());
                    }
                }
            });
        });
    }

}
