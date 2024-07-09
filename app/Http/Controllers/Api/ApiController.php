<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Admin\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
}
