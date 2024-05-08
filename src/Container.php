<?php

namespace DI;

class Container
{
    public function __construct()
    {
    }

    public function returnFakeDog()
    {
        throw new \InvalidArgumentException('i\'m not a dog');
    }
}