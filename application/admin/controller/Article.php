<?php

namespace app\admin\controller;

use app\server\SerPublic;
use think\{cache\driver\Redis,
    console\Output,
    Controller,
    Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    Exception,
    exception\DbException,
    facade\Request
};
use http\Exception\RuntimeException;

class Article extends Controller
{
    public function getAll()
    {
        try {
            $data = Db::table('article')
                ->alias('a')
                ->join('article_category ac', 'ac.id=a.cat_id')
                ->order('hot', 'desc')
                ->field('a.id,name as cat_name,hot,title,desc,create_time,update_time,author,a.cover_url,cat_id,a.status')
                ->select();
            if (count($data) == 0) {
                throw new DataNotFoundException('数据不存在！');
            }
            foreach ($data as $k => $v) {
                $data[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
                $data[$k]['update_time'] = date('Y-m-d H:i', $v['update_time']);
            }
            return SerPublic::ApiJson(array(
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
            /*$redis = new Redis();
            $info = $redis->get("ol_system_admin_article_{$id}");
            if ($info) {
                return SerPublic::ApiJson(json_decode($info, true), 0, 'success');
            }*/
            $info = Db::table('article')
                ->alias('a')
                ->join('article_category ac', 'ac.id=a.cat_id')
                ->where('a.id', $id)
                ->field('a.*,ac.name as cat_name')
                ->find();

            if (!$info) {
                throw new DataNotFoundException('数据不存在！');
            }
            $info['create_time'] = date('Y-m-d H:i', $info['create_time']);
            $info['update_time'] = date('Y-m-d H:i', $info['update_time']);
//            $redis->set("ol_system_admin_article_{$id}", json_encode($info), '3600');

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

    public function delete()
    {
    }

    public function insert()
    {
    }

    public function save()
    {
        try {
            $title = Request::post('title');
            $desc = Request::post('desc');
            $author = Request::post('author', '暂无');
            $content = Request::request('content');
            $cover_url = Request::post('cover_url');
            $cat_id = Request::post('cat_id');
            $hot = Request::post('hot', 0);
            if (!isset($title, $desc, $content)) {
                throw new \RuntimeException('参数有误！');
            }
            $id = request()->route('id');
            $data = [
                'title' => $title,
                'desc' => $desc,
                'update_time' => time(),
                'create_time' => time(),
                'cover_url' => $cover_url,
                'hot' => $hot,
                'status' => 0,
                'author' => $author,
                'content' => $content,
                'cat_id' => $cat_id
            ];
            /*更新*/
            if ($id) {
                $info = $this->info($id);
                $status = Request::post('status');
                if (!$info) throw new DataNotFoundException('ID有误！');
                if (!in_array($status, array(0, 1))) throw new \RuntimeException('参数有误！');
                if ($cover_url != $info['cover_url']) {
                    //封面不一样说明更换了封面
                    /*if (!SerPublic::checkUploadURL($cover_url, 'picture'))
                        throw new \RuntimeException('图片链接有误1！');*/
                    $cover_url = SerPublic::getWithoutTmp($cover_url);
                    if (!$cover_url) {
                        throw new \RuntimeException('图片链接有误2！');
                    }
                    $data['cover_url'] = $cover_url;
                }
                $data['status'] = $status;
                $res = Db::table('article')->where('id', $id)->update($data);
                if (!$res) throw new Exception('更新失败！');
                return SerPublic::ApiSuccess();
            }
            /*添加*/
            /*if (!SerPublic::checkUploadURL($cover_url, 'picture'))
                throw new \RuntimeException('图片链接有误1！');*/
            $cover_url = SerPublic::getWithoutTmp($cover_url);
            if (!$cover_url) {
                throw new \RuntimeException('图片链接有误2！');
            }
            $data['cover_url'] = $cover_url;
            $insert_id = Db::table('article')->insertGetId($data);
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
            $info = Db::table('article')->where('id', $id)->find();
            if (!$info) {
                return false;
            }
            return $info;
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
    }

    public function categoryAll()
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

    public function categoryOne($output = true, $id = '')
    {
        try {
            if (!$output) {
                return Db::table('article_category')->where('id', $id)->findOrFail();
            }
            $id = Request::get('id');
            if (!isset($id)) throw new \RuntimeException('参数有误！');
            $return = Db::table('article_category')->where('id', $id)->findOrFail();
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

    public function categorySave()
    {
        try {
            $id = Request::post('id');
            $cover_url = Request::post('cover_url');
            $name = Request::post('name');
            if (!isset($name)) throw new \RuntimeException('参数有误！');
            $data['name'] = $name;
            /*更新*/
            if ($id) {
                $info = $this->categoryOne(false, $id);
                if ($cover_url != $info['cover_url']) {
                    //封面不一样说明更换了封面
                    /*if (!SerPublic::checkUploadURL($cover_url, 'picture'))
                        throw new \RuntimeException('图片链接有误1！');*/
                    $cover_url = SerPublic::getWithoutTmp($cover_url);
                    if (!$cover_url) {
                        throw new \RuntimeException('图片链接有误2！');
                    }
                    $data['cover_url'] = $cover_url;
                }
                $res = Db::table('article_category')->where('id', $id)->update($data);
                if (!$res) throw new \Exception('更新失败！');
                return SerPublic::ApiSuccess('');
            }
            /*添加*/
            /*if (!SerPublic::checkUploadURL($cover_url, 'picture'))
                throw new \RuntimeException('图片链接有误1！');*/
            $cover_url = SerPublic::getWithoutTmp($cover_url);
            if (!$cover_url) {
                throw new \RuntimeException('图片链接有误2！');
            }
            $data['cover_url'] = $cover_url;
            $insert_id = Db::table('article_category')->insertGetId($data);
            if ($insert_id) {
                return SerPublic::ApiSuccess(array('id' => $insert_id));
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

    public function categoryDelete()
    {
        try {
            $id = Request::post('id');
            if (!isset($id)) throw new \RuntimeException('参数有误！');
            $return = Db::table('article_category')->where('id', $id)->delete();
            if ($return)
                return SerPublic::ApiSuccess($return);
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