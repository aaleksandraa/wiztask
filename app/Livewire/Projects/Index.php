<?php

namespace App\Livewire\Projects;

use App\Models\Client;
use App\Models\Project;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\Options;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Projekti')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $client_id = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $billing_type = '';

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
            'name' => '',
            'description' => '',
            'status' => 'planirano',
            'start_date' => '',
            'due_date' => '',
            'billing_type' => 'po_satu',
            'fixed_price' => 0,
            'currency' => AppSettings::defaultCurrency(),
            'note' => '',
        ];
        $this->editingId = null;
    }

    protected function rules(): array
    {
        return [
            'form.client_id' => ['required', 'exists:clients,id'],
            'form.name' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.status' => ['required', 'in:'.implode(',', array_keys(Options::PROJECT_STATUSES))],
            'form.start_date' => Dates::rule(),
            'form.due_date' => Dates::rule(),
            'form.billing_type' => ['required', 'in:'.implode(',', array_keys(Options::PROJECT_BILLING_TYPES))],
            'form.fixed_price' => ['required', 'numeric', 'min:0'],
            'form.currency' => ['required', 'in:'.implode(',', Options::CURRENCIES)],
            'form.note' => ['nullable', 'string'],
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
        $project = Project::findOrFail($id);
        $this->editingId = $project->id;
        $this->form = [
            'client_id' => $project->client_id,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status,
            'start_date' => Dates::toInput($project->start_date),
            'due_date' => Dates::toInput($project->due_date),
            'billing_type' => $project->billing_type,
            'fixed_price' => $project->fixed_price,
            'currency' => $project->currency,
            'note' => $project->note,
        ];
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate()['form'];
        $data = Dates::fillForSave($data, ['start_date', 'due_date']);

        if ($this->editingId) {
            Project::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Projekat je ažuriran.');
        } else {
            Project::create($data);
            session()->flash('success', 'Projekat je dodan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Project::findOrFail($id)->delete();
        session()->flash('success', 'Projekat je obrisan.');
    }

    public function updating($name): void
    {
        if (in_array($name, ['search', 'client_id', 'status', 'billing_type'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $projects = Project::query()
            ->with('client')
            ->withCount('tasks')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->client_id, fn ($q) => $q->where('client_id', $this->client_id))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->billing_type, fn ($q) => $q->where('billing_type', $this->billing_type))
            ->latest()
            ->paginate(12);

        return view('livewire.projects.index', [
            'projects' => $projects,
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'statuses' => Options::PROJECT_STATUSES,
            'billingTypes' => Options::PROJECT_BILLING_TYPES,
        ]);
    }
}
