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
        $sessions = Session::all();  // Obtiene todas las sesiones
        $apprentices = Apprentice::all();  // Obtiene todos los aprendices

        // Para cada sesión, generamos un registro de asistencia para cada aprendiz
        foreach ($sessions as $session) {
            foreach ($apprentices as $apprentice) { // Asigna a todos los aprendices
                Assistance::create([
                    'assistance' => 0, // Todas las asistencias serán 0
                    'session_id' => $session->id, // ID de la sesión
                    'apprentice_id' => $apprentice->id, // ID del aprendiz
                ]);
            }
        }
    }
}
