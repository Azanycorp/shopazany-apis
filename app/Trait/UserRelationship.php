<?php

namespace App\Trait;

use App\Models\Kyc;
use App\Models\User;
use App\Models\Order;
use App\Models\State;
use App\Models\Wallet;
use App\Models\Country;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\OrderRate;
use App\Models\B2bCompany;
use App\Models\B2BProduct;
use App\Models\UserAction;
use App\Models\BankAccount;
use App\Models\RedeemPoint;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\B2bOrderRating;
use App\Models\UserActivityLog;
use App\Models\UserSubcription;
use App\Models\WithdrawalRequest;
use App\Models\B2bWithdrawalMethod;
use App\Models\BusinessInformation;
use App\Models\UserShippingAddress;
use App\Models\UserBusinessInformation;
use App\Models\B2BSellerShippingAddress;
use App\Models\ProductAttribute;
use App\Models\UserWallet;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait UserRelationship
{
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }
    public function userWallet(): HasOne
    {
        return $this->hasOne(UserWallet::class, 'seller_id');
    }

    // Returns users that this user referred (i.e., their downline)
    public function referrals(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'referral_relationships', 'referrer_id', 'referee_id');
    }

    // Returns the user that referred this user (i.e., their upliner)
    public function referrer(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'referral_relationships', 'referee_id', 'referrer_id');
    }

    public function B2bWithdrawalMethod(): HasMany
    {
        return $this->HasMany(B2bWithdrawalMethod::class, 'user_id')->latest('id');
    }

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'user_id');
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class, 'user_id');
    }

    public function kyc(): HasOne
    {
        return $this->hasOne(Kyc::class, 'user_id');
    }

    public function userbusinessinfo(): HasOne
    {
        return $this->hasOne(UserBusinessInformation::class, 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function userOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function userCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country', 'id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'user_id');
    }


    public function orderRate(): HasMany
    {
        return $this->hasMany(OrderRate::class, 'user_id');
    }

    public function b2bOrderRate(): HasMany
    {
        return $this->hasMany(B2bOrderRating::class, 'user_id');
    }

    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function userActions(): HasMany
    {
        return $this->hasMany(UserAction::class, 'user_id');
    }

    public function userActivityLog(): HasMany
    {
        return $this->hasMany(UserActivityLog::class, 'user_id');
    }

    public function userShippingAddress(): HasMany
    {
        return $this->hasMany(UserShippingAddress::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function reedemPoints(): HasMany
    {
        return $this->hasMany(RedeemPoint::class, 'user_id');
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubcription::class, 'user_id');
    }

    public function businessInformation(): HasOne
    {
        return $this->hasOne(BusinessInformation::class, 'user_id');
    }

    public function b2bProducts(): HasMany
    {
        return $this->hasMany(B2BProduct::class, 'user_id');
    }

    public function b2bSellerShippingAddresses(): HasMany
    {
        return $this->hasMany(B2BSellerShippingAddress::class, 'user_id');
    }

    public function b2bCompany(): HasOne
    {
        return $this->hasOne(B2bCompany::class, 'user_id');
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'user_id');
    }
}
