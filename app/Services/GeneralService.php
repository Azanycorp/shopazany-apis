<?php

namespace App\Services;

use App\Exports\B2BProductExport;
use App\Exports\ProductExport;
use App\Trait\HttpResponse;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Application;
use Illuminate\Routing\UrlGenerator;
use Maatwebsite\Excel\Facades\Excel;

class GeneralService
{
    use HttpResponse;

    public function __construct(
        private readonly Application $application,
        private readonly FilesystemManager $filesystemManager,
        private readonly UrlGenerator $urlGenerator,
    ) {}

    public function exportProduct(string $userId)
    {
        $fileName = 'products_'.time().'.xlsx';
        $path = 'public';

        if ($this->application->environment('production')) {
            $folderPath = 'prod/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';

        } elseif ($this->application->environment('staging')) {
            $folderPath = 'stag/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';
        }

        Excel::store(new ProductExport($userId), $fileName, $path);

        $fileUrl = ($path === 's3') ?
            $this->filesystemManager->disk('s3')->url($fileName) :
            $this->urlGenerator->asset('storage/'.$fileName);

        return $this->success(['file_url' => $fileUrl], 'Product export successful.');
    }

    public function exportB2bProduct(string $userId, $data)
    {
        $fileName = 'products_'.time().'.xlsx';
        $path = 'public';

        if ($this->application->environment('production')) {
            $folderPath = 'prod/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';

        } elseif ($this->application->environment('staging')) {
            $folderPath = 'stag/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';
        }

        Excel::store(new B2BProductExport($userId, $data), $fileName, $path);

        $fileUrl = ($path === 's3') ?
            $this->filesystemManager->disk('s3')->url($fileName) :
            $this->urlGenerator->asset('storage/'.$fileName);

        return $this->success(['file_url' => $fileUrl], 'Product export successful.');
    }

    public function b2bExportProduct(string $userId)
    {
        $fileName = 'products_'.time().'.xlsx';
        $path = 'public';

        if ($this->application->environment('production')) {
            $folderPath = 'prod/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';

        } elseif ($this->application->environment('staging')) {
            $folderPath = 'stag/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';
        }

        $data = null;
        Excel::store(new B2BProductExport($userId, $data), $fileName, $path);
        $fileUrl = ($path === 's3') ? $this->filesystemManager->disk('s3')->url($fileName) : $this->urlGenerator->asset('storage/'.$fileName);

        return $this->success(['file_url' => $fileUrl], 'Product export successful.');
    }

    public function getStorageFolder(string $email): string
    {
        if ($this->application->environment('production')) {
            return "/prod/document/{$email}";
        }

        return "/stag/document/{$email}";
    }
}
