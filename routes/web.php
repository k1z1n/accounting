<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleCryptController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WalletHistoryController;
use App\Http\Controllers\YandexWebmasterController;
use Illuminate\Support\Facades\Route;

// Тестовый маршрут для AG Grid
Route::get('/test-ag-grid', function () {
    return view('test-ag-grid');
});

Route::middleware('guest')->group(function () {
//    Route::get('register', [AuthController::class, 'viewRegister'])->name('view.register');
    Route::get('login', [AuthController::class, 'viewLogin'])->name('view.login');
    Route::post('login', [AuthController::class, 'login'])->name('login.perform');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', [MainController::class, 'viewMain'])->name('view.main');

    Route::put('/applications/{id}', [MainController::class, 'update']);
    Route::get('/usdt-total', [MainController::class, 'usdtTotal'])->name('usdt.total');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'dashboard'])->name('view.profile');
    Route::get('/api/wallets/balances', [ProfileController::class, 'balances'])->name('api.wallets.balances');
    Route::get('/api/wallets/history', [ProfileController::class, 'history'])->name('api.wallets.history');
    Route::get('/chart/usdt', [MainController::class, 'usdtChart']);
    Route::resource('purchases', PurchaseController::class)->only(['index', 'show']);
    Route::resource('transfers', TransferController::class)->only(['index', 'show']);
    Route::resource('payments', PaymentController::class)->only(['index', 'show']);
    Route::resource('sale-crypts', SaleCryptController::class)->only(['index', 'show']);
    Route::get('/api/applications/{id}', [MainController::class, 'apiShowApplication']);
    Route::get('/api/applications', [MainController::class, 'apiApplications'])->name('api.applications');
    Route::get('/history/all', [MainController::class, 'allHistory'])->name('history.all');
    Route::get('/purchase/{purchase}', [\App\Http\Controllers\PurchaseController::class, 'show'])->name('purchase.show');
    Route::get('/salecrypt/{saleCrypt}', [\App\Http\Controllers\SaleCryptController::class, 'show'])->name('salecrypt.show');
    Route::get('/payment/{payment}', [\App\Http\Controllers\PaymentController::class, 'show'])->name('payment.show');
    Route::get('/transfer/{transfer}', [\App\Http\Controllers\TransferController::class, 'show'])->name('transfer.show');
});

Route::middleware(['admin'])->prefix('admin')->group(function () {

    Route::resource('currencies', CurrencyController::class)->except(['show', 'index', 'create']);
    Route::resource('platforms', PlatformController::class)->except(['show', 'index', 'create', 'store']);

    Route::resource('purchases', PurchaseController::class)->only(['index','update','destroy']);
    Route::resource('transfers', TransferController::class)->only(['index','update','destroy']);
    Route::resource('payments', PaymentController::class)->only(['index','update','destroy']);
    Route::resource('sale-crypts', SaleCryptController::class)->only(['index','update','destroy']);

    Route::get('/user/updates', [AdminController::class, 'viewUpdateLogs'])->name('view.update.logs');
    Route::get('/list/exchangers', [AdminController::class, 'viewExchangers'])->name('view.exchangers');
    Route::get('/list/currencies', [AdminController::class, 'viewCurrencies'])->name('view.currencies');
    Route::get('exchangers/create', [AdminController::class, 'createExchanger'])->name('exchangers.create');
    Route::post('exchangers', [AdminController::class, 'storeExchanger'])->name('exchangers.store');
    Route::post('payments',   [AdminController::class, 'storePayment'])->name('payments.store');
    Route::post('transfers',  [AdminController::class, 'storeTransfer'])->name('transfers.store');
    Route::post('salecrypts', [AdminController::class, 'storeSaleCrypt'])->name('salecrypts.store');
    Route::post('/purchases', [AdminController::class, 'storePurchase'])->name('purchases.store');
    Route::get('/currencies/create', [CurrencyController::class, 'create'])->name('view.currency.create');

    Route::get('/wallets/history',    [WalletHistoryController::class, 'index'])->name('wallets.history');
    Route::get('/wallets/history/data',[WalletHistoryController::class, 'data' ])->name('wallets.history.data');

//    Route::post('/currencies', [CurrencyController::class, 'store'])->name('currencies.store');
    Route::get('/login/logs', [AdminController::class, 'viewUserLogs'])->name('view.user.logs');
    Route::post('/user/{id:int}/update/role', [AdminController::class, 'updateStatus'])->name('user.update.role');
    Route::post('/user/{id:int}/update/blocked', [AdminController::class, 'updateBlocked'])->name('user.update.blocked');
    Route::get('/user/register', [AdminController::class, 'viewRegisterUser'])->name('view.register.user');
    Route::post('register', [AuthController::class, 'register'])->name('register.perform');

    Route::post('/applications', [MainController::class, 'storeApplication'])->name('applications.store');
    Route::put('/applications/{id}', [MainController::class, 'updateApplication'])->name('applications.update');
    Route::get('/api/applications/{id}', [MainController::class, 'apiShowApplication']);
});

// Маршруты для Яндекс.Вебмастера
Route::middleware(['auth', 'platform'])->prefix('webmaster')->name('webmaster.')->group(function () {
    Route::get('/dashboard', [YandexWebmasterController::class, 'dashboard'])->name('dashboard');
    Route::get('/comparison', [YandexWebmasterController::class, 'comparison'])->name('comparison');

    // API маршруты
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stats', [YandexWebmasterController::class, 'getStats'])->name('stats');
        Route::get('/search-queries', [YandexWebmasterController::class, 'getSearchQueries'])->name('search_queries');
        Route::get('/indexing', [YandexWebmasterController::class, 'getIndexing'])->name('indexing');
        Route::get('/crawl-errors', [YandexWebmasterController::class, 'getCrawlErrors'])->name('crawl_errors');
        Route::get('/external-links', [YandexWebmasterController::class, 'getExternalLinks'])->name('external_links');
        Route::get('/sites', [YandexWebmasterController::class, 'getSites'])->name('sites');
        Route::get('/test-connection', [YandexWebmasterController::class, 'testConnection'])->name('test_connection');
        Route::post('/export', [YandexWebmasterController::class, 'export'])->name('export');
    });
});


