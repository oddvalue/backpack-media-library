<?php

namespace Oddvalue\BackpackMediaLibrary;

use SergeYugai\Laravel\Backpack\FieldsAsClasses\Columns\Column;

class MediaBrowserColumn extends Column
{
    protected $result = [
        'type' => 'media-browser',
        'view_namespace' => 'media-library::crud.columns',
    ];

    // We re-declare this so that IDE would pick up
    public static function make(string $name = null, string $label = null) : MediaBrowserColumn
    {
        return new self($name, $label);
    }
}
