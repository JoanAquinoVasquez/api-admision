<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendRecommendationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-recommendation-emails 
                            {--limit= : Limit the number of emails to send for testing}
                            {--test-email= : Send all emails to this address for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends personalized master recommendation emails to FACHSE graduates from Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path('../BACHILLERES-FACHSE.xlsx');
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $this->info("Reading Excel file...");
        $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $filePath);
        $rows = $data[0];
        
        // Remove header if it exists (assuming first row is header)
        $header = array_shift($rows);
        
        $this->info("Fetching FACHSE master programs...");
        $programasRaw = \App\Models\Programa::with('grado')
            ->where('facultad_id', 8) // FACHSE
            ->where('grado_id', 2) // MAESTRIA
            ->where('estado', 1)
            ->get();

        $programas = $programasRaw->map(function($p) {
            $gradoNombre = mb_convert_case($p->grado->nombre, MB_CASE_TITLE, "UTF-8");
            
            $brochure = $p->brochure;
            // Convert Google Drive view links to direct download links
            if ($brochure && str_contains($brochure, 'drive.google.com')) {
                if (preg_match('/\/d\/(.+)\//', $brochure, $matches)) {
                    $fileId = $matches[1];
                    $brochure = "https://drive.google.com/uc?export=download&id={$fileId}";
                }
            }

            return [
                'nombre' => "{$gradoNombre} en {$p->nombre}",
                'brochure' => $brochure
            ];
        })->toArray();

        if (empty($programas)) {
            $this->error("No master programs found for FACHSE.");
            return;
        }

        $limit = $this->option('limit');
        if ($limit) {
            $rows = array_slice($rows, 0, (int)$limit);
        }

        $total = count($rows);
        $this->info("Starting to send {$total} emails...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($rows as $row) {
            // Expected columns: [PRIM_APE, SEG_APE, NOMBRE, CORREO ELECTRONICO]
            $nombre = $row[2];
            $email = $this->option('test-email') ?? $row[3];

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Check if already registered
                $alreadyRegistered = \App\Models\Postulante::where('email', $email)
                    ->whereHas('inscripcion')
                    ->exists();

                if ($alreadyRegistered && !$this->option('test-email')) {
                    $this->warn("\nSkipping already registered: {$email}");
                    $bar->advance();
                    continue;
                }

                try {
                    \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\RecomendacionMaestriaEmail($nombre, $programas));
                } catch (\Exception $e) {
                    $this->error("\nFailed to send to {$email}: {$e->getMessage()}");
                }
            } else {
                $this->warn("\nInvalid email: {$email} for {$nombre}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nDone!");
    }
}
