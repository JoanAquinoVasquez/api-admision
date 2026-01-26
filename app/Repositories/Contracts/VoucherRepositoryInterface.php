<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface VoucherRepositoryInterface extends RepositoryInterface
{
    /**
     * Find voucher by numero and num_iden (for BN payment type)
     *
     * @param string $numero
     * @param string $numIden
     * @return Model|null
     */
    public function findByNumeroAndNumIden(string $numero, string $numIden): ?Model;

    /**
     * Find voucher by last 6 digits and num_iden (for PY payment type)
     *
     * @param string $last6Digits
     * @param string $numIden
     * @return Model|null
     */
    public function findByLast6DigitsAndNumIden(string $last6Digits, string $numIden): ?Model;

    /**
     * Find carpeta voucher by num_iden
     *
     * @param string $numIden
     * @return Model|null
     */
    public function findCarpetaVoucher(string $numIden): ?Model;

    /**
     * Mark voucher as used
     *
     * @param int $voucherId
     * @return bool
     */
    public function markAsUsed(int $voucherId): bool;
}
