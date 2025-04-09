<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    const MESSAGE = '403 Forbidden';

    public function __construct(
        protected OrderService $service
    )
    {}

    public function orderAnalytics()
    {
        abort_if(Gate::denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->orderAnalytics();
    }

    public function localOrder(): array
    {
        abort_if(Gate::denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->localOrder();
    }

    public function intOrder(): array
    {
        abort_if(Gate::denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->intOrder();
    }

    public function orderDetail($id)
    {
        abort_if(Gate::denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->orderDetail($id);
    }

    public function searchOrder(Request $request): array
    {
        abort_if(Gate::denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->searchOrder($request);
    }
}
