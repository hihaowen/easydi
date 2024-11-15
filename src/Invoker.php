<?php

namespace DI;

interface Invoker
{
    /**
     * @param array $parameters
     *
     * @return mixed
     */
    public function call(array $parameters = []);
}