<?php

namespace Idkwhoami\FluxWizards;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FluxWizardsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::directive('step', function (string $expression) {
            return "<?php if (isset(\$this->currentStep) && \$this->currentStep === {$expression}): ?>";
        });

        Blade::directive('end-step', function ($expression) {
            return "<?php endif; ?>";
        });
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

}
