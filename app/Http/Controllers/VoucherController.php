<?php
namespace App\Http\Controllers;

use App\Exports\VoucherExport;
use App\Http\Requests\ValidarVoucherRequest;
use App\Models\Voucher;
use App\Services\DniService;
use App\Services\VoucherService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class VoucherController extends BaseController
{
    public function __construct(
        protected VoucherService $voucherService
    ) {
    }

    /**
     * Listar los vouchers de pago.
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $vouchers = Voucher::with('conceptoPago')->get();
            return $this->successResponse($vouchers);
        }, 'Error al obtener los vouchers');
    }

    /**
     * Mostrar voucher por número de identificación
     */
    public function show($num_iden)
    {
        return $this->handleRequest(function () use ($num_iden) {
            $voucher = Voucher::with('conceptoPago')
                ->where('num_iden', $num_iden)
                ->first();

            if (!$voucher) {
                return $this->successResponse([
                    'success' => false,
                    'message' => 'No se encuentra registrado su pago. Verifique que los datos ingresados sean correctos o, si ya realizó el pago, espere 24 horas hábiles para su inscripción.',
                ]);
            }

            $preinscripcion = \App\Models\PreInscripcion::where('num_iden', $num_iden)->first();

            return $this->successResponse([
                'success' => true,
                'voucher' => $voucher,
                'preinscripcion' => $preinscripcion ? true : false,
            ]);
        }, 'Error al obtener el voucher');
    }

    /**
     * Subir el txt del BN con los vouchers de pago.
     */
    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'file.*' => 'required|mimes:txt|max:2048',
            ]);

            $resultado = $this->voucherService->processVoucherFiles($request->file('file'));

            $this->logActivity('Vouchers procesados desde archivos TXT', null, [
                'registros' => $resultado['registros'],
            ]);

            return $this->successResponse($resultado, $resultado['message'], 201);
        }, 'Error al procesar los archivos de vouchers');
    }

    /**
     * Validar un voucher de pago.
     */
    public function validarVoucher(ValidarVoucherRequest $request, DniService $dniService)
    {
        return $this->handleRequest(function () use ($request, $dniService) {
            $validated = $request->validated();

            $resultado = $this->voucherService->validateVoucher(
                $validated['num_iden'],
                $validated['numero'],
                $validated['agencia'],
                $validated['fecha_pago'],
                $dniService
            );

            if (!$resultado['success']) {
                return $this->errorResponse($resultado['message'], 422);
            }

            $this->logActivity('Voucher validado', null, [
                'num_iden' => $validated['num_iden'],
                'voucher' => $validated['numero'],
            ]);

            return $this->successResponse($resultado, 'Pago validado correctamente.');
        }, 'Error al validar el voucher');
    }

    /**
     * Resumen de vouchers
     */
    public function resumenVouchers()
    {
        return $this->handleRequest(function () {
            $resumen = $this->voucherService->getVoucherSummary();
            return $this->successResponse($resumen);
        }, 'Error al obtener el resumen de vouchers');
    }

    /**
     * Exportar los vouchers de pago
     */
    public function export()
    {
        return $this->handleRequest(function () {
            $nombreArchivo = 'vouchers_' . now()->format('d-m-Y_His') . '.xlsx';
            return Excel::download(new VoucherExport, $nombreArchivo);
        }, 'Error al generar el reporte de vouchers');
    }
}
