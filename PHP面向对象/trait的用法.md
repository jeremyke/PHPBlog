 ## PHP中trait使用方法
 
 - 1.PHP中使用trait关键字是为了解决一个类既想集成基类的属性和方法，又想拥有别的基类的方法，而trait一般情况下是和use搭配使用的。
 ```php
<?php
  trait Drive {
    public $carName = 'trait';
    public function driving() {
      echo "driving {$this->carName}\n";
    }
  }
  class Person {
    public function eat() {
      echo "eat<br/>";
    }
  }
  class Student extends Person {
    use Drive;
    public function study() {
      echo "study<br/>";
    }
  }
  $student = new Student();
  $student->study();
  $student->eat();
  $student->driving();
 
?>
 ```
 输出结果为：
 ```
 study
 eat
 driving trait
 ```
 >上面的例子中，Student类通过继承Person，有了eat方法，通过组合Drive，有了driving方法和属性carName。
 
 - 2.如果Trait、基类和本类中都存在某个同名的属性或者方法，最终会保留哪一个呢？
 
 ```php
 <?php
   trait Drive {
     public function hello() {
       echo "hello drive\n";
     }
     public function driving() {
       echo "driving from drive<br/>";
     }
   }
   class Person {
     public function hello() {
       echo "hello person<br/>";
     }
     public function driving() {
       echo "driving from person<br/>";
     }
   }
   class Student extends Person {
     use Drive;
     public function hello() {
       echo "hello student<br/>";
     }
   }
   $student = new Student();
   $student->hello();
   $student->driving();
   
 ?>
 ```
  输出结果为：
  ```
hello student
driving from drive
  ```
  >因此得出结论：当方法或属性同名时，当前类中的方法会覆盖 trait的 方法，而 trait 的方法又覆盖了基类中的方法。
  
 - 3.如果要组合多个Trait，通过逗号分隔 Trait名称
 >use Trait1, Trait2;
 
 - 4.如果多个Trait中包含同名方法或者属性时，会怎样呢？答案是当组合的多个Trait包含同名属性或者方法时，需要明确声明解决冲突，否则会产生一个致命错误。
 ```php
<?php
trait Trait1 {
  public function hello() {
    echo "Trait1::hello<br/>";
  }
  public function hi() {
    echo "Trait1::hi<br/>";
  }
}
trait Trait2 {
  public function hello() {
    echo "Trait2::hello<br/>";
  }
  public function hi() {
    echo "Trait2::hi<br/>";
  }
}
class Class1 {
  use Trait1, Trait2;
}
?>
 ```
 以上代码会报错。
 使用insteadof和as操作符来解决冲突，insteadof是使用某个方法替代另一个，而as是给方法取一个别名，具体用法请看代码：
 
 ```php
<?php
trait Trait1 {
  public function hello() {
    echo "Trait1::hello<br/>";
  }
  public function hi() {
    echo "Trait1::hi<br/>";
  }
}
trait Trait2 {
  public function hello() {
    echo "Trait2::hello<br/>";
  }
  public function hi() {
    echo "Trait2::hi<br/>";
  }
}
class Class1 {
  use Trait1, Trait2 {
    Trait2::hello insteadof Trait1;
    Trait1::hi insteadof Trait2;
  }
}
class Class2 {
  use Trait1, Trait2 {
    Trait2::hello insteadof Trait1;
    Trait1::hi insteadof Trait2;
    Trait2::hi as hei;
    Trait1::hello as hehe;
  }
}
$Obj1 = new Class1();
$Obj1->hello();
$Obj1->hi();
echo "<br/>";
$Obj2 = new Class2();
$Obj2->hello();
$Obj2->hi();
$Obj2->hei();
$Obj2->hehe();
?>
```
输出结果如下：
``` 
Trait2::hello
Trait1::hi
 
Trait2::hello
Trait1::hi
Trait2::hi
Trait1::hello

```
 - 5 as关键词还有另外一个用途，那就是修改方法的访问控制：Trait 也能组合Trait，Trait中支持抽象方法、静态属性及静态方法，测试代码如下：
 ```php
<?php
trait Hello {
  public function sayHello() {
    echo "Hello<br/>";
  }
}
trait World {
  use Hello;
  public function sayWorld() {
    echo "World<br/>";
  }
  abstract public function getWorld();
  public function inc() {
    static $c = 0;
    $c = $c + 1;
    echo "$c<br/>";
  }
  public static function doSomething() {
    echo "Doing something<br/>";
  }
}
class HelloWorld {
  use World;
  public function getWorld() {
    return 'get World';
  }
}
$Obj = new HelloWorld();
$Obj->sayHello();
$Obj->sayWorld();
echo $Obj->getWorld() . "<br/>";
HelloWorld::doSomething();
$Obj->inc();
$Obj->inc();
?>
```
输出结果如下：
```php
Hello
World
get World
Doing something
1
2
```
 
 
 