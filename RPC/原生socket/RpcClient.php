<?php
/**
 * Description:用PHP的socket扩展来创建一个客户端
 * User: Jeremy.Ke
 * Time: 2019/4/4 17:30
 */
class RpcClient {
    protected $urlInfo = array();
    public function __construct($url) {
        //解析URL
        $this->urlInfo = parse_url($url);
        if(!$this->urlInfo) {
            exit("{$url} error \n");
        }
    }
    public function __call($method, $params) {
        //创建一个客户端
        $client = stream_socket_client("tcp://{$this->urlInfo['host']}:{$this->urlInfo['port']}", $errno, $errstr);
        if (!$client) {
            exit("{$errno} : {$errstr} \n");
        }
        //传递调用的类名
        $class = basename($this->urlInfo['path']);
        $proto = "RPC-Class: {$class};" . PHP_EOL;
        //传递调用的方法名
        $proto .= "RPC-Method: {$method};" . PHP_EOL;
        //传递方法的参数
        $params = json_encode($params);
        $proto .= "RPC-Params: {$params};" . PHP_EOL;
        //向服务端发送我们自定义的协议数据
        fwrite($client, $proto);
        //读取服务端传来的数据
        $data = fread($client, 2048);
        //关闭客户端
        fclose($client);
        return $data;
    }
}
$cli = new RpcClient('http://127.0.0.1:8888/test');
echo $cli->hehe();
echo $cli->hehe2(array('name' => 'test', 'age' => 27));