<?php

namespace Oddvalue\BackpackMediaLibrary\Http\Controllers;

use Oddvalue\BackpackMediaLibrary\Media;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class MediaLibraryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    public function setup()
    {
        CRUD::setModel(Media::class);
        CRUD::setEntityNameStrings(
            trans('media-library::admin.media'),
            trans('media-library::admin.media')
        );
        CRUD::setRoute(backpack_url('media-library'));
    }
    public function setupListOperation()
    {
        // columns to show in the table view
        CRUD::setColumns([
            [
                'name'  => 'name',
                'label' => trans('media-library::admin.media'),
            ],
            [
                'name'  => 'value',
                'label' => trans('media-library::admin.media'),
            ],
            [
                'name'  => 'description',
                'label' => trans('media-library::admin.media'),
            ],
        ]);
    }
    public function setupUpdateOperation()
    {
        CRUD::addField([
            'name'       => 'name',
            'label'      => trans('media-library::admin.media'),
            'type'       => 'text',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        CRUD::addField(json_decode(CRUD::getCurrentEntry()->field, true));
    }
}
