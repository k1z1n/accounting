<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Contracts
use App\Contracts\Repositories\ApplicationRepositoryInterface;
use App\Contracts\Repositories\BaseRepositoryInterface;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\DailyUsdtTotalRepositoryInterface;
use App\Contracts\Repositories\ExchangerRepositoryInterface;
use App\Contracts\Repositories\HistoryRepositoryInterface;
use App\Contracts\Repositories\LoginLogRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\PurchaseRepositoryInterface;
use App\Contracts\Repositories\SaleCryptRepositoryInterface;
use App\Contracts\Repositories\TransferRepositoryInterface;
use App\Contracts\Repositories\UpdateLogRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;

use App\Contracts\Services\ApplicationServiceInterface;
use App\Contracts\Services\AuditServiceInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\PlatformServiceInterface;
use App\Contracts\Services\StatisticsServiceInterface;
use App\Contracts\Services\YandexWebmasterServiceInterface;

// Repositories
use App\Repositories\ApplicationRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\DailyUsdtTotalRepository;
use App\Repositories\ExchangerRepository;
use App\Repositories\HistoryRepository;
use App\Repositories\LoginLogRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\SaleCryptRepository;
use App\Repositories\TransferRepository;
use App\Repositories\UpdateLogRepository;
use App\Repositories\UserRepository;

// Services
use App\Services\ApplicationService;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\PlatformService;
use App\Services\StatisticsService;
use App\Services\DashboardService;
use App\Services\YandexWebmasterService;

// Models
use App\Models\Application;
use App\Models\Currency;
use App\Models\DailyUsdtTotal;
use App\Models\Exchanger;
use App\Models\History;
use App\Models\LoginLog;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\SaleCrypt;
use App\Models\Transfer;
use App\Models\UpdateLog;
use App\Models\User;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Регистрация репозиториев
     */
    private function registerRepositories(): void
    {
        // User Repository
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });

        // Application Repository
        $this->app->bind(ApplicationRepositoryInterface::class, function ($app) {
            return new ApplicationRepository($app->make(Application::class));
        });

        // Currency Repository
        $this->app->bind(CurrencyRepositoryInterface::class, function ($app) {
            return new CurrencyRepository($app->make(Currency::class));
        });

        // History Repository
        $this->app->bind(HistoryRepositoryInterface::class, function ($app) {
            return new HistoryRepository($app->make(History::class));
        });

        // Transfer Repository
        $this->app->bind(TransferRepositoryInterface::class, function ($app) {
            return new TransferRepository($app->make(Transfer::class));
        });

        // Payment Repository
        $this->app->bind(PaymentRepositoryInterface::class, function ($app) {
            return new PaymentRepository($app->make(Payment::class));
        });

        // Purchase Repository
        $this->app->bind(PurchaseRepositoryInterface::class, function ($app) {
            return new PurchaseRepository($app->make(Purchase::class));
        });

        // SaleCrypt Repository
        $this->app->bind(SaleCryptRepositoryInterface::class, function ($app) {
            return new SaleCryptRepository($app->make(SaleCrypt::class));
        });

        // Exchanger Repository
        $this->app->bind(ExchangerRepositoryInterface::class, function ($app) {
            return new ExchangerRepository($app->make(Exchanger::class));
        });

        // UpdateLog Repository
        $this->app->bind(UpdateLogRepositoryInterface::class, function ($app) {
            return new UpdateLogRepository($app->make(UpdateLog::class));
        });

        // LoginLog Repository
        $this->app->bind(LoginLogRepositoryInterface::class, function ($app) {
            return new LoginLogRepository($app->make(LoginLog::class));
        });

        // DailyUsdtTotal Repository
        $this->app->bind(DailyUsdtTotalRepositoryInterface::class, function ($app) {
            return new DailyUsdtTotalRepository($app->make(DailyUsdtTotal::class));
        });
    }

    /**
     * Регистрация сервисов
     */
    private function registerServices(): void
    {
        // Auth Service
        $this->app->bind(AuthServiceInterface::class, AuthService::class);

        // Application Service
        $this->app->bind(ApplicationServiceInterface::class, ApplicationService::class);

        // Platform Service
        $this->app->bind(PlatformServiceInterface::class, PlatformService::class);

        // Statistics Service
        $this->app->bind(StatisticsServiceInterface::class, StatisticsService::class);

        // Dashboard Service
        $this->app->singleton(DashboardService::class);

        // Audit Service
        $this->app->bind(AuditServiceInterface::class, AuditService::class);

        // YandexWebmaster Service
        $this->app->bind(YandexWebmasterServiceInterface::class, YandexWebmasterService::class);
    }
}
