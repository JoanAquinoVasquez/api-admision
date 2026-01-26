<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValidarVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo_pago'   => 'required|string|in:BN,PY',
            'numero' => 'required|string|digits_between:6,7',
            'num_iden'    => 'required|string',
            'agencia'     => 'required|string|digits:4',
            'fecha_pago'  => 'required|date',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $tipoPago = $this->input('tipo_pago');
            $numVoucher = $this->input('numero');

            if ($tipoPago === 'BN' && strlen($numVoucher) !== 7) {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' => 'El número de voucher para el tipo de pago "BN" debe tener 7 dígitos.'
                ], 422));
            }

            if ($tipoPago === 'PY' && strlen($numVoucher) !== 6) {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' => 'El número de voucher para el tipo de pago "PY" debe tener 6 dígitos.'
                ], 422));
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        // Toma solo el primer mensaje de error
        $mensaje = $validator->errors()->first();

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $mensaje,
        ], 422));
    }
}
