<?php

use think\facade\{Request, Route};

Route::get('/', 'index/index');
Route::get('/index', 'index/index');
/*登录路由*/
Route::post('/login', 'login/index');
Route::post('/logout', 'login/logout');
Route::post('/token/refresh', 'login/refresh');
