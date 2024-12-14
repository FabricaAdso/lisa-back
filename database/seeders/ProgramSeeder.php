<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run()
    {
        // Crear 20 programas con códigos aleatorios de 4 dígitos
        for ($i = 0; $i < 20; $i++) {
            Program::create([
                'code' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT), // Código aleatorio de 4 dígitos
                'version' => 'V' . rand(1, 3), // Versión aleatoria (V1, V2, V3)
                'name' => 'Programa ' . $i + 1, // Nombre del programa (puedes personalizarlo si es necesario)
                'education_level_id' => null, // Puedes asignar un nivel educativo si ya lo tienes
                'training_center_id' => null, // Puedes asignar un centro de formación si ya lo tienes
            ]);
        }
    }
}
