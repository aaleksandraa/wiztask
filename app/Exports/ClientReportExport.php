<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class ClientReportExport implements FromView
{
    public function __construct(public array $report)
    {
    }

    public function view(): View
    {
        return view('reports.excel', ['report' => $this->report]);
    }
}
