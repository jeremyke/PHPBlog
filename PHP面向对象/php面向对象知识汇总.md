 ## 1.$this,self,parent，static详解？
 ```text
 $this:到主叫对象（被实例化的对象即为主叫对象）的引用，一些可以继承的，重写的属性，方法，都会指向类Son ，而私有的属性，方法还是调用$this说在方法的所属类。
 self:指当前类，写在哪个类里面就指哪个类。只能访问类的成员。比如可以用于访问类的静态属性、静态方法和常量。
 static：可以用于静态或非静态方法中，也可以访问类的静态属性、静态方法、常量和非静态方法，但不能访问非静态属性
 parent: 当前类的父类。只能访问类的成员。
 ```
 ## 2.面向对象的三大特性是指封装性，继承性，多态性
 
 ##### 2.1 封装性
 >封装就是把抽象出的数据和对数据的操作封装在一起，数据被保护在内部，程序的其他部分只有通过被授权的操作（成员方法）才能对数据进行操作。
 - 访问修饰符：
 ```text
（1）public：表示全局的(本类，之类，类外部)都可以访问 。
（2）protected:表示在本类，之类可以访问。
（3）private:只有在本类可以访问。
 ```
 - 关于封装中的魔术方法__set(),__get(),__isset(),__unset()
 ```text
  __set():给非公有属性赋值时,会自动调用此方法(必须在类中先声明__set())；
  __get():在获取非公有属性时,会自动调用此方法(必须在类中先声明__get())；
  __isset($propertyName)检测非公有属性是否存.(这是一个辅助检测函数,没有检测功能),还要在外部调用isset()函数时,才会自动调用此方法.
  __unset($propertyName)删除一个对象的非公有属性,要在外部调用unset()函数时,才会自动调用此方法.
 ```
 ##### 2.2 继承性
 >建立一个新的派生类，从一个先前定义的类中集成数据和函数，而且可以重新定义或加进新数据和函数，从而建立了类的层次或等级关系。子类可以从父类
 继承所有的内容，但是私有的属性或者方法是只能在子类中被调用。
 多态性增强了代码的复用性和维护性。
 
 - 方法重写
 >在子类中可以定义和父类同名的方法，因为父类的方法已经在子类中存在，这样在子类中就可以把从父类中继承过来的方法重写。
 ```text
 (1)在子类中调用父类被覆盖方法：
    A、对象->成员  类::成员;
    B、父类名::方法名();
    C、Parent::方法名();
 (2)在子类中编写构造方法，如果父类中也有构造方法，，一定要去调用一次父类中被覆盖的那个构造方法
 (3)类中重载的方法，不能低于父类中的访问权限
 ```
 - 重载
 ```text
 对所有的属性操作可用__set(),__get(),__isset(),__unset()；对所有的方法操作__call(),__callstic()
 ```
 
 ##### 2.3 多态性
 >多态性是指相同的操作或函数、过程可作用于同一个类的多种类型的对象上并获得不同的结果。也即不同的对象，收到同一消息将可以产生不同的结果，这种现象称为多态性。
 多态性增强了软件的灵活性和重用性。
 
 ## 3.PHP自动加载？
 
 当我们在使用一个类时，如果发现这个类没有加载，就会自动运行 __autoload() 函数，这个函数是我们在程序中自定义的，在这个函数中我们可以加载需要使用的类。
 ```php
 <?php
 
 function __autoload($classname) {
         require_once ($classname . ".class.php");
 }
 ```

 __autoload函数的问题：
 ```text
  （1）如果系统中类名和磁盘文件的映射关系不尽相同，就必须在__autoload函数中实现所有的映射规则，效率和可维护性不太好，
  （2） __autoload() 是全局函数只能定义一次，所有的类名与文件名对应的逻辑规则都要在一个函数里面实现，造成这个函数的臃肿。
 ``` 
 解决方案：
 使用一个 __autoload调用堆栈 ，不同的映射关系写到不同的 __autoload函数 中去，然后统一注册统一管理，这个就是 PHP5 引入的 SPL Autoload 。
 
 spl_autoload：PHP标准自动加载
 
 ```php
 <?php
 function my_autoloader($class) {
     include 'classes/' . $class . '.class.php';
 }
 //直接注册一个普通加载函数
 spl_autoload_register('my_autoloader');
 
 
 // 定义的 autoload 函数在 class 里
 // 静态方法
 class MyClass {
   public static function autoload($className) {
     // ...
   }
 }
 spl_autoload_register(MyClass::autoload);
 
 // 非静态方法
 class MyClass2 {
   public function autoload($className) {
     // ...
   }
 }
 $instance = new MyClass2();
 spl_autoload_register(array($instance, 'autoload'));
 
 ```
 spl_autoload_register() 就是我们上面所说的__autoload调用堆栈，我们可以向这个函数注册多个我们自己的 autoload() 函数，当 PHP 找不到类名时，
 PHP就会调用这个堆栈，然后去调用自定义的 autoload() 函数，实现自动加载功能。如果我们不向这个函数输入任何参数，那么就会默认注册 spl_autoload() 函数。
