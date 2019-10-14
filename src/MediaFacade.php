<?php

namespace Oddvalue\BackpackMediaLibrary;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Oddvalue\BackpackMediaLibrary\Media
 */
class MediaFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'media';
    }
}
