<?php

namespace App\Services\Implementations;

use App\Jobs\UpdateExpiredJustificationsJob;
use App\Models\Aprobation;
use App\Models\Assistance;
use App\Models\Justification;
use App\Services\JustificationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Validation\Rules\Can;

class JustificationServiceImpl implements JustificationService
{
    protected $festivos;

    public function __construct()
    {
        $this->festivos = $this->calcularFestivos(Carbon::now()->year);
    }

    //jobs
    public function checkAndUpdateExpiredJustifications()
    {
        // php artisan queue:work
        UpdateExpiredJustificationsJob::dispatch();
        return [
            'message' => 'Job para actualizar justificaciones vencidas despachado correctamente',
        ];
    }

    public function createJustification($request)
    {
        $request->validate([
            'assistance_id' => 'required|exists:assistances,id',
            'file' => 'required|mimes:pdf',
            'description' => 'nullable|string',
        ]);

        $assistance = Assistance::included()->findOrFail($request->assistance_id);
        $justification = Justification::where('assistance_id', $request->assistance_id)->first();

        $assistanceDate = $assistance->updated_at;
        $startJustificationDate = Carbon::parse($assistanceDate);
        $endJustificationDate = Carbon::now();

        $diasHabiles = 0;
        $periodo = CarbonPeriod::create($startJustificationDate, $endJustificationDate);
        foreach ($periodo as $date) {
            if (!$date->isSunday() && !$this->isHoliday($date)) {
                $diasHabiles++;
            }
        }


        $this->stateJustification($diasHabiles, $justification);

        $fileUrl = null;
        if(!is_null($justification->file_url)){
            return [
                'message' => 'Ya existe un archivo asociado a esta justificación, no se puede cargar uno nuevo.'
            ];
        }
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = "pdf_" . time() . "." . $file->guessExtension();
            $filePath = $file->storeAs('files', $fileName, 'public');     
            $fileUrl = url('storage/' . $filePath);
        }

        if ($justification) {
            $justification->update([
                'file_url' => $fileUrl ?? $justification->file_url,
                'description' => $request->description,
            ]);
            $justifications = Justification::included()->findOrFail($justification->id);
            return response()->json($justification,202);
        }
    }

    public function stateJustification($diasHabiles, $justification)
    {
        if ($diasHabiles > 3) {
            if ($justification->file_url === null) {
                if ($justification->aprobation) {
                }
                return [
                    'message' => 'No puedes cargar una justificación, ya que el plazo terminó',
                ];
            } elseif ($justification->file_url !== null) {
                if ($justification->aprobation) {
                }
                return [
                    'message' => 'Ya subiste una justificación, no puedes subir otra',
                ];
            }
        } elseif ($diasHabiles < 3) {
            if ($justification->file_url !== null) {
                if ($justification->aprobation) {
                    if ($justification->aprobation->state === 'Aprobada') {
                        return [
                            'message' => 'Justificación Aprobada'
                        ];
                    } elseif ($justification->aprobation->state === 'Rechazada') {
                        return [
                            'message' => 'Justificación Rechazada'
                        ];
                    } else {
                        $justification->aprobation->update(['state' => 'Pendiente']);
                        return [
                            'message' => 'Ya subiste una justificación, no puedes subir otra',
                        ];
                    }
                } else {
                    return [
                        'message' => 'No se ha cargado ninguna justificación'
                    ];
                }
            }

            return null;
        }
    }

    private function isHoliday(Carbon $date): bool
    {
        return in_array($date->toDateString(), $this->festivos);
    }

    ///Festivos
    private function calcularFestivos($year): array
    {
        // Festivos fijos
        $festivosFijos = [
            Carbon::create($year, 1, 1)->toDateString(),   // Año Nuevo
            Carbon::create($year, 5, 1)->toDateString(),   // Día del Trabajo
            Carbon::create($year, 7, 20)->toDateString(),  // Independencia de Colombia
            Carbon::create($year, 8, 7)->toDateString(),   // Batalla de Boyacá
            Carbon::create($year, 12, 25)->toDateString(), // Navidad
        ];

        // Festivos móviles
        $festivosMoviles = [
            $this->calcularFestivoMovil($year, 1, 6),   // Reyes Magos
            $this->calcularFestivoMovil($year, 3, 19),  // Día de San José
            $this->calcularFestivoMovil($year, 6, 29),  // San Pedro y San Pablo
            $this->calcularFestivoMovil($year, 8, 15),  // Asunción de la Virgen
            $this->calcularFestivoMovil($year, 10, 12), // Día de la Diversidad Étnica y Cultural
            $this->calcularFestivoMovil($year, 11, 1),  // Todos los Santos
            $this->calcularFestivoMovil($year, 11, 11), // Independencia de Cartagena
        ];

        return array_merge($festivosFijos, $festivosMoviles);
    }

    private function calcularFestivoMovil($year, $mont, $day): string
    {
        $fecha = Carbon::create($year, $mont, $day);
        if ($fecha->isSunday()) {
            return $fecha->toDateString();
        }
        return $fecha->next(Carbon::MONDAY)->toDateString();
    }
}
