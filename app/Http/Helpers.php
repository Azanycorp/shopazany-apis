<?php

use App\Models\BusinessSetting;
use App\Models\Language;
use App\Models\Upload;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

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


























