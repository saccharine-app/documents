<?php

namespace Saccharine\Documents;

use Illuminate\Support\ServiceProvider;

class DocumentsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load the migrations we built earlier
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        
    }
}