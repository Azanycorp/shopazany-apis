<?php

namespace App\Services\Admin;

use App\Http\Resources\PaymentServiceResource;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\PaymentService;

class FinanceService
{
    use HttpResponse;

    public function addPaymentService($request)
    {
        $slug = Str::slug($request->name);
        if (PaymentService::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . uniqid();
        }
        $paymentService = PaymentService::create([
            'name' => $request->name,
            'slug' => $slug
        ]);
        $paymentService->countries()->sync($request->country_ids);
        return $this->success(null, "Created successfully");
    }

    public function getPaymentService()
    {
        $paymentServices = PaymentService::with('countries')->get();
        $data = PaymentServiceResource::collection($paymentServices);

        return $this->success($data, 'List');
    }

    public function getSinglePaymentService($id)
    {
        $paymentService = PaymentService::with('countries')
            ->findOrFail($id);
        $data = new PaymentServiceResource($paymentService);

        return $this->success($data, 'Details');
    }

    public function updatePaymentService($request, $id)
    {
        $paymentService = PaymentService::with('countries')
            ->findOrFail($id);

        $slug = Str::slug($request->name);

        if (PaymentService::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . uniqid();
        }

        $paymentService->update([
            'name' => $request->name,
            'slug' => $slug
        ]);

        $paymentService->countries()->sync($request->country_ids);

        return $this->success(null, 'Updated successfully');
    }

    public function deletePaymentService($id)
    {
        $paymentService = PaymentService::findOrFail($id);
        $paymentService->delete();

        return $this->success(null, 'Deleted successfully');
    }
}





