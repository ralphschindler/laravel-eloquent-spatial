<?php

namespace LaravelEloquentSpatial\Tests\GeoTypes;

use LaravelEloquentSpatial\GeoTypes\Point;
use LaravelEloquentSpatial\Tests\AbstractTestCase;
use Orchestra\Testbench\TestCase;

class PointTest extends AbstractTestCase
{
    public function testSetStateFromGeoJson()
    {
        // $x = config('eloquent-spatial.geojson_assumed_srid');

        $point = new Point;
        $point->setStateFromGeoJson([
            'type' => 'Point',
            'coordinates' => [-90.1052249, 30.0051164]
        ]);

        $this->assertEquals(30.0051164, $point->latitude);
        $this->assertEquals(-90.1052249, $point->longitude);
    }
}

