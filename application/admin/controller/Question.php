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

class Question extends Controller
{
    public function getAll()
    {
        try {
            $section_id = Request::get('section_id');
            $data = Db::table('question')->where('section_id', $section_id)->selectOrFail();
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
        try {
            $id = Request::get('id');
            $data = $this->info($id);
            if (!$data) throw new DataNotFoundException('数据不存在！');
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

    public function delete()
    {
        try {
            $id = Request::post('id');
            if (!isset($id)) {
                throw new \RuntimeException('参数有误！');
            }
            $res = Db::table('question')->where('id', $id)->delete();
            if (!$res) throw new DataNotFoundException('删除失败！');
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

    public function insert()
    {
        try {
            $request_data = Request::post('data');
            $insert_data = [];
            if (!is_array($request_data)) {
                throw new \RuntimeException('参数应为数组！');
            }
            foreach ($request_data as $key => $v) {
                $difficulty = $v['difficulty'];
                $question_type = $v['question_type'];
                $section_id = $v['section_id'];
                $video_url = $v['video_url'];
                $title = $v['title'];
                $num = $v['num'];
                $answer_parsing = isset($v['answer_parsing']) ? $v['answer_parsing'] : '暂无';
                $selected = $v['selected'];
                $answer = $v['answer'];
                if (!isset($section_id, $title, $num, $selected, $answer) || !in_array($question_type, array(0, 1)) || !in_array($difficulty, array(1, 2, 3, 4, 5)))
                    throw new \RuntimeException('参数有误！');
                $data = [
                    'title' => $title,
                    'selected' => json_encode($selected),
                    'answer' => $answer,
                    'difficulty' => $difficulty,
                    'video_url' => $video_url,
                    'question_type' => $question_type,
                    'section_id' => $section_id,
                    'answer_parsing' => $answer_parsing,
                    'num' => $num
                ];
                if ($video_url != '') { //如果有视频
                    $video_url = SerPublic::getWithoutTmp($video_url);
                    if (!$video_url) {
                        throw new \RuntimeException('视频链接有误2！');
                    }
                    $data['video_url'] = $video_url;
                }
                array_push($insert_data, $data);
            }
            $insert = Db::table('question')->insertAll($insert_data);
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
                $difficulty = $v['difficulty'];
                $question_type = $v['question_type'];
                $section_id = $v['section_id'];
                $video_url = $v['video_url'];
                $title = $v['title'];
                $num = $v['num'];
                $answer_parsing = isset($v['answer_parsing']) ? $v['answer_parsing'] : '暂无';
                $selected = $v['selected'];
                $answer = $v['answer'];
                $id = $v['id'];
                if (!isset($section_id, $title, $num, $selected, $answer) || !in_array($question_type, array(0, 1)) || !in_array($difficulty, array(1, 2, 3, 4, 5)))
                    throw new \RuntimeException('参数有误！');
                $data = [
                    'id' => $id,
                    'title' => $title,
                    'selected' => json_encode($selected),
                    'answer' => $answer,
                    'difficulty' => $difficulty,
                    'video_url' => $video_url,
                    'question_type' => $question_type,
                    'section_id' => $section_id,
                    'answer_parsing' => $answer_parsing,
                    'num' => $num
                ];
                $info = $this->info($id);
                if (!$info) throw new DataNotFoundException('ID有误！');
                if ($video_url != '') { //如果有视频
                    if ($video_url != $info['video_url']) {
                        //链接不一样说明更换了链接
                        $video_url = SerPublic::getWithoutTmp($video_url);
                        if (!$video_url) {
                            throw new \RuntimeException('视频链接有误2！');
                        }
                        $data['video_url'] = $video_url;
                    }
                }
                array_push($update_data, $data);
            }
            $section = new \app\admin\model\Question();
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
            $info = Db::table('question')->where('id', $id)->find();
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