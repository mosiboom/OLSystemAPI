<?php

namespace app\server;

use think\{cache\driver\Redis};

class SerAuth
{
    public static function loadConf($conf)
    {
        return config()['jwt'][$conf];
    }

    /*生成token组*/
    public static function makeToken($uid)
    {
        $refresh = self::makeRefreshToken($uid, true);
        $access = self::makeAccessToken($uid, $refresh['jti'], true);
        //refresh_token进缓存
        $redis = new Redis();
        $redis->set("auth_refresh_{$refresh['jti']}", $refresh['token']);
        return array(
            'access_token' => $access['token'],
            'refresh_token' => $refresh['token'],
            'expires_in' => time() + self::loadConf('refresh_token_expire_time')
        );
    }

    /*
     * 代理token（扩展）
     * */
    public static function OauthToken($uid)
    {
        $token = self::makeToken($uid);
        //todo 校验client_id
        return [
            'token' => $token['access_token'],
            'auth_id' => md5($token['refresh_token']),
            'expires_in' => time() + self::loadConf('refresh_token_expire_time')
        ];
    }

    /**
     * 生成access_token接口
     * @param $uid
     * @param $refresh_id
     * @param bool $return_jti 是否返回jti
     * @return string | array
     * */
    public static function makeAccessToken($uid, $refresh_id, $return_jti = false)
    {
        $jti = md5(uniqid('JWT') . time());
        $exp = self::loadConf('access_token_expire_time');
        $data = array(
            'iss' => 'Jasper', //该JWT的签发者
            'iat' => time(), //签发时间
            'exp' => time() + $exp, //过期时间
            'nbf' => time() + 10, //该时间之前不接收处理该Token
            'uid' => $uid,
            'type' => 'access',
            'refresh_id' => $refresh_id,
            'jti' => $jti
        );
        if ($return_jti) {
            return array(
                'jti' => $jti,
                'token' => SerJwtToken::getToken($data)
            );
        }
        return SerJwtToken::getToken($data);
    }

    /**
     * 生成refresh_token接口
     * @param $uid
     * @param bool $return_jti 是否返回jti
     * @return array | string
     */
    public static function makeRefreshToken($uid, $return_jti = false)
    {
        $jti = md5(uniqid('JWT') . time());
        $exp = self::loadConf('refresh_token_expire_time');
        $data = array(
            'iss' => 'Jasper', //该JWT的签发者
            'iat' => time(), //签发时间
            'exp' => time() + $exp, //过期时间
            'nbf' => time() + 60, //该时间之前不接收处理该Token
            'uid' => $uid,
            'type' => 'refresh',
            'jti' => $jti
        );
        if ($return_jti) {
            return array(
                'jti' => $jti,
                'token' => SerJwtToken::getToken($data)
            );
        }
        return SerJwtToken::getToken($data);
    }

    /*验证token*/
    public static function verifyToken(string $token)
    {
        return SerJwtToken::verifyToken($token);
    }

    /**
     * 验证token 失败返回false,成功返回原/新token
     * @param string $token token组包含access_token和auth_id
     * @param string $auth_id
     * @return string|bool
     * */
    public static function makeNewAccess($token, $auth_id)
    {
        /*验证有效性*/
        $res = SerJwtToken::verifyToken($token);

        if (!$res['payload'] && $res['code'] != 4) {//认证失败
            return 201;
        } elseif ($res['payload']) {//正常
            return $token;
        }
        /*access过期生成新token*/
        $refresh_id = $res['jti'];
        $redis = new Redis();
        $refresh = $redis->get("auth_refresh_$refresh_id");//获取在缓存里的refresh_token
        if ($auth_id == md5($refresh)) { //校验$auth_id是否合格
            $token = self::accessFromRefresh($refresh);
            if (!$token['status']) {
                return $token['code'];
            }
        } else {
            return 201;
        }
        /*token合法*/
        return $token['data'];
    }

    /**
     * 根据refresh_token生成新token
     * @param string $refresh_token
     * @return array
     * */

    static function accessFromRefresh(string $refresh_token)
    {
        /*验证refresh_token是否有效*/
        $res = SerJwtToken::verifyToken($refresh_token, 'refresh');
        if ($res['code'] != 0) {
            $code = $res['code'] == 4 ? 202 : 201;
            return array('code' => $code, 'status' => false, 'data' => '');
        }
        if (!$res['payload']) {
            return array('code' => 201, 'status' => false, 'data' => '');
        }
        if ($res['payload']['type'] != 'refresh') {
            return array('code' => 201, 'status' => false, 'data' => '');
        }
        /*校验refreshToken合法后生成新access_token*/
        return array('code' => $res['code'], 'status' => true, 'data' => self::makeAccessToken($res['payload']['uid'], $res['payload']['jti']));
    }

    /*获取Bearer token*/
    public static function getFinalToken(string $token)
    {
        return trim(str_replace("Bearer", "", $token));
    }

}