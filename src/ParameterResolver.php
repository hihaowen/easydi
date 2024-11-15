<?php

namespace DI;

use DI\Definitions\EntryReference;
use DI\Exception\Exception;
use DI\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class ParameterResolver
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolveObject($class, array $parameters = [])
    {
        if (is_string($class)) {
            $className       = $class;
            $classReflection = new \ReflectionClass($className);
        } elseif ($class instanceof \ReflectionClass) {
            $className       = $class->getName();
            $classReflection = $class;
        } else {
            throw new Exception('暂不支持的类型: '.gettype($class));
        }

        if (!class_exists($className)) {
            throw new NotFoundException("不存在的类: {$className}");
        }

        $constructMethod = $classReflection->getConstructor();
        if (!$constructMethod) {
            $object = new $className;
        } else {
            $args   = $this->resolveParameters($constructMethod, $parameters);
            $object = $classReflection->newInstanceArgs($args);
        }

        return $object;
    }

    public function resolveParameters(\ReflectionFunctionAbstract $method, array $parameters)
    {
        $args = [];
        foreach ($method->getParameters() as $parameter) {
            if (array_key_exists($parameter->getName(), $parameters)) {
                $value  = $parameters[$parameter->getName()];
                $args[] = $value instanceof EntryReference
                    ? $this->container->get($value->getName())
                    : $value;
            } elseif (null !== ($parameterClass = $parameter->getClass())) {
                if ($this->container->has($parameterClass->getName())) {
                    $args[] = $this->container->get($parameterClass->getName());
                } else {
                    $args[] = $this->resolveObject($parameterClass);
                }
            } elseif ($parameter->isOptional()) {
                try {
                    $defaultValue = $parameter->getDefaultValue();
                } catch (\ReflectionException $e) {
                    throw new Exception(
                        "该方法 {$method->getName()} 的参数 {$parameter->getName()} 没有设置默认值"
                    );
                }

                $args[] = $defaultValue;
            } else {
                throw new Exception(
                    "{$method->getName()} 的参数 {$parameter->getName()} 没有可用的值来设置"
                );
            }
        }

        return $args;
    }
}