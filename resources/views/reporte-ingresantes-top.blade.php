@extends('pdf.layout')

@section('content')
    <div class="report-title">
        <h3>REPORTE DE INGRESANTES POR PROGRAMA</h3>
        </div>

    <div class="info-panel">
        <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
        <p><strong>Rector:</strong> Dr. Enrique Wilfredo Carpena Velásquez</p>
        <p><strong>Director de Escuela:</strong> Dr. Leandro Agapito Aznarán Castillo</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 25%;">FACULTAD</th>
                <th style="width: 15%;">GRADO ACADÉMICO</th>
                <th style="width: 45%;">PROGRAMA ACADÉMICO</th>
                <th style="width: 10%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalGeneral = 0;
                $contador = 1;
            @endphp
            @foreach ($programas as $programa)
                <tr>
                    <td class="text-center">{{ $contador++ }}</td>
                    <td>{{ mb_strtoupper($programa->facultad) }}</td>
                    <td class="text-center">{{ mb_strtoupper($programa->grado) }}</td>
                    <td>{{ mb_strtoupper($programa->programa) }}</td>
                    <td class="text-right">{{ $programa->total_ingresantes }}</td>
                </tr>
                @php $totalGeneral += $programa->total_ingresantes; @endphp
            @endforeach
            <tr style="background-color: #e8ecf1;">
                <td colspan="4" class="text-right fw-bold" style="color: #003366;">TOTAL GENERAL DE INGRESANTES</td>
                <td class="text-right fw-bold" style="color: #003366; font-size: 12px;">{{ $totalGeneral }}</td>
            </tr>
        </tbody>
    </table>

    <div class="firma-container">
        <div class="firma-linea"></div>
        <p class="firma-text fw-bold">Dr. Leandro Agapito Aznarán Castillo</p>
        <p class="firma-text">Director de la Escuela de Posgrado</p>
    </div>
@endsection
