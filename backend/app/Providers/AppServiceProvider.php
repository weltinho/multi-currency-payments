<?php

namespace App\Providers;

use App\Contracts\Auth\AuthServiceContract;
use App\Contracts\Employee\EmployeeServiceContract;
use App\Contracts\ExchangeRate\ExchangeRateServiceContract;
use App\Contracts\Payment\PaymentRepositoryContract;
use App\Contracts\Payment\PaymentServiceContract;
use App\Contracts\TestUser\TestUserServiceContract;
use App\Contracts\Translation\TranslatorContract;
use App\Repositories\EloquentPaymentRepository;
use App\Services\Auth\AuthService;
use App\Services\Employee\EmployeeService;
use App\Services\ExchangeRate\ExchangeRateService;
use App\Services\Payment\PaymentService;
use App\Services\TestUser\TestUserService;
use App\Services\Translation\Translator;
use App\Support\ScrambleConfigurator;
use Illuminate\Support\ServiceProvider;

/**
 * Binds interfaces to concrete services so controllers stay thin and unit tests
 * can swap implementations (e.g. mock PaymentRepositoryContract).
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TranslatorContract::class, Translator::class);
        $this->app->singleton(ExchangeRateServiceContract::class, ExchangeRateService::class);
        $this->app->singleton(EmployeeServiceContract::class, EmployeeService::class);
        $this->app->singleton(PaymentRepositoryContract::class, EloquentPaymentRepository::class);
        $this->app->singleton(PaymentServiceContract::class, PaymentService::class);
        $this->app->singleton(AuthServiceContract::class, AuthService::class);
        $this->app->singleton(TestUserServiceContract::class, TestUserService::class);
    }

    public function boot(): void
    {
        ScrambleConfigurator::register();
    }
}
