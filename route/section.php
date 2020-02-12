<?php
/*课程小节相关接口*/

use think\facade\{Request, Route};

/*前台*/
Route::group('/section', function () {
    Route::get('/get', 'getAll');
    Route::get('/detail', 'getOne');
    /*小节评论*/
    Route::get('/comment/get', 'comment');
    Route::post('/comment/insert', 'insertComment')->middleware('auth');
})->prefix('index/Section/');



/*
 * 小节示例视频地址：
    http://106.54.187.34/static/upload/video/1580458072.mp4
*/
/*后台*/
Route::group('/admin/section', function () {
    Route::get('/all', 'getAll');
    Route::get('/one', 'getOne');
    Route::post('/insert', 'insert');
    Route::post('/delete', 'delete');
    Route::post('/patch', 'save');
})->prefix('admin/Section/')->allowCrossDomain()->middleware('admin');
