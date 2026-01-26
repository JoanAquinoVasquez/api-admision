<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostulanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'programa_id' => 'sometimes|exists:programas,id',
            'nombres' => 'sometimes|string|max:255',
            'ap_paterno' => 'sometimes|string|max:255',
            'ap_materno' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'tipo_doc' => 'sometimes|string|in:DNI,CE,PAS',
            'num_iden' => 'sometimes|string|min:8|max:20',
            'fecha_nacimiento' => 'sometimes|date',
            'sexo' => 'sometimes|string|in:M,F',
            'celular' => 'sometimes|string|regex:/^(\+?\d{1,3}[-.\s]?)?(\(?\d{2,4}\)?[-.\s]?)?\d{7,10}$/',
            'distrito_id' => 'sometimes|exists:distritos,id',
            'direccion' => 'sometimes|string|max:255',
            'rutaDocIden' => 'sometimes|file|mimes:pdf|max:10240',
            'rutaFoto' => 'sometimes|file|mimes:jpg,jpeg,png|max:10240',
            'rutaCV' => 'sometimes|file|mimes:pdf|max:10240',
            'rutaVoucher' => 'sometimes|file|mimes:pdf|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'programa_id.exists' => 'El programa seleccionado no existe.',
            'email.email' => 'El correo no tiene un formato válido.',
            'celular.regex' => 'El celular no tiene un formato válido.',
        ];
    }
}
