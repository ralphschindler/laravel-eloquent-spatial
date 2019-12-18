<?php

namespace LaravelEloquentSpatial\Types;

use LaravelEloquentSpatial\Types\TypeInterface;

/**
 * @property float $latitude
 * @property float $longitude
 */
class Point implements TypeInterface
{
    public $x;
    public $y;
    public $srid;

    public function __construct()
    {
        // default SRID
        $this->srid = config('eloquent-spatial.geojson_assumed_srid');
    }

    public function setStateFromType(TypeInterface $point)
    {
        if (!$point instanceof Point) {
            throw new \RuntimeException('State for ' . __CLASS__ . ' can only be set from an instance of ' . __CLASS__);
        }

        $this->x = $point->x;
        $this->y = $point->y;
        $this->srid = $point->srid;
    }

    public function setStateFromGeoJson(array $geoJson)
    {
        if (!isset($geoJson['type']) || $geoJson['type'] !== 'Point') {
            throw new \InvalidArgumentException('The geojson provided does not have "type": "Point" as described by geojson spec');
        }

        if (!isset($geoJson['crs']) && config('eloquent-spatial.geojson_assumed_srid')) {
            $this->srid = config('eloquent-spatial.geojson_assumed_srid');
        }

        // GeoJson is Longitude first then Latitude
        $this->x = $geoJson['coordinates'][0];
        $this->y = $geoJson['coordinates'][1];
    }

    public function exportStateToGeoJson(): array
    {
        return [
            'type'        => 'Point',
            'coordinates' => [$this->x, $this->y]
        ];
    }

    public function __get($property)
    {
        // SRID's that define lat first, as the x coordinate
        // $latFirst = in_array($this->srid, [4326]);

        switch ($property) {
            case 'longitude':
                return $this->x;
            case 'latitude':
                return $this->y;
            default:
                throw new \OutOfRangeException($property . ' is not a valid property of ' . __CLASS__);
        }
    }

    public function __set($property, $value)
    {
        switch ($property) {
            case 'longitude':
                $this->x = $value;
                return;
            case 'latitude':
                $this->y = $value;
                return;
            default:
                throw new \OutOfRangeException($property . ' is not a valid property of ' . __CLASS__);
        }
    }
}

