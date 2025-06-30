<?php

namespace App\Services\Uploads;

use Illuminate\Support\Facades\Storage;

class S3Uploader
{
    public function upload($file, $folder)
    {
        $path = Storage::disk('s3')->putFile($folder, $file);

        if (! $path) {
            throw new \Exception('Failed to upload to S3.');
        }

        return [
            'url' => Storage::disk('s3')->url($path),
            'public_id' => $path,
        ];
    }

    public function delete($publicId)
    {
        if (! Storage::disk('s3')->delete($publicId)) {
            throw new \Exception('Failed to delete from S3.');
        }
    }
}
