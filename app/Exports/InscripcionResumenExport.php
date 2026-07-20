<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InscripcionResumenExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new InscripcionResumenSummarySheet(),
            new InscripcionResumenRawSheet(null, null),
        ];
    }
}
