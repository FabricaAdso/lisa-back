<?php

namespace App\Console\Commands;

use App\Events\NotificationEvent;
use App\Models\Aprobation;
use App\Models\Instructor;
use App\Models\Justification;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotifyInstructorJustificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'NotifyInstructorJustifications:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'noficar al instructor que tiene justificaciónes en estado pendiente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Lógica del comando
        Log::info("Comando para notificar al instructor que tiene justificaciones pendientes");
        $instructors = Instructor::with(['sessions.assistances.justifications' => function ($query) {
            $query->whereHas('aprobation', function ($q) {
                $q->where('state', 'Pendiente')
                    ->whereDate('updated_at', '>=', Carbon::yesterday());
            });
        }])->whereHas('sessions.assistances.justifications.aprobation', function ($q) {
            $q->where('state', 'Pendiente');
        })->get();

        foreach ($instructors as $instructor) {
            //filtrar solo justificaciones pendirntes en la coleccion cargada
            $justificationsPendientes = $instructor->sessions
                ->flatMap->assistances
                ->flatMap->justifications
                ->filter(fn($justification) => 
                $justification->aprobation &&
                $justification->aprobation->state === 'Pendiente' &&
                Carbon::parse($justification->aprobation->updated_at)->greaterThanOrEqualTo(Carbon::yesterday()));

            if ($justificationsPendientes->isNotEmpty()) {
                Log::info("El instructor con ID {$instructor->id} tiene justificaciones pendientes.");
                Log::info(json_encode($justificationsPendientes, JSON_PRETTY_PRINT));
                $notification = Notification::create([
                    'user_id' => $instructor->user_id,
                    'message' => 'Tienes justificaciones pendientes',
                    'type' => 'warning',
                ]);
                event(new NotificationEvent($notification));
                Log::info(json_encode($notification, JSON_PRETTY_PRINT));
            }
        }
    }
}
