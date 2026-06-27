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

        <div class="report-title">
            <h3>LISTA DE POSTULANTES APTOS MÉRITO FINAL</h3>
            <h4 class="mt-1 fw-bold">{{ mb_strtoupper($programaData['grado'], 'UTF-8') }} EN {{ mb_strtoupper($programaData['programa'], 'UTF-8') }}</h4>
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

        <div class="text-right mt-2" style="font-size: 10px; font-style: italic;">
            <p>Lambayeque, {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y, H:i') }}</p>
        </div>
    @endforeach
@endsection
