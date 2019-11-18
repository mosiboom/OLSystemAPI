<?php

namespace app\index\controller;

use app\server\SerAuth;
use app\server\SerPublic;
use think\facade\Request;

class Login
{
    public function index()
    {
        return SerPublic::ApiJson(SerAuth::makeToken(9001), 0, 'success');
    }

    /*刷新Token*/
    public function refresh()
    {
        /*auth_id是md5加密过的refresh_token*/
        $token = SerAuth::getFinalToken(Request::header('Authorization'));
        $auth_id = Request::post('auth_id');
        $return = SerAuth::makeNewAccess($token, $auth_id);
        if ($return == 201) {
            return SerPublic::ApiJson('', 201, 'request failed');
        } elseif ($return == 202) {
            return SerPublic::ApiJson('', 202, 'request failed');
        }
        $newToken = array(
            'token' => $return,
            'auth_id' => $auth_id,
            'expires_in' => time() + 72000
        );
        return SerPublic::ApiJson($newToken, 0, 'success');
    }

}