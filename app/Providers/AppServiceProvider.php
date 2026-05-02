<?php

namespace App\Providers;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; 

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
        Schema::defaultStringLength(191);

        // Rate Limiter general (catálogos, consultas públicas)
        // 300 req/min por IP — suficiente para periodo de inscripciones
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(300)->by($request->ip());
        });

        // Rate Limiter estricto (login, inscripción, validar-voucher)
        // 10 req/min por IP — previene ataques de fuerza bruta
        RateLimiter::for('sensitive', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        Relation::morphMap([
            'Docente' => Docente::class,
            'User' => User::class,
        ]);
    }
}
