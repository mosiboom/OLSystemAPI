<?php

use think\Facade\{Request, Route};

Route::get('/', 'index/index');
Route::get('/index', 'index/index');
/*登录路由*/
Route::post('/login', 'login/index');
Route::post('/logout', 'login/logout');
Route::post('/token/refresh', 'login/refresh');



Route::get('/loginTest', function () {
    $open_id = Request::param('payload')['uid'];
    echo "用户已登录，这是用户的id：" . $open_id;
})->middleware('auth');
Route::get('/test',function (){
    $tokenArr=\app\server\SerAuth::makeToken('213123');
    dump($tokenArr['access_token']);
    dump(md5($tokenArr['refresh_token']));

});
Route::get('/getJueJin','index/getJueJinArticle');
