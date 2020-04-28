<?php

namespace Oddvalue\BackpackMediaLibrary\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
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
            collect(request()->input('media_relations'))->map(function ($mediaIds, $relationName) use ($model) {
                $model->syncMedia($relationName, $mediaIds ?: []);
            });
        });
    }

    /**
     * Sync media collection
     *
     * @param string $collection
     * @param int|array|\Oddvalue\BackpackMediaLibrary\Media|\Illuminate\Support\Collection $media
     * @param  bool  $detaching
     * @return void
     */
    public function syncMedia(string $collection, $ids, $detaching = tue)
    {
        if ($ids instanceof Media) {
            $ids = [$ids->id];
        } elseif ($ids instanceof Collection) {
            $ids = $ids->all();
        } elseif (! Arr::accessible($ids)) {
            $ids = [$ids];
        }

        if (Arr::accessible(Arr::first($ids))) {
            $ids = Arr::pluck($ids, 'id');
        }

        $ids = array_map('intval', $ids);

        $currentMedia = $this->hasManyMedia($collection)->pluck('id')->all();
        if ($currentMedia === $ids) {
            // Media relation hasn't changed
            return;
        }

        return $this->hasManyMedia($collection)->sync(
            collect($ids)->mapWithKeys(function ($id, $sorting) use ($collection) {
                return [$id => [
                    'collection' => $collection,
                    'order_column' => $sorting
                ]];
            }),
            $detaching
        );
    }

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param  string $collection
     * @return \Oddvalue\BackpackMediaLibrary\MediaRelation
     */
    public function mediaRelation($collection = null)
    {
        $instance = $this->newRelatedInstance(Media::class);

        if (!$collection) {
            $collection = $this->guessMediaRelation();
        }

        $name = 'mediable';

        $foreignPivotKey = $name.'_id';

        $relatedPivotKey = $instance->getForeignKey();

        $table = Str::plural($name);

        return (new MediaRelation(
            $instance->newQuery(),
            $this,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $this->getKeyName(),
            $instance->getKeyName(),
            $collection,
            false
        ))
            ->withPivot(['collection', 'order_column'])
            ->orderBy('mediables.order_column')
            ->withTimestamps()
            ->wherePivot('collection', $collection);
    }

    public function hasManyMedia($collection = null)
    {
        return $this->mediaRelation($collection);
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
