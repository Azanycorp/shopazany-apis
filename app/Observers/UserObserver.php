<?php

namespace App\Observers;

use App\Enum\UserType;
use App\Mail\SignUpVerifyMail;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Mail;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Mail::to($user->email)->send(new SignUpVerifyMail($user));

        if($user->type === UserType::CUSTOMER) {
            reward_user($user, 'create_account', 'completed');

            $user->referrer_code = generate_referral_code();
            $user->referrer_link = generate_referrer_link($user->referrer_code);
            $user->save();
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
