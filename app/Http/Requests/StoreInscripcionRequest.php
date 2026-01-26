<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInscripcionRequest extends FormRequest
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
            'programa_id' => 'required|exists:programas,id',
            'nombres' => 'required|string|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'ap_paterno' => 'required|string|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'ap_materno' => 'required|string|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email' => 'required|email',
            'tipo_doc' => 'required|string',
            'num_iden' => 'required|max:20',
            'celular' => 'required|string|regex:/^(\+?\d{1,3}[-.\s]?)?(\(?\d{2,4}\)?[-.\s]?)?\d{7,10}$/',
            'fecha_nacimiento' => 'required|date|before:today',
            'distrito_id' => 'required|exists:distritos,id',
            'direccion' => 'required|string',
            'sexo' => 'required|string|in:M,F',
            'cod_voucher' => 'required|string|digits_between:6,7',
            'rutaVoucher' => 'file|mimes:pdf|max:10240',
            'rutaDocIden' => 'file|mimes:pdf|max:10240',
            'rutaFoto' => 'file|mimes:jpg,jpeg,png|max:10240',
            'rutaCV' => 'file|mimes:pdf|max:10240',
        ];
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cod_voucher.digits_between' => 'El número de voucher debe tener entre 6 y 7 dígitos.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'rutaVoucher.mimes' => 'El archivo del voucher debe ser un PDF.',
            'rutaFoto.mimes' => 'La foto debe ser JPG, JPEG o PNG.',
            'rutaDocIden.mimes' => 'El documento de identificación debe ser un PDF.',
            'rutaVoucher.max' => 'El archivo del voucher no debe exceder los 10 MB.',
            'rutaFoto.max' => 'La foto no debe exceder los 10 MB.',
            'rutaDocIden.max' => 'El documento de identificación no debe exceder los 10 MB.',
        ];
    }
}
