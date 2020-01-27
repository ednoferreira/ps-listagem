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
        // aqui declaramos de onde o pacote irá ler as views, prestando atenção no namespace
        $this->loadViewsFrom(__DIR__.'/resources/views', 'listagem');
    }
}
