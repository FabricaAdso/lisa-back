<?php

namespace App\Imports;

use App\Models\Regional;
use App\Models\TrainingCenter;
use App\Models\EducationLevel;
use App\Models\Program;
use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class CoursesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $stateMapping = [
            'Terminada por fecha' => 'Terminada_por_fecha',
            'En ejecucion' => 'En_ejecucion',
            'Terminada' => 'Terminada',
            'Terminada por unificacion' => 'Termindad_por_unificacion',
        ];

        $regional = Regional::firstOrCreate(['name' => $row['regional']]);

        $trainingCenter = TrainingCenter::firstOrCreate(
            ['code' => $row['id_centro']],
            ['name' => $row['centro'], 'regional_id' => $regional->id]
        );

        $educationLevel = EducationLevel::firstOrCreate(['name' => $row['nivel']]);

        $program = Program::updateOrCreate(
            ['code' => $row['codigo_programa']],
            [
                'version' => $row['version_programa'],
                'name' => $row['programa'],
                'education_level_id' => $educationLevel->id,
                'training_center_id' => $trainingCenter->id
            ]
        );

        $state = trim($row['estado_ficha']);
        $state = $stateMapping[$state] ?? 'En_ejecucion';

        return Course::firstOrCreate(
            ['code' => $row['ficha']],
            [
                'date_start' => \Carbon\Carbon::parse($row['fecha_inicio_ficha'])->format('Y-m-d'),
                'date_end'   => \Carbon\Carbon::parse($row['fecha_fin_ficha'])->format('Y-m-d'),
                'state'      => $state,
                'shift'      => $row['jornada'],
                'program_id' => $program->id
            ]
        );
    }

    


}
