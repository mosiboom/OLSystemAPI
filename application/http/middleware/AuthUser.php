<?php

namespace app\http\middleware;

use app\server\SerAuth;
use app\server\SerPublic;
use think\Facade\Request;

class AuthUser
{
    public function handle($request, \Closure $next)
    {
        /*获取token先在.htaccess中加入 SetEnvIf Authorization ^(.*) HTTP_AUTHORIZATION=$1 */
        $token = SerAuth::getFinalToken(Request::header('Authorization'));
        if (!$token) {
            return response()->data(SerPublic::ApiJson('', 200, '未登录'));
        }
        $result = SerAuth::verifyToken($token);
        if (!$result['payload'] && $result['code'] != 4) {
            return response()->data(SerPublic::ApiJson('', 201, 'request failed'));
        } elseif ($result['payload']) {
            return $next($request);
        } else {
            return response()->data(SerPublic::ApiJson('', 202, 'request failed'));
        }

    }
}
