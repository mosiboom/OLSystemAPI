<?php
/*文章相关接口*/

use think\facade\{Request, Route};

/*前台*/
Route::group('/article', function () {
    Route::get('/get', 'getAll')->middleware('auth');;
    Route::get('/detail', 'getOne');
    Route::get('/category', 'category');
    Route::get('/byCategory', 'byCategory');
    Route::get('/comment/get', 'comment');
    Route::post('/comment/insert', 'insertComment')->middleware('auth');
})->prefix('index/Article/');
/*管理员后台*/
Route::group('/admin/article', function () {
    Route::get('/all', 'getAll');
    Route::get('/one', 'getOne');
    Route::post('/insert', 'insert');
    Route::post('/delete', 'delete');
    Route::post('/patch', 'save');
    /*文章分类管理*/
    Route::get('/category/all', 'categoryAll');
    Route::get('/category/one', 'categoryOne');
    Route::post('/category/save', 'categorySave');
    Route::post('/category/delete','categoryDelete');
})->prefix('admin/Article/');