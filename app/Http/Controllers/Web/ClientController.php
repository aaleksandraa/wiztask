<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\ModelPresenter;
use App\Support\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $clients = Client::query()
            ->when($request->q, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                    ->orWhere('contact_person', 'like', "%{$request->q}%")
                    ->orWhere('email', 'like', "%{$request->q}%");
            }))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->city, fn ($q) => $q->where('city', 'like', "%{$request->city}%"))
            ->withCount(['projects', 'tasks'])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => ModelPresenter::paginatedClients($clients),
            'filters' => $request->only(['q', 'status', 'city']),
            'defaults' => [
                'default_hourly_rate' => AppSettings::defaultHourlyRate(),
                'currency' => AppSettings::defaultCurrency(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateClient($request);
        Client::create($data);

        return back()->with('success', 'Klijent je dodan.');
    }

    public function show(Client $client, Request $request): Response
    {
        $tab = $request->get('tab', 'pregled');

        $projects = collect();
        $tasks = collect();
        $timeEntries = collect();
        $attachments = collect();

        if (in_array($tab, ['projekti', 'taskovi', 'naplata'], true)) {
            $projects = $client->projects()->withCount('tasks')->latest()->limit(50)->get()
                ->map(fn (Project $p) => ModelPresenter::project($p));
        }

        if (in_array($tab, ['taskovi', 'naplata'], true)) {
            $tasks = $client->tasks()->with('project:id,name')->latest()->limit(50)->get()
                ->map(fn (Task $t) => ModelPresenter::task($t));
        }

        if ($tab === 'vrijeme') {
            $timeEntries = $client->timeEntries()->with('task:id,title')->latest('work_date')->limit(50)->get()
                ->map(fn ($e) => ModelPresenter::timeEntry($e));
        }

        if ($tab === 'fajlovi') {
            $projectIds = $client->projects()->pluck('id');
            $taskIds = $client->tasks()->pluck('id');
            $attachments = \App\Models\Attachment::query()
                ->when($projectIds->isNotEmpty() || $taskIds->isNotEmpty(), function ($q) use ($projectIds, $taskIds) {
                    $q->where(function ($q) use ($projectIds, $taskIds) {
                        if ($projectIds->isNotEmpty()) {
                            $q->where(fn ($q) => $q->where('attachable_type', Project::class)->whereIn('attachable_id', $projectIds));
                        }
                        if ($taskIds->isNotEmpty()) {
                            $q->orWhere(fn ($q) => $q->where('attachable_type', Task::class)->whereIn('attachable_id', $taskIds));
                        }
                    });
                }, fn ($q) => $q->whereRaw('0=1'))
                ->latest()
                ->limit(60)
                ->get()
                ->map(fn ($a) => (new \App\Http\Resources\AttachmentResource($a))->resolve());
        }

        $id = $client->id;

        return Inertia::render('Clients/Show', [
            'client' => ModelPresenter::client($client, true),
            'tab' => $tab,
            'projects' => $projects,
            'tasks' => $tasks,
            'timeEntries' => $timeEntries,
            'attachments' => $attachments,
            'stats' => [
                'projectsCount' => Project::where('client_id', $id)->count(),
                'totalMinutes' => (int) $client->timeEntries()->sum('total_minutes'),
                'totalBillable' => (float) $client->tasks()->where('is_billable', true)->sum('total_price'),
                'totalPaid' => (float) \App\Models\Payment::where('client_id', $id)->sum('amount'),
                'totalUnpaid' => (float) $client->tasks()->where('is_billable', true)->whereIn('payment_status', ['za_naplatu', 'fakturisano', 'djelimicno_placeno'])->sum('total_price'),
            ],
            'clientProjects' => $client->projects()->orderBy('name')->pluck('name', 'id'),
            'defaults' => [
                'start_date' => Dates::today(),
                'task_date' => Dates::today(),
                'hourly_rate' => $client->default_hourly_rate ?: AppSettings::defaultHourlyRate(),
                'currency' => $client->currency ?: AppSettings::defaultCurrency(),
            ],
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validateClient($request));

        return back()->with('success', 'Klijent je ažuriran.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Klijent je obrisan.');
    }

    protected function validateClient(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', array_keys(Options::CLIENT_STATUSES))],
            'default_hourly_rate' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'in:'.implode(',', Options::CURRENCIES)],
        ]);
    }
}
