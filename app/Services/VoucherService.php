<?php

namespace App\Services;

use App\Models\ConceptoPago;
use App\Models\Postulante;
use App\Models\Programa;
use App\Models\Voucher;
use App\Repositories\Contracts\VoucherRepositoryInterface;
use App\Services\DniService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class VoucherService
{
    public function __construct(
        protected VoucherRepositoryInterface $voucherRepository
    ) {
    }

    /**
     * Find voucher based on voucher code length (6 for PY, 7 for BN)
     */
    public function findVoucher(string $codVoucher, string $numIden): ?Model
    {
        // Si tiene 6 dígitos, asumimos PagaloPe (PY) y buscamos por los últimos 6 dígitos
        if (strlen($codVoucher) === 6) {
            return $this->voucherRepository->findByLast6DigitsAndNumIden($codVoucher, $numIden);
        }

        // Si no, asumimos Banco de la Nación (BN) y buscamos coincidencia exacta
        return $this->voucherRepository->findByNumeroAndNumIden($codVoucher, $numIden);
    }

    /**
     * Process voucher usage
     */
    public function processVoucherUsage(Model $voucher, string $numIden): void
    {
        // Si el voucher principal es de concepto 00000971 (Validación)
        // También debemos buscar y marcar como usado el voucher de concepto 00000970 (Carpeta)
        if ($voucher->conceptoPago->cod_concepto == '00000971') {
            // Buscamos el voucher de carpeta (00000970) para este postulante
            $voucherCarpeta = Voucher::where('num_iden', $numIden)
                ->whereHas('conceptoPago', function ($query) {
                    $query->where('cod_concepto', '00000970');
                })
                ->where('estado', true)
                ->first();

            if ($voucherCarpeta) {
                $this->voucherRepository->markAsUsed($voucherCarpeta->id);
            }
        }

        // Marcar voucher principal como usado
        $this->voucherRepository->markAsUsed($voucher->id);
    }

    /**
     * Process voucher TXT files from Banco de la Nación
     */
    public function processVoucherFiles(array $files): array
    {
        $allLines = [];
        $registrosExitosos = 0;

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $allLines = array_merge($allLines, $lines);
        }

        foreach ($allLines as $line) {
            $encoding = mb_detect_encoding($line, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding) {
                $line = mb_convert_encoding($line, 'UTF-8', $encoding);
            }

            $codPago = substr($line, 35, 8);
            $validCodes = ['00000012', '00001005', '00000971', '00000970'];

            if (in_array($codPago, $validCodes)) {
                $voucherData = $this->parseVoucherLine($line);
                if ($this->saveVoucher($voucherData)) {
                    $registrosExitosos++;
                }
            }
        }

        return [
            'success' => true,
            'message' => "Se procesaron correctamente {$registrosExitosos} vouchers.",
            'registros' => $registrosExitosos,
        ];
    }

    /**
     * Parse voucher line from TXT file
     */
    protected function parseVoucherLine(string $line): array
    {
        $nroVoucher = substr($line, 18, 7);
        $codPago = substr($line, 35, 8);
        $dni = substr($line, 54, 8);
        $montoSubstring = substr($line, 62, 15);
        $fecha = substr($line, 79, 8);
        $hora = substr($line, 87, 6);
        $cajero = substr($line, 93, 4);
        $agencia = substr($line, 97, 4);
        $nombre = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', trim(substr($line, 121, 35)));
        $monto = floatval(substr($montoSubstring, 0, 13)) + (floatval(substr($montoSubstring, 13, 2)) / 100);

        // BN TXT format is usually YYYYMMDD
        try {
            $fechaFormateada = Carbon::createFromFormat('Ymd', $fecha)->format('Y-m-d');
        } catch (\Exception $e) {
            // Fallback if it's actually DDMMYYYY
            try {
                $fechaFormateada = Carbon::createFromFormat('dmY', $fecha)->format('Y-m-d');
            } catch (\Exception $e2) {
                Log::error("Error parsing date: $fecha. Line: $line");
                $fechaFormateada = now()->format('Y-m-d');
            }
        }

        $horaFormateada = substr($hora, 0, 2) . ':' . substr($hora, 2, 2) . ':' . substr($hora, 4, 2);

        $conceptoPago = ConceptoPago::where('cod_concepto', $codPago)->first();

        return [
            'numero' => $nroVoucher,
            'cod_concepto' => $codPago,
            'concepto_pago_id' => $conceptoPago ? $conceptoPago->id : null,
            'num_iden' => $dni,
            'nombre_completo' => $nombre ?: $dni, // Fallback to DNI if name is empty
            'fecha_pago' => $fechaFormateada,
            'hora_pago' => $horaFormateada,
            'cajero' => $cajero,
            'agencia' => $agencia,
            'monto' => $monto,
        ];
    }

    /**
     * Save voucher to database
     */
    protected function saveVoucher(array $voucherData): bool
    {
        if (!$voucherData['concepto_pago_id']) {
            Log::warning("Skipping voucher save: concepto_pago_id is null for cod_concepto: " . ($voucherData['cod_concepto'] ?? 'unknown'));
            return false;
        }

        $exists = Voucher::where('numero', $voucherData['numero'])
            ->where('num_iden', $voucherData['num_iden'])
            ->exists();

        if ($exists) {
            return false;
        }

        // Remove cod_concepto as it's not in the vouchers table directly, we use concepto_pago_id
        $dataToSave = $voucherData;
        unset($dataToSave['cod_concepto']);

        Voucher::create($dataToSave);
        return true;
    }

    /**
     * Validate voucher and get available programs
     */
    public function validateVoucher(string $numIden, string $codVoucher, string $agencia, string $fechaPago, DniService $dniService): array
    {
        $query = Voucher::with('conceptoPago')
            ->where('num_iden', $numIden)
            ->where('agencia', $agencia)
            ->whereDate('fecha_pago', $fechaPago);

        if (strlen($codVoucher) === 6) {
            $query->whereRaw('RIGHT(numero, 6) = ?', [$codVoucher]);
        } else {
            $query->where('numero', $codVoucher);
        }

        // Solo permitir validar los de concepto 00000971 para iniciar inscripción
        $query->whereHas('conceptoPago', function ($q) {
            $q->where('cod_concepto', '00000971');
        });

        $voucher = $query->first();

        if (!$voucher) {
            // Buscamos si el voucher existe pero es de otro concepto (por ejemplo el 970)
            $existsOther = Voucher::where('num_iden', $numIden)
                ->where(function ($q) use ($codVoucher) {
                    if (strlen($codVoucher) === 6) {
                        $q->whereRaw('RIGHT(numero, 6) = ?', [$codVoucher]);
                    } else {
                        $q->where('numero', $codVoucher);
                    }
                })->first();

            if ($existsOther && $existsOther->conceptoPago->cod_concepto == '00000970') {
                return [
                    'success' => false,
                    'message' => 'Este voucher corresponde al concepto de CARPETA. Para inscribirse debe ingresar el voucher de concepto INSCRIPCIÓN (00000971).',
                ];
            }

            return [
                'success' => false,
                'message' => 'No encontramos su pago de INSCRIPCIÓN (00000971). Verifique los datos o espere 24 horas si recién realizó el pago.',
            ];
        }

        // REQUISITO CRÍTICO: Debe tener también el voucher de CARPETA (0970)
        $hasCarpeta = Voucher::where('num_iden', $numIden)
            ->whereHas('conceptoPago', function ($q) {
                $q->where('cod_concepto', '00000970');
            })
            ->where('estado', true)
            ->exists();

        if (!$hasCarpeta) {
            return [
                'success' => false,
                'message' => 'Para inscribirse debe contar con ambos pagos realizados (CARPETA e INSCRIPCIÓN). No hemos detectado su pago de CARPETA (00000970) en nuestro sistema.',
            ];
        }

        if ($voucher->estado == 0) {
            return [
                'success' => false,
                'message' => 'Su voucher ya fue utilizado.',
            ];
        }

        // Verificar si el postulante ya tiene una inscripción registrada
        $postulanteInscrito = Postulante::where('num_iden', $numIden)->has('inscripcion')->exists();
        if ($postulanteInscrito) {
            return [
                'success' => false,
                'message' => 'El postulante ya se encuentra inscrito en el proceso de admisión.',
            ];
        }

        $postulanteData = $this->getPostulanteData($numIden, $dniService);
        $programas = $this->getProgramasByConcepto($voucher->conceptoPago->cod_concepto);

        return [
            'success' => true,
            'voucher' => $voucher,
            'postulante' => $postulanteData,
            'programas' => $programas,
            'grado_id' => $postulanteData['grado_id'] ?? null,
            'programa_id' => $postulanteData['programa_id'] ?? null,
            'distrito_id' => $postulanteData['distrito_id'] ?? null,
            'provincia_id' => $postulanteData['provincia_id'] ?? null,
            'departamento_id' => $postulanteData['departamento_id'] ?? null,
        ];
    }

    /**
     * Get postulante data
     */
    protected function getPostulanteData(string $numIden, DniService $dniService): ?array
    {
        $postulante = Postulante::with(['preInscripcion.programa', 'distrito.provincia.departamento'])->where('num_iden', $numIden)->first();

        if ($postulante) {
            $data = [
                'num_iden' => $postulante->num_iden,
                'nombres' => $postulante->nombres,
                'ap_paterno' => $postulante->ap_paterno,
                'ap_materno' => $postulante->ap_materno,
                'email' => $postulante->email,
                'celular' => $postulante->celular,
                'distrito_id' => $postulante->distrito_id,
                'distrito_nombre' => $postulante->distrito->nombre ?? null,
                'provincia_id' => $postulante->distrito->provincia_id ?? null,
                'provincia_nombre' => $postulante->distrito->provincia->nombre ?? null,
                'departamento_id' => $postulante->distrito->provincia->departamento_id ?? null,
                'departamento_nombre' => $postulante->distrito->provincia->departamento->nombre ?? null,
                'fecha_nacimiento' => $postulante->fecha_nacimiento,
                'sexo' => $postulante->sexo,
                'tipo_doc' => $postulante->tipo_doc,
                'direccion' => $postulante->direccion,
            ];

            if ($postulante->preInscripcion) {
                $data['grado_id'] = $postulante->preInscripcion->programa->grado_id ?? null;
                $data['programa_id'] = $postulante->preInscripcion->programa_id ?? null;
            }

            return $data;
        }

        // Fallback: Buscar en la tabla de PreInscripciones
        $preInscripcion = \App\Models\PreInscripcion::with(['programa', 'distrito.provincia.departamento'])->where('num_iden', $numIden)->first();
        if ($preInscripcion) {
            return [
                'num_iden' => $preInscripcion->num_iden,
                'tipo_doc' => $preInscripcion->tipo_doc,
                'nombres' => $preInscripcion->nombres,
                'ap_paterno' => $preInscripcion->ap_paterno,
                'ap_materno' => $preInscripcion->ap_materno,
                'email' => $preInscripcion->email,
                'celular' => $preInscripcion->celular,
                'distrito_id' => $preInscripcion->distrito_id,
                'distrito_nombre' => $preInscripcion->distrito->nombre ?? null,
                'provincia_id' => $preInscripcion->distrito->provincia_id ?? null,
                'provincia_nombre' => $preInscripcion->distrito->provincia->nombre ?? null,
                'departamento_id' => $preInscripcion->distrito->provincia->departamento_id ?? null,
                'departamento_nombre' => $preInscripcion->distrito->provincia->departamento->nombre ?? null,
                'grado_id' => $preInscripcion->programa->grado_id ?? null,
                'programa_id' => $preInscripcion->programa_id ?? null,
                'fecha_nacimiento' => $preInscripcion->fecha_nacimiento,
                'sexo' => $preInscripcion->sexo,
            ];
        }

        $dniData = $dniService->getDniData($numIden);
        if ($dniData && $dniData['success']) {
            return [
                'num_iden' => $numIden,
                'nombres' => $dniData['data']['nombres'] ?? '',
                'ap_paterno' => $dniData['data']['apellidoPaterno'] ?? '',
                'ap_materno' => $dniData['data']['apellidoMaterno'] ?? '',
                'email' => null,
                'celular' => null,
            ];
        }

        return [
            'num_iden' => $numIden,
            'nombres' => '',
            'ap_paterno' => '',
            'ap_materno' => '',
            'email' => null,
            'celular' => null,
        ];
    }

    /**
     * Get programs by payment concept
     */
    protected function getProgramasByConcepto(string $codConcepto): Collection
    {
        $programas = collect([]);
        $conceptoPago = ConceptoPago::where('cod_concepto', $codConcepto)->first();
        if ($conceptoPago) {
            $programas = Programa::with(['grado', 'facultad'])
                ->where('concepto_pago_id', $conceptoPago->id)
                ->where('estado', true)
                ->get();
        }
        // Transformar para incluir grado_nombre y asegurar estructura
        return $programas->map(function ($programa) {
            $programa->grado_nombre = $programa->grado->nombre ?? '';
            return $programa;
        });
    }

    /**
     * Get voucher summary statistics
     */
    public function getVoucherSummary(): array
    {
        $vouchers = Voucher::with('conceptoPago')
            ->whereHas('conceptoPago', function ($query) {
                $query->whereNotIn('cod_concepto', ['00000001', '00000971', '00000970']);
            })
            ->get();
        // Cálculos en el backend
        $totalVouchers = $vouchers->count();

        $cutoffDate = \Carbon\Carbon::parse('2025-04-27');

        $totalRecaudado2026 = $vouchers->filter(function ($v) use ($cutoffDate) {
            return \Carbon\Carbon::parse($v->fecha_pago)->greaterThan($cutoffDate);
        })->sum('monto');

        $totalRecaudado2025 = $vouchers->filter(function ($v) use ($cutoffDate) {
            return \Carbon\Carbon::parse($v->fecha_pago)->lessThanOrEqualTo($cutoffDate);
        })->sum('monto');

        $inscritos = $vouchers->where('estado', 0)->count();
        $noInscritos = $totalVouchers - $inscritos;
        $pagaloPeCount = $vouchers->where('agencia', '0987')->count();
        $bancoNacionCount = $totalVouchers - $pagaloPeCount;

        // Calcular porcentajes
        $getPercentage = function ($count) use ($totalVouchers) {
            return $totalVouchers > 0 ? round(($count / $totalVouchers) * 100, 1) : 0;
        };

        // Datos a devolver al frontend
        return [
            'totalVouchers' => $totalVouchers,
            'totalRecaudado2026' => $totalRecaudado2026,
            'totalRecaudado2025' => $totalRecaudado2025,
            'inscritos' => $inscritos,
            'noInscritos' => $noInscritos,
            'pagaloPeCount' => $pagaloPeCount,
            'bancoNacionCount' => $bancoNacionCount,
            'porcentajeInscritos' => $getPercentage($inscritos),
            'porcentajeNoInscritos' => $getPercentage($noInscritos),
            'porcentajePagaloPe' => $getPercentage($pagaloPeCount),
            'porcentajeBancoNacion' => $getPercentage($bancoNacionCount),
        ];
    }
}
