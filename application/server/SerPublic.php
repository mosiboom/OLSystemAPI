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

    public static function ApiSuccess($data = '')
    {
        return self::ApiJson($data, 0, 'success');
    }

    /*上传文件静态接口*/
    public static function upload(string $uploadName, array $config = array(), string $returnType = 'array')
    {
        $upload = new SerUpload($uploadName, $config, $returnType);
        return $re = $upload->upload();
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
            'typeArr' => array('mp4', 'ogg', 'ogv', 'webm', 'wmv'),
            'size' => '4096m',
            'is_tmp' => true
        );
        $re = self::upload('uv', $config);
        if ($re['status']) {
            return self::ApiJson(array(
                'fileName' => $re['fileName'],
                'url' => app_domain . $relative . $re['fileName']
            ), 0, $re['msg']);
        } else {
            return self::ApiJson('', 100, $re['msg']);
        }
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
        $re = self::upload('up', $config);
        if ($re['status']) {
            return self::ApiJson(array(
                'fileName' => $re['fileName'],
                'url' => app_domain . $relative . $re['fileName']
            ), 0, $re['msg']);
        } else {
            return self::ApiJson('', 100, $re['msg']);
        }
    }

    /**
     * 去除上传文件的tmp后缀
     * @param $tmp_url //带有tmp的url
     * @return string | bool  //返回处理完的url
     */
    public static function getWithoutTmp($tmp_url)
    {
        $path = str_replace(app_domain, $_SERVER['DOCUMENT_ROOT'], $tmp_url);
        $new_path = strstr($path, '.tmp', true);
        if (!$new_path) {
            return false;
        }
        if (file_exists($path)) {
            rename($path, $new_path);
        } else {
            return false;
        }
        $url = str_replace($_SERVER['DOCUMENT_ROOT'], app_domain, $tmp_url);
        return $new_url = strstr($url, '.tmp', true);
    }


    /**
     * 检测上传的文件链接是否在指定处
     * @param $url //检测的链接
     * @param $type //文件类型
     * @return bool
     * */
    public static function checkUploadURL($url, $type)
    {
        $_data = parse_url($url);
        if (!isset($_data['scheme'], $_data['host']))
            return false;
        //检测域名
        $url_domain = $_data['scheme'] . "://" . $_data['host'];
        if ($url_domain != app_domain) {
            return false;
        }
        $needle = '/static/upload/';
        switch ($type) {
            case 'picture':
                $needle .= "picture/";
                break;
            case 'video':
                $needle .= "video/";
                break;
        }
        if (!strstr($_data['path'], $needle)) {
            return false;
        }
        if (getHttpCode($url) != '200') {
            return false;
        }

        return true;
    }

    /*递归无限制级数据*/
    public static function actionClassData($data, $pid = 0)
    {
        $new_data = array();
        $arr = array();
        foreach ($data as $key => $val) {
            if ($val['pid'] == $pid) {//当pid为0的话是个新的
                $arr = $val;
                unset($data[$key]);
                $arr1 = self::actionClassData($data, $arr['id']);
                $arr['list'] = $arr1;
                $new_data[] = $arr;
            }
        }
        return $new_data;
    }
}