<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    public function banner()
    {
        $lang = getSystemLanguage()->code;

        $decoded_slider_images = json_decode(
            getSetting('home_slider_images', null, $lang),
            true,
        );
        $sliders = getSliderImages($decoded_slider_images);
        $home_slider_links = getSetting('home_slider_links', null, $lang);
        $links = json_decode($home_slider_links, true);
        $links = is_array($links) ? $links : '';

        return response()->json([
            'status' => true,
            'message' => "Banners",
            'data' => [
                'links' => $links,
                'sliders' => $sliders
            ]
        ]);

    }

    public function categories()
    {
        $featured_categories = Cache::rememberForever('featured_categories', function () {
            return Category::with('bannerImage')->where('featured', 1)->take(10)->get();
        });

        $data = [];
        foreach ($featured_categories as $category) {
            $category_name = $category->getTranslation('name');
            $data[] = [
                'name' => $category_name,
                'image' => $category->bannerImage->file_name ?? null
            ];
        }

        return response()->json([
            'status' => true,
            'message' => "Banners",
            'data' => $data
        ]);
    }
}
