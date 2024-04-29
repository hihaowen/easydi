<?php

namespace DI\Test\UnitTest;

use PHPUnit\Framework\TestCase;

class FunctionTest extends TestCase
{
    /**
     * @covers ::\DI\foo
     */
    public function testFoo()
    {
        $result = \DI\foo('foo');

        $this->assertSame('foo', $result);
    }
}
