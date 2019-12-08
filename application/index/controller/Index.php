<?php

namespace app\index\controller;

use app\server\SerAuth;
use app\server\SerPublic;

class Index
{
    public function index()
    {
        $data = SerAuth::makeToken(9001);
        $return = array(
            'token' => $data['access_token'],
            'auth_id' => md5($data['refresh_token']),
            'expire_in' => $data['expires_in']
        );
        return SerPublic::ApiJson($return, 0, 'success');

    }

}
