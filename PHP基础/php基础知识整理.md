 ## 1 语法
 #### 1.1 关于print，echo，var_dump，printf，print_r
  
  ```text
 	echo：输出一个或多个字符串和数字。没有返回值。
    print：只允许输出一个字符串，变量的值或者表达式的值等，不能输出数组等复杂数据。返回值总为1。
    print_r：一般打印数组，只打印数组元素的下标和元素的值，不打印其他的额外的信息。
 	var_dump：不仅仅可以输出一个或多个表达式的值，还能输出表达式的类型，长度等其他额外的信息！并且能输出复杂数据类型，比如数组！
 	printf：擅长输出由静态文本和其他变量所组成的“混合产物”。（前半部分是用引号括起来的字符串和占位符，后面是占位符对应的变量）
  ```
 #### 1.2 系统函数
  
 #### 1.3 语句include和require的区别是什么?
  ```text
    在失败的时候：include 产生一个 warning ，而 require 直接产生错误中断；
    require 在运行前载入；include 在运行时载入；
    require_once 和 include_once 可以避免重复包含同一文件
  ```
 #### 1.4作用域
  ```php
  （1）全局作用域和局部作用域：
    在所有函数外部定义的变量，拥有全局作用域。除了函数外，全局变量可以被脚本中的任何部分访问，要在一个函数中访问一个全局变量，需要使用 global 关键字。
    全局作用域可以调用全局变量，但是不可以调用局部变量（因为函数执行完毕后，其内部的局部变量或函数要被计算机的内存所回收）。
    局部作用域可以调用局部变量但不可以调用全局变量。可以通过过$GLOBALS或global方式来访问全局变量。
    总结：$GLOBALS['i']与global $i的区别
        ① $GLOBALS['i']与全局变量$i是同一元素，删除其中任何一个都会对另外一个产生影响！
        ② global $i相当于应用全局变量$i在内存中的地址，删除global $i只是相当于移除了关联关系，但是其对原变量没有任何影响！
  （2）Static 作用域
     当一个函数完成时，它的所有变量通常都会被删除。然而，有时候您希望某个局部变量不要被删除。要做到这一点，在函数内部声明变量时使用 static 关键字即可。
  ``` 
 #### 1.5 isset（）和！empty（）
 ```text
  isset（）检查变量（或数组的元素或对象的属性）是否存在（并且不为null）。如果var存在则返回TRUE;否则返回FALSE。
  empty（）函数检查变量是否为空值空字符串，0，NULL或False。如果var具有非空和非零值，则返回FALSE。
 ```
 #### 1.6读取文件内容的PHP函数
 ```text
 (1)fopen():
    fopen()作用是打开一个文件, 返回的是文件指针, 它不能直接输出文件内容, 要配合fget()一类的函数来从文件指针中读取文件内容
    文件使用完之后需要通过fclose()函数来关闭该指针指向的文件
 (2)file_get_contents():
    file_get_contents()是将整个文件的内容读取到一个字符串中
 (3)file():
    file()函数和file_get_contents()函数类似, 不同的是file()函数读取文件内容并返回一个数组,该数组每个单元都是文件中相应的一行, 包括换行符在内。
 ```
 #### 1.7 合并数组的方式
 - array_merge
 ```text
 如果是相同的数字索引, array_merge()会重建索引,新的索引从0开始
 如果是相同的字符串索引, array_merge()会用后面数组的值覆盖掉前面的值
 ```
 ```php
 $arr1 = [1 => 2, 4, 'color' => 'red'];
 $arr2 = ['a', 'b', 'color' => ['green'], 'shape' => 'circle', 4];
 
 $res1 = array_merge($arr1, $arr2);
 print_r($res1);
 ```
  结果：
 ```text
 Array
 (
     [0] => 2
     [1] => 4
     [color] => Array
         (
             [0] => green
         )
     [2] => a
     [3] => b
     [shape] => circle
     [4] => 4
 )
 ```
 - \+
 >对于相同的数字索引和字符串索引, +会前面数组的值覆盖掉后面数组的值, 合并后的数组索引保持不变
 ```php
 $arr1 = [1 => 2, 4, 'color' => 'red'];
 $arr2 = ['a', 'b', 'color' => ['green'], 'shape' => 'circle', 4];
 
 $res1 = $arr1 + $arr2;
 print_r($res1);
 ```
 结果：
 ```text
 Array
 (
     [1] => 2
     [2] => 4
     [color] => red
     [0] => a
     [shape] => circle
 )
 ```
 - array_merge_recursive
 >如果是相同的数字索引, 处理方式和array_merge相同, 都是重建索引, 新的索引从0开始
  如果是相同的字符串索引, 会把相同的索引放到一个数组里面
  
 ```php
 <?php
 $arr1 = [1 => 2, 4, 'color' => 'red'];
 $arr2 = ['a', 'b', 'color' => ['green'], 'shape' => 'circle', 4];
 
 $res1 = $res1 = array_merge_recursive($arr1, $arr2);
 print_r($res1);
 ```
 结果：
 ```text
 Array
 (
     [0] => 2
     [1] => 4
     [color] => Array
         (
             [0] => red
             [1] => green
         )
     [2] => a
     [3] => b
     [shape] => circle
     [4] => 4
 )
 ```
 
 
 ## 2 变量和常量
 
 #### 2.1 常量
 >常量是一个简单值的标识符。该值在脚本中不能改变。一个常量由英文字母、下划线、和数字组成,但数字不能作为首字母出现。常量在定义后，默认是全
 局变量，可以在整个运行的脚本的任何地方使用。
 
 bool define ( string $name , mixed $value [, bool $case_insensitive = false ] )
 
 #### 2.2 变量
 
 ###### 2.2.1数据类型
 >布尔型，整形，浮点型，字符串型，数组,对象，空值（7种）
 ###### 2.2.2 字符串变量函数
  ```php
  <?php
  strlen($str);//返回字符串长度 mb_strlen($str) 可以返回中文字符长度；
  str_replace('a','b',$str);//用b替换$str 中的a 区分大小写  ;
  str_ireplace('a','b',$str);//替换 不区分大小写
  htmlspecialchars($str,ENT_NOQUOTES);//字符串转换为html实体。 ENT_COMPT(默认只编译双引号)ENT_QUOTES单引号双引号都编译,ENT_NOQUOTES不编译任何引号
  strpos($str,'a');//字符串a 在$str 第一次出现的位置 索引0开始 没有出现返回false 区分大小写;stripos($str,'a')不区分大小写
  strrpos($str,'a');//字符串a 在$str 最后一次出现的位置 索引0开始 没有出现返回false 区分大小写; strripos($str,'a’)不区分大小写
  substr($str,0,3);//截取字符串 $str 的第一个字符 截取长度3 长度不填默认截取到最后  参数为负数则倒数
  strstr($str,'a');//截取字符串 $str 中的第一个字符'a'后的字符串 如 sabc -> abc
  strrchr($str,'a');//截取字符串 $str 中最后一个字符'a'后的字符串
  strrev($str);//字符串反转 abcd->dcba
  explode('-',$str);//指定分隔符分割字符串 返回数组 ‘-’ 分割$str
  implode('-',$str);//数组拼接字符串 与explode()相反
  ```

 ###### 2.2.3 数组函数
 ```php
 <?php
 //(1)数组的键名和值
 array_values($arr);  //获得数组的所有值
 array_keys($arr);  //获得数组的所有键名
 array_flip($arr);  //数组中的值与键名互换（如果有重复前面的会被后面的覆盖）
 in_array("apple",$arr);  //在数组中检索apple
 array_search("apple",$arr);  //在数组中检索apple ，如果存在返回键名
 array_key_exists("apple",$arr);  //检索给定的键名是否存在数组中
 isset($arr[apple]);   //检索给定的键名是否存在数组中
 //(2)数组的内部指针
 current($arr);  //返回数组中的当前单元
 pos($arr);  //返回数组中的当前单元
 key($arr);  //返回数组中当前单元的键名
 prev($arr);  //将数组中的内部指针倒回一位
 next($arr);  //将数组中的内部指针向前移动一位
 end($arr);  //将数组中的内部指针指向最后一个单元
 reset($arr;  //将数组中的内部指针指向第一个单元
 each($arr);  //将返回数组当前元素的一个键名/值的构造数组，并使数组指针向前移动一位
 list($key,$value)=each($arr);  //获得数组当前元素的键名和值
 //(3)数组的分段和填充
 array_slice($arr,0,3);  //可以将数组中的一段取出，此函数忽略键名
 array_splice($arr,0,3，array("black","maroon"));  //可以将数组中的一段取出，与上个函数不同在于返回的序列从原数组中删除
 array_chunk($arr,3,TRUE);  //可以将一个数组分割成多个，TRUE为保留原数组的键名
 //(4)栈，队列
 array_push($arr,"apple","pear");  //将一个或多个元素压入数组栈的末尾（入栈），返回入栈元素的个数
 array_pop($arr);  //将数组栈的最后一个元素弹出（出栈） 
 array_shift($arr);//数组中的第一个元素移出并作为结果返回（数组长度减1，其他元素向前移动一位，数字键名改为从零技术，文字键名不变）
 array_unshift($arr,"a",array(1,2));//在数组的开头插入一个或多个元素
 sort();//根据数组中元素的值，以英文字母顺序排序，索引从0递增
 asort();//对数组排序，保持数组的索引和单元的关联。
 ksort();//根据数组中索引键的值，以英文字母顺序排序。
 ```
 
 #### 2.3超全局变量
  
  ```text
（1）$_SERVER
     1.$_SERVER['HTTP_HOST']  请求头信息中的Host内容，获取当前域名。(如：www.baidu.com)
     2.$_SERVER["SERVER_NAME"]  输出配置文件httpd.conf中的ServerName，一般情况下与HTTP_HOST值相同，但如果服务器端口不是默认的80端口，或者协议规范不是HTTP/1.1时，HTTP_HOST会包含这些信息，而SERVER_NAME不一定包含。（主要看配置文件的设置）。
     3.$_SERVER["SystemRoot"]  当前服务器的操作系统。
     4.$_SERVER["SERVER_ADDR"]  当前运行脚本的服务器的ip地址。
     5.$_SERVER["REMOTE_ADDR"]  浏览网页的用户ip。
     6.$_SERVER["DOCUMENT_ROOT"]  当前运行脚本所在的根目录。
     7.$_SERVER["REQUEST_SCHEME"]  服务器通信协议，是http或https。
     8.$_SERVER["CONTEXT_PREFIX"]  前缀。
     9.$_SERVER["CONTEXT_DOCUMENT_ROOT"]  当前脚本所在的文档根目录。
（2）$_FILES 上传文件
     1.$_FILES['userfile']['name'] 客户端文件原名
     2.$_FILES['userfile']['type'] 文件类型
     3.$_FILES['userfile']['size'] 上传文件大小，单位为字节
     4.$_FILES['userfile']['tmp_name'] 文件被上传在服务器端保存的临时文件
     5.$_FILES['userfile']['error'] 文件上传的错误码
（3）$_REQUEST
（4）$_GET
（5）$_POST
（6）GLOBALS
（7）$_SESSION
（8）$_COOKIE
 ```
 
 #### 2.4关于魔术
 
 ###### 2.4.1魔术常量
 >PHP 向它运行的任何脚本提供了大量的预定义常量,不过很多常量都是由不同的扩展库定义的，只有在加载了这些扩展库时才会出现，或者动态加载后，或者在编译时已经包括进去了,
 其中 有八个魔术常量它们的值随着它们在代码中的位置改变而改变。
 ```text
 __DIR__：它等价于 dirname(__FILE__)。除非是根目录，否则目录中名不包括末尾的斜杠。
 __FILE__：文件的完整路径和文件名。（绝对路径）
 __LINE__：文件中的当前行号。
 __CLASS__：该类被定义时的名字（区分大小写）
 __FUNCTION__：该函数被定义时的名字（区分大小写）
 __METHOD__：返回该方法被定义时的名字（区分大小写）
 __TRAIT__：实现了代码复用的一个方法，先顺序是当前类中的方法会覆盖 trait 方法，而 trait 方法又覆盖了基类中的方法。
 __NAMESPACE__：当前命名空间的名称（区分大小写）

 ```
 ###### 2.4.2魔术方法
 ```text
 __tostring():将对象当成字符串使用时候自动调用
 __invoke():将对象当成函数使用时自动调用
 __set():当给无法访问的属性赋值的时候自动调用
 __get():当获取无法访问的属性的时候自动调用
 __unset():当销毁无法访问的属性的时候自动调用
 __isset():但判断一个无法访问的属性是否存在时自动调用
 __call():当调用无法访问的方法时候自动触发
 __callstatic():当调用无法访问的静态方法时自动调用
 ```
 ## 3 函数
 
 #### 3.1  几个防止sql注入函数
  
  ```text
  addslashes()：转义字符串
  mysql_real_escape_string();用反斜杠转义字符串中的特殊字符
  htmlspecialchars():HTML实体转移
  ```
 #### 3.2 递归函数
 ```php
 <?php
 //满足的条件：① 必须要有一个递归条件 ② 递归本身是一个死循环，你必须要有一个递归出口，否则死循环是无法结束的！
 function digui($n){
     echo $n."\t";
     if($n>0){
         digui($n-1);
     }else{
         echo "<---->";
     }
     echo $n."\t";
 }
 digui(3);//执行结果：321<--->123
 
 //可以用于二分法查找
 function erfen($data,$value,$start,$end=null){
     if($end==null) $end = count($data)-1;
     $index = floor(($start + $end)/2);
     if($data[$index]==$value) return $index;
     if(data[$index]<$value) return erfen($data,$value,$index+1);
     if(data[$index]>$value) return erfen($data,$value,$0,$index-1);
 }
 ```