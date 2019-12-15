<?php

namespace app\index\controller;

use app\server\SerPublic;
use http\Exception\RuntimeException;
use think\cache\driver\Redis;
use think\Controller;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;

class Article extends Controller
{
    public function getAll()
    {
        try {
            $offset = Request::get('offset', 1);
            $data = Db::table('article')->where('status', '1')
                ->alias('a')
                ->join('article_category ac', 'ac.id=a.cat_id')
                ->order('hot', 'desc')
                ->page($offset, '20')
                ->field('a.id,name as cat_name,hot,title,desc,create_time,update_time,author,cover_url,cat_id')
                ->select();
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
        try {
            $id = Request::get('id');
            if (!$id) {
                throw new \RuntimeException('参数有误');
            }
            $redis = new Redis();
            $info = $redis->get("ol_system_article_{$id}");
            if ($info) {
                return SerPublic::ApiJson(json_decode($info, true), 0, 'success');
            }
            $info = Db::table('article')
                ->where('id', $id)
                ->field('*')
                ->find();

            if (!$info) {
                throw new DataNotFoundException('数据不存在！');
            }
            $info['create_time'] = date('Y-m-d H:i', $info['create_time']);
            $info['update_time'] = date('Y-m-d H:i', $info['update_time']);
            $redis->set("ol_system_article_{$id}", json_encode($info), '3600');

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

    public function getComment()
    {
    }

    public function insertComment()
    {
        try {
            //$user_id = Request::param('payload')['uid'];
            $user_id = Request::post('open_id');
            $content = Request::post('content');
            $article_id = Request::post('article_id');
            $pid = Request::post('pid');
            if (!$user_id || !$content || !$article_id || !$pid)
                throw new \RuntimeException('参数有误！');
            $data = array(
                'user_id' => $user_id,
                'content' => $content,
                'article_id' => $article_id,
                'create_time' => time(),
                'pid' => $pid
            );
            $res = Db::name('article_comment')->strict(false)->insert($data);
            if (!$res) {
                return SerPublic::ApiJson('', 3001, '插入失败！');
            }
            return SerPublic::ApiJson('', 0, 'success');
        } catch (\RuntimeException $exception) {
            return SerPublic::ApiJson('', 101, $exception->getMessage());
        }
    }

    public function comment()
    {
        try {
            $article_id = Request::get('article_id');
            $offset = Request::get('offset', 1);
            if (!$article_id || $offset) throw new \RuntimeException('参数有误');
            $data = Db::table('article_comment')
                ->alias('ac')
                ->join('user', 'user.open_id=ac.user_id')
                ->where('article_id', $article_id)
                ->order('create_time', 'desc')
                ->field('ac.*,user.name')->select();
            $data = SerPublic::actionClassData($data, 0);
            $count = count($data);
            $num = ceil($count / 5);
            if ($offset > $num) {
                return SerPublic::ApiJson(array('nextPage' => '', 'data' => array()), 0, 'success');
            }
            $data = array_chunk($data, 5);
            $return = $data[$offset - 1];
            return SerPublic::ApiJson(array('nextPage' => $offset + 1, 'data' => $return), 0, 'success');
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

    public function mock()
    {
        /*$contentArr = [
            '哦，你周末不陪你女朋友打篮球哈',
            '大佬牛逼，蹭个热度，从零实现webpack热更新https://juejin.im/user/5c2178986fb9a049d975396f/pinsjuejin.im/user/5c2178986...',
            '替换改名，这代码真烂，别误导新人',
            '您批评的是',
            '加你微信了，貌似还没加到。。。',
            '一篇文章升三级 ：）',
            '方法三有问题吧，如果value里面含有你指定的key，这样value的只会被改变，所以，最好不要用字符串替换，这种方法很low',
            '对象是什么？ 无序属性集合。那么暴力二delete后顺序怎么可以作为缺点呢？',
            '方案三的 代码  JSON.stringify(todayILearn) todayILearn是前面demo的变量，你第三个示例已经改成了mapObj，这里忘了改了',
            '每个知识点好像都是重新声明了变量的',
            '不错 写的很好',
            '特性三：”转换值如果有 toJSON() 函数，该函数返回什么值，序列化结果就是什么值“。===》 应该是以toJSON() 的返回值作为参数执行stringify吧',
            '例子中方案三不知道哪里优雅了?也许对优雅这个词有误解',
            '厉害了',
            '对于继承、原型链这些，有没有好的处理方法',
            'get到了对象还可以循环引用的呀,打印了一下,可以无限展开 本以为会像遍历一样陷入死循环卡死',
            '大佬，我可以转到我的公众号吗',
            '有点意思', '文章开头不好，方案三一点都不优雅，相反很low，并且是错误的，字符串替换就优雅了么? 核心点就是把下划线连接的key变成驼峰形式的key，需要方案四',
            '好贴，学到了！', '我喜欢这个前言，', '文章很好  但是其实这个答案是错误的   如果object的value值中含有你这4个字段,也会被替换,所以前两个解决办法虽然暴力,但是很好,最后一个优雅但是不能用.',
            '方案三我就没看的懂，可以解释下嘛',
            '第二个参数是数组时，感觉描述可以再丰富一点。'
        ];
        $userArr = [
            'o5nEd5EtCpel6kw-bQ9tCRCZOgO4',
            '1', '2', '3', '4', '5', '6', '7'
        ];
        $data = array();
        for ($i = 0; $i <= 3000; $i++) {
            $data[$i] = array(
                'user_id' => $userArr[rand(0, 7)],
                'content' => $contentArr[rand(0, 20)],
                'article_id' => rand(345, 720),
                'create_time' => time() + rand(0, 86400),
                'pid' => 0
            );
        }
        Db::name('article_comment')->data($data)->insertAll();
        echo '插入成功！';*/
        for ($i = 345; $i <= 720; $i++) {
            $data = Db::table('article_comment')
                ->where('article_id', $i)
                ->order('create_time', 'desc')
                ->column('id');
            $count = count($data);
            foreach ($data as $k => $v) {
                $rand = rand(0, $count - 1);
                if ($k % 2 == 0) {
                    Db::name('article_comment')
                        ->where('id', $v)
                        ->update(['pid' => $data[$rand]]);
                } else {
                    Db::name('article_comment')
                        ->where('id', $v)
                        ->update(['pid' => 0]);
                }
            }
        }


        echo "更新成功！";
    }

    public function rand1($i, $count)
    {
        dump($i);
        $rand = rand(0, $count - 1);
        while ($rand == $i) {
            $this->rand1($i, $count);
        }
        return $rand;
    }
}