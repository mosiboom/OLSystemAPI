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

function post($url, $param = array(), $header = array())
{
    try {
        if (!is_array($param)) {

            throw new RuntimeException("参数必须为array");

        }
        $header = array_merge(array("Content-type:application/json", "Accept:application/json"), $header);
        $http = curl_init($url);

        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($http, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");

        curl_setopt($http, CURLOPT_POST, 1);//设置为POST方式

        curl_setopt($http, CURLOPT_POSTFIELDS, $param);

        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($http, CURLOPT_HEADER, 1);

        curl_setopt($http, CURLOPT_HTTPHEADER, $header);

        $rst = curl_exec($http);

        curl_close($http);

        return $rst;
    } catch (RuntimeException $exception) {
        echo $exception->getMessage();
    }

}

/*获取http code*/
function getHttpCode($url, $param = array(), $header = array())
{
    try {
        if (!is_array($param)) {

            throw new RuntimeException("参数必须为array");

        }
        $header = array_merge(array("Content-type:application/json", "Accept:application/json"), $header);
        $http = curl_init($url);

        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($http, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");

        curl_setopt($http, CURLOPT_POST, 1);//设置为POST方式

        curl_setopt($http, CURLOPT_POSTFIELDS, $param);

        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($http, CURLOPT_HEADER, 1);

        curl_setopt($http, CURLOPT_HTTPHEADER, $header);

        curl_exec($http);
        $httpCode = curl_getinfo($http, CURLINFO_HTTP_CODE);
        curl_close($http);

        return $httpCode;
    } catch (RuntimeException $exception) {
        echo $exception->getMessage();
    }

}

function postRaw($url, $param, $header = array())
{
    try {
        $header = array_merge(array("Content-type:application/json", "Accept:application/json"), $header);
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($http, CURLOPT_POST, 1);//设置为POST方式
        if (!is_array($param)) {
            curl_setopt($http, CURLOPT_POSTFIELDS, $param);
        } else {
            curl_setopt($http, CURLOPT_POSTFIELDS, json_encode($param));
        }
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_HEADER, 1);
        curl_setopt($http, CURLOPT_HTTPHEADER, $header);
        $rst = curl_exec($http);
        $headerSize = curl_getinfo($http, CURLINFO_HEADER_SIZE);
        $headerTotal = strlen($rst);
        $bodySize = $headerTotal - $headerSize;
        $header = substr($rst, 0, $headerSize);
        $comma_separated = explode("\r\n", $header);
        $arr = array();
        foreach ($comma_separated as $value) {
            if (strpos($value, ':') !== false) {
                $a = explode(":", $value);
                $key = $a[0];
                $v = $a[1];
                $arr[$key] = $v;
            } else {
                array_push($arr, $value);
            }
        }
        $body = substr($rst, $headerSize, $bodySize);
        curl_close($http);

        return $body;
    } catch (RuntimeException $exception) {
        echo $exception->getMessage();
    }

}

function get($url, $param = array(), $headerArr = array(), $is_json = true)
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
        $headerArray = array_merge(array("Content-type:application/json;", "Accept:application/json"), $headerArr);
        $http = curl_init();
        curl_setopt($http, CURLOPT_URL, $url);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headerArray);
        $output = curl_exec($http);
        curl_close($http);
        if ($is_json) {
            $output = json_decode($output, true);
        }
        return $output;
    } catch (RuntimeException $exception) {
        echo $exception->getMessage();
    }
}