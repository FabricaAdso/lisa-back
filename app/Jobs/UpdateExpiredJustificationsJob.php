<?php

namespace App\Jobs;

use App\Models\Justification;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class UpdateExpiredJustificationsJob implements ShouldQueue
{
    use Queueable;
    /**
     * Ejecuta el job.
     */
    public function handle()
    {
        // Obtener todas las justificaciones pendientes
        $justifications = Justification::with('aprobation')
            ->whereHas('aprobation', function ($query) {
                $query->where('state', 'Pendiente');
            })
            ->get();

        foreach ($justifications as $justification) {
            $assistanceDate = $justification->assistance->updated_at;

            if (!$assistanceDate) {
                continue;
            }

            $startJustificationDate = Carbon::parse($assistanceDate);
            $endJustificationDate = Carbon::now();

            $diasHabiles = 0;
            $periodo = CarbonPeriod::create($startJustificationDate, $endJustificationDate);

            foreach ($periodo as $date) {
                if (!$date->isSunday() && !$this->isHoliday($date)) {
                    $diasHabiles++;
                }
            }

            if ($diasHabiles > 3 && $justification->file_url === null) {
                // Actualiza el estado de la aprobación a "Vencida"
                $justification->aprobation->update(['state' => 'Vencida']);
            }
        }
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
