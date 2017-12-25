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

Route::get('/api',function(){
    echo "1111";
});

Route::get('/api/baseInfo',function(){
    echo "1111";
});
Route::get('/api/hotInfo',function(){
    echo "1111";
});
Route::get('/api/user','share_userController@get');
Route::get('/api/file',function(){
    echo "1111";
});
Route::get('/api/search',function(){
    echo "1111";
});