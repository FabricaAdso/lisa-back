<?php

namespace App\Console\Commands;

use App\Events\NotificationEvent;
use App\Models\Apprentice;
use App\Models\Aprobation;
use App\Models\Assistance;
use App\Models\Justification;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ApprenticeUploadJustificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ApprenticeUploadJustification:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para notificar al aprendiz que suba su justificación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Comando para notificar al aprendiz que suba su justificación");
        // Lógica del comando
        $assistances = Assistance::where('assistance', 0)
            ->where('updated_at', '>=', Carbon::now()->subDays(4))
            ->with(['apprentice.user', 'justifications'])
            ->get();
        foreach ($assistances as $assistance) {
            foreach ($assistance->justifications as $justification) {
                //validar si file_ulr es diferente de null o vacio
                if (empty($justification->file_url)) {
                    Log::info("La justificación para la asistencia con ID " . $assistance->id . " no tiene archivo.");
                    Log::info(json_encode($assistance, JSON_PRETTY_PRINT));
                    $notification = Notification::create([
                        'user_id' => $assistance->apprentice->user->id,
                        'message' => 'Sube tu justificacion para la asistencia de la fecha ' . $assistance->updated_at,
                        'type' => 'warning',
                    ]);
                    event(new NotificationEvent($notification));
                    Log::info(json_encode($notification, JSON_PRETTY_PRINT));
                }
            }
        }
    }
}
