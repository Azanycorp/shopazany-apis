<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\B2BBuyerController;
use App\Http\Controllers\Api\B2BController;
use App\Http\Controllers\Api\B2BSellerController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MailingListController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['throttle:apis'])->group(function () {

    Route::prefix('connect')->controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')
            ->middleware('login.attempt');
        Route::post('/login/verify', 'loginVerify');
        Route::post('/signup', 'signup');
        Route::post('/forgot/password', 'forgot');
        Route::post('/reset/password', 'reset');
        Route::post('/signup/resend', 'resendCode');
        Route::post('/logout', 'logout');
        Route::post('/verify/email', 'verify');
        Route::post('/seller/signup', 'sellerSignup');

        Route::post('/affiliate/signup', 'affiliateSignup');
    });

    Route::get('/country', [ApiController::class, 'country']);
    Route::get('/states/{country_id}', [ApiController::class, 'states']);
});

Route::get('/banners', [ApiController::class, 'slider']);
Route::get('/featured/categories', [ApiController::class, 'categories']);

Route::prefix('user/category')->controller(CategoryController::class)->group(function () {
    Route::get('/all', 'categories');
    Route::get('/subcategory/{category_id}', 'getSubcategory');
});

Route::get('/user/seller/template', [SellerController::class, 'getTemplate']);
Route::get('/b2b/seller/template', [B2BSellerController::class, 'getTemplate']);
Route::get('/shop/country', [ApiController::class, 'getShopByCountry']);
Route::get('/shop-by/country/{shop_country_id}', [ApiController::class, 'userShopByCountry']);

Route::controller(HomeController::class)->group(function () {
    Route::get('/best/selling', 'bestSelling');
    Route::get('/category/{slug}', 'categorySlug');
    Route::get('/all/products', 'allProducts');
    Route::get('/featured/products', 'featuredProduct');
    Route::get('/pocket/friendly', 'pocketFriendly');
    Route::get('/recommended/products', 'recommendedProducts');
    Route::get('/single/product/{slug}', 'productSlug');
    Route::get('/top-brands', 'topBrands');
    Route::get('/top-sellers', 'topSellers');

    Route::prefix('seller')->group(function () {
        Route::get('/{uuid}', 'sellerInfo');
        Route::get('/{uuid}/category', 'sellerCategory');
        Route::get('/{uuid}/reviews', 'sellerReviews');
    });
});

Route::post('/payment/webhook', [PaymentController::class, 'webhook']);

Route::group(['middleware' => ['auth:api'], 'prefix' => 'user'], function () {
    // Product
    Route::post('product-review', [HomeController::class, 'productReview']);
    Route::post('/product/save-for-later', [HomeController::class, 'saveForLater']);
    // Move to Cart
    Route::post('/product/cart', [HomeController::class, 'moveToCart']);

    // Payment
    Route::prefix('payment')
        ->controller(PaymentController::class)
        ->group(function () {
            // Paystack
            Route::post('/paystack', 'processPayment');
            Route::get('/verify/paystack/{user_id}/{reference}', 'verifyPayment');

            // Authorize.net
            Route::post('/authorize', 'authorizeNetCard');

            // Payment Method
            Route::get('/method/{country_id}', 'getPaymentMethod');
    });

    // Subscription
    Route::prefix('subscription')
        ->controller(SubscriptionController::class)
        ->group(function () {
            Route::get('/country/{country_id}', 'getPlanByCountry');
            Route::post('/payment', 'subscriptionPayment');
            Route::get('/history/{user_id}', 'subscriptionHistory');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('/profile', 'profile');
        Route::post('/bank/account', 'bankAccount');
        Route::delete('/remove/account', 'removeBankAccount');
        Route::post('/withdraw', 'withdraw')
        ->middleware('check.wallet');

        Route::post('/kyc', 'userKyc');
        Route::post('/earning-option', 'earningOption');

        Route::prefix('affiliate')->group(function () {
            Route::get('/dashboard-analytic/{user_id}', 'dashboardAnalytic');
            Route::get('/transaction/{user_id}', 'transactionHistory');
            Route::post('/payment-method', 'addPaymentMethod');
            Route::get('/payment-method/{user_id}', 'getPaymentMethod');
        });

        Route::post('/update-profile/{user_id}', 'updateProfile');
        Route::post('/settings/{user_id}', 'changeSettings');
    });

    Route::prefix('cart')->controller(CartController::class)->group(function () {
        Route::get('/{user_id}', 'getCartItems');
        Route::post('/add', 'addToCart');
        Route::delete('/{user_id}/clear', 'clearCart');
        Route::delete('/{user_id}/remove/{cart_id}', 'removeCartItem');
        Route::patch('/update-cart', [CartController::class, 'updateCart']);
    });

    Route::prefix('customer')->controller(CustomerController::class)->group(function () {
        // Account and Dashboard Routes
        Route::get('/account-overview/{user_id}', 'acountOverview');
        Route::get('/activity/{user_id}', 'activity');
        Route::get('/dashboard/analytic/{user_id}', 'dashboardAnalytics');

        // Order Routes
        Route::get('/recent-orders/{user_id}', 'recentOrders');
        Route::get('/orders/{user_id}', 'getOrders');
        Route::get('/order/detail/{order_no}', 'getOrderDetail');
        Route::post('/rate/order', 'rateOrder');

        //Reward Point
        Route::get('/reward/dashboard/{user_id}', 'rewardDashboard');
        Route::post('/redeem/point', 'redeemPoint');

        // Support Route
        Route::post('/support', 'support');

        // Wishlist Routes
        Route::post('/wishlist', 'wishlist');
        Route::get('/wishlist/{user_id}', 'getWishlist');
        Route::get('/wishlist/single/{user_id}/{wishlist_id}', 'getSingleWishlist');
        Route::delete('/wishlist/remove/{user_id}/{wishlist_id}', 'removeWishlist');
    });

    Route::post('customer/mailing/subscribe', [MailingListController::class, 'signup']);

    Route::prefix('seller')->controller(SellerController::class)->group(function () {
        // Business Information
        Route::post('/business/information', 'businessInfo');
        Route::get('/dashboard/analytic/{user_id}', 'dashboardAnalytics');

        // Product Routes
        Route::prefix('product')->group(function () {
            Route::post('/create', 'createProduct');
            Route::post('/edit/{product_id}/{user_id}', 'updateProduct');
            Route::delete('/delete/{product_id}/{user_id}', 'deleteProduct');
            Route::get('/top-selling/{user_id}', 'topSelling');
            Route::get('/{user_id}', 'getProduct');
            Route::get('/{product_id}/{user_id}', 'getSingleProduct');

            Route::post('import', 'productImport');
            Route::get('export/{user_id}/{type}', 'export');
        });

        // Orders Routes
        Route::prefix('orders/{user_id}')->group(function () {
            Route::get('/', 'getAllOrders');
            Route::get('/confirmed', 'getConfirmedOrders');
            Route::get('/cancelled', 'getCancelledOrders');
            Route::get('/delivered', 'getDeliveredOrders');
            Route::get('/pending', 'getPendingOrders');
            Route::get('/processing', 'getProcessingOrders');
            Route::get('/shipped', 'getShippedOrders');
            Route::get('/summary', 'getOrderSummary');
        });
    });

});

// B2B
Route::middleware(['throttle:apis'])->group(function () {
    Route::prefix('b2b/connect')->controller(B2BController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/login/verify', 'loginVerify');
        Route::post('/seller/signup', 'signup');
        Route::post('/forgot/password', 'forgot');
        Route::post('/reset/password', 'reset');
        Route::post('/signup/resend', 'resendCode');
        Route::post('/logout', 'logout');
        Route::post('/verify', 'verify');

        // Buyer Onboarding
        Route::post('/buyer/signup', 'buyerOnboarding');
    });

    Route::prefix('b2b')->controller(B2BController::class)->group(function () {
        Route::post('/business/information', 'businessInformation');
        Route::get('/products', 'getProducts');
        Route::get('/product/{slug}', 'getProductDetail');
    });
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'b2b'], function () {
    // Seller
    Route::group(['middleware' => 'seller.auth', 'prefix' => 'seller', 'controller' => B2BSellerController::class], function () {
        Route::get('/profile', 'profile');
        Route::post('/edit-account', 'editAccount');
        Route::patch('/change-password', 'changePassword');
        Route::post('/edit-company', 'editCompany');
        Route::get('/refund/request', 'getComplaints');
        Route::get('/earning-report/{user_id}', 'getEarningReport');

        // Product
        Route::prefix('product')->group(function () {
            Route::post('/', 'addProduct');
            Route::get('/{user_id}', 'getAllProduct');
            Route::get('/analytic/{user_id}', 'getAnalytics');
            Route::get('/{user_id}/{product_id}', 'getProductById');
            Route::post('/update', 'updateProduct');
            Route::delete('/delete/{user_id}/{product_id}', 'deleteProduct');

            Route::post('import', 'productImport');
            Route::get('export/{user_id}/{type}', 'export');
        });

        // Shipping
        Route::prefix('shipping')->group(function () {
            Route::post('/', 'addShipping');
            Route::get('/{user_id}', 'getAllShipping');
            Route::get('/{user_id}/{shipping_id}', 'getShippingById');
            Route::patch('/update/{shipping_id}', 'updateShipping');
            Route::patch('/default/{user_id}/{shipping_id}', 'setDefault');
            Route::delete('/delete/{user_id}/{shipping_id}', 'deleteShipping');
        });
    });

    // Buyer
    Route::group(['middleware' => 'buyer.auth', 'prefix' => 'buyer', 'controller' => B2BBuyerController::class], function () {
        Route::post('request/refund', 'requestRefund');


    });

});
