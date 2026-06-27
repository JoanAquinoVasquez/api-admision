@extends('pdf.layout')

@section('custom_css')
    <style>
        table.data-table {
            font-size: 15px !important;
        }
        table.data-table th, table.data-table td {
            padding: 8px 10px !important;
        }
    </style>
@endsection

@section('content')
    @foreach ($programasData as $index => $programaData)
        @if ($index > 0)
            <div class="page-break"></div>
        @endif

        <div class="report-title" style="text-align: center; margin-bottom: 20px;">
            <h2 style="font-size: 32px; font-weight: bold; color: #003366; margin: 0 0 10px 0; letter-spacing: 0.5px;">{{ mb_strtoupper($programaData['aula']) }}</h2>
            <h4 style="font-size: 13px; font-weight: bold; color: #475569; margin: 0; text-transform: uppercase;">
                {{ mb_strtoupper($programaData['grado'], 'UTF-8') }} EN {{ mb_strtoupper($programaData['programa'], 'UTF-8') }}
            </h4>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">N°</th>
                    <th style="width: 100px;">DNI</th>
                    <th style="text-align: left;">APELLIDOS Y NOMBRES</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($programaData['inscripciones'] as $inscripcion)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $inscripcion->postulante->num_iden }}</td>
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
@endsection
