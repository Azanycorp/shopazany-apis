<?php

namespace App\Services\Admin;

use App\Enum\AdminType;
use App\Enum\PlanStatus;
use App\Enum\PlanType;
use App\Enum\UserStatus;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Mail\AdminUserMail;
use App\Models\AboutUs;
use App\Models\Admin;
use App\Models\ContactInfo;
use App\Models\CookiePolicy;
use App\Models\SeoConfiguration;
use App\Models\SubscriptionPlan;
use App\Models\TermsService;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Illuminate\Support\Str;

class SettingsService
{
    use HttpResponse, SignUp;

    public function __construct(private readonly \Illuminate\Foundation\Application $application, private readonly \Illuminate\Database\DatabaseManager $databaseManager, private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher) {}

    public function addSeo($request)
    {
        $folder = '/stag/seo';
        if ($this->application->environment('production')) {
            $folder = '/prod/seo';
        }

        $path = uploadImage($request, 'image', $folder);
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

        return $this->success(null, 'Successful', 201);
    }

    public function getSeo()
    {
        $seo = SeoConfiguration::first();

        if (! $seo) {
            return $this->error(null, 'Empty', 404);
        }

        $data = [
            'id' => $seo->id,
            'keywords' => $seo->keywords,
            'description' => $seo->description,
            'social_title' => $seo->social_title,
            'social_description' => $seo->social_description,
            'image' => $seo->image,
        ];

        return $this->success($data, 'Seo configuration');
    }

    public function addTermsService($request)
    {
        TermsService::updateOrCreate(
            ['id' => TermsService::first()?->id],
            [
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
            ]
        );

        return $this->success(null, 'Successful', 201);
    }

    public function getTermsService()
    {
        $terms = TermsService::first();

        if (! $terms) {
            return $this->error([], 'Empty', 404);
        }

        $data = [
            'id' => $terms->id,
            'title' => $terms->title,
            'slug' => $terms->slug,
            'description' => $terms->description,
        ];

        return $this->success($data, 'Terms of Service');
    }

    public function addCookiePolicy($request)
    {
        CookiePolicy::updateOrCreate(
            ['id' => CookiePolicy::first()?->id],
            [
                'short_description' => $request->short_description,
                'description' => $request->description,
                'status' => $request->status,
            ]
        );

        return $this->success(null, 'Successful', 201);
    }

    public function getCookiePolicy()
    {
        $cookie = CookiePolicy::first();

        if (! $cookie) {
            return $this->error([], 'Empty', 404);
        }

        $data = [
            'id' => $cookie->id,
            'short_description' => $cookie->short_description,
            'description' => $cookie->description,
            'status' => $cookie->status,
        ];

        return $this->success($data, 'Cookie Policy');
    }

    public function addAboutUs($request)
    {
        $folder = '/stag/settings/about';
        if ($this->application->environment('production')) {
            $folder = '/prod/settings/about';
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

        return $this->success(null, 'Successful', 201);
    }

    public function getAboutUs()
    {
        $about = AboutUs::first();

        if (! $about) {
            return $this->error([], 'Empty', 404);
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

        return $this->success($data, 'About Us');
    }

    public function addContactInfo($request)
    {
        ContactInfo::updateOrCreate(
            ['id' => ContactInfo::first()?->id],
            [
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
            ]
        );

        return $this->success(null, 'Successful');
    }

    public function getContactInfo()
    {
        $contact = ContactInfo::first();

        if (! $contact) {
            return $this->error([], 'Empty', 404);
        }

        $data = [
            'id' => $contact->id,
            'address' => $contact->address,
            'phone' => $contact->phone,
            'email' => $contact->email,
        ];

        return $this->success($data, 'Contact info');
    }

    public function addSocial($request)
    {
        ContactInfo::updateOrCreate(
            ['id' => ContactInfo::first()?->id],
            [
                'social_media' => $request->social_media,
            ]
        );

        return $this->success(null, 'Successful');
    }

    public function getSocial()
    {
        $contact = ContactInfo::first();

        if (! $contact) {
            return $this->error([], 'Empty', 404);
        }

        $data = [
            'id' => $contact->id,
            'social_media' => $contact->social_media,
        ];

        return $this->success($data, 'Social');
    }

    public function addPlan($request)
    {
        $currencyCode = $this->currencyCode($request);

        SubscriptionPlan::create([
            'title' => $request->title,
            'cost' => $request->cost,
            'country_id' => $request->country_id,
            'currency' => $currencyCode,
            'period' => $request->period,
            'tier' => $request->tier,
            'tagline' => $request->tagline,
            'designation' => $request->designation,
            'details' => $request->details,
            'type' => $request->type ?? PlanType::B2C,
            'status' => PlanStatus::ACTIVE,
        ]);

        return $this->success(null, 'Plan added successfully', 201);
    }

    public function getPlanById($id, \Illuminate\Http\Request $request)
    {
        $type = $request->query('type', PlanType::B2C);

        if (! in_array($type, [PlanType::B2C, PlanType::B2B, PlanType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $plan = SubscriptionPlan::where('type', $type)->findOrFail($id);
        $data = new SubscriptionPlanResource($plan);

        return $this->success($data, 'Subscription plan detail');
    }

    public function getPlanByCountry($countryId, \Illuminate\Http\Request $request)
    {
        $type = $request->query('type', PlanType::B2C);

        if (! in_array($type, [PlanType::B2C, PlanType::B2B, PlanType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $plan = SubscriptionPlan::where('country_id', $countryId)
            ->where('type', $type)
            ->get();

        $data = SubscriptionPlanResource::collection($plan);

        return $this->success($data, 'Subscription plan');
    }

    public function updatePlan($request, $id)
    {
        $plan = SubscriptionPlan::where('type', $request->type)
            ->findOrFail($id);

        $plan->update([
            'title' => $request->title,
            'cost' => $request->cost,
            'country_id' => $request->country_id,
            'period' => $request->period,
            'type' => $request->type,
            'tier' => $request->tier,
            'tagline' => $request->tagline,
            'designation' => $request->designation,
            'details' => $request->details,
            'status' => PlanStatus::ACTIVE,
        ]);

        return $this->success(null, 'Plan updated successfully');
    }

    public function deletePlan($id, \Illuminate\Http\Request $request)
    {
        $type = $request->query('type', PlanType::B2C);

        if (! in_array($type, [PlanType::B2C, PlanType::B2B, PlanType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $plan = SubscriptionPlan::where('type', $type)
            ->findOrFail($id);

        $plan->delete();

        return $this->success(null, 'Plan deleted successfully');
    }

    public function addUser($request)
    {
        try {
            $password = generateRandomString();

            $this->databaseManager->beginTransaction();

            $admin = Admin::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'type' => AdminType::B2C,
                'password' => $this->bcryptHasher->make($password),
                'status' => UserStatus::ACTIVE,
            ]);
            $admin->permissions()->sync($request->permissions);
            $this->databaseManager->commit();

            defer(fn () => send_email($request->email, new AdminUserMail($admin, $password)));

            return $this->success(null, 'Created successfully', 201);
        } catch (\Throwable $th) {
            $this->databaseManager->rollBack();

            throw $th;
        }
    }

    public function allUsers(\Illuminate\Http\Request $request): array
    {
        $search = trim($request->input('search'));

        $users = Admin::with(['permissions', 'roles.permissions'])
            ->where(function (\Illuminate\Contracts\Database\Query\Builder $query) use ($search): void {
                $query->where('first_name', 'LIKE', '%'.$search.'%')
                    ->orWhere('last_name', 'LIKE', '%'.$search.'%')
                    ->orWhere('email', 'LIKE', '%'.$search.'%');
            })
            ->paginate(25);

        $data = AdminUserResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'All Admin Users',
            'data' => $data,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'prev_page_url' => $users->previousPageUrl(),
                'next_page_url' => $users->nextPageUrl(),
            ],
        ];
    }

    public function updateUser($request, $id)
    {
        try {
            $this->databaseManager->beginTransaction();

            $admin = Admin::findOrFail($id);

            $admin->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
            ]);

            $admin->roles()->sync($request->role_id);
            $admin->permissions()->sync($request->permissions);

            $this->databaseManager->commit();

            return $this->success(null, 'Updated successfully');
        } catch (\Throwable $th) {
            $this->databaseManager->rollBack();
            throw $th;
        }
    }

    public function deleteUser($id)
    {
        $user = Admin::findOrFail($id);
        $user->delete();

        return $this->success(null, 'Deleted successfully');
    }
}
