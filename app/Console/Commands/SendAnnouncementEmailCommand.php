<?php

namespace App\Console\Commands;

use App\Models\Postulante;
use App\Mail\ReprogramacionExamenEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAnnouncementEmailCommand extends Command
{
    protected $signature = 'mail:send-announcement';
    protected $description = 'Send exam suspension announcement to all applicants';

    public function handle()
    {
        $count = Postulante::whereNotNull('email')->count();
        $this->info("Sending emails to {$count} applicants...");
        
        $sentCount = 0;
        Postulante::whereNotNull('email')->chunk(100, function ($postulantes) use (&$sentCount) {
            foreach ($postulantes as $postulante) {
                Mail::to($postulante->email)->queue(new ReprogramacionExamenEmail($postulante));
                $sentCount++;
            }
        });

        $this->info("Successfully queued {$sentCount} emails.");
    }
}
