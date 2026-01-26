<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreInscripcionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Agregar lógica de autorización si es necesario
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'postulante_id' => 'nullable|exists:postulantes,id',
            'distrito_id' => 'sometimes|required|exists:distritos,id',
            'nombres' => 'sometimes|required|string|max:255',
            'ap_paterno' => 'sometimes|required|string|max:255',
            'ap_materno' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'tipo_doc' => 'sometimes|required|string|max:50|in:DNI,CE,PASAPORTE',
            'num_iden' => 'sometimes|required|string|max:20|unique:pre_inscripcions,num_iden,' . $id,
            'fecha_nacimiento' => 'sometimes|required|date|before:today',
            'sexo' => 'sometimes|required|in:M,F',
            'celular' => 'sometimes|required|string|max:15|min:9',
            'uni_procedencia' => 'nullable|string|max:255',
            'centro_trabajo' => 'nullable|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'estado' => 'sometimes|boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'distrito_id.required' => 'Debe seleccionar un distrito',
            'distrito_id.exists' => 'El distrito seleccionado no existe',
            'nombres.required' => 'Los nombres son obligatorios',
            'ap_paterno.required' => 'El apellido paterno es obligatorio',
            'ap_materno.required' => 'El apellido materno es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ingresar un correo electrónico válido',
            'tipo_doc.in' => 'El tipo de documento debe ser DNI, CE o PASAPORTE',
            'num_iden.required' => 'El número de identificación es obligatorio',
            'num_iden.unique' => 'Este número de identificación ya está registrado',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
            'sexo.required' => 'Debe seleccionar el sexo',
            'sexo.in' => 'El sexo debe ser M o F',
            'celular.required' => 'El número de celular es obligatorio',
            'celular.min' => 'El celular debe tener al menos 9 dígitos',
        ];
    }
}
