<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Support\Dates;
use App\Support\Money;
use App\Support\ReportBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'client_id' => $request->get('client_id', ''),
            'project_id' => $request->get('project_id', ''),
            'status' => $request->get('status', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', ''),
            'only_billable' => $request->boolean('only_billable'),
            'only_unpaid' => $request->boolean('only_unpaid'),
        ];

        $report = null;
        if ($filters['client_id']) {
            $built = ReportBuilder::build($filters);
            if ($built['client']) {
                $currency = $built['client']->currency;
                $report = [
                    'client' => $built['client']->only(['id', 'name', 'currency']),
                    'tasks' => $built['tasks']->map(fn ($t) => [
                        'id' => $t->id,
                        'title' => $t->title,
                        'task_date_display' => Dates::formatOr($t->task_date),
                        'project' => $t->project?->name,
                        'status' => $t->status,
                        'payment_status' => $t->payment_status,
                        'minutes_human' => Money::minutesToHuman($t->totalMinutes()),
                        'total_price_formatted' => Money::format($t->total_price, $currency),
                    ]),
                    'totals' => $built['totals'],
                    'filters' => $built['filters'],
                ];
            }
        }

        return Inertia::render('Reports/Index', [
            'report' => $report,
            'filters' => $filters,
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'projects' => $filters['client_id']
                ? Project::where('client_id', $filters['client_id'])->orderBy('name')->pluck('name', 'id')
                : [],
            'exportParams' => array_filter($filters, fn ($v) => $v !== '' && $v !== false),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'date_from' => Dates::rule(),
            'date_to' => Dates::rule(),
        ]);

        return redirect()->route('reports.index', $request->only([
            'client_id', 'project_id', 'status', 'date_from', 'date_to', 'only_billable', 'only_unpaid',
        ]));
    }
}
