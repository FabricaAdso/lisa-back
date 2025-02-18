<?php

namespace Database\Seeders;
use App\Models\Session;
use App\Models\Instructor;
use App\Models\Course;
use Illuminate\Database\Seeder;

use Carbon\Carbon;

class SessionSeeder extends Seeder
{
    public function run()
    {
        // Obtener el primer instructor (Instructor 1) y los cursos
        $instructor1 = Instructor::first();  // Suponiendo que el instructor 1 es el primer registro
        $courses = Course::all();

        // Comprobamos si existe el instructor 1
        if (!$instructor1) {
            $this->command->error('Instructor 1 no encontrado.');
            return;
        }

        // Verificamos si hay cursos disponibles
        if ($courses->isEmpty()) {
            $this->command->error('No hay cursos disponibles para asignar a las sesiones.');
            return;
        }

        // Fecha límite para las sesiones pasadas: 16 de diciembre de 2024
        $limitDate = Carbon::create(2024, 12, 16);

        // Creamos 100 sesiones asignadas al instructor 1 con fechas pasadas
        for ($i = 0; $i < 1; $i++) {
            // Generar una fecha aleatoria antes del 16 de diciembre de 2024
            $randomDate = $limitDate->subDays(rand(1, 30));  // Fecha aleatoria entre el 16 de diciembre y 30 días atrás

            Session::create([
                'date' => $randomDate,  // Fecha aleatoria antes del 16 de diciembre de 2024
                'start_time' => $randomDate->setTime(rand(8, 18), rand(0, 59)), // Hora de inicio aleatoria
                'end_time' => $randomDate->setTime(rand(19, 23), rand(0, 59)), // Hora de fin aleatoria
                'instructor_id' => $instructor1->id, // Asignamos siempre el instructor 1
                'instructor2_id' => null, // Segundo instructor es null
                'course_id' => $courses->random()->id, // Curso aleatorio
            ]);
        }
    }
}

