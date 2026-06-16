<?php

namespace App\Http\Controllers;

use App\Exports\ClientReportExport;
use App\Support\ReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    protected function filters(Request $request): array
    {
        return [
            'client_id' => $request->query('client_id'),
            'project_id' => $request->query('project_id'),
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'only_billable' => $request->boolean('only_billable'),
            'only_unpaid' => $request->boolean('only_unpaid'),
        ];
    }

    protected function filename(array $report, string $ext): string
    {
        $name = $report['client'] ? Str::slug($report['client']->name) : 'izvjestaj';

        return 'izvjestaj-'.$name.'-'.now()->format('Y-m-d').'.'.$ext;
    }

    public function pdf(Request $request)
    {
        $report = ReportBuilder::build($this->filters($request));
        abort_unless($report['client'], 404);

        $pdf = Pdf::loadView('reports.pdf', ['report' => $report])->setPaper('a4');

        return $pdf->download($this->filename($report, 'pdf'));
    }

    public function excel(Request $request)
    {
        $report = ReportBuilder::build($this->filters($request));
        abort_unless($report['client'], 404);

        return Excel::download(new ClientReportExport($report), $this->filename($report, 'xlsx'));
    }

    public function print(Request $request)
    {
        $report = ReportBuilder::build($this->filters($request));
        abort_unless($report['client'], 404);

        return view('reports.print', ['report' => $report]);
    }
}
