<?php

namespace App\Services\User;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;

class UserService
{
    use HttpResponse;

    public function profile()
    {
        $auth = Auth::user();
        $user = User::with(['wallet', 'referrals'])->findOrFail($auth->id);
        $data = new ProfileResource($user);

        return $this->success($data, "Profile");
    }
}



