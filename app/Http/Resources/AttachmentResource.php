<?php

namespace App\Http\Resources;

use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Attachment */
class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'url' => $this->url(),
            'download_url' => route('attachments.download', $this->id),
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'human_size' => $this->humanSize(),
            'is_image' => $this->isImage(),
            'category' => $this->category,
            'description' => $this->description,
            'created_at' => Dates::formatOr($this->created_at),
        ];
    }
}
