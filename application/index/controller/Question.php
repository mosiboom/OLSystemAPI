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
use http\Exception\RuntimeException;

class Question extends Controller
{
    public function getAll()
    {
        try {
            $section_id = Request::get('section_id');
            $data = Db::table('question')->where('section_id', $section_id)->selectOrFail();
            $count['count'] = count($data);
            $count['score'] = 100 / $count['count'];
            $count['info'] = 'count:问题个数,score:每道题的分数';
            return SerPublic::ApiSuccess(['data' => $data, 'count' => $count]);
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

    public function insertScore()
    {
        try {
            $score = Request::post('score', 0);
            $section_id = Request::post('section_id');
            $payload = Request::param('payload');
            $user_id = $payload['uid'];
            if (!isset($section_id, $payload)) throw new \RuntimeException('参数有误');
            $data = [
                'user_id' => $user_id,
                'section_id' => $section_id,
                'score' => $score,
                'create_time' => time(),
                'update_time' => time()
            ];
            $res = Db::table('question_score')->insert($data);
            if ($res) return SerPublic::ApiSuccess();
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

    /*获取分数接口*/
    public function getScore()
    {
        try {
            $payload = Request::param('payload');
            $user_id = $payload['uid'];
            $section_id = Request::get('section_id');
            if (!isset($section_id)) throw new \RuntimeException('参数有误');
            $data = Db::table('question_score')
                ->where('user_id', $user_id)
                ->where('section_id', $section_id)
                ->order('create_time', 'desc')
                ->select();
            if ($data) {
                return SerPublic::ApiSuccess([
                    'data' => $data,
                    'is_do' => true
                ]);
            }
            return SerPublic::ApiSuccess([
                'data' => '',
                'is_do' => false
            ]);
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