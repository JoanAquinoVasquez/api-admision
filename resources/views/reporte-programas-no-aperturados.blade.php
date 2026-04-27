@extends('pdf.layout')

@section('content')
    <div class="report-title">
        <h3>REPORTE DE PROGRAMAS ACADÉMICOS NO APERTURADOS</h3>
        </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">GRADO ACADÉMICO</th>
                <th style="width: 70%;">PROGRAMAS ACADÉMICOS</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grados = ['DOCTORADO', 'MAESTRIA', 'SEGUNDA ESPECIALIDAD PROFESIONAL'];
                $agrupados = collect($programas)->groupBy('grado');
            @endphp

            @foreach ($grados as $grado)
                @php
                    $items = $agrupados->get($grado, collect());
                @endphp

                @if ($items->isNotEmpty())
                    <tr>
                        <td class="text-center fw-bold" style="vertical-align: middle; font-size: 11px;" rowspan="{{ $items->count() }}">
                            {{ strtoupper($grado) }}
                        </td>
                        <td>{{ mb_strtoupper($items[0]->programa) }}</td>
                    </tr>
                    @foreach ($items->slice(1) as $prog)
                        <tr>
                            <td>{{ mb_strtoupper($prog->programa) }}</td>
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