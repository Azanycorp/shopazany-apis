<?php

namespace App\Repositories;

use App\Contracts\B2BRepositoryInterface;
use App\Models\B2BSellerShippingAddress;

class B2BSellerShippingRepository implements B2BRepositoryInterface
{
    public function all(int $user)
    {
        return B2BSellerShippingAddress::with(['state', 'country', 'user'])->get();
    }

    public function create(array $data)
    {
        return B2BSellerShippingAddress::create($data);
    }

    public function find(int $id)
    {
        return B2BSellerShippingAddress::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $response = $this->find($id);
        $response->update($data);

        return $response;
    }

    public function delete(int $id)
    {
        $response = $this->find($id);
        $response->delete();

        return true;
    }
}










