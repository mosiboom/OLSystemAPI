<?php

namespace app\http\middleware;

use app\server\SerAuth;
use app\server\SerPublic;
use think\cache\driver\Redis;
use think\facade\Request;

class AuthAdmin
{
    public function handle($request, \Closure $next)
    {
        /*获取token先在.htaccess中加入 SetEnvIf Authorization ^(.*) HTTP_AUTHORIZATION=$1 */
        /* 判断有没有Authorization头 */
        $Auth = Request::header('Authorization');
        if (!$Auth) {
            return response()->data(SerPublic::ApiJson('', 200, '未登录1'));
        }
        $token = SerAuth::getFinalToken($Auth);
        if (!$token) {
            return response()->data(SerPublic::ApiJson('', 200, '未登录2'));
        }
        $result = SerAuth::verifyToken($token);
        if (!$result['payload'] && $result['code'] != 4) {
            return response()->data(SerPublic::ApiJson('', 201, '认证失败'));
        } elseif ($result['payload']) {
            if ($result['payload']['iss'] != 'Jasper_Admin')/*是否是管理员token*/
                return response()->data(SerPublic::ApiJson('', 201, '认证失败'));
            /*判断黑名单*/
            $redis = new Redis();
            $black_token = $redis->get("admin_login_blacklist_{$result['payload']['uid']}");
            if ($black_token == $token)
                return response()->data(SerPublic::ApiJson('', 203, '无效令牌'));
            $request->payload = $result['payload'];
            return $next($request);
        } else {
            return response()->data(SerPublic::ApiJson('', 202, 'request failed'));
        }

    }
}
