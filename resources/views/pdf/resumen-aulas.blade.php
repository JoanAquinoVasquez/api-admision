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
                    <td class="text-center fw-bold" style="font-size: 12px; @if(!$item['tiene_aula']) color: #94a3b8; @endif">{{ $item['inscritos'] }}</td>
                    <td class="text-center fw-bold" style="@if($item['tiene_aula']) color: #003366; font-size: 11px; @else color: #ef4444; font-size: 9px; @endif">
                        {{ mb_strtoupper($item['aulas'], 'UTF-8') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #7f8c8d;">
                <td colspan="3" class="text-right" style="padding: 8px;">SUBTOTAL (CON AULA ASIGNADA):</td>
                <td class="text-center" style="font-size: 13px; color: #003366; padding: 8px;">{{ $subtotalInscritos }}</td>
                <td class="text-center" style="font-size: 10px; padding: 8px; color: #475569;">{{ $cantidadProgramasConAula }} PROG.</td>
            </tr>
            <tr style="background-color: #e2e8f0; font-weight: bold; border-top: 1px solid #7f8c8d; border-bottom: 2px solid #003366;">
                <td colspan="3" class="text-right" style="padding: 10px;">TOTAL GENERAL (PROGRAMAS ABIERTOS):</td>
                <td class="text-center" style="font-size: 14px; color: #b45309; padding: 10px;">{{ $totalGeneralInscritos }}</td>
                <td class="text-center" style="font-size: 10px; padding: 10px; color: #475569;">{{ $totalProgramas }} PROG.</td>
            </tr>
        </tfoot>
    </table>
@endsection
