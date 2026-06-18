<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Http\Resources\AttachmentResource;
use App\Support\AttachmentUpload;
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
        $validated = $request->validate([
            'type' => ['required', Rule::in(['task', 'project'])],
            'id' => ['required', 'integer', 'min:1'],
            'file' => ['required_without:external_path', 'file', 'max:20480'],
            'external_path' => ['required_without:file', 'string', 'min:3', 'max:2048'],
            'label' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(Options::ATTACHMENT_CATEGORIES))],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $modelClass = $this->resolveType($validated['type']);
        $modelClass::findOrFail($validated['id']);

        if ($request->hasFile('file')) {
            $attachment = $this->storeUploadedFile($request, $validated, $modelClass);
            $message = 'Fajl je uploadovan.';
        } else {
            $attachment = $this->storeExternalPath($validated, $modelClass);
            $message = 'Putanja je sačuvana.';
        }

        return response()->json([
            'attachment' => new AttachmentResource($attachment),
            'message' => $message,
        ], 201);
    }

    protected function storeUploadedFile(Request $request, array $validated, string $modelClass): Attachment
    {
        $file = $request->file('file');
        AttachmentUpload::assertAllowedExtension($file);

        $folder = 'attachments/'.Str::of($modelClass)->afterLast('\\')->lower().'/'.$validated['id'];
        $stored = $file->store($folder, 'public');

        return Attachment::create([
            'attachable_type' => $modelClass,
            'attachable_id' => $validated['id'],
            'kind' => 'upload',
            'filename' => basename($stored),
            'original_name' => $file->getClientOriginalName(),
            'path' => $stored,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
        ]);
    }

    protected function storeExternalPath(array $validated, string $modelClass): Attachment
    {
        $path = trim($validated['external_path']);

        return Attachment::create([
            'attachable_type' => $modelClass,
            'attachable_id' => $validated['id'],
            'kind' => 'link',
            'filename' => '',
            'original_name' => AttachmentUpload::originalNameFromPath($path, $validated['label'] ?? null),
            'path' => '',
            'external_path' => $path,
            'mime_type' => null,
            'size' => 0,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
        ]);
    }

    public function destroy(Attachment $attachment): JsonResponse
    {
        $attachment->delete();

        return response()->json(['message' => 'Fajl je obrisan.']);
    }
}
