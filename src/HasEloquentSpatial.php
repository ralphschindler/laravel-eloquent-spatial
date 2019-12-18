<?php

namespace LaravelEloquentSpatial;

use Illuminate\Database\Eloquent\Model;
use LaravelEloquentSpatial\Types\EwkbFormat;
use LaravelEloquentSpatial\Types\TypeInterface;
use ReflectionClass;
use RuntimeException;

/**
 * @mixin Model
 */
trait HasEloquentSpatial
{
    /** @var TypeInterface[]  */
    protected $eloquentSpatialInstances = [];

    public static function bootHasEloquentSpatial()
    {
        $defaultProperties = (new ReflectionClass(__CLASS__))->getDefaultProperties();

        if (!isset($defaultProperties['spatialAttributes']) || !is_array($defaultProperties)) {
            throw new RuntimeException(__CLASS__ . '::$spatialAttributes must be an array of attributes mapped to their spatial impelementation, see readme for setup instructions');
        }

        // static::addGlobalScope('spatial', function (Builder $builder) {
        //     $model = $builder->getModel();
        //
        //     foreach (array_keys($model->spatialAttributes) as $attribute) {
        //         // @todo check existing select values
        //         $builder->select(['*']);
        //         $builder->addSelect($model->getConnection()->raw("ST_AsGeoJSON($attribute) as $attribute"));
        //     }
        // });

        static::retrieved(function (Model $model) {
            /** @var Model|HasEloquentSpatial $model */
            foreach (array_keys($model->spatialAttributes) as $attribute) {

                $model->eloquentSpatialInstances[$attribute]->setStateFromType(
                    (new EwkbFormat)->convertBinaryToObject($model->attributes[$attribute])
                );

                $model->attributes[$attribute] = $model->eloquentSpatialInstances[$attribute];
            }
        });

        static::saving(function (Model $model) {
            /** @var Model|HasEloquentSpatial $model */
            foreach ($model->spatialAttributes as $attribute => $type) {
                $model->attributes[$attribute] = $model->getConnection()->raw(
                    "X'"
                    . bin2hex((new EwkbFormat)->convertObjectToBinary($model->attributes[$attribute]))
                    . "'"
                );
            }
        });

        static::saved(function (Model $model) {
            /** @var Model|HasEloquentSpatial $model */
            foreach (array_keys($model->spatialAttributes) as $attribute) {
                $model->attributes[$attribute] = $model->eloquentSpatialInstances[$attribute];
            }
        });
    }

    public function initializeHasEloquentSpatial()
    {
        foreach ($this->spatialAttributes as $attribute => $type) {
            $instance = new $type;

            if (!$instance instanceof TypeInterface) {
                throw new RuntimeException(__CLASS__ . '::$spatialAttributes must be EloquentSpatialTypeInterface based classes');
            }

            $this->eloquentSpatialInstances[$attribute] = $instance;
            $this->attributes[$attribute] = $instance;
        }
    }

    // protected function parseGe
}
