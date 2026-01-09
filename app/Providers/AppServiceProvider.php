<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Space::class => \App\Policies\SpacePolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\EquipmentType::class => \App\Policies\EquipmentTypePolicy::class,
        \App\Models\Reservation::class => \App\Policies\ReservationPolicy::class,
        \App\Models\Invoice::class => \App\Policies\InvoicePolicy::class,
    ];

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
        Schema::defaultStringLength(191);
        
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
