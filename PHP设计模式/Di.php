<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/3/30
 * Time: 23:42
 */
namespace Core;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $instance = [];//对象存储的数组
    public function __construct($path) {
        $this->_autoload($path);  //首先我们要自动加载  psr-autoload
    }

    /**
     * 获取对象
     * @param $className
     * @return mixed|object
     * @throws \ReflectionException
     */
    public function build($className)
    {
        if(is_string($className) and $this->has($className)) {
            return $this->get($className);
        }
        //反射
        $reflector = new \ReflectionClass($className);

        // 检查类是否可实例化, 排除抽象类abstract和对象接口interface
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Can't instantiate ".$className);
        }
        /** @var \ReflectionMethod $constructor 获取类的构造函数 */
        $constructor = $reflector->getConstructor();
        // 若无构造函数，直接实例化并返回
        if (is_null($constructor)) {
            return new $className;
        }
        // 取构造函数参数,通过 ReflectionParameter 数组返回参数列表
        $parameters = $constructor->getParameters();
        // 递归解析构造函数的参数
        $dependencies = $this->getDependencies($parameters);
        // 创建一个类的新实例，给出的参数将传递到类的构造函数。
        $obj =  $reflector->newInstanceArgs($dependencies);
        $this->instance[$className] = $obj;
        return $obj;
    }

    /**
     * 获取构造函数的参数
     * @param array $parameters
     * @return array
     */
    public function getDependencies(array $parameters)
    {
        $dependencies = [];
        /** @var \ReflectionParameter $parameter */
        foreach ($parameters as $parameter) {
            /** @var \ReflectionClass $dependency */
            $dependency = $parameter->getClass();
            if (is_null($dependency)) {
                // 是变量,有默认值则设置默认值
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                // 是一个类，递归解析
                $dependencies[] = $this->build($dependency->name);
            }
        }
        return $dependencies;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return mixed
     * @throws \Exception
     */
    public function resolveNonClass(\ReflectionParameter $parameter)
    {
        // 有默认值则返回默认值
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        throw new \Exception($parameter->getName().' must be not null');
    }
    /**
     * 参照psr-autoload规范
     * @param $path
     */
    public function _autoload($path) {
        spl_autoload_register(function(string $class) use ($path) {
            $file = DIRECTORY_SEPARATOR.str_replace('\\',DIRECTORY_SEPARATOR, $class).'.php';
            if(is_file($path.$file)) {
                include($path.$file);
                return true;
            }
            return false;
        });
    }

    public function get($id)
    {
        if($this->has($id)) {
            return $this->instance[$id];
        }
        if(class_exists($id)){
            return $this->build($id);
        }
        throw new ClassNotFoundException('class not found');  //实现的PSR规范的异常
    }

    public function has($id)
    {
        return isset($this->instance[$id]) ? true : false;
    }
}
//使用
$container = new Container('../');//假设这是路径
$echoT = $container->get(\Test\EchoT::class);     //假设echoT类的命名空间是\Test
$echoT->echo();