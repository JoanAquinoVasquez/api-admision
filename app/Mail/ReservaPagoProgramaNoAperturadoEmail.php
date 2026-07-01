<?php

namespace App\Mail;

use App\Models\Inscripcion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaPagoProgramaNoAperturadoEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inscripcion;

    /**
     * Create a new message instance.
     */
    public function __construct(Inscripcion $inscripcion)
    {
        $this->inscripcion = $inscripcion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comunicado Importante: Estado de Inscripción - Reserva de Pago - Escuela de Posgrado UNPRG',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Eager-load relations to avoid N+1 queries during queue serialization/deserialization
        $this->inscripcion->loadMissing(['postulante.documentos', 'programa.grado', 'voucher']);

        $postulante = $this->inscripcion->postulante;
        $programa = $this->inscripcion->programa;
        $grado = $programa ? $programa->grado : null;
        $voucher = $this->inscripcion->voucher;

        // Get Google Drive voucher URL
        $voucherDoc = $postulante && $postulante->documentos
            ? $postulante->documentos->firstWhere('tipo', 'Voucher')
            : null;
        $voucherUrl = $voucherDoc ? $voucherDoc->url : '#';

        return new Content(
            view: 'email.programa-no-aperturado-reserva',
            with: [
                'sexo' => $postulante->sexo ?? 'M',
                'nombres' => $postulante->nombres ?? '',
                'ap_paterno' => $postulante->ap_paterno ?? '',
                'ap_materno' => $postulante->ap_materno ?? '',
                'nombre_grado' => $grado ? $grado->nombre : 'Posgrado',
                'nombre_programa' => $programa ? $programa->nombre : '',
                'codigo_voucher' => $this->inscripcion->codigo ?? ($voucher ? $voucher->codigo : ''),
                'monto_voucher' => $voucher ? number_format($voucher->monto, 2) : '0.00',
                'voucher_url' => $voucherUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
