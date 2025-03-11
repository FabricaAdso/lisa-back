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
use Illuminate\Support\Facades\Log;

class JustificationServiceImpl implements JustificationService
{
    protected $festivos;

    //jobs
    public function checkAndUpdateExpiredJustifications() {}

    public function editJustification($request)
    {
        $request->validate([
            'assistance_id' => 'required|exists:assistances,id',
            'file' => 'required|mimes:pdf|max:2048',
            'description' => 'nullable|string',
        ]);
        $assistance = Assistance::findOrFail($request->assistance_id);
        $justification = Justification::where('assistance_id', $assistance->id)->first();


        $fileUrl = null;
        if (!empty($justification->file_url)) {
            return [
                'message' => 'Ya existe un archivo asociado a esta justificación, no se puede cargar uno nuevo.'
            ];
        }
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = "pdf_" . time() . "." . $file->guessExtension();
                $filePath = $file->storeAs('files', $fileName, 'public');
                $fileUrl = url('storage/' . $filePath);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al cargar el archivo',
                'error' => $e->getMessage(),
            ], 500);
        }

        if ($justification) {
            $justification->update([
                'file_url' => $fileUrl ?? $justification->file_url,
                'description' => $request->description,
            ]);
            $this->stateJustification($justification);
            $justifications = Justification::included()->findOrFail($justification->id);
            return $justifications;
        }
    }

    public function stateJustification($justification)
    {
        if ($justification->aprobation) {
            if ($justification->aprobation->state === 'Aprobada') {
                return ['message' => 'Justificación Aprobada'];
            } elseif ($justification->aprobation->state === 'Rechazada') {
                return ['message' => 'Justificación Rechazada'];
            } else if ($justification->aprobation->state === 'En_espera') {
                $justification->aprobation()->update(['state' => 'Pendiente']);
            } else if ($justification->aprobation->state === 'Vencida') {
                return ['message' => 'Justificación Vencida'];
            } else if ($justification->aprobation->state === 'Pendiente') {
                return ['message' => 'Justificación Pendiente'];
            }
        }
        return null;
    }
}
