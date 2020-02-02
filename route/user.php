<?php
/*用户管理*/

use think\facade\{Request, Route};

/*管理员后台*/
Route::group('/admin/user', function () {
    Route::get('/all', 'getAll');
    Route::get('/one', 'getOne');
    Route::post('/insert', 'insert');
    Route::post('/delete', 'delete');
    Route::post('/patch', 'save');
})->prefix('admin/User/');