<?php

namespace LaravelEloquentSpatial\Types;

/**
 * @property float $latitude
 * @property float $longitude
 */
class Point extends AbstractType
{
    public $x;
    public $y;
    public $srid;

    public function __construct()
    {
        // default SRID
        $this->srid = config('eloquent-spatial.geojson_assumed_srid');
    }

    public function setStateFromType(AbstractType $point)
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

    public function setStateFromArray(array $point)
    {
        if (isset($point['latitude'], $point['longitude'])) {
            $this->latitude = $point['latitude'];
            $this->longitude = $point['longitude'];
        }

        if (isset($point['x'], $point['y'])) {
            $this->x = $point['x'];
            $this->y = $point['y'];
        }
    }

    public function exportStateToGeoJson(): array
    {
        return [
            'type'        => 'Point',
            'coordinates' => [$this->x, $this->y]
        ];
    }

    public function isNull()
    {
        return $this->x === null || $this->y === null;
    }

    public function __get($property)
    {
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

    public function toLatitudeLongitudeArray()
    {
        return [
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude
        ];
    }
}

