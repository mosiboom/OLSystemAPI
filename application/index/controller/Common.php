<?php

namespace app\index\controller;

use app\server\SerPublic;
use think\{App, Controller};

class Common extends Controller
{

    public function __construct(App $app = null)
    {
        parent::__construct($app);
    }

    /*上传媒体文件接口*/
    public function uploadVideo()
    {
        return SerPublic::uploadVideo();
    }

    /*上传图片文件接口*/
    public function uploadPicture()
    {
        return SerPublic::uploadPicture();
    }

}
