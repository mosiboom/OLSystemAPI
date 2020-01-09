<?php
/*课程小节相关接口*/

use think\facade\{Request, Route};


Route::group('/admin/section', function () {
    Route::get('/all', 'getAll');
    Route::get('/one', 'getOne');
    Route::post('/insert', 'insert');
    Route::post('/delete', 'delete');
    Route::post('/patch', 'save');
})->prefix('admin/Section/');
