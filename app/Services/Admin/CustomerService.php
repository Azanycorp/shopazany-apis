<?php

namespace App\Services\Admin;

use App\Http\Resources\CustomerResource;
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
        $user = User::where('id', $id)
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

}

