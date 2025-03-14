<?php

namespace App\Observers;

use App\Enum\MailingEnum;
use App\Models\User;
use App\Enum\UserType;
use App\Models\Product;
use App\Models\UserWallet;
use App\Enum\ProductStatus;
use App\Mail\SignUpVerifyMail;
use App\Models\Action;
use App\Models\Wallet;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $type = MailingEnum::SIGN_UP_OTP;
        $subject = "Verify Account";
        $mail_class = SignUpVerifyMail::class;
        $data = [
            'user' => $user
        ];
        mailSend($type, $user, $subject, $mail_class, $data);

        if (in_array($user->type, [UserType::CUSTOMER, UserType::SELLER])) {
            $actionSlug = Action::whereIn('name', ['Create account', 'Create an account', 'Create user'])
                ->orWhere('slug', 'create_an_account')
                ->value('slug');

            reward_user($user, $actionSlug, 'completed');
        }

        if ($user->is_affiliate_member) {
            $user->referrer_code = generate_referral_code();
            $user->referrer_link = generate_referrer_links($user->referrer_code);
            $user->save();
        }

        match ($user->type) {
            UserType::B2B_SELLER => UserWallet::create(['seller_id' => $user->id]),
            UserType::CUSTOMER => Wallet::create(['user_id' => $user->id]),
            UserType::SELLER => Wallet::create(['user_id' => $user->id]),
            default => null,
        };
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
        Product::where('user_id', $user->id)
            ->update(['status' => ProductStatus::DELETED]);
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
