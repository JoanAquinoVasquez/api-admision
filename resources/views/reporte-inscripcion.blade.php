{{-- <!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pre-Inscripción</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2d9c2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .no-border {
            border: none;
        }
    </style>
</head>

<body>
    <h2>REPORTE DE INSCRIPCION POR FACULTAD | ESCUELA DE POSGRADO - ADMISION  {{ config('admission.cronograma.periodo') }}</h2>
    <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
    <p><strong>Rector:</strong> Dr. Enrique Wilfredo Carpena Velásquez</p>
    <p><strong>Director de Escuela:</strong> Dr. Leandro Agapito Aznarán Castillo</p>

    <table>
        <thead>
            <tr>
                <th>GRADOS ACADÉMICOS</th>
                <th>FACULTAD</th>
                <th>PROGRAMAS ACADÉMICOS</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalGeneral = 0; // Acumulado horizontal
            @endphp
            @foreach ($datos as $dato)
                <tr>
                    <td>{{ $dato->grado }}</td>
                    <td>{{ $dato->facultad }}</td>
                    <td>{{ $dato->programa }}</td>
                    <td class="text-right">{{ $dato->total_inscritos }}</td>
                </tr>
                @php
                    $totalGeneral += $dato->total_inscritos;
                @endphp
            @endforeach
            <tr>
                <td class="no-border"></td>
                <td class="no-border"></td>
                <td><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ $totalGeneral }}</strong></td>
            </tr>
        </tbody>

    </table>
</body>

</html>
 --}}

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pre-Inscripción</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2d9c2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .no-border {
            border: none;
        }
    </style>
</head>

<body>
    <div style="text-align: center;">
        <img src="{{ public_path('img/isotipo_color_epg.webp') }}" alt="Logo" width="100">
        <h2>REPORTE DE INSCRIPCION POR FACULTAD | ESCUELA DE POSGRADO - ADMISION {{ config('admission.cronograma.periodo') }}</h2>
        <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
        <p><strong>Rector:</strong> Dr. Enrique Wilfredo Carpena Velásquez</p>
        <p><strong>Director de Escuela:</strong> Dr. Leandro Agapito Aznarán Castillo</p>
    </div>

    @foreach ($facultades as $facultad)
        <h3>Facultad: {{ $facultad->facultad }}</h3>
        <table>
            <thead>
                <tr>
                    <th>GRADOS ACADÉMICOS</th>
                    <th>PROGRAMAS ACADÉMICOS</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalFacultad = 0;
                @endphp
                @foreach ($facultad->programas as $programa)
                    <tr>
                        <td>{{ $programa->grado }}</td>
                        <td>{{ $programa->programa }}</td>
                        <td class="text-right">{{ $programa->total_inscritos }}</td>
                    </tr>
                    @php
                        $totalFacultad += $programa->total_inscritos;
                    @endphp
                @endforeach
                <tr>
                    <td class="no-border"></td>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>{{ $totalFacultad }}</strong></td>
                </tr>
            </tbody>
        </table>
        <pagebreak />
    @endforeach
</body>

</html>
