@extends('pdf.layout')

@section('content')
    @foreach ($programas as $index => $programa)
        @if ($index > 0)
            <div class="page-break"></div>
        @endif

        <div class="report-title">
            <h3>LISTA DE POSTULANTES - ENTREVISTA</h3>
            <h4 class="mt-1 fw-bold">{{ mb_strtoupper($programa->grado->nombre ?? '', 'UTF-8') }} EN {{ mb_strtoupper($programa->nombre ?? '', 'UTF-8') }}</h4>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">N°</th>
                    <th style="width: 80px;">N. IDEN</th>
                    <th style="text-align: left;">APELLIDOS Y NOMBRES</th>
                    <th style="width: 140px;">PUNTAJE (NÚMEROS)</th>
                    <th style="width: 140px;">PUNTAJE (LETRAS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($programa->inscripciones as $inscripcion)
                    <tr>
                        <td class="text-center" style="height: 30px; vertical-align: middle;">{{ $loop->iteration }}</td>
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
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="firma-container">
            <div class="firma-linea"></div>
            @if(!empty($programa->docente->nombres) && $programa->docente->nombres !== 'POR ASIGNAR')
                <p class="firma-text fw-bold">
                    {{ mb_strtoupper($programa->docente->nombres, 'UTF-8') }}
                </p>
                <p class="firma-text">DOCENTE EVALUADOR DE ENTREVISTA</p>
                @if(!empty($programa->docente->dni))
                    <p class="firma-text"><strong>DNI:</strong> {{ $programa->docente->dni }}</p>
                @else
                    <p class="firma-text"><strong>DNI:</strong> ________________________________</p>
                @endif
            @else
                <p class="firma-text fw-bold">Docente Evaluador</p>
                <p class="firma-text" style="margin-top: 10px;"><strong>NOMBRES:</strong> __________________________________________________</p>
                <p class="firma-text" style="margin-top: 10px;"><strong>DNI:</strong> ________________________________</p>
            @endif
        </div>

        <div class="text-right mt-2" style="font-size: 10px; font-style: italic;">
            <p>Lambayeque, {{ \Carbon\Carbon::now()->locale('es')->translatedFormat('d \d\e F \d\e Y, H:i') }}</p>
        </div>
    @endforeach
@endsection
