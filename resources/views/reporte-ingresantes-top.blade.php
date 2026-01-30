<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inscripción - Programas Top</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 10px 20px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 16px;
            font-weight: bold;
            color: #003366;
        }

        .info p {
            margin: 5px 0;
            font-size: 12px;
        }

        .info {
            margin-bottom: 15px;
        }

        .encabezado {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .encabezado img {
            height: 70px;
            margin-right: 15px;
        }

        .encabezado .titulo {
            text-align: center;
        }

        .encabezado .titulo h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .encabezado .subtitulo {
            font-size: 12px;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        td {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            background-color: #e6f7ff;
        }

        .firma {
            margin-top: 40px;
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
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <!-- Encabezado -->
    <div class="encabezado">
        <img src="{{ public_path('img/isotipo_color_epg.webp') }}" alt="Logo UNPRG">
        <div class="titulo">
            <h2>REPORTE DE INGRESANTES POR PROGRAMA</h2>
            <div class="subtitulo">ESCUELA DE POSGRADO - ADMISIÓN {{ config('admission.cronograma.periodo') }}</div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="info">
        <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
        <p><strong>Rector:</strong> Dr. Enrique Wilfredo Carpena Velásquez</p>
        <p><strong>Director de Escuela:</strong> Dr. Leandro Agapito Aznarán Castillo</p>
    </div>

    <!-- Tabla -->
    <table>
        <thead>
            <tr>
                <th class="text-center">N°</th>
                <th>FACULTAD</th>
                <th>GRADO ACADÉMICO</th>
                <th>PROGRAMA ACADÉMICO</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalGeneral = 0;
                $contador = 1;
            @endphp
            @foreach ($programas as $programa)
                <tr>
                    <td class="text-center">{{ $contador++ }}</td>
                    <td>{{ $programa->facultad }}</td>
                    <td>{{ $programa->grado }}</td>
                    <td>{{ $programa->programa }}</td>
                    <td class="text-right">{{ $programa->total_ingresantes }}</td>
                </tr>
                @php $totalGeneral += $programa->total_ingresantes; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL GENERAL</td>
                <td class="text-right">{{ $totalGeneral }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Firma -->
    <div class="firma">
        <div class="linea"></div>
        <p><strong>Dr. Leandro Agapito Aznarán Castillo</strong></p>
        <p>Director de la Escuela de Posgrado</p>
    </div>
</body>

</html>
