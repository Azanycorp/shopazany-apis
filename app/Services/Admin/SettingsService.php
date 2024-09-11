<?php

namespace App\Services\Admin;

use App\Http\Resources\SubscriptionPlanResource;
use App\Models\AboutUs;
use App\Models\ContactInfo;
use App\Models\CookiePolicy;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\TermsService;
use App\Models\SeoConfiguration;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\App;

class SettingsService
{
    use HttpResponse;

    public function addSeo($request)
    {
        $folder = null;

        if(App::environment('production')){
            $folder = '/prod/seo';
        } elseif(App::environment(['staging', 'local'])) {
            $folder = '/stag/seo';
        }

        $path = uploadImage($request, 'image', $folder);

        try {
            SeoConfiguration::updateOrCreate(
                ['id' => SeoConfiguration::first()?->id],
                [
                    'keywords' => $request->keywords,
                    'description' => $request->description,
                    'social_title' => $request->social_title,
                    'social_description' => $request->social_description,
                    'image' => $path,
                ]
            );

            return $this->success(null, "Successful");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getSeo()
    {
        $seo = SeoConfiguration::first();

        if(! $seo) {
            return $this->error([], "Empty", 403);
        }

        $data = [
            'id' => $seo->id,
            'keywords' => json_decode($seo->keywords),
            'description' => $seo->description,
            'social_title' => $seo->social_title,
            'social_description' => $seo->social_description,
            'image' => $seo->image,
        ];

        return $this->success($data, "Seo configuration");
    }

    public function addTermsService($request)
    {
        try {
            TermsService::updateOrCreate(
                ['id' => TermsService::first()?->id],
                [
                    'title' => $request->title,
                    'slug' => Str::slug($request->title),
                    'description' => $request->description,
                ]
            );

            return $this->success(null, "Successful");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getTermsService()
    {
        $terms = TermsService::first();

        if(! $terms) {
            return $this->error([], "Empty", 403);
        }

        $data = [
            'id' => $terms->id,
            'title' => $terms->title,
            'slug' => $terms->slug,
            'description' => $terms->description,
        ];

        return $this->success($data, "Terms of Service");
    }

    public function addCookiePolicy($request)
    {
        try {

            CookiePolicy::updateOrCreate(
                ['id' => CookiePolicy::first()?->id],
                [
                    'short_description' => $request->short_description,
                    'description' => $request->description,
                    'status' => $request->status,
                ]
            );

            return $this->success(null, "Successful");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getCookiePolicy()
    {
        $cookie = CookiePolicy::first();

        if(! $cookie) {
            return $this->error([], "Empty", 403);
        }

        $data = [
            'id' => $cookie->id,
            'short_description' => $cookie->short_description,
            'description' => $cookie->description,
            'status' => $cookie->status,
        ];

        return $this->success($data, "Cookie Policy");
    }

    public function addAboutUs($request)
    {
        try {

            $folder = null;

            if(App::environment('production')){
                $folder = '/prod/settings/about';
            } elseif(App::environment(['staging', 'local'])) {
                $folder = '/stag/settings/about';
            }

            $imageOne = uploadImage($request, 'image_one', $folder);
            $imageTwo = uploadImage($request, 'image_two', $folder);

            AboutUs::updateOrCreate(
                ['id' => AboutUs::first()?->id],
                [
                    'heading_one' => $request->heading_one,
                    'sub_text_one' => $request->sub_text_one,
                    'heading_two' => $request->heading_two,
                    'sub_text_two' => $request->sub_text_two,
                    'image_one' => $imageOne,
                    'image_two' => $imageTwo,
                ]
            );

            return $this->success(null, "Successful");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getAboutUs()
    {
        $about = AboutUs::first();

        if(! $about) {
            return $this->error([], "Empty", 403);
        }

        $data = [
            'id' => $about->id,
            'heading_one' => $about->heading_one,
            'sub_text_one' => $about->sub_text_one,
            'heading_two' => $about->heading_two,
            'sub_text_two' => $about->sub_text_two,
            'image_one' => $about->image_one,
            'image_two' => $about->image_two,
        ];

        return $this->success($data, "About Us");
    }

    public function addContactInfo($request)
    {
        try {

            ContactInfo::updateOrCreate(
                ['id' => ContactInfo::first()?->id],
                [
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'email' => $request->email,
                ]
            );

            return $this->success(null, "Successful");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getContactInfo()
    {
        $contact = ContactInfo::first();

        if(! $contact) {
            return $this->error([], "Empty", 403);
        }

        $data = [
            'id' => $contact->id,
            'address' => $contact->address,
            'phone' => $contact->phone,
            'email' => $contact->email,
        ];

        return $this->success($data, "Contact info");
    }

    public function addSocial($request)
    {
        try {

            ContactInfo::updateOrCreate(
                ['id' => ContactInfo::first()?->id],
                [
                    'social_media' => $request->social_media,
                ]
            );

            return $this->success(null, "Successful");
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function getSocial()
    {
        $contact = ContactInfo::first();

        if(! $contact) {
            return $this->error([], "Empty", 403);
        }

        $data = [
            'id' => $contact->id,
            'social_media' => $contact->social_media,
        ];

        return $this->success($data, "Social");
    }

    public function addPlan($request)
    {
        try {

            SubscriptionPlan::create([
                'title' => $request->title,
                'cost' => $request->cost,
                'country_id' => $request->country_id,
                'period' => $request->period,
                'tagline' => $request->tagline,
                'details' => $request->details,
                'status' => 'active'
            ]);

            return $this->success(null, 'Plan added successfully');

        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
    public function getPlanById($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $data = new SubscriptionPlanResource($plan);

        return $this->success($data, "Subscription plan detail");
    }

    public function getPlanByCountry($countryId)
    {
        $plan = SubscriptionPlan::where('country_id', $countryId)->get();
        $data = SubscriptionPlanResource::collection($plan);

        return $this->success($data, "Subscription plan");
    }

    public function updatePlan($request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        
        $plan->update([
            'title' => $request->title,
            'cost' => $request->cost,
            'country_id' => $request->country_id,
            'period' => $request->period,
            'tagline' => $request->tagline,
            'details' => $request->details,
            'status' => 'active'
        ]);

        return $this->success(null, 'Plan updated successfully');
    }

    public function deletePlan($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->delete();

        return $this->success(null, 'Plan deleted successfully');
    }
}







