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
    Route::crud('media-library', 'MediaLibraryCrudController');
    Route::get('media/tags', function() {
        return [];
    });
    Route::get('media/{type}', function() {
        return \Oddvalue\BackpackMediaLibrary\Media::all();
    });
});