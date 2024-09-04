<?php

use App\Actions\UserLogAction;
use App\Models\Upload;
use App\Models\Language;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\BusinessSetting;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\RewardPoint\RewardService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


if (!function_exists('reward_user')) {
    function reward_user($user, $actionName, $status)
    {
        $rewardService = app(RewardService::class);
        return $rewardService->rewardUser($user, $actionName, $status);
    }
}

if (!function_exists('log_user_activity')) {
    function log_user_activity($user, $action, $status, $description = null)
    {
        UserActivityLog::logAction($user, $action, $status, $description);
    }
}

if (!function_exists('userAuth')) {
    function userAuth() {
        return auth()->user();
    }
}

if (!function_exists('getSetting')) {
    function getSetting($key, $default = null, $lang = false)
    {
        $settings = Cache::remember('business_settings', 86400, function () {
            return BusinessSetting::all();
        });

        if ($lang === false) {
            $setting = $settings->where('type', $key)->first();
        } else {
            $setting = $settings->where('type', $key)->where('lang', $lang)->first();
            $setting = !$setting ? $settings->where('type', $key)->first() : $setting;
        }
        return $setting == null ? $default : $setting->value;
    }
}

if (!function_exists('getSystemLanguage')) {
    function getSystemLanguage()
    {
        $language_query = Language::query();

        $locale = 'en';
        if (Session::has('locale')) {
            $locale = Session::get('locale', Config::get('app.locale'));
        }

        $language_query->where('code',  $locale);

        return $language_query->first();
    }
}

if (!function_exists('getSliderImages')) {
    function getSliderImages($ids)
    {
        $slider_query = Upload::query();
        return $slider_query->whereIn('id', $ids)->get('file_name');
    }
}

if(!function_exists('getImportTemplate')){
    function getImportTemplate()
    {
        if (App::environment('production')){

            $data = "https://azany-uploads.s3.amazonaws.com/prod/product-template/product-template.xlsx";

        } elseif (App::environment(['local', 'staging'])) {

            $data = "https://azany-uploads.s3.amazonaws.com/stag/product-template/product-template.xlsx";
        }

        return $data;
    }
}

if(!function_exists('getRelativePath')){
    function getRelativePath($url) {
        return parse_url($url, PHP_URL_PATH);
    }
}

if(!function_exists('uploadSingleProductImage')){
    function uploadSingleProductImage($request, $file, $frontImage, $product)
    {
        if ($request->hasFile($file)) {

            if (!empty($product->image)) {
                $image = getRelativePath($product->image);

                if (Storage::disk('s3')->exists($image)) {
                    Storage::disk('s3')->delete($image);
                }
            }

            $fileSize = $request->file($file)->getSize();
            if ($fileSize > 3000000) {
                return json_encode(["status" => false, "message" => "file size is larger than 3MB.", "status_code" => 422]);
            }

            $path = $request->file($file)->store($frontImage, 's3');
            $url = Storage::disk('s3')->url($path);
        } else {
            $url = $product->image;
        }

        return $url;
    }
}

if(!function_exists('uploadImage')){
    function uploadImage($request, $file, $folder, $country = null)
    {
        $url = $country?->image;

        if ($request->hasFile($file)) {
            $fileSize = $request->file($file)->getSize();
            if ($fileSize > 3000000) {
                return json_encode(["status" => false, "message" => "File size is larger than 3MB.", "status_code" => 422]);
            }

            $image = $country?->image ? getRelativePath($country->image) : null;

            if ($image && Storage::disk('s3')->exists($image)) {
                Storage::disk('s3')->delete($image);
            }

            $path = $request->file($file)->store($folder, 's3');
            $url = Storage::disk('s3')->url($path);
        }

        return $url;
    }
}

if(!function_exists('uploadMultipleProductImage')){
    function uploadMultipleProductImage($request, $file, $folder, $product)
    {
        if ($request->hasFile($file)) {
            $product->productimages()->delete();

            foreach ($request->file($file) as $image) {
                $path = $image->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);

                $product->productimages()->create([
                    'image' => $url,
                ]);
            }
        }
    }
}

if(!function_exists('uploadUserImage')){
    function uploadUserImage($request, $file, $user)
    {
        $folder = null;

        $parts = explode('@', $user->email);
        $name = $parts[0];

        if(App::environment('production')){
            $folder = "/prod/profile/{$name}";
        } elseif(App::environment(['staging', 'local'])) {
            $folder = "/stag/profile/{$name}";
        }

        if ($request->hasFile($file)) {

            if (!empty($user->image)) {
                $image = getRelativePath($user->image);

                if (Storage::disk('s3')->exists($image)) {
                    Storage::disk('s3')->delete($image);
                }
            }

            $fileSize = $request->file($file)->getSize();
            if ($fileSize > 3000000) {
                return json_encode(["status" => false, "message" => "file size is larger than 3MB.", "status_code" => 422]);
            }

            $path = $request->file($file)->store($folder, 's3');
            $url = Storage::disk('s3')->url($path);
        } else {
            $url = $user->image;
        }

        return $url;
    }
}

if(!function_exists('generateTransactionReference')) {
    function generateTransactionReference()
    {
        do {
            $reference = 'TXN' . strtoupper(Str::random(8)) . time();
        } while (Transaction::where('reference', $reference)->exists());

        return $reference;
    }
}

if(!function_exists('logUserAction')) {
    function logUserAction($request, $action, $description, $response, $user = null)
    {
        (new UserLogAction($request, $action, $description, $response, $user))->run();
    }
}

if(!function_exists('generateVerificationCode')) {
    function generateVerificationCode()
    {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}

if(!function_exists('generate_referral_code')) {
    function generate_referral_code()
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (User::where('referrer_code', $code)->exists());

        return $code;
    }
}

if(!function_exists('generate_referrer_link')) {
    function generate_referrer_link($referrer_code)
    {
        if(App::environment('production')) {
            $url = config('services.frontend_baseurl') . '/register?referrer=' . $referrer_code;
        } else {
            $url = config('services.staging_frontend_baseurl') . '/register?referrer=' . $referrer_code;
        }

        return $url;
    }
}











