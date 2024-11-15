<?php

namespace DI\Definitions\Resolver;

use DI\Definitions\ClosureDefinition;
use DI\Definitions\DefinitionResolver;

class ClosureCreator implements DefinitionResolver
{
    /**
     * @var ClosureDefinition
     */
    protected $definition;

    public function __construct(ClosureDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function resolve(array $parameters = [])
    {
        $functionReflection = new \ReflectionFunction($this->definition->closure());

        return $functionReflection->getClosure();
    }
}