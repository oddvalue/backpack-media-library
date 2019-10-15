<?php

namespace Oddvalue\BackpackMediaLibrary\Http\Controllers;

use Oddvalue\BackpackMediaLibrary\Media;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class MediaLibraryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        $this->crud->setModel(Media::class);
        $this->crud->setEntityNameStrings(
            trans('media-library::admin.media'),
            trans('media-library::admin.media')
        );
        $this->crud->setRoute(backpack_url('media-library'));
    }
    public function setupListOperation()
    {
        $this->crud->setListView('media-library::crud.list');

        // columns to show in the table view
        $this->crud->setColumns([
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
        $this->crud->addField([
            'name'       => 'name',
            'label'      => trans('media-library::admin.media'),
            'type'       => 'text',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        $this->crud->addField(json_decode($this->crud->getCurrentEntry()->field, true));
    }
}
