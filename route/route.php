<?php

Route::get('/index', 'index/index');
Route::get('/loginTest', function () {
    echo "登录成功";
})->middleware('auth');

Route::post('/auth/token/refresh', 'login/refresh');
Route::get('/login', function () {
    echo "登录接口";
});

return [

];
