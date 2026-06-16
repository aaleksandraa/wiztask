<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\Money;
use App\Support\ModelPresenter;
use App\Support\Options;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        $taskAgg = Task::query()
            ->whereNull('archived_at')
            ->selectRaw('
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = ? AND updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as done_month,
                SUM(CASE WHEN is_billable = 1 AND payment_status IN (?, ?, ?) THEN 1 ELSE 0 END) as to_bill,
                SUM(CASE WHEN is_billable = 1 AND payment_status IN (?, ?, ?) THEN total_price ELSE 0 END) as unpaid_amount,
                SUM(CASE WHEN task_date BETWEEN ? AND ? THEN total_price ELSE 0 END) as value_month
            ', [
                'u_toku',
                'zavrseno', $startMonth, $endMonth,
                'za_naplatu', 'fakturisano', 'djelimicno_placeno',
                'za_naplatu', 'fakturisano', 'djelimicno_placeno',
                $startMonth, $endMonth,
            ])
            ->first();

        $baseListQuery = fn () => Task::active()->with(['client:id,name,currency', 'project:id,name']);

        $mapList = fn ($items) => $items->map(fn (Task $t) => [
            ...ModelPresenter::task($t),
            'due_date_display' => Dates::formatOr($t->due_date),
            'due_overdue' => $t->due_date && $t->due_date->isPast(),
        ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeClients' => Client::where('status', 'aktivan')->count(),
                'tasksInProgress' => (int) ($taskAgg->in_progress ?? 0),
                'tasksDoneThisMonth' => (int) ($taskAgg->done_month ?? 0),
                'tasksToBill' => (int) ($taskAgg->to_bill ?? 0),
                'unpaidAmount' => Money::format((float) ($taskAgg->unpaid_amount ?? 0)),
                'minutesThisMonth' => Money::minutesToHuman((int) TimeEntry::whereBetween('work_date', [$startMonth, $endMonth])->sum('total_minutes')),
                'valueThisMonth' => Money::format((float) ($taskAgg->value_month ?? 0)),
            ],
            'lists' => [
                'latestTasks' => $mapList($baseListQuery()->latest()->limit(6)->get()),
                'inProgress' => $mapList($baseListQuery()->where('status', 'u_toku')->latest()->limit(6)->get()),
                'waitingClient' => $mapList($baseListQuery()->where('status', 'ceka_klijenta')->latest()->limit(6)->get()),
                'toBill' => $mapList($baseListQuery()->where('is_billable', true)->whereIn('payment_status', ['za_naplatu', 'fakturisano', 'djelimicno_placeno'])->latest()->limit(6)->get()),
                'withDueDate' => $mapList($baseListQuery()->whereNotNull('due_date')->whereNotIn('status', ['zavrseno', 'placeno', 'otkazano'])->orderBy('due_date')->limit(6)->get()),
            ],
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'today' => Dates::formatOr(now()),
            'defaults' => [
                'task_date' => Dates::today(),
                'hourly_rate' => AppSettings::defaultHourlyRate(),
            ],
        ]);
    }
}
