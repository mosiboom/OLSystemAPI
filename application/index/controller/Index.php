<?php

namespace app\index\controller;

use League\HTMLToMarkdown\HtmlConverter;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch('/welcome');
    }

    /*获取掘金文章*/
    public function getJueJinArticle()
    {
        $return = postRaw('https://web-api.juejin.im/query', '{"operationName":"","query":"","variables":{"first":100,"after":"","order":"POPULAR"},"extensions":{"query":{"id":"21207e9ddb1de777adeaca7a2fb38030"}}}', array('X-Agent:Juejin/Web'));
        $return = json_decode($return, true);
        $data = $return['data']['articleFeed']['items']['edges'];
        dump($data);
        $article = get('https://juejin.im/post/5dee6f54f265da33ba5a79c8', array(), array(), false);
        $matches = array();
        preg_match_all('/<article[^>]*>([\s\S]*?)<\/article>/i', $article, $matches);
        $html = $matches[0][0];
        $html = preg_replace('#<div[^>]*?class="author-info-box"[^>]*>(.*?)</div>#is', '', $html);
        $html = preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', '', $html);
        echo $html;
        //dump(strip_tags($article));

    }

}
