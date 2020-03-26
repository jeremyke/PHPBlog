<?php
/**
 * Description:php5大设计模式
 * User: Jeremy.Ke
 * Time: 2019/4/3 10:26
 */

/**
 * 单例模式
 * 应用场景：程序应用中，涉及到数据库操作时，如果每次操作的时候连接数据库，会带来大量的资源消耗。可以通过单例模式，创建唯一的数据库连接对象。
 * 特点：三私一公：私有的静态变量（存放实例），私有的构造方法（防止创建实例），私有的克隆方法(防止克隆对象)，公有的静态方法（对外界提供实例）
 */
class Singleton
{
    private static $_instance;
    private function __construct(){}
    private function __clone(){}
    public static function getInstance()
    {
        if(!self::$_instance instanceof Singleton){//instanceof 判断一个实例是否是某个类的对象
            self::$_instance = new Singleton();
        }
        return self::$_instance;
    }
}

/**
 * 工厂模式
 * 特点：将调用对象与创建对象分离,调用者直接向工厂请求,减少代码的耦合.提高系统的可维护性与可扩展性。
 * 应用场景：提供一种类，具有为您创建对象的某些方法，这样就可以使用工厂类创建对象，而不直接使用new。这样如果想更改创建的对象类型，只需更改该工厂即可。
 */
//假设3个待实例化的类
class Aclass
{

}
class Bclass
{

}
class Cclass
{

}
class Factory
{
    //定义每个类的类名
    const ACLASS = 'Aclass';
    const BCLASS = 'Bclass';
    const CCLASS = 'Cclass';
    public static function getInstance($newclass)
    {
        $class = $newclass;//真实项目中这里常常是用来解析路由，加载文件。
        return new $class;
    }
}//调用方法：Factory::getInstance(Factory::ACLASS);

/**
 * 注册树模式
 * 特点：注册树模式通过将对象实例注册到一棵全局的对象树上，需要的时候从对象树上采摘的模式设计方法。
 * 应用：不管你是通过单例模式还是工厂模式还是二者结合生成的对象，都统统给我“插到”注册树上。我用某个对象的时候，直接从注册树上取一下就好。
 * 这和我们使用全局变量一样的方便实用。而且注册树模式还为其他模式提供了一种非常好的想法。
 * (如下实例是单例，工厂，注册树的联合使用)
 */
//创建单例
class Single{
    public $hash;
    static protected $ins=null;
    final protected function __construct(){
        $this->hash=rand(1,9999);
    }

    static public function getInstance(){
        if (!self::$ins instanceof self) {
            return self::$ins;
        }
        self::$ins=new self();
        return self::$ins;
    }
}

//工厂模式
class RandFactory{
    public static function factory(){
        return Single::getInstance();
    }
}

//注册树
class Register{
    protected static $objects;
    public static function set($alias,$object){
        self::$objects[$alias]=$object;
    }
    public static function get($alias){
        return self::$objects[$alias];
    }
    public static function _unset($alias){
        unset(self::$objects[$alias]);
    }
}
//调用
//Register::set('rand',RandFactory::factory());
//$object=Register::get('rand');
//print_r($object);

/**
 * 策略模式
 * 定义：定义一系列算法，将每一个算法封装起来，并让它们可以相互替换。策略模式让算法独立于使用它的客户而变化。
 * 特点：策略模式提供了管理相关的算法族的办法； 策略模式提供了可以替换继承关系的办法；使用策略模式可以避免使用多重条件转移语句。
 * 应用场景： 多个类只区别在表现行为不同，可以使用Strategy模式，在运行时动态选择具体要执行的行为。比如上学，有多种策略：走路，公交，地铁...
 */
abstract class Strategy
{
    abstract function goSchool();
}
class Run extends Strategy
{
    public function goSchool()
    {
        // TODO: Implement goSchool() method.
    }
}
class Subway extends Strategy
{
    public function goSchool()
    {
        // TODO: Implement goSchool() method.
    }
}
class Bike extends Strategy
{
    public function goSchool()
    {
        // TODO: Implement goSchool() method.
    }
}
class Context
{
    protected $_stratege;//存储传过来的策略对象
    public function goSchoole()
    {
        $this->_stratege->goSchoole();
    }
}
//调用：
//$contenx = new Context();
//$avil_stratery = new Subway();
//$contenx->goSchoole($avil_stratery);

/**
 * 适配器模式
 * 特点：将各种截然不同的函数接口封装成统一的API。
 * 应用：PHP中的数据库操作有MySQL,MySQLi,PDO三种，可以用适配器模式统一成一致，使不同的数据库操作，统一成一样的API。
 *      类似的场景还有cache适配器，可以将memcache,redis,file,apc等不同的缓存函数，统一成一致。
 */
abstract class Toy
{
    public abstract function openMouth();

    public abstract function closeMouth();
}

class Dog extends Toy
{
    public function openMouth()
    {
        echo "Dog open Mouth\n";
    }

    public function closeMouth()
    {
        echo "Dog close Mouth\n";
    }
}

class Cat extends Toy
{
    public function openMouth()
    {
        echo "Cat open Mouth\n";
    }

    public function closeMouth()
    {
        echo "Cat close Mouth\n";
    }
}


//目标角色（红）
interface RedTarget
{
    public function doMouthOpen();

    public function doMouthClose();
}

//目标角色（绿）
interface GreenTarget
{
    public function operateMouth($type = 0);
}


//类适配器角色（红）
class RedAdapter implements RedTarget
{
    private $adaptee;

    function __construct(Toy $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    //委派调用Adaptee的sampleMethod1方法
    public function doMouthOpen()
    {
        $this->adaptee->openMouth();
    }

    public function doMouthClose()
    {
        $this->adaptee->closeMouth();
    }
}

//类适配器角色（绿）
class GreenAdapter implements GreenTarget
{
    private $adaptee;

    function __construct(Toy $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    //委派调用Adaptee：GreenTarget的operateMouth方法
    public function operateMouth($type = 0)
    {
        if ($type) {
            $this->adaptee->openMouth();
        } else {
            $this->adaptee->closeMouth();
        }
    }
}



class testDriver
{
    public function run()
    {
        //实例化一只狗玩具
        $adaptee_dog = new Dog();
        echo "给狗套上红枣适配器\n";
        $adapter_red = new RedAdapter($adaptee_dog);
        //张嘴
        $adapter_red->doMouthOpen();
        //闭嘴
        $adapter_red->doMouthClose();
        echo "给狗套上绿枣适配器\n";
        $adapter_green = new GreenAdapter($adaptee_dog);
        //张嘴
        $adapter_green->operateMouth(1);
        //闭嘴
        $adapter_green->operateMouth(0);
    }
}
//调用
//$test = new testDriver();
//$test->run();

/**
 * 观察者模式
 * 特点:观察者模式(Observer)，当一个对象状态发生变化时，依赖它的对象全部会收到通知，并自动更新。观察者模式实现了低耦合，非侵入式的通知与更新机制。
 * 应用：一个事件发生后，要执行一连串更新操作。传统的编程方式，就是在事件的代码之后直接加入处理的逻辑。当更新的逻辑增多之后，代码会变得难以维护。
 * 这种方式是耦合的，侵入式的，增加新的逻辑需要修改事件的主体代码。
 */
// 主题接口
interface Subject{
    public function register(Observer $observer);
    public function notify();
}
// 观察者接口
interface Observer{
    public function watch();
}
// 主题
class Action implements Subject{
    public $_observers=[];
    public function register(Observer $observer){
        $this->_observers[]=$observer;
    }

    public function notify(){
        foreach ($this->_observers as $observer) {
            $observer->watch();
        }

    }
}

// 观察者
class Cat1 implements Observer{
    public function watch(){
        echo "Cat1 watches TV<hr/>";
    }
}
 class Dog1 implements Observer{
     public function watch(){
         echo "Dog1 watches TV<hr/>";
     }
 }
 class People implements Observer{
     public function watch(){
         echo "People watches TV<hr/>";
     }
 }
// 调用实例
//$action=new Action();
//$action->register(new Cat1());
//$action->register(new People());
//$action->register(new Dog1());
//$action->notify();





