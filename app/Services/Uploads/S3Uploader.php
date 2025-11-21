<?php

namespace App\Services\Uploads;

class S3Uploader
{
    public function __construct(private readonly \Illuminate\Filesystem\FilesystemManager $filesystemManager) {}

    public function upload($file, $folder): array
    {
        $path = $this->filesystemManager->disk('s3')->putFile($folder, $file);

        if (! $path) {
            throw new \Exception('Failed to upload to S3.');
        }

        return [
            'url' => $this->filesystemManager->disk('s3')->url($path),
            'public_id' => $path,
        ];
    }

    public function delete($publicId): void
    {
        if (! $this->filesystemManager->disk('s3')->delete($publicId)) {
            throw new \Exception('Failed to delete from S3.');
        }
    }
}
