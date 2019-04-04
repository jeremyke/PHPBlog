<?php
class Aclass
{

}
class Bclass
{

}
class Cclass
{

}
class FileToClass
{
    //定义每个类的类名
    const ACLASS = 'Aclass';
    const BCLASS = 'Bclass';
    const CCLASS = 'Cclass';
}
class Factory
{
    public function getInstance($newclass)
    {
        $class = $newclass;
        return new $class;
    }
}
$aa = new Factory();
$aa->getInstance(FileToClass::ACLASS);
?>