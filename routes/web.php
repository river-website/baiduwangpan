<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/api/user','share_userController@get');
Route::get('/api/file','share_fileController@get');
Route::get('/api/file/search','share_fileController@search');
Route::get('/api/webSite','webSiteController@get');
Route::get('/api/hotFile','hotFileController@getHot');
Route::get('/api/hotUser','hotUserController@getHot');
Route::get('/api/hotSearch','hotSearchController@getHot');
Route::get('/api/type','typeController@get');
Route::get('/api/suffix/search','suffixController@search');
Route::get('/api/crawl/yousuu','crawlController@yousuu');
