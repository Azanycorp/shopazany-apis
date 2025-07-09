<?php

use App\Actions\UserLogAction;
use App\Models\Action;
use App\Models\B2BRequestRefund;
use App\Models\BusinessSetting;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Mailing;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\RewardPointSetting;
use App\Models\Transaction;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\FileUploader;
use App\Services\RewardPoint\RewardService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('total_amount')) {
    function total_amount($unit_price, $moq): int|float
    {
        return $unit_price * $moq;
    }
}

if (! function_exists('reward_user')) {
    function reward_user($user, $actionName, $status, $newUser = null)
    {
        $rewardService = app(RewardService::class);

        return $rewardService->rewardUser($user, $actionName, $status, $newUser);
    }
}

if (! function_exists('log_user_activity')) {
    function log_user_activity($user, $action, $status, $description = null): void
    {
        UserActivityLog::logAction($user, $action, $status, $description);
    }
}

if (! function_exists('userAuth')) {
    function userAuth()
    {
        return auth()->user();
    }
}

if (! function_exists('userAuthId')) {
    function userAuthId()
    {
        return auth()->id();
    }
}

if (! function_exists('getSetting')) {
    function getSetting($key, $default = null, $lang = false)
    {
        $settings = Cache::remember('business_settings', 86400, function () {
            return BusinessSetting::all();
        });

        if ($lang === false) {
            $setting = $settings->where('type', $key)->first();
        } else {
            $setting = $settings->where('type', $key)->where('lang', $lang)->first();
            $setting = $setting ? $setting : $settings->where('type', $key)->first();
        }

        return $setting == null ? $default : $setting->value;
    }
}

if (! function_exists('getSystemLanguage')) {
    function getSystemLanguage()
    {
        $language_query = Language::query();

        $locale = 'en';
        if (Session::has('locale')) {
            $locale = Session::get('locale', Config::get('app.locale'));
        }

        $language_query->where('code', $locale);

        return $language_query->first();
    }
}

if (! function_exists('getSliderImages')) {
    function getSliderImages($ids)
    {
        $slider_query = Upload::query();

        return $slider_query->whereIn('id', $ids)->get('file_name');
    }
}

if (! function_exists('getImportTemplate')) {
    function getImportTemplate(): string
    {
        if (App::environment('production')) {
            return 'https://azany-uploads.s3.amazonaws.com/prod/product-template/product-template.xlsx';
        }

        return 'https://azany-uploads.s3.amazonaws.com/stag/product-template/product-template.xlsx';
    }
}

if (! function_exists('getB2BProductTemplate')) {
    function getB2BProductTemplate(): string
    {
        if (App::environment('production')) {
            return 'https://azany-uploads.s3.us-east-1.amazonaws.com/prod/product-template/b2b/seller-product-template.xlsx';
        }

        return 'https://azany-uploads.s3.us-east-1.amazonaws.com/stag/product-template/b2b/seller-product-template.xlsx';
    }
}

if (! function_exists('getRelativePath')) {
    function getRelativePath($url): string|false|null
    {
        return parse_url($url, PHP_URL_PATH);
    }
}

if (! function_exists('getFolderPrefix')) {
    function getFolderPrefix(): string
    {
        $segments = request()->segments();

        if (in_array('b2b', $segments)) {
            return 'b2b';
        }

        return 'b2c';
    }
}

if (! function_exists('uploadImageFile')) {
    function uploadImageFile($file, $folder = 'uploads')
    {
        $prefix = getFolderPrefix();
        $folderName = ltrim($folder, '/');
        $fullFolder = "{$prefix}/{$folderName}";

        $uploader = app(FileUploader::class);

        return $uploader->upload($file, $fullFolder);
    }
}
if (! function_exists('uploadSingleProductImage')) {
    function uploadSingleProductImage($request, $file, $folder, $product)
    {
        if ($request->hasFile($file)) {
            if (! empty($product->public_id)) {
                app(FileUploader::class)->deleteFile($product->public_id);
            }

            $fileSize = $request->file($file)->getSize();
            if ($fileSize > 3000000) {
                return json_encode(['status' => false, 'message' => 'file size is larger than 3MB.', 'status_code' => 422]);
            }

            $upload = uploadImageFile($request->file($file), $folder);

            return [
                'url' => $upload['url'],
                'public_id' => $upload['public_id'],
            ];
        }

        return [
            'url' => $product->image,
            'public_id' => $product->public_id,
        ];
    }
}

if (! function_exists('uploadImage')) {
    function uploadImage($request, $file, $folder, $country = null, $banner = null)
    {
        $response = [
            'url' => null,
            'public_id' => null,
        ];

        if (! is_null($country)) {
            $response['url'] = $country->image;
            $response['public_id'] = $country->public_id;
        }

        if (! is_null($banner)) {
            $response['url'] = $banner->image;
            $response['public_id'] = $banner->public_id;
        }

        if ($request->hasFile($file)) {
            $fileSize = $request->file($file)->getSize();

            if ($fileSize > 3000000) {
                return json_encode([
                    'status' => false,
                    'message' => 'File size is larger than 3MB.',
                    'status_code' => 422,
                ]);
            }

            if (! is_null($country) && ! empty($country->public_id)) {
                app(FileUploader::class)->deleteFile($country->public_id);
            }

            if (! is_null($banner) && ! empty($banner->public_id)) {
                app(FileUploader::class)->deleteFile($banner->public_id);
            }

            $upload = uploadImageFile($request->file($file), $folder);
            $response = $upload;
        }

        return $response;
    }
}

if (! function_exists('uploadMultipleProductImage')) {
    function uploadMultipleProductImage($request, $file, $folder, $product): void
    {
        if ($request->hasFile($file)) {
            foreach ($request->file($file) as $image) {
                $upload = uploadImageFile($image, $folder);

                $product->productimages()->create([
                    'image' => $upload['url'],
                    'public_id' => $upload['public_id'],
                ]);
            }
        }
    }
}

if (! function_exists('uploadMultipleB2BProductImage')) {
    function uploadMultipleB2BProductImage($request, $file, $folder, $product): void
    {
        if ($request->hasFile($file)) {
            foreach ($request->file($file) as $image) {
                $upload = uploadImageFile($image, $folder);

                $product->b2bProductImages()->create([
                    'image' => $upload['url'],
                    'public_id' => $upload['public_id'],
                ]);
            }
        }
    }
}

if (! function_exists('uploadFunction')) {
    function uploadFunction($file, $folder, $model = null): array
    {
        if ($file->getSize() > 3000000) {
            abort(422, 'File size is larger than 3MB.');
        }

        if (! is_null($model) && ! empty($model->public_id)) {
            app(FileUploader::class)->deleteFile($model->public_id);
        }

        $upload = uploadImageFile($file, $folder);

        return [
            'url' => $upload['url'],
            'public_id' => $upload['public_id'],
        ];
    }
}

if (! function_exists('deleteFile')) {
    function deleteFile($model): void
    {
        if (! is_null($model) && ! empty($model->public_id)) {
            app(FileUploader::class)->deleteFile($model->public_id);
        }
    }
}

if (! function_exists('uploadUserImage')) {
    function uploadUserImage($request, $file, $user)
    {
        $parts = explode('@', $user->email);
        $name = $parts[0];

        $folder = folderName('profile')."/{$name}";

        if (! is_null($user) && ! empty($user->public_id)) {
            app(FileUploader::class)->deleteFile($user->public_id);
        }

        $fileSize = $request->file($file)->getSize();
        if ($fileSize > 3000000) {
            return json_encode(['status' => false, 'message' => 'file size is larger than 3MB.', 'status_code' => 422]);
        }

        $upload = uploadImageFile($request->file($file), $folder);

        return [
            'url' => $upload['url'],
            'public_id' => $upload['public_id'],
        ];
    }
}

// ////// Deprecated upload functions ////////////
if (! function_exists('uploadSingleProductImageOld')) {
    function uploadSingleProductImageOld($request, $file, $frontImage, $product)
    {
        if ($request->hasFile($file)) {
            if (! empty($product->image)) {
                $image = getRelativePath($product->image);

                if (Storage::disk('s3')->exists($image)) {
                    Storage::disk('s3')->delete($image);
                }
            }
            $fileSize = $request->file($file)->getSize();
            if ($fileSize > 3000000) {
                return json_encode(['status' => false, 'message' => 'file size is larger than 3MB.', 'status_code' => 422]);
            }
            $path = $request->file($file)->store($frontImage, 's3');

            return Storage::disk('s3')->url($path);
        }

        return $product->image;
    }
}

if (! function_exists('uploadImageOld')) {
    function uploadImageOld($request, $file, $folder, $country = null, $banner = null)
    {
        $url = null;

        if (! is_null($country)) {
            $url = $country->image;
        }

        if (! is_null($banner)) {
            $url = $banner->image;
        }

        if ($request->hasFile($file)) {
            $fileSize = $request->file($file)->getSize();

            if ($fileSize > 3000000) {
                return json_encode([
                    'status' => false,
                    'message' => 'File size is larger than 3MB.',
                    'status_code' => 422,
                ]);
            }

            $existingImage = $country?->image ? getRelativePath($country->image) : null;
            $existingBanner = $banner?->image ? getRelativePath($banner->image) : null;

            if ($existingImage && Storage::disk('s3')->exists($existingImage)) {
                Storage::disk('s3')->delete($existingImage);
            }

            if ($existingBanner && Storage::disk('s3')->exists($existingBanner)) {
                Storage::disk('s3')->delete($existingBanner);
            }

            $path = $request->file($file)->store($folder, 's3');
            $url = Storage::disk('s3')->url($path);
        }

        return $url;
    }
}

if (! function_exists('uploadMultipleProductImageOld')) {
    function uploadMultipleProductImageOld($request, $file, $folder, $product): void
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

if (! function_exists('uploadUserImageOld')) {
    function uploadUserImageOld($request, $file, $user)
    {
        $folder = null;

        $parts = explode('@', $user->email);
        $name = $parts[0];

        if (App::environment('production')) {
            $folder = "/prod/profile/{$name}";
        } elseif (App::environment(['staging', 'local'])) {
            $folder = "/stag/profile/{$name}";
        }

        if ($request->hasFile($file)) {
            if (! empty($user->image)) {
                $image = getRelativePath($user->image);

                if (Storage::disk('s3')->exists($image)) {
                    Storage::disk('s3')->delete($image);
                }
            }
            $fileSize = $request->file($file)->getSize();
            if ($fileSize > 3000000) {
                return json_encode(['status' => false, 'message' => 'file size is larger than 3MB.', 'status_code' => 422]);
            }
            $path = $request->file($file)->store($folder, 's3');

            return Storage::disk('s3')->url($path);
        }

        return $user->image;
    }
}

// ////// Ends here /////////

if (! function_exists('generateTransactionReference')) {
    function generateTransactionReference(): string
    {
        do {
            $reference = 'TXN'.strtoupper(Str::random(8)).time();
        } while (Transaction::where('reference', $reference)->exists());

        return $reference;
    }
}

if (! function_exists('logUserAction')) {
    function logUserAction($request, $action, $description, $response, $user = null): void
    {
        (new UserLogAction($request, $action, $description, $response, $user))->run();
    }
}

if (! function_exists('generateVerificationCode')) {
    function generateVerificationCode(): string
    {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('generateRefCode')) {
    function generateRefCode(): string
    {
        return 'AZY-'.sprintf('%06d', mt_rand(1, 999999));
    }
}

if (! function_exists('generate_referral_code')) {
    function generate_referral_code(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (User::where('referrer_code', $code)->exists());

        return $code;
    }
}

if (! function_exists('generate_referrer_link')) {
    function generate_referrer_link(string $referrer_code): string
    {
        if (App::environment('production')) {
            return config('services.frontend.seller_baseurl').'?referrer='.$referrer_code;
        }

        return config('services.frontend.staging_seller_baseurl').'?referrer='.$referrer_code;
    }
}

if (! function_exists('generate_referrer_links')) {
    function generate_referrer_links(string $referrer_code): array
    {
        $environment = app()->environment();

        $baseUrls = [
            'production' => [
                'b2c' => config('services.frontend.seller_baseurl'),
                'b2b' => config('services.frontend.b2b_baseurl'),
                'agriecom' => config('services.frontend.agriecom_baseurl'),
            ],
            'staging' => [
                'b2c' => config('services.frontend.staging_seller_baseurl'),
                'b2b' => config('services.frontend.b2b_staging_baseurl'),
                'agriecom' => config('services.frontend.agricom_staging_baseurl'),
            ],
        ];

        $selectedBaseUrls = in_array($environment, ['local', 'staging'])
            ? $baseUrls['staging']
            : ($baseUrls[$environment] ?? $baseUrls['staging']);

        return array_map(fn ($key, $url): array => [
            'name' => $key,
            'link' => $url.'?referrer='.$referrer_code,
        ], array_keys($selectedBaseUrls), $selectedBaseUrls);
    }
}

if (! function_exists('send_email')) {
    function send_email($email, $action): void
    {
        Mail::to($email)->send($action);
    }
}

if (! function_exists('generateRandomString')) {
    function generateRandomString($length = 15): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}

if (! function_exists('abbreviateNumber')) {
    function abbreviateNumber($number)
    {
        if ($number >= 1000000000) {
            return number_format($number / 1000000000, 1).'B';
        }
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1).'M';
        }
        if ($number >= 1000) {
            return number_format($number / 1000, 1).'K';
        }

        return $number;
    }
}

if (! function_exists('folderName')) {
    function folderName($name)
    {
        return match (App::environment()) {
            'production' => "/prod/{$name}",
            'staging', 'local' => "/stag/{$name}",
            default => null,
        };
    }
}

if (! function_exists('folderNames')) {
    function folderNames(string $folderName, string $user, ?string $subFolder = null, ?string $anotherFolder = null): object
    {
        $envPrefix = match (App::environment()) {
            'production' => 'prod',
            'staging', 'local' => 'stag',
            default => 'dev',
        };

        $basePath = "/{$envPrefix}/{$folderName}/{$user}";

        $fullFolder = $basePath;
        if ($anotherFolder !== null && $anotherFolder !== '' && $anotherFolder !== '0') {
            $fullFolder .= "/{$anotherFolder}";
        }

        return (object) [
            'folder' => $fullFolder,
            'frontImage' => "{$basePath}/{$subFolder}",
        ];
    }
}

if (! function_exists('getCurrencyCode')) {
    function getCurrencyCode($code): string
    {
        $countries = config('countries');

        return $countries[$code]['currencyCode'] ?? 'USD';
    }
}

if (! function_exists('generateRefundComplaintNumber')) {
    function generateRefundComplaintNumber(string $prefix = 'RFC'): string
    {
        $uniqueNumber = $prefix.'-'.strtoupper(Str::random(8)).'-'.time();

        while (B2BRequestRefund::where('complaint_number', $uniqueNumber)->exists()) {
            $uniqueNumber = $prefix.'-'.strtoupper(Str::random(8)).'-'.time();
        }

        return $uniqueNumber;
    }
}

if (! function_exists('orderNo')) {
    function orderNo(): string
    {
        $timestamp = now()->timestamp;
        $randomNumber = mt_rand(100000, 999999);

        $uniqueOrderNumber = 'ORD-'.$timestamp.'-'.$randomNumber;

        while (Order::where('order_no', $uniqueOrderNumber)->exists()) {
            $randomNumber = mt_rand(100000, 999999);
            $uniqueOrderNumber = 'ORD-'.$timestamp.'-'.$randomNumber;
        }

        return $uniqueOrderNumber;
    }
}

if (! function_exists('currencyConvert')) {
    function currencyConvert($from, $amount, $to = null): float
    {
        static $rates = [];

        $from = $from ?? 'USD';

        if ($to === null || $from === $to) {
            return round($amount, 2);
        }

        $cacheKey = "{$from}_to_{$to}";
        if (! isset($rates[$cacheKey])) {
            $rates[$cacheKey] = Cache::remember($cacheKey, now()->addHours(24), function () use ($from, $to): int|float {
                $fromRate = Currency::where('code', $from)->value('exchange_rate');
                $toRate = Currency::where('code', $to)->value('exchange_rate');

                if (! $fromRate || ! $toRate) {
                    throw new Exception("Currency rate not found for '{$from}' or '{$to}'.");
                }

                return $toRate / $fromRate;
            });
        }

        return round($amount * $rates[$cacheKey], 2);
    }
}

if (! function_exists('currencyConvertTo')) {
    function currencyConvertTo($amount, $to): float
    {
        static $rates = [];

        $to = $to ?? 'USD';

        $cacheKey = "USD_to_{$to}";
        if (! isset($rates[$cacheKey])) {
            $rates[$cacheKey] = Cache::remember($cacheKey, now()->addHours(24), function () use ($to): int|float {
                $toRate = Currency::where('code', $to)->value('exchange_rate');

                if (! $toRate || $toRate <= 0) {
                    throw new Exception("Currency rate not found or invalid for '{$to}'.");
                }

                return $toRate;
            });
        }

        return $to === 'USD'
            ? round($amount / $rates[$cacheKey], 2)
            : round($amount * $rates[$cacheKey], 2);
    }
}

if (! function_exists('mailSend')) {
    function mailSend($type, $recipient, $subject, $mail_class, $payloadData = []): void
    {
        $data = [
            'type' => $type,
            'email' => $recipient->email,
            'subject' => $subject,
            'body' => '',
            'mailable' => $mail_class,
            'scheduled_at' => now(),
            'payload' => array_merge($payloadData),
        ];

        Mailing::saveData($data);
    }
}

if (! function_exists('currencyCodeByCountryId')) {
    function currencyCodeByCountryId($countryId): string
    {
        $currencyCode = 'NGN';
        if ($countryId) {
            $country = Country::findOrFail($countryId);
            $currencyCode = getCurrencyCode($country->sortname);
        }

        return $currencyCode;
    }
}

if (! function_exists('logOrderActivity')) {
    function logOrderActivity($orderId, $message, $status): void
    {
        OrderActivity::updateOrCreate(
            [
                'order_id' => $orderId,
                'status' => $status,
            ],
            [
                'message' => $message,
                'status' => $status,
                'date' => now(),
            ]
        );
    }
}

if (! function_exists('getOrderStatusMessage')) {
    function getOrderStatusMessage(string $status): string
    {
        $statusMessages = [
            'confirmed' => 'Your order has been confirmed.',
            'cancelled' => 'Unfortunately, your order has been cancelled.',
            'delivered' => 'Great news! Your order has been delivered successfully.',
            'completed' => 'Your order has been completed. Thank you for shopping with us!',
            'pending' => 'Your order is currently pending. We will update you soon.',
            'processing' => 'Your order is being processed. Please wait while we prepare it.',
            'in-progress' => 'Your order is in progress. Our team is working on it.',
            'review' => 'Your order is under review. We will notify you once it’s approved.',
            'shipped' => 'Your order has been shipped and is on its way!',
            'paid' => 'Payment received! Your order will be processed shortly.',
        ];

        return $statusMessages[$status] ?? 'Your order status has been updated.';
    }
}

if (! function_exists('getRewards')) {
    function getRewards($countryId)
    {
        $id = (int) $countryId;

        return Action::whereJsonContains('country_ids', $id)
            ->select('id', 'name', 'description', 'icon', 'verification_type', 'points')
            ->get();
    }
}

if (! function_exists('userRewards')) {
    function userRewards($userId)
    {
        $user = User::with(['userActions' => function ($query): void {
            $query->select('id', 'user_id', 'action_id', 'points')
                ->with('action:id,name,icon,points');
        }])->findOrFail($userId);

        $userCurrency = $user->default_currency ?? 'USD';

        return $user->userActions->map(function ($action) use ($userCurrency) {
            $action->value = pointConvert($action->points, $userCurrency);
            $action->currency = $userCurrency;

            return $action;
        });
    }
}

if (! function_exists('pointConvert')) {
    function pointConvert($point, $to): float
    {
        $usdSetting = RewardPointSetting::where('currency', 'USD')->first();
        if (! $usdSetting) {
            throw new Exception('Reward point setting for USD not found.');
        }

        $usdValue = ($point * $usdSetting->value) / $usdSetting->point;
        $convertedValue = currencyConvert('USD', $usdValue, $to);

        return round($convertedValue, 2);
    }
}

if (! function_exists('amountToPoint')) {
    function amountToPoint($amount, $currency): float
    {
        $usdSetting = RewardPointSetting::where('currency', 'USD')->first();
        if (! $usdSetting) {
            throw new Exception('Reward point setting for USD not found.');
        }

        $usdValue = currencyConvert($currency, $amount, 'USD');
        $points = ($usdValue * $usdSetting->point) / $usdSetting->value;

        return round($points);
    }
}
