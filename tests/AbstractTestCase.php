<?php

namespace LaravelEloquentSpatial\Tests;

use LaravelEloquentSpatial\EloquentSpatialProvider;
use Orchestra\Testbench\TestCase;

class AbstractTestCase extends TestCase
{
    public function getPackageProviders($app)
    {
        return [EloquentSpatialProvider::class];
    }
}

