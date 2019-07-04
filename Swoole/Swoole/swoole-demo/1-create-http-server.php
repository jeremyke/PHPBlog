<?php

// 创建这个swoole的http服务器的对象
$serv = new swoole_http_server('0.0.0.0', 8000);

// 把这个swoole的worker进程的数量调整为10个
$serv->set([
    'daemonize' => true, // 表示swoole在后台运行
    'worker_num' => 10,
    'task_worker_num' => 10,
]);

// 当有请求过来时
$serv->on('connect', function(swoole_server $serv, int $fd, int $from_id) {
    $serv->task("this is data");
});

//
// 当这个浏览器连接这个http服务器时，给这个浏览器发送一个 Hello World
$serv->on('request', function($request, $response)
{
    // $request包含这个请求的详细信息，比如请求时携带的参数
    // $response 包含返回给浏览器的信息，比如返回 Hello World
    // var_dump($request, $response);
    $response->end("Hello World.");
});

// 设置这个任务的处理函数，就是某个任务被触发后，执行下面的函数
$serv->on('task', function(swoole_server $serv, $task_id, $from_id, $data) {
    var_dump($task_id, $from_id, $data);
    return '123';
});
// 设置这个任务执行完成后，这个任务返回的数据怎么处理
$serv->on('finish', function(swoole_server $serv, int $task_id, string $data) {
    var_dump($task_id, $data);
});

// 启动这个http服务器
$serv->start();
