<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithTitle;

class InscripcionResumenRawSheet extends InscripcionExport implements WithTitle
{
    public function title(): string
    {
        return 'Worksheet';
    }
}
