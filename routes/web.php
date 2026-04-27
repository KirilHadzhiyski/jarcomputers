<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminBusinessDashboardController;
use App\Http\Controllers\Admin\AdminBusinessReportController;
use App\Http\Controllers\Admin\AdminBusinessResourceController;
use App\Http\Controllers\Admin\AdminPricingAnalysisController;
use App\Http\Controllers\Admin\AdminPricingBenchmarkController;
use App\Http\Controllers\Admin\AdminPricingConfigurationController;
use App\Http\Controllers\Admin\AdminPricingDashboardController;
use App\Http\Controllers\Admin\AdminPricingMarketController;
use App\Http\Controllers\Admin\AdminPricingSourceController;
use App\Http\Controllers\Admin\AdminPricingSyncController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessagingWebhookController;
use App\Http\Controllers\RepairRequestController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TicketController;
use App\Support\SiteData;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/remont-iphone', [SiteController::class, 'mainService'])->name('main-service');
Route::get('/kontakti', [SiteController::class, 'contact'])->name('contact');
Route::get('/ceni', [SiteController::class, 'pricing'])->name('pricing');
Route::get('/za-nas', [SiteController::class, 'about'])->name('about');
Route::get('/chzv', [SiteController::class, 'faq'])->name('faq');
Route::get('/politika-za-poveritelnost', [SiteController::class, 'privacy'])->name('privacy');
Route::get('/obshti-usloviya', [SiteController::class, 'terms'])->name('terms');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SiteController::class, 'robots'])->name('robots');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login')->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:auth-password-email')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:auth-password-reset')->name('password.update');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-register')->name('register.store');
    Route::get('/verify-account', [AuthController::class, 'showVerification'])->name('verification.notice');
    Route::post('/verify-account', [AuthController::class, 'verify'])->middleware('throttle:auth-verify')->name('verification.verify');
    Route::post('/verify-account/resend', [AuthController::class, 'resendVerificationCode'])->middleware('throttle:auth-resend')->name('verification.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('business')->name('business.')->group(function () {
        Route::get('/', [AdminBusinessDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [AdminBusinessReportController::class, 'index'])->name('reports');
        Route::get('/{resource}', [AdminBusinessResourceController::class, 'index'])->name('index');
        Route::get('/{resource}/create', [AdminBusinessResourceController::class, 'create'])->name('create');
        Route::post('/{resource}', [AdminBusinessResourceController::class, 'store'])->name('store');
        Route::get('/{resource}/{record}/edit', [AdminBusinessResourceController::class, 'edit'])->name('edit');
        Route::put('/{resource}/{record}', [AdminBusinessResourceController::class, 'update'])->name('update');
        Route::delete('/{resource}/{record}', [AdminBusinessResourceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('pricing')->name('pricing.')->group(function () {
        Route::get('/', [AdminPricingDashboardController::class, 'index'])->name('dashboard');
        Route::resource('configurations', AdminPricingConfigurationController::class)->except(['show']);
        Route::resource('markets', AdminPricingMarketController::class)->except(['show']);
        Route::resource('sources', AdminPricingSourceController::class)->except(['show']);
        Route::resource('benchmarks', AdminPricingBenchmarkController::class)->except(['show']);
        Route::get('/analysis', [AdminPricingAnalysisController::class, 'index'])->name('analysis.index');
        Route::post('/analysis/run', [AdminPricingAnalysisController::class, 'store'])->name('analysis.store');
        Route::post('/sync', [AdminPricingSyncController::class, 'store'])->name('sync.store');
    });

    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [AdminTicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [AdminTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}/edit', [AdminTicketController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [AdminTicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [AdminTicketController::class, 'destroy'])->name('tickets.destroy');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

foreach (SiteData::services() as $service) {
    Route::get("/{$service['slug']}", [SiteController::class, 'service'])
        ->defaults('slug', $service['slug'])
        ->name("services.{$service['slug']}");
}

foreach (SiteData::models() as $model) {
    Route::get("/{$model['slug']}", [SiteController::class, 'model'])
        ->defaults('slug', $model['slug'])
        ->name("models.{$model['slug']}");
}

foreach (SiteData::cities() as $city) {
    Route::get("/{$city['slug']}", [SiteController::class, 'city'])
        ->defaults('slug', $city['slug'])
        ->name("cities.{$city['slug']}");
}

Route::get('/{serviceBase}-iphone-{series}', [SiteController::class, 'seo'])
    ->where('serviceBase', SiteData::serviceBasesPattern())
    ->where('series', SiteData::modelSeriesPattern())
    ->name('seo.show');

Route::post('/repair-requests', [RepairRequestController::class, 'store'])->middleware('throttle:repair-requests')->name('repair-requests.store');
Route::get('/webhooks/{channel}', [MessagingWebhookController::class, 'verify'])->name('webhooks.verify');
Route::post('/webhooks/{channel}', [MessagingWebhookController::class, 'receive'])->name('webhooks.receive');

Route::fallback([SiteController::class, 'notFound']);
