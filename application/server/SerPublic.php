<?php

namespace app\server;

use think\Cache;
use think\cache\driver\Redis;

class SerPublic
{
    /*api返回数据*/
    public static function ApiJson($data, int $code, string $msg): string
    {
        $return = array(
            'data' => $data,
            'error' => array(
                'code' => $code,
                'msg' => $msg,
                /*与js时间戳统一*/
                'sequence' => time()
            )
        );

        return json_encode($return);
    }

    /*上传文件静态接口*/
    public static function upload(string $uploadName, array $config = array(), string $returnType = 'array')
    {
        $upload = new SerUpload($uploadName, $config, $returnType);
        $re = $upload->upload();
        if ($re['status']) {
            return self::ApiJson(array('fileName' => $re['fileName']), 0, $re['msg']);
        } else {
            return self::ApiJson('', 100, $re['msg']);
        }
    }

    /*上传视频*/
    public static function uploadVideo()
    {
        $relative = '/static/upload/video/';
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $relative;
        $config = array(
            'filePath' => $filePath,
            'original' => false,
            'fileName' => time(),
            'uploadType' => 'media',
            'typeArr' => array('mp4', 'ogg', 'ogv', 'webm'),
            'size' => '4096m',
            'is_tmp' => true
        );
        return self::upload('uv', $config);
    }

    /*上传图片*/
    public static function uploadPicture()
    {
        $relative = '/static/upload/picture/';
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $relative;
        $config = array(
            'filePath' => $filePath,
            'original' => false,
            'fileName' => time(),
            'uploadType' => 'picture',
            'size' => '4096m',
            'is_tmp' => true
        );
        return SerPublic::upload('up', $config);
    }


}