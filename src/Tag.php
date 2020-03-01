<?php

namespace Oddvalue\BackpackMediaLibrary;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'media_tags';

    protected $fillable = ['name'];

    public function media()
    {
        return $this->belongsToMany(Media::class);
    }
}
