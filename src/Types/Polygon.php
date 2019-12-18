<?php

namespace LaravelEloquentSpatial\Types;

/**
 * @property Point[] $exteriorRing
 * @property Point[] $firstInteriorRing
 */
class Polygon
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
}
