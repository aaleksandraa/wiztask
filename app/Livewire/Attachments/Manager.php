<?php

namespace App\Livewire\Attachments;

use App\Models\Attachment;
use App\Support\AttachmentUpload;
use App\Support\Options;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Manager extends Component
{
    use WithFileUploads;

    public string $attachableType;
    public int $attachableId;
    public string $title = 'Fajlovi i slike';

    public array $files = [];
    public string $category = 'ostalo';
    public string $description = '';
    public string $external_path = '';
    public string $path_label = '';

    public function updatedFiles(): void
    {
        $this->validate([
            'files.*' => ['file', 'max:20480'],
        ], [], ['files.*' => 'fajl']);

        foreach ($this->files as $file) {
            AttachmentUpload::assertAllowedExtension($file);
        }
    }

    public function upload(): void
    {
        $this->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:20480'],
            'category' => ['required', 'in:'.implode(',', array_keys(Options::ATTACHMENT_CATEGORIES))],
        ], [], ['files.*' => 'fajl']);

        $folder = 'attachments/'.Str::of($this->attachableType)->afterLast('\\')->lower().'/'.$this->attachableId;

        foreach ($this->files as $file) {
            AttachmentUpload::assertAllowedExtension($file);
            $stored = $file->store($folder, 'public');

            Attachment::create([
                'attachable_type' => $this->attachableType,
                'attachable_id' => $this->attachableId,
                'kind' => 'upload',
                'filename' => basename($stored),
                'original_name' => $file->getClientOriginalName(),
                'path' => $stored,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'category' => $this->category,
                'description' => $this->description ?: null,
            ]);
        }

        $this->reset('files', 'description');
        $this->dispatch('attachments-updated');
        session()->flash('success', 'Fajlovi su dodani.');
    }

    public function addExternalPath(): void
    {
        $this->validate([
            'external_path' => ['required', 'string', 'min:3', 'max:2048'],
            'path_label' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'in:'.implode(',', array_keys(Options::ATTACHMENT_CATEGORIES))],
        ]);

        $path = trim($this->external_path);

        Attachment::create([
            'attachable_type' => $this->attachableType,
            'attachable_id' => $this->attachableId,
            'kind' => 'link',
            'filename' => '',
            'original_name' => AttachmentUpload::originalNameFromPath($path, $this->path_label ?: null),
            'path' => '',
            'external_path' => $path,
            'mime_type' => null,
            'size' => 0,
            'category' => $this->category,
            'description' => $this->description ?: null,
        ]);

        $this->reset('external_path', 'path_label', 'description');
        $this->dispatch('attachments-updated');
        session()->flash('success', 'Putanja je sačuvana.');
    }

    public function deleteAttachment(int $id): void
    {
        $attachment = Attachment::where('attachable_type', $this->attachableType)
            ->where('attachable_id', $this->attachableId)
            ->findOrFail($id);

        $attachment->delete();
        $this->dispatch('attachments-updated');
        session()->flash('success', 'Fajl je obrisan.');
    }

    public function render()
    {
        $attachments = Attachment::where('attachable_type', $this->attachableType)
            ->where('attachable_id', $this->attachableId)
            ->latest()
            ->get();

        return view('livewire.attachments.manager', [
            'attachments' => $attachments,
            'categories' => Options::ATTACHMENT_CATEGORIES,
        ]);
    }
}
