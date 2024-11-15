<?php

namespace DI\Definitions\Resolver;

use DI\Definitions\DefinitionResolver;
use DI\Definitions\EntryReference;
use DI\Definitions\ObjectDefinition;
use DI\ParameterResolver;
use Psr\Container\ContainerInterface;

class ObjectCreator implements DefinitionResolver
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ObjectDefinition
     */
    protected $definition;

    /**
     * @var ParameterResolver
     */
    protected $parameterResolver;

    public function __construct(
        ContainerInterface $container,
        ObjectDefinition $definition,
        ParameterResolver $parameterResolver
    ) {
        $this->container         = $container;
        $this->definition        = $definition;
        $this->parameterResolver = $parameterResolver;
    }

    public function resolve(array $parameters = [])
    {
        // new object
        $parameters += $this->definition->getConstructParameters();
        $object     = $this->parameterResolver->resolveObject($this->definition->name(), $parameters);

        // set properties
        $this->resolveProperties($object);

        return $object;
    }

    protected function resolveProperties($object)
    {
        foreach ($this->definition->getProperties() as $name => $value) {
            $property = new \ReflectionProperty($this->definition->name(), $name);
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }
            $property->setValue(
                $object,
                $value instanceof EntryReference
                    ? $this->container->get($value->getName())
                    : $value
            );
        }
    }
}