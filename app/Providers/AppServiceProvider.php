<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/** Laravel's application service provider. BAKR registers nothing here; it is kept because bootstrap/providers.php loads it. */
class AppServiceProvider extends ServiceProvider
{
    /** Registers application services (none). */
    public function register(): void
    {
        //
    }

    /** Bootstraps application services (none). */
    public function boot(): void
    {
        //
    }
}
