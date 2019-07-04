<?php

// 编写我们的Mysql连接池,这个连接池整个程序中只能存在一个
class MysqlConnectionPool
{
    private static $instance;// 这个属性就是保存当前这个类的单例对象
    private $connectionNumber = 20; // 表示我这个mysql连接池中有多少个mysql连接
    private $connections = []; // 保存所有的mysql连接
    private $avilableNumber = 20; // 表示我们的这个mysql连接池中当前有多少个可用的连接

    // 获取到这个类的单例对象
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    //
    private function __construct()
    {
        // 连接这个mysql，创建$this->connectionNumber个mysql的连接
        for ($i = 0; $i < $this->connectionNumber; $i ++) {
            // 连接mysql
            $dsn = "mysql:host=192.168.1.116;dbname=php57";
            $this->connections[] = new Pdo($dsn, 'root', 'root');
        }
    }

    // 执行查询的sql语句
    public function query($sql)
    {
        if ($this->avilableNumber == 0) {
            // mysql连接已经耗尽
            throw new Exception('mysql连接资源已经用完。');
        }
        // 执行这个sql语句
        // 从连接池中取出一个mysql的连接，把这个连接从连接池中删除
        $pdo = array_pop($this->connections);
        // 这个可用的mysql连接数-1
        $this->avilableNumber --;
        // 使用从连接池中取出的mysql的连接执行查询操作,把数据取成关联数组
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // 把这个mysql连接放回连接池中, 这个连接池中的可用的连接数+1
        array_push($this->connections, $pdo);
        $this->avilableNumber ++;

        return $rows;
    }


    private function __clone() {}
}

MysqlConnectionPool::getInstance();

// 创建这个swoole的http服务器的对象
$serv = new swoole_http_server('0.0.0.0', 8000);

// 当这个浏览器连接这个http服务器时，给这个浏览器发送一个 Hello World
$serv->on('request', function($request, $response)
{
    $stop = false;
    while (!$stop) {
        try {
            $sql = "SELECT * FROM member ORDER BY created_at DESC LIMIT 10";
            $rows = MysqlConnectionPool::getInstance()->query($sql);
            var_dump($rows);
            $stop = true;
        } catch (Exception $e) {
            // 继续重试，但是为了避免这个cpu过高
            usleep(100000); // 暂停0.1秒
        }
    }

    // $request包含这个请求的详细信息，比如请求时携带的参数
    // $response 包含返回给浏览器的信息，比如返回 Hello World
    // var_dump($request, $response);
    $response->end("Hello World.");
});

// 启动这个http服务器
$serv->start();
