<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\ModelPresenter;
use App\Support\Money;
use App\Support\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $tasks = Task::query()
            ->with(['client', 'project'])
            ->withSum('timeEntries as logged_minutes', 'total_minutes')
            ->when($request->boolean('showArchived'), fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->when($request->q, fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', "%{$request->q}%")->orWhere('description', 'like', "%{$request->q}%")))
            ->when($request->client_id, fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->project_id, fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->billing_type, fn ($q) => $q->where('billing_type', $request->billing_type))
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('is_billable'), fn ($q) => $q->where('is_billable', $request->boolean('is_billable')))
            ->when($request->date_from, fn ($q) => $q->whereDate('task_date', '>=', Dates::toDatabase($request->date_from)))
            ->when($request->date_to, fn ($q) => $q->whereDate('task_date', '<=', Dates::toDatabase($request->date_to)))
            ->when($request->month, fn ($q) => $q->whereMonth('task_date', $request->month))
            ->when($request->year, fn ($q) => $q->whereYear('task_date', $request->year))
            ->latest('task_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Tasks/Index', [
            'tasks' => ModelPresenter::paginatedTasks($tasks),
            'filters' => $request->only(['q', 'client_id', 'project_id', 'status', 'priority', 'billing_type', 'payment_status', 'is_billable', 'date_from', 'date_to', 'month', 'year', 'showArchived']),
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'filterProjects' => $request->client_id
                ? Project::where('client_id', $request->client_id)->orderBy('name')->pluck('name', 'id')
                : [],
            'defaults' => [
                'task_date' => Dates::today(),
                'hourly_rate' => AppSettings::defaultHourlyRate(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTask($request);
        $hours = (int) ($data['hours'] ?? 0);
        $minutes = (int) ($data['minutes'] ?? 0);
        unset($data['hours'], $data['minutes']);
        $data['project_id'] = $data['project_id'] ?: null;
        $data = Dates::fillForSave($data, ['task_date', 'due_date']);

        $task = Task::create($data);
        $this->createTimeEntryIfNeeded($task, $hours, $minutes);
        $task->recalcTotalPrice();

        if ($request->boolean('redirect_show')) {
            return redirect()->route('tasks.show', $task)->with('success', 'Task je dodan.');
        }

        return back()->with('success', 'Task je dodan.');
    }

    public function show(Task $task): Response
    {
        $task->load('client', 'project');

        $timeEntries = $task->timeEntries()->latest('work_date')->get()
            ->map(fn ($e) => ModelPresenter::timeEntry($e));

        return Inertia::render('Tasks/Show', [
            'task' => ModelPresenter::task($task, true),
            'timeEntries' => $timeEntries,
            'stats' => [
                'totalMinutes' => Money::minutesToHuman($task->totalMinutes()),
                'totalPrice' => Money::format($task->total_price, $task->client->currency),
            ],
            'attachments' => ModelPresenter::attachmentsFor(Task::class, $task->id),
            'defaults' => [
                'work_date' => Dates::today(),
                'hourly_rate' => $task->hourly_rate ?: $task->client->default_hourly_rate,
            ],
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $data = $this->validateTask($request);
        $hours = (int) ($data['hours'] ?? 0);
        $minutes = (int) ($data['minutes'] ?? 0);
        unset($data['hours'], $data['minutes']);
        $data['project_id'] = $data['project_id'] ?: null;
        $data = Dates::fillForSave($data, ['task_date', 'due_date']);
        $task->update($data);
        $this->createTimeEntryIfNeeded($task, $hours, $minutes);
        $task->recalcTotalPrice();

        return back()->with('success', 'Task je ažuriran.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task je obrisan.');
    }

    public function duplicate(Task $task): RedirectResponse
    {
        $copy = $task->replicate(['total_price', 'archived_at']);
        $copy->title = $task->title.' (kopija)';
        $copy->status = 'novo';
        $copy->payment_status = 'za_naplatu';
        $copy->total_price = $task->billing_type === 'fiksno' ? $task->fixed_price : 0;
        $copy->archived_at = null;
        $copy->save();

        return back()->with('success', 'Task je dupliciran.');
    }

    public function toggleArchive(Task $task): RedirectResponse
    {
        $task->archived_at = $task->archived_at ? null : now();
        $task->saveQuietly();

        return back()->with('success', $task->archived_at ? 'Task je arhiviran.' : 'Task je vraćen iz arhive.');
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:'.implode(',', array_keys(Options::TASK_STATUSES))]]);
        $task->update(['status' => $request->status]);

        return back()->with('success', 'Status je promijenjen.');
    }

    public function updatePaymentStatus(Request $request, Task $task): RedirectResponse
    {
        $request->validate(['payment_status' => ['required', 'in:'.implode(',', array_keys(Options::PAYMENT_STATUSES))]]);
        $task->update(['payment_status' => $request->payment_status]);

        return back()->with('success', 'Status plaćanja je promijenjen.');
    }

    public function storeTime(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate([
            'work_date' => Dates::rule(required: true),
            'description' => ['nullable', 'string'],
            'hours' => ['required', 'integer', 'min:0', 'max:999'],
            'minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'is_billable' => ['boolean'],
        ]);
        $data = Dates::fillForSave($data, ['work_date']);
        $data['client_id'] = $task->client_id;
        $data['project_id'] = $task->project_id;
        $data['task_id'] = $task->id;

        TimeEntry::create($data);

        return back()->with('success', 'Vrijeme je dodano.');
    }

    public function updateTime(Request $request, Task $task, TimeEntry $timeEntry): RedirectResponse
    {
        abort_unless($timeEntry->task_id === $task->id, 404);

        $data = $request->validate([
            'work_date' => Dates::rule(required: true),
            'description' => ['nullable', 'string'],
            'hours' => ['required', 'integer', 'min:0', 'max:999'],
            'minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'is_billable' => ['boolean'],
        ]);
        $data = Dates::fillForSave($data, ['work_date']);
        $timeEntry->update($data);

        return back()->with('success', 'Unos vremena je ažuriran.');
    }

    public function destroyTime(Task $task, TimeEntry $timeEntry): RedirectResponse
    {
        abort_unless($timeEntry->task_id === $task->id, 404);
        $timeEntry->delete();

        return back()->with('success', 'Unos vremena je obrisan.');
    }

    protected function validateTask(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', array_keys(Options::TASK_STATUSES))],
            'priority' => ['required', 'in:'.implode(',', array_keys(Options::TASK_PRIORITIES))],
            'task_date' => Dates::rule(),
            'due_date' => Dates::rule(),
            'billing_type' => ['required', 'in:'.implode(',', array_keys(Options::TASK_BILLING_TYPES))],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'fixed_price' => ['required', 'numeric', 'min:0'],
            'is_billable' => ['boolean'],
            'payment_status' => ['required', 'in:'.implode(',', array_keys(Options::PAYMENT_STATUSES))],
            'internal_note' => ['nullable', 'string'],
            'hours' => ['nullable', 'integer', 'min:0', 'max:999'],
            'minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
        ]);
    }

    protected function createTimeEntryIfNeeded(Task $task, int $hours, int $minutes): void
    {
        if ($task->billing_type !== 'po_satu' || ($hours === 0 && $minutes === 0)) {
            return;
        }

        TimeEntry::create([
            'client_id' => $task->client_id,
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'work_date' => $task->task_date ?? now(),
            'description' => null,
            'hours' => $hours,
            'minutes' => $minutes,
            'hourly_rate' => $task->hourly_rate,
            'is_billable' => $task->is_billable,
        ]);
    }
}
