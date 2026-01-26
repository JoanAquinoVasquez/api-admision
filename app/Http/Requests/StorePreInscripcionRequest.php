<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreInscripcionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Permitir acceso público para preinscripciones
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
            'postulante_id' => 'nullable|exists:postulantes,id',
            'programa_id' => 'required|exists:programas,id',
            'distrito_id' => 'required|exists:distritos,id',
            'nombres' => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tipo_doc' => 'required|string|max:20|in:DNI,CE,PASAPORTE',
            'num_iden' => 'required|string|max:20|unique:pre_inscripcions,num_iden',
            'fecha_nacimiento' => 'required|date|before:today',
            'sexo' => 'required|in:M,F',
            'celular' => 'required|string|max:15|min:9',
            'uni_procedencia' => 'nullable|string|max:255',
            'centro_trabajo' => 'nullable|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'estado' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'programa_id.required' => 'Debe seleccionar un programa',
            'programa_id.exists' => 'El programa seleccionado no existe',
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

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'programa_id' => 'programa',
            'distrito_id' => 'distrito',
            'nombres' => 'nombres',
            'ap_paterno' => 'apellido paterno',
            'ap_materno' => 'apellido materno',
            'email' => 'correo electrónico',
            'tipo_doc' => 'tipo de documento',
            'num_iden' => 'número de identificación',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'sexo' => 'sexo',
            'celular' => 'celular',
        ];
    }
}
