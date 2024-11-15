<?php

namespace DI\Test\IntegrationTest\Fixtures;

class Person
{
    private $foo;

    private $name;

    private $age;

    private $sex;

    public function __construct(Foo $foo, $name, $age = 25)
    {
        $this->foo  = $foo;
        $this->name = $name;
        $this->age  = $age;
    }

    public function updateAge($age)
    {
        var_dump('updateAge: '.$age);
        $this->age = $age;
    }

    public function desc($level = 'info')
    {
        return "name: $this->name, age: $this->age, bar: {$this->foo->getBarValue()}, sex: $this->sex, level: $level";
    }

    public static function say($word = 'Hello')
    {
        return "the person say: $word";
    }
}