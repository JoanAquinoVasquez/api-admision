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
        // Definir un limitador para la API
        Schema::defaultStringLength(191);
        RateLimiter::for('api', function () {
            return Limit::perMinute(120);  // Limita a 60 solicitudes por minuto
        });

        Relation::morphMap([
            'Docente' => Docente::class,
            'User' => User::class,  
        ]);
    }
}
