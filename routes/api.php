<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\B2BSellerController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\MailingListController;
use App\Http\Controllers\Api\SubscriptionController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['throttle:apis', 'doNotCacheResponse'])->group(function (): void {

    Route::prefix('connect')
        ->controller(AuthController::class)
        ->group(function (): void {

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

    // Google Auth
    Route::controller(GoogleAuthController::class)
        ->group(function (): void {
            Route::get('auth/google', 'redirectToGoogle');
            Route::get('auth/google/callback', 'handleCallback');
        });
});

Route::get('/banners', [ApiController::class, 'slider']);
Route::get('/featured/categories', [ApiController::class, 'categories']);

Route::prefix('user/category')->controller(CategoryController::class)->group(function (): void {
    Route::get('/all', 'categories');
    Route::get('/subcategory/{category_id}', 'getSubcategory');
});

Route::get('/user/seller/template', [SellerController::class, 'getTemplate']);
Route::get('/b2b/seller/template', [B2BSellerController::class, 'getTemplate']);
Route::get('/shop/country', [ApiController::class, 'getShopByCountry']);
Route::get('/shop-by/country/{shop_country_id}', [ApiController::class, 'userShopByCountry']);

Route::middleware('cacheResponse:300')
    ->controller(HomeController::class)
    ->group(function (): void {
        Route::get('/best/selling', 'bestSelling');
        Route::get('/category/{slug}', 'categorySlug');
        Route::get('/all/products', 'allProducts');
        Route::get('/featured/products', 'featuredProduct');
        Route::get('/pocket/friendly', 'pocketFriendly');
        Route::get('/recommended/products', 'recommendedProducts');
        Route::get('/single/product/{slug}', 'productSlug');
        Route::get('/top-brands', 'topBrands');
        Route::get('/top-sellers', 'topSellers');

        Route::prefix('seller')->group(function (): void {
            Route::get('/{uuid}', 'sellerInfo');
            Route::get('/{uuid}/category', 'sellerCategory');
            Route::get('/{uuid}/reviews', 'sellerReviews');
        });
    });

Route::post('/payment/webhook', [PaymentController::class, 'webhook']);

Route::group(['middleware' => ['auth:api'], 'prefix' => 'user'], function (): void {
    // Product
    Route::post('product-review', [HomeController::class, 'productReview']);
    Route::post('/product/save-for-later', [HomeController::class, 'saveForLater']);
    // Move to Cart
    Route::post('/product/cart', [HomeController::class, 'moveToCart']);

    // Payment
    Route::prefix('payment')
        ->controller(PaymentController::class)
        ->group(function (): void {
            // Paystack
            Route::post('/paystack', 'processPayment');
            Route::get('/verify/paystack/{user_id}/{reference}', 'verifyPayment');

            // Authorize.net
            Route::post('/authorize', 'authorizeNetCard');

            // Authorize.net for B2B
            Route::post('/authorize-b2b', 'b2bAuthorizeNetCard');

            // Payment Method
            Route::get('/method/{country_id}', 'getPaymentMethod');
        });

    // Subscription
    Route::prefix('subscription')
        ->controller(SubscriptionController::class)
        ->group(function (): void {
            Route::get('/country/{country_id}', 'getPlanByCountry');
            Route::post('/payment', 'subscriptionPayment');
            Route::get('/history/{user_id}', 'subscriptionHistory');
        });

    Route::controller(UserController::class)->group(function (): void {
        Route::get('/profile', 'profile');
        Route::post('/bank/account', 'bankAccount');
        Route::delete('/remove/account', 'removeBankAccount');
        Route::post('/withdraw', 'withdraw')
            ->middleware('check.wallet');

        Route::post('/kyc', 'userKyc');
        Route::post('/earning-option', 'earningOption');

        Route::prefix('affiliate')->group(function (): void {
            Route::get('/dashboard-analytic/{user_id}', 'dashboardAnalytic');
            Route::get('/transaction/{user_id}', 'transactionHistory');
            Route::post('/payment-method', 'addPaymentMethod');
            Route::get('/payment-method/{user_id}', 'getPaymentMethod');
        });

        Route::post('/update-profile/{user_id}', 'updateProfile');
        Route::post('/settings/{user_id}', 'changeSettings');
    });

    Route::prefix('cart')->controller(CartController::class)->group(function (): void {
        Route::get('/{user_id}', 'getCartItems');
        Route::post('/add', 'addToCart');
        Route::delete('/{user_id}/clear', 'clearCart');
        Route::delete('/{user_id}/remove/{cart_id}', 'removeCartItem');
        Route::patch('/update-cart', [CartController::class, 'updateCart']);
    });

    Route::prefix('customer')->controller(CustomerController::class)->group(function (): void {
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

    Route::prefix('seller')->controller(SellerController::class)->group(function (): void {
        // Business Information
        Route::post('/business/information', 'businessInfo');
        Route::get('/dashboard/analytic/{user_id}', 'dashboardAnalytics');

        // Product Routes
        Route::prefix('product')->group(function (): void {
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
        Route::prefix('orders/{user_id}')->group(function (): void {
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


require __DIR__ . '/b2b.php';
