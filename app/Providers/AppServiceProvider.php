<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Review;
use App\Observers\ProductMeilisearchObserver;
use App\Observers\ProductObserver;
use App\Observers\ReviewObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->defineGates();
        Review::observe(ReviewObserver::class);
        Product::observe(ProductObserver::class);
        Product::observe(ProductMeilisearchObserver::class);
    }

    /**
     * Define os gates de autorização do sistema.
     */
    protected function defineGates(): void
    {
        Gate::define('admin.only', fn ($user) => $user?->isAdmin() ?? false);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
