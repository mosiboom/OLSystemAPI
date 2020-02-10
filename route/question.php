<?php
/*课程小节的问题相关接口*/

use think\facade\{Request, Route};


/*管理员后台*/
Route::group('/admin/question', function () {
    Route::get('/all', 'getAll');
    Route::get('/one', 'getOne');
    Route::post('/insert', 'insert');
    Route::post('/delete', 'delete');
    Route::post('/patch', 'save');
})->prefix('admin/Question/')->middleware('admin');