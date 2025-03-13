<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Primero, se crean los registros básicos que no dependen de otros seeders
        $this->call([
            RolesSeeder::class,
            ]);
            //     UserRegisterSeeder::class, // Los usuarios

        // Llamada a seeders que dependen de los anteriores
        $this->call([
            UserRegisterSeeder::class, // Los usuarios
            InstructorSeeder::class, // El instructor con el usuario 30
            SessionSeeder::class, // Las sesiones
            ApprenticeSeeder::class, // Los aprendices
            AssistanceSeeder::class, // Las asistencias
            JustificationSeeder::class, // Las justificaciones
            // AprobationSeeder::class,
        ]);
    }
}

