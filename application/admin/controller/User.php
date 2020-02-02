<?php

namespace app\admin\controller;

use app\server\SerPublic;
use think\{Controller,
    Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    facade\Request,
};
use http\Exception\RuntimeException;

class User extends Controller
{
    /*用户所有信息和统计数据*/
    public function getAll()
    {
        try {
            $data = Db::table('user')
                ->order('create_time', 'desc')
                ->selectOrFail();
            foreach ($data as $k => $v) {
                $data[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
                $data[$k]['wechat_info'] = json_decode($v['wechat_user'], true);
            }
            return SerPublic::ApiSuccess(
                ['data' => $data, 'statistical' => $this->statistical()]
            );
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

    /*用户统计信息*/
    public function statistical()
    {
        //算出本月的年月
        $data = date('Y-m', time());
        //计算本月有几天
        $_now_month_day = cal_days_in_month(CAL_GREGORIAN, date('m', time()), date('Y', time()));
        $_now_month_first = strtotime($data);//本月第一天
        $_now_month_last = $_now_month_first + 3600 * 24 * $_now_month_day;//本月最后一天
        /*总用户量*/
        $total = Db::table('user')->count();
        /*本月新增的用户量*/
        $now_month = Db::table('user')
            ->where('create_time', '>', $_now_month_first)
            ->where('create_time', '<', $_now_month_last)
            ->count();
        /*近三个月用户增长率*/
        //三个月前的年份，如果是1月2月，则年份则会减一
        $_three_count = $_now_month_day;
        if (date('m', time()) - 1 > 0) {
            $_three_count += cal_days_in_month(CAL_GREGORIAN, date('m', time()) - 1, date('Y', time()));
            if (date('m', time()) - 2 > 0) {
                $_three_count += cal_days_in_month(CAL_GREGORIAN, date('m', time()) - 2, date('Y', time()));
            } else {
                $_three_count += cal_days_in_month(CAL_GREGORIAN, 12, date('Y', time()) - 1);
            }
        } else {//说明是1月，近三个月是1，12，11
            $_three_count += cal_days_in_month(CAL_GREGORIAN, 12, date('Y', time()) - 1) + cal_days_in_month(CAL_GREGORIAN, 11, date('Y', time()) - 1);
        }
        $_three_month_first = $_now_month_last - 3600 * 24 * $_three_count;//三个月前第一天
        $_three_month_user = Db::table('user')
            ->where('create_time', '>', $_three_month_first)
            ->where('create_time', '<', $_now_month_last)
            ->count();
        $three_month = (($_three_month_user / $total) * 100) . "%";
        /*正常用户量*/
        $normal = Db::table('user')->where('status', 1)->count();
        /*活跃的用户量（评论超过3条）*/
        $_active_article = Db::table('article_comment')
            ->group('user_id')
            ->having('count(*)>3')
            ->column('user_id');
        $_active_section = Db::table('section_comment')
            ->group('user_id')
            ->having('count(*)>3')
            ->column('user_id');
        $active = count(array_unique(array_merge($_active_article, $_active_section)));

        /*男性用户量*/
        $man = Db::table('user')->where('sex', 1)->count();
        /*女性用户量*/
        $woman = Db::table('user')->where('sex', 2)->count();
        return [
            'total' => ['data' => $total, 'info' => '总用户量'],
            'now_month' => ['data' => $now_month, 'info' => '本月新增的用户量'],
            'three_month' => ['data' => $three_month, 'info' => '近三个月用户增长率'],
            'normal' => ['data' => $normal, 'info' => '正常用户量'],
            'abnormal' => ['data' => $total - $normal, 'info' => '异常用户量'],
            'active' => ['data' => $active, 'info' => '活跃用户量（评论超过3条）'],
            'man' => ['data' => $man, 'info' => '男性用户量'],
            'woman' => ['data' => $woman, 'info' => '女性用户量'],
        ];

    }

    public function getOne()
    {
        try {
            $user_id = Request::get('id');
            if (!isset($user_id)) throw new \RuntimeException('参数有误！');
            $data = Db::table('user')->where('open_id', $user_id)->findOrFail();
            $data['wechat_info'] = json_decode($data['wechat_user'], true);
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
    }

    public function status()
    {
        try {
            $user_id = Request::post('id');
            $status = Request::post('status');
            if (!isset($user_id) || !in_array($status, [0, 1])) throw new \RuntimeException('参数有误！');
            $data = [
                'open_id' => $user_id,
                'status' => $status
            ];
            $res = Db::table('user')->update($data);
            if (!$res) throw new DbException('更新失败');
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

    public function save()
    {
    }

}