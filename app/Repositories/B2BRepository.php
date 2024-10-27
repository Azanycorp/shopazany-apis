<?php

namespace App\Repositories;

use App\Contracts\B2BRepositoryInterface;
use App\Models\B2BProduct;
use App\Models\Post;

class B2BRepository implements B2BRepositoryInterface
{
    public function all()
    {
        return B2BProduct::all();
    }

    public function create(array $data)
    {
        return B2BProduct::create($data);
    }

    public function find(int $id)
    {
        return B2BProduct::findOrFail($id);
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

