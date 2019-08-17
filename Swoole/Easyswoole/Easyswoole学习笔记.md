 ## 1. Easywoole入门
 
 #### 1.1 环境安装
 
 **题外**
 ```
  （1）vim ~/.bash_profile 添加环境变量：比如：alias php7=[php安装的bin路径]
  （2）搜索某个端口： netstat -anp | grep [你需要查询的端口号]
  （3）php里面抽象类的抽象方法都必须被子类实现
  （4）trait的用法，参考https://blog.csdn.net/ssfz123/article/details/79849289
 ```
 
 **安装**
 >composer create-project easyswoole/app easyswoole
 
 **启动**
 >php easyswoole start
 >php easyswoole start --d（以守护进程方式执行）
 >php easyswoole start --p-8888（动态设置端口）
 >php easyswoole restart
 
 **小技巧**
 - onRequest方法可以作为对方法的安全过滤来使用
 ```php
     base.controller.php
     /**
      * 权限控制
      * @param $action 方法名
      * @return bool|null
      */
     protected function onRequest($action):?bool
     {
         $safe_act = ['index','add','update'];//这里写允许请求的方法
         if(in_array($action,$safe_act)){
            return true;
         }else{
            return false;
         }  
     }
 
 ```
 - onException当请求报错的时候，会走到这个方法
 ```php
        /**
         * 代码错误和谐提示
         * @param \Throwable $throwable
         * @param $actionName
         * @throws \Throwable
         */
        public function onException(\Throwable $throwable, $actionName): void
        {
            $this->writeJson('500','请求不合法');
        }
 ```

 **数据库操作**
 >EasySwoole本身并不提供封装好的数据库操作与Model层，但我们强力推荐在项目中使用第三方开源库https://github.com/joshcam/PHP-MySQLi-Database-Class 
 作为数据操作类库，并构建自己的Model。
 
 这里使用php composer 来安装MysqliDb
 >composer require joshcam/mysqli-database-class:dev-master
 
 
 
 ## 2.性能测试
 
 - 工具apache bench,简称ab
 
 - 安装
 >yum -y install httpd-tools
 
 - 使用
 >(1)ab -n [总共请求数量] -c [并发数量] [测试域名]（例如：ab -n 1000 -c 100 https:www.lewaimai.com/）<br/>
 >参数说明：Requests per second:    47.30 [#/sec] (mean)[每秒执行的请求数，qps]
 
 ## 3. 消息队列
 
 #### 3.1 消息队列
 >流程图：生产者->Broker（消息处理中心（消息的存储，分发...））->消费者<br/>
 >常用消息队列：kafka(分布式，多语言),rabbitMQ,redis(本课程使用redis+swoole4.X)
 
 #### 3.2 redis
 
 - 安装
 安装redis4.0.9以及phpredis扩展
 
 - redis底层封装
 ```php
class Redis
{
    use Singleton;
    public $redis = "";

    private function __construct() {
        try {
            if(!extension_loaded('redis')){
                throw new \Exception('redis扩展异常',400);
            }
            $this->redis = new \Redis();
            //$redis_conf = Config::getInstance()->getConf('redis');
            $redis_conf = \Yaconf::get('redis');
            $result = $this->redis->connect($redis_conf['host'],$redis_conf['port'],$redis_conf['time_out']);
            if($result===false){
                throw new \Exception("redis连接失败",500);
            }
        } catch(\Exception $e) {
            if(!empty($e->getCode())){
                throw new \Exception($e->getMessage());
            }else{
                throw new \Exception("redis服务异常");
            }
        }
    }

    public function get($key) {
        if(empty($key)) {
            return '';
        }
        return $this->redis->get($key);
    }

    public function set($key, $value, $time = 0) {
        if(empty($key)) {
            return '';
        }
        if(is_array($value)) {
            $value = json_encode($value);
        }
        if(!$time) {
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key, $time, $value);
    }
    /**
     * 当类中不存在该方法时候，直接调用call 实现调用底层redis相关的方法
     */
    public function __call($name, $arguments) {

        ///var_dump(...$arguments);
        return $this->redis->$name(...$arguments);
    }

}

```
 - 配置文件
 **初始化框架的时候引入配置文件**
 ```php
public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
        //获得原先的config配置项,加载到新的配置项中
        self::loadConf(EASYSWOOLE_ROOT . '/Config');
    }

    //加载配置文件
    function loadConf($ConfPath)
    {
        $Conf  = Config::getInstance();
        $files = File::scanDirectory($ConfPath);
        foreach ($files['files'] as $file) {
            $data = require_once $file;
            $Conf->setConf(strtolower(basename($file, '.php')), (array)$data);
        }
    }
```
 **使用yaconf**
 >(1)安装： wget http://pecl.php.net/get/yaconf-1.0.7.tgz<br>
 >tar -zxvf yaconf-1.0.7.tgz<br>
 >phpize<br>
 >make -j<br>
 >make install<br>
 >在php.ini加入yaconf(extension=yaconf yaconf.directory=/data/wwwroot/easyswoole/ini)<br>
 >(2)配置文件定义<br>(host="127.0.0.1" port=6379 time_out=3)<br>
 >(3)使用<br>
 >\Yaconf::get('文件名')(得到的是一个数组)<br>
 
 #### 3.3 实现消息队列
 
 **在mainServerCreate中创建3个进程**
 ```php
 $allNum = 3;
 for ($i = 0 ;$i < $allNum;$i++){
     ServerManager::getInstance()->getSwooleServer()->addProcess((new ConsumerTest("consumer_{$i}"))->getProcess());
 }
 ```
 **消费者**
 ```php
 public function run($arg)
    {
        // TODO: Implement run() method.
        /*
         * 举例，消费redis中的队列数据
         * 定时500ms检测有没有任务，有的话就while死循环执行
         */
        $this->addTick(500,function (){
            if(!$this->isRun){
                $this->isRun = true;
                while (true){
                    try{
                        $task = Di::getInstance()->get('REDIS')->lPop('task_list');
                        var_dump($this->getProcessName());
                        if($task){
                            // do you task
                            var_dump($this->getProcessName().'--------->'.$task);
                            Logger::getInstance()->log($this->getProcessName().'----'.$task);
                        }else{
                            break;
                        }
                    }catch (\Throwable $throwable){
                        break;
                    }
                }
                $this->isRun = false;
            }
            //var_dump($this->getProcessName().' task run check');
        });
```
**生产者**
```php
//消息队列生产者
    public function pub()
    {
        $params = $this->request()->getRequestParam();
        Di::getInstance()->get("REDIS")->rPush('task_list',$params['f']);
    }
```

 #### 3.4 实现前后端分离
 
 **配置nginx**
 >9502端口直接指向webroot目录；<br>
 >php转发到swoole服务器
 ```bash
if (!-e $request_filename){
    proxy_pass http://127.0.0.1:9501;
}
```
 **功能流程图**
 
 ![image](https://github.com/jeremyke/PHPBlog/raw/master/Pictures/16570127747882.png)
 
 **技术流程图**
 
 ![image](https://github.com/jeremyke/PHPBlog/raw/master/Pictures/17860531256108.png)
 
 ## 4. 小视频上传
 
 #### 4.1 上传到本地服务器
 >这一章节主要看下作者对业务逻辑的封装以及php的反射机制.
 
 - upload.php
 ```php
<?php
public function file()
    {
        try{
            $request = $this->request();
            $files = $request->getSwooleRequest()->files;
            $types = array_keys($files);
            $type = $types[0];
            if(empty($type)) {
                return $this->writeJson(400, '上传文件不合法');
            }
            //$video_obj = new Video($request,$type);
            //$file = $video_obj->upload();
            $classObj = new ClassArr();
            $classStats = $classObj->uploadClassStat();
            $uploadObj = $classObj->initClass($type, $classStats, [$request, $type]);
            $file = $uploadObj->upload();
            if(empty($file)){
                throw new \Exception('上传失败');
            }
            $data = [
              'url' =>  $file,
            ];
            return $this->writeJson(200,"上传成功",$data);
        }catch (\Exception $e){
            return $this->writeJson(400,$e->getMessage(),[]);
        }
    }
 ```
 - 反射机制
  ```php
  <?php
  /**
       * 反射对应的类文件
       * @return array
       */
      public function uploadClassStat() {
          return [
              "image" => "\App\Lib\Upload\Image",
              "video" => "\App\Lib\Upload\Video",
          ];
      }
  
      /**
       * 反射写法
       * @param $type 反射类型
       * @param $supportedClass 反射类数组
       * @param array $params 参数
       * @param bool $needInstance 是否需要实例化
       * @return bool|object
       * @throws \ReflectionException
       */
      public function initClass($type, $supportedClass, $params = [], $needInstance = true) {
          if(!array_key_exists($type, $supportedClass)) {
              return false;
          }
  
          $className = $supportedClass[$type];
  
          return $needInstance ? (new \ReflectionClass($className))->newInstanceArgs($params) : $className;
      }
  ```
 - 上传文件基类
 ```php
<?php
namespace App\Lib\Upload;

use App\Lib\Utils;

class Base {

    /**
     * 上传文件的 file - key
     * @var string
     */
    public $type = "";

    public function __construct($request, $type = null) {
        $this->request = $request;
        if(empty($type)) {
            $files = $this->request->getSwooleRequest()->files;
            $types = array_keys($files);
            $this->type = $types[0];
        } else {
            $this->type = $type;
        }
    }


    //上传文件
    public function upload() {
        if($this->type != $this->fileType) {
            return false;
        }
        $videos = $this->request->getUploadedFile($this->type);
        $this->size = $videos->getSize();
        $this->checkSize();
        $fileName = $videos->getClientFileName();
        $this->clientMediaType = $videos->getClientMediaType();
        $this->checkMediaType();
        $file = $this->getFile($fileName);
        $flag = $videos->moveTo($file);
        if(!empty($flag)) {
            return $this->file;
        }

        return false;

    }

    public function getFile($fileName) {
        $pathinfo = pathinfo($fileName);
        $extension = $pathinfo['extension'];

        $dirname = "/".$this->type . "/". date("Y") . "/" . date("m");
        $dir = EASYSWOOLE_ROOT  . "/webroot" . $dirname;
        if(!is_dir($dir)) {
            mkdir($dir, 0777 , true);
        }

        $basename = "/" .Utils::getFileKey($fileName) . ".".$extension;

        $this->file = $dirname . $basename;
        return$dir  . $basename;

    }

    /**
     * 检查文件类型
     */
    public function checkMediaType() {
        $clientMediaType = explode("/", $this->clientMediaType);
        $clientMediaType = $clientMediaType[1] ?? "";
        if(empty($clientMediaType)) {
            throw new \Exception("上传{$this->type}文件不合法");
        }
        if(!in_array($clientMediaType, $this->fileExtTypes)) {
            throw new \Exception("上传{$this->type}文件不合法");
        }

        return true;
    }
    public function checkSize() {
        if(empty($this->size)) {
            return false;
        }

        // todo
        //
        //
    }
}
```
 - 数据验证（validate）
 >这部分主要是阅读文档和源码，以明确框架提供的验证方法以及如何自定义验证函数  
 ```php
 <?php
//数据校验
        $params = $this->request()->getRequestParam();
        Logger::getInstance()->log($this->logType . "add:" .json_encode($params));
        $valitor = new Validate();
        $valitor->addColumn('name', "视频名称错误")->required('视频名称不能为空')->lengthMin(2, '最小长度不小于2')->lengthMax(20, '最大长度不能大于20');
        $valitor->addColumn('url', "视频地址错误")->required('视频地址参数缺失')->notEmpty('视频地址不能为空');
        $valitor->addColumn('image', "图片地址错误")->required('图片地址参数缺失')->notEmpty('图片地址不能为空');
        $valitor->addColumn('content', "视频描述错误")->required('视频描述参数缺失')->notEmpty('视频描述不能为空');
        $valitor->addColumn('cat_id', "栏目ID错误")->required('栏目ID参数缺失')->notEmpty('栏目ID不能为空');
        $validata = $valitor->validate($params);
        if(!$validata) {
            //print_r($validata->getErrorList());
            return $this->writeJson(Status::CODE_BAD_REQUEST, $valitor->getError()->__toString());
        }

```
 #### 4.2 静态化API
 >在用户请求之前，预先生成静态化json数据，不用再实时请求mysql，直接将json渲染到页面。
 
 - easyswoole+crontab定时任务
 
 注入任务
 ```php
 <?php
public static function mainServerCreate(EventRegister $register)
    {
        //crontab任务计划
        Crontab::getInstance()->addTask(TestTask::setRule("*/1 * * * *"));

    }
```
任务规则
```php
<?php
/**
 * Description:
 * User: Jeremy.Ke
 * Time: 2019/8/1 17:32
 */
namespace App\Crontab;

use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use App\Lib\Cache\Video as videoCache;

class TestTask extends AbstractCronTask
{
    public static $rules= "*/1 * * * *";
    public static function getRule(): string
    {
        return self::$rules;
    }
    public static function setRule($rules)
    {
        self::$rules = $rules;
        return self::class;
    }

    public static function getTaskName(): string
    {
        // TODO: Implement getTaskName() method.
        // 定时任务名称
        return 'taskOne';
    }

    //消费任务
    static function run(\swoole_server $server, int $taskId, int $fromWorkerId,$flags=null)
    {
        $videoCache = new videoCache();
        $videoCache->setIndexVideo();
    }
}
```

 - swoole的timer实现
 
 注入任务
 ```php
 <?php
$register->add(EventRegister::onWorkerStart, function (\swoole_server $server, $workerId) {
            if ($workerId == 0) {
                Timer::getInstance()->loop(10 * 1000, function () {
                    $obj = new \App\Lib\Cache\Video();
                    $obj->setIndexVideo();
                });
            }
        });
```
消费端和上面一样。

 - 写入swoole_table
 >首先安装fast-cache插件
 ```shell
 composer require easyswoole/fast-cache
 ```
 
 服务注册：
 ```php
<?php
use EasySwoole\FastCache\Cache;
Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)->attachToServer(ServerManager::getInstance()->getSwooleServer());
```

 - 写入reids
 略。
 
 #### 4.3 异步任务(worker进程实现)
 ```php
<?php
use EasySwoole\EasySwoole\Swoole\Task\TaskManager;
use EasySwoole\Component\Di;
TaskManager::async(function () use($id){
    Di::getInstance()->get("REDIS")->zincrby(\Yaconf::get('redis.video_play_key'),1,$id);
});
```

 ## 5. elasticsearch
 
 #### 5.1 elasticsearch和head的配合使用
 
 - elasticsearch安装
 >移步我的elastic专栏（本实验是搭建els集群，所以我这里启动了2个els节点）
 
 - head安装
 >head是elasticsearch的可视化项目，搭建方法很多，这里我选择用chrome的一个插件elasticsearch-head非常省事
 
 - 创建索引
 >http://47.98.143.87:9502/my_video/
 ```json
{
  "mappings": {
    "properties": {
      "name": {
        "type": "text"
      },
      "cat_id": {
        "type": "integer"
      },
      "image": {
        "type": "text"
      },
      "url": {
        "type": "text"
      },
      "type": {
        "type": "byte"
      },
      "content": {
        "type": "text"
      },
      "uploader": {
        "type": "keyword"
      },
      "create_time": {
        "type": "integer"
      },
      "update_time": {
        "type": "integer"
      },
      "status": {
        "type": "byte"
      },
      "video_id": {
        "type": "keyword"
      }
    }
  }
}
```
结果如下，说明创建成功！！！
```json
{
"acknowledged": true,
"shards_acknowledged": true,
"index": "my_video"
}
```
>也可以创建模板，便于快速下次创建索引。

 - 创建一条elasticsearch数据
 
 **请求：**
 >[url]/[索引名]/[索引类型]/[id]/-put（例如：http://47.98.143.87:9502/my_video/_doc/1/）自定义id
 
 **请求参数：**
 ```json
{
   "name": "许嵩",
   "cat_id": 1,
   "image": "/user/upload/1.jpg",
   "url": "http://www.baidu.com",
   "uploader": "ace",
   "status": 1,
   "video_id": "sdfsdftesdt"
}

```
**成功返回：**
```json
{
   "_index": "my_video",
   "_type": "_doc",
   "_id": "1",
   "_version": 1,
   "result": "created",
   "_shards": {
   "total": 2,
   "successful": 2,
   "failed": 0
   },
   "_seq_no": 0,
   "_primary_term": 1
}
```
**请求：**
>[url]/[索引名]/[索引类型]/-post（例如：http://47.98.143.87:9502/my_video/_doc/）随机ID

 **请求参数：**
 ```json
{
       "name": "许嵩",
       "cat_id": 1,
       "image": "/user/upload/1.jpg",
       "url": "http://www.baidu.com",
       "uploader": "ace",
       "status": 1,
       "video_id": "sdfsdftesdt"
     }
```
**成功返回：**
```json
{
   "_index": "my_video",
   "_type": "_doc",
   "_id": "1",
   "_version": 1,
   "result": "created",
   "_shards": {
   "total": 2,
   "successful": 2,
   "failed": 0
   },
   "_seq_no": 0,
   "_primary_term": 1
}
```

- 文档查询
>英文单词可以直接基本查询，中文只能通过api接口
 
 **请求：**
 >http://47.98.143.87:9502/my_video/_doc/   _search   [post]
 
 **参数：**
 ```json
{
  "query": {
    "match": {
      "name": "许嵩"
    }
  }
}

```
或者：
 ```json
{
  "query": {
    "match_phrase": {
      "name": "许嵩"
    }
  }
}

```

 
 #### 5.2 elasticsearch-php的底层类库的安装和部署
 
 - 安装
 >在composer.json文件里面添加
 ```json
{
     "require":{
         "elasticsearch/elasticsearch":"^7.0"
     }
}
```
- 使用

**搜索基类**
```php
<?php
/**
 * Description:
 * User: Jeremy.Ke
 * Time: 2019/8/15 19:35
 */
namespace App\Model\Es;

use EasySwoole\Component\Singleton;
use Elasticsearch\ClientBuilder;

class EsClient
{
    use Singleton;
    public $esClient = null;
    private function __construct()
    {
        $es_conf = \Yaconf::get('es');
        $this->esClient = ClientBuilder::create()->setHosts([$es_conf['host'].":".$es_conf['port']])->build();
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        return $this->esClient->$name(...$arguments);
    }
}
```
**调用**
```php
<?php
public function elsLike()
{
    $param = [
        'index'=>'my_video',
        'type'=>'_doc',
        'body'=>[
            'query'=>[
                'match'=>[
                    'name'=>'杰'
                ],
            ],
        ],

    ];
    $client = ClientBuilder::create()->setHosts(['127.0.0.1:9502'])->build();
    $res = $client->search($param);
    return $this->writeJson(200,'ok',$res);
}
?>
```

#### 5.3 elasticsearch-analysic-ik分词器

 - 安装
> ./bin/elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-ik/releases/download/v7.0.0/elasticsearch-analysis-ik-7.0.0.zip
> 装完需要重启2.restart elasticsearch

 - 创建mapping
 >curl -XPOST http://localhost:9200/index/_mapping -H 'Content-Type:application/json' -d'
```json
{
    "properties": {
        "content": {
            "type": "text",
            "analyzer": "ik_max_word",
            "search_analyzer": "ik_smart"
        }
    }

}
```
 - query with highlighting
 >curl -XPOST http://localhost:9200/index/_search  -H 'Content-Type:application/json' -d'
 ```json
 {
     "query" : { "match" : { "content" : "中国" }},
     "highlight" : {
         "pre_tags" : ["<tag1>", "<tag2>"],
         "post_tags" : ["</tag1>", "</tag2>"],
         "fields" : {
             "content" : {}
         }
     }
 }
```
**结果**
```json
{
    "took": 14,
    "timed_out": false,
    "_shards": {
        "total": 5,
        "successful": 5,
        "failed": 0
    },
    "hits": {
        "total": 2,
        "max_score": 2,
        "hits": [
            {
                "_index": "index",
                "_type": "fulltext",
                "_id": "4",
                "_score": 2,
                "_source": {
                    "content": "中国驻洛杉矶领事馆遭亚裔男子枪击 嫌犯已自首"
                },
                "highlight": {
                    "content": [
                        "<tag1>中国</tag1>驻洛杉矶领事馆遭亚裔男子枪击 嫌犯已自首 "
                    ]
                }
            },
            {
                "_index": "index",
                "_type": "fulltext",
                "_id": "3",
                "_score": 2,
                "_source": {
                    "content": "中韩渔警冲突调查：韩警平均每天扣1艘中国渔船"
                },
                "highlight": {
                    "content": [
                        "均每天扣1艘<tag1>中国</tag1>渔船 "
                    ]
                }
            }
        ]
    }
}
```
 - 自定义词典
 >IKAnalyzer.cfg.xml can be located at {conf}/analysis-ik/config/IKAnalyzer.cfg.xml or {plugins}/elasticsearch-analysis-ik-*/config/IKAnalyzer.cfg.xml
 
```xml
 <?xml version="1.0" encoding="UTF-8"?>
 <!DOCTYPE properties SYSTEM "http://java.sun.com/dtd/properties.dtd">
 <properties>
 	<comment>IK Analyzer 扩展配置</comment>
 	<!--用户可以在这里配置自己的扩展字典 -->
 	<entry key="ext_dict">custom/mydict.dic;custom/single_word_low_freq.dic</entry>
 	 <!--用户可以在这里配置自己的扩展停止词字典-->
 	<entry key="ext_stopwords">custom/ext_stopword.dic</entry>
  	<!--用户可以在这里配置远程扩展字典 -->
 	<entry key="remote_ext_dict">location</entry>
  	<!--用户可以在这里配置远程扩展停止词字典-->
 	<entry key="remote_ext_stopwords">http://xxx.com/xxx.dic</entry>
 </properties>
 ```                                

 ## 6. 性能调优
 
 ## 7. 总结
 
 - 多进程，异步任务，消息队列
 - 定时器，连接池，协程
 - 全局事件注册
 - 框架底层源码分析
 - elasticsearch搜索
 - yaconf配置文件插件
 - 应对高并发
 - 静态api缓存(redis，文件，swoole_table)
 - nginx+lua+easyswoole
 
 