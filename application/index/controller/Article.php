<?php

namespace app\index\controller;

use app\server\SerPublic;
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
                ->order('hot', 'desc')
                ->page($offset, '20')
                ->field('id,hot,title,desc,create_time,update_time,author,cover_url,cat_id')
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
}