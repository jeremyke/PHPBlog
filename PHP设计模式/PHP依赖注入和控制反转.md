 ## PHP依赖注入和控制反转（DI&&IoC）
 >User: Jeremy.Ke
 >Time: 2019/6/25 3:32

 #### 1 理论分析
 
 - 1.1 概念
 >依赖注入和控制反转是对同一件事情的不同描述，从某个方面讲，就是它们描述的角度不同。
 
 (1)依赖注入是从应用程序的角度在描述，即：应用程序依赖容器创建并注入它所需要的外部资源；
 
 (2)而控制反转是从容器的角度在描述，即：容器控制应用程序，由容器反向的向应用程序注入应用程序所需要的外部资源.
 
 - 1.2 优势
 >使用依赖注入，最重要的一点好处就是有效的分离了对象和它所需要的外部资源，使得它们松散耦合，有利于功能复用，更重要的是使得程序的整个体系结构变得非常灵活。
 
 - 1.3 自问自答
 
 （1）整个过程中的参与者？
 ``` 
 一般有三方参与者，一个是某个对象；一个是IoC/DI的容器；另一个是某个对象的外部资源。
    
 其一：某个对象指的就是任意的、普通的PHP对象; 
    
 其二：IoC/DI的容器简单点说就是指用来实现IoC/DI功能的一个框架程序；
    
 其三：对象的外部资源指的就是对象需要的，但是是从对象外部获取的，都统称资源，比如：对象需要的其它对象、或者是对象需要的文件资源等等。

 ```
 
 （2）谁依赖于谁？为什么需要依赖？
 >某个对象依赖于IoC/DI的容器,对象需要IoC/DI的容器来提供对象需要的外部资源.
 
 （3）谁注入于谁?注入什么?
 >IoC/DI的容器 注入(某些资源到)某个对象，注入的对象所需的资源。
 
 （4）控制什么？为何叫反转？什么是正转？
 >主要是控制对象实例的创建。<br/>
  A类的实例化需要资源B,直接在A类中去获取配置文件或者实例化B获取资源B，叫做正转；<br/>
  A类不再主动去获取C，而是被动等待，等待IoC/DI的容器获取一个C的实例，然后反向的注入到A类中，叫做反转。<br/>
  
  图解：
  
  ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/1561401590ewrew.jpg)
  
  ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/1561401640hjk.jpg)
  
 - 1.4 总结
 >其实IoC/DI对编程带来的最大改变不是从代码上，而是从思想上，发生了“主从换位”的变化。应用程序原本是老大，<br/>
 要获取什么资源都是主动出击，但是在IoC/DI思想中，应用程序就变成被动的了，被动的等待IoC/DI容器来创建并注入它所需要的资源了。
  
  #### 2 代码演示
  
  - 痛点：如果实例化A需要对象B，也就是A与B有强关联的关系，B的变化会影响A。
  ```php
<?php
class B
{
    public function bsay()
    {
        echo "b say";
    }
}

class A
{
    public function asay()
    {
        $b_obj = new B;
        $b_obj->bsay();
    }
}
 $a_obj = new A;
 $a_obj->asay();
  ```
  - 通过构造函数来将B对象注入到A，使得A和B解耦，就是B的类名和内部属性方法改变不影响这个程序。
   ```php
 <?php
 class B
 {
     public function bsay()
     {
         echo "b say";
     }
 }
 
 class A
 {
     private $_bobj;
     public function __construct($b_obj)
     {
         $this->_bobj = $b_obj;
     }
     public function asay()
     {
         $this->_bobj->bsay();
     }
 }
 $b_obj = new B;
 $a_obj = new A($b_obj);
 $a_obj->asay();
 ?>
 ``` 
 >但是如果A需要的资源不只是B的对象，还有C，D...这个时候，你就需要在构造函数写很多变量来接收这个资源，显然，这是不合理的。这个<br/>
 时候需要一个专门的容器来存储所要需要的资源。
 
 - 申明一个DI来存储需要注入的资源，以便全局位置可以使用这些资源，并且可以存储C，D...等需要的所有依赖资源（对象，数组...）。
 
 ```php
  <?php
  class DI
  {
      public $b;
      public $c;
      public $d;
      public function __construct()
      {
          $this->c = new C();
          $this->d = new D();
          $this->b = new B();
      }
  }
  class B
  {
      public function bsay()
      {
          echo "b say";
      }
  }
  class C
  {
    public function csay()
    {
        echo "c say";
    }
  }
  class D
  {
      public function dsay()
      {
          echo "d say";
      }
  }
  
  class A
  {
      private $_bobj;
      public function __construct($b_obj)
      {
          $this->_bobj = $b_obj;
      }
      public function asay()
      {
          $this->_bobj->bsay();
      }
  }
  $di_obj = new DI();
  $a_obj = new A($di_obj->b);
  $a_obj->asay();
  ?>
  ```
  >以上基本能满足如何使用依赖注入解决我们的问题。不是在代码内部创建依赖关系，而是让其作为一个参数传递，这使得我们的程序更容易维<br/>
  护，降低程序代码的耦合度，实现一种松耦合。但是从长远来看，这种形式的依赖注入也有一些缺点。例如，如果组件中有较多的依赖关系，<br/>
  我们需要创建多个setter方法传递，或创建构造函数进行传递。另外，每次使用组件时，都需要创建依赖组件，使代码维护不太易。我们需要修改<br/>
  一下DI这个容器类。
  
 ```php
<?php

class Di
{
    private $container = array();
    public function set($key, $obj,$arg)
    {
        if(count($arg) == 1 && is_array($arg[0])){
            $arg = $arg[0];
        }
        /*
         * 注入的时候不做任何的类型检测与转换
         * 由于编程人员为问题，该注入资源并不一定会被用到
         */
        $this->container[$key] = array(
            "obj"=>$obj,
            "params"=>$arg,
        );
    }

    function delete($key)
    {
        unset( $this->container[$key]);
    }

    function clear()
    {
        $this->container = array();
    }

    function get($key)
    {
        if(isset($this->container[$key])){
            $result = $this->container[$key];
            if(is_object($result['obj'])){
                return $result['obj'];
            }else if(is_callable($result['obj'])){
                return $this->container[$key]['obj'];
            }else if(is_string($result['obj']) && class_exists($result['obj'])){
                $reflection = new \ReflectionClass ( $result['obj'] );
                $ins =  $reflection->newInstanceArgs ( $result['params'] );
                $this->container[$key]['obj'] = $ins;
                return $this->container[$key]['obj'];
            }else{
                return $result['obj'];
            }
        }else{
            return null;
        }
    }
}
class B
{
    public function bsay()
    {
        echo "b say";
    }
}

class A
{
    private $_bobj;
    public function __construct($b_obj)
    {
        $this->_bobj = $b_obj;
    }
    public function asay()
    {
        $this->_bobj->bsay();
    }
}
$di_obj = new DI();
$di_obj->set('b_obj','B');
$a_obj = new A($di_obj->get('b_obj'));
$a_obj->asay();
?>
 ```
 >现在，该Di类只有访问某种service的时候才需要它，如果它不需要，它甚至不初始化，以节约资源。该组件是高度解耦。他们的行为，或者说他们的任<br/>
 何其他方面都不会影响到组件本身。<br/>
 >优点：<br/>
 >(1)我们可以更换一个依赖资源，从他们本身或者第三方轻松创建。<br/>
 >(2)我们可以充分的控制对象的初始化，并对对象进行各种设置。<br/>
 >(3)我们可以使用统一的方式从容器中，得到一个结构化的全局实例。<br/>
 >(4)DI类。除非开发人员在注入服务的时候直接实例化一个对象，然后存存储到容器中。在容器中，通过数组，字符串等方式存储的服务都将被延迟加载，即只有在请求对象的时候才被初始化。<br/>