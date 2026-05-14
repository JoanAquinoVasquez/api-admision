@extends('pdf.layout')

@section('content')
    <div class="report-title">
        <h3>REPORTE DE INSCRIPCION POR DEMANDA (TOP)</h3>
        </div>

    <div class="info-panel">
        <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
        <p><strong>Rector:</strong> Dr. Enrique Wilfredo Carpena Velásquez</p>
        <p><strong>Director de Escuela:</strong> Dr. Leandro Agapito Aznarán Castillo</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%;">FACULTAD</th>
                <th style="width: 20%;">GRADOS ACADÉMICOS</th>
                <th style="width: 65%;">PROGRAMAS ACADÉMICOS</th>
                <th style="width: 8%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeneral = 0; @endphp
            @foreach ($programas as $programa)
                <tr>
                    <td>{{ mb_strtoupper($programa->facultad) }}</td>
                    <td class="text-center">{{ mb_strtoupper($programa->grado) }}</td>
                    <td>{{ mb_strtoupper($programa->programa) }}</td>
                    <td class="text-right fw-bold" style="font-size: 14px;">{{ $programa->total_inscritos }}</td>
                </tr>
                @php $totalGeneral += $programa->total_inscritos; @endphp
            @endforeach
            <tr style="background-color: #e8ecf1;">
                <td colspan="3" class="text-right fw-bold" style="color: #003366;">TOTAL GENERAL DE INSCRITOS</td>
                <td class="text-right fw-bold" style="color: #003366; font-size: 13px;">{{ $totalGeneral }}</td>
            </tr>
        </tbody>
    </table>
@endsection
