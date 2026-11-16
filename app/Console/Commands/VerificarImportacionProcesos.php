<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class VerificarImportacionProcesos extends Command
{
    protected $signature = 'admision:verificar-importacion-procesos';

    protected $description = 'Verifica conteos y vínculos por proceso en db_sistema_admision sin modificar datos.';

    private const PROCESOS = ['2024-II', '2025-I', '2026-I', '2027-I'];

    public function handle(): int
    {
        try {
            $db = DB::connection('mysql_unificada');
            $rows = [];
            $hasErrors = false;

            foreach (self::PROCESOS as $codigo) {
                $process = $db->table('procesos_admision')->where('codigo', $codigo)->first(['id', 'estado']);
                if (! $process) {
                    $rows[] = [$codigo, 'FALTA', 0, 0, 0, 0, 0, 0, 0, 'sin proceso'];
                    $hasErrors = true;
                    continue;
                }

                $processId = (int) $process->id;
                $offers = $db->table('programas_procesos')->where('proceso_admision_id', $processId)->count();
                $preRegistrations = $db->table('pre_inscripcions')->where('proceso_admision_id', $processId)->count();
                $vouchers = $db->table('vouchers')->where('proceso_admision_id', $processId)->count();
                $registrations = $db->table('inscripcions')->where('proceso_admision_id', $processId)->count();
                $documents = $db->table('documentos')->where('proceso_admision_id', $processId)->count();
                $grades = $db->table('notas')
                    ->join('inscripcions', 'inscripcions.id', '=', 'notas.inscripcion_id')
                    ->where('inscripcions.proceso_admision_id', $processId)
                    ->count();
                $emails = $db->table('correos')
                    ->whereIn('inscripcion_id', $db->table('inscripcions')->select('id')->where('proceso_admision_id', $processId))
                    ->orWhereIn('pre_inscripcion_id', $db->table('pre_inscripcions')->select('id')->where('proceso_admision_id', $processId))
                    ->count();

                $brokenInscriptionLinks = $db->table('inscripcions as i')
                    ->where('i.proceso_admision_id', $processId)
                    ->where(function ($query) use ($processId) {
                        $query->whereNotExists(function ($subquery) use ($processId) {
                            $subquery->selectRaw('1')->from('postulantes as p')->whereColumn('p.id', 'i.postulante_id');
                        })->orWhereNotExists(function ($subquery) use ($processId) {
                            $subquery->selectRaw('1')->from('programas_procesos as pp')
                                ->whereColumn('pp.programa_id', 'i.programa_id')
                                ->where('pp.proceso_admision_id', $processId);
                        })->orWhereNotExists(function ($subquery) use ($processId) {
                            $subquery->selectRaw('1')->from('vouchers as v')
                                ->whereColumn('v.id', 'i.voucher_id')
                                ->where('v.proceso_admision_id', $processId);
                        });
                    })
                    ->count();

                $brokenDocumentLinks = $db->table('documentos as d')
                    ->where('d.proceso_admision_id', $processId)
                    ->whereNotExists(function ($query) {
                        $query->selectRaw('1')->from('postulantes as p')->whereColumn('p.id', 'd.postulante_id');
                    })
                    ->count();

                $status = $process->estado;
                if ($codigo !== '2027-I' && ($offers === 0 || $registrations === 0 || $brokenInscriptionLinks > 0 || $brokenDocumentLinks > 0)) {
                    $hasErrors = true;
                }
                if ($codigo === '2024-II' && $db->table('inscripcions')->where('proceso_admision_id', $processId)->where('estado', '<>', 1)->exists()) {
                    $hasErrors = true;
                }
                if ($codigo === '2027-I' && ($status !== 'preparacion' || $registrations > 0 || $preRegistrations > 0)) {
                    $hasErrors = true;
                }

                $rows[] = [
                    $codigo,
                    $status,
                    $offers,
                    $preRegistrations,
                    $vouchers,
                    $registrations,
                    $documents,
                    $grades,
                    $emails,
                    "inscr. rotas={$brokenInscriptionLinks}; docs rotos={$brokenDocumentLinks}",
                ];
            }

            $this->table(
                ['Proceso', 'Estado', 'Programas', 'Preinscr.', 'Vouchers', 'Inscr.', 'Docs', 'Notas', 'Correos', 'Referencias'],
                $rows
            );

            $users = $db->table('users')->count();
            $people = $db->table('postulantes')->count();
            $globalRoles = $db->table('role_user')->count();
            $scopedRoles = $db->table('proceso_admision_user_roles')->count();
            $this->line("Usuarios globales: {$users}; personas por DNI: {$people}; roles globales: {$globalRoles}; asignaciones por proceso: {$scopedRoles}.");
            $this->comment('Verificación de solo lectura.');

            return $hasErrors ? self::FAILURE : self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('No se pudo completar la verificación: ' . $exception->getMessage());
            return self::FAILURE;
        }
    }
}
