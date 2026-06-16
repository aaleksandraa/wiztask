<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\Options;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public bool $showQuickTaskModal = false;

    public array $quickTask = [];

    public function mount(): void
    {
        $this->resetQuickTask();
    }

    protected function resetQuickTask(): void
    {
        $this->quickTask = [
            'client_id' => '',
            'project_id' => '',
            'title' => '',
            'status' => 'novo',
            'priority' => 'normalan',
            'task_date' => Dates::today(),
            'hourly_rate' => AppSettings::defaultHourlyRate(),
        ];
    }

    public function openQuickTask(): void
    {
        $this->resetQuickTask();
        $this->resetValidation();
        $this->showQuickTaskModal = true;
    }

    public function saveQuickTask(): void
    {
        $data = $this->validate([
            'quickTask.client_id' => ['required', 'exists:clients,id'],
            'quickTask.project_id' => ['nullable', 'exists:projects,id'],
            'quickTask.title' => ['required', 'string', 'max:255'],
            'quickTask.status' => ['required', 'in:'.implode(',', array_keys(Options::TASK_STATUSES))],
            'quickTask.priority' => ['required', 'in:'.implode(',', array_keys(Options::TASK_PRIORITIES))],
            'quickTask.task_date' => Dates::rule(),
            'quickTask.hourly_rate' => ['required', 'numeric', 'min:0'],
        ], [], [
            'quickTask.client_id' => 'klijent',
            'quickTask.title' => 'naslov',
        ])['quickTask'];

        $client = Client::findOrFail($data['client_id']);

        $task = Task::create([
            'client_id' => $client->id,
            'project_id' => $data['project_id'] ?: null,
            'title' => $data['title'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'task_date' => Dates::toDatabase($data['task_date']),
            'billing_type' => 'po_satu',
            'hourly_rate' => $data['hourly_rate'] ?: $client->default_hourly_rate,
            'is_billable' => true,
            'payment_status' => 'za_naplatu',
        ]);

        $task->recalcTotalPrice();

        $this->showQuickTaskModal = false;
        session()->flash('success', 'Task je brzo dodan.');

        $this->redirect(route('tasks.show', $task), navigate: true);
    }

    public function render()
    {
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        $activeClients = Client::where('status', 'aktivan')->count();

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

        $minutesThisMonth = (int) TimeEntry::whereBetween('work_date', [$startMonth, $endMonth])->sum('total_minutes');

        $baseListQuery = fn () => Task::active()->with(['client:id,name', 'project:id,name']);

        return view('livewire.dashboard', [
            'activeClients' => $activeClients,
            'tasksInProgress' => (int) ($taskAgg->in_progress ?? 0),
            'tasksDoneThisMonth' => (int) ($taskAgg->done_month ?? 0),
            'tasksToBill' => (int) ($taskAgg->to_bill ?? 0),
            'unpaidAmount' => (float) ($taskAgg->unpaid_amount ?? 0),
            'minutesThisMonth' => $minutesThisMonth,
            'valueThisMonth' => (float) ($taskAgg->value_month ?? 0),
            'latestTasks' => $baseListQuery()->latest()->limit(6)->get(['id', 'title', 'status', 'due_date', 'client_id', 'project_id']),
            'inProgress' => $baseListQuery()->where('status', 'u_toku')->latest()->limit(6)->get(['id', 'title', 'status', 'due_date', 'client_id', 'project_id']),
            'waitingClient' => $baseListQuery()->where('status', 'ceka_klijenta')->latest()->limit(6)->get(['id', 'title', 'status', 'due_date', 'client_id', 'project_id']),
            'toBill' => $baseListQuery()
                ->where('is_billable', true)
                ->whereIn('payment_status', ['za_naplatu', 'fakturisano', 'djelimicno_placeno'])
                ->latest()->limit(6)->get(['id', 'title', 'status', 'due_date', 'client_id', 'project_id']),
            'withDueDate' => $baseListQuery()
                ->whereNotNull('due_date')
                ->whereNotIn('status', ['zavrseno', 'placeno', 'otkazano'])
                ->orderBy('due_date')->limit(6)->get(['id', 'title', 'status', 'due_date', 'client_id', 'project_id']),
            'taskStatuses' => Options::TASK_STATUSES,
            'priorities' => Options::TASK_PRIORITIES,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'quickProjects' => $this->quickTask['client_id']
                ? Project::where('client_id', $this->quickTask['client_id'])->orderBy('name')->pluck('name', 'id')
                : collect(),
        ]);
    }
}
