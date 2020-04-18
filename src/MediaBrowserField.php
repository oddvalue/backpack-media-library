<?php

namespace Oddvalue\BackpackMediaLibrary;

use Illuminate\Support\Arr;
use SergeYugai\Laravel\Backpack\FieldsAsClasses\Fields\Field;

class MediaBrowserField extends Field
{
    protected $result = [
        'type' => 'media-browser',
        'view_namespace' => 'media-library::crud.fields',
        'is_many' => true,
    ];

    public static function make(string $name = null, string $label = null) : MediaBrowserField
    {
        return new static($name, $label);
    }

    public function calculateName()
    {
        return "media_relations[{$this->result['name']}]";
    }

    public function getValue()
    {
        if (!$this->offsetExists('value') || !$this->offsetGet('value')) {
            return null;
        }

        $value = $this->offsetGet('value');

        if (! $this->offsetGet('is_many')) {
            $value = [$value];
        }

        if (is_object(Arr::first($value))) {
            $value = collect($value)->pluck('id')->all();
        }

        return $value;
    }

    public function name(string $value) : MediaBrowserField
    {
        $this->offsetSet('name', $value);
        return $this;
    }

    public function value(string $value) : MediaBrowserField
    {
        $this->offsetSet('value', $value);
        return $this;
    }

    public function single(bool $value = true) : MediaBrowserField
    {
        $this->offsetSet('is_many', !$value);
        return $this;
    }

    public function offsetGet($key)
    {
        switch ($key) {
            case 'fake';
                return false;

            default:
                return parent::offsetGet($key);
        }
    }
}
