<?php

namespace App\Services\Uploads;

class LocalUploader
{
    public function __construct(private readonly \Illuminate\Filesystem\FilesystemManager $filesystemManager) {}

    public function upload($file, $folder): array
    {
        $path = $this->filesystemManager->disk('public')->putFile($folder, $file);

        if (! $path) {
            throw new \Exception('Failed to upload to local disk.');
        }

        return [
            'url' => $this->filesystemManager->disk('public')->url($path),
            'public_id' => $path,
        ];
    }

    public function delete($publicId): void
    {
        if (! $this->filesystemManager->disk('public')->delete($publicId)) {
            throw new \Exception('Failed to delete from local disk.');
        }
    }
}
