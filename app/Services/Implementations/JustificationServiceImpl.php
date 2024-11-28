<?php

namespace App\Services\Implementations;

use App\Models\Assistance;
use App\Models\Justification;
use App\Services\JustificationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class JustificationServiceImpl implements JustificationService
{
    public function createJustification($request)
    {
        $request->validate([
            'assistance_id' => 'required|exists:assistances,id',
            'file' => 'required|mimes:pdf|max:2048',
            'description' => 'nullable|string',
        ]);

        $assistance = Assistance::with('session')->findOrFail($request->assistance_id);

        $assistanceDate = $assistance->session->date ?? null;
        $startJustificationDate = Carbon::parse($assistanceDate);
        $endJustificationDate = Carbon::now();

        // Calcular los días hábiles
        $diasHabiles = 0;
        $periodo = CarbonPeriod::create($startJustificationDate, $endJustificationDate);
        foreach($periodo as $date){
            if(!$date->isWeekend()){
                $diasHabiles++;
            }
        }

        // Validar si el plazo ha terminado
        if($diasHabiles > 3){
            return [
                'message' => 'No se puede crear justificación, ya que el plazo terminó'
            ];
        }

        // Subir el archivo si está presente
        $fileUrl = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = "pdf_" . time() . "." . $file->guessExtension();
            $filePath = $file->storeAs('public/files', $fileName); // Guarda el archivo en 'storage/app/public/files'
            $fileUrl = Storage::url($filePath);  // Obtiene la URL pública del archivo
        }

        // Buscar la justificación existente
        $justification = Justification::where('assistance_id', $request->assistance_id)->first();

        // Si la justificación existe, la actualizamos
        if ($justification) {
            $justification->update([
                'file_url' => $fileUrl ?? $justification->file_url,  // Solo actualizar si hay un nuevo archivo
                'description' => $request->description,
            ]);

            return [
                'message' => 'Justificación actualizada con éxito',
                'justification' => $justification
            ];
        } else {
            return [
                'message' => 'No se encontró la justificación para esta asistencia'
            ];
        }
    }
}
