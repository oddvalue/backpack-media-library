<?php

namespace Oddvalue\BackpackMediaLibrary;

use Illuminate\Database\Eloquent\Model;

class MediaFolder extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
    ];

    protected $with = [
        'children',
    ];

    public function parent()
    {
        return $this->belongsTo(MediaFolder::class);
    }

    public function children()
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    public function indentDescendants($collection, $depth = 0)
    {
        $collection->push((object)[
            'indentedName' => str_repeat('─', $depth) . ' ' . $this->name,
            'name' => $this->name,
            'id' => $this->id,
        ]);
        $this->children->map(function ($child) use ($collection, $depth) {
            $child->indentDescendants($collection, ++$depth);
        });
        return $collection;
    }

    public function media()
    {
        return $this->hasMany(Media::class, 'folder_id');
    }
}
