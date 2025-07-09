<?php

namespace App\Services\Uploads;

use Illuminate\Support\Facades\Storage;

class LocalUploader
{
    public function upload($file, $folder): array
    {
        $path = Storage::disk('public')->putFile($folder, $file);

        if (! $path) {
            throw new \Exception('Failed to upload to local disk.');
        }

        return [
            'url' => Storage::disk('public')->url($path),
            'public_id' => $path,
        ];
    }

    public function delete($publicId): void
    {
        if (! Storage::disk('public')->delete($publicId)) {
            throw new \Exception('Failed to delete from local disk.');
        }
    }
}
