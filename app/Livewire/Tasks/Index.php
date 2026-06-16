<?php

namespace App\Livewire\Tasks;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\Options;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Taskovi')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';
    #[Url]
    public string $client_id = '';
    #[Url]
    public string $project_id = '';
    #[Url]
    public string $status = '';
    #[Url]
    public string $priority = '';
    #[Url]
    public string $billing_type = '';
    #[Url]
    public string $payment_status = '';
    #[Url]
    public string $is_billable = '';
    #[Url]
    public string $date_from = '';
    #[Url]
    public string $date_to = '';
    #[Url]
    public string $month = '';
    #[Url]
    public string $year = '';
    #[Url]
    public bool $showArchived = false;

    public bool $showModal = false;
    public ?int $editingId = null;
    public array $form = [];

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->form = [
            'client_id' => $this->client_id ?: '',
            'project_id' => '',
            'title' => '',
            'description' => '',
            'status' => 'novo',
            'priority' => 'normalan',
            'task_date' => Dates::today(),
            'due_date' => '',
            'billing_type' => 'po_satu',
            'hourly_rate' => AppSettings::defaultHourlyRate(),
            'fixed_price' => 0,
            'is_billable' => true,
            'payment_status' => 'za_naplatu',
            'internal_note' => '',
        ];
        $this->editingId = null;
    }

    protected function rules(): array
    {
        return [
            'form.client_id' => ['required', 'exists:clients,id'],
            'form.project_id' => ['nullable', 'exists:projects,id'],
            'form.title' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.status' => ['required', 'in:'.implode(',', array_keys(Options::TASK_STATUSES))],
            'form.priority' => ['required', 'in:'.implode(',', array_keys(Options::TASK_PRIORITIES))],
            'form.task_date' => Dates::rule(),
            'form.due_date' => Dates::rule(),
            'form.billing_type' => ['required', 'in:'.implode(',', array_keys(Options::TASK_BILLING_TYPES))],
            'form.hourly_rate' => ['required', 'numeric', 'min:0'],
            'form.fixed_price' => ['required', 'numeric', 'min:0'],
            'form.is_billable' => ['boolean'],
            'form.payment_status' => ['required', 'in:'.implode(',', array_keys(Options::PAYMENT_STATUSES))],
            'form.internal_note' => ['nullable', 'string'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $task = Task::findOrFail($id);
        $this->editingId = $task->id;
        $this->form = [
            'client_id' => $task->client_id,
            'project_id' => $task->project_id ?: '',
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'task_date' => Dates::toInput($task->task_date),
            'due_date' => Dates::toInput($task->due_date),
            'billing_type' => $task->billing_type,
            'hourly_rate' => $task->hourly_rate,
            'fixed_price' => $task->fixed_price,
            'is_billable' => $task->is_billable,
            'payment_status' => $task->payment_status,
            'internal_note' => $task->internal_note,
        ];
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate()['form'];
        $data['project_id'] = $data['project_id'] ?: null;
        $data = Dates::fillForSave($data, ['task_date', 'due_date']);

        if ($this->editingId) {
            $task = Task::findOrFail($this->editingId);
            $task->update($data);
            session()->flash('success', 'Task je ažuriran.');
        } else {
            $task = Task::create($data);
            session()->flash('success', 'Task je dodan.');
        }

        $task->recalcTotalPrice();

        $this->showModal = false;
        $this->resetForm();
    }

    public function duplicate(int $id): void
    {
        $task = Task::findOrFail($id);
        $copy = $task->replicate(['total_price', 'archived_at']);
        $copy->title = $task->title.' (kopija)';
        $copy->status = 'novo';
        $copy->payment_status = 'za_naplatu';
        $copy->total_price = $task->billing_type === 'fiksno' ? $task->fixed_price : 0;
        $copy->archived_at = null;
        $copy->save();

        session()->flash('success', 'Task je dupliciran.');
    }

    public function toggleArchive(int $id): void
    {
        $task = Task::findOrFail($id);
        $task->archived_at = $task->archived_at ? null : now();
        $task->saveQuietly();
        session()->flash('success', $task->archived_at ? 'Task je arhiviran.' : 'Task je vraćen iz arhive.');
    }

    public function delete(int $id): void
    {
        Task::findOrFail($id)->delete();
        session()->flash('success', 'Task je obrisan.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'client_id', 'project_id', 'status', 'priority', 'billing_type', 'payment_status', 'is_billable', 'date_from', 'date_to', 'month', 'year', 'showArchived']);
        $this->resetPage();
    }

    public function updating($name): void
    {
        if (! in_array($name, ['showModal', 'editingId'], true) && ! str_starts_with($name, 'form.')) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $tasks = Task::query()
            ->with(['client', 'project'])
            ->when($this->showArchived, fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%")))
            ->when($this->client_id, fn ($q) => $q->where('client_id', $this->client_id))
            ->when($this->project_id, fn ($q) => $q->where('project_id', $this->project_id))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
            ->when($this->billing_type, fn ($q) => $q->where('billing_type', $this->billing_type))
            ->when($this->payment_status, fn ($q) => $q->where('payment_status', $this->payment_status))
            ->when($this->is_billable !== '', fn ($q) => $q->where('is_billable', (bool) $this->is_billable))
            ->when($this->date_from, fn ($q) => $q->whereDate('task_date', '>=', Dates::toDatabase($this->date_from)))
            ->when($this->date_to, fn ($q) => $q->whereDate('task_date', '<=', Dates::toDatabase($this->date_to)))
            ->when($this->month, fn ($q) => $q->whereMonth('task_date', $this->month))
            ->when($this->year, fn ($q) => $q->whereYear('task_date', $this->year))
            ->latest('task_date')
            ->latest('id')
            ->paginate(15);

        $projectsForFilter = $this->client_id
            ? Project::where('client_id', $this->client_id)->orderBy('name')->pluck('name', 'id')
            : collect();

        $projectsForForm = ($this->form['client_id'] ?? null)
            ? Project::where('client_id', $this->form['client_id'])->orderBy('name')->pluck('name', 'id')
            : collect();

        return view('livewire.tasks.index', [
            'tasks' => $tasks,
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'projectsForFilter' => $projectsForFilter,
            'projectsForForm' => $projectsForForm,
            'statuses' => Options::TASK_STATUSES,
            'priorities' => Options::TASK_PRIORITIES,
            'billingTypes' => Options::TASK_BILLING_TYPES,
            'paymentStatuses' => Options::PAYMENT_STATUSES,
            'months' => [1=>'Januar',2=>'Februar',3=>'Mart',4=>'April',5=>'Maj',6=>'Juni',7=>'Juli',8=>'August',9=>'Septembar',10=>'Oktobar',11=>'Novembar',12=>'Decembar'],
        ]);
    }
}
