<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Inyecta los módulos activos del tenant en todas las vistas
        View::composer('*', function ($view) {
            $modulos = collect();
            try {
                if (tenancy()->tenant !== null && Schema::hasTable('catalog_modules')) {
                    $modulos = \App\Models\CatalogModule::where('activo', true)
                        ->orderBy('orden')
                        ->get(['id', 'nombre', 'slug', 'icono', 'color']);
                }
            } catch (\Throwable) {
                // Sin tenant activo o BD no migrada — colección vacía
            }
            $view->with('nav_modulos', $modulos);
        });
    }
}
