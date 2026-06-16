<?php

namespace App\Livewire\Reports;

use App\Models\Client;
use App\Models\Project;
use App\Support\Dates;
use App\Support\Options;
use App\Support\ReportBuilder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Izvještaji')]
class Index extends Component
{
    #[Url]
    public string $client_id = '';
    #[Url]
    public string $project_id = '';
    #[Url]
    public string $status = '';
    #[Url]
    public string $date_from = '';
    #[Url]
    public string $date_to = '';
    #[Url]
    public bool $only_billable = false;
    #[Url]
    public bool $only_unpaid = false;

    public bool $generated = false;

    public function mount(): void
    {
        if ($this->client_id) {
            $this->generated = true;
        }
    }

    public function generate(): void
    {
        $this->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'date_from' => Dates::rule(),
            'date_to' => Dates::rule(),
        ], [], ['client_id' => 'klijent']);

        $this->generated = true;
    }

    public function updatedClientId(): void
    {
        $this->project_id = '';
    }

    protected function filters(): array
    {
        return [
            'client_id' => $this->client_id,
            'project_id' => $this->project_id,
            'status' => $this->status,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'only_billable' => $this->only_billable,
            'only_unpaid' => $this->only_unpaid,
        ];
    }

    public function render()
    {
        $report = $this->generated && $this->client_id
            ? ReportBuilder::build($this->filters())
            : null;

        $projects = $this->client_id
            ? Project::where('client_id', $this->client_id)->orderBy('name')->pluck('name', 'id')
            : collect();

        return view('livewire.reports.index', [
            'report' => $report,
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'projects' => $projects,
            'taskStatuses' => Options::TASK_STATUSES,
            'paymentStatuses' => Options::PAYMENT_STATUSES,
            'exportParams' => array_filter($this->filters(), fn ($v) => $v !== '' && $v !== false),
        ]);
    }
}
