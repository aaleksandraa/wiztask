<?php

namespace App\Livewire\Clients;

use App\Models\Attachment;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\Options;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Client $client;

    #[Url]
    public string $tab = 'pregled';

    public bool $showProjectModal = false;
    public bool $showTaskModal = false;

    public array $projectForm = [];
    public array $taskForm = [];

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->resetProjectForm();
        $this->resetTaskForm();
    }

    protected function resetProjectForm(): void
    {
        $this->projectForm = [
            'name' => '',
            'description' => '',
            'status' => 'planirano',
            'start_date' => Dates::today(),
            'due_date' => '',
            'billing_type' => 'po_satu',
            'fixed_price' => 0,
            'currency' => $this->client->currency ?: AppSettings::defaultCurrency(),
            'note' => '',
        ];
    }

    protected function resetTaskForm(): void
    {
        $this->taskForm = [
            'project_id' => '',
            'title' => '',
            'description' => '',
            'status' => 'novo',
            'priority' => 'normalan',
            'task_date' => Dates::today(),
            'due_date' => '',
            'billing_type' => 'po_satu',
            'hourly_rate' => $this->client->default_hourly_rate ?: AppSettings::defaultHourlyRate(),
            'fixed_price' => 0,
            'is_billable' => true,
            'payment_status' => 'za_naplatu',
        ];
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function openProjectModal(): void
    {
        $this->resetProjectForm();
        $this->resetValidation();
        $this->showProjectModal = true;
    }

    public function openTaskModal(): void
    {
        $this->resetTaskForm();
        $this->resetValidation();
        $this->showTaskModal = true;
    }

    public function saveProject(): void
    {
        $data = $this->validate([
            'projectForm.name' => ['required', 'string', 'max:255'],
            'projectForm.description' => ['nullable', 'string'],
            'projectForm.status' => ['required', 'in:'.implode(',', array_keys(Options::PROJECT_STATUSES))],
            'projectForm.start_date' => Dates::rule(),
            'projectForm.due_date' => Dates::rule(),
            'projectForm.billing_type' => ['required', 'in:'.implode(',', array_keys(Options::PROJECT_BILLING_TYPES))],
            'projectForm.fixed_price' => ['required', 'numeric', 'min:0'],
            'projectForm.currency' => ['required', 'in:'.implode(',', Options::CURRENCIES)],
            'projectForm.note' => ['nullable', 'string'],
        ], [], ['projectForm.name' => 'naziv projekta'])['projectForm'];

        $data['client_id'] = $this->client->id;
        $data = Dates::fillForSave($data, ['start_date', 'due_date']);

        Project::create($data);

        $this->showProjectModal = false;
        $this->tab = 'projekti';
        session()->flash('success', 'Projekat je dodan.');
    }

    public function saveTask(): void
    {
        $data = $this->validate([
            'taskForm.project_id' => ['nullable', 'exists:projects,id'],
            'taskForm.title' => ['required', 'string', 'max:255'],
            'taskForm.description' => ['nullable', 'string'],
            'taskForm.status' => ['required', 'in:'.implode(',', array_keys(Options::TASK_STATUSES))],
            'taskForm.priority' => ['required', 'in:'.implode(',', array_keys(Options::TASK_PRIORITIES))],
            'taskForm.task_date' => Dates::rule(),
            'taskForm.due_date' => Dates::rule(),
            'taskForm.billing_type' => ['required', 'in:'.implode(',', array_keys(Options::TASK_BILLING_TYPES))],
            'taskForm.hourly_rate' => ['required', 'numeric', 'min:0'],
            'taskForm.fixed_price' => ['required', 'numeric', 'min:0'],
            'taskForm.is_billable' => ['boolean'],
            'taskForm.payment_status' => ['required', 'in:'.implode(',', array_keys(Options::PAYMENT_STATUSES))],
        ], [], ['taskForm.title' => 'naslov taska'])['taskForm'];

        $data['client_id'] = $this->client->id;
        $data['project_id'] = $data['project_id'] ?: null;
        $data = Dates::fillForSave($data, ['task_date', 'due_date']);

        $task = Task::create($data);
        $task->recalcTotalPrice();

        $this->showTaskModal = false;
        $this->tab = 'taskovi';
        session()->flash('success', 'Task je dodan.');
    }

    protected function summaryStats(): array
    {
        $id = $this->client->id;

        return [
            'projectsCount' => Project::where('client_id', $id)->count(),
            'totalMinutes' => (int) $this->client->timeEntries()->sum('total_minutes'),
            'totalBillable' => (float) $this->client->tasks()->where('is_billable', true)->sum('total_price'),
            'totalPaid' => (float) Payment::where('client_id', $id)->sum('amount'),
            'totalUnpaid' => (float) $this->client->tasks()
                ->where('is_billable', true)
                ->whereIn('payment_status', ['za_naplatu', 'fakturisano', 'djelimicno_placeno'])
                ->sum('total_price'),
        ];
    }

    public function render()
    {
        $stats = $this->summaryStats();

        $projects = collect();
        $tasks = collect();
        $timeEntries = collect();
        $attachments = collect();

        if (in_array($this->tab, ['projekti', 'taskovi', 'naplata'], true)) {
            $projects = $this->client->projects()->withCount('tasks')->latest()->limit(50)->get();
        }

        if (in_array($this->tab, ['taskovi', 'naplata'], true)) {
            $tasks = $this->client->tasks()->with('project:id,name')->latest()->limit(50)->get();
        }

        if ($this->tab === 'vrijeme') {
            $timeEntries = $this->client->timeEntries()
                ->with('task:id,title')
                ->latest('work_date')
                ->limit(50)
                ->get();
        }

        if ($this->tab === 'fajlovi') {
            $projectIds = $this->client->projects()->pluck('id');
            $taskIds = $this->client->tasks()->pluck('id');

            if ($projectIds->isNotEmpty() || $taskIds->isNotEmpty()) {
                $attachments = Attachment::query()
                    ->where(function ($q) use ($projectIds, $taskIds) {
                        if ($projectIds->isNotEmpty()) {
                            $q->where(fn ($q) => $q->where('attachable_type', Project::class)->whereIn('attachable_id', $projectIds));
                        }
                        if ($taskIds->isNotEmpty()) {
                            $q->orWhere(fn ($q) => $q->where('attachable_type', Task::class)->whereIn('attachable_id', $taskIds));
                        }
                    })
                    ->latest()
                    ->limit(60)
                    ->get();
            }
        }

        $clientProjects = $this->client->projects()->orderBy('name')->pluck('name', 'id');

        return view('livewire.clients.show', [
            'projects' => $projects,
            'tasks' => $tasks,
            'timeEntries' => $timeEntries,
            'attachments' => $attachments,
            'projectsCount' => $stats['projectsCount'],
            'totalMinutes' => $stats['totalMinutes'],
            'totalBillable' => $stats['totalBillable'],
            'totalPaid' => $stats['totalPaid'],
            'totalUnpaid' => $stats['totalUnpaid'],
            'clientProjects' => $clientProjects,
            'clientStatuses' => Options::CLIENT_STATUSES,
            'projectStatuses' => Options::PROJECT_STATUSES,
            'projectBillingTypes' => Options::PROJECT_BILLING_TYPES,
            'taskStatuses' => Options::TASK_STATUSES,
            'taskPriorities' => Options::TASK_PRIORITIES,
            'taskBillingTypes' => Options::TASK_BILLING_TYPES,
            'paymentStatuses' => Options::PAYMENT_STATUSES,
        ]);
    }
}
