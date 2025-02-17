<?php

namespace Database\Seeders;

use App\Models\Aprobation;
use App\Models\Justification; // Si usas el modelo Justification
use Illuminate\Database\Seeder;

class AprobationSeeder extends Seeder
{
    public function run(): void
    {
        // Motivos aleatorios para justificar una falta
        $motives = [
            'Enfermedad',
            'Cita médica',
            'Problemas familiares',
            'Transporte averiado',
            'Clima adverso',
            'Asuntos personales'
        ];

        // Crear registros con el estado "Pendiente" y motivos aleatorios
        $aprobations = [];
        // Obtener los IDs de justificaciones existentes
        $justificationIds = Justification::pluck('id')->toArray();

        if (empty($justificationIds)) {
            $this->command->error('No se encontraron justificaciones en la base de datos.');
            return;
        }

        for ($i = 1; $i <= 20; $i++) {
            $aprobations[] = [
                'state' => 'Pendiente',  // El estado es siempre "Pendiente"
                'motive' => $motives[array_rand($motives)],  // Motivo aleatorio
                'justification_id' => $justificationIds[array_rand($justificationIds)],  // Usar un ID válido de justificación
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar los registros en la base de datos usando el modelo Aprobation
        Aprobation::insert($aprobations);
    }
}
