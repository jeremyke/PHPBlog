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