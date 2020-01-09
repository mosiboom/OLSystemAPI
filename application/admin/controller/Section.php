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

class Section extends Controller
{
    public function getAll()
    {
        try {
            $course_id = request()->route('course_id');
            if (!isset($course_id)) throw new \RuntimeException('参数有误');
            $data = Db::table('section')->where('course_id', $course_id)->select();
            if (!$data) throw new DataNotFoundException('该课程未添加小节');
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

    public function getOne()
    {
    }

    public function delete()
    {
    }

    public function insert()
    {
        try {
            $request_data = Request::post('data');
            $insert_data = [];
            if (!is_array($request_data)) {
                throw new \RuntimeException('参数应为数组！');
            }
            foreach ($request_data as $key => $v) {
                $course_id = $v['course_id'];
                $content = $v['content'];
                $video_url = $v['video_url'];
                $title = $v['title'];
                $num = $v['num'];
                $diffcult_point = $v['diffcult_point'];
                if (!isset($course_id, $content, $title, $num) || !in_array($diffcult_point, array(0, 1)))
                    throw new \RuntimeException('参数有误！');
                $data = [
                    'course_id' => $course_id,
                    'content' => $content,
                    'video_url' => $video_url,
                    'title' => $title,
                    'num' => $num,
                    'diffcult_point' => $diffcult_point
                ];
                if ($video_url != '') { //如果有视频
                    if (SerPublic::checkUploadURL($video_url, 'video'))
                        throw new \RuntimeException('视频链接有误1！');
                    $video_url = SerPublic::getWithoutTmp($video_url);
                    if (!$video_url) {
                        throw new \RuntimeException('视频链接有误2！');
                    }
                    $data['video_url'] = $video_url;
                }
                unset($data['id']);
                array_push($insert_data, $data);
            }
            $insert = Db::table('section')->insertAll($insert_data);
            if ($insert) {
                return SerPublic::ApiSuccess();
            }
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

    public function save()
    {
        try {
            $request_data = Request::post('data');
            $update_data = [];
            if (!is_array($request_data)) {
                throw new \RuntimeException('参数应为数组！');
            }
            foreach ($request_data as $key => $v) {
                $course_id = $v['course_id'];
                $content = $v['content'];
                $video_url = $v['video_url'];
                $title = $v['title'];
                $num = $v['num'];
                $id = $v['id'];
                $diffcult_point = $v['diffcult_point'];
                if (!isset($course_id, $content, $title, $num, $id) || !in_array($diffcult_point, array(0, 1)))
                    throw new \RuntimeException('参数有误！');
                $data = [
                    'id' => $id,
                    'course_id' => $course_id,
                    'content' => $content,
                    'video_url' => $video_url,
                    'title' => $title,
                    'num' => $num,
                    'diffcult_point' => $diffcult_point
                ];
                $info = $this->info($id);
                if (!$info) throw new DataNotFoundException('ID有误！');
                if ($video_url != '') {
                    if ($video_url != $info['video_url']) {
                        //链接不一样说明更换了链接
                        if (SerPublic::checkUploadURL($video_url, 'video'))
                            throw new \RuntimeException('视频链接有误1！');
                        $video_url = SerPublic::getWithoutTmp($video_url);
                        if (!$video_url) {
                            throw new \RuntimeException('视频链接有误2！');
                        }
                        $data['video_url'] = $video_url;
                    }
                }
                array_push($update_data, $data);
            }
            $section = new \app\admin\model\Section();
            $res = $section->saveAll($update_data);
            if (!$res) throw new Exception('更新失败！');
            return SerPublic::ApiSuccess();
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

    private function info($id)
    {
        try {
            $info = Db::table('section')->where('id', $id)->find();
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
