<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailingList;
use App\Models\User;
use Illuminate\Http\Request;

class MailingListController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        $user = User::findOrFail($request->user_id);

        try {
            MailingList::create([
                'user_id' => $request->user_id,
                'email' => $user->email,
            ]);
    
            reward_user($user, 'mailing_subscribe', 'completed');

            return $this->success(null, "Successfully subscribed to the mailing list.");
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
