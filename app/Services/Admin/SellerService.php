<?php

namespace App\Services\Admin;

use App\Http\Resources\SellerResource;
use App\Models\User;

class SellerService
{
    public function allSellers()
    {
        $users = User::with(['products'])
        ->where('type', 'seller')
        ->paginate(25);

        $data = SellerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'International Orders',
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




