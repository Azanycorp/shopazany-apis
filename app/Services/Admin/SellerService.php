<?php

namespace App\Services\Admin;

use App\Enum\UserType;
use App\Http\Resources\SellerResource;
use App\Models\User;
use App\Trait\HttpResponse;

class SellerService
{
    use HttpResponse;

    public function allSellers()
    {
        $users = User::with(['products'])
        ->where('type', UserType::SELLER)
        ->paginate(25);

        $data = SellerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'All Sellers',
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

    public function approveSeller($request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->is_admin_approve = !$user->is_admin_approve;
        $user->status = $user->is_admin_approve ? 'active' : 'blocked';

        $user->save();

        $status = $user->is_admin_approve ? "Approved successfully" : "Disapproved successfully";

        return $this->success(null, $status);
    }

    public function viewSeller($id)
    {
        $user = User::with(['products'])->where('id', $id)
        ->where('type', UserType::SELLER)
        ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $data = new SellerResource($user);

        return [
            'status' => 'true',
            'message' => 'Seller details',
            'data' => $data,
        ];
    }

    public function editSeller($request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email_address,
            'phone' => $request->phone_number,
            'password' => bcrypt($request->passowrd),
        ]);

        $data = [
            'user_id' => $user->id
        ];

        return $this->success($data, "Updated successfully");
    }

    public function banSeller($request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->status = 'blocked';
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    public function removeSeller($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->delete();

        return $this->success(null, "User has been removed successfully");
    }

    public function filter()
    {
        $query = request()->query('approved');

        $users = User::with(['products'])
            ->where('type', UserType::SELLER)
            ->when($query !== null, function ($queryBuilder) use ($query) {
                $queryBuilder->where('is_admin_approve', $query);
            })
            ->paginate(25);

        $data = SellerResource::collection($users);

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

    public function search()
    {
        $query = request()->input('query');

        $users = User::where('type', UserType::SELLER)
        ->where(function($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'LIKE', '%' . $query . '%')
                         ->orWhere('last_name', 'LIKE', '%' . $query . '%')
                         ->orWhere('middlename', 'LIKE', '%' . $query . '%')
                         ->orWhere('email', 'LIKE', '%' . $query . '%');
        })
        ->paginate(25);

        $data = SellerResource::collection($users);

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




