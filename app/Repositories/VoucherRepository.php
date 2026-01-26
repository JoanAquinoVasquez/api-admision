<?php

namespace App\Repositories;

use App\Models\Voucher;
use App\Repositories\Contracts\VoucherRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class VoucherRepository extends BaseRepository implements VoucherRepositoryInterface
{
    /**
     * VoucherRepository constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create model instance
     *
     * @return Model
     */
    protected function makeModel(): Model
    {
        return new Voucher();
    }

    /**
     * Find voucher by numero and num_iden (for BN payment type)
     *
     * @param string $numero
     * @param string $numIden
     * @return Model|null
     */
    public function findByNumeroAndNumIden(string $numero, string $numIden): ?Model
    {
        return $this->model->with('conceptoPago')
            ->where([
                'numero' => $numero,
                'num_iden' => $numIden,
            ])
            ->where('concepto_pago_id', '!=', 4)
            ->first();
    }

    /**
     * Find voucher by last 6 digits and num_iden (for PY payment type)
     *
     * @param string $last6Digits
     * @param string $numIden
     * @return Model|null
     */
    public function findByLast6DigitsAndNumIden(string $last6Digits, string $numIden): ?Model
    {
        return $this->model->with('conceptoPago')
            ->where(['num_iden' => $numIden])
            ->whereRaw('RIGHT(numero, 6) = ?', [$last6Digits])
            ->where('concepto_pago_id', '!=', 4)
            ->first();
    }

    /**
     * Find carpeta voucher by num_iden
     *
     * @param string $numIden
     * @return Model|null
     */
    public function findCarpetaVoucher(string $numIden): ?Model
    {
        return $this->model->where('num_iden', $numIden)
            ->where('concepto_pago_id', 4)
            ->where('estado', true)
            ->first();
    }

    /**
     * Mark voucher as used
     *
     * @param int $voucherId
     * @return bool
     */
    public function markAsUsed(int $voucherId): bool
    {
        $voucher = $this->findOrFail($voucherId);
        return $voucher->update(['estado' => false]);
    }
}
