<?php

namespace Database\Seeders;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        // Crear 20 cursos con códigos aleatorios de 7 dígitos
        for ($i = 0; $i < 20; $i++) {
            Course::create([
                'code' => str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT), // Genera un código aleatorio de 7 dígitos
                'date_start' => now()->addDays(rand(1, 30)), // Fecha de inicio aleatoria
                'date_end' => now()->addDays(rand(31, 60)), // Fecha de fin aleatoria
                'shift' => rand(0, 1) ? 'Mañana' : 'Tarde', // Asigna turno aleatorio
                'state' => collect(['Terminada_por_fecha', 'En_ejecucion', 'Terminada', 'Termindad_por_unificacion'])->random(), // Estado aleatorio
                'stage' => collect(['PRACTICA', 'LECTIVA'])->random(), // Etapa aleatoria
                'program_id' => null, // Puedes asignar un programa si ya tienes programas creados
                'environment_id' => null, // Puedes asignar un ambiente si ya tienes ambientes creados
            ]);
        }
    }
}
