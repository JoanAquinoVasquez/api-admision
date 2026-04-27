@extends('pdf.layout')

@section('content')
    <div class="report-title">
        <h3>REPORTE DE INSCRIPCION POR FACULTAD</h3>
        </div>

    <div class="info-panel">
        <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
        <p><strong>Rector:</strong> Dr. Enrique Wilfredo Carpena Velásquez</p>
        <p><strong>Director de Escuela:</strong> Dr. Leandro Agapito Aznarán Castillo</p>
    </div>

    @foreach ($facultades as $facultad)
        <h4 class="mt-2 mb-1" style="color: #003366; text-transform: uppercase;">Facultad: {{ $facultad->facultad }}</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">GRADOS ACADÉMICOS</th>
                    <th style="width: 50%;">PROGRAMAS ACADÉMICOS</th>
                    <th style="width: 20%;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalFacultad = 0;
                @endphp
                @foreach ($facultad->programas as $programa)
                    <tr>
                        <td class="text-center">{{ mb_strtoupper($programa->grado) }}</td>
                        <td>{{ mb_strtoupper($programa->programa) }}</td>
                        <td class="text-right">{{ $programa->total_inscritos }}</td>
                    </tr>
                    @php
                        $totalFacultad += $programa->total_inscritos;
                    @endphp
                @endforeach
                <tr style="background-color: #e8ecf1;">
                    <td style="border: none;"></td>
                    <td class="text-right fw-bold" style="color: #003366;">TOTAL DE LA FACULTAD</td>
                    <td class="text-right fw-bold" style="color: #003366; font-size: 11px;">{{ $totalFacultad }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach
@endsection
