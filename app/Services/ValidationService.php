<?php

namespace App\Services;

use App\Jobs\SendEmailValidarInscripcionJob;
use App\Repositories\Contracts\InscripcionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidationService
{
    public function __construct(
        protected InscripcionRepositoryInterface $inscripcionRepository
    ) {}

    /**
     * Validar digitalmente una inscripción
     */
    public function validateDigital(int $inscripcionId, int $tipoVal, ?string $observacion = null): array
    {
        $inscripcion = $this->inscripcionRepository->findWithRelations($inscripcionId);

        if (!$inscripcion) {
            throw new \Exception("La inscripción con ID {$inscripcionId} no existe");
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();

            if ($tipoVal == 2) { // Observación
                $inscripcion->update([
                    'val_digital' => 2,
                    'observacion' => $observacion,
                ]);

                $this->logActivity($user, $inscripcion, 2, $observacion, 'Inscripción observada');

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'La inscripción ha sido observada correctamente',
                ];
            } else if ($tipoVal == 1) { // Validación exitosa
                $inscripcion->update([
                    'val_digital' => 1,
                    'observacion' => 'Validación digital exitosa',
                ]);

                $this->logActivity($user, $inscripcion, 1, null, 'Inscripción validada digitalmente');

                // Preparar datos para correo
                $datosCorreo = $this->getDatosCorreo($inscripcion);
                
                $this->logActivity(
                    $user, 
                    $inscripcion, 
                    1, 
                    null, 
                    'Correo de validación digital y constancia de postulante enviado',
                    ['correo_enviado' => 'Inscripción validada y constancia de postulante']
                );

                DB::commit();

                // Enviar correo
                SendEmailValidarInscripcionJob::dispatch(
                    $inscripcion->postulante->email,
                    $inscripcion,
                    $datosCorreo['autoridad'],
                    $datosCorreo['gradoRequerido'],
                    $datosCorreo['urlDocumentos']
                );

                return [
                    'success' => true,
                    'message' => 'Validación digital exitosa y correo enviado',
                ];
            } else {
                throw new \Exception('Tipo de validación no válido');
            }
        } catch (\Exception $e) {
            Log::error("ValidationService Error: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validar físicamente una inscripción
     */
    public function validateFisica(int $inscripcionId): array
    {
        $inscripcion = $this->inscripcionRepository->findWithRelations($inscripcionId);

        if (!$inscripcion) {
            throw new \Exception("La inscripción con ID {$inscripcionId} no existe");
        }

        if ($inscripcion->val_digital == 0) {
            return [
                'success' => false,
                'message' => 'La inscripción aún no ha sido validada digitalmente',
            ];
        }

        if ($inscripcion->val_digital == 2) {
            return [
                'success' => false,
                'message' => 'La inscripción tiene una observación sin resolver',
            ];
        }

        if ($inscripcion->val_fisico) {
            return [
                'success' => true,
                'message' => 'La inscripción ya ha sido validada físicamente',
            ];
        }

        try {
            $inscripcion->update(['val_fisico' => 1]);

            $this->logActivity(
                auth()->user(), 
                $inscripcion, 
                'fisica', 
                null, 
                'Inscripción validada físicamente'
            );

            return [
                'success' => true,
                'message' => 'Validación física exitosa',
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al validar la inscripción: ' . $e->getMessage());
        }
    }

    /**
     * Obtener datos para el correo según el programa
     */
    private function getDatosCorreo($inscripcion): array
    {
        $gradoId = $inscripcion->programa->grado_id;
        $facultadId = $inscripcion->programa->facultad_id;

        $autoridad = config("admission.autoridades.{$gradoId}", 'Autoridad');
        $gradoRequerido = config("admission.grados_requeridos.{$gradoId}", '');
        
        $urlDocumentos = '';
        if ($gradoId == 3) { // Segunda Especialidad
            $urlDocumentos = config("admission.url_documentos.facultades.{$facultadId}", '');
        } else {
            $urlDocumentos = config("admission.url_documentos.default");
        }

        return [
            'autoridad' => $autoridad,
            'gradoRequerido' => $gradoRequerido,
            'urlDocumentos' => $urlDocumentos,
        ];
    }

    /**
     * Registrar actividad en log
     */
    private function logActivity($user, $inscripcion, $tipoVal, $observacion, $logMessage, $extraProps = [])
    {
        if (!$inscripcion->postulante) {
            Log::warning("Inscripcion {$inscripcion->id} has no postulante. Skipping activity log.");
            return;
        }

        $properties = array_merge([
            'subject' => [
                'nombres' => $inscripcion->postulante->nombres,
                'ap_paterno' => $inscripcion->postulante->ap_paterno,
                'ap_materno' => $inscripcion->postulante->ap_materno,
                'num_iden' => $inscripcion->postulante->num_iden,
                'tipo_doc' => $inscripcion->postulante->tipo_doc,
            ],
            'tipo_validacion' => $tipoVal,
            'inscripcion_id' => $inscripcion->id,
        ], $extraProps);

        if ($observacion) {
            $properties['observacion'] = $observacion;
        }

        if (function_exists('activity')) {
             activity()
                ->causedBy($user)
                ->performedOn($inscripcion)
                ->withProperties($properties)
                ->log($logMessage);
        } else {
            Log::warning("Activity logger function not found.");
        }
       
    }
}
