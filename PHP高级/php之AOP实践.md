## 1. 定义

AOP意为面向切面编程，可以通过预编译方式和运行期动态代理实现在不修改源代码的情况下给程序动态统一添加功能的一种技术。设计模式孜孜不倦追求的是调用者和被调用者之间的解耦，AOP可以说也是这种目标的一种实现。

## 2. 术语解释

#### 2.2.1 Advice（通知）

Advice用于调用Aspect（切面），Advice用于定义某种情况下做什么和什么时间做这件事情。在我们之前的例子中，checkAuthentication(做什么）是advice（通知），在指定方法中它应该在执行代码之前（什么时间）被调用。

#### 2.2.2 Joinpoint（接入点）

Joinpoint是我们创建Advice应用中的位置。再翻看之前的代码，你会发现我调用了几个与业务逻辑没有直接关联的功能。在createPost()中，切面逻辑应该在执行验证逻辑之前和发送信息给管理员之后发生。这些都可能是接入点。在你的应用代码中，接入点可以放置在任何位置。但是Advice仅能在某些点中布置，这要根据你的AOP框架。

#### 2.2.3 Pointcut（点切割）

 点切割定义了一种把通知匹配到某些接入点的方式。虽然在我们的例子中只有一对接入点，但是在你的应用中你可以放置上千个接入点，你也不需要把通知应用到所有的接入点上。你可以把一些你认为有必要的接入点绑定到通知上。假设我们想要通知 createPost(),approvePost() 和 editPost()，但是现在没有viewPost()。我们使用某种方法把这三种方法绑定到通知上。之后我们创建一个包含切面细节的XML文件，这些细节包含一些匹配接入点的正则表达式。总结：当有横向切入关系存在于我们的应用的时候，我们可以创建一个切面，这个切面在一些选择使用点切割的接入点上应用通知功能。

## 3. AOP 通知类型

#### 3.1 前通知

在你的代码中一些特殊点之前使用通知,正常是调用一个方法。为了简化概念和让你更快的理解你的代码，我经常把通知写到方法里。但是在真实的环境里，通知经常是不写在方法里的。应该有一个独立的控制器，每个方法都在这个控制器里，而且每个方法都包裹着AOP的功能。这个全局的控制器运行在整个系统里，而且对我们是不可见的。

```php
<?php
class PathController
{
    function controlPaths($className, $funcName) {
        Authentication::checkAuthentication();
        $classObj = new $className();
        $classObj->$funcName();
    }
}
```

在这里假设有这么一个类，主要是用于给你展现这个类实际上发生了什么事情。假设那个controlPaths方法是应用中全局切入点，访问应用中的每个方法都需要通过这个方法访问。上面的方法中在执行每个方法之前，我们调用了通知checkAuthentication(),这就是前通知。

#### 3.2 返回后通知

这个通知在指定功能执行完后只执行一次，并且返回那个访问点。考虑下面的代码：

```php
<?php
class PathController
{
    function controlPaths($className, $funcName) {
        $classObj = new $className();
        $classObj->$funcName();
        Database::closeConnection();
    }
}
```

注意这里，当方法完成之后，我们清理了数据库资源。在返回通知之后，我们调用这个通知。

#### 3.3 抛出后通知

如果在执行进程期间函数抛出异常，那么在抛出完异常之后应用通知。这里是抛出完异常之后，通知就变成错误提示。

```php
<?php
class PathController
{
    function controlPaths($className, $funcName) {
        try {
            $classObj = new $className();
            $classObj->$funcName();
        }
        catch (Exception $e) {
            Error::reportError();
        }
    }
}
```

#### 3.4 周边通知

他是前通知和返回后通知的合并体。

```php
<?php
class PathController
{
    function controlPaths($className, $funcName) {
        Logger::startLog();
        $classObj = new $className();
        $classObj->$funcName();
        Logger::endLog();
    }
}
```

