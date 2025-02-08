<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AdminCouponController;
use App\Http\Controllers\Api\AdminSellerController;
use App\Http\Controllers\Api\BannerPromoController;
use App\Http\Controllers\Api\RewardPointController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\B2B\B2BAdminController;
use App\Http\Controllers\Api\AdminCustomerController;
use App\Http\Controllers\Api\B2B\B2BAdminBuyerController;
use App\Http\Controllers\Api\B2B\B2BAdminSellerController;
use App\Http\Controllers\Api\B2B\B2BBannerPromoController;
use App\Http\Controllers\Api\B2B\ProductCategoryController;

Route::prefix('/connect')->controller(AdminAuthController::class)->group(function (): void {
    Route::post('/login', 'login');
    Route::post('/forgot/password', 'forgot');
    Route::post('/reset/password', 'reset');
    Route::post('/logout', 'logout');
    Route::post('/verify/email', 'verify');
});

Route::controller(ApiController::class)->group(function (): void {
    Route::get('brands', 'brands');
    Route::get('colors', 'colors');
    Route::get('units', 'units');
    Route::get('sizes', 'sizes');
});

Route::group(['middleware' => ['auth:sanctum', 'auth-gates']], function (): void {

    Route::get('/profile', [ApiController::class, 'adminProfile']);

    Route::post('/add/slider', [ApiController::class, 'addSlider']);
    Route::get('/slider', [ApiController::class, 'slider']);
    Route::resource('brand', BrandController::class);
    Route::resource('color', ColorController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('size', SizeController::class);
    Route::post('/shop/country', [ApiController::class, 'shopByCountry']);

    Route::prefix('banner')->controller(BannerPromoController::class)->group(function (): void {
        Route::post('/add', 'addBanner');
        Route::get('/', 'banners');
        Route::get('/{id}', 'getOneBanner');
        Route::post('/edit/{id}', 'editBanner');
        Route::delete('/delete/{id}', 'deleteBanner');
    });

    Route::prefix('promo')->controller(BannerPromoController::class)->group(function (): void {
        Route::post('/add', 'addPromo');
        Route::get('/', 'promos');
        Route::delete('/delete/{id}', 'deletePromo');
    });

    Route::prefix('dashboard')->controller(DashboardController::class)->group(function (): void {
        Route::get('/analytic', 'dashboardAnalytics');
        Route::get('/best-sellers', 'bestSellers');
        Route::get('/best-selling-categories', 'bestSellingCat');
    });

    Route::prefix('category')->controller(CategoryController::class)->group(function (): void {
        Route::post('/create', 'createCategory');
        Route::get('/all', 'adminCategories');
        Route::get('/analytics', 'categoryAnalytic');
        Route::patch('/change/{category_id}', 'featuredStatus');
        Route::delete('/delete/{id}', 'deleteCategory');

        Route::post('/create/subcategory', 'createSubCategory');
        Route::get('/subcategory', 'getAdminSubcategory');
        Route::get('/{category_id}/subcategory', 'getSubcategory');
        Route::patch('/subcategory/status/{sub_category_id}', 'subStatus');
        Route::delete('/subcategory/delete/{id}', 'deleteSubCategory');
    });

    Route::prefix('order')->controller(OrderController::class)->group(function (): void {
        Route::get('/analytic', 'orderAnalytics');
        Route::get('/local', 'localOrder');
        Route::get('/international', 'intOrder');
        Route::get('/detail/{id}', 'orderDetail');
        Route::get('/search', 'searchOrder');
    });

    Route::prefix('customer')->controller(AdminCustomerController::class)->group(function (): void {
        // GET routes
        Route::get('/', 'allCustomers');
        Route::get('/filter', 'filter');
        Route::get('/{user_id}', 'viewCustomer');
        Route::get('/payment/{id}', 'getPayment');

        Route::post('/add', 'addCustomer');
        Route::post('/edit', 'editCustomer');

        Route::patch('/approve', 'approveCustomer');
        Route::patch('/ban', 'banCustomer');

        Route::delete('/remove', 'removeCustomer');
    });

    Route::prefix('seller')->controller(AdminSellerController::class)->group(function (): void {
        Route::get('/', 'allSellers');
        Route::get('/{user_id}', 'viewSeller');
        Route::get('/payment-history/{user_id}', 'paymentHistory');

        Route::patch('/{user_id}/edit', 'editSeller');
        Route::delete('/remove/{user_id}', 'removeSeller');

        Route::patch('/approve', 'approveSeller');
        Route::patch('/ban', 'banSeller');

        Route::delete('/bulk/remove', 'bulkRemove');
    });

    Route::prefix('product')->controller(AdminProductController::class)->group(function (): void {
        Route::post('/add', 'addProduct');
        Route::get('/', 'getProduct');
        Route::get('/{slug}', 'getOneProduct');
    });

    Route::prefix('reward')->controller(RewardPointController::class)->group(function (): void {
        Route::post('/action', 'addPoints');
        Route::get('/action', 'getPoints');
        Route::get('/action/{id}', 'getOnePoints');
        Route::patch('/action/{id}', 'editPoints');
        Route::delete('/delete/{id}', 'deletePoints');
    });

    Route::prefix('role')->controller(RoleController::class)->group(function (): void {
        Route::post('/', 'addRole');
        Route::get('/', 'getRole');
        Route::post('/assign/permission', 'assignPermission');
    });

    Route::prefix('permission')->controller(RoleController::class)->group(function (): void {
        Route::post('/', 'addPermission');
        Route::get('/', 'getPermission');
    });

    Route::prefix('settings')->controller(SettingsController::class)->group(function (): void {
        // Admin User
        Route::post('/add-user', 'addUser');
        Route::get('/all-users', 'allUsers');
        Route::patch('/update-user/{id}', 'updateUser');
        Route::delete('/delete-user/{id}', 'deleteUser');

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

    Route::prefix('subscription')->controller(SettingsController::class)->group(function (): void {
        Route::post('/', 'addPlan');
        Route::get('/country/{country_id}', 'getPlanByCountry');
        Route::get('/{id}', 'getPlanById');
        Route::patch('/update/{id}', 'updatePlan');
        Route::delete('/remove/{id}', 'deletePlan');
    });

    Route::prefix('finance')->controller(FinanceController::class)->group(function (): void {
        Route::post('/payment/service', 'addPaymentService');
        Route::get('/payment/service', 'getPaymentService');
        Route::get('/payment/service/{id}', 'getSinglePaymentService');
        Route::patch('/payment/service/{id}', 'updatePaymentService');
        Route::delete('/payment/service/{id}', 'deletePaymentService');
    });

    Route::prefix('coupon')
        ->controller(AdminCouponController::class)
        ->group(function (): void {
            Route::post('/create', 'createCoupon');
            Route::get('/', 'getCoupon');
        });

    Route::resource('settings/faq', FaqController::class);

    Route::get('/generate/users/link', [ApiController::class, 'referralGenerate']);

    //b2b admin
    Route::prefix('b2b')->group(function () {
        Route::controller(B2BAdminController::class)->group(function () {
            Route::get('/dashboard', 'dashboard')->middleware('cacheResponse:300');

            Route::get('/profile', 'adminProfile')->middleware('cacheResponse:300');
            Route::post('/update-profile', 'updateAdminProfile');
            Route::post('/update-password', 'updateAdminPassword');
            Route::post('/enable-2fa', 'enable2FA');
            Route::get('/get-config', 'getConfigDetails');
            Route::post('/update-config', 'updateConfigDetails');
        });

        Route::controller(B2BAdminController::class)->prefix('admin-users')->group(function () {
            Route::get('/', 'adminUsers')->middleware('cacheResponse:300');
            Route::post('/add', 'addAdmin');
            Route::get('/details/{id}', 'viewAdminUser');
            Route::post('/update/{id}', 'editAdminUser');
            Route::post('/revoke-access/{id}', 'revokeAccess');
            Route::post('/verify-password', 'verifyPassword');
            Route::delete('/delete-account/{id}', 'removeAdmin');
        });

        Route::prefix('category')->controller(ProductCategoryController::class)->group(function () {
            Route::post('/create', 'createCategory');
            Route::get('/all', 'adminCategories')->middleware('cacheResponse:300');
            Route::get('/analytics', 'categoryAnalytic');
            Route::post('/update/{id}', 'updateCategory');
            Route::post('/change/{category_id}', 'featuredStatus');
            Route::delete('/delete/{id}', 'deleteCategory');

            Route::post('/create/subcategory', 'createSubCategory');
            Route::get('/subcategory', 'getAdminSubcategory');
            Route::get('/{category_id}/subcategory', 'getSubcategory')->middleware('cacheResponse:300');
            Route::post('/subcategory/status/{sub_category_id}', 'subStatus');
            Route::delete('/subcategory/delete/{id}', 'deleteSubCategory');
        });

        Route::prefix('banner')->controller(B2BBannerPromoController::class)->group(function (): void {
            Route::post('/add', 'addBanner');
            Route::get('/', 'banners')->middleware('cacheResponse:300');
            Route::get('/{id}', 'getOneBanner');
            Route::post('/edit/{id}', 'editBanner');
            Route::delete('/delete/{id}', 'deleteBanner');
        });

        Route::prefix('promo')->controller(B2BBannerPromoController::class)->group(function (): void {
            Route::post('/add', 'addPromo');
            Route::get('/', 'promos');
            Route::get('/products', 'getProducts')->middleware('cacheResponse:300');
            Route::delete('/delete/{id}', 'deletePromo');
        });

        //buyers
        Route::prefix('buyer')->controller(B2BAdminBuyerController::class)->group(function (): void {
            // GET routes
            Route::get('/', 'allBuyers')->middleware('cacheResponse:300');
            Route::get('/filter', 'filter');
            Route::get('/details/{user_id}', 'viewBuyer');
            Route::post('/update-details/{id}', 'editBuyer');
            Route::post('/update-company-details/{id}', 'editBuyerCompany');
            Route::patch('/approve/{user_id}', 'approveBuyer');
            Route::patch('/ban/{user_id}', 'banBuyer');
            Route::delete('/bulk-remove', 'bulkRemoveBuyer');
            Route::delete('/remove/{user_id}', 'removeBuyer');
        });

        //Sellers
        Route::prefix('seller')->controller(B2BAdminSellerController::class)->group(function (): void {
            Route::get('/', 'allSellers')->middleware('cacheResponse:300');
            Route::get('/details/{user_id}', 'viewSeller')->middleware('cacheResponse:300');
            Route::delete('/remove/{user_id}', 'removeSeller');
            Route::post('/approve/{id}', 'approveSeller');
            Route::post('/ban/{id}', 'banSeller');
            Route::delete('/bulk/remove', 'bulkRemove');

            Route::prefix('product')->controller(B2BAdminSellerController::class)->group(function (): void {
                Route::post('/add', 'addSellerProduct');
                Route::get('/details/{user_id}/{id}', 'viewSellerProduct');
                Route::post('/update/{user_id}/{id}', 'editSellerProduct');
                Route::delete('/delete/{user_id}/{id}', 'removeSellerProduct');
            });
        });

        //Withdrawal requests
        Route::prefix('widthrawal-request')->controller(B2BAdminController::class)->group(function (): void {
            Route::get('/', 'widthrawalRequests')->middleware('cacheResponse:300');
            Route::get('/view/{id}', 'viewWidthrawalRequest')->middleware('cacheResponse:300');
            Route::post('/approve/{id}', 'approveWidthrawalRequest');
            Route::post('/cancel/{id}', 'cancelWidthrawalRequest');
        });

        //Withdrawal method requests
        Route::prefix('widthrawal-method-request')->controller(B2BAdminController::class)->group(function (): void {
            Route::get('/', 'widthrawalMethods')->middleware('cacheResponse:300');
            Route::get('/view/{id}', 'viewWidthrawalMethod')->middleware('cacheResponse:300');
            Route::post('/approve/{id}', 'approveWidthrawalMethod');
            Route::post('/reject/{id}', 'rejectWidthrawalMethod');
        });

        //Shipping Country
        Route::prefix('Shipping-management')->controller(B2BAdminController::class)->group(function (): void {

            Route::prefix('country')->group(function (): void {
                Route::get('/', 'shippingCountries');
                Route::post('/add', 'addShippingCountry');
                Route::get('/details/{id}', 'viewShippingCountry');
                Route::post('/update/{id}', 'editShippingCountry');
                Route::delete('/delete/{id}', 'deleteShippingCountry');
            });
        });

        //Seller Product Approval requests
        Route::prefix('product-approval-request')->controller(B2BAdminController::class)->group(function (): void {
            Route::get('/', 'allProducts');
            Route::get('/view/{id}', 'viewProduct');
            Route::post('/approve/{id}', 'approveProduct');
            Route::post('/reject/{id}', 'rejectProduct');
        });

        //Rfq
        Route::middleware('cacheResponse:300')->prefix('rfqs')->controller(B2BAdminController::class)->group(function (): void {
            Route::get('/', 'allRfq');
            Route::get('/details/{id}', 'rfqDetails');
        });
        //Orders
        Route::middleware('cacheResponse:300')->prefix('orders')->controller(B2BAdminController::class)->group(function (): void {
            Route::get('/', 'allOrders');
            Route::get('/details/{id}', 'orderDetails');
            Route::post('/mark-completed/{id}', 'markCompleted');
            Route::post('/cancel-order/{id}', 'cancelOrder');
        });
    });
});
