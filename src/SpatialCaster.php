<?php


namespace LaravelEloquentSpatial;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class SpatialCaster implements CastsAttributes
{
    const TYPE_MAP = [
        'point' => Types\Point::class,
        'polygon' => Types\Polygon::class
    ];

    protected $type;

    public function __construct($type)
    {
        if (!isset(static::TYPE_MAP[$type])) {
            throw new \RuntimeException('Not a valid type for ' . __CLASS__);
        }

        $this->type = $type;
    }

    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $typeClass = static::TYPE_MAP[$this->type];

        if (!$value) {
            return new $typeClass;
        }

        $type = (new Types\EwkbFormat)->convertBinaryToObject($value);

        if (!$type instanceof $typeClass) {
            throw new \RuntimeException(
                'For ' . get_class($model) . ' ' . $key . ' was configured to be a '
                . $typeClass . ' but ' . get_class($type) . ' was inferred from the data in the database'
            );
        }

        return $type;
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, string $key, $value, array $attributes)
    {
        $castValue = $model->getConnection()->raw("X'" . $value->exportStateToEwkbHexidecimal() . "'");

        return [$key => $castValue];
    }
}

