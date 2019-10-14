<?php

namespace Oddvalue\BackpackMediaLibrary;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    /**
     * Friendly welcome
     *
     * @param string $phrase Phrase to return
     *
     * @return string Returns the phrase passed in
     */
    public function echoPhrase(string $phrase): string
    {
        return $phrase;
    }
}
