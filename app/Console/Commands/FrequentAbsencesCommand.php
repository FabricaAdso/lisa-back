<?php

namespace App\Console\Commands;

use App\Events\NotificationEvent;
use App\Models\Apprentice;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FrequentAbsencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verificar:inasistencias-frecuentes:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'comando para contar las inasistencias de los aprendices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Lógica del comando
        Log::info("comando para contar las inasistencias de los aprendices");
        $apprentices = Apprentice::whereHas('assistances', function ($q) {
            $q->where('assistance', 0);
        })->with(['assistances' => function ($q) {
            $q->where('assistance', 0)
                ->orderBy('updated_at', 'asc');
        }])->get();
        Log::info("total aprendices con alguna falta: " . $apprentices->count());

        foreach ($apprentices as $apprentice) {
            $totalInasistencias = $apprentice->assistances->where('assistance', 0)->count();
            Log::info("Aprendiz ID {$apprentice->id}: Total de inasistencias: {$totalInasistencias}");
            Log::info(json_encode($apprentice, JSON_PRETTY_PRINT));

            // Extraer y ordenar las fechas de las asistencias
            $fechas = collect($apprentice['assistances'])
                ->pluck('updated_at')
                ->sort()
                ->values();

            Log::info("Fechas ordenadas: " . json_encode($fechas, JSON_PRETTY_PRINT));

            $faltasConsecutivas = 1; // La primera asistencia ya cuenta
            for ($i = 1; $i < $fechas->count(); $i++) {
                // Calcula la diferencia en días en forma absoluta
                $fechaAnterior = $fechas[$i -1]->copy()->timezone('UTC')->startOfDay();
                $fechaActuales = $fechas[$i]->copy()->timezone('UTC')->startOfDay();
                // $diferencia = $fechas[$i]->diffInDays($fechas[$i - 1], true);
                Log::info("Fecha anterior: {$fechaAnterior}");
                Log::info("Fecha actual: {$fechaActuales}");
                if ($fechaAnterior->diffInDays($fechaActuales) == 1) {
                    $faltasConsecutivas++;
                } else {
                    $faltasConsecutivas = 1;
                }

                Log::info("Aprendiz ID {$apprentice->id}: Faltas consecutivas actual: {$faltasConsecutivas}");
            }

            if ($faltasConsecutivas == 3) {
                Log::info("el aprendiz con el id {$apprentice->id} tiene 3 faltas consecutivas");
                $notification = Notification::create([
                    'user_id' => $apprentice->user->id,
                    'message' => 'El aprendiz con id ' . $apprentice->id . ' tiene 3 faltas consecutivas',
                    'type' => 'warning',
                ]);
                event(new NotificationEvent($notification));
                Log::info(json_encode($notification, JSON_PRETTY_PRINT));
            }
            if ($totalInasistencias == 5) {
                Log::info("el aprendiz con el id {$apprentice->id} tiene 5 faltas discontinuas");
                $notification = Notification::create([
                    'user_id' => $apprentice->user->id,
                    'message' => 'El aprendiz con id ' . $apprentice->id . ' tiene 5 faltas discontinuas',
                    'type' => 'warning',
                ]);
                event(new NotificationEvent($notification));
                Log::info(json_encode($notification, JSON_PRETTY_PRINT));
            }
        }
    }
}
