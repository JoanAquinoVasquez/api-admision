<?php

namespace App\Providers;

use App\Repositories\Contracts\InscripcionRepositoryInterface;
use App\Repositories\Contracts\PostulanteRepositoryInterface;
use App\Repositories\Contracts\PreInscripcionRepositoryInterface;
use App\Repositories\Contracts\ProgramaRepositoryInterface;
use App\Repositories\Contracts\VoucherRepositoryInterface;
use App\Repositories\Contracts\NotaRepositoryInterface;
use App\Repositories\Contracts\FacultadRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\InscripcionRepository;
use App\Repositories\PostulanteRepository;
use App\Repositories\PreInscripcionRepository;
use App\Repositories\ProgramaRepository;
use App\Repositories\VoucherRepository;
use App\Repositories\NotaRepository;
use App\Repositories\FacultadRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(InscripcionRepositoryInterface::class, InscripcionRepository::class);
        $this->app->bind(PostulanteRepositoryInterface::class, PostulanteRepository::class);
        $this->app->bind(PreInscripcionRepositoryInterface::class, PreInscripcionRepository::class);
        $this->app->bind(ProgramaRepositoryInterface::class, ProgramaRepository::class);
        $this->app->bind(VoucherRepositoryInterface::class, VoucherRepository::class);
        $this->app->bind(NotaRepositoryInterface::class, NotaRepository::class);
        $this->app->bind(FacultadRepositoryInterface::class, FacultadRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
