<?php

namespace LaravelEloquentSpatial\Types;

abstract class AbstractType
{
    abstract public function setStateFromGeoJson(array $geoJson);

    abstract public function exportStateToGeoJson(): array;

    abstract public function setStateFromType(AbstractType $type);

    public function exportStateToEwkb(): string
    {
        return (new EwkbFormat)->convertObjectToBinary($this);
    }

    public function exportStateToEwkbHexidecimal(): string
    {
        return bin2hex((new EwkbFormat)->convertObjectToBinary($this));
    }
}
