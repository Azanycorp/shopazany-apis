<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $service;

    public function __construct(SettingsService $service)
    {
        $this->service = $service;
    }

    public function addSeo(Request $request)
    {
        $request->validate([
            'keywords' => ['required', 'array']
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
}