<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Support\AppSettings;
use App\Support\Dates;
use App\Support\ModelPresenter;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TimeEntryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TimeEntry::query()
            ->with(['client', 'task'])
            ->when($request->client_id, fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->date_from, fn ($q) => $q->whereDate('work_date', '>=', Dates::toDatabase($request->date_from)))
            ->when($request->date_to, fn ($q) => $q->whereDate('work_date', '<=', Dates::toDatabase($request->date_to)))
            ->when($request->filled('is_billable'), fn ($q) => $q->where('is_billable', $request->boolean('is_billable')));

        $sumMinutes = (int) (clone $query)->sum('total_minutes');
        $sumPrice = (float) (clone $query)->sum('total_price');

        $entries = $query->latest('work_date')->latest('id')->paginate(20)->withQueryString();

        return Inertia::render('Time/Index', [
            'entries' => ModelPresenter::paginatedTimeEntries($entries),
            'filters' => $request->only(['client_id', 'date_from', 'date_to', 'is_billable']),
            'summary' => [
                'minutes' => Money::minutesToHuman($sumMinutes),
                'price' => Money::format($sumPrice),
            ],
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
            'tasks' => Task::orderBy('title')->limit(200)->pluck('title', 'id'),
            'defaults' => [
                'work_date' => Dates::today(),
                'hourly_rate' => AppSettings::defaultHourlyRate(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
            'work_date' => Dates::rule(required: true),
            'description' => ['nullable', 'string'],
            'hours' => ['required', 'integer', 'min:0', 'max:999'],
            'minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'is_billable' => ['boolean'],
        ]);
        $data = Dates::fillForSave($data, ['work_date']);
        $task = Task::findOrFail($data['task_id']);
        $data['client_id'] = $task->client_id;
        $data['project_id'] = $task->project_id;

        TimeEntry::create($data);

        return back()->with('success', 'Vrijeme je dodano.');
    }

    public function update(Request $request, TimeEntry $timeEntry): RedirectResponse
    {
        $data = $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
            'work_date' => Dates::rule(required: true),
            'description' => ['nullable', 'string'],
            'hours' => ['required', 'integer', 'min:0', 'max:999'],
            'minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'is_billable' => ['boolean'],
        ]);
        $data = Dates::fillForSave($data, ['work_date']);
        $task = Task::findOrFail($data['task_id']);
        $data['client_id'] = $task->client_id;
        $data['project_id'] = $task->project_id;
        $timeEntry->update($data);

        return back()->with('success', 'Unos vremena je ažuriran.');
    }

    public function destroy(TimeEntry $timeEntry): RedirectResponse
    {
        $timeEntry->delete();

        return back()->with('success', 'Unos vremena je obrisan.');
    }
}
