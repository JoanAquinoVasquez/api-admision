@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Constancia</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            width: 21cm;
            min-height: 29.7cm;
            position: relative;
        }

        .content-wrapper {
            margin: 1cm;
        }

        .text-center {
            text-align: center;
        }

        .section-header {
            border: 1px solid grey;
            color: #596C8A;
            font-weight: bold;
            text-align: center;
            padding: 2px 0;
        }
    </style>
</head>

<body>
    <!-- Background Image -->
    <img src="{{ public_path('img/ficha.png') }}"
        style="position: absolute; width: 100%; top: 0; left: 0; z-index: -1;">

    <div class="content-wrapper">

        <!-- Photo Section -->
        <div style="position: relative; width: 100%; height: 5cm; margin-bottom: 0.5cm;">
            @if ($foto)
                <img src="{{ $foto }}" alt="Foto del Postulante"
                    style="position: absolute; right: 8%; top: 0; width: 3.5cm; height: 4.5cm; border: 1px solid black;">
            @endif
        </div>

        <!-- Section Headers: Datos Personales & Información de Contacto -->
        <div style="position: relative; width: 100%; margin-bottom: 0.3cm;">
            <div style="display: inline-block; width: 58%;">
                <div class="section-header">DATOS PERSONALES</div>
            </div>
            <div style="display: inline-block; width: 38%; margin-left: 3%; vertical-align: top;">
                <div class="section-header">INFORMACIÓN DE CONTACTO</div>
            </div>
        </div>

        <!-- Personal Data & Contact Info -->
        <div style="position: relative; width: 100%; margin-bottom: 1cm;">
            <!-- Left Column: Personal Data -->
            <div style="display: inline-block; width: 58%; vertical-align: top;">
                <p style="font-size: 16px; margin: 0;">
                    <strong>{{ $tipo_doc }}:</strong> {{ $num_iden }}<br>
                    <strong>Fecha de Nacimiento:</strong> {{ Carbon::parse($fecha_nacimiento)->format('d/m/Y') }}<br>
                    <strong>Nombres y apellidos:</strong> {{ $nombres }} {{ $apellidos }}<br>
                    <strong>Lugar de Residencia:</strong> {{ $departamento }} | {{ $provincia }} | {{ $distrito }}<br>
                    <strong>Dirección:</strong> {{ $direccion }}
                </p>
            </div>

            <!-- Right Column: Contact Info -->
            <div style="display: inline-block; width: 38%; margin-left: 3%; vertical-align: top; margin-bottom: 0;">
                <p style="font-size: 16px; margin: 0;">
                    <strong>Celular:</strong> {{ $celular }}<br>
                    <strong>Correo:</strong> {{ $correo }}
                </p>
            </div>
        </div>

        <!-- Two Column Section: Declaración Jurada (Left) & Datos de Postulación (Right) -->
        <div style="position: relative; width: 100%; margin-bottom: 0.3cm;">
            <!-- Left Column: Declaración Jurada Header -->
            <div style="display: inline-block; width: 58%; vertical-align: top;">
                <div class="section-header">DECLARACIÓN JURADA</div>
            </div>

            <!-- Right Column: Datos de Postulación Header -->
            <div style="display: inline-block; width: 38%; margin-left: 3%; vertical-align: top;">
                <div class="section-header">DATOS DE POSTULACIÓN</div>
            </div>
        </div>

        <!-- Two Column Content -->
        <div style="position: relative; width: 100%; margin-bottom: 2cm;">
            <!-- Left Column: Declaración Jurada Content -->
            <div style="display: inline-block; width: 58%; vertical-align: top;">
                <p style="font-size: 12px; text-align: justify; line-height: 1.4; margin: 0;">
                    Conozco, acepto y me someto a las bases, condiciones y procedimientos establecidos en el Reglamento
                    del
                    Concurso de Admisión, de la Universidad Nacional Pedro Ruiz Gallo.
                    <br><br>
                    La información y fotografía registrada es AUTÉNTICA. Las imágenes de mi DNI y el voucher de pago
                    enviados para mi inscripción como postulante al presente Concurso de Admisión, son copia fiel al
                    original; en caso de faltar a la verdad, me someto a las sanciones correspondientes (Art.97, Art.105
                    y
                    Art.127 del Reglamento de Admisión de la Universidad Nacional Pedro Ruiz Gallo).
                    <br><br><br><br>
                    ___________________________________________________
                </p>
            </div>

            <!-- Right Column: Datos de Postulación Content -->
            <div style="display: inline-block; width: 38%; margin-left: 3%; vertical-align: top;">
                <p style="font-size: 16px; margin: 0; padding-top: 5px;">
                    <strong>Modalidad:</strong> EXAMEN ORDINARIO<br>
                    <strong>Grado Académico:</strong> {{ $nombreGrado }}<br>
                    <strong>Programa Académico:</strong> {{ $nombrePrograma }}<br>
                    <strong>N° Voucher:</strong> {{ $cod_voucher }}
                </p>
            </div>
        </div>

        <!-- Footer: Date and Signature -->
        <div style="position: absolute; bottom: 1cm; left: 1cm; right: 1cm; width: calc(100% - 2cm);">
            <div style="display: inline-block; width: 48%; vertical-align: bottom;">
                <p style="font-size: 16px; margin: 0;">
                    <strong>Lambayeque,
                        {{ Carbon::parse($updated_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</strong>
                </p>
            </div>
            <div
                style="display: inline-block; width: 48%; text-align: center; vertical-align: bottom; margin-left: 3%;">
                <p style="font-size: 16px; margin: 0;">
                    <strong>
                        _______________________________<br>
                        Firma Postulante
                    </strong>
                </p>
            </div>
        </div>

    </div> <!-- End content-wrapper -->

</body>

</html>