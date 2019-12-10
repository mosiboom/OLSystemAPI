<?php

namespace app\index\controller;

use app\server\SerPublic;
use think\cache\driver\Redis;
use think\Controller;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;

class Article extends Controller
{
    public function getAll()
    {
        try {
            $offset = Request::get('offset', 1);
            $data = Db::table('article')->where('status', '1')
                ->alias('a')
                ->join('article_category ac', 'ac.id=a.cat_id')
                ->order('hot', 'desc')
                ->page($offset, '20')
                ->field('a.id,name as cat_name,hot,title,desc,create_time,update_time,author,cover_url,cat_id')
                ->select();
            foreach ($data as $k => $v) {
                $data[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
                $data[$k]['update_time'] = date('Y-m-d H:i', $v['update_time']);
            }
            return SerPublic::ApiJson(array(
                'nextPage' => $offset + 1,
                'data' => $data
            ), 0, 'success');
        } catch (DataNotFoundException $e) {
            return SerPublic::ApiJson('', 3002, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return SerPublic::ApiJson('', 3002, $e->getMessage());
        } catch (DbException $e) {
            return SerPublic::ApiJson('', 3001, $e->getMessage());
        }
    }

    public function getOne()
    {
        try {
            $id = Request::get('id');
            if (!$id) {
                throw new \RuntimeException('参数有误');
            }
            $redis = new Redis();
            $info = $redis->get("ol_system_article_{$id}");
            if ($info) {
                return SerPublic::ApiJson(json_decode($info, true), 0, 'success');
            }
            $info = Db::table('article')
                ->where('id', $id)
                ->field('*')
                ->find();

            if (!$info) {
                throw new DataNotFoundException('数据不存在！');
            }
            $info['create_time'] = date('Y-m-d H:i', $info['create_time']);
            $info['update_time'] = date('Y-m-d H:i', $info['update_time']);
            $redis->set("ol_system_article_{$id}", json_encode($info), '3600');

            return SerPublic::ApiJson($info, 0, 'success');
        } catch (\RuntimeException $exception) {
            return SerPublic::ApiJson('', 101, $exception->getMessage());
        } catch (DataNotFoundException $e) {
            return SerPublic::ApiJson('', 3002, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return SerPublic::ApiJson('', 3002, $e->getMessage());
        } catch (DbException $e) {
            return SerPublic::ApiJson('', 3001, $e->getMessage());
        }
    }
}