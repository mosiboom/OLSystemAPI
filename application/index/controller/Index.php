<?php
namespace app\index\controller;
use app\server\SerAuth;
use app\server\SerPublic;
class Index
{
    public function index()
    {
        return SerPublic::ApiJson(SerAuth::makeToken(9001),0,'success');
    }

}
