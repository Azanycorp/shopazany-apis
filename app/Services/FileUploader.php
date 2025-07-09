<?php

namespace App\Services;

use App\Services\Uploads\ImageKitUploader;
use App\Services\Uploads\LocalUploader;
use App\Services\Uploads\S3Uploader;

class FileUploader
{
    protected $providers = [];

    public function __construct()
    {
        foreach (config('fileservices.providers') as $providerConfig) {
            $providerName = $providerConfig['name'];
            $retries = $providerConfig['retries'];

            switch ($providerName) {
                case 'imagekit':
                    $this->providers[] = [
                        'instance' => new ImageKitUploader,
                        'name' => 'ImageKit',
                        'retries' => $retries,
                    ];
                    break;
                case 's3':
                    $this->providers[] = [
                        'instance' => new S3Uploader,
                        'name' => 'S3',
                        'retries' => $retries,
                    ];
                    break;
                case 'local':
                    $this->providers[] = [
                        'instance' => new LocalUploader,
                        'name' => 'Local',
                        'retries' => $retries,
                    ];
                    break;

                default: break;
            }
        }
    }

    public function upload($file, $folder)
    {
        foreach ($this->providers as $provider) {
            $attempts = 0;
            while ($attempts < $provider['retries']) {
                try {
                    return $provider['instance']->upload($file, $folder);
                } catch (\Throwable $e) {
                    $attempts++;
                    logger()->warning("Upload failed on {$provider['name']} attempt {$attempts}/{$provider['retries']}: ".$e->getMessage());
                }
            }
            logger()->info("All retries exhausted for {$provider['name']}, moving to next provider.");
        }

        throw new \Exception('All providers failed after retries.');
    }

    public function deleteFile($publicId): void
    {
        if (! $publicId) {
            return;
        }

        foreach ($this->providers as $provider) {
            try {
                $provider['instance']->delete($publicId);
                logger()->info("Deleted file from {$provider['name']} using ID: {$publicId}");

                return;
            } catch (\Throwable $e) {
                logger()->warning("Delete failed on {$provider['name']}: ".$e->getMessage());
            }
        }

        logger()->error("Failed to delete file: {$publicId} on all providers.");
    }
}
