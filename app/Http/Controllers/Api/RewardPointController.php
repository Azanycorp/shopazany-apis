<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddRewardPointRequest;
use App\Services\RewardPoint\RewardPointService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class RewardPointController extends Controller
{
    const MESSAGE = '403 Forbidden';

    public function __construct(
        protected RewardPointService $service
    ) {}

    public function addPoints(AddRewardPointRequest $request)
    {
        abort_if(Gate::denies('points_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->addPoints($request);
    }

    public function getPoints()
    {
        abort_if(Gate::denies('points_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getPoints();
    }

    public function getOnePoints($id)
    {
        abort_if(Gate::denies('points_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getOnePoints($id);
    }

    public function editPoints(Request $request, $id)
    {
        abort_if(Gate::denies('points_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        $request->validate([
            'name' => ['nullable', 'string'],
            'icon' => ['nullable', 'image'],
            'points' => ['nullable', 'integer'],
            'verification_type' => ['nullable', 'string'],
            'country_ids' => ['nullable', 'array'],
        ]);

        return $this->service->editPoints($request, $id);
    }

    public function deletePoints($id)
    {
        abort_if(Gate::denies('points_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->deletePoints($id);
    }

    public function addPointSetting(Request $request)
    {
        abort_if(Gate::denies('points_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->addPointSetting($request);
    }

    public function getPointSetting()
    {
        abort_if(Gate::denies('points_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getPointSetting();
    }
}
