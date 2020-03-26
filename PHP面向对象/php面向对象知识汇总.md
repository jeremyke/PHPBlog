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
 >子类可以从父类继承所有的内容，但是私有的属性或者方法是不能在子类中被调用。
 
 - 方法重写
 >在子类中可以定义和父类同名的方法，因为父类的方法已经在子类中存在，这样在子类中就可以把从父类中继承过来的方法重写。
 ```text
 (1)在子类中调用父类被覆盖方法：
    A、对象->成员  类::成员;
    B、父类名::方法名();
    C、Parent::方法名();
 (2)在子类中编写构造方法，如果父类中也有构造方法，，一定要去调用一次父类中被覆盖的那个构造方法
 (3)类中重写的方法，不能低于父类中的访问权限
 ```
 - 重载
 
 同一个类中的多个方法可以有相同的方法名称，但是有不同的参数列表，这就称为方法重载,php中不存在。
 
 ```text
 对所有的属性操作可用__set(),__get(),__isset(),__unset()；对所有的方法操作__call(),__callstic()
 ```
 
 ##### 2.3 多态性
 >相同的操作或函数作用于同一个类的不同的对象，产生不同的结果，这种现象称为多态性。多态性增强了软件的灵活性和重用性。
 
 ## 3.PHP自动加载？
 
 #### 3.1 __autoload()
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
 
 #### 3.2 spl_autoload：PHP标准自动加载
 
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
 spl_autoload_register(MyClass::autoload);//或者写成：spl_autoload_register(['MyClass','autoload']);
 
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
 
 一个简单的autoload类：
 ```php
 <?php
 class autoloader {
   public static $loader;
   public static function init() {
     if (self::$loader == NULL){
         self::$loader = new self ();
     }
     return self::$loader;
   }
   public function __construct() {
     spl_autoload_register ( array ($this, 'model' ) );
     spl_autoload_register ( array ($this, 'helper' ) );
     spl_autoload_register ( array ($this, 'controller' ) );
     spl_autoload_register ( array ($this, 'library' ) );
   }
   public function library($class) {
     set_include_path ( get_include_path () . PATH_SEPARATOR . '/lib/' );
     spl_autoload_extensions ( '.library.php' );
     spl_autoload ( $class );
   }
   public function controller($class) {
     $class = preg_replace ( '/_controller$/ui', '', $class );
     set_include_path ( get_include_path () . PATH_SEPARATOR . '/controller/' );
     spl_autoload_extensions ( '.controller.php' );
     spl_autoload ( $class );
   }
   public function model($class) {
     $class = preg_replace ( '/_model$/ui', '', $class );
     set_include_path ( get_include_path () . PATH_SEPARATOR . '/model/' );
     spl_autoload_extensions ( '.model.php' );
     spl_autoload ( $class );
   }
   public function helper($class) {
     $class = preg_replace ( '/_helper$/ui', '', $class );
     set_include_path ( get_include_path () . PATH_SEPARATOR . '/helper/' );
     spl_autoload_extensions ( '.helper.php' );
     spl_autoload ( $class );
   }
 }
 //call
 autoloader::init ();
 ?>
 ```
 ## 4.抽象类、接口异同和使用场景
 
 #### 4.1 异同点
 ```text
 （1）什么是接口？
    接口是定义了要做的事情，包含了许多的方法，但没有定义这些方法应该如何做。
 （2）什么是抽象类？
    抽象类表示的是，这个对象是什么。如果某一些类的实现有共通之处，则可以抽象出来一个抽象类，让抽象类实现接口的公用的代码，而那些个性化的方法则由各个子类去实现。 
 （3）相同点：都不能被实例化
 （4）不同点以及使用场景：
    第一点． 接口是抽象类的变体，接口比抽象类更加抽象，接口中所有的方法都是抽象的（使用：把公用的方法提升到抽象类中，然后具体的方法可以留给子类自己实现
 此处经典的应用-模板方法设计模式。所以抽象类可以更好的实现代码的复用。
    第二点． 每个类只能继承一个抽象类，但是可以实现多个接口。
    第三点． 抽象类中不一定都是抽象方法，可以实现部分方法；但是接口中方法必须都是抽象的，必须为public修饰的。
    第四点． 接口中的属性都为为static，而抽象类不是的。
 ```
 
 ## 5. MVC的理解？
 ```text
 用一种把业务逻辑、中心数据、界面显示分离的方法组织代码。将业务逻辑聚集到一个部件里面，在改进和个性化定制界面及用户交互的同时，不需要重新编写业务逻辑。
 它把软件系统分为三个基本部分：
 　　　　模型（Model）：负责存储系统的中心数据。
 　　　　视图（View）：将信息显示给用户（可以定义多个视图）。
 　　　　控制器（Controller）：处理用户输入的信息。负责从视图读取数据，控制用户输入，并向模型发送数据，是应用程序中处理用户交互的部分。负责管理与
 用户交互交互控制。
 视图和控制器共同构成了用户接口。
 ```
 - 优点：
 ```text
    1、分工明确（开发人员可以只关注整个结构中的其中某一层）：使用MVC可以把数据库开发，程序业务逻辑开发，页面开发分开，每一层都具有相同的特征，方便以后的代码维护。
 它使程序员（Java开发人员）集中精力于业务逻辑，界面程序员（HTML和JSP开发人员）集中精力于表现形式上。
    2、松耦合（可以降低层与层之间的依赖）：视图层和业务层分离，这样就允许更改视图层代码而不用重新编译模型和控制器代码，同样，一个应用的业务流程或者业务规则
 的改变只需要改动MVC的模型层即可。因为模型与控制器和视图相分离，所以很容易改变应用程序的数据层和业务规则。 
    3、复用性高（利于各层逻辑的复用）：像多个视图能够共享一个模型，不论你视图层是用flash界面或是wap界面，用一个模型就能处理他们。将数据和业务规则从表示层
 分开，就可以最大化从用代码。
    4、可维护性强（有利于通过工程化、工具化产生管理程序代码）
 ```
 - 缺点
 ```text
    1.完全理解MVC比较复杂：由于MVC模式提出的时间不长，加上同学们的实践经验不足，所以完全理解并掌握MVC不是一个很容易的过程。
    2.调试困难：因为模型和视图要严格的分离，这样也给调试应用程序带来了一定的困难，每个构件在使用之前都需要经过彻底的测试。
    3.不适合小型，中等规模的应用程序：在一个中小型的应用程序中，强制性的使用MVC进行开发，往往会花费大量时间，并且不能体现MVC的优势，同时会使开发变得繁琐。
    4.增加系统结构和实现的复杂性：对于简单的界面，严格遵循MVC，使模型、视图与控制器分离，会增加结构的复杂性，并可能产生过多的更新操作，降低运行效率。
    5.视图与控制器间的过于紧密的连接并且降低了视图对模型数据的访问：视图与控制器是相互分离，但却是联系紧密的部件，视图没有控制器的存在，其应用是很有限的，
 反之亦然，这样就妨碍了他们的独立重用。依据模型操作接口的不同，视图可能需要多次调用才能获得足够的显示数据。对未变化数据的不必要的频繁访问，也将损害操作性能。
 ```
 ## 6. OOP的理解？
 ```text
    面向对象以对象为中心，将对象的内部属性与外部环境区分开来，将表征对象内部属性的方法与外部隔离开来，其行为与属性构成一个整体，因此具有更好的封装性和安全性。
 在编写代码时候以对象为载体去实现系统功能的实现就叫面向对象。由于继承和多态的引入而增强了编程的组织性，重用性和灵活性。面向对象具有三大特征六大基本原则。
 ```
 #### 6.1 面向对象的六大基本原则
 ```text
 单一职责原则:
    一个类应该是一组相关性很高的函数、数据的封装，简单点说：类要职责单一，一个类只需要做好一件事情。
 开闭原则:
    软件中的对象（类、模块、函数等）应该对于扩展是开放的，但是，对于修改是封闭的。
 里氏替换原则:
    子类可以扩展父类的功能，但不能改变父类原有的功能(可以实现父类的抽象方法和增加自己特有的方法，不要覆盖父类的非抽象方法)。
 依赖倒置原则:
    面向接口编程：只需关心接口，不需要关心实现。 
 接口隔离原则：
    建立单一接口，尽量细化接口，接口中的方法尽量少。低耦合高内聚。
 最少知识原则：
    一个类对自己依赖的类知道的越少越好，两实体间最好不交互或少交互。
    
 ```
 
 ## 7. 对象
 
 #### 7.1 foreach 迭代对象？
 
 foreach用法和之前的数组遍历是一样的，只不过这里遍历的key是属性名，value是属性值。在类外部遍历时，只能遍历到public属性的，因为其它的都是受保护的，类外部不可见。
 ```php
 <?php
 foreach ($hardDiskDrive as $property => $value) {
     var_dump($property, $value);
     echo '<br>';
 }
 ```
 如果我们想遍历出对象的所有属性，就需要控制foreach的行为，就需要给类对象，提供更多的功能，需要继承自Iterator的接口。
 
 - 为什么一个类只要实现了Iterator迭代器，其对象就可以被用作foreach的对象呢？
 
 在对PHP实例对象使用foreach语法时，会检查这个实例有没有实现Iterator接口，如果实现了，就会通过内置方法或使用实现类中的方法模拟foreach语句。
 
 ![image](https://github.com/jeremyke/PHPBlog/raw/master/Pictures/3b3de50e098eb76faea856150ba0df0c_720430-20190411154253007-1474559548.png)
 
 ```php
 <?php
 class Team implements Iterator {
     private $info = ['name' => 'itbsl', 'age' => 25, 'hobby' => 'fishing'];
     public function rewind()
     {
         reset($this->info); //重置数组指针
     }
     public function valid()
     {
         //如果为null,表示没有元素，返回false
         //如果不为null,返回true
         return !is_null(key($this->info));
     }
 
     public function current()
     {
         return current($this->info);
     }
 
     public function key()
     {
         return key($this->info);
     }
 
     public function next()
     {
         return next($this->info);
     }
 }
 
 $team = new Team();
 
 foreach ($team as $property => $value) {
 
     var_dump($property, $value);
     echo '<br>';
 }
 
 ```
 ```text
  //输出
   string(4) "name" string(5) "itbsl" 
   string(3) "age" int(25) 
   string(5) "hobby" string(7) "fishing" 
 ```
 #### 7.2 如何数组化操作对象 $obj[key]?
 
 PHP提供了ArrayAccess接口使实现此接口的类的实例可以向操作数组一样通过$obj[key]来操作
 
 ```php
 <?php
 class obj implements arrayaccess{
     private $container = [];
     public function __construct(){
         $this->container = array(
             "one"   => 1,
             "two"   => 2,
             "three" => 3,);
     }
 
    //设置一个偏移位置的值
    public function offsetSet($offset, $value){
         if(is_null($offset)){
             $this->container[] = $value;
         } else {
             $this->container[$offset] = $value;
         }
     }
 
    //检查一个偏移位置是否存在
     public function offsetExists($offset) {
         return isset($this->container[$offset]);
     }
 
    //复位一个偏移位置的值
     public function offsetUnset($offset) {
         unset($this->container[$offset]);
     }
 
    //获取一个偏移位置的值
     public function offsetGet($offset){
         return isset($this->container[$offset]) ? $this->container[$offset]: null;
     }
 }
 //对该类测试使用：
 
 $obj = new obj();
 var_dump(isset($obj["two"]));
 var_dump($obj["two"]);
 unset($obj["two"]);
 var_dump(isset($obj["two"]));
 $obj["two"] = "A value";
 var_dump($obj["two"]);
 $obj[] = 'Append 1';
 $obj[] = 'Append 2';
 $obj[] = 'Append 3';
 print_r($obj);
 ```
 输出
 ```text
 bool(true)
 
 int(2)
 
 bool(false)
 
 string(7) "A value"
 
 obj Object
 
 (
     [container:obj:private] => Array
         (
             [one] => 1
             [three] => 3
             [two] => A value
             [0] => Append 1
             [1] => Append 2
             [2] => Append 3
         )
 )
 ```
 #### 7.3 如何函数化对象 $obj(123)?
 
 利用PHP提供的魔术函数__invoke()方法可以直接实现，当尝试以调用函数的方式调用一个对象时，__invoke() 方法会被自动调用
 
 ```php
 <?php
 class CallableClass
 {
     function __invoke($x) {
         var_dump($x);
     }
 }
 $obj = new CallableClass;
 
 $obj(5);
 var_dump(is_callable($obj));
 ```
 输出：
 ```text
 int(5)
 bool(true)
 ```
 
