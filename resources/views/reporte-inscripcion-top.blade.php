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
    <title>Reporte de Inscripción - Programas Top</title>
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
    </style>
</head>

<body>
    <h2>REPORTE DE INSCRIPCION ORDENADO POR INSCRITOS | ESCUELA DE POSGRADO - ADMISION {{ config('admission.cronograma.periodo') }}
    </h2>
    <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
    <p><strong>Rector:</strong> Dr. Enrique Wilfredo Carpena Velásquez</p>
    <p><strong>Director de Escuela:</strong> Dr. Leandro Agapito Aznarán Castillo</p>

    <table>
        <thead>
            <tr>
                <th>FACULTAD</th>
                <th>GRADOS ACADÉMICOS</th>
                <th>PROGRAMAS ACADÉMICOS</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeneral = 0; @endphp
            @foreach ($programas as $programa)
                <tr>
                    <td>{{ $programa->facultad }}</td>
                    <td>{{ $programa->grado }}</td>
                    <td>{{ $programa->programa }}</td>
                    <td class="text-right">{{ $programa->total_inscritos }}</td>
                </tr>
                @php $totalGeneral += $programa->total_inscritos; @endphp
            @endforeach
            <tr>
                <td colspan="3"><strong>TOTAL GENERAL</strong></td>
                <td class="text-right"><strong>{{ $totalGeneral }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
