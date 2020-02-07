<?php

namespace Proseleta\Listagem;

use Illuminate\Support\ServiceProvider;

class ListagemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/proseleta-listagem.php', 'proseleta-listagem');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // view
        $this->loadViewsFrom(__DIR__.'/resources/views', 'listagem');

        // config
        $this->publishes([
            __DIR__.'/config/proseleta-listagem.php' => config_path('proseleta-listagem.php'),
        ], 'proseleta-listagem');
    }
}
