<?php

namespace Oddvalue\BackpackMediaLibrary\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Oddvalue\BackpackMediaLibrary\Media;
use Oddvalue\BackpackMediaLibrary\MediaRelation;

trait HasMedia
{
    /**
     * The many to many relationship methods.
     *
     * @var array
     */
    public static $mediaMethods = [
        'hasOneMedia', 'hasManyMedia', 'mediaRelation',
    ];

    /**
     * Boot the trait
     *
     * @return void
     */
    public static function bootHasMedia()
    {
        /**
         * Save media relations after the model is saved
         */
        static::saved(function ($model) {
            foreach (request()->input('media_relations') as $relationName => $mediaIds) {
                $mediaIds = array_map('intval', $mediaIds ?: []);
                $currentMedia = $model->hasManyMedia($relationName)->pluck('id')->all();
                if ($currentMedia !== $mediaIds) {
                    $model->hasManyMedia($relationName)->sync(
                        collect($mediaIds)->mapWithKeys(function ($id, $sorting) use ($relationName) {
                            return [$id => [
                                'collection' => $relationName,
                                'order_column' => $sorting
                            ]];
                        })
                    );
                }
            }
        });
    }

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param  string|null  $collection
     * @param  string|null  $localKey
     * @return \Oddvalue\BackpackMediaLibrary\MediaRelation
     */
    public function mediaRelation($collection = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance(Media::class);

        $caller = $collection;

        $name = 'mediable';

        $foreignPivotKey = $name.'_id';

        $relatedPivotKey = $instance->getForeignKey();

        $table = Str::plural($name);

        $localKey = $localKey ?: $this->getKeyName();

        return (new MediaRelation(
            $instance->newQuery(), $this, $name, $table,
            $foreignPivotKey, $relatedPivotKey, $this->getKeyName(),
            $instance->getKeyName(), $caller, false
        ))->withPivot(['collection', 'order_column'])->orderBy('mediables.order_column');
    }

    public function hasManyMedia($collection = null)
    {
        if (!$collection) {
            $collection = $this->guessMediaRelation();
        }
        return $this->mediaRelation($collection)->wherePivot('collection', $collection)->withTimestamps();
    }

    public function hasOneMedia($collection = null)
    {
        return $this->hasManyMedia($collection)->single();
    }

    public function guessMediaRelation()
    {
        $caller = Arr::first(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), function ($trace) {
            return ! in_array(
                $trace['function'],
                array_merge(static::$mediaMethods, ['guessMediaRelation'])
            );
        });

        return ! is_null($caller) ? $caller['function'] : null;
    }
}
