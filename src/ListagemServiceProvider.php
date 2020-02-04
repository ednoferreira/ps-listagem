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
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 
        $this->loadViewsFrom(__DIR__.'/resources/views', 'listagem');
    }
}
