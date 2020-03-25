## 1.NULL合并运算符（??），简化三元运算：

```php
<?php
$abc = $_GET['abc'] ?? 'abc';
```

## 2. 整除法函数（intdiv）:

```php
<?php
var_dump(intdiv(10,3));//3

var_dump(intdiv(-10,3));//-3

//注意：（1）ceil向上取整；（2）floor向下取整；
```

## 3.组合比较符：

太空船操作符用于比较两个表达式。当$a大于、等于或小于$b时它分别返回-1、0或1。

```php
echo 1<=>1;//返回0

echo 1<=>2;//返回-1

echo 2<=>1;//返回1
```

## 4.次方的算法：

```php
echo 3**2;//9
```

## 5.统一语法变量：
```php
例1：

$wzl = 'wangzilong'；

$foo = array(

'bar'=>array('baz'=>'wzl'),

);

echo $$foo['bar']['baz'];//会报错

例2：

$foo = 'wzl';

$wzl = array(

'bar'=>array('baz'=>'wzl'),

);

echo $$foo['bar']['baz'];//不会报错
```
## 6. 可以使用define（php5中只可以定义标量）定义常量数组：

```php
<?php

define('wzl',['wang','zi','long']);

var_dump(wzl);
```

## 7.标量类型声明

PHP 7 中的函数的形参类型声明可以是标量了。在 PHP 5 中只能是类名、接口、array 或者 callable (PHP 5.4，即可以是函数，包括匿名函数)，现在也可以使用 string、int、float和 bool 了。

```php
<?php
// 强制模式
function sumOfInts(int ...$ints)
{
    return array_sum($ints);
}

var_dump(sumOfInts(2, '3', 4.1));//9形参被强制转换为了int
```

## 8. 返回值类型声明

PHP 7 增加了对返回类型声明的支持。 类似于参数类型声明，返回类型声明指明了函数返回值的类型。可用的类型与参数声明中可用的类型相同。
```php
<?php

function arraysSum(array ...$arrays): array
{
    return array_map(function(array $array): int {
        return array_sum($array);
    }, $arrays);
}

print_r(arraysSum([1,2,3], [4,5,6], [7,8,9]));

//返回
Array
(
    [0] => 6
    [1] => 15
    [2] => 24
)
```


## 9. 匿名类

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
$app->setLogger(new class implements Logger {
    public function log(string $msg) {
        echo $msg;
    }
});

var_dump($app->getLogger());
?>
```




