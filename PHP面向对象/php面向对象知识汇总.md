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
     if (self::$loader == NULL)
       self::$loader = new self ();
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
 
 ## MVC的理解？
 ```text
 用一种业务逻辑、数据、界面显示分离的方法组织代码，将业务逻辑聚集到一个部件里面，在改进和个性化定制界面及用户交互的同时，不需要重新编写业务逻辑。
 MVC被独特的发展起来用于映射传统的输入、处理和输出功能在一个逻辑的图形化用户界面的结构中。它把软件系统分为三个基本部分：
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
    4、可维护性搞（有利于通过工程化、工具化产生管理程序代码）
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
 ## OOP的理解？
 ```text
    面向对象以对象为中心，将对象的内部属性与外部环境区分开来，将表征对象的内部属性数据的方法与外部隔离开来，其行为与属性构成一个整体，而系统功能则表现为一系列
 对象之间的相互作用的序列，能更加形象的模拟或表达现实世界。在编程组织中，对象的属性与方法不再像面向过程那样分开存放，而是视为一个整体（程序的最终实现其实还
 是分离的，但这仅仅是物理实现上的，不影响将对象的这两个部分视为一个整体），因此具有更好的封装性和安全性（表征内部的属性数据需要通过对象的提供的方法来访问）。面向对象强调的是整体性，因此面向对象与面向过程在很多方面是可以互补的。同时由于对象继承和多态技术的引入，使得面向对象具有更强、更简洁的对现实世界的表达能力。从而增强了编程的组织性，重用性和灵活性。
 面向对象依然保留着面向过程的特性，面向过程中的功能变成了对象的方法，加工处理功能变成了对象的服务性方法，而这部分方法依然需要外界的输入，同时也对外界进行输
 出，只是输入和输出也变成了对象。在面向对象编程中，大多时候，我们并不需要关心一个对象对象的方方面面，有些对象在整个系统中都是充当“原料”和“成品”的角色，其本
 身的行为并不在我们关心的范围，而另外有些对象处于一种加工厂地位，我们也仅关心这些对象的服务性功能，不需要太多关注对象内部属性和自我行为，针对这些对象关注点
 的不同会对对象进行分类，比如前面提到的两类对象，就是从在系统中所处的角色不同而分类，前者叫实体对象，后者称为操作对象。从方法论来讲，我们可以将面向过程与面
 向对象看做是事物的两个方面--局部与整体（注意：局部与整体是相对的），在实际应用中，两者方法都同样重要。面向过程和面向对象是编程方法中最基本的两种方法，处于
 编程方法体系的底层。
 ```
 
 https://github.com/search?p=2&q=php%E9%9D%A2%E8%AF%95&type=Repositories
 https://github.com/colinlet/PHP-Interview-QA
 https://github.com/wudi/PHP-Interview-Best-Practices-in-China
 https://github.com/duiying/php-notes
 https://github.com/pangg/PHP-Interview-Summary
 https://github.com/lisiqiong/phper/blob/master/oop.md#%E5%AF%B9%E8%B1%A1%E5%BC%95%E7%94%A8
 
 