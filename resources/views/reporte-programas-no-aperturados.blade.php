<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Programas Académicos No Aperturados</title>
    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        h2,
        p {
            text-align: center;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4.2px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .grado-academico {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>

<body>
 <div style="text-align: center;">
        <img src="{{ public_path('img/isotipo_color_epg.webp') }}" alt="Logo UNPRG" width="100px">
        <h2>REPORTE DE PROGRAMAS ACADÉMICOS NO APERTURADOS</h2>
        <p>Examen de Admisión {{ config('admission.cronograma.periodo') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30%;">GRADO ACADÉMICO</th>
                <th style="width: 70%;">PROGRAMAS ACADÉMICOS</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grados = ['DOCTORADO', 'MAESTRIA', 'SEGUNDA ESPECIALIDAD'];
                $agrupados = collect($programas)->groupBy('grado');
            @endphp

            @foreach ($grados as $grado)
                @php
                    $items = $agrupados->get($grado, collect());
                @endphp

                @if ($items->isNotEmpty())
                    <tr>
                        <td class="grado-academico" rowspan="{{ $items->count() }}">
                            {{ strtoupper($grado) }}
                        </td>
                        <td>{{ $items[0]->programa }}</td>
                    </tr>
                    @foreach ($items->slice(1) as $prog)
                        <tr>
                            <td>{{ $prog->programa }}</td>
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