<?php
namespace App\Services;

use App\Models\Course;
use App\Models\Day;
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
    protected $courseEnvironmentService;
    public function __construct(DataService $dataService, CourseShiftService $courseShiftService, CourseEnvironmentService $courseEnvironmentService)
    {
        $this->dataService = $dataService;
        $this->courseShiftService = $courseShiftService;
        $this->courseEnvironmentService = $courseEnvironmentService;
    }


    public function getMunicipalityId($municipio)
    {
        $municiId = Municipality::where('name', $municipio)->first();
        return $municiId ? $municiId->id : null;
    }

    public function getDaysId($days)
    {
        $days = explode(', ', $days);
        foreach ($days as $day) {
            $day = trim($day);
            $dayId = Day::where('name', $day)->first();

            if($dayId){
                $daysId[] = $dayId->id;
            }else{
                return ['error' => "{$day} no es un dia valido"];
            }
        }
        return $daysId;
    }


    public function saveRowData($data)
    {
        // Convert date values and opening/closing times
        $dateStart = $this->dataService->excelDateToDate($data['Fecha de inicio']);
        $dateEnd = $this->dataService->excelDateToDate($data['Fecha de fin']);
        $opening_time = $this->dataService->excelDecimalToTime($data['Hora de apertura']);
        $closing_time = $this->dataService->excelDecimalToTime($data['Hora de cierre']);
        
        // Validate date and time ranges
        if ($validateStartEndDate = $this->dataService->validateDateAndTime($dateStart, $dateEnd)) {
            return $validateStartEndDate;
        }
        if ($validateOpeningClosingTime = $this->dataService->validateDateAndTime($opening_time, $closing_time)) {
            return $validateOpeningClosingTime;
        }

        // Update or create related records
        $trainingCenterId = TrainingCenter::updateOrCreate(
            ['code' => $data['Codigo']],
            ['name' => $data['CentroFormacion'] ?? null]
        );

        $headquartersId = Headquarters::updateOrCreate(
            [
                'name' => $data['Sedes'] ?? null,
                'municipality_id' => $this->getMunicipalityId($data['Municipio']) ?? null,
                'training_center_id' => $trainingCenterId->id ?? null,
            ],
            [
                'adress' => $data['Direccion'] ?? null,
                'opening_time' => $opening_time,
                'closing_time' => $closing_time,
            ]
        );

        $envirimentAreaId = EnvironmentArea::updateOrCreate(
            ['name' => $data['Tipo de Ambiente'] ?? null]
        );

        $environment = Environment::updateOrCreate(
            [
                'name' => $data['Ambientes'] ?? null,
                'headquarters_id' => $headquartersId->id ?? null,
            ],
            [
                'capacity' => $data['Capacidad'] ?? null,
                'environment_area_id' => $envirimentAreaId->id ?? null,
            ]
        );

        $educationLevelId = EducationLevel::updateOrCreate(
            ['name' => $data['Nivel'] ?? null]
        );

        $programId = Program::updateOrCreate(
            [
                'name' => $data['Programas'] ?? null,
                'education_level_id' => $educationLevelId->id ?? null,
                'training_center_id' => $trainingCenterId->id ?? null,
            ]
        );

        // Process shift data
        $shiftIds = [];
        $jornadas = explode(',', $data['Jornadas']);
        foreach ($jornadas as $jornada) {
            $jornada = trim($jornada);
            
            switch ($jornada) {
                case 'Mañana':
                    if (empty($data['Hora inicio mañana']) || empty($data['Hora fin mañana'])) {
                        return ['error' => "Los horarios de la jornada Mañana están incompletos."];
                    }
                    $start_time = $this->dataService->excelDecimalToTime($data['Hora inicio mañana']);
                    $end_time = $this->dataService->excelDecimalToTime($data['Hora fin mañana']);
                    break;
                case 'Tarde':
                    if (empty($data['Hora inicio tarde']) || empty($data['Hora fin tarde'])) {
                        return ['error' => "Los horarios de la jornada Tarde están incompletos."];
                    }
                    $start_time = $this->dataService->excelDecimalToTime($data['Hora inicio tarde']);
                    $end_time = $this->dataService->excelDecimalToTime($data['Hora fin tarde']);
                    break;
                case 'Noche':
                    if (empty($data['Hora inicio noche']) || empty($data['Hora fin noche'])) {
                        return ['error' => "Los horarios de la jornada Noche están incompletos."];
                    }
                    $start_time = $this->dataService->excelDecimalToTime($data['Hora inicio noche']);
                    $end_time = $this->dataService->excelDecimalToTime($data['Hora fin noche']);
                    break;
                default:
                    return ['error' => "La jornada '{$jornada}' no es válida."];
            }
        
            // Validate start and end times
            if ($validateStartEndTime = $this->dataService->validateDateAndTime($start_time, $end_time)) {
                return $validateStartEndTime;
            }
        
            // Update or create the shift
            $shift = Shift::updateOrCreate(
                ['name' => $jornada],
                ['start_time' => $start_time, 'end_time' => $end_time]
            );
        
            $shiftIds[] = $shift->id;
        }

        // Update or create the course
        $course = Course::updateOrCreate(
            ['code' => $data['Fichas'] ?? null],
            [
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'program_id' => $programId->id ?? null,
            ]
        );

        $traerDias = $this->getDaysId($data['Dias']);
        if (isset($traerDias['error'])) {
            return $traerDias;
        }

        // Assign course to shifts
        $response = $this->courseShiftService->assignCourseShifts($shiftIds, [$course]);
        if (isset($response['error'])) {
            return $response;
        }
        // // Assign environment to course
        // $responseCE = $this->courseEnvironmentService->assingCourseEnvitonment([$environment->id], [$course]);
        // if (isset($responseCE['error'])) {
        //     return $responseCE;
        // }

        return ['success' => 'Data saved successfully'];
    }  
    
}  