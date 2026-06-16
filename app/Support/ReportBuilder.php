<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Task;
use Illuminate\Support\Collection;

class ReportBuilder
{
    /**
     * @param  array  $filters  keys: client_id, date_from, date_to, project_id, status, only_billable, only_unpaid
     * @return array{client: ?Client, tasks: Collection, totals: array, filters: array}
     */
    public static function build(array $filters): array
    {
        $client = ! empty($filters['client_id']) ? Client::find($filters['client_id']) : null;

        $tasks = collect();
        $totals = [
            'count' => 0,
            'minutes' => 0,
            'billable' => 0.0,
            'paid' => 0.0,
            'unpaid' => 0.0,
        ];

        if ($client) {
            $query = Task::query()
                ->with('project')
                ->where('client_id', $client->id)
                ->when(! empty($filters['project_id']), fn ($q) => $q->where('project_id', $filters['project_id']))
                ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
                ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('task_date', '>=', Dates::toDatabase($filters['date_from'])))
                ->when(! empty($filters['date_to']), fn ($q) => $q->whereDate('task_date', '<=', Dates::toDatabase($filters['date_to'])))
                ->when(! empty($filters['only_billable']), fn ($q) => $q->where('is_billable', true))
                ->when(! empty($filters['only_unpaid']), fn ($q) => $q->whereIn('payment_status', ['za_naplatu', 'fakturisano', 'djelimicno_placeno']))
                ->orderBy('task_date');

            $tasks = $query->get();

            $totals['count'] = $tasks->count();
            $totals['minutes'] = (int) $tasks->sum(fn (Task $t) => $t->totalMinutes());
            $totals['billable'] = (float) $tasks->where('is_billable', true)->sum('total_price');
            $totals['paid'] = (float) $tasks->where('payment_status', 'placeno')->sum('total_price');
            $totals['unpaid'] = (float) $tasks->whereIn('payment_status', ['za_naplatu', 'fakturisano', 'djelimicno_placeno'])->sum('total_price');
        }

        return [
            'client' => $client,
            'tasks' => $tasks,
            'totals' => $totals,
            'filters' => $filters,
        ];
    }
}
