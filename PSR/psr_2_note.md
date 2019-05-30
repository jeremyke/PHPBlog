# PSR 规范PSR-2 编码风格规范学习笔记
## 文章链接
+ [PSR-2 编码风格规范](https://laravel-china.org/topics/2079/psr-specification-psr-2-coding-style-specification)

## 规范概述
+ Tab键=4个whitespace
+ namespace命名空间行和use引入类行中间要隔开一行。且namespace先于use.
+ 控制结构的关键字后必须要有一个空格符，而调用的方法或函数后面一定不可有空格符。
+ 控制结构的开始左括号后(比如："(")和结束右括号前(比如：")")，都一定不可 有空格符。
+ 类和方法的"{"和"}"都必须自成一行。
+ 控制结构的"{"与声明同一行，而"}"都必须自成一行。
+ 类的属性和方法必须添加访问限制符( **private、protected 以及 public**),不写默认public.
+ **abstract 以及 final** 必须 声明在访问修饰符之前，而 **static** 必须 声明在访问修饰符之后。

## 通则
### 文件
+ PHP文件行的结束必须用 **“换行符”**
+ PHP文件文件的结束必须用 **“空白行”**，纯PHP文本结束后面不加 **“?>”**

### 关键字以及True/False/Null
+ PHP中关键字以及True/False/Null都必须小写。

### 类、属性和方法
+ 关键词 extends 和 implements( [两者区别](http://www.cnblogs.com/buyanyu520/p/3197532.html))必须写在类名称的同一行。
+ implements的继承列表可以分成多行每个继承接口名称都必须分开独立成行，包括第一个。
比如：
```php
class ClassName extends ParentClass implements
    \ArrayAccess,
    \Countable,
    \Serializable
{
```
+ 方法的参数列表中，每个逗号后面 **必须** 要有一个空格，而逗号前面 **一定不可** 有空格。有默认值的参数，**必须** 放到参数列表的末尾。
+ 方法的参数列表可以分列成多行，这样，包括第一个参数在内的每个参数都 必须 单独成行。拆分成多行的参数列表后，结束括号以及方法开始花括号必须写在同一行，中间用一个空格分隔。
例如：
```php
namespace Vendor\Package;

class ClassName
{
    public function aVeryLongMethodName(
        ClassTypeHint $arg1,
        &$arg2,
        array $arg3 = []
    ) {
        // 方法的内容
    }
}
```

+ 方法及函数调用时，方法名或函数名与参数左括号之间 一定不可 有空格，参数右括号前也 一定不可 有空格。每个参数前 一定不可 有空格，但其后 必须 有一个空格。例如：
```php
<?php
bar();
$foo->bar($arg1);
Foo::bar($arg2, $arg3);
```
+ 参数 可以 分列成多行，此时包括第一个参数在内的每个参数都 必须 单独成行。例如：
```php
<?php
$foo->bar(
    $longArgument,
    $longerArgument,
    $muchLongerArgument
);
```
+ 右括号 **)** 与开始花括号 **{** 间 必须 有一个空格。
+ 




