<?php

use App\Actions\UserLogAction;
use App\Models\B2BRequestRefund;
use App\Models\Upload;
use App\Models\Language;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\BusinessSetting;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Mailing;
use App\Models\Order;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\RewardPoint\RewardService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


if (!function_exists('total_amount')) {
    function total_amount($unit_price, $moq): int|float
    {
        return $unit_price * $moq;
    }
}

if (!function_exists('reward_user')) {
    function reward_user($user, $actionName, $status, $newUser = null)
    {
        $rewardService = app(RewardService::class);
        return $rewardService->rewardUser($user, $actionName, $status, $newUser);
    }
}

if (!function_exists('log_user_activity')) {
    function log_user_activity($user, $action, $status, $description = null): void
    {
        UserActivityLog::logAction($user, $action, $status, $description);
    }
}

if (!function_exists('userAuth')) {
    function userAuth() {
        return auth()->user();
    }
}

if (!function_exists('userAuthId')) {
    function userAuthId() {
        return auth()->id();
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
            $setting = $setting ? $setting : $settings->where('type', $key)->first();
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
  function getImportTemplate(): string
  {
    if (App::environment('production')){
      return "https://azany-uploads.s3.amazonaws.com/prod/product-template/product-template.xlsx";
    }
    return "https://azany-uploads.s3.amazonaws.com/stag/product-template/product-template.xlsx";;
  }
}

if(!function_exists('getB2BProductTemplate')){
  function getB2BProductTemplate(): string
  {
    if (App::environment('production')){
        return "https://azany-uploads.s3.us-east-1.amazonaws.com/prod/product-template/b2b/seller-product-template.xlsx";
    }
    return "https://azany-uploads.s3.us-east-1.amazonaws.com/stag/product-template/b2b/seller-product-template.xlsx";
  }
}

if(!function_exists('getRelativePath')){
    function getRelativePath($url): string|false|null {
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
            return Storage::disk('s3')->url($path);
        }

        return $product->image;
    }
}

if (!function_exists('uploadImage')) {
    function uploadImage($request, $file, $folder, $country = null, $banner = null)
    {
        $url = null;

        if (!is_null($country)) {
            $url = $country->image;
        }

        if (!is_null($banner)) {
            $url = $banner->image;
        }

        if ($request->hasFile($file)) {
            $fileSize = $request->file($file)->getSize();

            if ($fileSize > 3000000) {
                return json_encode([
                    "status" => false,
                    "message" => "File size is larger than 3MB.",
                    "status_code" => 422
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

if(!function_exists('uploadMultipleProductImage')){
    function uploadMultipleProductImage($request, $file, $folder, $product): void
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
            return Storage::disk('s3')->url($path);
        }

        return $user->image;
    }
}

if(!function_exists('generateTransactionReference')) {
    function generateTransactionReference(): string
    {
        do {
            $reference = 'TXN' . strtoupper(Str::random(8)) . time();
        } while (Transaction::where('reference', $reference)->exists());

        return $reference;
    }
}

if(!function_exists('logUserAction')) {
    function logUserAction($request, $action, $description, $response, $user = null): void
    {
        (new UserLogAction($request, $action, $description, $response, $user))->run();
    }
}

if(!function_exists('generateVerificationCode')) {
    function generateVerificationCode(): string
    {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}

if(!function_exists('generateRefCode')) {
    function generateRefCode(): string
    {
        return 'AZY-' . sprintf("%06d", mt_rand(1, 999999));
    }
}

if(!function_exists('generate_referral_code')) {
    function generate_referral_code(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (User::where('referrer_code', $code)->exists());

        return $code;
    }
}

if(!function_exists('generate_referrer_link')) {
    function generate_referrer_link(string $referrer_code): string {
        if (App::environment('production')) {
            return config('services.frontend.seller_baseurl') . '?referrer=' . $referrer_code;
        }
        return config('services.frontend.staging_seller_baseurl') . '?referrer=' . $referrer_code;
    }
}

if (!function_exists('generate_referrer_links')) {
    function generate_referrer_links(string $referrer_code): array {
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

        return array_map(fn ($key, $url) => [
            'name' => $key,
            'link' => $url . '?referrer=' . $referrer_code
        ], array_keys($selectedBaseUrls), $selectedBaseUrls);
    }
}

if(!function_exists('send_email')) {
    function send_email($email, $action): void {
        Mail::to($email)->send($action);
    }
}

if (!function_exists('generateRandomString')) {
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

if (!function_exists('abbreviateNumber')) {
    function abbreviateNumber($number)
    {
        if ($number >= 1000000000) {
            return number_format($number / 1000000000, 1) . 'B';
        }
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1) . 'M';
        }
        if ($number >= 1000) {
            return number_format($number / 1000, 1) . 'K';
        }

        return $number;
    }
}

if (!function_exists('folderName')) {
    function folderName($name) {
        $environment = App::environment();
        return match ($environment) {
            'production' => "/prod/{$name}",
            'staging', 'local' => "/stag/{$name}",
            default => null,
        };
    }
}

if (!function_exists('folderNames')) {
    function folderNames($folderName, $user, $subFolder) {
        if(App::environment('production')) {
            $folder = "/prod/{$folderName}/{$user}";
            $frontImage = "/prod/{$folderName}/{$user}/{$subFolder}";
        } elseif(App::environment(['staging', 'local'])) {
            $folder = "/stag/{$folderName}/{$user}";
            $frontImage = "/stag/{$folderName}/{$user}/{$subFolder}";
        }

        return (object)[
            'folder' => $folder,
            'frontImage' => $frontImage
        ];
    }
}

if (!function_exists('getCurrencyCode')) {
    function getCurrencyCode($code): string|int {
        $countries = [
            'AD' =>
            [
              'countryName' => 'Andorra',
              'currencyCode' => 'EUR',
              'population' => 84000,
              'capital' => 'Andorra la Vella',
              'continentName' => 'Europe',
            ],
            'AE' =>
            [
              'countryName' => 'United Arab Emirates',
              'currencyCode' => 'AED',
              'population' => 4975593,
              'capital' => 'Abu Dhabi',
              'continentName' => 'Asia',
            ],
            'AF' =>
            [
              'countryName' => 'Afghanistan',
              'currencyCode' => 'AFN',
              'population' => 29121286,
              'capital' => 'Kabul',
              'continentName' => 'Asia',
            ],
            'AG' =>
            [
              'countryName' => 'Antigua and Barbuda',
              'currencyCode' => 'XCD',
              'population' => 86754,
              'capital' => 'St. John\'s',
              'continentName' => 'North America',
            ],
            'AI' =>
            [
              'countryName' => 'Anguilla',
              'currencyCode' => 'XCD',
              'population' => 13254,
              'capital' => 'The Valley',
              'continentName' => 'North America',
            ],
            'AL' =>
            [
              'countryName' => 'Albania',
              'currencyCode' => 'ALL',
              'population' => 2986952,
              'capital' => 'Tirana',
              'continentName' => 'Europe',
            ],
            'AM' =>
            [
              'countryName' => 'Armenia',
              'currencyCode' => 'AMD',
              'population' => 2968000,
              'capital' => 'Yerevan',
              'continentName' => 'Asia',
            ],
            'AO' =>
            [
              'countryName' => 'Angola',
              'currencyCode' => 'AOA',
              'population' => 13068161,
              'capital' => 'Luanda',
              'continentName' => 'Africa',
            ],
            'AQ' =>
            [
              'countryName' => 'Antarctica',
              'currencyCode' => '',
              'population' => 0,
              'capital' => '',
              'continentName' => 'Antarctica',
            ],
            'AR' =>
            [
              'countryName' => 'Argentina',
              'currencyCode' => 'ARS',
              'population' => 41343201,
              'capital' => 'Buenos Aires',
              'continentName' => 'South America',
            ],
            'AS' =>
            [
              'countryName' => 'American Samoa',
              'currencyCode' => 'USD',
              'population' => 57881,
              'capital' => 'Pago Pago',
              'continentName' => 'Oceania',
            ],
            'AT' =>
            [
              'countryName' => 'Austria',
              'currencyCode' => 'EUR',
              'population' => 8205000,
              'capital' => 'Vienna',
              'continentName' => 'Europe',
            ],
            'AU' =>
            [
              'countryName' => 'Australia',
              'currencyCode' => 'AUD',
              'population' => 21515754,
              'capital' => 'Canberra',
              'continentName' => 'Oceania',
            ],
            'AW' =>
            [
              'countryName' => 'Aruba',
              'currencyCode' => 'AWG',
              'population' => 71566,
              'capital' => 'Oranjestad',
              'continentName' => 'North America',
            ],
            'AX' =>
            [
              'countryName' => 'Åland',
              'currencyCode' => 'EUR',
              'population' => 26711,
              'capital' => 'Mariehamn',
              'continentName' => 'Europe',
            ],
            'AZ' =>
            [
              'countryName' => 'Azerbaijan',
              'currencyCode' => 'AZN',
              'population' => 8303512,
              'capital' => 'Baku',
              'continentName' => 'Asia',
            ],
            'BA' =>
            [
              'countryName' => 'Bosnia and Herzegovina',
              'currencyCode' => 'BAM',
              'population' => 4590000,
              'capital' => 'Sarajevo',
              'continentName' => 'Europe',
            ],
            'BB' =>
            [
              'countryName' => 'Barbados',
              'currencyCode' => 'BBD',
              'population' => 285653,
              'capital' => 'Bridgetown',
              'continentName' => 'North America',
            ],
            'BD' =>
            [
              'countryName' => 'Bangladesh',
              'currencyCode' => 'BDT',
              'population' => 156118464,
              'capital' => 'Dhaka',
              'continentName' => 'Asia',
            ],
            'BE' =>
            [
              'countryName' => 'Belgium',
              'currencyCode' => 'EUR',
              'population' => 10403000,
              'capital' => 'Brussels',
              'continentName' => 'Europe',
            ],
            'BF' =>
            [
              'countryName' => 'Burkina Faso',
              'currencyCode' => 'XOF',
              'population' => 16241811,
              'capital' => 'Ouagadougou',
              'continentName' => 'Africa',
            ],
            'BG' =>
            [
              'countryName' => 'Bulgaria',
              'currencyCode' => 'BGN',
              'population' => 7148785,
              'capital' => 'Sofia',
              'continentName' => 'Europe',
            ],
            'BH' =>
            [
              'countryName' => 'Bahrain',
              'currencyCode' => 'BHD',
              'population' => 738004,
              'capital' => 'Manama',
              'continentName' => 'Asia',
            ],
            'BI' =>
            [
              'countryName' => 'Burundi',
              'currencyCode' => 'BIF',
              'population' => 9863117,
              'capital' => 'Bujumbura',
              'continentName' => 'Africa',
            ],
            'BJ' =>
            [
              'countryName' => 'Benin',
              'currencyCode' => 'XOF',
              'population' => 9056010,
              'capital' => 'Porto-Novo',
              'continentName' => 'Africa',
            ],
            'BL' =>
            [
              'countryName' => 'Saint Barthélemy',
              'currencyCode' => 'EUR',
              'population' => 8450,
              'capital' => 'Gustavia',
              'continentName' => 'North America',
            ],
            'BM' =>
            [
              'countryName' => 'Bermuda',
              'currencyCode' => 'BMD',
              'population' => 65365,
              'capital' => 'Hamilton',
              'continentName' => 'North America',
            ],
            'BN' =>
            [
              'countryName' => 'Brunei',
              'currencyCode' => 'BND',
              'population' => 395027,
              'capital' => 'Bandar Seri Begawan',
              'continentName' => 'Asia',
            ],
            'BO' =>
            [
              'countryName' => 'Bolivia',
              'currencyCode' => 'BOB',
              'population' => 9947418,
              'capital' => 'Sucre',
              'continentName' => 'South America',
            ],
            'BQ' =>
            [
              'countryName' => 'Bonaire',
              'currencyCode' => 'USD',
              'population' => 18012,
              'capital' => 'Kralendijk',
              'continentName' => 'North America',
            ],
            'BR' =>
            [
              'countryName' => 'Brazil',
              'currencyCode' => 'BRL',
              'population' => 201103330,
              'capital' => 'Brasília',
              'continentName' => 'South America',
            ],
            'BS' =>
            [
              'countryName' => 'Bahamas',
              'currencyCode' => 'BSD',
              'population' => 301790,
              'capital' => 'Nassau',
              'continentName' => 'North America',
            ],
            'BT' =>
            [
              'countryName' => 'Bhutan',
              'currencyCode' => 'BTN',
              'population' => 699847,
              'capital' => 'Thimphu',
              'continentName' => 'Asia',
            ],
            'BV' =>
            [
              'countryName' => 'Bouvet Island',
              'currencyCode' => 'NOK',
              'population' => 0,
              'capital' => '',
              'continentName' => 'Antarctica',
            ],
            'BW' =>
            [
              'countryName' => 'Botswana',
              'currencyCode' => 'BWP',
              'population' => 2029307,
              'capital' => 'Gaborone',
              'continentName' => 'Africa',
            ],
            'BY' =>
            [
              'countryName' => 'Belarus',
              'currencyCode' => 'BYR',
              'population' => 9685000,
              'capital' => 'Minsk',
              'continentName' => 'Europe',
            ],
            'BZ' =>
            [
              'countryName' => 'Belize',
              'currencyCode' => 'BZD',
              'population' => 314522,
              'capital' => 'Belmopan',
              'continentName' => 'North America',
            ],
            'CA' =>
            [
              'countryName' => 'Canada',
              'currencyCode' => 'CAD',
              'population' => 33679000,
              'capital' => 'Ottawa',
              'continentName' => 'North America',
            ],
            'CC' =>
            [
              'countryName' => 'Cocos [Keeling] Islands',
              'currencyCode' => 'AUD',
              'population' => 628,
              'capital' => 'West Island',
              'continentName' => 'Asia',
            ],
            'CD' =>
            [
              'countryName' => 'Democratic Republic of the Congo',
              'currencyCode' => 'CDF',
              'population' => 70916439,
              'capital' => 'Kinshasa',
              'continentName' => 'Africa',
            ],
            'CF' =>
            [
              'countryName' => 'Central African Republic',
              'currencyCode' => 'XAF',
              'population' => 4844927,
              'capital' => 'Bangui',
              'continentName' => 'Africa',
            ],
            'CG' =>
            [
              'countryName' => 'Republic of the Congo',
              'currencyCode' => 'XAF',
              'population' => 3039126,
              'capital' => 'Brazzaville',
              'continentName' => 'Africa',
            ],
            'CH' =>
            [
              'countryName' => 'Switzerland',
              'currencyCode' => 'CHF',
              'population' => 7581000,
              'capital' => 'Bern',
              'continentName' => 'Europe',
            ],
            'CI' =>
            [
              'countryName' => 'Ivory Coast',
              'currencyCode' => 'XOF',
              'population' => 21058798,
              'capital' => 'Yamoussoukro',
              'continentName' => 'Africa',
            ],
            'CK' =>
            [
              'countryName' => 'Cook Islands',
              'currencyCode' => 'NZD',
              'population' => 21388,
              'capital' => 'Avarua',
              'continentName' => 'Oceania',
            ],
            'CL' =>
            [
              'countryName' => 'Chile',
              'currencyCode' => 'CLP',
              'population' => 16746491,
              'capital' => 'Santiago',
              'continentName' => 'South America',
            ],
            'CM' =>
            [
              'countryName' => 'Cameroon',
              'currencyCode' => 'XAF',
              'population' => 19294149,
              'capital' => 'Yaoundé',
              'continentName' => 'Africa',
            ],
            'CN' =>
            [
              'countryName' => 'China',
              'currencyCode' => 'CNY',
              'population' => 1330044000,
              'capital' => 'Beijing',
              'continentName' => 'Asia',
            ],
            'CO' =>
            [
              'countryName' => 'Colombia',
              'currencyCode' => 'COP',
              'population' => 47790000,
              'capital' => 'Bogotá',
              'continentName' => 'South America',
            ],
            'CR' =>
            [
              'countryName' => 'Costa Rica',
              'currencyCode' => 'CRC',
              'population' => 4516220,
              'capital' => 'San José',
              'continentName' => 'North America',
            ],
            'CU' =>
            [
              'countryName' => 'Cuba',
              'currencyCode' => 'CUP',
              'population' => 11423000,
              'capital' => 'Havana',
              'continentName' => 'North America',
            ],
            'CV' =>
            [
              'countryName' => 'Cape Verde',
              'currencyCode' => 'CVE',
              'population' => 508659,
              'capital' => 'Praia',
              'continentName' => 'Africa',
            ],
            'CW' =>
            [
              'countryName' => 'Curacao',
              'currencyCode' => 'ANG',
              'population' => 141766,
              'capital' => 'Willemstad',
              'continentName' => 'North America',
            ],
            'CX' =>
            [
              'countryName' => 'Christmas Island',
              'currencyCode' => 'AUD',
              'population' => 1500,
              'capital' => 'Flying Fish Cove',
              'continentName' => 'Asia',
            ],
            'CY' =>
            [
              'countryName' => 'Cyprus',
              'currencyCode' => 'EUR',
              'population' => 1102677,
              'capital' => 'Nicosia',
              'continentName' => 'Europe',
            ],
            'CZ' =>
            [
              'countryName' => 'Czechia',
              'currencyCode' => 'CZK',
              'population' => 10476000,
              'capital' => 'Prague',
              'continentName' => 'Europe',
            ],
            'DE' =>
            [
              'countryName' => 'Germany',
              'currencyCode' => 'EUR',
              'population' => 81802257,
              'capital' => 'Berlin',
              'continentName' => 'Europe',
            ],
            'DJ' =>
            [
              'countryName' => 'Djibouti',
              'currencyCode' => 'DJF',
              'population' => 740528,
              'capital' => 'Djibouti',
              'continentName' => 'Africa',
            ],
            'DK' =>
            [
              'countryName' => 'Denmark',
              'currencyCode' => 'DKK',
              'population' => 5484000,
              'capital' => 'Copenhagen',
              'continentName' => 'Europe',
            ],
            'DM' =>
            [
              'countryName' => 'Dominica',
              'currencyCode' => 'XCD',
              'population' => 72813,
              'capital' => 'Roseau',
              'continentName' => 'North America',
            ],
            'DO' =>
            [
              'countryName' => 'Dominican Republic',
              'currencyCode' => 'DOP',
              'population' => 9823821,
              'capital' => 'Santo Domingo',
              'continentName' => 'North America',
            ],
            'DZ' =>
            [
              'countryName' => 'Algeria',
              'currencyCode' => 'DZD',
              'population' => 34586184,
              'capital' => 'Algiers',
              'continentName' => 'Africa',
            ],
            'EC' =>
            [
              'countryName' => 'Ecuador',
              'currencyCode' => 'USD',
              'population' => 14790608,
              'capital' => 'Quito',
              'continentName' => 'South America',
            ],
            'EE' =>
            [
              'countryName' => 'Estonia',
              'currencyCode' => 'EUR',
              'population' => 1291170,
              'capital' => 'Tallinn',
              'continentName' => 'Europe',
            ],
            'EG' =>
            [
              'countryName' => 'Egypt',
              'currencyCode' => 'EGP',
              'population' => 80471869,
              'capital' => 'Cairo',
              'continentName' => 'Africa',
            ],
            'EH' =>
            [
              'countryName' => 'Western Sahara',
              'currencyCode' => 'MAD',
              'population' => 273008,
              'capital' => 'Laâyoune / El Aaiún',
              'continentName' => 'Africa',
            ],
            'ER' =>
            [
              'countryName' => 'Eritrea',
              'currencyCode' => 'ERN',
              'population' => 5792984,
              'capital' => 'Asmara',
              'continentName' => 'Africa',
            ],
            'ES' =>
            [
              'countryName' => 'Spain',
              'currencyCode' => 'EUR',
              'population' => 46505963,
              'capital' => 'Madrid',
              'continentName' => 'Europe',
            ],
            'ET' =>
            [
              'countryName' => 'Ethiopia',
              'currencyCode' => 'ETB',
              'population' => 88013491,
              'capital' => 'Addis Ababa',
              'continentName' => 'Africa',
            ],
            'FI' =>
            [
              'countryName' => 'Finland',
              'currencyCode' => 'EUR',
              'population' => 5244000,
              'capital' => 'Helsinki',
              'continentName' => 'Europe',
            ],
            'FJ' =>
            [
              'countryName' => 'Fiji',
              'currencyCode' => 'FJD',
              'population' => 875983,
              'capital' => 'Suva',
              'continentName' => 'Oceania',
            ],
            'FK' =>
            [
              'countryName' => 'Falkland Islands',
              'currencyCode' => 'FKP',
              'population' => 2638,
              'capital' => 'Stanley',
              'continentName' => 'South America',
            ],
            'FM' =>
            [
              'countryName' => 'Micronesia',
              'currencyCode' => 'USD',
              'population' => 107708,
              'capital' => 'Palikir',
              'continentName' => 'Oceania',
            ],
            'FO' =>
            [
              'countryName' => 'Faroe Islands',
              'currencyCode' => 'DKK',
              'population' => 48228,
              'capital' => 'Tórshavn',
              'continentName' => 'Europe',
            ],
            'FR' =>
            [
              'countryName' => 'France',
              'currencyCode' => 'EUR',
              'population' => 64768389,
              'capital' => 'Paris',
              'continentName' => 'Europe',
            ],
            'GA' =>
            [
              'countryName' => 'Gabon',
              'currencyCode' => 'XAF',
              'population' => 1545255,
              'capital' => 'Libreville',
              'continentName' => 'Africa',
            ],
            'GB' =>
            [
              'countryName' => 'United Kingdom',
              'currencyCode' => 'GBP',
              'population' => 62348447,
              'capital' => 'London',
              'continentName' => 'Europe',
            ],
            'GD' =>
            [
              'countryName' => 'Grenada',
              'currencyCode' => 'XCD',
              'population' => 107818,
              'capital' => 'St. George\'s',
              'continentName' => 'North America',
            ],
            'GE' =>
            [
              'countryName' => 'Georgia',
              'currencyCode' => 'GEL',
              'population' => 4630000,
              'capital' => 'Tbilisi',
              'continentName' => 'Asia',
            ],
            'GF' =>
            [
              'countryName' => 'French Guiana',
              'currencyCode' => 'EUR',
              'population' => 195506,
              'capital' => 'Cayenne',
              'continentName' => 'South America',
            ],
            'GG' =>
            [
              'countryName' => 'Guernsey',
              'currencyCode' => 'GBP',
              'population' => 65228,
              'capital' => 'St Peter Port',
              'continentName' => 'Europe',
            ],
            'GH' =>
            [
              'countryName' => 'Ghana',
              'currencyCode' => 'GHS',
              'population' => 24339838,
              'capital' => 'Accra',
              'continentName' => 'Africa',
            ],
            'GI' =>
            [
              'countryName' => 'Gibraltar',
              'currencyCode' => 'GIP',
              'population' => 27884,
              'capital' => 'Gibraltar',
              'continentName' => 'Europe',
            ],
            'GL' =>
            [
              'countryName' => 'Greenland',
              'currencyCode' => 'DKK',
              'population' => 56375,
              'capital' => 'Nuuk',
              'continentName' => 'North America',
            ],
            'GM' =>
            [
              'countryName' => 'Gambia',
              'currencyCode' => 'GMD',
              'population' => 1593256,
              'capital' => 'Bathurst',
              'continentName' => 'Africa',
            ],
            'GN' =>
            [
              'countryName' => 'Guinea',
              'currencyCode' => 'GNF',
              'population' => 10324025,
              'capital' => 'Conakry',
              'continentName' => 'Africa',
            ],
            'GP' =>
            [
              'countryName' => 'Guadeloupe',
              'currencyCode' => 'EUR',
              'population' => 443000,
              'capital' => 'Basse-Terre',
              'continentName' => 'North America',
            ],
            'GQ' =>
            [
              'countryName' => 'Equatorial Guinea',
              'currencyCode' => 'XAF',
              'population' => 1014999,
              'capital' => 'Malabo',
              'continentName' => 'Africa',
            ],
            'GR' =>
            [
              'countryName' => 'Greece',
              'currencyCode' => 'EUR',
              'population' => 11000000,
              'capital' => 'Athens',
              'continentName' => 'Europe',
            ],
            'GS' =>
            [
              'countryName' => 'South Georgia and the South Sandwich Islands',
              'currencyCode' => 'GBP',
              'population' => 30,
              'capital' => 'Grytviken',
              'continentName' => 'Antarctica',
            ],
            'GT' =>
            [
              'countryName' => 'Guatemala',
              'currencyCode' => 'GTQ',
              'population' => 13550440,
              'capital' => 'Guatemala City',
              'continentName' => 'North America',
            ],
            'GU' =>
            [
              'countryName' => 'Guam',
              'currencyCode' => 'USD',
              'population' => 159358,
              'capital' => 'Hagåtña',
              'continentName' => 'Oceania',
            ],
            'GW' =>
            [
              'countryName' => 'Guinea-Bissau',
              'currencyCode' => 'XOF',
              'population' => 1565126,
              'capital' => 'Bissau',
              'continentName' => 'Africa',
            ],
            'GY' =>
            [
              'countryName' => 'Guyana',
              'currencyCode' => 'GYD',
              'population' => 748486,
              'capital' => 'Georgetown',
              'continentName' => 'South America',
            ],
            'HK' =>
            [
              'countryName' => 'Hong Kong',
              'currencyCode' => 'HKD',
              'population' => 6898686,
              'capital' => 'Hong Kong',
              'continentName' => 'Asia',
            ],
            'HM' =>
            [
              'countryName' => 'Heard Island and McDonald Islands',
              'currencyCode' => 'AUD',
              'population' => 0,
              'capital' => '',
              'continentName' => 'Antarctica',
            ],
            'HN' =>
            [
              'countryName' => 'Honduras',
              'currencyCode' => 'HNL',
              'population' => 7989415,
              'capital' => 'Tegucigalpa',
              'continentName' => 'North America',
            ],
            'HR' =>
            [
              'countryName' => 'Croatia',
              'currencyCode' => 'HRK',
              'population' => 4284889,
              'capital' => 'Zagreb',
              'continentName' => 'Europe',
            ],
            'HT' =>
            [
              'countryName' => 'Haiti',
              'currencyCode' => 'HTG',
              'population' => 9648924,
              'capital' => 'Port-au-Prince',
              'continentName' => 'North America',
            ],
            'HU' =>
            [
              'countryName' => 'Hungary',
              'currencyCode' => 'HUF',
              'population' => 9982000,
              'capital' => 'Budapest',
              'continentName' => 'Europe',
            ],
            'ID' =>
            [
              'countryName' => 'Indonesia',
              'currencyCode' => 'IDR',
              'population' => 242968342,
              'capital' => 'Jakarta',
              'continentName' => 'Asia',
            ],
            'IE' =>
            [
              'countryName' => 'Ireland',
              'currencyCode' => 'EUR',
              'population' => 4622917,
              'capital' => 'Dublin',
              'continentName' => 'Europe',
            ],
            'IL' =>
            [
              'countryName' => 'Israel',
              'currencyCode' => 'ILS',
              'population' => 7353985,
              'capital' => '',
              'continentName' => 'Asia',
            ],
            'IM' =>
            [
              'countryName' => 'Isle of Man',
              'currencyCode' => 'GBP',
              'population' => 75049,
              'capital' => 'Douglas',
              'continentName' => 'Europe',
            ],
            'IN' =>
            [
              'countryName' => 'India',
              'currencyCode' => 'INR',
              'population' => 1173108018,
              'capital' => 'New Delhi',
              'continentName' => 'Asia',
            ],
            'IO' =>
            [
              'countryName' => 'British Indian Ocean Territory',
              'currencyCode' => 'USD',
              'population' => 4000,
              'capital' => '',
              'continentName' => 'Asia',
            ],
            'IQ' =>
            [
              'countryName' => 'Iraq',
              'currencyCode' => 'IQD',
              'population' => 29671605,
              'capital' => 'Baghdad',
              'continentName' => 'Asia',
            ],
            'IR' =>
            [
              'countryName' => 'Iran',
              'currencyCode' => 'IRR',
              'population' => 76923300,
              'capital' => 'Tehran',
              'continentName' => 'Asia',
            ],
            'IS' =>
            [
              'countryName' => 'Iceland',
              'currencyCode' => 'ISK',
              'population' => 308910,
              'capital' => 'Reykjavik',
              'continentName' => 'Europe',
            ],
            'IT' =>
            [
              'countryName' => 'Italy',
              'currencyCode' => 'EUR',
              'population' => 60340328,
              'capital' => 'Rome',
              'continentName' => 'Europe',
            ],
            'JE' =>
            [
              'countryName' => 'Jersey',
              'currencyCode' => 'GBP',
              'population' => 90812,
              'capital' => 'Saint Helier',
              'continentName' => 'Europe',
            ],
            'JM' =>
            [
              'countryName' => 'Jamaica',
              'currencyCode' => 'JMD',
              'population' => 2847232,
              'capital' => 'Kingston',
              'continentName' => 'North America',
            ],
            'JO' =>
            [
              'countryName' => 'Jordan',
              'currencyCode' => 'JOD',
              'population' => 6407085,
              'capital' => 'Amman',
              'continentName' => 'Asia',
            ],
            'JP' =>
            [
              'countryName' => 'Japan',
              'currencyCode' => 'JPY',
              'population' => 127288000,
              'capital' => 'Tokyo',
              'continentName' => 'Asia',
            ],
            'KE' =>
            [
              'countryName' => 'Kenya',
              'currencyCode' => 'KES',
              'population' => 40046566,
              'capital' => 'Nairobi',
              'continentName' => 'Africa',
            ],
            'KG' =>
            [
              'countryName' => 'Kyrgyzstan',
              'currencyCode' => 'KGS',
              'population' => 5776500,
              'capital' => 'Bishkek',
              'continentName' => 'Asia',
            ],
            'KH' =>
            [
              'countryName' => 'Cambodia',
              'currencyCode' => 'KHR',
              'population' => 14453680,
              'capital' => 'Phnom Penh',
              'continentName' => 'Asia',
            ],
            'KI' =>
            [
              'countryName' => 'Kiribati',
              'currencyCode' => 'AUD',
              'population' => 92533,
              'capital' => 'Tarawa',
              'continentName' => 'Oceania',
            ],
            'KM' =>
            [
              'countryName' => 'Comoros',
              'currencyCode' => 'KMF',
              'population' => 773407,
              'capital' => 'Moroni',
              'continentName' => 'Africa',
            ],
            'KN' =>
            [
              'countryName' => 'Saint Kitts and Nevis',
              'currencyCode' => 'XCD',
              'population' => 51134,
              'capital' => 'Basseterre',
              'continentName' => 'North America',
            ],
            'KP' =>
            [
              'countryName' => 'North Korea',
              'currencyCode' => 'KPW',
              'population' => 22912177,
              'capital' => 'Pyongyang',
              'continentName' => 'Asia',
            ],
            'KR' =>
            [
              'countryName' => 'South Korea',
              'currencyCode' => 'KRW',
              'population' => 48422644,
              'capital' => 'Seoul',
              'continentName' => 'Asia',
            ],
            'KW' =>
            [
              'countryName' => 'Kuwait',
              'currencyCode' => 'KWD',
              'population' => 2789132,
              'capital' => 'Kuwait City',
              'continentName' => 'Asia',
            ],
            'KY' =>
            [
              'countryName' => 'Cayman Islands',
              'currencyCode' => 'KYD',
              'population' => 44270,
              'capital' => 'George Town',
              'continentName' => 'North America',
            ],
            'KZ' =>
            [
              'countryName' => 'Kazakhstan',
              'currencyCode' => 'KZT',
              'population' => 15340000,
              'capital' => 'Astana',
              'continentName' => 'Asia',
            ],
            'LA' =>
            [
              'countryName' => 'Laos',
              'currencyCode' => 'LAK',
              'population' => 6368162,
              'capital' => 'Vientiane',
              'continentName' => 'Asia',
            ],
            'LB' =>
            [
              'countryName' => 'Lebanon',
              'currencyCode' => 'LBP',
              'population' => 4125247,
              'capital' => 'Beirut',
              'continentName' => 'Asia',
            ],
            'LC' =>
            [
              'countryName' => 'Saint Lucia',
              'currencyCode' => 'XCD',
              'population' => 160922,
              'capital' => 'Castries',
              'continentName' => 'North America',
            ],
            'LI' =>
            [
              'countryName' => 'Liechtenstein',
              'currencyCode' => 'CHF',
              'population' => 35000,
              'capital' => 'Vaduz',
              'continentName' => 'Europe',
            ],
            'LK' =>
            [
              'countryName' => 'Sri Lanka',
              'currencyCode' => 'LKR',
              'population' => 21513990,
              'capital' => 'Colombo',
              'continentName' => 'Asia',
            ],
            'LR' =>
            [
              'countryName' => 'Liberia',
              'currencyCode' => 'LRD',
              'population' => 3685076,
              'capital' => 'Monrovia',
              'continentName' => 'Africa',
            ],
            'LS' =>
            [
              'countryName' => 'Lesotho',
              'currencyCode' => 'LSL',
              'population' => 1919552,
              'capital' => 'Maseru',
              'continentName' => 'Africa',
            ],
            'LT' =>
            [
              'countryName' => 'Lithuania',
              'currencyCode' => 'EUR',
              'population' => 2944459,
              'capital' => 'Vilnius',
              'continentName' => 'Europe',
            ],
            'LU' =>
            [
              'countryName' => 'Luxembourg',
              'currencyCode' => 'EUR',
              'population' => 497538,
              'capital' => 'Luxembourg',
              'continentName' => 'Europe',
            ],
            'LV' =>
            [
              'countryName' => 'Latvia',
              'currencyCode' => 'EUR',
              'population' => 2217969,
              'capital' => 'Riga',
              'continentName' => 'Europe',
            ],
            'LY' =>
            [
              'countryName' => 'Libya',
              'currencyCode' => 'LYD',
              'population' => 6461454,
              'capital' => 'Tripoli',
              'continentName' => 'Africa',
            ],
            'MA' =>
            [
              'countryName' => 'Morocco',
              'currencyCode' => 'MAD',
              'population' => 33848242,
              'capital' => 'Rabat',
              'continentName' => 'Africa',
            ],
            'MC' =>
            [
              'countryName' => 'Monaco',
              'currencyCode' => 'EUR',
              'population' => 32965,
              'capital' => 'Monaco',
              'continentName' => 'Europe',
            ],
            'MD' =>
            [
              'countryName' => 'Moldova',
              'currencyCode' => 'MDL',
              'population' => 4324000,
              'capital' => 'Chişinău',
              'continentName' => 'Europe',
            ],
            'ME' =>
            [
              'countryName' => 'Montenegro',
              'currencyCode' => 'EUR',
              'population' => 666730,
              'capital' => 'Podgorica',
              'continentName' => 'Europe',
            ],
            'MF' =>
            [
              'countryName' => 'Saint Martin',
              'currencyCode' => 'EUR',
              'population' => 35925,
              'capital' => 'Marigot',
              'continentName' => 'North America',
            ],
            'MG' =>
            [
              'countryName' => 'Madagascar',
              'currencyCode' => 'MGA',
              'population' => 21281844,
              'capital' => 'Antananarivo',
              'continentName' => 'Africa',
            ],
            'MH' =>
            [
              'countryName' => 'Marshall Islands',
              'currencyCode' => 'USD',
              'population' => 65859,
              'capital' => 'Majuro',
              'continentName' => 'Oceania',
            ],
            'MK' =>
            [
              'countryName' => 'Macedonia',
              'currencyCode' => 'MKD',
              'population' => 2062294,
              'capital' => 'Skopje',
              'continentName' => 'Europe',
            ],
            'ML' =>
            [
              'countryName' => 'Mali',
              'currencyCode' => 'XOF',
              'population' => 13796354,
              'capital' => 'Bamako',
              'continentName' => 'Africa',
            ],
            'MM' =>
            [
              'countryName' => 'Myanmar [Burma]',
              'currencyCode' => 'MMK',
              'population' => 53414374,
              'capital' => 'Naypyitaw',
              'continentName' => 'Asia',
            ],
            'MN' =>
            [
              'countryName' => 'Mongolia',
              'currencyCode' => 'MNT',
              'population' => 3086918,
              'capital' => 'Ulan Bator',
              'continentName' => 'Asia',
            ],
            'MO' =>
            [
              'countryName' => 'Macao',
              'currencyCode' => 'MOP',
              'population' => 449198,
              'capital' => 'Macao',
              'continentName' => 'Asia',
            ],
            'MP' =>
            [
              'countryName' => 'Northern Mariana Islands',
              'currencyCode' => 'USD',
              'population' => 53883,
              'capital' => 'Saipan',
              'continentName' => 'Oceania',
            ],
            'MQ' =>
            [
              'countryName' => 'Martinique',
              'currencyCode' => 'EUR',
              'population' => 432900,
              'capital' => 'Fort-de-France',
              'continentName' => 'North America',
            ],
            'MR' =>
            [
              'countryName' => 'Mauritania',
              'currencyCode' => 'MRO',
              'population' => 3205060,
              'capital' => 'Nouakchott',
              'continentName' => 'Africa',
            ],
            'MS' =>
            [
              'countryName' => 'Montserrat',
              'currencyCode' => 'XCD',
              'population' => 9341,
              'capital' => 'Plymouth',
              'continentName' => 'North America',
            ],
            'MT' =>
            [
              'countryName' => 'Malta',
              'currencyCode' => 'EUR',
              'population' => 403000,
              'capital' => 'Valletta',
              'continentName' => 'Europe',
            ],
            'MU' =>
            [
              'countryName' => 'Mauritius',
              'currencyCode' => 'MUR',
              'population' => 1294104,
              'capital' => 'Port Louis',
              'continentName' => 'Africa',
            ],
            'MV' =>
            [
              'countryName' => 'Maldives',
              'currencyCode' => 'MVR',
              'population' => 395650,
              'capital' => 'Malé',
              'continentName' => 'Asia',
            ],
            'MW' =>
            [
              'countryName' => 'Malawi',
              'currencyCode' => 'MWK',
              'population' => 15447500,
              'capital' => 'Lilongwe',
              'continentName' => 'Africa',
            ],
            'MX' =>
            [
              'countryName' => 'Mexico',
              'currencyCode' => 'MXN',
              'population' => 112468855,
              'capital' => 'Mexico City',
              'continentName' => 'North America',
            ],
            'MY' =>
            [
              'countryName' => 'Malaysia',
              'currencyCode' => 'MYR',
              'population' => 28274729,
              'capital' => 'Kuala Lumpur',
              'continentName' => 'Asia',
            ],
            'MZ' =>
            [
              'countryName' => 'Mozambique',
              'currencyCode' => 'MZN',
              'population' => 22061451,
              'capital' => 'Maputo',
              'continentName' => 'Africa',
            ],
            'NA' =>
            [
              'countryName' => 'Namibia',
              'currencyCode' => 'NAD',
              'population' => 2128471,
              'capital' => 'Windhoek',
              'continentName' => 'Africa',
            ],
            'NC' =>
            [
              'countryName' => 'New Caledonia',
              'currencyCode' => 'XPF',
              'population' => 216494,
              'capital' => 'Noumea',
              'continentName' => 'Oceania',
            ],
            'NE' =>
            [
              'countryName' => 'Niger',
              'currencyCode' => 'XOF',
              'population' => 15878271,
              'capital' => 'Niamey',
              'continentName' => 'Africa',
            ],
            'NF' =>
            [
              'countryName' => 'Norfolk Island',
              'currencyCode' => 'AUD',
              'population' => 1828,
              'capital' => 'Kingston',
              'continentName' => 'Oceania',
            ],
            'NG' =>
            [
              'countryName' => 'Nigeria',
              'currencyCode' => 'NGN',
              'population' => 154000000,
              'capital' => 'Abuja',
              'continentName' => 'Africa',
            ],
            'NI' =>
            [
              'countryName' => 'Nicaragua',
              'currencyCode' => 'NIO',
              'population' => 5995928,
              'capital' => 'Managua',
              'continentName' => 'North America',
            ],
            'NL' =>
            [
              'countryName' => 'Netherlands',
              'currencyCode' => 'EUR',
              'population' => 16645000,
              'capital' => 'Amsterdam',
              'continentName' => 'Europe',
            ],
            'NO' =>
            [
              'countryName' => 'Norway',
              'currencyCode' => 'NOK',
              'population' => 5009150,
              'capital' => 'Oslo',
              'continentName' => 'Europe',
            ],
            'NP' =>
            [
              'countryName' => 'Nepal',
              'currencyCode' => 'NPR',
              'population' => 28951852,
              'capital' => 'Kathmandu',
              'continentName' => 'Asia',
            ],
            'NR' =>
            [
              'countryName' => 'Nauru',
              'currencyCode' => 'AUD',
              'population' => 10065,
              'capital' => 'Yaren',
              'continentName' => 'Oceania',
            ],
            'NU' =>
            [
              'countryName' => 'Niue',
              'currencyCode' => 'NZD',
              'population' => 2166,
              'capital' => 'Alofi',
              'continentName' => 'Oceania',
            ],
            'NZ' =>
            [
              'countryName' => 'New Zealand',
              'currencyCode' => 'NZD',
              'population' => 4252277,
              'capital' => 'Wellington',
              'continentName' => 'Oceania',
            ],
            'OM' =>
            [
              'countryName' => 'Oman',
              'currencyCode' => 'OMR',
              'population' => 2967717,
              'capital' => 'Muscat',
              'continentName' => 'Asia',
            ],
            'PA' =>
            [
              'countryName' => 'Panama',
              'currencyCode' => 'PAB',
              'population' => 3410676,
              'capital' => 'Panama City',
              'continentName' => 'North America',
            ],
            'PE' =>
            [
              'countryName' => 'Peru',
              'currencyCode' => 'PEN',
              'population' => 29907003,
              'capital' => 'Lima',
              'continentName' => 'South America',
            ],
            'PF' =>
            [
              'countryName' => 'French Polynesia',
              'currencyCode' => 'XPF',
              'population' => 270485,
              'capital' => 'Papeete',
              'continentName' => 'Oceania',
            ],
            'PG' =>
            [
              'countryName' => 'Papua New Guinea',
              'currencyCode' => 'PGK',
              'population' => 6064515,
              'capital' => 'Port Moresby',
              'continentName' => 'Oceania',
            ],
            'PH' =>
            [
              'countryName' => 'Philippines',
              'currencyCode' => 'PHP',
              'population' => 99900177,
              'capital' => 'Manila',
              'continentName' => 'Asia',
            ],
            'PK' =>
            [
              'countryName' => 'Pakistan',
              'currencyCode' => 'PKR',
              'population' => 184404791,
              'capital' => 'Islamabad',
              'continentName' => 'Asia',
            ],
            'PL' =>
            [
              'countryName' => 'Poland',
              'currencyCode' => 'PLN',
              'population' => 38500000,
              'capital' => 'Warsaw',
              'continentName' => 'Europe',
            ],
            'PM' =>
            [
              'countryName' => 'Saint Pierre and Miquelon',
              'currencyCode' => 'EUR',
              'population' => 7012,
              'capital' => 'Saint-Pierre',
              'continentName' => 'North America',
            ],
            'PN' =>
            [
              'countryName' => 'Pitcairn Islands',
              'currencyCode' => 'NZD',
              'population' => 46,
              'capital' => 'Adamstown',
              'continentName' => 'Oceania',
            ],
            'PR' =>
            [
              'countryName' => 'Puerto Rico',
              'currencyCode' => 'USD',
              'population' => 3916632,
              'capital' => 'San Juan',
              'continentName' => 'North America',
            ],
            'PS' =>
            [
              'countryName' => 'Palestine',
              'currencyCode' => 'ILS',
              'population' => 3800000,
              'capital' => '',
              'continentName' => 'Asia',
            ],
            'PT' =>
            [
              'countryName' => 'Portugal',
              'currencyCode' => 'EUR',
              'population' => 10676000,
              'capital' => 'Lisbon',
              'continentName' => 'Europe',
            ],
            'PW' =>
            [
              'countryName' => 'Palau',
              'currencyCode' => 'USD',
              'population' => 19907,
              'capital' => 'Melekeok',
              'continentName' => 'Oceania',
            ],
            'PY' =>
            [
              'countryName' => 'Paraguay',
              'currencyCode' => 'PYG',
              'population' => 6375830,
              'capital' => 'Asunción',
              'continentName' => 'South America',
            ],
            'QA' =>
            [
              'countryName' => 'Qatar',
              'currencyCode' => 'QAR',
              'population' => 840926,
              'capital' => 'Doha',
              'continentName' => 'Asia',
            ],
            'RE' =>
            [
              'countryName' => 'Réunion',
              'currencyCode' => 'EUR',
              'population' => 776948,
              'capital' => 'Saint-Denis',
              'continentName' => 'Africa',
            ],
            'RO' =>
            [
              'countryName' => 'Romania',
              'currencyCode' => 'RON',
              'population' => 21959278,
              'capital' => 'Bucharest',
              'continentName' => 'Europe',
            ],
            'RS' =>
            [
              'countryName' => 'Serbia',
              'currencyCode' => 'RSD',
              'population' => 7344847,
              'capital' => 'Belgrade',
              'continentName' => 'Europe',
            ],
            'RU' =>
            [
              'countryName' => 'Russia',
              'currencyCode' => 'RUB',
              'population' => 140702000,
              'capital' => 'Moscow',
              'continentName' => 'Europe',
            ],
            'RW' =>
            [
              'countryName' => 'Rwanda',
              'currencyCode' => 'RWF',
              'population' => 11055976,
              'capital' => 'Kigali',
              'continentName' => 'Africa',
            ],
            'SA' =>
            [
              'countryName' => 'Saudi Arabia',
              'currencyCode' => 'SAR',
              'population' => 25731776,
              'capital' => 'Riyadh',
              'continentName' => 'Asia',
            ],
            'SB' =>
            [
              'countryName' => 'Solomon Islands',
              'currencyCode' => 'SBD',
              'population' => 559198,
              'capital' => 'Honiara',
              'continentName' => 'Oceania',
            ],
            'SC' =>
            [
              'countryName' => 'Seychelles',
              'currencyCode' => 'SCR',
              'population' => 88340,
              'capital' => 'Victoria',
              'continentName' => 'Africa',
            ],
            'SD' =>
            [
              'countryName' => 'Sudan',
              'currencyCode' => 'SDG',
              'population' => 35000000,
              'capital' => 'Khartoum',
              'continentName' => 'Africa',
            ],
            'SE' =>
            [
              'countryName' => 'Sweden',
              'currencyCode' => 'SEK',
              'population' => 9828655,
              'capital' => 'Stockholm',
              'continentName' => 'Europe',
            ],
            'SG' =>
            [
              'countryName' => 'Singapore',
              'currencyCode' => 'SGD',
              'population' => 4701069,
              'capital' => 'Singapore',
              'continentName' => 'Asia',
            ],
            'SH' =>
            [
              'countryName' => 'Saint Helena',
              'currencyCode' => 'SHP',
              'population' => 7460,
              'capital' => 'Jamestown',
              'continentName' => 'Africa',
            ],
            'SI' =>
            [
              'countryName' => 'Slovenia',
              'currencyCode' => 'EUR',
              'population' => 2007000,
              'capital' => 'Ljubljana',
              'continentName' => 'Europe',
            ],
            'SJ' =>
            [
              'countryName' => 'Svalbard and Jan Mayen',
              'currencyCode' => 'NOK',
              'population' => 2550,
              'capital' => 'Longyearbyen',
              'continentName' => 'Europe',
            ],
            'SK' =>
            [
              'countryName' => 'Slovakia',
              'currencyCode' => 'EUR',
              'population' => 5455000,
              'capital' => 'Bratislava',
              'continentName' => 'Europe',
            ],
            'SL' =>
            [
              'countryName' => 'Sierra Leone',
              'currencyCode' => 'SLL',
              'population' => 5245695,
              'capital' => 'Freetown',
              'continentName' => 'Africa',
            ],
            'SM' =>
            [
              'countryName' => 'San Marino',
              'currencyCode' => 'EUR',
              'population' => 31477,
              'capital' => 'San Marino',
              'continentName' => 'Europe',
            ],
            'SN' =>
            [
              'countryName' => 'Senegal',
              'currencyCode' => 'XOF',
              'population' => 12323252,
              'capital' => 'Dakar',
              'continentName' => 'Africa',
            ],
            'SO' =>
            [
              'countryName' => 'Somalia',
              'currencyCode' => 'SOS',
              'population' => 10112453,
              'capital' => 'Mogadishu',
              'continentName' => 'Africa',
            ],
            'SR' =>
            [
              'countryName' => 'Suriname',
              'currencyCode' => 'SRD',
              'population' => 492829,
              'capital' => 'Paramaribo',
              'continentName' => 'South America',
            ],
            'SS' =>
            [
              'countryName' => 'South Sudan',
              'currencyCode' => 'SSP',
              'population' => 8260490,
              'capital' => 'Juba',
              'continentName' => 'Africa',
            ],
            'ST' =>
            [
              'countryName' => 'São Tomé and Príncipe',
              'currencyCode' => 'STD',
              'population' => 175808,
              'capital' => 'São Tomé',
              'continentName' => 'Africa',
            ],
            'SV' =>
            [
              'countryName' => 'El Salvador',
              'currencyCode' => 'USD',
              'population' => 6052064,
              'capital' => 'San Salvador',
              'continentName' => 'North America',
            ],
            'SX' =>
            [
              'countryName' => 'Sint Maarten',
              'currencyCode' => 'ANG',
              'population' => 37429,
              'capital' => 'Philipsburg',
              'continentName' => 'North America',
            ],
            'SY' =>
            [
              'countryName' => 'Syria',
              'currencyCode' => 'SYP',
              'population' => 22198110,
              'capital' => 'Damascus',
              'continentName' => 'Asia',
            ],
            'SZ' =>
            [
              'countryName' => 'Swaziland',
              'currencyCode' => 'SZL',
              'population' => 1354051,
              'capital' => 'Mbabane',
              'continentName' => 'Africa',
            ],
            'TC' =>
            [
              'countryName' => 'Turks and Caicos Islands',
              'currencyCode' => 'USD',
              'population' => 20556,
              'capital' => 'Cockburn Town',
              'continentName' => 'North America',
            ],
            'TD' =>
            [
              'countryName' => 'Chad',
              'currencyCode' => 'XAF',
              'population' => 10543464,
              'capital' => 'N\'Djamena',
              'continentName' => 'Africa',
            ],
            'TF' =>
            [
              'countryName' => 'French Southern Territories',
              'currencyCode' => 'EUR',
              'population' => 140,
              'capital' => 'Port-aux-Français',
              'continentName' => 'Antarctica',
            ],
            'TG' =>
            [
              'countryName' => 'Togo',
              'currencyCode' => 'XOF',
              'population' => 6587239,
              'capital' => 'Lomé',
              'continentName' => 'Africa',
            ],
            'TH' =>
            [
              'countryName' => 'Thailand',
              'currencyCode' => 'THB',
              'population' => 67089500,
              'capital' => 'Bangkok',
              'continentName' => 'Asia',
            ],
            'TJ' =>
            [
              'countryName' => 'Tajikistan',
              'currencyCode' => 'TJS',
              'population' => 7487489,
              'capital' => 'Dushanbe',
              'continentName' => 'Asia',
            ],
            'TK' =>
            [
              'countryName' => 'Tokelau',
              'currencyCode' => 'NZD',
              'population' => 1466,
              'capital' => '',
              'continentName' => 'Oceania',
            ],
            'TL' =>
            [
              'countryName' => 'East Timor',
              'currencyCode' => 'USD',
              'population' => 1154625,
              'capital' => 'Dili',
              'continentName' => 'Oceania',
            ],
            'TM' =>
            [
              'countryName' => 'Turkmenistan',
              'currencyCode' => 'TMT',
              'population' => 4940916,
              'capital' => 'Ashgabat',
              'continentName' => 'Asia',
            ],
            'TN' =>
            [
              'countryName' => 'Tunisia',
              'currencyCode' => 'TND',
              'population' => 10589025,
              'capital' => 'Tunis',
              'continentName' => 'Africa',
            ],
            'TO' =>
            [
              'countryName' => 'Tonga',
              'currencyCode' => 'TOP',
              'population' => 122580,
              'capital' => 'Nuku\'alofa',
              'continentName' => 'Oceania',
            ],
            'TR' =>
            [
              'countryName' => 'Turkey',
              'currencyCode' => 'TRY',
              'population' => 77804122,
              'capital' => 'Ankara',
              'continentName' => 'Asia',
            ],
            'TT' =>
            [
              'countryName' => 'Trinidad and Tobago',
              'currencyCode' => 'TTD',
              'population' => 1228691,
              'capital' => 'Port of Spain',
              'continentName' => 'North America',
            ],
            'TV' =>
            [
              'countryName' => 'Tuvalu',
              'currencyCode' => 'AUD',
              'population' => 10472,
              'capital' => 'Funafuti',
              'continentName' => 'Oceania',
            ],
            'TW' =>
            [
              'countryName' => 'Taiwan',
              'currencyCode' => 'TWD',
              'population' => 22894384,
              'capital' => 'Taipei',
              'continentName' => 'Asia',
            ],
            'TZ' =>
            [
              'countryName' => 'Tanzania',
              'currencyCode' => 'TZS',
              'population' => 41892895,
              'capital' => 'Dodoma',
              'continentName' => 'Africa',
            ],
            'UA' =>
            [
              'countryName' => 'Ukraine',
              'currencyCode' => 'UAH',
              'population' => 45415596,
              'capital' => 'Kiev',
              'continentName' => 'Europe',
            ],
            'UG' =>
            [
              'countryName' => 'Uganda',
              'currencyCode' => 'UGX',
              'population' => 33398682,
              'capital' => 'Kampala',
              'continentName' => 'Africa',
            ],
            'UM' =>
            [
              'countryName' => 'U.S. Minor Outlying Islands',
              'currencyCode' => 'USD',
              'population' => 0,
              'capital' => '',
              'continentName' => 'Oceania',
            ],
            'US' =>
            [
              'countryName' => 'United States',
              'currencyCode' => 'USD',
              'population' => 310232863,
              'capital' => 'Washington',
              'continentName' => 'North America',
            ],
            'UY' =>
            [
              'countryName' => 'Uruguay',
              'currencyCode' => 'UYU',
              'population' => 3477000,
              'capital' => 'Montevideo',
              'continentName' => 'South America',
            ],
            'UZ' =>
            [
              'countryName' => 'Uzbekistan',
              'currencyCode' => 'UZS',
              'population' => 27865738,
              'capital' => 'Tashkent',
              'continentName' => 'Asia',
            ],
            'VA' =>
            [
              'countryName' => 'Vatican City',
              'currencyCode' => 'EUR',
              'population' => 921,
              'capital' => 'Vatican City',
              'continentName' => 'Europe',
            ],
            'VC' =>
            [
              'countryName' => 'Saint Vincent and the Grenadines',
              'currencyCode' => 'XCD',
              'population' => 104217,
              'capital' => 'Kingstown',
              'continentName' => 'North America',
            ],
            'VE' =>
            [
              'countryName' => 'Venezuela',
              'currencyCode' => 'VEF',
              'population' => 27223228,
              'capital' => 'Caracas',
              'continentName' => 'South America',
            ],
            'VG' =>
            [
              'countryName' => 'British Virgin Islands',
              'currencyCode' => 'USD',
              'population' => 21730,
              'capital' => 'Road Town',
              'continentName' => 'North America',
            ],
            'VI' =>
            [
              'countryName' => 'U.S. Virgin Islands',
              'currencyCode' => 'USD',
              'population' => 108708,
              'capital' => 'Charlotte Amalie',
              'continentName' => 'North America',
            ],
            'VN' =>
            [
              'countryName' => 'Vietnam',
              'currencyCode' => 'VND',
              'population' => 89571130,
              'capital' => 'Hanoi',
              'continentName' => 'Asia',
            ],
            'VU' =>
            [
              'countryName' => 'Vanuatu',
              'currencyCode' => 'VUV',
              'population' => 221552,
              'capital' => 'Port Vila',
              'continentName' => 'Oceania',
            ],
            'WF' =>
            [
              'countryName' => 'Wallis and Futuna',
              'currencyCode' => 'XPF',
              'population' => 16025,
              'capital' => 'Mata-Utu',
              'continentName' => 'Oceania',
            ],
            'WS' =>
            [
              'countryName' => 'Samoa',
              'currencyCode' => 'WST',
              'population' => 192001,
              'capital' => 'Apia',
              'continentName' => 'Oceania',
            ],
            'XK' =>
            [
              'countryName' => 'Kosovo',
              'currencyCode' => 'EUR',
              'population' => 1800000,
              'capital' => 'Pristina',
              'continentName' => 'Europe',
            ],
            'YE' =>
            [
              'countryName' => 'Yemen',
              'currencyCode' => 'YER',
              'population' => 23495361,
              'capital' => 'Sanaa',
              'continentName' => 'Asia',
            ],
            'YT' =>
            [
              'countryName' => 'Mayotte',
              'currencyCode' => 'EUR',
              'population' => 159042,
              'capital' => 'Mamoudzou',
              'continentName' => 'Africa',
            ],
            'ZA' =>
            [
              'countryName' => 'South Africa',
              'currencyCode' => 'ZAR',
              'population' => 49000000,
              'capital' => 'Pretoria',
              'continentName' => 'Africa',
            ],
            'ZM' =>
            [
              'countryName' => 'Zambia',
              'currencyCode' => 'ZMW',
              'population' => 13460305,
              'capital' => 'Lusaka',
              'continentName' => 'Africa',
            ],
            'ZW' =>
            [
              'countryName' => 'Zimbabwe',
              'currencyCode' => 'ZWL',
              'population' => 13061000,
              'capital' => 'Harare',
              'continentName' => 'Africa',
            ],
        ];

        return isset($countries[$code]) ? $countries[$code]['currencyCode'] : 'NGN';
    }
}

if (!function_exists('generateRefundComplaintNumber')) {
    function generateRefundComplaintNumber(string $prefix = 'RFC'): string {
        $uniqueNumber = $prefix . '-' . strtoupper(Str::random(8)) . '-' . time();

        while (B2BRequestRefund::where('complaint_number', $uniqueNumber)->exists()) {
            $uniqueNumber = $prefix . '-' . strtoupper(Str::random(8)) . '-' . time();
        }

        return $uniqueNumber;
    }
}

if (!function_exists('orderNo')) {
    function orderNo (): string {
        $timestamp = now()->timestamp;
        $randomNumber = mt_rand(100000, 999999);

        $uniqueOrderNumber = 'ORD-' . $timestamp . '-' . $randomNumber;

        while (Order::where('order_no', $uniqueOrderNumber)->exists()) {
            $randomNumber = mt_rand(100000, 999999);
            $uniqueOrderNumber = 'ORD-' . $timestamp . '-' . $randomNumber;
        }

        return $uniqueOrderNumber;
    }
}

if (! function_exists('currencyConvert')) {
    function currencyConvert($from, $amount, $to = null): float {
        static $rates = [];

        $from = $from ?? 'USD';

        if ($to === null || $from === $to) {
            return round($amount, 2);
        }

        $cacheKey = "{$from}_to_{$to}";
        if (!isset($rates[$cacheKey])) {
            $rates[$cacheKey] = Cache::remember($cacheKey, now()->addHours(24), function () use ($from, $to): int|float {
                $fromRate = Currency::where('code', $from)->value('exchange_rate');
                $toRate = Currency::where('code', $to)->value('exchange_rate');

                if (!$fromRate || !$toRate) {
                    throw new Exception("Currency rate not found for '{$from}' or '{$to}'.");
                }

                return $toRate / $fromRate;
            });
        }

        return round($amount * $rates[$cacheKey], 2);
    }
}

if (! function_exists('mailSend')) {
    function mailSend($type, $recipient, $subject, $mail_class, $payloadKey = 'user') {
        $data = [
            'type' => $type,
            'email' => $recipient->email,
            'subject' => $subject,
            'body' => "",
            'mailable' => $mail_class,
            'scheduled_at' => now(),
            'payload' => [
                $payloadKey => $recipient
            ]
        ];

        Mailing::saveData($data);
    }
}

if (! function_exists('currencyCodeByCountryId')) {
    function currencyCodeByCountryId($countryId) {
        $currencyCode = 'NGN';
        if($countryId) {
            $country = Country::findOrFail($countryId);
            $currencyCode = getCurrencyCode($country->sortname);
        }
        return $currencyCode;
    }
}


