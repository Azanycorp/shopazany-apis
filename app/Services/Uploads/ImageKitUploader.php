<?php

namespace App\Services\Uploads;

use ImageKit\ImageKit;

class ImageKitUploader
{
    public function __construct(private readonly \Illuminate\Contracts\Config\Repository $repository) {}

    public function upload($file, $folder): array
    {
        $imageKit = new ImageKit(
            $this->repository->get('services.imagekit.public_key'),
            $this->repository->get('services.imagekit.private_key'),
            $this->repository->get('services.imagekit.endpoint_key')
        );

        $uploadFile = fopen($file->getRealPath(), 'r');

        $uploadResponse = $imageKit->upload([
            'file' => $uploadFile,
            'fileName' => $file->getClientOriginalName(),
            'folder' => $folder,
        ]);

        if (! isset($uploadResponse->result->url)) {
            throw new \Exception('No URL returned from ImageKit.');
        }

        return [
            'url' => $uploadResponse->result->url,
            'public_id' => $uploadResponse->result->fileId,
        ];
    }

    public function delete($publicId): void
    {
        $imageKit = new ImageKit(
            $this->repository->get('services.imagekit.public_key'),
            $this->repository->get('services.imagekit.private_key'),
            $this->repository->get('services.imagekit.endpoint_key')
        );

        $result = $imageKit->deleteFile($publicId);

        if ($result->result === null || $result->result !== 'success') {
            throw new \Exception('Failed to delete file from ImageKit.');
        }
    }
}
