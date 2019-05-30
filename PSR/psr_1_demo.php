<?php
//以下是一个 反例，一份包含「函数声明」以及产生「副作用」的代码：
// 「副作用」：修改 ini 配置
ini_set('error_reporting', E_ALL);
// 「副作用」：引入文件
include "file.php";
// 「副作用」：生成输出
echo "<html>\n";
// 声明函数
function foo()
{
    // 函数主体部分
}
?>

<?php
//下面是一个范例，一份只包含声明不产生「副作用」的代码：
// 声明函数
function foo()
{
    // 函数主体部分
}
// 条件声明 **不** 属于「副作用」
if (! function_exists('bar')) {
    function bar()
    {
        // 函数主体部分
    }
}