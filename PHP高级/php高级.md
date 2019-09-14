## 1. yield

#### 1.1 yield 是什么，使用场景?

它最简单的调用形式看起来像一个return申明，不同之处在于普通return会返回值并终止函数的执行，而yield会返回一个值给循环调用此生成器的代码并且只是暂停执行生成器函数。

我的简单理解：yield起一个暂停程序的作用，比如在一个循环中，程序执行遇到yield语句就会返回yield声明的数据，而不是循环完整体返回，加了yield后就会挨个返回。

Caution：如果在一个表达式上下文(例如在一个赋值表达式的右侧)中使用yield，你必须使用圆括号把yield申明包围起来。 例如这样是有效的：$data = (yield $value);
```php
<?php

/**
 * 生成一个长度为$num的数组
 *
 * @param $num
 * @return array
 */
function createRange($num)
{
    for ($i = 0; $i < $num; $i++) {
        sleep(1);
        yield time();
    }
}

$arr = createRange(10);

foreach ($arr as $val) {
    echo $val;
}
```
分析:
```text
# 执行时, 程序每间隔1s输出一个$val
# $arr的生成依赖于foreach, foreach每循环一次, $arr就生成一个值
# 无论$num是100万或更大数据时, $arr都会只在内存中记录一条数据, 节约大量的内存
```


## 2. PHP 进程模型，进程通讯方式，进程线程区别

#### 2.1 PHP 进程模型?

- (1)、PHP-FPM是多进程模式，master进程管理worker进程，进程的数量，都可以通过php-fpm.conf做具体配置，而PHP-FPM的进程，亦可以分为动态模式及静态模式和按需模式。
```text
    ①：静态（static）：直接开启指定数量的php-fpm进程，不再增加或者减少；启动固定数量的进程，占用内存高。但在用户请求波动大的时候，对Linux操作系统进程的
处理上耗费的系统资源低。
    ②：动态（dynamic）：开始的时候开启一定数量的php-fpm进程，当请求量变大的时候，动态的增加php-fpm进程数到上限，当空闲的时候自动释放空闲的进程数到一个下
限。动态模式，会根据max、min、idle children 配置，动态的调整进程数量。在用户请求较为波动，或者瞬间请求增高的时候，进行大量进程的创建、销毁等操作，而造成
Linux负载波动升高，简单来说，请求量少，PHP-FPM进程数少，请求量大，进程数多。优势就是，当请求量小的时候，进程数少，内存占用也小。
    ③：按需模式（ondemand）：这种模式下，在PHP-FPM启动的时候，master进程不会给这个pool启动任何一个worker进程，是按需启动，当有连接过来才会启动。这种模式很
少使用，因为这种模式，基本上是无法适应有一定量级的线上业务的。由于php-fpm是短连接的，所以每次请求都会先建立连接，在大流量的系统上master进程会变得繁忙，占用系统，
cpu资源，不适合大流量环境的部署。
```
按需模式如图说明：

![image](https://github.com/jeremyke/PHPBlog/raw/master/Pictures/1012804-968e65ef3010cebd.webp)

每个php-fpm进程占用30M内存，所有一般2G内存设置只内存为50个比较合适。

- (2)、PHP-FPM并不是是主进程接收请求转给子进程，而是子进程子进程抢占式的接受用户的请求。

```text
可以通过 trace -p [pid]命令去跟踪系统调用即可。分别跟踪php-fpm的主进程id以及php-fpm子进程id，然后访问nginx，由nginx通过fast-cgi协议转到php-fpm进程上，
看在哪个进程上发送了系统调用。
```
- (3)、PHP-FPM模式下的Yac为何无法和Cli模式无法共享内存。
```text
PHP-FPM模式下，在PHP-FPM进程启动的时候，便初始化一块共享内存，供各个进程来共享使用。开启共享内存需要的系统ID，shared_segment_name，此值，包含了进程的ID。
也就是php-fpm的主进程id。这就是，PHP-FPM模式所有进程间能够通信的奥秘所在（它们有相同的共享内存标识ID）。
在CLI模式下，这样是不可能拿到PHP-FPM模式下设置的共享内存数据的因为，因为CLI模式下，执行php脚本，进程ID，和PHP-FPM模式下的进程ID，根本就不相同。

```
#### 2.2 进程通讯方式
```text
pcntl扩展：主要的进程扩展，完成进程的创建，子进程的创建，也是当前使用比较广的多进程。

posix扩展：完成posix兼容机通用api,如获取进程id,杀死进程等。主要依赖 IEEE 1003.1 (POSIX.1) ，兼容posix

sysvmsg扩展：实现system v方式的进程间通信之消息队列。

sysvsem扩展：实现system v方式的信号量。

sysvshm扩展：实现system v方式的共享内存。

sockets扩展：实现socket通信，跨机器，跨平台。
```
#### 2.3 进程线程区别
```text
    从逻辑角度来看，多线程的意义在于一个应用程序中，有多个执行部分可以同时执行。但操作系统并没有将多个线程看做多个独立的应用，来实现进程的调度和管理以及资源分配。这就是进程和线程的重要区别。

    进程:是具有一定独立功能的程序关于某个数据集合上的一次运行活动,进程是系统进行资源分配和调度的一个独立单位。
    线程:是进程的一个实体,是CPU调度和分派的基本单位,它是比进程更小的能独立运行的基本单位.线程自己基本上不拥有系统资源,只拥有一点在运行中必不可少的资源(如程序计数器,一组寄存器和栈),
但是它可与同属一个进程的其他的线程共享进程所拥有的全部资源。
    一个线程可以创建和撤销另一个线程;同一个进程中的多个线程之间可以并发执。
    一个程序至少有一个进程,一个进程至少有一个线程,进程和线程的主要差别在于它们是不同的操作系统资源管理方式。进程有独立的地址空间，一个进程崩溃后，在保护模式下不会对其它进程产生影响，
而线程只是一个进程中的不同执行路径。线程有自己的堆栈和局部变量，但线程之间没有单独的地址空间，一个线程死掉就等于整个进程死掉，所以多进程的程序要比多线程的程序健壮，但在进程切换时，耗费
资源较大，效率要差一些。但对于一些要求同时进行并且又要共享某些变量的并发操作，只能用线程，不能用进程.
```
#### 2.4 PHP多进程
- 关于pcntl函数
```text
# pcntl_fork()函数
系统从当前进程生成一个新的进程, 原来的进程叫做父进程, 新生成的进程叫做子进程，子进程获得父进程数据空间、堆和栈的复制。
pcntl_fork()执行一次, 返回两次, 一次返回到新进程(0), 一次返回到父进程(子进程的pid).
子进程从pcntl_fork()返回后的位置开始执行, 但是拥有父进程前面定义的变量名和变量值.
pcntl_fork()执行失败返回-1.
一般来说, 在pcntl_fork()之后是父进程先执行还是子进程先执行是不确定的, 这取决于内核所使用的调度算法.

# posix_getpid()
返回当前进程ID

# posix_getppid()
返回父进程ID

# pcntl_wait()
阻塞当前的进程直到子进程退出, 父进程回收子进程的资源, 防止产生僵尸进程
```
- 创建子进程
```php
<?php

echo '父进程ID: ' . posix_getpid() . PHP_EOL;

$pid = pcntl_fork();

switch ($pid) {
    case -1:
        die('fork failed');
        break;
    case 0:
        echo '子进程ID: ' . posix_getpid() . PHP_EOL;
        sleep(2);
        echo '子进程已经退出';
        break;
    default:
        pcntl_wait($status);
        echo '父进程已经退出' . PHP_EOL;
        break;
}
```
运行结果：
```text
[root@e2963c647c8b www]# php threads.php
父进程ID: 573
子进程ID: 574
子进程已经退出父进程已经退出
```
- 理解pcntl_wait()
>pcntl_wait()在子进程退出之前会阻塞, 直到一个子进程退出后再创建另外一个子进程



## 3. PHP 7 与 PHP 5 有什么区别？

#### 3.1 php标量类型和返回类型声明
```text
#主要分为两种模式，强制性模式和严格模式
declare(strict_types=1)
#1表示严格类型校验模式，作用于函数调用和返回语句；0表示弱类型校验模式。
```
#### 3.2 NULL合并运算符
```text
$site = isset($_GET['site']) ? $_GET['site'] : 'wo';
#简写成
$site = $_GET['site'] ??'wo';
```
#### 3.3 组合预算符
```text
// 整型比较
print( 1 <=> 1);print(PHP_EOL);
print( 1 <=> 2);print(PHP_EOL);
print( 2 <=> 1);print(PHP_EOL);
print(PHP_EOL); // PHP_EOL 为换行符
//结果：
0
-1
1
```
#### 3.4 常量数组
```text
// 使用 define 函数来定义数组
define('sites', [
   'Google',
   'Jser',
   'Taobao'
]);

print(sites[1]);
```
#### 3.5 匿名类
```php
<?php

interface Logger { 
   public function log(string $msg); 
} 

class Application { 
   private $logger; 

   public function getLogger(): Logger { 
      return $this->logger; 
   } 

   public function setLogger(Logger $logger) { 
      $this->logger = $logger; 
   }   
} 

$app = new Application; 
// 使用 new class 创建匿名类 
$app->setLogger(new class implements Logger { 
   public function log(string $msg) { 
      print($msg); 
   } 
}); 

$app->getLogger()->log("我的第一条日志"); 
```

#### 3.6 Closure::call()方法增加，意思向类绑定个匿名函数
```php
<?php 
class A { 
    private $x = 1; 
} 

// PHP 7 之前版本定义闭包函数代码 
$getXCB = function() { 
    return $this->x; 
}; 

// 闭包函数绑定到类 A 上 
$getX = $getXCB->bindTo(new A, 'A');  

echo $getX(); 
print(PHP_EOL); 

// PHP 7+ 代码 
$getX = function() { 
    return $this->x; 
}; 
echo $getX->call(new A); 
?>
```
#### 3.7 CSPRNG（伪随机数产生器）
```text
PHP 7 通过引入几个 CSPRNG 函数提供一种简单的机制来生成密码学上强壮的随机数。

random_bytes() - 加密生存被保护的伪随机字符串。

random_int() - 加密生存被保护的伪随机整数。
```
#### 3.8 use语句改变
```text
#可以导入同一个namespace下的类简写
use some\namespace\{ClassA, ClassB, ClassC as C};
```
#### 3.8 Session 选项
```text
1.session_start()可以定义数组
<?php
session_start(&#91;
   'cache_limiter' => 'private',
   'read_and_close' => true,
]);
?>
2.引入了一个新的php.ini设置（session.lazy_write）,默认情况下设置为 true，意味着session数据只在发生变化时才写入。
```
####  PHP7 比 PHP5 性能提升了？
```text
1、变量存储字节减小，减少内存占用，提升变量操作速度

2、改善数组结构，数组元素和hash映射表被分配在同一块内存里，降低了内存占用、提升了 cpu 缓存命中率

3、改进了函数的调用机制，通过优化参数传递的环节，减少了一些指令，提高执行效率
```


