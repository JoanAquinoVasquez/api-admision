<?php

namespace App\Console\Commands;

use App\Mail\AdmisionAccessEmail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAdmissionAccessMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-admission-access {--test= : Send a test email to this address} {--all : Send emails to all admission committee members}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Send access notification emails to the admission committee';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testEmail = $this->option('test');
        $sendAll = $this->option('all');

        if (!$testEmail && !$sendAll) {
            $this->error('You must specify either --test={email} or --all');
            return;
        }

        if ($testEmail) {
            $this->info("Sending test email to: $testEmail");

            // Create a mock user for the test
            $user = new User();
            $user->name = 'Usuario de Prueba';
            $user->email = $testEmail;

            Mail::to($testEmail)->send(new AdmisionAccessEmail($user));

            $this->info('Test email sent successfully!');
            return;
        }

        if ($sendAll) {
            $users = User::whereHas('roles', function ($query) {
                $query->where('slug', 'comision');
            })->get();

            if ($users->isEmpty()) {
                $this->warn('No users found with the "comision" role.');
                return;
            }

            $count = $users->count();
            if (!$this->confirm("Are you sure you want to send emails to $count users?")) {
                $this->info('Operation cancelled.');
                return;
            }

            $this->output->progressStart($count);

            foreach ($users as $user) {
                try {
                    Mail::to($user->email)->send(new AdmisionAccessEmail($user));
                } catch (\Exception $e) {
                    $this->error("\nFailed to send email to $user->email: " . $e->getMessage());
                }
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $this->info('All emails have been sent.');
        }
    }
}
