<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulantes Aptos</title>
    <style>
        @page {
            margin: 0.9cm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            text-align: center;
        }

        .header-container {
            display: flex;
            align-items: center;
            /* Alinea verticalmente la imagen con el texto */
        }

        .title {
            border-bottom: 2px solid black;
            display: inline-block;
            padding-bottom: 5px;
        }

        .title-container {
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid black;
        }

        thead th,
        tbody td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th:nth-child(5),
        td:nth-child(5) {
            width: 150px;
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 40px;
        }

        tbody td {
            padding: 8px;
            font-size: 14px;
        }

        td:nth-child(3) {
            text-align: left;
        }

        h2,
        h3,
        h4 {
            margin: 0;
        }

        .block-1 h2,
        .block-1 h3,
        .block-1 h4 {
            line-height: 1.5;
        }

        .block-2 h4 {
            line-height: 1.5;
        }

        .block-1 {
            margin-bottom: 1.5em;
        }

        .block-2 {
            margin-top: 1.5em;
        }

        /* Estilo para el footer (firma del docente) */
        .footer {
            position: fixed;
            bottom: 50px;
            /* Ajusta según sea necesario */
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 14px;
        }

        .firma {
            margin-top: 40px;
            border-top: 2px solid black;
            display: inline-block;
            padding-top: 5px;
        }

        .firma-container {
            margin-top: 30px;
            /* Ajusta según necesidad */
            text-align: center;
        }

        .espacio {
            display: inline-block;
            min-width: 100px;
        }

        .espacio-nombre {
            display: inline-block;
            min-width: 250px;
        }

        .fecha {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header-container">
        {{-- <img src="img/logo_negro.png" alt="Logo" class="logo"> --}}
        <div class="block-1">
            <h2>UNIVERSIDAD NACIONAL PEDRO RUIZ GALLO</h2>
            <h3>ESCUELA DE POSGRADO</h3>
            <h4>ADMISIÓN {{ config('admission.cronograma.periodo') }}</h4>
        </div>
        <div class="block-2">
            <h4>{{ mb_strtoupper($inscripciones->first()->programa->grado->nombre ?? '', 'UTF-8') }} EN
                {{ mb_strtoupper($inscripciones->first()->programa->nombre ?? '', 'UTF-8') }}</h4>
        </div>
    </div>

    <div class="title-container">
        <h3 class="title">LISTA DE POSTULANTES APTOS PARA LA ENTREVISTA</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>N. IDEN</th>
                <th>APELLIDOS Y NOMBRES</th>
                <th>PUNTAJE (NÚMEROS)</th>
                <th>PUNTAJE (LETRAS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inscripciones as $inscripcion)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $inscripcion->postulante->num_iden }}</td>
                    <td>{{ mb_strtoupper(
                        $inscripcion->postulante->ap_paterno .
                            ' ' .
                            $inscripcion->postulante->ap_materno .
                            ', ' .
                            $inscripcion->postulante->nombres,
                        'UTF-8',
                    ) }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Firma del docente evaluador -->
    <div class="firma-container">
        <div class="datos-docente">
            <p class="firma">Docente Evaluador</p>
            <p><strong>NOMBRES: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;</strong></p>
            <p><strong>DNI: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</strong> </p>
        </div>
    </div>

    <div class="fecha">
        <p>Lambayeque, {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y, H:i') }}</p>
    </div>
</body>

</html>
