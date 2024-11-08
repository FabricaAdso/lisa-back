<?php
namespace App\Services;

use App\Models\Course;
use App\Models\EducationLevel;
use App\Models\Environment;
use App\Models\EnvironmentArea;
use App\Models\Headquarters;
use App\Models\Municipality;
use App\Models\Program;
use App\Models\Shift;
use App\Models\TrainingCenter;


class ExcelImportService
{
    protected $dataService;
    protected $environmentService;
    protected $courseService;
    public function __construct(DataService $dataService, EnvironmentService $environmentService, CourseService $courseService)
    {
        $this->dataService = $dataService;
        $this->environmentService = $environmentService;
        $this->courseService = $courseService;
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

    public function saveRowData($data)
    {
        // Convierte los valores seriales de fecha a formato Y-m-d
        $dateStart = $this->dataService->excelDateToDate($data['Fecha de inicio']);
        $dateEnd = $this->dataService->excelDateToDate($data['Fecha de fin']);
        $start_time = $this->dataService->excelDecimalToTime($data['Hora inicio']);
        $end_time = $this->dataService->excelDecimalToTime($data['Hora fin']);
        $opening_time = $this->dataService->excelDecimalToTime($data['Fecha de apertura']);
        $closing_time = $this->dataService->excelDecimalToTime($data['Fecha de cierre']);

        
        $trainingCenterId = TrainingCenter::updateOrCreate([
            'code' => $data['Codigo'],
            'name' => $data['CentroFormacion'] ?? null,
        ]);
        $headquartersId = Headquarters::updateOrCreate([
            'name' => $data['Sedes'] ?? null,
            'opening_time' => $opening_time,
            'closing_time' => $closing_time,
            'municipality_id' => $this->getTrainingCenterId($data['Municipio']) ?? null,
            'training_center_id' =>  $trainingCenterId ? $trainingCenterId->id :  null,
        ]);
        $envirimentAreaId = EnvironmentArea::updateOrCreate([
            'name' => $data['Tipo de Ambiente'] ?? null,
        ]);
        $environment = Environment::updateOrCreate([
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

        $shift = Shift::updateOrCreate([
            'name' => $data['Jornadas'] ?? null,
            'start_time' =>$start_time,
            'end_time' =>$end_time
        ]);
        $course = Course::updateOrCreate([
            'code' => $data['Fichas'] ?? null,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'program_id' => $programId ? $programId->id : null,
        ]);
    }
    
}