<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type', 'attachable_id', 'kind', 'filename', 'original_name',
        'path', 'external_path', 'mime_type', 'size', 'category', 'description',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Attachment $attachment) {
            if ($attachment->isLink() || $attachment->path === '' || $attachment->path === null) {
                return;
            }

            Storage::disk('public')->delete($attachment->path);
        });
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isLink(): bool
    {
        return $this->kind === 'link';
    }

    public function isImage(): bool
    {
        if ($this->isLink()) {
            return false;
        }

        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function url(): ?string
    {
        if ($this->isLink() || $this->path === '' || $this->path === null) {
            return null;
        }

        return '/storage/'.str_replace('\\', '/', $this->path);
    }

    public function humanSize(): string
    {
        if ($this->isLink()) {
            return 'Putanja';
        }

        $bytes = (int) $this->size;
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i === 0 ? 0 : 1).' '.$units[$i];
    }
}
