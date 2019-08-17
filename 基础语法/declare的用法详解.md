>declare 结构用来设定一段代码的执行指令。declare 的语法和其它流程控制结构相似：
```php
declare (directive)
statement
```

directive 部分允许设定 declare 代码段的行为。目前只认识两个指令：ticks（更多信息见下面 ticks 指令）以及encoding（更多信息见下面 encoding 指令）。

**Note:** encoding 是 PHP 5.3.0 新增指令。

declare 代码段中的 statement 部分将被执行——怎样执行以及执行中有什么副作用出现取决于 directive 中设定的指令。

declare 结构也可用于全局范围，影响到其后的所有代码（但如果有 declare 结构的文件被其它文件包含，则对包含它的父文件不起作用）。

```php
<?php 
  declare (ticks = 1); //这句这么写表示全局的脚本都做处理
  function foo() { //注册的函数
      static $no;
      $no++;
      echo $no."======";
      echo microtime()."\n";

  } 
  register_tick_function("foo"); //注册函数，后面可以跟第2个参数，表示函数的参数
  $a = 1;
  for($i=0;$i<5;$i++) { //这里的循环也是语句，会做一次判断$i<5的判断执行
      $b = 1;
  }
 ?>

```

 - declare 调试内部程序使用.
>先简单说明，declare这个函数只支持一个参数就是ticks，函数表示记录程序块，需配合register_tick_function 函数使用。ticks参数表示运行多少语句调用一次register_tick_function的函数。并且declare支持两种写法：
```php
1. declare(ticks = 1); 整个脚本
2. declare(ticks = 1) { 内部的代码做记录
…
}
```

上述代码除了 函数体内,外部都会被执行,运行可以看执行次数和时间. 他跟适合做测试代码段中每一步分的执行时间 和执行次数. <br/>
declare 必须是全局的,放在程序外部.<br/>
tick 代表一个事件，事件的定义是在register_tick_function；事件的执行频率是在(ticks=3)。<br/>
表示事件频率是执行3个才记录一次. microtime() 的打印时间.<br/>