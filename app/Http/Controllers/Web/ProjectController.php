<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\ModelPresenter;
use App\Support\Money;
use App\Support\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = Project::query()
            ->with('client:id,name,currency')
            ->withCount('tasks')
            ->when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->when($request->client_id, fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->billing_type, fn ($q) => $q->where('billing_type', $request->billing_type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => ModelPresenter::paginatedProjects($projects),
            'filters' => $request->only(['q', 'client_id', 'status', 'billing_type']),
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'defaults' => [
                'currency' => AppSettings::defaultCurrency(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProject($request);
        $data = Dates::fillForSave($data, ['start_date', 'due_date']);
        Project::create($data);

        return back()->with('success', 'Projekat je dodan.');
    }

    public function show(Project $project): Response
    {
        $project->load('client');

        $tasks = $project->tasks()->with('client')->latest()->get()
            ->map(fn ($t) => ModelPresenter::task($t));

        return Inertia::render('Projects/Show', [
            'project' => ModelPresenter::project($project, true),
            'tasks' => $tasks,
            'stats' => [
                'totalMinutes' => Money::minutesToHuman($project->totalMinutes()),
                'totalValue' => Money::format($project->totalValue(), $project->currency),
            ],
            'attachments' => ModelPresenter::attachmentsFor(Project::class, $project->id),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validateProject($request);
        $data = Dates::fillForSave($data, ['start_date', 'due_date']);
        $project->update($data);

        return back()->with('success', 'Projekat je ažuriran.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Projekat je obrisan.');
    }

    protected function validateProject(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', array_keys(Options::PROJECT_STATUSES))],
            'start_date' => Dates::rule(),
            'due_date' => Dates::rule(),
            'billing_type' => ['required', 'in:'.implode(',', array_keys(Options::PROJECT_BILLING_TYPES))],
            'fixed_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'in:'.implode(',', Options::CURRENCIES)],
            'note' => ['nullable', 'string'],
        ]);
    }
}
