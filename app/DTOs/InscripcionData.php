<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

class InscripcionData
{
    public function __construct(
        // Postulante Data
        public readonly string $nombres,
        public readonly string $apPaterno,
        public readonly string $apMaterno,
        public readonly string $email,
        public readonly string $tipoDoc,
        public readonly string $numIden,
        public readonly string $fechaNacimiento,
        public readonly string $sexo,
        public readonly string $celular,
        public readonly string $direccion,
        public readonly int $distritoId,

        // Inscripcion Data
        public readonly int $programaId,
        public readonly ?string $tipoPago,
        public readonly string $codVoucher,

        // Files
        public readonly ?UploadedFile $voucherFile,
        public readonly ?UploadedFile $docIdentidadFile,
        public readonly ?UploadedFile $fotoFile,
        public readonly ?UploadedFile $cvFile,
    ) {
    }

    /**
     * Create DTO from Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            nombres: mb_strtoupper($request->input('nombres'), 'UTF-8'),
            apPaterno: mb_strtoupper($request->input('ap_paterno'), 'UTF-8'),
            apMaterno: mb_strtoupper($request->input('ap_materno'), 'UTF-8'),
            email: $request->input('email'),
            tipoDoc: $request->input('tipo_doc'),
            numIden: $request->input('num_iden'),
            fechaNacimiento: $request->input('fecha_nacimiento'),
            sexo: $request->input('sexo'),
            celular: $request->input('celular'),
            direccion: mb_strtoupper($request->input('direccion'), 'UTF-8'),
            distritoId: (int) $request->input('distrito_id'),
            programaId: (int) $request->input('programa_id'),
            tipoPago: $request->input('tipo_pago'),
            codVoucher: $request->input('cod_voucher'),
            voucherFile: $request->file('rutaVoucher'),
            docIdentidadFile: $request->file('rutaDocIden'),
            fotoFile: $request->file('rutaFoto'),
            cvFile: $request->file('rutaCV'),
        );
    }

    /**
     * Convert to array for Postulante creation
     */
    public function toPostulanteArray(): array
    {
        return [
            'distrito_id' => $this->distritoId,
            'nombres' => $this->nombres,
            'ap_paterno' => $this->apPaterno,
            'ap_materno' => $this->apMaterno,
            'email' => $this->email,
            'tipo_doc' => $this->tipoDoc,
            'num_iden' => $this->numIden,
            'fecha_nacimiento' => $this->fechaNacimiento,
            'sexo' => $this->sexo,
            'celular' => $this->celular,
            'direccion' => $this->direccion,
        ];
    }
}
