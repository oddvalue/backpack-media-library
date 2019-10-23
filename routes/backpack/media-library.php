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
        return '{"pagination":{"total":2,"per_page":24,"current_page":1,"last_page":1,"from":1,"to":2},"data":{"total":2,"per_page":24,"current_page":1,"last_page":1,"next_page_url":null,"prev_page_url":null,"from":1,"to":2,"data":[{"id":3582,"filename":"new-intranet-news-3582.jpg","type":"image","caption":null,"private":0,"created_at":"2019-10-23 11:21:23","updated_at":"2019-10-23 11:21:23","folder_id":null,"tags":[]},{"id":3581,"filename":"new-intranet-news-tiles-home-3581.jpg","type":"image","caption":null,"private":0,"created_at":"2019-10-23 11:19:35","updated_at":"2019-10-23 11:19:35","folder_id":null,"tags":[]}]},"folders":[{"id":13,"name":"Alumni","parent_id":null,"created_at":"2019-10-01 14:18:41","updated_at":"2019-10-01 14:18:41"},{"id":5,"name":"Buildings and facilities","parent_id":null,"created_at":"2019-10-01 13:14:28","updated_at":"2019-10-01 13:14:28"},{"id":1,"name":"Curriculum","parent_id":null,"created_at":"2019-10-01 13:06:47","updated_at":"2019-10-01 13:06:47"},{"id":8,"name":"Documents","parent_id":null,"created_at":"2019-10-01 13:14:58","updated_at":"2019-10-01 13:14:58"},{"id":54,"name":"Forms","parent_id":null,"created_at":"2019-10-01 16:39:04","updated_at":"2019-10-01 16:39:04"},{"id":7,"name":"Graphics","parent_id":null,"created_at":"2019-10-01 13:14:52","updated_at":"2019-10-01 13:14:52"},{"id":56,"name":"Internal news","parent_id":null,"created_at":"2019-10-16 11:40:48","updated_at":"2019-10-16 11:40:48"},{"id":6,"name":"Locations","parent_id":null,"created_at":"2019-10-01 13:14:40","updated_at":"2019-10-01 13:14:40"},{"id":9,"name":"Misc","parent_id":null,"created_at":"2019-10-01 13:16:00","updated_at":"2019-10-01 13:16:00"},{"id":10,"name":"News and blog","parent_id":null,"created_at":"2019-10-01 13:17:26","updated_at":"2019-10-01 15:49:07"},{"id":53,"name":"Policy documents","parent_id":null,"created_at":"2019-10-01 16:36:31","updated_at":"2019-10-01 16:36:31"},{"id":52,"name":"Prospectuses","parent_id":null,"created_at":"2019-10-01 16:36:19","updated_at":"2019-10-01 16:36:19"},{"id":11,"name":"Staff","parent_id":null,"created_at":"2019-10-01 13:43:00","updated_at":"2019-10-01 13:43:00"},{"id":40,"name":"Student support","parent_id":null,"created_at":"2019-10-01 15:51:46","updated_at":"2019-10-01 15:51:46"}]}';
    });
});