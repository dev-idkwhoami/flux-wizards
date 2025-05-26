<?php


use Illuminate\Support\ServiceProvider;

class FluxWizardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->prepareConfig();
        $this->prepareLocalization();
        $this->prepareCommands();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-wizards');

        $this->loadViewComponentsAs('flux-wizards', [
            //
        ]);

        $this->publishes([
            __DIR__.'/../resources/views/flux' => \Idkwhoami\FluxWizards\resource_path('views/flux'),
        ], 'flux-wizards-flux-views');

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
            __DIR__.'/../config/flux-wizards.php' => \Idkwhoami\FluxWizards\config_path('flux-wizards.php'),
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
    public function prepareLocalization(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'flux-wizards');

        $this->publishes([
            __DIR__.'/../lang' => \Idkwhoami\FluxWizards\lang_path('vendor/flux-wizards'),
        ], [
            'flux-wizards-lang',
            'flux-wizards'
        ]);
    }

}