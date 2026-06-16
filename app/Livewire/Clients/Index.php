<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Support\AppSettings;
use App\Support\Options;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Klijenti')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $city = '';

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
            'name' => '',
            'contact_person' => '',
            'email' => '',
            'phone' => '',
            'website' => '',
            'city' => '',
            'country' => '',
            'address' => '',
            'note' => '',
            'status' => 'aktivan',
            'default_hourly_rate' => AppSettings::defaultHourlyRate(),
            'currency' => AppSettings::defaultCurrency(),
        ];
        $this->editingId = null;
    }

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.contact_person' => ['nullable', 'string', 'max:255'],
            'form.email' => ['nullable', 'email', 'max:255'],
            'form.phone' => ['nullable', 'string', 'max:255'],
            'form.website' => ['nullable', 'string', 'max:255'],
            'form.city' => ['nullable', 'string', 'max:255'],
            'form.country' => ['nullable', 'string', 'max:255'],
            'form.address' => ['nullable', 'string', 'max:255'],
            'form.note' => ['nullable', 'string'],
            'form.status' => ['required', 'in:'.implode(',', array_keys(Options::CLIENT_STATUSES))],
            'form.default_hourly_rate' => ['required', 'numeric', 'min:0'],
            'form.currency' => ['required', 'in:'.implode(',', Options::CURRENCIES)],
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
        $client = Client::findOrFail($id);
        $this->editingId = $client->id;
        $this->form = $client->only(array_keys($this->form));
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate()['form'];

        if ($this->editingId) {
            Client::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Klijent je ažuriran.');
        } else {
            Client::create($data);
            session()->flash('success', 'Klijent je dodan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Client::findOrFail($id)->delete();
        session()->flash('success', 'Klijent je obrisan.');
    }

    public function updating($name): void
    {
        if (in_array($name, ['search', 'status', 'city'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('contact_person', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->city, fn ($q) => $q->where('city', 'like', "%{$this->city}%"))
            ->withCount(['projects', 'tasks'])
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.clients.index', [
            'clients' => $clients,
            'statuses' => Options::CLIENT_STATUSES,
        ]);
    }
}
