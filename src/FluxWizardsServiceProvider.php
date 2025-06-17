<?php

namespace Idkwhoami\FluxWizards;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FluxWizardsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->prepareLocalization();

        Blade::directive('step', function (string $expression) {
            return "<?php if (isset(\$this->currentStep) && \$this->currentStep === {$expression}): ?>";
        });

        Blade::directive('endstep', function () {
            return "<?php endif; ?>";
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-wizards');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/flux-wizards'),
        ]);
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
    public function prepareLocalization(): void
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
