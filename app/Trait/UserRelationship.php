<?php

namespace App\Trait;

use App\Models\B2bCompany;
use App\Models\B2bOrderRating;
use App\Models\B2BProduct;
use App\Models\B2BSellerShippingAddress;
use App\Models\B2bWithdrawalMethod;
use App\Models\BankAccount;
use App\Models\BusinessInformation;
use App\Models\Country;
use App\Models\Kyc;
use App\Models\Order;
use App\Models\OrderRate;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\PromoRedemption;
use App\Models\RedeemPoint;
use App\Models\State;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAction;
use App\Models\UserActivityLog;
use App\Models\UserBusinessInformation;
use App\Models\UserShippingAddress;
use App\Models\UserSubcription;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\Wishlist;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait UserRelationship
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Wallet, $this>
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\UserWallet, $this>
     */
    public function userWallet(): HasOne
    {
        return $this->hasOne(UserWallet::class, 'seller_id');
    }

    // Returns users that this user referred (i.e., their downline)
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function referrals(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'referral_relationships', 'referrer_id', 'referee_id');
    }

    // Returns the user that referred this user (i.e., their upliner)
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function referrer(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'referral_relationships', 'referee_id', 'referrer_id');
    }

    public function B2bWithdrawalMethod(): HasMany
    {
        return $this->HasMany(B2bWithdrawalMethod::class, 'user_id')->latest('id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\BankAccount, $this>
     */
    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\WithdrawalRequest, $this>
     */
    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Kyc, $this>
     */
    public function kyc(): HasOne
    {
        return $this->hasOne(Kyc::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\UserBusinessInformation, $this>
     */
    public function userbusinessinfo(): HasOne
    {
        return $this->hasOne(UserBusinessInformation::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Order, $this>
     */
    public function userOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Order, $this>
     */
    public function sellerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Country, $this>
     */
    public function userCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\PaymentMethod, $this>
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\OrderRate, $this>
     */
    public function orderRate(): HasMany
    {
        return $this->hasMany(OrderRate::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\B2bOrderRating, $this>
     */
    public function b2bOrderRate(): HasMany
    {
        return $this->hasMany(B2bOrderRating::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Wishlist, $this>
     */
    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\UserAction, $this>
     */
    public function userActions(): HasMany
    {
        return $this->hasMany(UserAction::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\UserActivityLog, $this>
     */
    public function userActivityLog(): HasMany
    {
        return $this->hasMany(UserActivityLog::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\UserShippingAddress, $this>
     */
    public function userShippingAddress(): HasMany
    {
        return $this->hasMany(UserShippingAddress::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\RedeemPoint, $this>
     */
    public function reedemPoints(): HasMany
    {
        return $this->hasMany(RedeemPoint::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\UserSubcription, $this>
     */
    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubcription::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\BusinessInformation, $this>
     */
    public function businessInformation(): HasOne
    {
        return $this->hasOne(BusinessInformation::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\B2BProduct, $this>
     */
    public function b2bProducts(): HasMany
    {
        return $this->hasMany(B2BProduct::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\B2BSellerShippingAddress, $this>
     */
    public function b2bSellerShippingAddresses(): HasMany
    {
        return $this->hasMany(B2BSellerShippingAddress::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\B2bCompany, $this>
     */
    public function b2bCompany(): HasOne
    {
        return $this->hasOne(B2bCompany::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ProductAttribute, $this>
     */
    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\PromoRedemption, $this>
     */
    public function promoRedemptions(): HasMany
    {
        return $this->hasMany(PromoRedemption::class, 'user_id');
    }
}
