<?php

namespace App\Http\Controllers;

use App\Models\Headquarters;
use App\Models\TrainingCenter;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelController extends Controller
{
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx|max:10240'
        ]);

        $file = $request->file('file');
        //guardar temporalmente
        $spreadsheet = IOFactory::load($file->getRealPath());

        // Obtener la primera hoja
        $worksheet = $spreadsheet->getActiveSheet();

        //encavezados
        $headers = [];

        //cargar headers el archivo
        foreach($worksheet->getRowIterator(1,1) as $headerRow){
            foreach($headerRow->getCellIterator() as $cell){
                $headers[] = $cell->getValue();
            }
        }

        //iterar sobre las filas, empezando sobre la 2
        foreach ($worksheet->getRowIterator(2) as $row) {
            $data = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            //asociar cada celda con su encabezado
            $index = 0;
            foreach ($cellIterator as $cell) {
                $data[$headers[$index]] = $cell->getValue();
                $index++;
            }
            //para guardar la fila
            $this->saveRowData($data);
        }
        return response()->json([
            'message' => 'Datos cargados exitosamente',
            'data' => $data
            ]);
    }

    private function saveRowData($data)
    {
        TrainingCenter::updateOrCreate(['name' => $data['CentroFormacion'] ?? null]);
        Headquarters::updateOrCreate([
            'name' => $data['Sedes'] ?? null,
            'training_center_id' => $data['CentroFormacion'] ?? null
        ]);
    }
}
