<?php

use App\Http\Controllers\Api\AdminAffiliateController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminCouponController;
use App\Http\Controllers\Api\AdminCustomerController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AdminSellerController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\B2B\B2BAdminBuyerController;
use App\Http\Controllers\Api\B2B\B2BAdminController;
use App\Http\Controllers\Api\B2B\B2BAdminSellerController;
use App\Http\Controllers\Api\B2B\B2BBannerPromoController;
use App\Http\Controllers\Api\B2B\ProductCategoryController;
use App\Http\Controllers\Api\BannerPromoController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RewardPointController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\SuperAdminAuthController;
use App\Http\Controllers\Api\SuperAdminController;
use App\Http\Controllers\Api\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware('validate.header')
    ->group(function (): void {
        Route::get('/clear-cache', [SuperAdminController::class, 'clearCache']);
        Route::post('/run-migration', [SuperAdminController::class, 'runMigration']);
        Route::post('/seed-run', [SuperAdminController::class, 'seedRun']);

        Route::prefix('connect')->controller(AdminAuthController::class)->group(function (): void {
            Route::post('/login', 'login');
            Route::post('/forgot/password', 'forgot');
            Route::post('/reset/password', 'reset');
            Route::post('/logout', 'logout');
            Route::post('/verify/email', 'verify');
        });

        Route::controller(ApiController::class)
            ->group(function (): void {
                Route::get('brands', 'brands');
                Route::get('colors', 'colors');
                Route::get('units', 'units');
                Route::get('sizes', 'sizes');
            });

        Route::middleware(['auth:sanctum', 'auth-gates', 'auth.check'])
            ->group(function (): void {
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
                    Route::post('/edit', 'editCategory');
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

                    Route::prefix('category')->controller(ProductCategoryController::class)->group(function (): void {
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
                    Route::get('/', 'getProducts');
                    Route::get('/{slug}', 'getOneProduct');
                    Route::patch('/featured', 'changeFeatured');
                });

                Route::prefix('reward')->controller(RewardPointController::class)->group(function (): void {
                    Route::post('/action', 'addPoints');
                    Route::get('/action', 'getPoints');
                    Route::get('/action/{id}', 'getOnePoints');
                    Route::post('/action/{id}', 'editPoints');
                    Route::delete('/delete/{id}', 'deletePoints');

                    Route::post('/point/setting', 'addPointSetting');
                    Route::get('/point/setting', 'getPointSetting');
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

                // Shipping Agency
                Route::prefix('shipping-management')
                    ->controller(B2BAdminController::class)
                    ->group(function (): void {
                        Route::get('/', 'shippingAgents');
                        Route::post('/add', 'addShippingAgent');
                        Route::get('/details/{id}', 'viewShippingAgent');
                        Route::post('/update/{id}', 'editShippingAgent');
                        Route::delete('/delete/{id}', 'deleteShippingAgent');
                    });

                Route::prefix('affiliate')
                    ->controller(AdminAffiliateController::class)
                    ->group(function (): void {
                        Route::get('/overview', 'overview')
                            ->middleware('cacheResponse:600');
                        Route::get('/users', 'allUsers')
                            ->middleware('cacheResponse:600');
                        Route::get('/user/{id}', 'userDetail');
                        Route::patch('/suspend/{id}', 'suspend');
                        Route::post('/reset-password', 'resetPassword');
                    });

                Route::controller(AdminController::class)
                    ->group(function (): void {
                        // Admin users
                        Route::prefix('admin-users')->group(function (): void {
                            Route::get('/', 'adminUsers');
                            Route::post('/add', 'addAdmin');
                            Route::get('/details/{id}', 'viewAdminUser');
                            Route::post('/update/{id}', 'editAdminUser');
                            Route::post('/revoke-access/{id}', 'revokeAccess');
                            Route::post('/verify-password', 'verifyPassword');
                            Route::delete('/delete-account/{id}', 'removeAdmin');
                        });

                        Route::prefix('profile')->group(function () {
                            Route::get('/', 'adminProfile');
                            Route::post('/update', 'updateAdminProfile');
                            Route::post('/verify-password', 'verifyPassword');
                            Route::get('/send-code', 'sendCode');
                            Route::post('/verify-code', 'verifyCode');
                            Route::post('/update-password', 'updateAdminPassword');
                            Route::post('/enable-2fa', 'enable2FA');
                        });
                    });

                // b2b admin
                Route::prefix('b2b')->group(function (): void {
                    Route::controller(B2BAdminController::class)->group(function (): void {
                        Route::get('/dashboard', 'dashboard');

                        Route::get('/profile', 'adminProfile');
                        Route::post('/update-profile', 'updateAdminProfile');
                        Route::post('/update-password', 'updateAdminPassword');
                        Route::post('/enable-2fa', 'enable2FA');
                        Route::get('/get-config', 'getConfigDetails');
                        Route::post('/update-config', 'updateConfigDetails');

                        Route::prefix('page-banners')->group(function (): void {
                            Route::get('/', 'getAllBanners');
                            Route::post('/add', 'addNewBanner');
                            Route::post('/update/{id}', 'updatePageBanner');
                            Route::get('/view/{id}', 'editPageBanner');
                            Route::delete('/delete/{id}', 'deletePageBanner');
                        });

                        Route::prefix('blog')->group(function (): void {
                            Route::get('/', 'getBlogs');
                            Route::post('/create', 'addBlog');
                            Route::get('/details/{id}', 'getBlog');
                            Route::post('/update/{id}', 'updateBlog');
                            Route::delete('/delete/{id}', 'deleteBlog');
                        });

                        Route::prefix('client-logo')->group(function (): void {
                            Route::get('/', 'allClientLogos');
                            Route::post('/create', 'addClientLogo');
                            Route::get('/details/{id}', 'getClientLogo');
                            Route::post('/update/{id}', 'updateClientLogo');
                            Route::delete('/delete/{id}', 'deleteClientLogo');
                        });
                        Route::prefix('social-links')->group(function (): void {
                            Route::get('/', 'socialLinks');
                            Route::post('/add', 'addLink');
                            Route::get('/details/{id}', 'viewLink');
                            Route::post('/update/{id}', 'editLink');
                            Route::delete('/delete/{id}', 'deleteLink');
                        });
                    });

                    Route::prefix('admin-users')
                        ->controller(AdminController::class)
                        ->group(function (): void {
                            Route::get('/', 'adminUsers');
                            Route::post('/add', 'addAdmin');
                            Route::get('/details/{id}', 'viewAdminUser');
                            Route::post('/update/{id}', 'editAdminUser');
                            Route::post('/revoke-access/{id}', 'revokeAccess');
                            Route::post('/verify-password', 'verifyPassword');
                            Route::delete('/delete-account/{id}', 'removeAdmin');
                        });

                    Route::prefix('category')
                        ->controller(ProductCategoryController::class)
                        ->group(function (): void {
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

                    Route::controller(B2BBannerPromoController::class)
                        ->group(function (): void {
                            Route::prefix('banner')->group(function (): void {
                                Route::post('/add', 'addBanner');
                                Route::get('/', 'banners')->middleware('cacheResponse:300');
                                Route::get('/{id}', 'getOneBanner');
                                Route::post('/edit/{id}', 'editBanner');
                                Route::delete('/delete/{id}', 'deleteBanner');
                            });
                            Route::prefix('slider')->group(function (): void {
                                Route::get('/', 'sliders');
                                Route::post('/add', 'addSlider');
                                Route::get('/view/{id}', 'getSlider');
                                Route::post('/update/{id}', 'updateSlider');
                                Route::delete('/delete/{id}', 'deleteSlider');
                            });
                        });

                    Route::prefix('promo')
                        ->controller(B2BBannerPromoController::class)
                        ->group(function (): void {
                            Route::post('/add', 'addPromo');
                            Route::get('/', 'promos');
                            Route::get('/products', 'getProducts')->middleware('cacheResponse:300');
                            Route::delete('/delete/{id}', 'deletePromo');
                        });

                    // buyers
                    Route::prefix('buyer')
                        ->controller(B2BAdminBuyerController::class)
                        ->group(function (): void {
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

                    // Sellers
                    Route::prefix('seller')
                        ->controller(B2BAdminSellerController::class)
                        ->group(function (): void {
                            Route::get('/', 'allSellers')->middleware('cacheResponse:300');
                            Route::get('/details/{user_id}', 'viewSeller')->middleware('cacheResponse:300');
                            Route::delete('/remove/{user_id}', 'removeSeller');
                            Route::post('/approve/{id}', 'approveSeller');
                            Route::post('/ban/{id}', 'banSeller');
                            Route::delete('/bulk/remove', 'bulkRemove');

                            Route::prefix('product')
                                ->controller(B2BAdminSellerController::class)
                                ->group(function (): void {
                                    Route::post('/add', 'addSellerProduct');
                                    Route::get('/details/{user_id}/{id}', 'viewSellerProduct');
                                    Route::post('/update/{user_id}/{id}', 'editSellerProduct');
                                    Route::delete('/delete/{user_id}/{id}', 'removeSellerProduct');
                                });
                        });

                    // Withdrawal requests
                    Route::prefix('widthrawal-request')
                        ->controller(B2BAdminController::class)
                        ->group(function (): void {
                            Route::get('/', 'widthrawalRequests')->middleware('cacheResponse:300');
                            Route::get('/view/{id}', 'viewWidthrawalRequest')->middleware('cacheResponse:300');
                            Route::post('/approve/{id}', 'approveWidthrawalRequest');
                            Route::post('/cancel/{id}', 'cancelWidthrawalRequest');
                        });

                    // Withdrawal method requests
                    Route::prefix('widthrawal-method-request')
                        ->controller(B2BAdminController::class)
                        ->group(function (): void {
                            Route::get('/', 'widthrawalMethods')->middleware('cacheResponse:300');
                            Route::get('/view/{id}', 'viewWidthrawalMethod')->middleware('cacheResponse:300');
                            Route::post('/approve/{id}', 'approveWidthrawalMethod');
                            Route::post('/reject/{id}', 'rejectWidthrawalMethod');
                        });

                    // Subscriprion plans
                    Route::prefix('subscription-plans')
                        ->controller(B2BAdminController::class)
                        ->group(function (): void {
                            Route::get('/', 'b2bSubscriptionPlans');
                            Route::post('add/', 'addSubscriptionPlan');
                            Route::get('/details/{id}', 'viewSubscriptionPlan');
                            Route::post('/update/{id}', 'editSubscriptionPlan');
                            Route::delete('/remove/{id}', 'deleteSubscriptionPlan');
                        });

                    // Seller Product Approval requests
                    Route::prefix('product-approval-request')
                        ->controller(B2BAdminController::class)
                        ->group(function (): void {
                            Route::get('/', 'allProducts');
                            Route::get('/view/{id}', 'viewProduct');
                            Route::post('/approve/{id}', 'approveProduct');
                            Route::post('/reject/{id}', 'rejectProduct');
                        });

                    // Rfq
                    Route::middleware('cacheResponse:300')
                        ->prefix('rfqs')
                        ->controller(B2BAdminController::class)
                        ->group(function (): void {
                            Route::get('/', 'allRfq');
                            Route::get('/details/{id}', 'rfqDetails');
                        });

                    // Orders
                    Route::middleware('cacheResponse:300')
                        ->prefix('orders')
                        ->controller(B2BAdminController::class)
                        ->group(function (): void {
                            Route::get('/', 'allOrders');
                            Route::get('/details/{id}', 'orderDetails');
                            Route::post('/mark-completed/{id}', 'markCompleted');
                            Route::post('/cancel-order/{id}', 'cancelOrder');
                        });
                });
            });

        // Super admin
        Route::prefix('super-admin')
            ->group(function (): void {
                Route::prefix('/auth')
                    ->controller(SuperAdminAuthController::class)
                    ->group(function () {
                        Route::post('/connect', 'login');
                        Route::post('/forgot/password', 'forgot');
                        Route::post('/verify/email', 'verifyEmail');
                        Route::post('/reset/password', 'reset');
                    });

                Route::middleware(['auth:sanctum', 'role.super_admin'])
                    ->controller(SuperAdminController::class)
                    ->group(function (): void {
                        Route::post('/add-user', 'addUser');
                        Route::get('/profiles', 'getProfiles');
                        Route::post('/security', 'security');
                        Route::post('/verify-code', 'verifyCode');
                        Route::post('/change-password', 'changePassword');
                        Route::get('/profile/{user_id}', 'getProfile');

                        // delivery (collation centers and hubs)
                        Route::controller(AdminController::class)->group(function (): void {
                            Route::get('/delivery-overview', 'deliveryOverview');
                            Route::prefix('collation-centre')->group(function (): void {
                                Route::get('/', 'allCollationCentres');
                                Route::post('/add', 'addCollationCentre');
                                Route::post('/log-item', 'findCollationCentreOrder');
                                Route::get('/details/{id}', 'viewCollationCentre');
                                Route::patch('/update/{id}', 'editCollationCentre');
                                Route::delete('/delete/{id}', 'deleteCollationCentre');

                                Route::prefix('hubs')->group(function (): void {
                                    Route::get('/', 'allCollationCentreHubs');
                                    Route::post('/add', 'addHub');
                                    Route::post('/log-item', 'findPickupLocationOrder');
                                    Route::get('/details/{id}', 'viewHub');
                                    Route::patch('/update/{id}', 'editHub');
                                    Route::delete('/delete/{id}', 'deleteHub');
                                });
                            });

                            Route::prefix('notification')->group(function (): void {
                                Route::get('/', 'getNotifications');
                                Route::get('/details/{id}', 'getNotification');
                                Route::patch('/mark-read/{id}', 'markRead');
                            });

                            Route::prefix('shipments')->group(function (): void {
                                Route::get('/', 'allShipments');
                                Route::get('/order-finder/{order_number}', 'findOrder');
                                Route::get('/details/{id}', 'shippmentDetails');
                                Route::patch('/update/{id}', 'updateShippmentDetails');
                                Route::patch('/delivery/{id}', 'readyForDelivery');
                                Route::patch('/return/{id}', 'returnToSender');
                                Route::patch('/pickup/{id}', 'readyForPickup');
                                Route::patch('/dispatched/{id}', 'readyForDispatched');
                                Route::patch('/transfer/{id}', 'transferShippment');
                            });

                            Route::prefix('batch')->group(function (): void {
                                Route::get('/', 'allShipments');
                                Route::post('/create', 'createBatch');
                                Route::get('/details/{id}', 'batchDetails');
                            });
                        });
                });
            });
    });
