<?php

namespace App\Services\Admin;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\User;
use App\Trait\HttpResponse;

class CustomerService
{
    use HttpResponse;

    public function allCustomers()
    {
        $query = request()->input('search');

        $users = User::where('type', 'customer')
        ->where(function($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'LIKE', '%' . $query . '%')
                         ->orWhere('last_name', 'LIKE', '%' . $query . '%')
                         ->orWhere('middlename', 'LIKE', '%' . $query . '%')
                         ->orWhere('email', 'LIKE', '%' . $query . '%');
        })
        ->paginate(25);

        $data = CustomerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'All Customers',
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

    public function viewCustomer($id)
    {
        $user = User::with(['userCountry', 'state', 'wishlist.product', 'payments.order'])->where('id', $id)
        ->where('type', 'customer')
        ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $data = new CustomerResource($user);

        return [
            'status' => 'true',
            'message' => 'Customer details',
            'data' => $data,
        ];
    }

    public function banCustomer($request)
    {
        $user = User::where('id', $request->user_id)
        ->where('type', 'customer')
        ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->status = 'blocked';
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    public function removeCustomer($id)
    {
        $user = User::where('id', $id)
        ->where('type', 'customer')
        ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->delete();

        return $this->success(null, "User has been removed successfully");
    }

    public function filter()
    {
        $query = request()->query('approved');

        $users = User::where('type', 'customer')
            ->when($query !== null, function ($queryBuilder) use ($query) {
                $queryBuilder->where('is_admin_approve', $query);
            })
            ->paginate(25);

        $data = CustomerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'Filter by approval',
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

    public function addCustomer($request)
    {
        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middlename' => $request->middlename,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'status' => $request->status,
            ]);

            $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : null;

            $user->update(['image' => $image]);

        } catch (\Throwable $th) {
            throw $th;
        }

        return $this->success(null, "User has been created successfully");
    }

    public function editCustomer($request)
    {
        $user = User::where('id', $request->user_id)
        ->where('type', 'customer')
        ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'image' => $image,
            'status' => $request->status,
        ]);

        return $this->success(null, "User has been updated successfully");
    }

    public function getPayment($id)
    {
        $payment = Payment::with(['user', 'order'])->findOrFail($id);
        $data = new PaymentResource($payment);

        return $this->success($data, "Payment detail");
    }

}

