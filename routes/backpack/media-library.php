<?php
/*
|--------------------------------------------------------------------------
| Oddvalue\BackpackMediaLibrary Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Oddvalue\BackpackMediaLibrary package.
|
*/
Route::group([
    'namespace'  => 'Oddvalue\BackpackMediaLibrary\Http\Controllers',
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', backpack_middleware()],
], function () {
    Route::get('media-library/folder/options', 'FolderCrudController@getDropdownOptions');
    Route::get('media-library/tags', 'MediaLibraryCrudController@getTags');
    Route::resource('media-library/folder', 'FolderCrudController', ['except' => ['show', 'index']]);
    Route::resource('media-library', 'MediaLibraryCrudController');
});
Route::get('media/image/{mode}/{size}/{filename}', 'Oddvalue\BackpackMediaLibrary\Http\Controllers\MediaLibraryCrudController@resize')
    ->name('media-library.resize')
    ->where('size', '\d+x?\d*');
