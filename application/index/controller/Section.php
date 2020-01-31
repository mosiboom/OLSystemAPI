<?php

namespace app\index\controller;

use app\server\SerPublic;
use think\{Controller,
    Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    facade\Request
};

class Section extends \app\admin\controller\Section
{
    public function get()
    {
        return $this->getAll();

    }

    public function detail()
    {
        $this->getOne();
    }


    public function insertComment()
    {
        try {
            $user_id = Request::param('payload')['uid'];
            //$user_id = Request::post('user_id');
            $content = Request::post('content');
            $section_id = Request::post('section_id');
            $pid = Request::post('pid');
            if (!$user_id || !$content || !$section_id || $pid == "")
                throw new \RuntimeException('参数有误！');
            $data = array(
                'user_id' => $user_id,
                'content' => $content,
                'section_id' => $section_id,
                'create_time' => time(),
                'pid' => $pid
            );
            $res = Db::name('section_comment')->strict(false)->insert($data);
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
            $section_id = Request::get('section_id');
            $offset = Request::get('offset', 1);
            if (!$section_id || !$offset) throw new \RuntimeException('参数有误');
            $data = Db::table('section_comment')
                ->alias('ac')
                ->join('user', 'user.open_id=ac.user_id')
                ->where('section_id', $section_id)
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

}