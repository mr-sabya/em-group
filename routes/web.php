<?php

use App\Http\Middleware\SetDefaultTenantParameter;
use Illuminate\Support\Facades\Route;

use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

// home page
Route::get('/', function () {
    return view('frontend.home.index');
})->name('home');

// Guest Routes (Login Page)
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/forgot-password', [App\Http\Controllers\Admin\AdminController::class, 'showForgotPassword'])->name('password.request');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Admin\AdminController::class, 'showResetPassword'])->name('password.reset');
});

Route::middleware('auth:admin')->prefix('dashboard')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('dashboard');
    
    // create tenant
    Route::get('/tenant/create', [App\Http\Controllers\Admin\TenantController::class, 'create'])->name('tenants.create');


    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');

    Route::name('product.')->group(function () {

        // categories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('create');
            Route::get('/{category}/edit', [App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('edit');
        });

        // brands
        Route::get('/brands', [App\Http\Controllers\Admin\HomeController::class, 'brands'])->name('brands.index');

        // coupons
        Route::prefix('coupons')->name('coupons.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CouponController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\CouponController::class, 'create'])->name('create');
            Route::get('/{coupon}/edit', [App\Http\Controllers\Admin\CouponController::class, 'edit'])->name('edit');
        });

        // tags
        Route::get('/tags', [App\Http\Controllers\Admin\HomeController::class, 'tags'])->name('tags.index');

        // products
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('create');
            Route::get('/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('edit');

            // reviews
        });
        Route::get('/reviews', [App\Http\Controllers\Admin\ProductController::class, 'review'])->name('reviews.index');
    });


    Route::name('users.')->group(function () {
        // customers
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'customers'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'createCustomer'])->name('create');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'editCustomer'])->name('edit');
        });


        // investors
        Route::prefix('investors')->name('investors.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'investors'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'createInvestor'])->name('create');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'editInvestor'])->name('edit');
        });


        // vendors
        Route::prefix('vendors')->name('vendors.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'vendors'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'createVendors'])->name('create');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'editVendors'])->name('edit');
        });
    });



    // locations
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/countries', [App\Http\Controllers\Admin\LocationController::class, 'countries'])->name('countries');
        Route::get('/states', [App\Http\Controllers\Admin\LocationController::class, 'states'])->name('states');
        Route::get('/cities', [App\Http\Controllers\Admin\LocationController::class, 'cities'])->name('cities');
    });

    // investment
    Route::prefix('investment')->name('investment.')->group(function () {
        Route::get('/projects', [App\Http\Controllers\Admin\ProjectController::class, 'index'])->name('projects.index');
        Route::get('/investments', [App\Http\Controllers\Admin\InvestmentController::class, 'index'])->name('investments.index');
    });

    // attributes
    Route::prefix('attributes')->name('attribute.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AttributeController::class, 'attributes'])->name('attributes.index');
        Route::get('/attribute-values', [App\Http\Controllers\Admin\AttributeController::class, 'attributeValues'])->name('attribute-values.index');
        Route::get('/attribute-sets', [App\Http\Controllers\Admin\AttributeController::class, 'attributeSets'])->name('attribute-sets.index');
    });

    // website
    Route::prefix('website')->name('website.')->group(function () {
        // banners
        Route::get('/banners', [App\Http\Controllers\Admin\WebsiteController::class, 'banners'])->name('banner.index');

        // feature
        Route::get('/features', [App\Http\Controllers\Admin\WebsiteController::class, 'features'])->name('feature.index');

        Route::get('/about', [App\Http\Controllers\Admin\AboutController::class, 'index'])->name('about.index');

        // ad banner
        Route::get('/ad-banner', [App\Http\Controllers\Admin\AdBannerController::class, 'index'])->name('ad-banner.index');
    });

    // orders
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('order.index');

    Route::get('/orders/{orderId}/invoice', [App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('orders.invoice');

    // manage order
    Route::get('/orders/{orderId}/manage', [App\Http\Controllers\Admin\OrderController::class, 'manage'])->name('orders.manage');

    // deals
    Route::prefix('deals')->name('deal.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\DealController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\DealController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\DealController::class, 'edit'])->name('edit');
    });

    // collection
    Route::prefix('collection')->name('collection.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CollectionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\CollectionController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\CollectionController::class, 'edit'])->name('edit');
    });


    // blog
    Route::prefix('blog')->name('blog.')->group(function () {
        // category
        Route::get('/category', [App\Http\Controllers\Admin\Blog\CategoryController::class, 'index'])->name('category.index');

        // tag
        Route::get('/tag', [App\Http\Controllers\Admin\Blog\TagController::class, 'index'])->name('tag.index');

        // blog post
        Route::get('/blog-post', [App\Http\Controllers\Admin\Blog\BlogPostController::class, 'index'])->name('post.index');
        Route::get('/blog-post/create', [App\Http\Controllers\Admin\Blog\BlogPostController::class, 'create'])->name('post.create');
        Route::get('/blog-post/{id}/edit', [App\Http\Controllers\Admin\Blog\BlogPostController::class, 'edit'])->name('post.edit');
    });


    // page
    Route::prefix('page')->name('page.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PageController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\PageController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\PageController::class, 'edit'])->name('edit');
    });

    // shipping methods
    Route::get('/shipping-methods', [App\Http\Controllers\Admin\ShippingMethodController::class, 'index'])->name('shipping-method.index');

    // payment methods
    Route::get('/payment-methods', [App\Http\Controllers\Admin\PaymentMethodController::class, 'index'])->name('payment-method.index');


    Route::get('/profile', [App\Http\Controllers\Admin\AdminController::class, 'profile'])->name('profile');
    Route::get('/change-password', [App\Http\Controllers\Admin\AdminController::class, 'changePassword'])->name('change-password');
});
