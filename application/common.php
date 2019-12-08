<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function post($url, $param = array())
{
    try {
        if (!is_array($param)) {

            throw new RuntimeException("参数必须为array");

        }

        $http = curl_init($url);

        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 1);

        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($http, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");

        curl_setopt($http, CURLOPT_POST, 1);//设置为POST方式

        curl_setopt($http, CURLOPT_POSTFIELDS, $param);

        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($http, CURLOPT_HEADER, 1);

        $rst = curl_exec($http);

        curl_close($http);

        return $rst;
    } catch (RuntimeException $exception) {
        echo $exception->getMessage();
    }

}

function get($url, $param = array())
{
    try {
        if (!is_array($param)) {
            throw new RuntimeException("参数必须为array");
        }
        if (!empty($param)) {
            $url .= "?";
            foreach ($param as $k => $v) {
                $url .= "{$k}=$v&";
            }
            $url = substr($url, 0, -1);
        }
        $headerArray = array("Content-type:application/json;", "Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, true);
        return $output;
    } catch (RuntimeException $exception) {
        echo $exception->getMessage();
    }
}