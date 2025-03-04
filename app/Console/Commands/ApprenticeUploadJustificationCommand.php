<?php

namespace App\Console\Commands;

use App\Events\NotificationEvent;
use App\Mail\JustificationReminter;
use App\Models\Apprentice;
use App\Models\Aprobation;
use App\Models\Assistance;
use App\Models\Justification;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        // $assistances = Assistance::where('assistance', 0)
        //     ->where('updated_at', '>=', Carbon::now()->subDays(4))
        //     ->whereHas('justifications', function ($query) {
        //         $query->whereNull('file_url')->orWhere('file_url', ''); // Filtra solo justificaciones sin archivo
        //     })
        //     ->with(['apprentice.user', 'justifications' => function ($query) {
        //         $query->whereNull('file_url')->orWhere('file_url', ''); // Carga solo justificaciones sin archivo
        //     }])
        //     ->get();

        $assistances =  Assistance::where('assistance', 0)
            ->where('updated_at', '>=', Carbon::now()->subDays(4))
            ->whereHas('justifications', function ($q) {
                $q->whereNull('file_url')->orWhere('file_url', '');
            })->with(['apprentice.user', 'justifications' => function ($q) {
                $q->whereNull('file_url')->orWhere('file_url', '')->orWhere('file_url', null);
            }])->get();
        foreach ($assistances as $assistance) {
            if (!$assistance->apprentice || !$assistance->apprentice->user) {
                Log::warning("Asistencia ID {$assistance->id} no tiene un aprendiz o usuario asociado.");
                continue;
            }

            $user = $assistance->apprentice->user;
            Log::info(json_encode($user, JSON_PRETTY_PRINT));
            // Crear notificaciónes
            $this->sendNotifications($user, $assistance);
            // Enviar correo electrónico
            $this->sendEmails($user, $assistance);

            Log::info("Comando ejecutado correctamente.");
        }
    }

    protected function sendNotifications(User $user, Assistance $assistance)
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'message' => 'Sube tu justificación para la asistencia del ' . $assistance->updated_at->format('d/m/Y'),
            'type' => 'warning',
        ]);
        event(new NotificationEvent($notification));
    }

    protected function sendEmails(User $user, Assistance $assistance)
    {
        $response = Mail::to($user->email)->send(new JustificationReminter($user, $assistance));
        Log::info("Correo electrónico enviado a {$user->email} con respuesta: " . json_encode($response, JSON_PRETTY_PRINT));
    }
}
