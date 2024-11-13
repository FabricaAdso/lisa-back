<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\EducationLevel;
use App\Models\Environment;
use App\Models\EnvironmentArea;
use App\Models\Headquarters;
use App\Models\Municipality;
use App\Models\Program;
use App\Models\TrainingCenter;
use App\Services\DataService;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelController extends Controller
{
    protected $excelImportService;

    public function __construct(ExcelImportService $excelImportService)
    {
        $this->excelImportService = $excelImportService;
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx|max:10240'
        ]);

        $file = $request->file('file');
        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($file->getRealPath());

        // Obtener la primera hoja
        $worksheet = $spreadsheet->getActiveSheet();

        // Obtener los encabezados
        $headers = [];
        foreach ($worksheet->getRowIterator(1, 1) as $headerRow) {
            foreach ($headerRow->getCellIterator() as $cell) {
                $headers[] = $cell->getValue();
            }
        }

        $optionalColumns = [
            'Hora inicio mañana',
            'Hora fin mañana',
            'Hora inicio tarde',
            'Hora fin tarde',
            'Hora inicio noche',
            'Hora fin noche',
        ];

        $allData = [];

        // Iterar sobre las filas, comenzando desde la fila 2
        foreach ($worksheet->getRowIterator(2) as $row) {
            $data = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $index = 0;
            foreach ($cellIterator as $cell) {
            $header = $headers[$index] ?? 'Columna desconocida';
            $data[$header] = $cell->getValue();
            
            if (empty($data[$header]) && !in_array($header, $optionalColumns)) {
                return response()->json([
                    'message' => 'Verifique que no tenga campos vacíos',
                    'fila' => "fila {$row->getRowIndex()}",
                    'columna' => $header,
                    'datos' => $data,
                ]);
            }
                $index++;
            }

            // Usar el servicio para guardar los datos de la fila
            $envioDeDatos = $this->excelImportService->saveRowData($data);
            $allData[] = $data;
            if(isset($envioDeDatos['error'])){
            $mensajeError = "Error en la fila {$row->getRowIndex()}: " .$envioDeDatos['error'];
                return response()->json([
                     'message' => $mensajeError,
                ]);
            }
        }
        return response()->json([
            'message' => 'Datos asignados correctamente',
            'datos' => $allData,
        ]);
    }

}
