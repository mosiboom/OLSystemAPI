<?php

namespace app\index\controller;

use think\Controller;
use think\Db;


class Index extends Controller
{
    public function index()
    {
        return $this->fetch('/welcome');
    }

    public function notFound()
    {
        return $this->fetch('/404');
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

    /*获取慕课网课程*/
    public function getImoocClass()
    {
        require_once 'SerSimpleHtmlDom.php';
        $str = get('https://www.imooc.com/course/list', array(), array(), false);
        $html = new simple_html_dom();
        $html->load($str);
        //dump($html);
        $insertData = [];
        foreach ($html->find('div.course-card-container') as $element) {
            $title = $element->find('h3.course-card-name', 0)->plaintext;
            $desc = $element->find('p.course-card-desc', 0)->plaintext;
            $cover_url = $element->find('div.course-card-top img.course-banner', 0)->getAttribute('data-original');
            $cover_url = str_replace('//', 'http://', $cover_url);
            $hot = $element->find('div.course-card-info span', 1)->plaintext;
            $data = [
                'title' => $title,
                'desc' => $desc,
                'teacher' => '慕课免费课程',
                'teacher_desc' => '以上课程内容来自慕课网的免费IT课程，如有侵权，请联系作者删除。慕课网是垂直的互联网IT技能免费学习网站。以独家视频教程、在线编程工具、学习计划、问答社区为核心特色。在这里，你可以找到最好的互联网技术牛人，也可以通过免费的在线公开视频课程学习国内领先的互联网IT技术。
慕课网课程涵盖前端开发、PHP、Html5、Android、iOS、Swift等IT前沿技术语言，包括基础课程、实用案例、高级分享三大类型，适合不同阶段的学习人群。以纯干货、短视频的形式为平台特点，为在校学生、职场白领提供了一个迅速提升技能、共同分享进步的学习平台。',
                'cover_url' => $cover_url,
                'hot' => $hot,
                'update_time' => time(),
                'create_time' => time(),
                'status' => '1'
            ];
            array_push($insertData, $data);
        }
        $res = Db::table('course')->insertAll($insertData);
        dump($res);
    }
}
