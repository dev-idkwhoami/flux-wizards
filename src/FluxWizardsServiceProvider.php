<?php

namespace Idkwhoami\FluxWizards;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class FluxWizardsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->prepareConfig();
        $this->prepareLocalization();
        $this->prepareCommands();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-wizards');

        $this->loadViewComponentsAs('flux-wizards', [
            //
        ]);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/flux-wizards'),
        ], 'flux-wizards-views');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * @return void
     */
    private function prepareCommands(): void
    {
        $this->commands([
            //
        ]);
    }

    /**
     * @return void
     */
    private function prepareConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/flux-wizards.php' => config_path('flux-wizards.php'),
        ], [
            'flux-wizards-config',
            'flux-wizards'
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/flux-wizards.php',
            'flux-wizards'
        );
    }

    /**
     * @return void
     */
    public function prepareLocalization()
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'flux-wizards');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/flux-wizards'),
        ], [
            'flux-wizards-lang',
            'flux-wizards'
        ]);
    }

}
