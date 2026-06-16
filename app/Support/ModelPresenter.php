<?php

namespace App\Support;

use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ModelPresenter
{
    public static function client(Client $client, bool $detail = false): array
    {
        $data = [
            'id' => $client->id,
            'name' => $client->name,
            'contact_person' => $client->contact_person,
            'email' => $client->email,
            'phone' => $client->phone,
            'website' => $client->website,
            'city' => $client->city,
            'country' => $client->country,
            'address' => $client->address,
            'note' => $client->note,
            'status' => $client->status,
            'default_hourly_rate' => (float) $client->default_hourly_rate,
            'currency' => $client->currency,
            'projects_count' => $client->projects_count ?? null,
            'tasks_count' => $client->tasks_count ?? null,
        ];

        return $data;
    }

    public static function project(Project $project, bool $detail = false): array
    {
        return [
            'id' => $project->id,
            'client_id' => $project->client_id,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status,
            'start_date' => Dates::toInput($project->start_date),
            'start_date_display' => Dates::formatOr($project->start_date),
            'due_date' => Dates::toInput($project->due_date),
            'due_date_display' => Dates::formatOr($project->due_date),
            'billing_type' => $project->billing_type,
            'fixed_price' => (float) $project->fixed_price,
            'currency' => $project->currency,
            'note' => $project->note,
            'tasks_count' => $project->tasks_count ?? null,
            'client' => $project->relationLoaded('client') && $project->client
                ? ['id' => $project->client->id, 'name' => $project->client->name, 'currency' => $project->client->currency]
                : null,
        ];
    }

    public static function task(Task $task, bool $detail = false): array
    {
        $data = [
            'id' => $task->id,
            'client_id' => $task->client_id,
            'project_id' => $task->project_id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'task_date' => Dates::toInput($task->task_date),
            'task_date_display' => Dates::formatOr($task->task_date),
            'due_date' => Dates::toInput($task->due_date),
            'due_date_display' => Dates::formatOr($task->due_date),
            'billing_type' => $task->billing_type,
            'hourly_rate' => (float) $task->hourly_rate,
            'fixed_price' => (float) $task->fixed_price,
            'total_price' => (float) $task->total_price,
            'is_billable' => (bool) $task->is_billable,
            'payment_status' => $task->payment_status,
            'internal_note' => $task->internal_note,
            'archived_at' => $task->archived_at?->toISOString(),
            'logged_minutes' => isset($task->logged_minutes) ? (int) $task->logged_minutes : null,
            'total_minutes' => $detail ? $task->totalMinutes() : null,
            'client' => $task->relationLoaded('client') && $task->client
                ? ['id' => $task->client->id, 'name' => $task->client->name, 'currency' => $task->client->currency, 'default_hourly_rate' => (float) $task->client->default_hourly_rate]
                : null,
            'project' => $task->relationLoaded('project') && $task->project
                ? ['id' => $task->project->id, 'name' => $task->project->name]
                : null,
        ];

        return $data;
    }

    public static function timeEntry(TimeEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'client_id' => $entry->client_id,
            'project_id' => $entry->project_id,
            'task_id' => $entry->task_id,
            'work_date' => Dates::toInput($entry->work_date),
            'work_date_display' => Dates::formatOr($entry->work_date),
            'description' => $entry->description,
            'hours' => $entry->hours,
            'minutes' => $entry->minutes,
            'total_minutes' => $entry->total_minutes,
            'hourly_rate' => (float) $entry->hourly_rate,
            'total_price' => (float) $entry->total_price,
            'is_billable' => (bool) $entry->is_billable,
            'client' => $entry->relationLoaded('client') && $entry->client
                ? ['id' => $entry->client->id, 'name' => $entry->client->name, 'currency' => $entry->client->currency]
                : null,
            'task' => $entry->relationLoaded('task') && $entry->task
                ? ['id' => $entry->task->id, 'title' => $entry->task->title]
                : null,
        ];
    }

    /** @return array<string, mixed> */
    public static function paginatedClients(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => collect($paginator->items())->map(fn (Client $c) => self::client($c))->values(),
            'links' => $paginator->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function paginatedTasks(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => collect($paginator->items())->map(fn (Task $t) => self::task($t))->values(),
            'links' => $paginator->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function paginatedProjects(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => collect($paginator->items())->map(fn (Project $p) => self::project($p))->values(),
            'links' => $paginator->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function paginatedTimeEntries(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => collect($paginator->items())->map(fn (TimeEntry $e) => self::timeEntry($e))->values(),
            'links' => $paginator->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public static function attachmentsFor(string $type, int $id): array
    {
        return AttachmentResource::collection(
            Attachment::where('attachable_type', $type)
                ->where('attachable_id', $id)
                ->latest()
                ->get()
        )->resolve();
    }
}
