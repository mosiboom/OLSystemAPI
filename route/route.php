<?php

use think\facade\{Request, Route};

Route::get('/', 'index/index');
Route::get('/index', 'index/index');
/*登录*/
Route::post('/login', 'login/index');
Route::post('/logout', 'login/logout');
Route::post('/token/refresh', 'login/refresh');

/*管理员登录*/
Route::group('/admin',function (){
    Route::post('/login','login');
    Route::post('/logout','logout')->middleware('admin');
})->prefix('admin/Login/');
