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

class Course extends Controller
{
    /*获取所有课程数据*/
    public function getAll()
    {
        try {
            $offset = Request::get('offset', 1);
            $data = Db::table('course')->where('status', '1')
                ->order('hot', 'desc')
                ->page($offset, '20')
                ->field('id,title,desc,update_time,create_time,teacher,teacher_desc,cover_url,hot')
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

    /*获取单个课程及小节*/
    public function getOne()
    {
        try {
            $id = Request::get('course_id');
            if (!isset($id)) throw new \RuntimeException('参数有误');
            $data['course'] = Db::table('course')->where('id', $id)->findOrFail();
            $data['section'] = Db::table('section')->where('course_id', $id)->select();
            return SerPublic::ApiSuccess($data);
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
}