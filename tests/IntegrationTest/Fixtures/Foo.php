<?php

namespace DI\Test\IntegrationTest\Fixtures;

class Foo
{
    public $bar;

    public function __construct($bar = 'bar1')
    {
        $this->bar = $bar;
    }

    public function getBarValue()
    {
        return $this->bar;
    }
}