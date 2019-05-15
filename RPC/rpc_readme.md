### 什么是RPC？

RPC（Remote Procedure Call Protocol）——远程过程调用协议，它是一种通过网络从远程计算机程序上请求服务，而不需要了解底层网络技术的协议。RPC协议假定某些传输协议的存在，如TCP或UDP，为通信程序之间携带信息数据。在OSI网络通信模型中，RPC跨越了传输层和应用层【参考下图】。RPC使得开发包括网络分布式多程序在内的应用程序更加容易。
##### 网络数据传输层：
![在这里插入图片描述](https://img-blog.csdnimg.cn/20181125171703476.png?x-oss-process=image/watermark,type_ZmFuZ3poZW5naGVpdGk,shadow_10,text_aHR0cHM6Ly9ibG9nLmNzZG4ubmV0L2plcmVteV9rZQ==,size_16,color_FFFFFF,t_70)

### 为什么要使用RPC？

1. 如果我们开发简单的单一应用，逻辑简单、用户不多、流量不大，那我们用不着；
2. 当我们的系统访问量增大、业务增多时，我们会发现一台单机运行此系统已经无法承受。此时，我们可以将业务拆分成几个互不关联的应用，分别部署在各自机器上，以划清逻辑并减小压力。此时，我们也可以不需要RPC，因为应用之间是互不关联的。
3. 当我们的业务越来越多、应用也越来越多时，自然的，我们会发现有些功能已经不能简单划分开来或者划分不出来。此时，可以将公共业务逻辑抽离出来，将之组成独立的服务Service应用 。而原有的、新增的应用都可以与那些独立的Service应用 交互，以此来完成完整的业务功能。所以此时，我们急需一种高效的应用程序之间的通讯手段来完成这种需求，所以你看，RPC大显身手的时候来了！
其实3描述的场景也是服务化 、微服务 和分布式系统架构 的基础场景。即RPC框架就是实现以上结构的有力方式。

### RPC和HTTP的区别？
1. HTTP接口由于受限于HTTP协议，需要带HTTP请求头，导致传输起来效率或者说安全性不如RPC。
2. http接口是在接口不多、系统与系统交互较少的情况下，解决信息孤岛初期常使用的一种通信手段；优点就是简单、直接、开发方便。利用现成的http协议 进行传输。但是如果是一个大型的网站，内部子系统较多、接口非常多的情况下，RPC框架的好处就显示出来了，首先就是长链接，不必每次通信都要像http 一样去3次握手什么的，减少了网络开销；其次就是RPC框架一般都有注册中心，有丰富的监控管理；发布、下线接口、动态扩展等，对调用方来说是无感知、统 一化的操作。第三个来说就是安全性。最后就是最近流行的服务化架构、服务化治理，RPC框架是一个强力的支撑。
3. RPC 是 远程过程调用，RPC 包含传输协议和编码协议。
4. http是超文本传输协议，RPC 也可以用http作为传输协议，但一般是用 tcp作为传输协议。用json作为编码协议。

### RPC框架？
由于笔者是一位PHP程序员，这里就说下sina使用的yar框架。
##### （1）下载
```shell
wget http://pecl.php.net/get/yar-1.2.4.tgz
```
##### （2）编译安装yar
```shell
	phpize
	.configure
	make
	make install
```
##### （3）在php.ini中添加扩展
```shell
extension=yar.so
```
#### （4）简单测试
server.php
```php
<?php
class Test
{
    public function Hello()
    {
        return 'Hello world';
    }
}
$service = new Yar_Server(new Test);
$service->handle();
```
client.php
```php
<?php:
$client = new Yar_Client('http://127.0.0.1:80/server.php');
$res = $client->Hello();
var_dump($res);
```
测试结果
```
php client.php
```
![在这里插入图片描述](https://img-blog.csdnimg.cn/20181125173310619.png)

