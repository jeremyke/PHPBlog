 ## 1. 概述
针对MYSQL数据库数据的数据变更事件（insert, update, delete）进行监听并消费事件。

Ps: MYSQL必须打开binlog日志。

 ## 2. 应用场景
数据同步

可以实现MYSQL数据到其他系统的数据实时同步，例如：MYSQL TO ELASTICSEARCH、 MYSQL TO MYSQL、MYSQL TO OTHER DB

异步更新缓存

例如：根据mysql数据变化，异步清理/更新redis缓存

异步业务处理

业务解耦，让核心业务更简单。例如：下单后，触发订单配送、赠送积分、订单状态推送等等。

 ## 3. 架构设计
 #### 3.1. 事件流设计

![image](https://github.com/jeremyke/PHPBlog/raw/master/Pictures/MySQL事件消费组件.png)
MYSQL事件从左边流向右边的DbEventWorker。

待开发的模块有两个：

DTS- 负责监听mysql binlog, 将事件投递到KAFKA队列,(DTS默认开发了一个中间件，可以考虑使用阿里云的DTS替换，替换后处理下DbEventListener处理的消息格式即可)。
DbEventWorker- 负责最终从KAFKA队列，消费事件。

 #### 3.2. DBEventWorker逻辑设计
![image](https://github.com/jeremyke/PHPBlog/raw/master/Pictures/DbEventWorker组件设计.png)

主要包括如下子模块：

DbEventServer                        - db event worker入口，负责启动相关子组件，负责全量同步任务接口。
DbEventListenerManage         - Listener管理器，负责启动和监控listener。
DbEventFullSyncWorker          - 全量同步任务组件。
DbEventListener                     - Listener监听器，负责消费MYSQL事件。
 ## 4. 监听器开发例子
定义Listener

在配置文件db_event_listener.php中定义监听器, 下面是配置详解。
```yaml
<?php
use lwm\services\SrvType;
return [
    "listeners" => [
        //监听器配置
        "listener-id 监听器id" => [
            //需要订阅的数据库表，格式:
            //         "数据库名" => ["表1", "表2", "表3"]
            "subscribe" => [
                "weixin" => ["wx_config","wx_admin"],
                "platform" => ["wx_config","wx_admin"],
            ],
            //并发数
            "workers" => 3,
            //监听器版本，主要用于灰度升级，灰度环境的版本号大于生产环境的版本号，则由灰度环境运行该监听器，反之由线上运行。
            "version" => 1,
            //批处理大小，一次性处理多少条事件
            "batchSize" => 128,
            //handler, 事件处理器 格式[service类型, '业务接口']
            //事件处理器，函数参数为：events数组
            //函数原型: public function handler(array $evs)
            //evs 参数格式:
            // [
            //    ["EvId" => "事件id", "TableName" => "表名", "Schema" => "数据库名", "PK" => "主键", "Action" => "事件类型，目前包括update，insert, delete", "Data" => [表字段数组]]
            // ]
            //处理成功返回true, 失败返回false, 失败会无限重试.
            "handler" => [SrvType::COMMON_HELPER, 'test']
        ],
    ]
];

```

编写监听器实现代码：

下面是handler配置的service方法
```php
<?php
public function test($evs)
{
   foreach ($evs as $ev) {
		//处理mysql事件
		switch ($ev['Action']) {
			case 'insert': doing..
			case 'update': doing..
			case 'delete': doing..
		}
	}

	return true;
}
```



 ## 5. 全量同步
默认情况DbEventWorker的接口绑定的是本地IP地址，因此需要登录启动DbEventWorker的服务器访问下面的接口添加同步任务。

添加同步任务命令：

curl "http://127.0.0.1:12359/?table=表名&listenerid=监听器id"

ps: 关于请求端口，可以查看db_event_listener.php配置文件listen.port定义。

//例子1
curl "http://127.0.0.1:12359/?table=wx_config&listenerid=test"

//例子2
//表名可以携带数据库名前缀
curl "http://127.0.0.1:12359/?table=weixin.wx_config&listenerid=test"



## 6. 部署
DbEventWorker启动比较简单，在php cli模式下按下面方式启动即可：

./scripts/startDbEventWorker.sh



可以通过以下方式启动单个listener

php ./scripts/DbEventListener.php -t  listener id

