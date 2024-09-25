<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminCustomerController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AdminSellerController;
use App\Http\Controllers\Api\BannerPromoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\RewardPointController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingsController;

Route::prefix('connect')->controller(AdminAuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/forgot/password', 'forgot');
    Route::post('/reset/password', 'reset');
    Route::post('/logout', 'logout');
    Route::post('/verify/email', 'verify');
});

Route::controller(ApiController::class)->group(function () {
    Route::get('brands', 'brands');
    Route::get('colors', 'colors');
    Route::get('units', 'units');
    Route::get('sizes', 'sizes');
});


Route::group(['middleware' => ['auth:sanctum', 'auth-gates']], function () {

    Route::post('/add/slider', [ApiController::class, 'addSlider']);
    Route::get('/slider', [ApiController::class, 'slider']);
    Route::resource('brand', BrandController::class);
    Route::resource('color', ColorController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('size', SizeController::class);
    Route::post('/shop/country', [ApiController::class, 'shopByCountry']);

    Route::prefix('banner')->controller(BannerPromoController::class)->group(function () {
        Route::post('/add', 'addBanner');
        Route::get('/', 'banners');
        Route::get('/{id}', 'getOneBanner');
        Route::post('/edit/{id}', 'editBanner');
        Route::delete('/delete/{id}', 'deleteBanner');
    });

    Route::prefix('promo')->controller(BannerPromoController::class)->group(function () {
        Route::post('/add', 'addPromo');
        Route::get('/', 'promos');
        Route::delete('/delete/{id}', 'deletePromo');
    });

    Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
        Route::get('/analytic', 'dashboardAnalytics');
        Route::get('/best-sellers', 'bestSellers');
        Route::get('/best-selling-categories', 'bestSellingCat');
    });

    Route::prefix('category')->controller(CategoryController::class)->group(function () {
        Route::post('/create', 'createCategory');
        Route::get('/all', 'adminCategories');
        Route::get('/analytics', 'categoryAnalytic');
        Route::patch('/change/{category_id}', 'featuredStatus');

        Route::post('/create/subcategory', 'createSubCategory');
        Route::get('/subcategory', 'getAdminSubcategory');
        Route::get('/{category_id}/subcategory', 'getSubcategory');
        Route::patch('/subcategory/status/{sub_category_id}', 'subStatus');
    });

    Route::prefix('order')->controller(OrderController::class)->group(function () {
        Route::get('/analytic', 'orderAnalytics');
        Route::get('/local', 'localOrder');
        Route::get('/international', 'intOrder');
        Route::get('/detail/{id}', 'orderDetail');
        Route::get('/search', 'searchOrder');
    });

    Route::prefix('customer')->controller(AdminCustomerController::class)->group(function () {
        // GET routes
        Route::get('/', 'allCustomers');
        Route::get('/filter', 'filter');
        Route::get('/{user_id}', 'viewCustomer');
        Route::get('/payment/{id}', 'getPayment');

        Route::post('/add', 'addCustomer');
        Route::post('/edit', 'editCustomer');

        Route::patch('/approve', 'approveCustomer');
        Route::patch('/ban', 'banCustomer');

        Route::delete('/remove/{user_id}', 'removeCustomer');
    });

    Route::prefix('seller')->controller(AdminSellerController::class)->group(function () {
        Route::get('/', 'allSellers');
        Route::get('/{user_id}', 'viewSeller');
        Route::get('/payment-history/{user_id}', 'paymentHistory');

        Route::patch('/{user_id}/edit', 'editSeller');
        Route::delete('/remove/{user_id}', 'removeSeller');

        Route::patch('/approve', 'approveSeller');
        Route::patch('/ban', 'banSeller');
    });

    Route::prefix('product')->controller(AdminProductController::class)->group(function () {
        Route::post('/add', 'addProduct');
        Route::get('/', 'getProduct');
        Route::get('/{slug}', 'getOneProduct');
    });

    Route::prefix('reward')->controller(RewardPointController::class)->group(function () {
        Route::post('/action', 'addPoints');
        Route::get('/action', 'getPoints');
        Route::get('/action/{id}', 'getOnePoints');
        Route::patch('/action/{id}', 'editPoints');
        Route::delete('/delete/{id}', 'deletePoints');
    });

    Route::prefix('role')->controller(RoleController::class)->group(function () {
        Route::post('/', 'addRole');
        Route::get('/', 'getRole');
        Route::post('/assign/permission', 'assignPermission');
    });

    Route::prefix('permission')->controller(RoleController::class)->group(function () {
        Route::post('/', 'addPermission');
        Route::get('/', 'getPermission');
    });

    Route::prefix('settings')->controller(SettingsController::class)->group(function () {
        Route::post('/add-user', 'addUser');
        Route::post('/seo', 'addSeo');
        Route::get('/seo', 'getSeo');
        Route::post('/terms-service', 'addTermsService');
        Route::get('/terms-service', 'getTermsService');
        Route::post('/cookie-policy', 'addCookiePolicy');
        Route::get('/cookie-policy', 'getCookiePolicy');
        Route::post('/about-us', 'addAboutUs');
        Route::get('/about-us', 'getAboutUs');
        Route::post('/contact-info', 'addContactInfo');
        Route::get('/contact-info', 'getContactInfo');

        Route::post('/social', 'addSocial');
        Route::get('/social', 'getSocial');
    });

    Route::prefix('subscription')->controller(SettingsController::class)->group(function () {
        Route::post('/', 'addPlan');
        Route::get('/country/{country_id}', 'getPlanByCountry');
        Route::get('/{id}', 'getPlanById');
        Route::patch('/update/{id}', 'updatePlan');
        Route::delete('/remove/{id}', 'deletePlan');
    });

    Route::resource('settings/faq', FaqController::class);

    Route::get('/generate/users/link', [ApiController::class, 'referralGenerate']);

});






