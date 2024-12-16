<?php

namespace Database\Seeders;

use App\Models\Assistance;
use App\Models\Justification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class JustificationSeeder extends Seeder
{
    public function run()
    {
        // Obtenemos todas las asistencias con 'assistance' igual a 0 (inasistencia)
        $assistances = Assistance::where('assistance', 0)->get();

        foreach ($assistances as $assistance) {
            // Crear una descripción de la justificación
            $description = "Justificación por inasistencia en la sesión " . $assistance->session->id;

            // Simulamos la creación de un archivo PDF (solo nombre de archivo)
            $fileName = 'justification_' . $assistance->id . '.pdf';

            // Crear la justificación en la base de datos
            Justification::create([
                'file_url' => 'storage/justifications/' . $fileName,  // Simulamos el archivo .pdf
                'description' => $description,
                'assistance_id' => $assistance->id,
            ]);
        }
    }
}
