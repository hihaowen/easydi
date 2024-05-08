<?php

namespace DI\Test\UnitTest;

use DI\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testHasNonDogReturn()
    {
        $this->expectException(\InvalidArgumentException::class);

        $container = new Container();
        $container->returnFakeDog();
    }
}
