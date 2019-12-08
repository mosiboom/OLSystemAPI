<?php

use think\Facade\{Request,Route};

Route::get('/', 'index/index');
Route::get('/index', 'index/index');
/*登录路由*/
Route::post('/login', 'login/index');
Route::get('/loginTest', function () {
    $open_id = Request::param('payload')['uid'];
    echo "用户已登录，这是用户的id：" . $open_id;
})->middleware('auth');

Route::post('/auth/token/refresh', 'login/refresh');


return [

];
