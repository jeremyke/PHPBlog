<?php

// 创建这个websocket服务器的对象
$serv = new swoole_websocket_server('0.0.0.0', 8000);

// 当这个websocket客户端给我们的这个服务器发送消息时进行回应
$serv->on('message', function(swoole_websocket_server $server, swoole_websocket_frame $frame) {
    // var_dump($server, $frame);
    // 获取websocket客户端发送给我们需要计算的数据
    $data = json_decode($frame->data, true);
    $result = null;
    switch($data['operator']) {
        case '+':
            $result = $data['firstNumber'] + $data['secondNumber'];
            break;
        case '-':
            $result = $data['firstNumber'] - $data['secondNumber'];
            break;
        case '*':
            $result = $data['firstNumber'] * $data['secondNumber'];
            break;
        case '/':
            $result = $data['firstNumber'] / $data['secondNumber'];
            break;
    }
    // 把这个计算的结果发送给websocket客户端
    $server->push($frame->fd, $result);// 给这个当前给我发送数据的这个websocket客户端回复消息，这个$frame->fd就是websocket客户端的唯一标识
});

// 启动这个websocket服务器
$serv->start();
