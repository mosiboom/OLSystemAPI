<?php
/*文章相关接口*/

use think\facade\{Request, Route};

/*前台*/
Route::group('/article', function () {
    Route::get('/get', 'getAll');
    Route::get('/detail', 'getOne');
    Route::get('/comment/get', 'comment');
    Route::get('/category', 'category');
    Route::get('/byCategory', 'byCategory');
    Route::post('/comment/insert', 'insertComment')->middleware('auth');
})->prefix('index/Article/');
/*管理员后台*/
Route::group('/admin/article', function () {
    Route::get('/all', 'getAll');
    Route::get('/one', 'getOne');
    Route::post('/insert', 'insert');
    Route::post('/delete', 'delete');
    Route::post('/patch', 'save');
})->prefix('admin/Article/');