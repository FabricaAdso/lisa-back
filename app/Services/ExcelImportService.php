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
    protected $courseShiftService;
    public function __construct(DataService $dataService, CourseShiftService $courseShiftService)
    {
        $this->dataService = $dataService;
        $this->courseShiftService = $courseShiftService;
    }


    public function getMunicipalityId($municipio)
    {
        $municiId = Municipality::where('name', $municipio)->first();
        return $municiId ? $municiId->id : null;
    }


    public function saveRowData($data)
    {
        // Convierte los valores seriales de fecha a formato Y-m-d
        $dateStart = $this->dataService->excelDateToDate($data['Fecha de inicio']);
        $dateEnd = $this->dataService->excelDateToDate($data['Fecha de fin']);
        $opening_time = $this->dataService->excelDecimalToTime($data['Hora de apertura']);
        $closing_time = $this->dataService->excelDecimalToTime($data['Hora de cierre']);
        //validar fechas y horas de inicio < fechas y horas de fin
        if($validateStartEndDate = $this->dataService->validateDateAndTime($dateStart, $dateEnd)){
            return $validateStartEndDate;
        }

        if( $validateOpeningClosingTime = $this->dataService->validateDateAndTime($opening_time, $closing_time)){
            return $validateOpeningClosingTime;
        }

        $trainingCenterId = TrainingCenter::updateOrCreate([
            'code' => $data['Codigo']
        ], [
            'name' => $data['CentroFormacion'] ?? null,
        ]);

        $headquartersId = Headquarters::updateOrCreate([
            'name' => $data['Sedes'] ?? null,
            'municipality_id' => $this->getMunicipalityId($data['Municipio']) ?? null,
            'training_center_id' => $trainingCenterId ? $trainingCenterId->id : null,
        ], [
            'adress' => $data['Direccion'] ?? null,
            'opening_time' => $opening_time,
            'closing_time' => $closing_time,
        ]);

        $envirimentAreaId = EnvironmentArea::updateOrCreate([
            'name' => $data['Tipo de Ambiente'] ?? null,
        ]);

        $environment = Environment::updateOrCreate([
            'name' => $data['Ambientes'] ?? null,
            'headquarters_id' => $headquartersId ? $headquartersId->id : null,
        ],
        [
            'capacity' => $data['Capacidad'] ?? null,
            'environment_area_id' => $envirimentAreaId ? $envirimentAreaId->id : null,
        ]);   

        $educationLevelId = EducationLevel::updateOrCreate([
            'name' => $data['Nivel'] ?? null,
        ]);
        $programId = Program::updateOrCreate([
            'name' =>  $data['Programas'] ?? null,
            'education_level_id' => $educationLevelId ? $educationLevelId->id : null,
            'training_center_id' =>  $trainingCenterId ? $trainingCenterId->id :  null,
        ]);

        //dividir las jornadas 
        $shiftIds = [];
        $jornadas = explode(',', $data['Jornadas']);
        foreach ($jornadas as $jornada) {
            $jornada = trim($jornada);
            //definir horarios de inicio y fin segun la jornada
            if($jornada == 'Mañana'){
                if(empty($data['Hora inicio mañana']) || empty($data['Hora inicio mañana'])){
                    return ['error' => "Los horarios de la jornada Mañana están incompletos."];
                }
                $start_time = $this->dataService->excelDecimalToTime($data['Hora inicio mañana']);
                $end_time = $this->dataService->excelDecimalToTime($data['Hora fin mañana']);

            }else if($jornada == 'Tarde'){
                if (empty($data['Hora inicio tarde']) || empty($data['Hora fin tarde'])) {
                    return ['error' => "Los horarios de la jornada Tarde están incompletos."];
                }
                $start_time = $this->dataService->excelDecimalToTime($data['Hora inicio tarde']);
                $end_time = $this->dataService->excelDecimalToTime($data['Hora fin tarde']);
            }else if($jornada == 'Noche'){
                if (empty($data['Hora inicio noche']) || empty($data['Hora fin noche'])) {
                    return ['error' => "Los horarios de la jornada de Noche están incompletos."];
                }
                $start_time = $this->dataService->excelDecimalToTime($data['Hora inicio noche']);
                $end_time = $this->dataService->excelDecimalToTime($data['Hora fin noche']);
            }

            if ($validateStartEndTime = $this->dataService->validateDateAndTime($start_time, $end_time)) {
                return $validateStartEndTime;
            }
            
            $shift = Shift::updateOrCreate([
                'name' => $jornada
            ], [
                'start_time' =>$start_time,
                'end_time' =>$end_time
            ]);

            $shiftIds[] = $shift->id;
        }

        $courseIds = [];
        $course = Course::updateOrCreate([
            'code' => $data['Fichas'] ?? null,
        ],[
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'program_id' => $programId ? $programId->id : null,
        ]);
        $shiftIds[] = $course->id;
        //redireccion para asignar cursos a jornadas
        $response = $this->courseShiftService->assignCourseShifts($shiftIds, [$course->id]);

        if (isset($response['error'])) {
            return $response; // Si hay error, retornar el error
        }
        return true;
    }
    
}