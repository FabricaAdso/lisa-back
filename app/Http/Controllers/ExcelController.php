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
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelController extends Controller
{
    protected $dataService;

    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

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

    public function getTrainingCenterId($municipio)
    {
        $municiId = Municipality::where('name', $municipio)->first();
        return $municiId ? $municiId->id : null;
    }

    public function getEducationLevelId($eduLevel)
    {
        $educationLevelId = EducationLevel::where('name', $eduLevel)->first();
        return $educationLevelId ? $educationLevelId->id : null;
    }


    private function saveRowData($data)
    {

        $trainingCenterId = TrainingCenter::updateOrCreate([
            'code' => $data['Codigo'] ?? null,
            'name' => $data['CentroFormacion'] ?? null,
        ]);
        $headquartersId = Headquarters::updateOrCreate([
            'name' => $data['Sedes'] ?? null,
            'municipality_id' => $this->getTrainingCenterId($data['Municipio']) ?? null,
            'training_center_id' =>  $trainingCenterId ? $trainingCenterId->id :  null,
        ]);
        $envirimentAreaId = EnvironmentArea::updateOrCreate([
            'name' => $data['Tipo de Ambiente'] ?? null,
        ]);
        Environment::updateOrCreate([
            'name' => $data['Ambientes'] ?? null,
            'capacity' => $data['Capacidad'] ?? null,
            'headquarters_id' =>  $headquartersId ? $headquartersId->id : null,
            'environment_area_id' =>  $envirimentAreaId ? $envirimentAreaId->id : null,
        ]);
        $programId = Program::updateOrCreate([
            'name' =>  $data['Programas'] ?? null,
            'education_level_id' => $this->getEducationLevelId($data['Nivel']) ?? null,
            'training_center_id' =>  $trainingCenterId ? $trainingCenterId->id :  null,
        ]);
        // Convierte los valores seriales de fecha a formato Y-m-d
        $dateStart = $this->dataService->excelDateToDate($data['Fecha de inicio']);
        $dateEnd = $this->dataService->excelDateToDate($data['Fecha de fin']);
        Course::updateOrCreate([
            'code' => $data['Fichas'] ?? null,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'program_id' => $programId ? $programId->id : null
        ]);

    }
}
