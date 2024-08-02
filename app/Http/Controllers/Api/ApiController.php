<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected $service;

    public function __construct(AdminService $adminService)
    {
        $this->service = $adminService;
    }

    public function addSlider(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg']
        ]);

        return $this->service->addSlider($request);
    }

    public function slider()
    {
        return $this->service->slider();
    }

    public function categories()
    {
        return $this->service->categories();
    }

    public function country()
    {
        return $this->service->country();
    }

    public function states($id)
    {
        return $this->service->states($id);
    }

    public function brands()
    {
        return $this->service->brands();
    }

    public function colors()
    {
        return $this->service->colors();
    }

    public function units()
    {
        return $this->service->units();
    }

    public function sizes()
    {
        return $this->service->sizes();
    }
}
