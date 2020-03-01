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
    Route::resource('media-library/folder', 'FolderCrudController', ['except' => ['show', 'index']]);
    Route::resource('media-library', 'MediaLibraryCrudController', ['except' => ['show']]);
    Route::get('media/tags', 'MediaLibraryCrudController@getTags');
    Route::get('media-library/{type}', 'MediaLibraryCrudController@getList');
});
Route::get('media/image/{mode}/{size}/{filename}', 'Oddvalue\BackpackMediaLibrary\Http\Controllers\MediaLibraryCrudController@resize')
    ->name('media-library.resize')
    ->where('size', '\d+x?\d*');
