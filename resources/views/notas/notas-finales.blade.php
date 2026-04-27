@extends('pdf.layout')

@section('custom_css')
<style>
    body {
        background-image: url('{{ public_path('img/logo_negro_epg.png') }}');
        background-position: center;
        background-repeat: no-repeat;
        background-size: 30%;
        background-opacity: 0.1;
    }
</style>
@endsection

@section('content')
    @foreach ($programas as $index => $programa)
        @if ($index > 0)
            <div class="page-break"></div>
        @endif

        <div class="report-title">
            <h3>CONSOLIDADO FINAL DE NOTAS Y MÉRITO</h3>
            <h4 class="mt-1 fw-bold">{{ mb_strtoupper($programa->grado->nombre ?? '', 'UTF-8') }} EN {{ mb_strtoupper($programa->nombre ?? '', 'UTF-8') }}</h4>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">N°</th>
                    <th style="width: 10%;">N. IDEN</th>
                    <th style="text-align: left; width: 35%;">APELLIDOS Y NOMBRES</th>
                    <th style="width: 10%;">CURRIC.</th>
                    <th style="width: 10%;">ENTREV.</th>
                    <th style="width: 10%;">EXAMEN</th>
                    <th style="width: 10%;">PUNTAJE</th>
                    <th style="width: 10%;">MÉRITO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($programa->inscripciones as $inscripcion)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $inscripcion->postulante->num_iden }}</td>
                        <td>
                            {{ mb_strtoupper($inscripcion->postulante->ap_paterno . ' ' . $inscripcion->postulante->ap_materno . ', ' . $inscripcion->postulante->nombres, 'UTF-8') }}
                        </td>
                        <td class="text-center">{{ $inscripcion->nota->cv ?? 'NSP' }}</td>
                        <td class="text-center">{{ $inscripcion->nota->entrevista ?? 'NSP' }}</td>
                        <td class="text-center">{{ $inscripcion->nota->examen ?? 'NSP' }}</td>
                        <td class="text-center fw-bold">{{ number_format($inscripcion->puntaje_final, 2) }}</td>
                        <td class="text-center fw-bold">{{ $inscripcion->merito }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Firma -->
        <div class="firma-container">
            <div class="firma-linea"></div>
            <p class="firma-text fw-bold">Dr. Leandro Agapito Aznarán Castillo</p>
            <p class="firma-text">Director de la Escuela de Posgrado</p>
        </div>
        
        <div class="text-right mt-2" style="font-size: 10px; font-style: italic;">
            <p>Lambayeque, {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y, H:i') }}</p>
        </div>
    @endforeach
@endsection
