<?php

namespace app\index\controller;

use app\server\SerPublic;
use think\Controller;
use think\Db;
use think\Exception;
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

    public function ImoocSection()
    {
        require_once 'SerSimpleHtmlDom.php';
        /*$html1 = new simple_html_dom();

        $return1 = postRaw('https://www.imooc.com/course/list?sort=pop', '');
        $html1->load($return1);
        $j = 0;
        $all_data = array();
        foreach ($html1->find('div.course-card-container') as $element) {
            $j++;
            if ($j < 21) {
                continue;
            }
            $str1 = $element->find('.course-card', 0)->getAttribute('href');
            $url = 'https://www.imooc.com' . $str1;
            $return = postRaw($url, '');
            $html = new simple_html_dom();
            $html->load($return);

            $b = 0;
            foreach ($html->find('.video') as $ul) {
                foreach ($ul->find('li') as $k => $li) {
                    $a = trim(rtrim(str_replace("开始学习", '', trim($li->plaintext))));
                    $a = preg_replace("/\\d+/", '', $a);
                    $a = str_replace('- ', '', $a);
                    $video_url = $this->tmpRandVideo();
                    $b++;
                    $data = [
                        'title' => $a,
                        'num' => $b,
                        'content' => $this->tmpRandContent(),
                        'course_id' => $j,
                        'video_url' => $video_url,
                        'diffcult_point' => rand(0, 1)
                    ];
                    array_push($all_data, $data);
                }
            }
        }
        //dump($all_data);
        $res = Db::table('section')->insertAll($all_data);
        dump($res);*/

    }

    /*随机有没有视频*/
    public function tmpRandVideo()
    {
        $rand = rand(1, 3);
        switch ($rand) {
            case 1:
                $video = 'http://106.54.187.34/static/upload/video/1580458072.mp4';
                break;
            default:
                $video = '';
        }
        return $video;
    }

    /*随机生成内容*/
    public function tmpRandContent()
    {
        $rand = rand(1, 8);
        switch ($rand) {
            case 1:
                $video = 'C语言中的变量有哪些存储类型，你还记得吗？static老手都这样用。

1、 先来回顾C语言变量

C语言中变量值的存储位置有两类：CPU的寄存器和内存。变量存储类型关系到其存储位置，除了register型存储在CPU寄存器中，C语言提供的其它三种存储类型（auto型、static型、extern型）的变量均存储在内存中。存储位置不同，决定了变量的生存期和作用域。

具体变量介绍请见作者的另一篇文章，名为《说一说C语言中的变量存储类型——“extern”》。

下面我们直接讲干货，static关键字用法。

2、 Static关键字用法

C语言中，无论是变量还是函数都可以用static关键字来修饰。具体用法我们分别来看。

1) 修饰函数

我们知道函数的声明（定义）也可以包括存储类型，但只有extern/static两种。当函数声明为extern，说明函数具有外部链接，其它文件可以调用此函数；当函数声明为static，说明函数是内部链接，即只能在定义函数的文件内部调用函数。当不指明函数存储类型，则默认该函数具有外部链接。

这种情况适用于多文件编程（大多数程序都是这样的）。当某个文件中定义的函数不希望被其它源文件调用，就可以把它声明为static，对其它文件来说该函数不可见。例如，你在file1.c文件中定义了多个函数，如果你不允许函数名为fun2的函数被其它文件的函数调用，只需要将其声明为static即可，这样fun2函数只允许被file1.c中的其它函数调用，其它源文件中的函数无法调用fun2，起到了隐藏的作用。

static int fun2(char c); //内部链接，对外不可见

2) 修饰全局变量

我们知道，全局变量存储在静态存储区，但是它不是静态变量，它的作用范围从定义处到所在源文件末尾。但是它对其它源文件都是有效的，只需要通过extern声明一下即可，详见作者的另一篇文章《说一说C语言中的变量存储类型——“extern”》。

但是有些情况下，某些全局变量不想被同程序的其它源文件使用，那么我们就可以使用static关键字声明一下即可。例如，在file1.c中我们定义的整型全局变量g_a不想被其它源文件中函数使用，只需要在定义g_a时加上static关键字修饰。

static int g_a;

这样g_a对其它源文件比如file2.c是不可见的，当然，file2.c中也可以定义一个同名的静态全局变量，这是没有问题的。';
                break;
            case 2:
                $video = '反射是 PHP 中的一种功能，在 Java 中也有。最开始了解反射是在 Java 中了解的。当时，只是听过，而没有了解过，也没有使用过。听同事说，反射在框架中会经常用到，而在实际项目中用的机会比较少。后来通过实际的项目，我接触了反射，也实际的应用了反射。

这里主要来说一下 PHP 中的反射，实际的项目中我是从 Java 中了解了反射。

实际的项目需求
项目中需要开发一个 TCP 的服务器，用来保持和设备的长连接通信，设备会发来响应的数据，到服务器端来解析并入库。设备在不同的企业发送的数据格式是不同的，而服务器端接收到数据后，需要根据企业的 ID 进行判断解析。

在解析设备发来的数据时，如果逐个字段进行解析，那么代码会非常的长，这时如果使用反射会大大的缩短代码的行数。为甚麽可以使用反射，因为整个数据其实只有几种格式，字符串、单字节和 IEEE 编码的浮点数。

定义数据格式的类
根据不同企业定义不同企业的数据格式的类。这个数据格式其实就是协议，收发双方的协议。收发双方的协议按照企业的不同而不同。

作者：码农UP2U
链接：https://www.jianshu.com/p/359eca45ddd4
来源：简书
著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。';
                break;
            case 3:
                $video = '2019年PHP7.4发布，给PHP在预加载方面与性能在速度有很大的提升，PHP预加载的实现使得我们可以用PHP文件加载到内存中来应对后续的请求，所以优点当然是提升PHP性能，相反会相对消耗机器内存性能。

PHP7.4除了提供预加载功能，还是提供更多扩展类，以应对外部函数接口。

2019在各大网站提供的接口包括api或SDK中，PHP的并没有减少反而不断增加，说明PHP并没有被开发者放弃的预告。前不久有调查网站公布web开发领域中，有79%的网站编程还是选择PHP作为主要开发语言，而java作为第二备选。


2019年PHP开发框架也不断为开发者提供更成熟更便捷更实用功能或工具，比如WordPress是个人博客系统逐步改变成一个完美的内容管理系统（CMS）；laravel框架，目前来说是PHP开发最经典、最成功的框架系统之一，最大的亮点应该是完全支持composer包管理工具，laravel因为是基础组件式框架，所以在开发中我们感觉比较臃肿，但是在结合最新PHP7.4，性能依然有不错的提升。

记得几年前就有一些开发者开始炒作PHP被其他编程语言代替或快要结束PHP编程语言的时代，甚至还有些初学者说，现在都什么年代了还在学习PHP开发，PHP在编程界已经不再重要了等等说法。网上也时不时出现关于PHP与其他编程语言对比的文章，但是我们现在回顾2019年PHP的发展，不管是PHP本身改进还是PHP框架都在不断改进，而且没有并且没有进一步下降，而是PHP不断成长，更是丰收的一年。';
                break;
            case 4:
                $video = '对于初学者来说，学习Java语言首先要从理解Java语言的各种抽象开始，其中类和对象是首先应该掌握的概念，掌握了类和对象之后，再理解封装、继承和多态这些概念的时候会更容易一些。理解抽象本身具有一定的难度，对于没有编程语言基础的人来说更是如此，而要想更好地了解这些抽象，应该通过各种实验来建立画面感。按照历史经验来看，Java语言的初期学习难度是比较大的，后期的学习难度相对会比较低。所以学习Java编程，一定要坚持。

Java语言本身是纯粹的面向对象编程语言，而且语法规则比较严谨，这样做的好处是保证了java语言的运行效率和程序可读性（规范性），但是坏处是初学者需要记住很多规则，只有多用才能逐渐熟悉这些规则。为了提高初学者编写代码的规范性，java初学者还需要学习一系列编程模式，所以在掌握了基本的Java语法之后，紧接着就需要学习一系列Java模式。

学习Java语言还需要学习一系列开发框架，不同的开发框架有不同的应用场景，会解决不同的问题，目前应该重点学习一下Spring框架，经过多年的发展，目前Spring系列框架已经比较成熟了，可以说为开发者提供了“一站式解决方案”。

最后，学习java一定要注重实践，所以在学习完基本的java框架之后，最好在实习岗位上锻炼一下。

我从事互联网行业多年，目前也在带计算机专业的研究生，主要的研究方向集中在大数据和人工智能领域，我会陆续写一些关于互联网技术方面的文章，感兴趣的朋友可以关注我，相信一定会有所收获。

如果有互联网、大数据、人工智能等方面的问题，或者是考研方面的问题，都可以在评论区留言，或者私信我！';
                break;
            case 5:
                $video = '上一篇文章当中，我们了解了网页的根本基石—超文本标记语言。

超文本标记语言自出现至今，从HTML1.0发展至现在的HTML5.0。

在这个信息时代，很多知识都不是停滞的，不变的，这些知识随着时代的发展也在一直在更新。

既然HTML已经更新到了5.0版本。那我们就来详细的了解一下HTML5.0是如何出现的，


HTML5.0的出现

HTML5是HTML的最新版本，由万维网联盟（W3C）于2014年10月完成标准制定。目标是取代1999年所制定的HTML 4.01标准，使得网页标准能在互联网应用迅速发展的时候达到符合当时的网络需求。


一般在说起HTML5时，实际指的是包括HTML、CSS和JavaScript在内的一套技术组件合集。

HTML5旨在减少网页浏览器提供丰富的网络应用服务的同时对插件的依赖（也就是在摆脱各种插件的同时并提供与之相同的服务），例如：Adobe Flash。HTML5的网页不需要它就可以提供相应的服务。


在上一篇文章当中，学记只是粗浅地描述了一下 HTML5 在何时出现。为此，在这篇文章当中学记会详细讲述它是如何出现的。

网页超文本技术工作小组（WHATWG）于2004年开始制定新标准。当时，HTML 4.01自2000年以来从未更新，因为万维网联盟（W3C）正在将未来的发展重点放在XHTML2.0(可扩展超文本标记语言，是一种标记语言，表现方式与超文本标记语言（HTML）类似，不过语法上更加严格)。


2009年，W3C决定结束XHTML 2.0的开发工作。W3C与WHATWG决定转向合作开始开发HTML5。

2004年6月，Mozilla基金会和Opera软件(欧朋）公司在万维网联盟（W3C）所主办的研讨会上提出了一份立场文件，其重点是开发与现有浏览器向后兼容的技术。研讨会最后以—8票赞成，14票反对—否决继续对HTML的开发工作。这引起一些人的不满。


这里有一个问题，学记在网上搜集资料时，并未发现是谁阻挡HTML5的开发，只是说在研讨会上有人投了反对票，不想开发新的HTML标准。

上图中的Opera公司，是浏览器行业中的一个翘楚。

现在浏览器上的很多新功能都是由他们首先开发出来的，像标签式浏览，鼠标手势，共享书签，同步传输数据。这些功能都是有Opera 公司率先使用的。

其实，阻挡HTML新标准出现的人，都不用猜，在2004年，谁占据了大量的浏览器市场份额，谁就最有可能是阻挠它出现的人。

很多公司，在占据市场份额之后，就不想向前开拓了，只想坐在功劳簿上吃老本，每当有开拓者想要向前进取，它们就会变成开拓进取的阻力。

这种公司，以前会有，以后也会有。不过，在历史的车轮面前不过是灰尘而已。

在研讨会之后，成立了网页超文本技术工作小组（WHATWG），以根据该目标开始工作，并宣布了第二个草案Web Applications 1.0。后来这两种规范合并形成HTML5。2007年，此小组获得W3C接纳，并成立了新的HTML工作团队。2008年1月22日，第一份公开工作草案发布。

尽管HTML5已经在网络开发人员中非常出名了，但是它成为主流媒体的一个话题是在2010年的4月，当时苹果公司的CEO乔布斯发表一篇题为“对Flash的思考”的文章，指出随着HTML5的发展，观看影片或其它内容时，Adobe Flash将不再是必须的。这引发了开发人员间的争论，HTML5虽然提供了加强的功能，但开发人员必须考虑到不同浏览器对标准不同部分的支持程度的不同，以及HTML5和Flash间的功能差异。


2014年10月28日，W3C正式发布HTML 5.0推荐标准。

如果想学习更多科技知识，可以点击关注。

如果对文章中的内容有什么困惑的地方，可以在评论区提出自己的问题，学记同大家一起交流，解决各种问题，一起进步。';
                break;
            case 6:
                $video = "Swoole 介绍

1.swoole提供了PHP语言的异步多线程服务器，异步TCP/UDP网络客户端，异步MySQL，异步Redis， 数据库连接池，AsyncTask，消息队列，毫秒定时器，异步文件读写，异步DNS查询。 Swoole还内置了Http/WebSocket服务器端/客户端、Http2.0服务器端。

2.Swoole可以广泛应用于互联网、移动通信、企业软件、网络游戏、物联网、车联网、智能家庭等领域。 使用PHP+Swoole作为网络通信框架， 可以使企业IT研发团队的效率大大提升，更加专注于开发创新产品。

3.Swoole底层内置了异步非阻塞、多线程的网络IO服务器。PHP程序员仅需处理事件回调即可，无需关心底层。与Nginx/Tornado/Node.js等全异步的框架不同，Swoole既支持全异步，也支持同步。

Swoole 如何处理高并发

①对Reactor模型介绍我们都知道IO复用异步非阻塞程序使用的是经典的Reactor模型，Reactor就是反应堆的意思，也就是说它本身不处理任何数据收发。只是可以监视一个socket(比如管道、eventfd、信号)句柄的事件变化。Reactor只作为一个事件发生器，实际对socket句柄的操作，如connect/accept、send/recv、close等都是在callback中完成的。看看下面图片就可以了解到。


②swoole的架构咱们再来看看swoole的架构，我们也可以从以下借鉴的图片可以看出，swoole采用的架构模式：多线程Reactor+多进程Worker，因为reactor是基于epoll的，所以不难看出每个reactor，它可以用来处理无数个连接请求。 如此，swoole就轻松的实现了高并发的处理。这里对高并发还不清楚的话，请自行网上看看教程，这里就不多做解释了。


Swoole的处理连接流程图如下：


当请求到达时，Swoole是这样处理的：


Swoole 如何实现异步I/O

基于上面的Swoole结构图，我们可以知道：Swoole的worker进程有2种类型：一种是普通的worker进程，一种是task worker进程。这两种类型分别用来处理什么呢？

worker进程：用来处理普通的耗时不是太长的请求
task worker进程：用来处理耗时较长的请求，比如数据库的I/O操作
我们再以异步MySQL举例，不难看出通过worker、task worker结合的方式，我们就实现了异步I/O。

";
                break;
            case 7:
                $video = '前端开发中，当需要做一个网站时，第一步便是UI设计师设计页面图片，然后是前端工程师将UI设计的图片用代码做出真实的页面，并且一定要精准到像素级别，比如padding多少像素，margin多少等等都要求非常精确，其次就是后端开发人员将网站逻辑实现。


今天来说说前端工程师所要做的，如何将页面搭建出来，对于新手来说，一个合理的步骤将会带来全新的认识。

说说我的经验，一般自己一个人做网站的时候，前端和后端都要自己解决，也没人给你设计页面，所以做的时候都是从网上找一个和自己需求差不多的网站，对照着人家的样式自己做页面。


做的时候用sublime或者webstorm，它们提供快速代码生成功能特别好，标签也能快速生成，

比如做导航条的时候，需要用到无序列表，一般来说都是ul中有好多个li，用sublime就可以这样写：ul>li*10


然后按tab键就能够生成一个包含了十个li的ul标签，特别方便，还支持这样的：ul>li*10>a

这样生成的10个li中每一个li又都包含一个a标签，所以特别方便。

最关键的，写页面的时候用sublime快速将html写出来，写html的时候css不用问，只要你心中能够知道这些html标签能够实现什么样的效果就行了。最后写完了在一点点写css，写的时候就像一点点盖房子一样，特别有成就感。';
                break;
            default:
                $video = 'Java现在已经发展到了Java13了（正式版本），相信很多朋友还对各个版本还不是很熟悉，这里面专门把Java9到Java13各个版本的一些新特性做了一些详细讲解。我在网上也找了很多，但基本都是官方文档的CV，没有任何代码演示，而且官方的示例代码也不是很好找得到，官方API目前还是Java10，官方文档真是坑啊。所以我在这里专门写了一篇文章，主要针对平时开发与有关的功能Java9到Java13各个版本都做了代码详细讲解。

【PS】：这个季节太冷了，南方湿冷，我的手都生冻疮了，看在年前最后几天了，没办法，我最后选择去网吧花了几天时间，网费都花了好几百块，为了打造这篇干货不惜下血本啊。终于奋战几天写出来了这篇文章。每一个语法细节都经过实例演示过的，我特意把每个版本的Open JDK都下载了一遍，体验里面的细节差距和新特性。

希望大家点赞，评论和收藏三连，也不负我的一片苦心，谢谢大家了。

想获得更多干货，欢迎大家多多关注我的博客。本文为作者AWeiLoveAndroid原创，未经授权，严禁转载。


文章目录

一、Java 9
Java 9 集合工厂方法
REPL (JShell)
接口支持私有方法和私有静态方法：
改进的 Stream API和Optional 类
改进的 CompletableFuture API
异常处理机制改进try-with-resources
改进的 @Deprecated 注解
钻石操作符(Diamond Operator“”)
Unicode 7.0扩展支持：
二、Java 10
var 局部变量类型推断
支持Unicode 8.0。
三、Java 11
局部变量的语法lambda参数
启动单文件源代码程序
四、Java 12
对 switch 语句进行扩展：
五、Java 13
switch表达式预览版
Text Blocks预览版（文字块）

一、Java 9

【注：】Java9的更新是最多的，这个需要特别注意学一下。

Java 9 集合工厂方法

示例：

public static void main(String[] args) { Set set = Set.of("set1", "set2", "set3"); // set: [set1, set3, set2] System.out.println("set: " + set); Map maps1 = Map.of( "map1","Apple", "map2","Orange","map3","Banana", "map4","cherry"); // maps1: {map3=Banana, map2=Orange, map1=Apple, map4=cherry} System.out.println("maps1: " + maps1); Map maps2 = Map.ofEntries ( new AbstractMap.SimpleEntry("map1","Apple"), new AbstractMap.SimpleEntry("map2","Orange"), new AbstractMap.SimpleEntry("map3","Banana"), new AbstractMap.SimpleEntry("map4","cherry"), new AbstractMap.SimpleEntry("map5","Apple"), new AbstractMap.SimpleEntry("map6","Orange"), new AbstractMap.SimpleEntry("map7","Banana"), new AbstractMap.SimpleEntry("map8","cherry"), new AbstractMap.SimpleEntry("map9","Apple"), new AbstractMap.SimpleEntry("map10","Orange"), new AbstractMap.SimpleEntry("map11","Banana"), new AbstractMap.SimpleEntry("map12","cherry") ); // maps2: {map3=Banana, map2=Orange, map1=Apple, map12=cherry, map11=Banana, map10=Orange, // map9=Apple, map8=cherry, map7=Banana, map6=Orange, map5=Apple, map4=cherry} System.out.println("maps2: " + maps2);}Java9以前的做法：

List list = new ArrayList();list.add("A");list.add("B");list.add("C");Set set = new HashSet();set.add("A");set.add("B");set.add("C");Map map = new HashMap();map.put("A","Apple");map.put("B","Boy");map.put("C","Cat");Java9可以直接输出集合的内容，在此之前必须遍历集合才能全部获取里面的元素。这是一个很大的改进。

Java 9 List，Set 和 Map 接口中，新增静态工厂方法可以创建这些集合的不可变实例。

Java 9 中，可以使用以下方法创建 List，Set 和 Map 的集合对象。重载方法有很多，示例如下：

static List of()static List of(E e1)static List of(E e1, E e2)static List of(E e1, E e2, E e3)static List of(E e1, E e2, E e3, E e4)static List of(E e1, E e2, E e3, E e4, E e5)static List of(E e1, E e2, E e3, E e4, E e5, E e6)static List of(E e1, E e2, E e3, E e4, E e5, E e6, E e7)static List of(E e1, E e2, E e3, E e4, E e5, E e6, E e7, E e8)static List of(E e1, E e2, E e3, E e4, E e5, E e6, E e7, E e8, E e9)static List of(E e1, E e2, E e3, E e4, E e5, E e6, E e7, E e8, E e9, E e10)static List of(E... elements)static Set of()static Set of(E e1)static Set of(E e1, E e2)static Set of(E e1, E e2, E e3)static Set of(E e1, E e2, E e3, E e4)static Set of(E e1, E e2, E e3, E e4, E e5)static Set of(E e1, E e2, E e3, E e4, E e5, E e6)static Set of(E e1, E e2, E e3, E e4, E e5, E e6, E e7)static Set of(E e1, E e2, E e3, E e4, E e5, E e6, E e7, E e8)static Set of(E e1, E e2, E e3, E e4, E e5, E e6, E e7, E e8, E e9)static Set of(E e1, E e2, E e3, E e4, E e5, E e6, E e7, E e8, E e9, E e10)static Set of(E... elements)static Map of() static Map of(K k1, V v1)static Map of(K k1, V v1, K k2, V v2)static Map of(K k1, V v1, K k2, V v2, K k3, V v3)static Map of(K k1, V v1, K k2, V v2, K k3, V v3, K k4, V v4)static Map of(K k1, V v1, K k2, V v2, K k3, V v3, K k4, V v4, K k5, V v5)static Map of(K k1, V v1, K k2, V v2, K k3, V v3, K k4, V v4, K k5, V v5, K k6, V v6)static Map of(K k1, V v1, K k2, V v2, K k3, V v3, K k4, V v4, K k5, V v5, K k6, V v6, K k7, V v7)static Map of(K k1, V v1, K k2, V v2, K k3, V v3, K k4, V v4, K k5, V v5, K k6, V v6, K k7, V v7, K k8, V v8)static Map of(K k1, V v1, K k2, V v2, K k3, V v3, K k4, V v4, K k5, V v5, K k6, V v6, K k7, V v7, K k8, V v8, K k9, V v9)static Map of(K k1, V v1, K k2, V v2, K k3, V v3, K k4, V v4, K k5, V v5, K k6, V v6, K k7, V v7, K k8, V v8, K k9, V v9, K k10, V v10)static Map ofEntries(Entry extends K, ? extends V>... entries)List ，Set 和Map 接口, of(…) 方法重载了 0 ~ 10 个参数的不同方法 。Map 接口如果超过 10 个参数, 可以使用 ofEntries(…) 方法。

REPL (JShell)

REPL(Read Eval Print Loop)意为交互式的编程环境。JShell 是 Java 9 新增的一个交互式的编程环境工具。它允许你无需使用类或者方法包装来执行 Java 语句。它与 Python 的解释器类似，可以直接输入表达式并查看其执行结果。

例如：

输入“jshell”打开jshell命令窗口：


输入“/help”查看帮助信息：


进行运算，创建和使用函数，以及退出：


接口支持私有方法和私有静态方法：

下图是Java8和java9的接口的变化的对比：


示例如下：

interface Test{ String fields = "interface field"; public abstract void abstractMethods(); default void defaultMethods() { System.out.println("default Method"); staticMethods(); privateMethods(); privateStaticMethods(); } static void staticMethods() { System.out.println("static Method"); } private void privateMethods() { System.out.println("private Method"); } private static void privateStaticMethods() { System.out.println("private Static Method"); } }接口实现类：

public class TestImpl implements Test{ @Override public void abstractMethods() { System.out.println("abstract Method"); } }测试类：

public class Demo{ public static void main(String[] args) { TestImpl testImpl = new TestImpl(); System.out.println(testImpl.fields); testImpl.abstractMethods(); testImpl.defaultMethods(); }}测试结果：

interface fieldabstract Methoddefault Methodstatic Methodprivate Methodprivate Static Method改进的 Stream API和Optional 类

Java 9 改进的 Stream API ，为 Stream 新增了几个方法：dropWhile、takeWhile、ofNullable，为 iterate 方法新增了一个重载方法，使流处理更容易。

Optional 类在Java8中引入，它的引入很好的解决空指针异常，在 java 9 中, 添加了stream()，ifPresentOrElse()和or()三个方法来改进它的功能。

示例如下：

Stream.of("a","b","c","","e","f").takeWhile(s->!s.isEmpty()) .forEach(System.out::print);System.out.println();Stream.of("10","20","30","","40","50").dropWhile(s-> !s.isEmpty()) .forEach(System.out::print);System.out.println();IntStream.iterate(3, x -> x x+ 3).forEach(System.out::print);System.out.println();System.out.println(Stream.ofNullable(100).count());System.out.println(Stream.ofNullable(null).count());结果：

// abc// 4050// 369// 1// 0public static void main(String[] args) { // stream()用法： List> list = Arrays.asList ( Optional.of("data1"), Optional.empty(), Optional.of("data2"), Optional.empty(), Optional.of("data3")); List result = list.stream() .flatMap(Optional::stream) .collect(Collectors.toList()); // 结果 [data1, data2, data3] System.out.println(result); // ifPresentOrElse使用： Optional optional = Optional.of("datas"); // 结果 Value: datas optional.ifPresentOrElse( x -> System.out.println("Value: " + x),() -> System.out.println("No data found")); optional = Optional.empty(); // 结果 No data found optional.ifPresentOrElse( x -> System.out.println("Value: " + x),() -> System.out.println("No data found")); Optional optional1 = Optional.of("datas"); Supplier> nullData = () -> Optional.of("No data found"); optional1 = optional1.or(nullData); // Value: datas optional1.ifPresent( x -> System.out.println("Value: " + x)); optional1 = Optional.empty(); optional1 = optional1.or(nullData); // Value: No data found optional1.ifPresent( x -> System.out.println("Value: " + x));}改进的 CompletableFuture API

支持 delays 和 timeouts，提升了对子类化的支持，新的工厂方法：

public CompletableFuture completeOnTimeout(T value, long timeout, TimeUnit unit)：在timeout（单位在java.util.concurrent.Timeunits units中，比如 MILLISECONDS ）前以给定的 value 完成这个 CompletableFutrue。返回这个 CompletableFutrue。

public CompletableFuture orTimeout(long timeout, TimeUnit unit)：如果没有在给定的 timeout 内完成，就以 java.util.concurrent.TimeoutException 完成这个 CompletableFutrue，并返回这个 CompletableFutrue。

public CompletableFuture newIncompleteFuture()：使得CompletableFuture可以被更简单的继承

CompletionStage completedStage(U value)：返回一个新的以指定 value 完成的CompletionStage ，并且只支持 CompletionStage 里的接口。

CompletionStage failedStage(Throwable ex)：返回一个新的以指定异常完成的CompletionStage ，并且只支持 CompletionStage 里的接口。

异常处理机制改进try-with-resources

try-with-resources 声明在 JDK 9 已得到改进。如果你已经有一个资源是 final 或等效于 final 变量,您可以在 try-with-resources 语句中使用该变量，而无需在 try-with-resources 语句中声明一个新变量。

示例如下：

public static void main(String[] args) throws IOException { System.out.println(readData("test"));// 结果：test}static String readData(String message) throws IOException { Reader inputString = new StringReader(message); BufferedReader br = new BufferedReader(inputString); // Java8处理方式： // try (BufferedReader br1 = br) { // return br1.readLine(); // } // Java9处理方式： try (br) { return br.readLine(); }}改进的 @Deprecated 注解

Java 9 中注解增加了两个新元素：since和forRemoval。since: 元素指定已注解的API元素已被弃用的版本。forRemoval: 元素表示注解的 API 元素在将来的版本中被删除，应该迁移 API。示例如下：

@Deprecated(since = "1.9", forRemoval = true)class Test{}钻石操作符(Diamond Operator“”)

在 java 9 中， “”可以与匿名的内部类一起使用，从而提高代码的可读性。

示例：

public class Test { public static void main(String[] args) { Handler intHandler = new Handler(1) { @Override public void handle() { System.out.println(content); } }; intHandler.handle(); Handler extends Number> intHandler1 = new Handler(2) { @Override public void handle() { System.out.println(content); } }; intHandler1.handle(); Handler> handler = new Handler("test") { @Override public void handle() { System.out.println(content); } }; handler.handle(); } } abstract class Handler { public T content; public Handler(T content) { this.content = content; } abstract void handle();}在java8中，上例中的 newHandler后面的里面必须带有泛型类型。Java9就不需要了。

Unicode 7.0扩展支持：

从Java SE 9，升级现有平台的API，支持7.0版本的Unicode标准，主要在以下类中：

java.lang.Character和java.lang.Stringjava.text包中的Bidi，BreakIterator和Normalizer此次升级将包括改善双向行为，从而可以更好地显示Unicode 6.3中引入的阿拉伯语和希伯来语等文本。 Unicode 7.0本身将添加大约三千个字符和二十多个脚本。

更多详情请查看：https://openjdk.java.net/projects/jdk9/

二、Java 10

这里重点看我们开发者能够直接体验到的一些功能：

var 局部变量类型推断

示例：

var list = new ArrayList(); // 代表 ArrayListvar stream = list.stream(); // 代表 Stream这种处理将仅限于带有初始值设定项的局部变量，增强的for循环中的索引以及在传统的for循环中声明的局部变量。它不适用于方法形式，构造函数形式，方法返回类型，字段，catch形式或任何其他类型的变量声明。

支持Unicode 8.0。

增强了java.util.Locale和相关的API，以实现BCP 47语言标签的其他Unicode扩展。

此次针对BCP 47语言标签扩展包括：

cu （货币类型）
fw （一周的第一天）
rg （区域覆盖）
tz （时区）
具体API变更有：

java.text.DateFormat::get*Instance将根据扩展名返回实例ca，rg和/或tz
java.text.DateFormatSymbols::getInstance 将根据扩展名返回实例 rg
java.text.DecimalFormatSymbols::getInstance 将根据扩展名返回实例 rg
java.text.NumberFormat::get*Instance将根据扩展名nu和/或返回实例rg
java.time.format.DateTimeFormatter::localizedBy将返回DateTimeFormatter基于扩展情况下ca，rg和/或tz
java.time.format.DateTimeFormatterBuilder::getLocalizedDateTimePattern将根据rg扩展名返回模式字符串。
java.time.format.DecimalStyle::of将DecimalStyle根据扩展名返回实例nu，和/或rg
java.time.temporal.WeekFields::of将WeekFields根据扩展名fw和/或返回实例rg
java.util.Calendar::{getFirstDayOfWeek,getMinimalDaysInWeek}将根据扩展名fw和/或返回值rg
java.util.Currency::getInstance将Currency根据扩展名cu和/或返回实例rg
java.util.Locale::getDisplayName 将返回一个字符串，其中包括这些U扩展名的显示名称
java.util.spi.LocaleNameProvider 这些U扩展的键和类型将具有新的SPI
其他特性都是有关垃圾回收，编译器，证书，以及命令工具等有关的，这里就不列举了。

更多详情请查看：https://openjdk.java.net/projects/jdk/10/

三、Java 11

局部变量的语法lambda参数

Java11中的lambda表达式可以为隐式类型，其中类型的形式参数都可以被推断出。对于隐式类型的lambda表达式的形式参数，允许使用保留的类型名称var，以便：(var x, var y) -> x.process(y)等效于：

(x, y) -> x.process(y) // 这样的对的(var x, int y) -> x.process(y) // 这样就会报错其他的lambda用法和Java8里的lambda用法一样。

启动单文件源代码程序

增强java启动器以运行作为Java源代码的单个文件提供的程序，包括通过“ shebang”文件和相关技术从脚本内部使用该程序。

从JDK 10开始，java启动器以三种模式运行：启动类文件，启动JAR文件的main类或启动模块的main类。在这里，我们添加了新的第四种模式：启动在源文件中声明的类。

如果“类名”标识具有.java扩展名的现有文件，则选择源文件模式，并编译和运行该文件。该–source选项可用于指定源代码的源版本。

如果文件没有.java扩展名，则–source必须使用该选项来强制源文件模式。例如当源文件是要执行的“脚本”并且源文件的名称不遵循Java源文件的常规命名约定时。

更多详情请查看：https://openjdk.java.net/projects/jdk/11/

四、Java 12

对 switch 语句进行扩展：

扩展switch语句，以便可以将其用作语句或表达式，并且两种形式都可以使用“传统”或“简化”作用域并控制流的行为。这些变化将简化日常编码在switch中。这是JDK 12中的预览功能。

请注意：此JEP已被JDK 13的JEP 354取代。

普通写法：

switch (day) { case MONDAY: case FRIDAY: case SUNDAY: System.out.println(6); break; case TUESDAY: System.out.println(7); break; case THURSDAY: case SATURDAY: System.out.println(8); break; case WEDNESDAY: System.out.println(9); break;}现在引入一种新的switch标签形式，写为“case L ->”，表示如果匹配标签，则只执行标签右边的代码。例如，现在可以编写以前的代码：

switch (day) { case MONDAY, FRIDAY, SUNDAY -> System.out.println(6); case TUESDAY -> System.out.println(7); case THURSDAY, SATURDAY -> System.out.println(8); case WEDNESDAY -> System.out.println(9);}再比如局部变量，普通写法是这样的：

int numLetters;switch (day) { case MONDAY: case FRIDAY: case SUNDAY: numLetters = 6; break; case TUESDAY: numLetters = 7; break; case THURSDAY: case SATURDAY: numLetters = 8; break; case WEDNESDAY: numLetters = 9; break; default: throw new IllegalStateException("Wat: " + day);}现在的写法是这样的：

int numLetters = switch (day) { case MONDAY, FRIDAY, SUNDAY -> 6; case TUESDAY -> 7; case THURSDAY, SATURDAY -> 8; case WEDNESDAY -> 9;};更多详情请查看：https://openjdk.java.net/projects/jdk/12/

五、Java 13

switch表达式预览版

JDK 13中新增 switch 表达式beta 版本，这是对Java12 switch表达式功能的增强版本，并且Java13版本的switch表达式的更新可以用于生产环境中。switch 表达式扩展了 switch 语句，使其不仅可以作为语句（statement），还可以作为表达式（expression），并且两种写法都可以使用传统的 switch 语法。

除了Java12的用法之外，Java13的更新引入一个新的关键字yield。大多数switch表达式在“case L ->”开关标签的右侧都有一个表达式。如果需要一个完整的块，需要使用yield语句来产生一个值，该值是封闭switch表达式的值。

示例：

int j = switch (day) { case MONDAY -> 0; case TUESDAY -> 1; default -> { int k = day.toString().length(); int result = f(k); yield result; }};上例也可以使用传统的switch语句：

int result = switch (s) { case "Foo": yield 1; case "Bar": yield 2; default: System.out.println("Neither Foo nor Bar, hmmm..."); yield 0;};switch表达的情况必须详细;对于所有可能的值，必须有一个匹配的switch标签。（显然，switch声明并非必须详细。）这通常意味着需要一个default子句。但是enum switch对于覆盖所有已知常量的表达式，default编译器会插入一个子句以指示该enum定义在编译时和运行时之间已更改。依靠这种隐式default子句的插入可以使代码更健壮。现在，当重新编译代码时，编译器将检查所有情况是否得到明确处理。

此外，switch表达式必须以一个值正常完成，或者必须通过引发异常来突然完成。这有许多后果。首先，编译器会检查每个开关标签是否匹配，然后产生一个值。

示例：

int i = switch (day) { case MONDAY -> { System.out.println("Monday"); // ERROR! Block doesn\'t contain a yield statement } default -> 1;};i = switch (day) { case MONDAY, TUESDAY, WEDNESDAY: yield 0; default: System.out.println("Second half of the week"); // ERROR! Group doesn\'t contain a yield statement};另一种后果是，控制语句，break，yield，return和continue，无法通过跳switch表达式，示例：

z: for (int i = 0; i Text Blocks预览版（文字块）

简单地说就是：可以跨多行显示字符串并且不对转义字符进行转义。目标是编写Java程序的任务，同时避免了常见情况下的转义序列，增强Java程序中表示用非Java语言编写的代码的字符串的可读性。

在Java中，在字符串文字中嵌入HTML，XML，SQL或JSON片段"…"通常需要先进行转义和串联的大量编辑工作，然后才能编译包含该代码块的代码。该代码快通常难以阅读且难以维护。但是Java13的代码块功能会更直观地表示字符串，而且可以跨越多行，而且不会出现转义的视觉混乱，这将提高Java程序的可读性和可写性。本质上是二维文本块，而不是一维字符序列。

基本语法形式：

"""line 1line 2line 3"""等效于："line 1\nline 2\nline 3\n"

或字符串文字的串联：

"line 1\n" +"line 2\n" +"line 3\n"如果在字符串的末尾不需要行终止符，则可以将结束定界符放在内容的最后一行。例如，文本块：

"""line 1line 2line 3"""具体使用：

字符串里面写HTML代码，

Java13之前写法：

String html = "\n" + " \n" + " Hello, world

\n" + " \n" + "\n";Java13写法：

String html = """ Hello, world

""";再比如SQL示例：

Java13之前写法：

String query = "SELECT `EMP_ID`, `LAST_NAME` FROM `EMPLOYEE_TB`\n" + "WHERE `CITY` = \'INDIANAPOLIS\'\n" + "ORDER BY `EMP_ID`, `LAST_NAME`;\n";Java13写法：

String query = """ SELECT `EMP_ID`, `LAST_NAME` FROM `EMPLOYEE_TB` WHERE `CITY` = \'INDIANAPOLIS\' ORDER BY `EMP_ID`, `LAST_NAME`; """; 再比如：

Java13之前写法：

ScriptEngine engine = new ScriptEngineManager().getEngineByName("js");Object obj = engine.eval("function hello() {\n" + " print(\'\"Hello, world\"\');\n" + "}\n" + "\n" + "hello();\n"); Java13写法：

ScriptEngine engine = new ScriptEngineManager().getEngineByName("js");Object obj = engine.eval(""" function hello() { print(\'"Hello, world"\'); } hello(); """);更多详情请查看：https://openjdk.java.net/projects/jdk/13/';
        }
        return $video;
    }

    public function tmp()
    {

        $a = 0;
        $up_data = [];
        for ($i = 1; $i <= 900; $i++) {
            $b = $a + 1;
            $a = 5 * $i;//5
            for ($j = $b; $j <= $a; $j++) {
                $data = [
                    'section_id' => $i,
                    'id' => $j,
                    'difficulty' => rand(1, 5)
                ];
                array_push($up_data, $data);
            }
        }
        $section = new \app\admin\model\Question();
        $res = $section->saveAll($up_data);
        if (!$res) throw new Exception('更新失败！');
    }

    public function tmpRandNick()
    {
        $nicheng_tou = array('快乐的', '冷静的', '醉熏的', '潇洒的', '糊涂的', '积极的', '冷酷的', '深情的', '粗暴的', '温柔的', '可爱的', '愉快的', '义气的', '认真的', '威武的', '帅气的', '传统的', '潇洒的', '漂亮的', '自然的', '专一的', '听话的', '昏睡的', '狂野的', '等待的', '搞怪的', '幽默的', '魁梧的', '活泼的', '开心的', '高兴的', '超帅的', '留胡子的', '坦率的', '直率的', '轻松的', '痴情的', '完美的', '精明的', '无聊的', '有魅力的', '丰富的', '繁荣的', '饱满的', '炙热的', '暴躁的', '碧蓝的', '俊逸的', '英勇的', '健忘的', '故意的', '无心的', '土豪的', '朴实的', '兴奋的', '幸福的', '淡定的', '不安的', '阔达的', '孤独的', '独特的', '疯狂的', '时尚的', '落后的', '风趣的', '忧伤的', '大胆的', '爱笑的', '矮小的', '健康的', '合适的', '玩命的', '沉默的', '斯文的', '香蕉', '苹果', '鲤鱼', '鳗鱼', '任性的', '细心的', '粗心的', '大意的', '甜甜的', '酷酷的', '健壮的', '英俊的', '霸气的', '阳光的', '默默的', '大力的', '孝顺的', '忧虑的', '着急的', '紧张的', '善良的', '凶狠的', '害怕的', '重要的', '危机的', '欢喜的', '欣慰的', '满意的', '跳跃的', '诚心的', '称心的', '如意的', '怡然的', '娇气的', '无奈的', '无语的', '激动的', '愤怒的', '美好的', '感动的', '激情的', '激昂的', '震动的', '虚拟的', '超级的', '寒冷的', '精明的', '明理的', '犹豫的', '忧郁的', '寂寞的', '奋斗的', '勤奋的', '现代的', '过时的', '稳重的', '热情的', '含蓄的', '开放的', '无辜的', '多情的', '纯真的', '拉长的', '热心的', '从容的', '体贴的', '风中的', '曾经的', '追寻的', '儒雅的', '优雅的', '开朗的', '外向的', '内向的', '清爽的', '文艺的', '长情的', '平常的', '单身的', '伶俐的', '高大的', '懦弱的', '柔弱的', '爱笑的', '乐观的', '耍酷的', '酷炫的', '神勇的', '年轻的', '唠叨的', '瘦瘦的', '无情的', '包容的', '顺心的', '畅快的', '舒适的', '靓丽的', '负责的', '背后的', '简单的', '谦让的', '彩色的', '缥缈的', '欢呼的', '生动的', '复杂的', '慈祥的', '仁爱的', '魔幻的', '虚幻的', '淡然的', '受伤的', '雪白的', '高高的', '糟糕的', '顺利的', '闪闪的', '羞涩的', '缓慢的', '迅速的', '优秀的', '聪明的', '含糊的', '俏皮的', '淡淡的', '坚强的', '平淡的', '欣喜的', '能干的', '灵巧的', '友好的', '机智的', '机灵的', '正直的', '谨慎的', '俭朴的', '殷勤的', '虚心的', '辛勤的', '自觉的', '无私的', '无限的', '踏实的', '老实的', '现实的', '可靠的', '务实的', '拼搏的', '个性的', '粗犷的', '活力的', '成就的', '勤劳的', '单纯的', '落寞的', '朴素的', '悲凉的', '忧心的', '洁净的', '清秀的', '自由的', '小巧的', '单薄的', '贪玩的', '刻苦的', '干净的', '壮观的', '和谐的', '文静的', '调皮的', '害羞的', '安详的', '自信的', '端庄的', '坚定的', '美满的', '舒心的', '温暖的', '专注的', '勤恳的', '美丽的', '腼腆的', '优美的', '甜美的', '甜蜜的', '整齐的', '动人的', '典雅的', '尊敬的', '舒服的', '妩媚的', '秀丽的', '喜悦的', '甜美的', '彪壮的', '强健的', '大方的', '俊秀的', '聪慧的', '迷人的', '陶醉的', '悦耳的', '动听的', '明亮的', '结实的', '魁梧的', '标致的', '清脆的', '敏感的', '光亮的', '大气的', '老迟到的', '知性的', '冷傲的', '呆萌的', '野性的', '隐形的', '笑点低的', '微笑的', '笨笨的', '难过的', '沉静的', '火星上的', '失眠的', '安静的', '纯情的', '要减肥的', '迷路的', '烂漫的', '哭泣的', '贤惠的', '苗条的', '温婉的', '发嗲的', '会撒娇的', '贪玩的', '执着的', '眯眯眼的', '花痴的', '想人陪的', '眼睛大的', '高贵的', '傲娇的', '心灵美的', '爱撒娇的', '细腻的', '天真的', '怕黑的', '感性的', '飘逸的', '怕孤独的', '忐忑的', '高挑的', '傻傻的', '冷艳的', '爱听歌的', '还单身的', '怕孤单的', '懵懂的');

        $nicheng_wei = array('嚓茶', '凉面', '便当', '毛豆', '花生', '可乐', '灯泡', '哈密瓜', '野狼', '背包', '眼神', '缘分', '雪碧', '人生', '牛排', '蚂蚁', '飞鸟', '灰狼', '斑马', '汉堡', '悟空', '巨人', '绿茶', '自行车', '保温杯', '大碗', '墨镜', '魔镜', '煎饼', '月饼', '月亮', '星星', '芝麻', '啤酒', '玫瑰', '大叔', '小伙', '哈密瓜，数据线', '太阳', '树叶', '芹菜', '黄蜂', '蜜粉', '蜜蜂', '信封', '西装', '外套', '裙子', '大象', '猫咪', '母鸡', '路灯', '蓝天', '白云', '星月', '彩虹', '微笑', '摩托', '板栗', '高山', '大地', '大树', '电灯胆', '砖头', '楼房', '水池', '鸡翅', '蜻蜓', '红牛', '咖啡', '机器猫', '枕头', '大船', '诺言', '钢笔', '刺猬', '天空', '飞机', '大炮', '冬天', '洋葱', '春天', '夏天', '秋天', '冬日', '航空', '毛衣', '豌豆', '黑米', '玉米', '眼睛', '老鼠', '白羊', '帅哥', '美女', '季节', '鲜花', '服饰', '裙子', '白开水', '秀发', '大山', '火车', '汽车', '歌曲', '舞蹈', '老师', '导师', '方盒', '大米', '麦片', '水杯', '水壶', '手套', '鞋子', '自行车', '鼠标', '手机', '电脑', '书本', '奇迹', '身影', '香烟', '夕阳', '台灯', '宝贝', '未来', '皮带', '钥匙', '心锁', '故事', '花瓣', '滑板', '画笔', '画板', '学姐', '店员', '电源', '饼干', '宝马', '过客', '大白', '时光', '石头', '钻石', '河马', '犀牛', '西牛', '绿草', '抽屉', '柜子', '往事', '寒风', '路人', '橘子', '耳机', '鸵鸟', '朋友', '苗条', '铅笔', '钢笔', '硬币', '热狗', '大侠', '御姐', '萝莉', '毛巾', '期待', '盼望', '白昼', '黑夜', '大门', '黑裤', '钢铁侠', '哑铃', '板凳', '枫叶', '荷花', '乌龟', '仙人掌', '衬衫', '大神', '草丛', '早晨', '心情', '茉莉', '流沙', '蜗牛', '战斗机', '冥王星', '猎豹', '棒球', '篮球', '乐曲', '电话', '网络', '世界', '中心', '鱼', '鸡', '狗', '老虎', '鸭子', '雨', '羽毛', '翅膀', '外套', '火', '丝袜', '书包', '钢笔', '冷风', '八宝粥', '烤鸡', '大雁', '音响', '招牌', '胡萝卜', '冰棍', '帽子', '菠萝', '蛋挞', '香水', '泥猴桃', '吐司', '溪流', '黄豆', '樱桃', '小鸽子', '小蝴蝶', '爆米花', '花卷', '小鸭子', '小海豚', '日记本', '小熊猫', '小懒猪', '小懒虫', '荔枝', '镜子', '曲奇', '金针菇', '小松鼠', '小虾米', '酒窝', '紫菜', '金鱼', '柚子', '果汁', '百褶裙', '项链', '帆布鞋', '火龙果', '奇异果', '煎蛋', '唇彩', '小土豆', '高跟鞋', '戒指', '雪糕', '睫毛', '铃铛', '手链', '香氛', '红酒', '月光', '酸奶', '银耳汤', '咖啡豆', '小蜜蜂', '小蚂蚁', '蜡烛', '棉花糖', '向日葵', '水蜜桃', '小蝴蝶', '小刺猬', '小丸子', '指甲油', '康乃馨', '糖豆', '薯片', '口红', '超短裙', '乌冬面', '冰淇淋', '棒棒糖', '长颈鹿', '豆芽', '发箍', '发卡', '发夹', '发带', '铃铛', '小馒头', '小笼包', '小甜瓜', '冬瓜', '香菇', '小兔子', '含羞草', '短靴', '睫毛膏', '小蘑菇', '跳跳糖', '小白菜', '草莓', '柠檬', '月饼', '百合', '纸鹤', '小天鹅', '云朵', '芒果', '面包', '海燕', '小猫咪', '龙猫', '唇膏', '鞋垫', '羊', '黑猫', '白猫', '万宝路', '金毛', '山水', '音响');

        $tou_num = rand(0, 331);

        $wei_num = rand(0, 325);

        $nicheng = $nicheng_tou[$tou_num] . $nicheng_wei[$wei_num];

        return $nicheng; //输出生成的昵称
    }

    public function createQuestion()
    {
        $choose = [
            [
                'id' => 'A',
                'info' => '汹猛洪涝，罕见冰雪，特大地震… … 当我们面对这一切的时候，不由生出多难兴邦的历史感慨。',
                'is_right' => true
            ],
            [
                'id' => 'B',
                'info' => '集电话、电脑、相机、信用卡等功能于一体，这款新型手机在生活中的作用被发挥得酣畅淋漓。',
                'is_right' => false
            ],
            [
                'id' => 'C',
                'info' => '现代社会信息量与时俱进，上网已成为追求时尚的当代中学生经常挂在嘴边的炙手可热的话题',
                'is_right' => true
            ],
            [
                'id' => 'D',
                'info' => '英国的一项科学研究显示，播放古典音乐能促使食客情不自禁地慷慨解囊，从而增加酒店收入。',
                'is_right' => false
            ],
            [
                'id' => 'E',
                'info' => '五月的西湖公园，姹紫嫣红，一片绚丽的景象。',
                'is_right' => true
            ],
            [
                'id' => 'F',
                'info' => '登高远眺。青山如屏，绿水如带，令人心旷神怡。',
                'is_right' => true
            ],
        ];
        $choose = json_encode($choose);
        $data = [
            'title' => '选出下列成语使用正确的一项是',
            'selected' => $choose,
            'answer' => 'A,E,F',
            'difficulty' => rand(1, 5),
            'video_url' => '',
            'question_type' => 1,
            'section_id' => 1,
            'answer_parsing' => '酣畅淋漓：形容非常畅快。
炙手可热：比喻权势大，气焰盛，使人不敢接近。
慷慨解囊：形容极其大方地在经济上帮助别人。
酣畅淋漓适用于人，本处应该为“淋漓尽致”；炙手可热形容坏人当道，是贬义词；慷慨解囊为拿钱送人，不能买。
',
            'num' => '5'
        ];
        Db::table('question')->insert($data);
    }
}