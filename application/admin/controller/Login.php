<?php

namespace app\admin\controller;

use app\server\SerAuth;
use app\server\SerPublic;
use think\{cache\driver\Redis,
    Controller,
    Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    Exception,
    exception\DbException,
    facade\Request,
    Route
};
use http\Exception\RuntimeException;

class Login extends Controller
{
    public function login()
    {
        try {
            $account = Request::post('account');
            $password = Request::post('pwd');
            if (!isset($account) || strlen($password) != 32) throw new \RuntimeException('参数有误！');
            $info = Db::table('admin')->where('account', $account)->find();
            if (!$info) throw new DataNotFoundException('用户不存在！');
            if ($password != $info['pwd'])
                throw new \RuntimeException('用户名或密码不正确！');
            $redis = new Redis();
            $time = time();
            $data = $redis->get("admin_login_{$info['id']}");
            if ($data) {
                $data = json_decode($data, true);
                if ($time - $data['time'] < 120) throw new \RuntimeException('操作频繁，请2分钟后再试！');
            }
            $token = SerAuth::makeAccessToken($info['id'], 'Jasper_Admin');
            $redis->set("admin_login_{$info['id']}", json_encode(['token' => $token, 'time' => time()]));
            return SerPublic::ApiSuccess(['token' => $token]);
        } catch (\RuntimeException $e) {
            return SerPublic::ApiJson('', 101, $e->getMessage());
        } catch (DataNotFoundException $e) {
            return SerPublic::ApiJson('', 3002, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return SerPublic::ApiJson('', 3002, $e->getMessage());
        } catch (DbException $e) {
            return SerPublic::ApiJson('', 3001, $e->getMessage());
        } catch (\Exception $e) {
            return SerPublic::ApiJson('', 3003, $e->getMessage());
        }
    }

    public function logout()
    {
        $Auth = Request::header('Authorization');
        $result = Request::param('payload');
        $token = SerAuth::getFinalToken($Auth);
        $expire_time = config()['jwt']['access_token_expire_time'];
        $redis = new Redis();
        $redis->set("admin_login_blacklist_{$result['uid']}", $token, $expire_time);
        return SerPublic::ApiSuccess();

    }
}