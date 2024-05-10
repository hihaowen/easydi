<?php

namespace DI;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    public function __construct()
    {
    }

    public function get(string $id)
    {
        // TODO: Implement get() method.
    }

    public function has(string $id)
    {
        // TODO: Implement has() method.
    }

    public function returnFakeDog()
    {
        throw new \InvalidArgumentException('i\'m not a dog');
    }
}