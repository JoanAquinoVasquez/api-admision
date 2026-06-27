@extends('pdf.layout')

@section('document_title', 'Relación de Evaluadores y Aulas')

@section('report_title', 'RELACIÓN DE EVALUADORES DE ENTREVISTA Y AULAS')
@section('report_subtitle', 'PROCESO DE ADMISIÓN ' . config('admission.cronograma.periodo'))

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">N°</th>
                <th style="width: 35%; text-align: left;">DOCENTE EVALUADOR</th>
                <th style="width: 10%; text-align: center;">AULA</th>
                <th style="width: 40%; text-align: left;">PROGRAMA</th>
                <th style="width: 10%; text-align: center;">INSCRITOS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datos as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="fw-bold" style="color: #1e293b;">{{ $item['evaluador'] }}</td>
                    <td class="text-center fw-bold" style="color: #003366; font-size: 11px;">{{ $item['aulas'] }}</td>
                    <td>{{ mb_strtoupper($item['grado'], 'UTF-8') }} EN {{ mb_strtoupper($item['programa'], 'UTF-8') }}</td>
                    <td class="text-center fw-bold">{{ $item['inscritos'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
