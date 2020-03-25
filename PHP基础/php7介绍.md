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

