<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\TrainingCenter;
use App\Models\Regional;
use App\Models\Course;
use App\Models\Program;
use App\Models\EducationLevel;
use App\Models\DocumentType;
use App\Models\User;
use App\Models\Apprentice;



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


//     public function upload(Request $request)
// {

//     set_time_limit(0);

//     try {
//         $request->validate([
//             'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
//         ]);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return response()->json(['error' => $e->errors()], 422);
//     }

//     // Leer el archivo
//     $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
//     $sheet = $spreadsheet->getActiveSheet();
//     $batchSize = 1000; // Procesar en lotes de 1000 filas
//     $rows = [];

//     // Definir estados válidos para los campos enum
//     $validCourseStates = ['Terminada_por_fecha', 'En_ejecucion', 'Terminada', 'Termindad_por_unificacion'];
//     $validApprenticeStates = ['Formacion', 'Desertado', 'Etapa_productiva', 'Retiro_voluntario'];

//     // Omitir la primera fila (encabezados)
//     $firstRow = true;

//     // Procesar filas
//     foreach ($sheet->getRowIterator() as $row) {
//         if ($firstRow) {
//             $firstRow = false;
//             continue; // Omitir la primera fila (encabezados)
//         }

//         $data = [];
//         foreach ($row->getCellIterator() as $cell) {
//             $data[] = $cell->getValue();
//         }
//         $rows[] = $data;

//         // Procesar lote cuando se alcance el tamaño del batch
//         if (count($rows) >= $batchSize) {
//             $this->importBatch($rows, $validCourseStates, $validApprenticeStates);
//             $rows = [];
//         }
//     }

//     // Procesar el último lote si queda algo pendiente
//     if (count($rows) > 0) {
//         $this->importBatch($rows, $validCourseStates, $validApprenticeStates);
//     }

//     // Devolver una respuesta JSON en lugar de redirigir
//     return response()->json(['success' => 'Archivo importado exitosamente.']);
// }

//     private function importBatch($rows, $validCourseStates, $validApprenticeStates)
//     {
//         collect($rows)->chunk(500)->each(function ($chunk) use ($validCourseStates, $validApprenticeStates) {
//             DB::transaction(function () use ($chunk, $validCourseStates, $validApprenticeStates) {
//                 foreach ($chunk as $row) {
//                     try {
//                         $courseState = str_replace(' ', '_', $row[5]);
//                         $apprenticeState = str_replace(' ', '_', $row[15]);

//                         if (!in_array($courseState, $validCourseStates)) {
//                             throw new \Exception("Estado de ficha no válido: {$row[5]}");
//                         }
//                         if (!in_array($apprenticeState, $validApprenticeStates)) {
//                             throw new \Exception("Estado de aprendiz no válido: {$row[15]}");
//                         }

//                         $trainingCenter = TrainingCenter::updateOrCreate(
//                             ['code' => $row[0]], ['name' => $row[1]]
//                         );

//                         $regional = Regional::updateOrCreate(
//                             ['code' => $row[2]], ['name' => $row[3]]
//                         );

//                         $program = Program::updateOrCreate(
//                             ['code' => $row[6]],
//                             ['version' => $row[7], 'name' => $row[8], 'training_center_id' => $trainingCenter->id]
//                         );

//                         $course = Course::updateOrCreate(
//                             ['code' => $row[4]], ['state' => $courseState, 'program_id' => $program->id]
//                         );

//                         $educationLevel = EducationLevel::updateOrCreate(['name' => $row[9]]);

//                         $documentType = DocumentType::updateOrCreate(['abbreviation' => $row[10]]);

//                         $user = User::updateOrCreate(
//                             ['identity_document' => $row[11]],
//                             ['name' => $row[12], 'last_name' => $row[13] . ' ' . $row[14], 'document_type_id' => $documentType->id]
//                         );

//                         $apprentice = Apprentice::updateOrCreate(
//                             ['user_id' => $user->id, 'course_id' => $course->id], ['state' => $apprenticeState]
//                         );
//                     } catch (\Exception $e) {
//                         Log::error("Error en la fila: " . json_encode($row) . " - " . $e->getMessage());
//                     }
//                 }
//             });
//         });
//     }









public function upload(Request $request)
{
    // Validar que se haya enviado un archivo
    $request->validate([
        'file' => 'required|mimes:xlsx,xls|max:2048', // Acepta solo archivos Excel
    ]);

    try {
        // Leer el archivo Excel directamente desde la solicitud
        $data = Excel::toArray([], $request->file('file'))[0]; // Convierte el archivo en un array

        // Eliminar la primera fila (encabezados)
        array_shift($data);

        // Insertar los datos en la tabla temporal
        foreach ($data as $row) {
            DB::table('temp_excel_table')->insert([
                'CODIGO_SEDE' => $row[0],
                'SEDE' => $row[1],
                'CODIGO_REGIONAL' => $row[2],
                'REGIONAL' => $row[3],
                'FICHA' => $row[4],
                'ESTADO_FICHA' => $row[5],
                'CODIGO_PROGRAMA' => $row[6],
                'VERSION_PROGRANA' => $row[7],
                'PROGRAMA' => $row[8],
                'NIVEL_DE_FORMACION' => $row[9],
                'TIPO_DOCUMENTO' => $row[10],
                'NUMERO_DOCUMENTO' => $row[11],
                'NOMBRE' => $row[12],
                'PRIMER_APELLIDO' => $row[13],
                'SEGUNDO_APELLIDO' => $row[14],
                'ESTADO_APRENDIZ' => $row[15],
                'IDENTIFICADOR_CONVENIO' => $row[16],
                'AMPLIACION_COVERTURA' => $row[17],
                'CONVENIO' => $row[18],
                'TIPO_DOCUMENTO_EMPRESA' => $row[19],
                'NUMERO_DOCUMENTO_EMPRESA' => $row[20],
                'EMPRESA' => $row[21],
            ]);
        }

        // Ejecutar el procedimiento almacenado
        DB::statement('CALL CargarDatosDesdeExcel()');

        // Vaciar la tabla temporal después de procesar los datos
        DB::table('temp_excel_table')->truncate();

        return response()->json([
            'message' => 'Datos cargados exitosamente.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al procesar el archivo.',
            'error' => $e->getMessage(),
        ], 500);
    }
}









}
