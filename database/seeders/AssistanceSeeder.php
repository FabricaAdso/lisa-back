<?php

namespace Database\Seeders;

use App\Models\Assistance;
use App\Models\Session;
use App\Models\Apprentice;
use Illuminate\Database\Seeder;

class AssistanceSeeder extends Seeder
{
    public function run()
    {
        $sessions = Session::all();
        $apprentices = Apprentice::all();

        // Para cada sesión y aprendiz, generamos un registro de asistencia
        foreach ($sessions as $session) {
            foreach ($apprentices->random(10) as $apprentice) { // asignamos asistencia a 10 aprendices por sesión
                Assistance::create([
                    'assistance' => rand(0, 1), // 0 o 1 para asistencia
                    'session_id' => $session->id, // ID de la sesión
                    'apprentice_id' => $apprentice->id, // ID del aprendiz
                ]);
            }
        }
    }
}
