<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Primero, se crean los registros básicos que no dependen de otros seeders
        //      // Los usuarios

        // Llamada a seeders que dependen de los anteriores
        $this->call([
            RolesSeeder::class,
            DocumentTypeSeeder::class,
            RegionalSeeder::class,
            TrainingCenterSeeder::class,
            UserRegisterSeeder::class,
            KnowledgeNetworkSeeder::class,
            ProgramSeeder::class,
            CourseSeeder::class,
            SubjectSeeder::class,
            RapSeeder::class,
            InstructorSeeder::class, // El instructor con el usuario 30
            SessionSeeder::class, // Las sesiones
            ApprenticeSeeder::class, // Los aprendices
            AssistanceSeeder::class, // Las asistencias
            JustificationSeeder::class, // Las justificaciones
            // AprobationSeeder::class,
        ]);
    }
}
