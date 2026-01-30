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
        body {
            font-family: 'Arial', sans-serif;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <img src="{{ public_path('img/ficha.png') }}" style="position: absolute; width: 112.9%; margin-left: -1.19cm; margin-top: -1.2cm">
    <div style="position: relative; width: 21cm; height: 4cm;"> <!-- Ancho aproximado de una página A4 -->
        @if ($foto)
           <img src="{{ $foto }}" alt="Foto del Postulante"
                style="position: absolute; left: 14.1cm; top: 0 cm; width: 3cm; height:4cm; border: 1px solid black;">
        @else
            <p>No hay foto disponible</p>
        @endif
    </div>

    <div style="position: relative; width: 21cm; height: 1cm;">
        <b class="text-center"
            style="position: absolute; left: 0 cm; top: 0 cm; width: 11.5cm; border: 1px solid grey; color: #596C8A">DATOS
            PERSONALES</b>
        <b class="text-center"
            style="position: absolute; left: 12cm; top: 0.5cm; width: 7cm; border: 1px solid grey; color: #596C8A">INFORMACION
            DE
            CONTACTO</b>
    </div>


    <div style="position: relative; width: 21cm; height: 2cm; margin-top: 0cm; margin-bottom: 1cm">
        <p style="position: absolute; left: 0 cm; top: 0 cm; width: 11.5cm;">
            <strong>{{ $tipo_doc }}:</strong> {{ $num_iden }}
            <span style="float: right; height: 0.5cm">
                <strong>Fecha de Nacimiento: </strong>{{ Carbon::parse($fecha_nacimiento)->format('d/m/Y') }}</span>
            <br>
            <strong>Nombres y apellidos:</strong> {{ $nombres }} {{ $apellidos }} <br>
            <strong>Lugar de Residencia:</strong> {{ $departamento }} | {{ $provincia }} | {{ $distrito }} <br>
            <strong>Dirección:</strong> {{ $direccion }}
        </p>
        <p style="position: absolute; left: 12cm; top: 0 cm; width: 7cm; margin-top: 0.5cm;">
            <strong>Celular:</strong> {{ $celular }} <br>
            <strong>Correo:</strong> {{ $correo }}
        </p>
    </div>

    <div style="position: relative; width: 21cm; height: 0cm;">
        <b class="text-center"
            style="position: absolute; left: 12cm; top: 0 cm; width: 7cm; border: 1px solid grey; color: #596C8A;">DATOS
            DE POSTULACIÓN</b>
    </div>

    <div style="position: relative; width: 7cm; height: 0cm; margin-top: 1cm;">
        <p style="position: absolute; left: 12cm; top: 0 cm; width: 7cm;">
            <strong>Modalidad:</strong> EXAMEN ORDINARIO <br>
            <strong>Grado Académico:</strong> {{ $nombreGrado }} <br>
            <strong>Programa Académico:</strong> {{ $nombrePrograma }} <br>
            <strong>N° Voucher:</strong> {{ $cod_voucher }}
        </p>
    </div>

    <div style="position: relative; width: 11cm; height: 0cm; margin-top: 1cm;">
        <b class="text-center"
            style="position: absolute; left: 0; top: 0.5em; width: 11cm; border: 1px solid grey; color: #596C8A; line-height: 1.5;">DECLARACIÓN
            JURADA</b>
    </div>

    <div style="position: relative; width: 11cm; height: 6cm; margin-top: 0.5cm;">
        <br>
        <p style="font-size: 12px"> Conozco, acepto y me someto a las bases, condiciones y procedimientos establecidos
            en el Reglamento del Concurso de Admisión, de la Universidad Nacional Pedro Ruiz Gallo.
            <br>
            <br>
            La información y fotografía registrada es AUTÉNTICA. Las imágenes de mi DNI y el voucher de pago enviados
            para mi
            inscripción como postulante al presente Concurso de Admisión, son copia fiel al original; en caso de faltar
            a la verdad, me
            someto a las sanciones correspondientes (Art.97, Art.105 y Art.127 del Reglamento de Admisión de la
            Uiversidad Nacional Pedro Ruiz Gallo).
            <br>
            <br>
            <br>
            <br>
            <br>
            __________________________________________

        </p>

    </div>


    <div style="position: absolute; bottom: 0; width: 21cm; height: 2.4cm;">
        <b class="text-start">
            <p style="font-size: 14px; position: absolute; left: 0cm; bottom: 0; width: 10.5cm;">
                <strong>Lambayeque,
                    {{ Carbon::parse($updated_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</strong>
            </p>
        </b>
        <b class="text-end">
            <p class="text-center" style="font-size: 14px; position: absolute; right: 0cm; bottom: 0; width: 10.5cm;">
                <strong>
                    _______________________________<br>
                    Firma Postulante
                </strong>
            </p>
        </b>
    </div>


</body>

</html>
