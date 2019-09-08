## 基本语法

### PHP标记

1. 如果文件内容是纯 PHP 代码，最好在文件末尾删除 PHP 结束标记。<font color="pink">这可以避免在 PHP 结束标记之后万一意外加入了空格或者换行符，会导致 PHP 开始输出这些空白，而脚本中此时并无输出的意图。</font>
2. 文件的开头是"<?php[whitespace]",而不是"<?php"，这里whitespace包括\t,\n,\r,\s,不要仅仅以为是“\s”；此外[whitespace]不包括注释“/**/”。

### 从HTML中分离

1. PHP将跳过条件语句未达成的段落，即使该段落位于 PHP 开始和结束标记之外。由于 PHP 解释器会在条件未达成时直接跳过该段条件语句块，因此 PHP 会根据条件来忽略之。要输出大段文本时，跳出 PHP 解析模式通常比将文本通过 echo 或 print 输出更有效率。比如：
```php
<?php if ($expression == true): ?>
  This will show if the expression is true.
<?php else: ?>
  Otherwise this will show.
<?php endif; ?>
```
2. php4种标记：
	- <?php ?>,推荐使用，此外短格式的 echo 标记 <?= 总会被识别并且合法，而不管 short_open_tag 的设置是什么。
	- <script language="php"> </script>，少使用
	- <? ?>,打开php.ini扩展才可用，不建议
	- <% %>打开php.ini扩展才可用不建议使用，ASP风格，不建议
	
### 指令分隔符

1. PHP 需要在每个语句后用分号结束指令,最后一行之所以没有用分号，是因为PHP代码段的结束标记隐含了一个分号。
```php
<?php
    echo "This is a test";
?>
<?php echo "This is a test" ?>
<?php echo 'We omitted the last closing tag';
```

### 注释

1. **行注释** 单行注释仅仅注释到行末或者当前的 PHP 代码块，视乎哪个首先出现。这意味着在 // ... ?> 或者 # ... ?> 之后的 HTML 代码将被显示出来：?> 跳出了 PHP 模式并返回了 HTML 模式，// 或 # 并不能影响到这一点
2. **块注释** /**/

## 数据类型
### 概述
#### 9种数据类型
* 四种标量类型：
	* boolean（布尔型）  
	* integer（整型）  
	* float（浮点型，也称作 double)  
	* string（字符串）
* 三种复合类型：
	* array（数组）  
	* object（对象）  
	* callable（可调用）
* 两种特殊类型：
	* resource（资源）  
	* NULL（无类型）
### Boolean
boolean值不区分大小写(包括false,0,0.0,"","0",array()),其他都为true.

### Integer
要使用八进制表达，数字前必须加上 0（零）。要使用十六进制表达，数字前必须加上 0x。要使用二进制表达，数字前必须加上 0b。

***
通读PHP手册笔记
========================

##访问控制（可见性）
-----------------

* 首先我们必须从架构层去理解这个访问控制。不管成员是public,protected or private 的，在其子类中都会得到继承，只是为了达到某种业务需求，突出子类的特性就如同基因遗传有显性隐性一样，private修饰的成员无法被被子类调用。而protected 修饰的成员在整个类链中都可以访问到。public修饰的成员，在任何地方都可以被调用。看看单例（三私一公）吧：
    1. 需要一个保存类的唯一实例的静态成员变量。
    2. 构造函数和克隆函数必须声明为私有的，防止外部程序new类从而失去单例模式的意义。
    3. 必须提供一个访问这个实例的公共的静态方法（通常为getInstance方法），从而返回唯一实例的一个引用。
* public 和 protected修饰的成员在子类中会被重写。

	

