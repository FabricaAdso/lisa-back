<?php

namespace Database\Seeders;
use App\Models\Session;
use App\Models\Instructor;
use App\Models\Course;
use Illuminate\Database\Seeder;

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

        // Creamos 10 sesiones asignadas al instructor 1
        for ($i = 0; $i < 10; $i++) {
            Session::create([
                'date' => now()->addDays(rand(1, 30)), // fecha aleatoria dentro del mes
                'start_time' => now()->setTime(rand(8, 18), rand(0, 59)), // hora de inicio aleatoria
                'end_time' => now()->setTime(rand(19, 23), rand(0, 59)), // hora de fin aleatoria
                'instructor_id' => $instructor1->id, // Asignamos siempre el instructor 1
                'instructor2_id' => null, // Segundo instructor es null
                'course_id' => $courses->random()->id, // Curso aleatorio
            ]);
        }
    }
}
