<?php

namespace Database\Seeders;

use App\Models\Apprentice;
use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;

class ApprenticeSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $courses = Course::all();

        $states = ['Formacion', 'Desertado', 'Etapa_productiva', 'Retiro_voluntario'];

        // Creamos 22 aprendices con un estado aleatorio
        foreach ($users->take(22) as $user) {
            Apprentice::create([
                'state' => $states[array_rand($states)], // Estado aleatorio
                'user_id' => $user->id, // ID del usuario
                'course_id' => $courses->random()->id, // Curso aleatorio
            ]);
        }
    }
}
