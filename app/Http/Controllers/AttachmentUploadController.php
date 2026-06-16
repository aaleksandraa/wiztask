<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Http\Resources\AttachmentResource;
use App\Support\AppSettings;
use App\Support\Options;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttachmentUploadController extends Controller
{
    protected function resolveType(string $type): string
    {
        return match ($type) {
            'task' => Task::class,
            'project' => Project::class,
            default => abort(422, 'Nepoznat tip entiteta.'),
        };
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['task', 'project'])],
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $modelClass = $this->resolveType($validated['type']);
        $modelClass::findOrFail($validated['id']);

        $attachments = Attachment::where('attachable_type', $modelClass)
            ->where('attachable_id', $validated['id'])
            ->latest()
            ->get();

        return response()->json([
            'attachments' => AttachmentResource::collection($attachments),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $allowed = AppSettings::allowedFileTypes();

        $validated = $request->validate([
            'type' => ['required', Rule::in(['task', 'project'])],
            'id' => ['required', 'integer', 'min:1'],
            'file' => ['required', 'file', 'max:20480', 'mimes:'.implode(',', $allowed)],
            'category' => ['required', Rule::in(array_keys(Options::ATTACHMENT_CATEGORIES))],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $modelClass = $this->resolveType($validated['type']);
        $modelClass::findOrFail($validated['id']);

        $folder = 'attachments/'.Str::of($modelClass)->afterLast('\\')->lower().'/'.$validated['id'];
        $file = $request->file('file');
        $stored = $file->store($folder, 'public');

        $attachment = Attachment::create([
            'attachable_type' => $modelClass,
            'attachable_id' => $validated['id'],
            'filename' => basename($stored),
            'original_name' => $file->getClientOriginalName(),
            'path' => $stored,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'attachment' => new AttachmentResource($attachment),
            'message' => 'Fajl je uploadovan.',
        ], 201);
    }

    public function destroy(Attachment $attachment): JsonResponse
    {
        $attachment->delete();

        return response()->json(['message' => 'Fajl je obrisan.']);
    }
}
