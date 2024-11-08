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

        $allData = [];

        // Iterar sobre las filas, comenzando desde la fila 2
        foreach ($worksheet->getRowIterator(2) as $row) {
            $data = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $index = 0;
            foreach ($cellIterator as $cell) {
                $data[$headers[$index]] = $cell->getValue();
                if(empty($data[$headers[$index]] = $cell->getValue())){
                    return response()->json([
                        'message' => 'vrifique que no tenga campos vacios',
                    ]);
                }
                $index++;
            }

            // Usar el servicio para guardar los datos de la fila
            $this->excelImportService->saveRowData($data);
            $allData[] = $data;
            // return response()->json($data);
        }
        return response()->json([
            'message' => 'Datos cargados exitosamente',
            'todos los datos' => $allData
        ]);
    }

}
