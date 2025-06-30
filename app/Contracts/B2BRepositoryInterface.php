<?php

namespace App\Contracts;

interface B2BRepositoryInterface
{
    public function all(int $user);

    public function create(array $data);

    public function find(int $id);

    public function update(int $id, array $data);

    public function delete(int $id);
}
