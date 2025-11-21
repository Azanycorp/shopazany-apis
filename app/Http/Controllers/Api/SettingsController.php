<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddAdminUserRequest;
use App\Http\Requests\Admin\SubscriptionPlanRequest;
use App\Services\Admin\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $service
    ) {}

    public function addSeo(Request $request)
    {
        $request->validate([
            'keywords' => ['required', 'array'],
        ]);

        return $this->service->addSeo($request);
    }

    public function getSeo()
    {
        return $this->service->getSeo();
    }

    public function addTermsService(Request $request)
    {
        return $this->service->addTermsService($request);
    }

    public function getTermsService()
    {
        return $this->service->getTermsService();
    }

    public function addCookiePolicy(Request $request)
    {
        return $this->service->addCookiePolicy($request);
    }

    public function getCookiePolicy()
    {
        return $this->service->getCookiePolicy();
    }

    public function addAboutUs(Request $request)
    {
        return $this->service->addAboutUs($request);
    }

    public function getAboutUs()
    {
        return $this->service->getAboutUs();
    }

    public function addContactInfo(Request $request)
    {
        return $this->service->addContactInfo($request);
    }

    public function getContactInfo()
    {
        return $this->service->getContactInfo();
    }

    public function addSocial(Request $request)
    {
        return $this->service->addSocial($request);
    }

    public function getSocial()
    {
        return $this->service->getSocial();
    }

    public function addPlan(SubscriptionPlanRequest $request)
    {
        return $this->service->addPlan($request);
    }

    public function getPlanById(Request $request, $id)
    {
        return $this->service->getPlanById($request, $id);
    }

    public function getPlanByCountry(Request $request, $countryId)
    {
        return $this->service->getPlanByCountry($request, $countryId);
    }

    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'type' => ['required', 'in:b2c,b2b,agriecom_b2c'],
        ]);

        return $this->service->updatePlan($request, $id);
    }

    public function deletePlan(Request $request, $id)
    {
        return $this->service->deletePlan($request, $id);
    }

    public function addUser(AddAdminUserRequest $request)
    {
        return $this->service->addUser($request);
    }

    public function allUsers(Request $request): array
    {
        return $this->service->allUsers($request);
    }

    public function updateUser(Request $request, $id)
    {
        return $this->service->updateUser($request, $id);
    }

    public function deleteUser($id)
    {
        return $this->service->deleteUser($id);
    }
}
