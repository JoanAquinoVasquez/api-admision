@extends('pdf.layout')

@section('content')
    <div class="report-title">
        <h3>REPORTE DE PROGRAMAS ACADÉMICOS APERTURADOS</h3>
        </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">N°</th>
                <th style="width: 90%;">PROGRAMA ACADÉMICO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grados = ['DOCTORADO', 'MAESTRIA', 'SEGUNDA ESPECIALIDAD PROFESIONAL'];
                $agrupados = collect($programas)->groupBy('grado');
            @endphp

            @foreach ($grados as $grado)
                @php $items = $agrupados->get($grado, collect()); @endphp

                @if ($items->isNotEmpty())
                    <tr style="background-color: #e8ecf1;">
                        <td colspan="2" class="fw-bold" style="padding-left: 15px; color: #003366;">
                            {{ strtoupper($grado) }}
                        </td>
                    </tr>

                    @foreach ($items as $index => $prog)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ mb_strtoupper($grado) }} EN {{ mb_strtoupper($prog->programa) }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="text-right mt-2" style="font-size: 10px; font-style: italic;">
        <p>Fecha de generación: {{ $fechaHora->format('d/m/Y H:i') }}</p>
    </div>
@endsection