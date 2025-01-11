<?php

namespace App\Repositories;

use App\Contracts\B2BRepositoryInterface;
use App\Models\B2BProduct;

class B2BProductRepository implements B2BRepositoryInterface
{
    public function all(int $user, string $search = null)
    {
        $query = B2BProduct::with(['b2bProductImages', 'category', 'country', 'user', 'subCategory'])
            ->where('user_id', $user);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhereHas('category', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function create(array $data)
    {
        return B2BProduct::create($data);
    }

    public function find(int $id)
    {
        return B2BProduct::with(['b2bProductImages', 'category', 'country', 'user', 'subCategory'])
            ->findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $post = $this->find($id);
        $post->update($data);

        return $post;
    }

    public function delete(int $id)
    {
        $post = $this->find($id);
        $post->delete();

        return true;
    }
}

