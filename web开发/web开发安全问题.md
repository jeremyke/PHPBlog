#### 1. 跨站脚本攻击(XSS)
>跨站脚本攻击(Cross Site Script，简称 XSS)，利用网页开发时留下的漏洞，通过巧妙的方法注入恶意指令代码到网页，使用户加载并执行攻击者恶意制造的网页程序

- 分类
```text
反射型 XSS：简单地将用户输入的数据反射给浏览器
存储型 XSS：把用户输入的数据存储在服务器端
DOM Based XSS：修改页面 DOM 节点形成的 XSS
```
- 防御
```text
为 Cookie 设置 HttpOnly，避免 Cookie 被劫持泄露
对输入/输出进行检查，明确编码方式
```
#### 2. 跨站点请求伪造(CSRF)
>跨站请求伪造(Cross-site request forgery,简称 CSRF)， 是一种挟制用户在当前已登录的 Web 应用程序上执行非本意的操作的攻击方法

- 防御
```text
增加验证码(简单有效)
检查请求来源是否合法
增加随机 token
```
#### 3. SQL注入
>输入的字符串中注入 SQL 指令，若程序当中忽略了字符检查，导致恶意指令被执行而遭到破坏或入侵

##### 3.1 数字注入
```text
在浏览器地址栏输入：learn.me/sql/article.php?id=1，这是一个get型接口，发送这个请求相当于调用一个查询语句：

$sql = "SELECT * FROM article WHERE id =",$id

正常情况下，应该返回一个id=1的文章信息。那么，如果在浏览器地址栏输入：learn.me/sql/article.php?id=-1 OR 1 =1，这就是一个SQL注入攻击了，可能会返回所有文章的相关信息。为什么会这样呢？

这是因为，id = -1永远是false，1=1永远是true，所有整个where语句永远是ture，所以where条件相当于没有加where条件，那么查询的结果相当于整张表的内容
```
##### 3.2 字符串注入
```text
有这样一个用户登录场景：登录界面包括用户名和密码输入框，以及提交按钮。输入用户名和密码，提交。

这是一个post请求，登录时调用接口learn.me/sql/login.html，首先连接数据库，然后后台对post请求参数中携带的用户名、密码进行参数校验，即sql的查询过程。假设正确的用户名和密码为user和pwd123，输入正确的用户名和密码、提交，相当于调用了以下的SQL语句：

SELECT * FROM user WHERE username = 'user' ADN password = 'pwd123'

由于用户名和密码都是字符串，SQL注入方法即把参数携带的数据变成mysql中注释的字符串。mysql中有2种注释的方法：

1）'#'：'#'后所有的字符串都会被当成注释来处理

用户名输入：user'#（单引号闭合user左边的单引号），密码随意输入，如：111，然后点击提交按钮。等价于SQL语句：

SELECT * FROM user WHERE username = 'user'#'ADN password = '111'

'#'后面都被注释掉了，相当于：

SELECT * FROM user WHERE username = 'user'
2）'-- ' （--后面有个空格）：'-- '后面的字符串都会被当成注释来处理

用户名输入：user'-- （注意--后面有个空格，单引号闭合user左边的单引号），密码随意输入，如：111，然后点击提交按钮。等价于SQL语句：

SELECT * FROM user WHERE username = 'user'-- 'AND password = '111'

SELECT * FROM user WHERE username = 'user'-- 'AND password = '1111'

'-- '后面都被注释掉了，相当于：

SELECT * FROM user WHERE username = 'user'

因此，以上两种情况可能输入一个错误的密码或者不输入密码就可登录用户名为'user'的账号，这是十分危险的事情。

```

- 示例
```text
$id = $_GET['id'];
$sql = "SELECT * FROM `user` WHERE `id`={$id}";
```
- 防御
```text
使用预编译语句绑定变量(最佳方式)
使用安全的存储过程(也可能存在注入问题)
检查输入数据的数据类型(可对抗注入)
数据库最小权限原则
```
#### 4. DDOS分布式拒绝服务

- 网络层 DDOS
>伪造大量源 IP 地址，向服务器发送大量 SYN 包，因为源地址是伪造的，不会应答，大量消耗服务器资源(CPU 和内存)

- 应用层 DDOS
>应用层 DDOS，不同于网络层 DDOS，由于发生在应用层，因此 TCP 三次握手已完成，连接已建立，发起攻击的 IP 地址都是真实的
```text
CC 攻击：对一些消耗资源较大的应用界面不断发起正常的请求，以消耗服务器端资源
限制请求频率：在应用中针对每个客户端做一个请求频率的限制
```

#### 5.PHP 安全
- 文件包含漏洞
>include、require、include_once、require_once，使用这4个函数包含文件，该文件将作为 PHP 代码执行，PHP 内核不会在意该包含的文件是什么类型

- 代码执行漏洞
>危险函数exec、shell_exec、system可以直接执行系统命令。eval函数可以执行 PHP 代码


 