<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Justification;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateExpiredJustificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateExpiredJustification:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'comenzando a verificar las justificaciones en base a su tiempo de vencimiento';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        // Obtener todas las justificaciones con estado de aprobation de En_espera
        $justifications = Justification::with('aprobation')
            ->whereHas('aprobation', function ($query) {
                $query->where('state', 'En_espera');
            })
            ->get();

        Log::info('Iniciando el proceso de verificación de justificaciones vencidas');
        foreach ($justifications as $justification) {
            Log::info("Justificación: {$justification->id}");

            $assistanceDate = $justification->assistance->updated_at;

            if (!$assistanceDate) {
                Log::error("No se ha encontrado fecha de actualización para la asistencia {$justification->assistance->id}");
                continue;
            }

            $startJustificationDate = Carbon::parse($assistanceDate);
            $endJustificationDate = Carbon::now();

            Log::info("Período de vencimiento: {$startJustificationDate->format('d/m/Y')} - {$endJustificationDate->format('d/m/Y')}");

            $diasHabiles = 0;
            $periodo = CarbonPeriod::create($startJustificationDate, $endJustificationDate);

            foreach ($periodo as $date) {
                if (!$date->isSunday() && !$this->isHoliday($date)) {
                    $diasHabiles++;
                }
            }

            Log::info("Días hábiles: {$diasHabiles}");

            if ($diasHabiles > 3 && $justification->aprobation->state == 'En_espera') {
                // Actualiza el estado de la aprobación a "Vencida"
                $justification->aprobation->update(['state' => 'Vencida']);

                Log::info("Se ha actualizado el estado de la aprobación a 'Vencida' para la justificación {$justification->id}");
            }
        }

        Log::info('Finalizando el proceso de verificación de justificaciones vencidas');
    }

    private function isHoliday(Carbon $date): bool
    {
        $festivos = $this->calcularFestivos($date->year);
        return in_array($date->toDateString(), $festivos);
    }

    private function calcularFestivos($year): array
    {
        return [
            Carbon::create($year, 1, 1)->toDateString(),   // Año Nuevo
            Carbon::create($year, 5, 1)->toDateString(),   // Día del Trabajo
            Carbon::create($year, 7, 20)->toDateString(),  // Independencia de Colombia
            Carbon::create($year, 8, 7)->toDateString(),   // Batalla de Boyacá
            Carbon::create($year, 12, 25)->toDateString(), // Navidad
        ];
    }
}
