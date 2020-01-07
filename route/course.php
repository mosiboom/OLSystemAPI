<?php
/*课程相关接口*/

use think\facade\{Request, Route};

/*前台*/
Route::group('/course', function () {
    Route::get('/get', 'getAll');
    Route::get('/detail', 'getOne');
})->prefix('index/Course/');

/*管理员后台*/
Route::group('/admin/course', function () {
    Route::get('/all', 'getAll');
    Route::get('/one', 'getOne');
    Route::post('/insert', 'insert');
    Route::post('/delete', 'delete');
    Route::post('/patch', 'save');
})->prefix('admin/Course/');