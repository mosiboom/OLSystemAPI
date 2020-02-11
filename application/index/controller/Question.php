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
    }

    public function delete()
    {
    }

    public function insert()
    {
    }

    public function save()
    {
    }

}