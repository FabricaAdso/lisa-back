<?php

namespace App\Services\Implementations;

use App\Models\Course;
use App\Models\EducationLevel;
use App\Models\Program;
use App\Models\Regional;
use App\Models\TrainingCenter;
use App\Services\ExcelService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExcelServiceImpl implements ExcelService
{
    public function excelImport($request)
    {
        set_time_limit(300); // 300 segundos (5 minutos)

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
        foreach ($worksheet->getRowIterator(5, 5) as $headerRow) {
            foreach ($headerRow->getCellIterator() as $cell) {
                $headers[] = $cell->getValue();
            }
        }

        $optionalColumns = ['SEGUNDO_APELLIDO'];

        $stateMapping = [
            'En ejecucion' => 'En_ejecucion',
            'Terminada por fecha' => 'Terminada_por_fecha',
            'Terminada' => 'Terminada',
            'Termindad por unificacion' => 'Termindad_por_unificacion',
        ];

        // Procesar el archivo por lotes (chunks)
        $batchSize = 500; // Tamaño del chunk
        $allData = [];
        $rowData = [];

        foreach ( $worksheet->getRowIterator(6) as $row){
            $data = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $index = 0;
            foreach ($cellIterator as $cell) {
                $header = $headers[$index];
                $data[$header] = $cell->getValue();
                if (empty($data[$header]) && !in_array($header, $optionalColumns)) {
                    return [
                        'message' => 'Verifique que no tenga campos vacíos',
                        'fila' => "fila {$row->getRowIndex()}",
                        'columna' => $header,
                        'datos' => $data,
                    ];
                }
                $data['ESTADO_FICHA'] = $stateMapping[$data['ESTADO_FICHA']] ?? null;
                $rowData[] = $data;

                if(count($rowData) >= $batchSize){
                    $this->processChunk($rowData);
                    $rowData = [];
                }
            }
            if(!empty($rowData)){
                $this->processChunk($rowData);
            }
            
            return [
                'message' => 'Archivo procesado correctamente',
            ];
        }
    }

    private function processChunk($chunk)
    {
        DB::transaction(function () use ($chunk) {
            foreach ($chunk as $data) {
                $this->processData($data);
            }
        });
    }

    public function processData($data)
    {
        // Buscar o crear el Regional
        $regional = Regional::updateOrCreate(
            ['code' => $data['CODIGO_REGIONAL']],
            ['name' => $data['REGIONAL']]
        );

        // Buscar o crear el Training Center
        $trainingCenter = TrainingCenter::updateOrCreate(
            ['code' => $data['CODIGO_SEDE']],
            ['name' => $data['SEDE'], 
            'regional_id' => $regional->id]
        );

        // Buscar o crear el Education Level
        $educationLevel = EducationLevel::updateOrCreate(
            ['name' => $data['NIVEL_DE_FORMACION']]
        );

        // Buscar o crear el Program
        $program = Program::updateOrCreate(
            ['code' => trim($data['CODIGO_PROGRAMA'])],
            [
                'version' => $data['VERSION_PROGRANA'] ?? 'N/A',
                'name' => $data['PROGRAMA'],
                'education_level_id' => $educationLevel->id,
                'training_center_id' => $trainingCenter->id,
            ]
        );

        // Usar upsert para Course
        $courseData = [
            [
                'code' => $data['FICHA'],
                'date_start' => null,
                'date_end' => null,
                'shift' => null,
                'state' => $data['ESTADO_FICHA'],
                'stage' => null,
                'program_id' => $program->id,
            ]
        ];

        Course::upsert($courseData, ['code'], ['date_start', 'date_end', 'shift', 'state', 'stage', 'program_id']);
    }

}
