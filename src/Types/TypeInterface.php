<?php

namespace LaravelEloquentSpatial\Types;

interface TypeInterface
{
    public function setStateFromGeoJson(array $geoJson);
    public function exportStateToGeoJson(): array;
    public function setStateFromType(TypeInterface $type);
}
