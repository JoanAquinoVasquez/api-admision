@extends('pdf.layout')

@section('document_title', 'Resumen de Inscritos y Aulas')

@section('report_title', 'RESUMEN DE INSCRITOS POR AULA')
@section('report_subtitle', 'PROCESO DE ADMISIÓN ' . config('admission.cronograma.periodo'))

@section('content')
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">N°</th>
                <th style="width: 15%; text-align: left;">GRADO</th>
                <th style="width: 50%; text-align: left;">PROGRAMA</th>
                <th style="width: 15%; text-align: center;">INSCRITOS</th>
                <th style="width: 15%; text-align: center;">AULA(S)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datos as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ mb_strtoupper($item['grado'], 'UTF-8') }}</td>
                    <td>{{ mb_strtoupper($item['programa'], 'UTF-8') }}</td>
                    <td class="text-center fw-bold" style="font-size: 12px;">{{ $item['inscritos'] }}</td>
                    <td class="text-center fw-bold" style="color: #003366; font-size: 11px;">{{ $item['aulas'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
