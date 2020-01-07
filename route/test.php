<?php

use think\facade\{Request, Route};

//Route::get('/mockComment', 'article/mock');
Route::get('/loginTest', function () {
    $open_id = Request::param('payload')['uid'];
    echo "用户已登录，这是用户的id：" . $open_id;
})->middleware('auth');
Route::get('/test', function () {
    $tokenArr = \app\server\SerAuth::makeToken('213123');
    dump($tokenArr['access_token']);
    // dump(md5($tokenArr['refresh_token']));
    //dump(rand(100, 9999));
});

Route::get('/test1', function () {
    dump(Request::path());
});

Route::get('/script/run', 'script/run');
Route::get('/getJueJin', 'index/getJueJinArticle');
Route::get('/getImooc', 'index/getImoocClass');