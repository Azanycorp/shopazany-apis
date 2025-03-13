<?php

namespace App\Repositories;

use App\Contracts\B2BRepositoryInterface;
use App\Models\B2BSellerShippingAddress;

class B2BSellerShippingRepository implements B2BRepositoryInterface
{
    public function all(int $user)
    {
        return B2BSellerShippingAddress::with(['state', 'country', 'user'])
            ->where('user_id', $user)
            ->get();
    }

    public function create(array $data)
    {
        return B2BSellerShippingAddress::create($data);
    }

    public function find(int $id)
    {
        $currentUserId = userAuthId();
        return B2BSellerShippingAddress::where('user_id',$currentUserId)->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $response = $this->find($id);
        $response->update($data);

        return $response;
    }

    public function delete(int $id): bool
    {
        $response = $this->find($id);
        $response->delete();

        return true;
    }
}










