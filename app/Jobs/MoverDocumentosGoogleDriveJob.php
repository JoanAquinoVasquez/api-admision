<?php

namespace App\Jobs;

use App\Models\Inscripcion;
use App\Models\Postulante;
use App\Models\Programa;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MoverDocumentosGoogleDriveJob implements ShouldQueue
{
    use Queueable;

    protected $postulanteId;
    protected $gradoId;
    protected $filesEnviados;

    public function __construct($postulanteId, $gradoId, array $filesEnviados)
    {
        $this->postulanteId = $postulanteId;
        $this->gradoId = $gradoId;
        $this->filesEnviados = $filesEnviados;
    }

    public function handle(GoogleDriveService $driveService)
    {
        $driveService->moverDocumentos(
            $this->postulanteId,
            $this->gradoId,
            $this->filesEnviados
        );
    }
}
