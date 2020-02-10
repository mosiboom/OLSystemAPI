<?php

use think\facade\{Request, Route};

//公共路由
Route::group('/common', function () {
    /*上传视频文件接口*/
    Route::post('/upload/video', 'common/uploadVideo');
    /*上传图片文件接口*/
    Route::post('/upload/picture', 'common/uploadPicture');
})->middleware('admin');
