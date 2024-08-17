<?php

namespace App\Http\Controllers;

use App\Actions\UserLogAction;
use App\Exports\ProductExport;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

abstract class Controller
{
    use HttpResponse;

    public function generateUniqueReferrerCode()
    {
        do {
            $referrer_code = Str::random(10);
        } while (User::where('referrer_code', $referrer_code)->exists());

        return $referrer_code;
    }

    public function generateAlternateReferrerCode()
    {
        return strrev(Str::random(6) . rand(4, 9876));
    }

    public function getUserReferrer($user)
    {
        if($user->referrer_code !== null){
            return $this->error(null, 'Account has been created', 400);
        }
    }

    protected function userAuth()
    {
        return Auth::user();
    }

    public function logUserAction($request, $action, $description, $response, $user = null)
    {
        (new UserLogAction($request, $action, $description, $response, $user))->run();
    }

    protected function getStorageFolder(string $email): string
    {
        if (App::environment('production')) {
            return "/prod/document/{$email}";
        }

        return "/stag/document/{$email}";
    }

    protected function storeFile($file, string $folder): string
    {
        $path = $file->store($folder, 's3');
        return Storage::disk('s3')->url($path);
    }

    protected function exportProduct($userId)
    {
        $fileName = 'products_' . time() . '.xlsx';
        $path = 'public';

        if(App::environment('production')) {
            $folderPath = 'prod/exports/' . 'user_'. $userId . '/';
            $fileName = $folderPath . 'products_' . time() . '.xlsx';
            $path = 's3';

        } elseif(App::environment('staging')) {
            $folderPath = 'stag/exports/' . 'user_'. $userId . '/';
            $fileName = $folderPath . 'products_' . time() . '.xlsx';
            $path = 's3';
        }

        Excel::store(new ProductExport($userId), $fileName, $path);

        $fileUrl = ($path === 's3') ? Storage::disk('s3')->url($fileName) : asset('storage/' . $fileName);

        return $this->success(['file_url' => $fileUrl], "Product export successful.");
    }

    protected function addBankTransfer($request, User $user): bool
    {
        try {
            $user->paymentMethods()->create([
                'type' => $request->type,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name,
                'swift' => $request->swift,
                'bank_branch' => $request->bank_branch
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function addPayPal($request, User $user): bool
    {
        try {
            $user->paymentMethods()->create([
                'type' => $request->type,
                'paypal_email' => $request->paypal_email
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
