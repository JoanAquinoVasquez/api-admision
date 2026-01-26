<?php

namespace App\Services;

use App\Repositories\Contracts\PreInscripcionRepositoryInterface;

class PreInscripcionService
{
    public function __construct(
        protected PreInscripcionRepositoryInterface $repository,
        protected PreInscripcionReportService $reportService,
        protected PreInscripcionStatisticsService $statisticsService
    ) {
    }

    /**
     * Get all pre-inscripciones with payment status
     */
    public function getAllWithPaymentStatus()
    {
        return $this->repository->getAllWithPaymentStatus();
    }

    /**
     * Get summary of pre-inscripciones
     */
    public function getResumen(): array
    {
        return $this->statisticsService->getResumen();
    }

    /**
     * Get table summary
     */
    public function getResumenTabla(): array
    {
        return $this->statisticsService->getResumenTabla();
    }

    /**
     * Create new pre-inscripcion
     */
    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * Find pre-inscripcion by num_iden
     */
    public function findByNumIden(string $numIden)
    {
        return $this->repository->findByNumIden($numIden);
    }

    /**
     * Update pre-inscripcion
     */
    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Deactivate pre-inscripcion
     */
    public function deactivate(int $id): bool
    {
        return $this->repository->deactivate($id);
    }

    /**
     * Check if pre-inscrito exists
     */
    public function checkPreInscrito(string $numIden): array
    {
        $preInscripcion = $this->repository->findByNumIden($numIden);

        if ($preInscripcion) {
            return [
                'exists' => true,
                'message' => "La pre-inscripción con número de identidad: {$numIden} ya existe",
                'data' => [
                    'ap_paterno' => $preInscripcion->ap_paterno,
                    'ap_materno' => $preInscripcion->ap_materno,
                    'nombres' => $preInscripcion->nombres,
                ]
            ];
        }

        return [
            'exists' => false,
            'message' => "La pre-inscripción con número de identidad: {$numIden} no existe",
            'data' => []
        ];
    }

    /**
     * Get general summary for comision
     */
    public function getResumenGeneral(): array
    {
        return $this->statisticsService->getResumenGeneral();
    }

    /**
     * Generate Excel report
     */
    public function generateReport(int $gradoId, int $programaId)
    {
        return $this->reportService->generatePreInscripcionReport($gradoId, $programaId);
    }

    /**
     * Generate daily report
     */
    public function generateDailyReport()
    {
        return $this->reportService->generateDailyReport();
    }

    /**
     * Generate daily faculty report
     */
    public function generateDailyFacultyReport()
    {
        return $this->reportService->generateDailyFacultyReport();
    }
}
