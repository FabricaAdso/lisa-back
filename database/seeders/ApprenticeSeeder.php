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

        foreach ($users->take(20) as $user) {
            Apprentice::create([
                'state' =>'Formacion',
                'user_id' => $user->id, // ID del usuario
                'course_id' => $courses->random()->id, // Curso aleatorio
            ]);
        }
    }
}
