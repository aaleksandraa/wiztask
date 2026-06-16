<?php

namespace App\Livewire\TimeEntries;

use App\Models\Client;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Support\AppSettings;
use App\Support\Dates;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Vrijeme')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $client_id = '';
    #[Url]
    public string $date_from = '';
    #[Url]
    public string $date_to = '';
    #[Url]
    public string $is_billable = '';

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
            'client_id' => '',
            'task_id' => '',
            'work_date' => Dates::today(),
            'description' => '',
            'hours' => 0,
            'minutes' => 0,
            'hourly_rate' => AppSettings::defaultHourlyRate(),
            'is_billable' => true,
        ];
        $this->editingId = null;
    }

    protected function rules(): array
    {
        return [
            'form.client_id' => ['required', 'exists:clients,id'],
            'form.task_id' => ['required', 'exists:tasks,id'],
            'form.work_date' => Dates::rule(required: true),
            'form.description' => ['nullable', 'string'],
            'form.hours' => ['required', 'integer', 'min:0', 'max:999'],
            'form.minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'form.hourly_rate' => ['required', 'numeric', 'min:0'],
            'form.is_billable' => ['boolean'],
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
        $entry = TimeEntry::findOrFail($id);
        $this->editingId = $entry->id;
        $this->form = [
            'client_id' => $entry->client_id,
            'task_id' => $entry->task_id,
            'work_date' => Dates::toInput($entry->work_date),
            'description' => $entry->description,
            'hours' => $entry->hours,
            'minutes' => $entry->minutes,
            'hourly_rate' => $entry->hourly_rate,
            'is_billable' => $entry->is_billable,
        ];
        $this->resetValidation();
        $this->showModal = true;
    }

    public function updatedFormTaskId($value): void
    {
        if ($value && ($task = Task::find($value))) {
            $this->form['client_id'] = $task->client_id;
            if (empty($this->form['hourly_rate'])) {
                $this->form['hourly_rate'] = $task->hourly_rate;
            }
        }
    }

    public function save(): void
    {
        $data = $this->validate()['form'];
        $data = Dates::fillForSave($data, ['work_date']);
        $task = Task::findOrFail($data['task_id']);
        $data['client_id'] = $task->client_id;
        $data['project_id'] = $task->project_id;

        if ($this->editingId) {
            TimeEntry::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Unos vremena je ažuriran.');
        } else {
            TimeEntry::create($data);
            session()->flash('success', 'Vrijeme je dodano.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        TimeEntry::findOrFail($id)->delete();
        session()->flash('success', 'Unos vremena je obrisan.');
    }

    public function updating($name): void
    {
        if (in_array($name, ['client_id', 'date_from', 'date_to', 'is_billable'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = TimeEntry::query()
            ->with(['client', 'task'])
            ->when($this->client_id, fn ($q) => $q->where('client_id', $this->client_id))
            ->when($this->date_from, fn ($q) => $q->whereDate('work_date', '>=', Dates::toDatabase($this->date_from)))
            ->when($this->date_to, fn ($q) => $q->whereDate('work_date', '<=', Dates::toDatabase($this->date_to)))
            ->when($this->is_billable !== '', fn ($q) => $q->where('is_billable', (bool) $this->is_billable));

        $sumMinutes = (int) (clone $query)->sum('total_minutes');
        $sumPrice = (float) (clone $query)->sum('total_price');

        $entries = $query->latest('work_date')->latest('id')->paginate(20);

        $tasksForForm = ($this->form['client_id'] ?? null)
            ? Task::where('client_id', $this->form['client_id'])->orderBy('title')->pluck('title', 'id')
            : Task::orderBy('title')->limit(100)->pluck('title', 'id');

        return view('livewire.time-entries.index', [
            'entries' => $entries,
            'sumMinutes' => $sumMinutes,
            'sumPrice' => $sumPrice,
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'tasksForForm' => $tasksForForm,
        ]);
    }
}
