<?php

namespace App\Services\Admin;

use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\SellerResource;
use App\Models\Order;
use App\Models\User;
use App\Trait\HttpResponse;

class SellerService
{
    use HttpResponse;

    public function __construct(private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher) {}

    public function allSellers(\Illuminate\Http\Request $request): array
    {
        $searchQuery = $request->input('search');
        $approvedQuery = $request->query('approved');

        $users = User::with(['products', 'b2bProducts', 'bankAccount', 'wallet'])
            ->where('type', UserType::SELLER)
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
                $queryBuilder->where(function (\Illuminate\Contracts\Database\Query\Builder $subQuery) use ($searchQuery): void {
                    $subQuery->where('first_name', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('last_name', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('middlename', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('email', 'LIKE', '%'.$searchQuery.'%');
                });
            })
            ->when($approvedQuery !== null, function ($queryBuilder) use ($approvedQuery): void {
                $queryBuilder->where('is_admin_approve', $approvedQuery);
            })
            ->paginate(25);

        $data = SellerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'Sellers filtered',
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

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $user->is_admin_approve = ! $user->is_admin_approve;
        $user->status = $user->is_admin_approve ? 'active' : 'blocked';

        $user->save();

        $status = $user->is_admin_approve ? 'Approved successfully' : 'Disapproved successfully';

        return $this->success(null, $status);
    }

    public function viewSeller($id)
    {
        $user = User::with(['products'])->where('id', $id)
            ->where('type', UserType::SELLER)
            ->first();

        if (! $user) {
            return $this->error(null, 'User not found', 404);
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

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email_address,
            'phone' => $request->phone_number,
            'password' => $this->bcryptHasher->make($request->passowrd),
        ]);

        $data = [
            'user_id' => $user->id,
        ];

        return $this->success($data, 'Updated successfully');
    }

    public function banSeller($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $user->status = 'blocked';
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, 'User has been blocked successfully');
    }

    public function removeSeller($id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $user->delete();

        return $this->success(null, 'User has been removed successfully');
    }

    public function paymentHistory($id)
    {
        $orders = Order::whereHas('products', function (\Illuminate\Contracts\Database\Query\Builder $query) use ($id): void {
            $query->where('user_id', $id);
        })
            ->with('payments')
            ->get();

        $payments = $orders->flatMap->payments;

        $data = PaymentResource::collection($payments);

        return $this->success($data, 'Payment history');
    }

    public function bulkRemove($request)
    {
        User::whereIn('id', $request->user_ids)
            ->update([
                'status' => UserStatus::DELETED,
                'is_verified' => 0,
                'is_admin_approve' => 0,
            ]);

        User::whereIn('id', $request->user_ids)->delete();

        return $this->success(null, 'User(s) have been removed successfully');
    }
}
