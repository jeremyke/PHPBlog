<?php
namespace Extend;

use ReflectionClass;
use Exception;

/**
 * 用户相关类
 * Class User
 * @package Extend
 */
class User{
    const ROLE = 'Students';
    public $username = '';
    private $password = '';

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * 获取用户名
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * 设置用户名
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * 获取密码
     * @return string
     */
    private function getPassword()
    {
        return $this->password;
    }

    /**
     * 设置密码
     * @param string $password
     */
    private function setPassowrd($password)
    {
        $this->password = $password;
    }
}

//$class = new ReflectionClass('Extend\User');  // 将类名User作为参数，即可建立User类的反射类
//$properties = $class->getProperties();  // 获取User类的所有属性，返回ReflectionProperty的数组
//$property = $class->getProperty('password'); // 获取User类的password属性ReflectionProperty
//$methods = $class->getMethods();   // 获取User类的所有方法，返回ReflectionMethod数组
//$method = $class->getMethod('getUsername');  // 获取User类的getUsername方法的ReflectionMethod
//$constants = $class->getConstants();   // 获取所有常量，返回常量定义数组
//$constant = $class->getConstant('ROLE');   // 获取ROLE常量
//$namespace = $class->getNamespaceName();  // 获取类的命名空间
//$comment_class = $class->getDocComment();  // 获取User类的注释文档，即定义在类之前的注释
//$comment_method = $class->getMethod('getUsername')->getDocComment();  // 获取User类中getUsername方法的注释文档
// 获取对象属性列表
$student = new User('Tom','123');

$reflect = new \ReflectionObject($student);
$props = $reflect->getProperties();
foreach ($props as $prop) {
    print $prop->getName() ."\n";
}
$m = $reflect->getMethods();
foreach ($m as $prop) {
    print $prop->getName() ."\n";
}
