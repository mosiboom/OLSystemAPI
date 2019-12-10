<?php

namespace app\index\controller;

use app\server\SerPublic;
use think\Controller;
use think\Db;
use think\facade\Request;

class Script extends Controller
{
    public function run()
    {
        $sc = Request::get('sc');
        $pw = Request::get('pw');
        if (!isset($sc, $pw)) {
            return SerPublic::ApiJson('', 101, '参数有误');
        }
        if ($pw != "Jasper9360!!!") {
            return SerPublic::ApiJson('', 101, '参数有误');
        }
        switch ($sc) {
            case 'insertJueJin';
                return $this->insertJueJin();
                break;
        }

    }

    public function insertJueJin()
    {
        $return = postRaw('https://web-api.juejin.im/query', '{"operationName":"","query":"","variables":{"first":100,"after":"0.13427484195712","order":"POPULAR"},"extensions":{"query":{"id":"21207e9ddb1de777adeaca7a2fb38030"}}}', array('X-Agent:Juejin/Web'));
        $return = json_decode($return, true);
        $data = $return['data']['articleFeed']['items']['edges'];
        $insertAll = array();
        foreach ($data as $k => $v) {
            $cat_data = array(
                'name' => $v['node']["category"]['name']
            );
            $cat = Db::table('article_category')->where('name', $cat_data['name'])->find();
            if ($cat) {
                $cat_Id = $cat['id'];
            } else {
                $cat_Id = Db::name('article_category')->insertGetId($cat_data);
            }
            $article = get($v['node']['originalUrl'], array(), array(), false);
            $matches = array();
            if (preg_match_all('/<article[^>]*>([\s\S]*?)<\/article>/i', $article, $matches)) {
                $html = $matches[0][0];
            } else {
                echo '1';
                dump($matches);
                continue;
            }
            $html = preg_replace('#<div[^>]*?class="author-info-box"[^>]*>(.*?)</div>#is', '', $html);
            $content = preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', '', $html);
            $art = array(
                'title' => $v['node']['title'],
                'desc' => $v['node']['content'],
                'create_time' => strtotime($v['node']['createdAt']),
                'update_time' => strtotime($v['node']['updatedAt']),
                'hot' => rand(100, 9999),
                'status' => '1',
                'author' => $v['node']['user']['username'],
                'cover_url' => $v['node']['screenshot'],
                'cat_id' => $cat_Id,
                'content' => addslashes($content),
            );
            array_push($insertAll, $art);

        }
        Db::name('article')->insertAll($insertAll);
        echo '插入完成';
        exit;
    }
}