@extends('pdf.layout')

@section('content')
@php
    $inscripcion = $inscripciones->first();
    $programa = $inscripcion->programa ?? null;
    $isObras = ($programa->id ?? null) == 9;
    
    // Resolve docenteObj
    $docenteObj = null;
    if ($programa) {
        $docenteObj = app(\App\Services\DocenteService::class)->getDocenteEntrevistaForReport($programa->id, $programa->docenteEntrevista);
    }

    if ($isObras) {
        $groups = [
            $inscripciones->take(30),
            $inscripciones->slice(30)->take(25)->values()
        ];
    } else {
        $groups = [$inscripciones];
    }
@endphp

@foreach($groups as $groupIndex => $group)
    @if($groupIndex > 0)
        <div class="page-break"></div>
    @endif
    <div class="report-title">
        <h3>LISTA DE POSTULANTES APTOS PARA LA ENTREVISTA</h3>
        <h4 class="mt-1 fw-bold">{{ mb_strtoupper($inscripciones->first()->programa->grado->nombre ?? '', 'UTF-8') }} EN {{ mb_strtoupper($inscripciones->first()->programa->nombre ?? '', 'UTF-8') }}</h4>
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
            @foreach ($group as $inscripcion)
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
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="firma-container">
        <div class="firma-linea"></div>
        @php
            $docenteActual = $docenteObj;
            if ($isObras) {
                $docenteActual = $groupIndex == 0 
                    ? (object)['nombres' => 'DR. CARLOS ADOLFO LOAYZA RIVAS', 'ap_paterno' => '', 'ap_materno' => '', 'dni' => '']
                    : (object)['nombres' => 'DR. JUAN FARIAS FEIJOO', 'ap_paterno' => '', 'ap_materno' => '', 'dni' => ''];
            }
        @endphp
        @if(!empty($docenteActual->nombres) && $docenteActual->nombres !== 'POR ASIGNAR')
            <p class="firma-text fw-bold">
                {{ mb_strtoupper($docenteActual->nombres, 'UTF-8') }}
            </p>
            <p class="firma-text">DOCENTE EVALUADOR DE ENTREVISTA</p>
            @if(!empty($docenteActual->dni))
                <p class="firma-text"><strong>DNI:</strong> {{ $docenteActual->dni }}</p>
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
