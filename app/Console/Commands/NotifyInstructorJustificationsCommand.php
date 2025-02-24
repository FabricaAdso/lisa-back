<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    protected $description = 'noficar al instructor que el aprendiz subio justificación y esta pendiente de aprobación o rechazo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
    }
}
