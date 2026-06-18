<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class AttachmentUpload
{
    public static function assertAllowedExtension(UploadedFile $file): void
    {
        if (AppSettings::allowsAllFileTypes()) {
            return;
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());

        if ($extension === '' || ! AppSettings::isAllowedExtension($extension)) {
            throw ValidationException::withMessages([
                'file' => 'Tip fajla nije dozvoljen. Dozvoljene ekstenzije: '.implode(', ', AppSettings::allowedFileTypes()).'.',
            ]);
        }
    }

    public static function originalNameFromPath(string $path, ?string $label = null): string
    {
        if ($label !== null && trim($label) !== '') {
            return trim($label);
        }

        $normalized = str_replace('\\', '/', trim($path));

        return basename($normalized) ?: trim($path);
    }
}
