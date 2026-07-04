@extends('pdf.layout')

@section('document_title', 'Reporte de Asistencia')

@section('custom_css')
    <style>
        table.data-table {
            font-size: 11px !important;
            font-weight: bold !important;
        }
        table.data-table th, table.data-table td {
            padding: 6px 8px !important;
            font-weight: bold !important;
        }
    </style>
@endsection

@section('content')
    <div class="report-title" style="text-align: center; margin-bottom: 20px;">
        <h2 style="font-size: 32px; font-weight: bold; color: #003366; margin: 0 0 10px 0; letter-spacing: 0.5px;">AULA 02</h2>
        <h4 style="font-size: 13px; font-weight: bold; color: #475569; margin: 0; text-transform: uppercase;">
            RELACIÓN DE POSTULANTES Y CONTROL DE ASISTENCIA - EXAMEN
        </h4>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">N°</th>
                <th style="width: 12%; text-align: center;">DNI</th>
                <th style="text-align: left; width: 35%;">APELLIDOS Y NOMBRES</th>
                <th style="text-align: left; width: 13%;">GRADO</th>
                <th style="text-align: left; width: 20%;">PROGRAMA</th>
                <th style="width: 15%; text-align: center;">FIRMA DEL POSTULANTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inscripciones as $inscripcion)
                <tr>
                    <td class="text-center" style="height: 35px; vertical-align: middle;">{{ $loop->iteration }}</td>
                    <td class="text-center" style="vertical-align: middle;">{{ $inscripcion->postulante->num_iden }}</td>
                    <td style="vertical-align: middle;">
                        {{ mb_strtoupper(
                            $inscripcion->postulante->ap_paterno .
                                ' ' .
                                $inscripcion->postulante->ap_materno .
                                ', ' .
                                $inscripcion->postulante->nombres,
                            'UTF-8',
                        ) }}
                    </td>
                    <td style="vertical-align: middle;">
                        @php
                            $grado_id = $inscripcion->programa->grado->id ?? null;
                            $grado_nombre = match ($grado_id) {
                                1 => 'DOCTORADO',
                                2 => 'MAESTRÍA',
                                3 => 'SEG. ESP. PROF.',
                                default => mb_strtoupper($inscripcion->programa->grado->nombre ?? 'N/A', 'UTF-8')
                            };
                        @endphp
                        {{ $grado_nombre }}
                    </td>
                    <td style="vertical-align: middle;">
                        {{ mb_strtoupper($inscripcion->programa->nombre ?? 'N/A', 'UTF-8') }}
                    </td>
                    <td style="vertical-align: middle;"></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-right mt-2" style="font-size: 10px; font-style: italic;">
        <p>Lambayeque, {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y, H:i') }}</p>
    </div>
@endsection
