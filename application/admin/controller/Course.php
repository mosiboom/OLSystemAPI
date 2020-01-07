<?php

namespace app\admin\controller;

use app\server\SerPublic;
use think\{Controller,
    Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    Exception,
    exception\DbException,
    facade\Request
};
use http\Exception\RuntimeException;

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
    }

    public function delete()
    {
    }

    /*添加、更新课程*/
    public function save()
    {
        try {
            $title = Request::post('title');
            $desc = Request::post('desc');
            $teacher = Request::post('teacher', '暂无');
            $teacher_desc = Request::post('teacher_desc', '暂无');
            $cover_url = Request::post('cover_url');
            if (!isset($title, $desc, $cover_url)) {
                throw new \RuntimeException('参数有误！');
            }
            $id = request()->route('id');
            $data = [
                'title' => $title,
                'desc' => $desc,
                'update_time' => time(),
                'create_time' => time(),
                'teacher' => $teacher,
                'teacher_desc' => $teacher_desc,
                'cover_url' => $cover_url,
                'hot' => 0,
                'status' => 0
            ];
            /*更新*/
            if ($id) {
                $info = $this->info($id);
                if (!$info) throw new DataNotFoundException('ID有误！');
                if ($cover_url != $info['cover_url']) {
                    //封面不一样说明更换了封面
                    $cover_url = SerPublic::getWithoutTmp($cover_url);
                    if (!$cover_url) {
                        throw new \RuntimeException('上传参数有误！');
                    }
                    $data['cover_url'] = $cover_url;
                }
                $res = Db::table('course')->where('id', $id)->update($data);
                if (!$res) throw new Exception('更新失败！');
                return SerPublic::ApiSuccess('');
            }
            /*添加*/
            $cover_url = SerPublic::getWithoutTmp($cover_url);
            if (!$cover_url) {
                throw new \RuntimeException('上传参数有误！');
            }
            $data['cover_url'] = $cover_url;
            $insert_id = Db::table('course')->insertGetId($data);
            if ($insert_id) {
                return SerPublic::ApiSuccess(array('id' => $insert_id));
            }
        } catch (\RuntimeException $exception) {
            return SerPublic::ApiJson('', 101, $exception->getMessage());
        } catch (DataNotFoundException $e) {
            return SerPublic::ApiJson('', 3002, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            return SerPublic::ApiJson('', 3002, $e->getMessage());
        } catch (DbException $e) {
            return SerPublic::ApiJson('', 3001, $e->getMessage());
        } catch (Exception $e) {
            return SerPublic::ApiJson('', 3003, $e->getMessage());
        }
    }

    private function info($id)
    {
        try {
            $info = Db::table('course')->where('id', $id)->find();
            if (!$info) {
                return false;
            }
            return $info;
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
    }
}