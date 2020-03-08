<?php

namespace app\index\controller;

use app\server\SerAuth;
use app\server\SerPublic;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\facade\Env;
use think\facade\Request;

class Login
{
    /*登录*/
    public function index()
    {
        try {
            $code = trim(Request::post('code'));
            $userInfo = Request::post('userInfo');
            if (!isset($code, $userInfo)) {
                throw new \RuntimeException('参数有误');
            }

            $wechat_appid = Env::get('wechat_app_id');
            $wechat_secret = Env::get('wechat_app_key');
            $param = array(
                'js_code' => $code,
                'appid' => $wechat_appid,
                'secret' => $wechat_secret,
                'grant_type' => 'authorization_code'
            );
            $url = "https://api.weixin.qq.com/sns/jscode2session";
            $return = get($url, $param);
            //$userInfo='{"nickName":"〔 _ 〕","gender":1,"language":"zh_CN","city":"Shantou","province":"Guangdong"}';
            //$return['openid']='123123123';

            if (isset($return['errcode'])) {
                return SerPublic::ApiJson($return, 101, '小程序接口参数有误');
            }
            $userInfo_arr = json_decode($userInfo, true);
            if (empty($userInfo_arr)) {
                throw new \RuntimeException('参数有误');
            }
            $open_id = $return['openid'];
            $info = Db::table('user')->where('open_id', $open_id)->find();
            $data = array(
                'name' => $userInfo_arr['nickName'],
                'sex' => $userInfo_arr['gender'],
                'wechat_user' => $userInfo
            );
            if ($info) {
                Db::table('user')->where('open_id', $open_id)->update($data);
                $getToken = SerAuth::makeToken($open_id);
                $token = array(
                    'token' => $getToken['access_token'],
                    'auth_id' => md5($getToken['refresh_token']),
                    'expire_in' => $getToken['expires_in']
                );
                return SerPublic::ApiJson($token, 0, 'success');
            }
            $data['open_id'] = $open_id;
            Db::name('user')->insert($data);
            $getToken = SerAuth::makeToken($open_id);
            $token = array(
                'token' => $getToken['access_token'],
                'auth_id' => md5($getToken['refresh_token']),
                'expire_in' => $getToken['expires_in']
            );
            return SerPublic::ApiJson($token, 0, 'success');
        } catch (\RuntimeException $exception) {
            return SerPublic::ApiJson('', 101, $exception->getMessage());
        } catch (PDOException $e) {
            return SerPublic::ApiJson('', 3001, $e->getMessage());
        } catch (Exception $e) {
            return SerPublic::ApiJson('', 3001, $e->getMessage());
        }
    }

    /*刷新Token*/
    public function refresh()
    {
        try {
            /*auth_id是md5加密过的refresh_token*/
            $Auth = Request::header('Authorization');
            $auth_id = Request::post('auth_id');
            if (!isset($Auth, $auth_id)) {
                throw new \RuntimeException('参数有误');
            }
            $token = SerAuth::getFinalToken($Auth);
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
        } catch (\RuntimeException $exception) {
            return SerPublic::ApiJson('', 101, $exception->getMessage());
        }

    }

    /*退出登录*/
    public function logout()
    {
        return SerPublic::ApiJson('', 0, '退出成功！');
    }
}