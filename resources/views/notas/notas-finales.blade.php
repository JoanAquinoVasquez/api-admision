<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Ingresantes {{ config('admission.cronograma.periodo') }}</title>
    <style>
        @page {
            margin: 1cm;
            background-image: url('{{ public_path('img/logo_negro_epg.png') }}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: 30%;
            background-opacity: 0.1;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .header-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .header-container img {
            width: 100px;
            margin-bottom: 10px;
        }

        .title {
            border-bottom: 2px solid #003366;
            display: inline-block;
            padding-bottom: 5px;
            font-size: 18px;
            font-weight: bold;
        }

        .title-container {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        thead th,
        tbody td {
            border: 1px solid black;
            padding: 6px;
            vertical-align: middle;
        }

        thead th {
            background-color: #f0f0f0;
            font-size: 13px;
            text-align: center;
        }

        tbody td {
            font-size: 11px;
        }

        td:nth-child(1),
        td:nth-child(2),
        td:nth-child(4),
        td:nth-child(5),
        td:nth-child(6),
        td:nth-child(7),
        td:nth-child(8) {
            text-align: center;
            width: 6%;
        }

        td:nth-child(3) {
            text-align: left;
            width: 32%;
        }

        h2,
        h3,
        h4 {
            margin: 0;
            line-height: 1.3;
        }

        .block-1 {
            margin-bottom: 0.5em;
        }

        .block-2 {
            margin-top: 0.5em;
        }

        .fecha {
            margin-top: 20px;
            text-align: right;
            font-size: 11px;
            font-style: italic;
        }

        .header-container h2,
        .header-container h3,
        .header-container h4 {
            margin: 5px 0;
            color: #003366;
        }

        .firma {
            text-align: center;
            font-size: 12px;
        }

        .firma .linea {
            display: inline-block;
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 50px;
        }

        .firma p {
            margin-top: 3px;
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    @foreach ($programas as $index => $programa)
        @if ($index > 0)
            <div style="page-break-before: always;"></div>
        @endif

        <div class="header-container">
            <img src="{{ public_path('img/logo_negro_epg.png') }}" alt="Logo UNPRG">
            <div class="block-1">
                <h2>UNIVERSIDAD NACIONAL PEDRO RUIZ GALLO</h2>
                <h3>ESCUELA DE POSGRADO</h3>
                <h4>ADMISIÓN {{ config('admission.cronograma.periodo') }}</h4>
            </div>

            <div class="block-2 title-container">
                <h4 class="title"> {{ mb_strtoupper($programa->grado->nombre ?? '', 'UTF-8') }} EN
                    {{ mb_strtoupper($programa->nombre ?? '', 'UTF-8') }}</h4>
            </div>
        </div>


        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>N. IDEN</th>
                    <th>APELLIDOS Y NOMBRES</th>
                    <th>CURRIC.</th>
                    <th>ENTREV.</th>
                    <th>EXAMEN</th>
                    <th>PUNTAJE</th>
                    <th>MÉRITO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($programa->inscripciones as $inscripcion)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $inscripcion->postulante->num_iden }}</td>
                        <td>
                            {{ mb_strtoupper($inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno . ', ' . $inscripcion->postulante->nombres, 'UTF-8') }}
                        </td>
                        <td>{{ $inscripcion->nota->cv ?? 'NSP' }}</td>
                        <td>{{ $inscripcion->nota->entrevista ?? 'NSP' }}</td>
                        <td>{{ $inscripcion->nota->examen ?? 'NSP' }}</td>
                        <td>{{ number_format($inscripcion->puntaje_final, 2) }}</td>
                        <td>{{ $inscripcion->merito }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Firma -->
        <div class="firma">
            <div class="linea"></div>
            <p><strong>Dr. Leandro Agapito Aznarán Castillo</strong></p>
            <p>Director de la Escuela de Posgrado</p>
        </div>
    @endforeach
</body>

</html>
