<?php

namespace app\index\controller;

use app\server\SerPublic;
use http\Exception\RuntimeException;
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
                ->field('a.id,name as cat_name,hot,title,desc,create_time,update_time,author,a.cover_url,cat_id')
                ->select();
            if (count($data) == 0) {
                throw new DataNotFoundException('数据不存在！');
            }
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

    public function insertComment()
    {
        try {
            $user_id = Request::param('payload')['uid'];
            //$user_id = Request::post('user_id');
            $content = Request::post('content');
            $article_id = Request::post('article_id');
            $pid = Request::post('pid');
            if (!$user_id || !$content || !$article_id || $pid == "")
                throw new \RuntimeException('参数有误！');
            $data = array(
                'user_id' => $user_id,
                'content' => $content,
                'article_id' => $article_id,
                'create_time' => time(),
                'pid' => $pid
            );
            $res = Db::name('article_comment')->strict(false)->insert($data);
            if (!$res) {
                return SerPublic::ApiJson('', 3001, '插入失败！');
            }
            return SerPublic::ApiJson('', 0, 'success');
        } catch (\RuntimeException $exception) {
            return SerPublic::ApiJson('', 101, $exception->getMessage());
        }
    }

    public function comment()
    {
        try {
            $article_id = Request::get('article_id');
            $offset = Request::get('offset', 1);
            if (!$article_id || !$offset) throw new \RuntimeException('参数有误');
            $data = Db::table('article_comment')
                ->alias('ac')
                ->join('user', 'user.open_id=ac.user_id')
                ->where('article_id', $article_id)
                ->order('create_time', 'desc')
                ->field('ac.*,user.name')->select();
            $data = SerPublic::actionClassData($data, 0);
            $count = count($data);
            $num = ceil($count / 5);
            if ($offset > $num) {
                return SerPublic::ApiJson(array('nextPage' => '', 'data' => array()), 0, 'success');
            }
            $data = array_chunk($data, 5);
            $return = $data[$offset - 1];
            return SerPublic::ApiJson(array('nextPage' => $offset + 1, 'data' => $return), 0, 'success');
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

    public function category()
    {
        try {
            $return = Db::table('article_category')->select();
            return SerPublic::ApiSuccess($return);
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

    public function byCategory()
    {
        try {
            $cat_id = Request::get('id');
            $offset = Request::get('offset', 1);
            if (!$cat_id) throw new \RuntimeException('参数有误！');
            $data = Db::table('article')
                ->alias('a')
                ->where('cat_id', $cat_id)
                ->order('hot', 'desc')
                ->page($offset, '20')
                ->field('a.id,hot,title,desc,create_time,update_time,author,cover_url,cat_id')
                ->select();
            if (!$data) {
                throw new DataNotFoundException('该分类没有数据！');
            }
            return SerPublic::ApiSuccess(array('data' => $data, 'nextPage' => $offset + 1));
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