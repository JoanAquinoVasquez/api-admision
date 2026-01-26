<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulantes Aptos Final</title>
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
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 5px;
            /* Reduce la altura entre los elementos de la cabecera */
        }

        .title {
            border-bottom: 2px solid black;
            display: inline-block;
            padding-bottom: 5px;
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
            border: 1px solid black;
        }

        thead th,
        tbody td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        tbody td {
            padding: 8px;
            font-size: 14px;
        }

        th:nth-child(1),
        td:nth-child(1) {
            width: 30px;
            text-align: center;
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 80px;
            text-align: center;
        }

        td:nth-child(3) {
            text-align: left;
        }

        h2,
        h3,
        h4 {
            margin: 0;
            padding: 0;
        }

        .block-1 h2,
        .block-1 h3,
        .block-1 h4 {
            margin: 0;
            padding: 0;
            font-size: 20px;
            /* Reducción del tamaño de la fuente */
            line-height: 1.1;
            /* Ajuste para mayor compresión */
        }

        .block-2 h1 {
            font-size: 40px;
            /* Ajuste del tamaño */
            margin: 0;
        }

        .block-2 h4 {
            font-size: 20px;
            /* Ajuste del tamaño */
            line-height: 1.1;
        }

        .block-1 {
            margin-bottom: 0.5em;
            /* Espaciado reducido entre los bloques */
        }

        .block-2 {
            margin-top: 0.0em;
        }

        h3.title {
            font-size: 18px;
            margin-bottom: 10px;
        }

        /* Estilo para el footer (firma del docente) */
        .footer {
            position: fixed;
            bottom: 50px;
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
            text-align: center;
        }

        .datos-docente {
            display: block;
            text-align: center;
            margin-top: 10px;
        }

        .datos-docente p {
            margin: 5px 0;
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
    @foreach ($programasData as $index => $programaData)
        @if ($index > 0)
            <div style="page-break-before: always;"></div>
        @endif

        <div class="header-container">
            <div class="block-1">
                <h2>UNIVERSIDAD NACIONAL PEDRO RUIZ GALLO</h2>
                <h3>ESCUELA DE POSGRADO</h3>
                <h4>ADMISIÓN {{ config('admission.cronograma.periodo') }}</h4>
            </div>
            <div class="block-2">
                <h1>{{ $programaData['aula'] }}</h1>
                <h4>{{ mb_strtoupper($programaData['grado'], 'UTF-8') }} EN
                    {{ mb_strtoupper($programaData['programa'], 'UTF-8') }}</h4>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>DNI</th>
                    <th>APELLIDOS Y NOMBRES</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($programaData['inscripciones'] as $inscripcion)
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
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>

</html>
