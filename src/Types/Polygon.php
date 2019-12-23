<?php

namespace LaravelEloquentSpatial\Types;

/**
 * @property Point[] $exteriorRing
 * @property Point[] $firstInteriorRing
 */
class Polygon implements TypeInterface
{
    public $rings = [];
    public $srid;

    public function __get($property)
    {
        $p = strtolower(str_replace('_', '', $property));

        switch ($p) {
            case 'exteriorring':
                return $this->rings[0] ?? null;
            case 'firstinteriorring':
                return $this->rings[1] ?? null;
            case 'interiorrings':
                return array_slice($this->rings, 1);
            default:
                throw new \OutOfRangeException($property . ' is not a valid property on this object ' . __CLASS__);
        }
    }

    public function setStateFromGeoJson(array $geoJson)
    {
        // TODO: Implement setStateFromGeoJson() method.
    }

    public function exportStateToGeoJson(): array
    {
        return [
            'type'        => 'Polygon',
            'coordinates' => collect($this->rings)->map(function ($ring) {
                return collect($ring)->map(function ($point) {
                    return $point->exportStateToGeoJson()['coordinates'];
                })->toArray();
            })->toArray()
        ];
    }

    public function setStateFromType(TypeInterface $type)
    {
        if (!$type instanceof Polygon) {
            throw new \RuntimeException('Can only set state from a ' . __CLASS__ . ' object');
        }

        $this->rings = $type->rings;
        $this->srid = $type->srid;
    }
}
