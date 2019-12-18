<?php

namespace LaravelEloquentSpatial;

use Illuminate\Support\ServiceProvider;

class EloquentSpatialProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/eloquent-spatial.php' => config_path('eloquent-spatial.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/eloquent-spatial.php', 'eloquent-spatial'
        );
    }
}
