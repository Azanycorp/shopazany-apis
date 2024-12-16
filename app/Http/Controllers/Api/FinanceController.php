<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\FinanceService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    protected $service;

    public function __construct(FinanceService $service)
    {
        $this->service = $service;
    }

    public function addPaymentService(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'country_ids' => ['required', 'array'],
            'country_ids.*' => ['required', 'integer', 'exists:countries,id']
        ]);

        return $this->service->addPaymentService($request);
    }

    public function getPaymentService()
    {
        return $this->service->getPaymentService();
    }

    public function getSinglePaymentService($id)
    {
        return $this->service->getSinglePaymentService($id);
    }

    public function updatePaymentService(Request $request, $id)
    {
        return $this->service->updatePaymentService($request, $id);
    }

    public function deletePaymentService($id)
    {
        return $this->service->deletePaymentService($id);
    }
}
