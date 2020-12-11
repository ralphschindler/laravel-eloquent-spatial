<?php

namespace LaravelEloquentSpatial;

use Illuminate\Database\Eloquent\Model;
use LaravelEloquentSpatial\Types\EwkbFormat;
use LaravelEloquentSpatial\Types\AbstractType;
use ReflectionClass;
use RuntimeException;

/**
 * @mixin Model
 */
trait HasEloquentSpatial
{
    /** @var AbstractType[]  */
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
            foreach ($model->spatialAttributes as $attribute => $type) {

                if (isset($model->attributes[$attribute])) {
                    $model->eloquentSpatialInstances[$attribute]->setStateFromType(
                        (new EwkbFormat)->convertBinaryToObject($model->attributes[$attribute])
                    );
                } else {
                    $model->eloquentSpatialInstances[$attribute] = new $type;
                }

                $model->attributes[$attribute] = $model->eloquentSpatialInstances[$attribute];
                $model->original[$attribute] = $model->attributes[$attribute];
            }
        });

        static::saving(function (Model $model) {
            /** @var Model|HasEloquentSpatial $model */
            foreach ($model->spatialAttributes as $attribute => $type) {
                if (is_array($model->attributes[$attribute])) {
                    if (isset($model->attributes[$attribute]['latitude'], $model->attributes[$attribute]['longitude'])) {
                        $model->eloquentSpatialInstances[$attribute]->setStateFromArray($model->attributes[$attribute]);
                        $model->attributes[$attribute] = $model->eloquentSpatialInstances[$attribute];
                    } else {
                        throw new RuntimeException("$attribute was set/reset to an array but does not contain a latitude or longitude");
                    }
                }

                if ($model->attributes[$attribute] === null || $model->attributes[$attribute]->isNull()) {
                    $model->attributes[$attribute] = null;
                    continue;
                }

                $model->attributes[$attribute] = $model->getConnection()->raw(
                    "X'" . $model->attributes[$attribute]->exportStateToEwkbHexidecimal() . "'"
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

            if (!$instance instanceof AbstractType) {
                throw new RuntimeException(__CLASS__ . '::$spatialAttributes must be EloquentSpatialTypeInterface based classes');
            }

            $this->eloquentSpatialInstances[$attribute] = $instance;
            $this->attributes[$attribute] = $instance;
        }
    }

    // protected function parseGe
}
