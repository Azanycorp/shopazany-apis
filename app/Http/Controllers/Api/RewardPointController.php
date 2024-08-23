<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddRewardPointRequest;
use App\Services\RewardPoint\RewardPointService;
use Illuminate\Http\Request;

class RewardPointController extends Controller
{
    protected $service;

    public function __construct(RewardPointService $service)
    {
        $this->service = $service;
    }

    public function addPoints(AddRewardPointRequest $request)
    {
        return $this->service->addPoints($request);
    }

    public function getPoints()
    {
        return $this->service->getPoints();
    }

    public function getOnePoints($id)
    {
        return $this->service->getOnePoints($id);
    }

    public function editPoints(Request $request, $id)
    {
        return $this->service->editPoints($request, $id);
    }
}
