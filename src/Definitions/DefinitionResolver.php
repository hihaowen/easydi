<?php

namespace DI\Definitions;

interface DefinitionResolver
{
    /**
     * @param array $parameters
     *
     * @return mixed
     */
    public function resolve(array $parameters = []);
}