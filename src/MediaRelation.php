<?php

namespace Oddvalue\BackpackMediaLibrary;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

class MediaRelation extends MorphToMany
{
    /**
     * Whether or not this relation is a single media item
     *
     * @var boolean
     */
    protected $single = false;

    /**
     * Set to single media relation
     *
     * @return $this
     */
    public function single()
    {
        $this->single = true;
        return $this;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return with(parent::getResults(), function ($results) {
            if ($this->single) {
                return $results->first();
            }
            return $results;
        });
    }
}
