<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Programas</title>
    <style>
        /* ... tus estilos base ... */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
        }

        /* Estilo para el encabezado de la tabla */
        th {
            background-color: #444;
            color: white;
            text-align: center;
        }

        /* Estilo para la fila separadora del Grado */
        .grado-header {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
            padding-left: 15px;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <h2>REPORTE DE PROGRAMAS ACADÉMICOS APERTURADOS</h2>
    <p>Examen de Admisión {{ config('admission.cronograma.periodo') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">N°</th>
                <th style="width: 90%;">PROGRAMA ACADÉMICO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grados = ['DOCTORADO', 'MAESTRIA', 'SEGUNDA ESPECIALIDAD PROFESIONAL'];
                $agrupados = collect($programas)->groupBy('grado');
            @endphp

            @foreach ($grados as $grado)
                @php $items = $agrupados->get($grado, collect()); @endphp

                @if ($items->isNotEmpty())
                    <tr>
                        <td colspan="2" class="grado-header">
                            {{ strtoupper($grado) }}
                        </td>
                    </tr>

                    @foreach ($items as $index => $prog)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td> {{($grado) }} en {{ $prog->programa }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Fecha de generación: {{ $fechaHora->format('d/m/Y H:i') }}</p>
    </div>

</body>

</html>
