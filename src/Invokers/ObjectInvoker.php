<?php

namespace DI\Invokers;

use DI\Exception\UnsupportedException;
use DI\Invoker;
use DI\ParameterResolver;
use Psr\Container\ContainerInterface;

class ObjectInvoker implements Invoker
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ParameterResolver
     */
    protected $parameterResolver;

    /**
     * @var object|string
     */
    protected $class;

    /**
     * @var string
     */
    protected $method;

    public function __construct(
        ContainerInterface $container,
        ParameterResolver $parameterResolver,
        $class,
        string $method
    ) {
        if (!is_object($class) && !is_string($class)) {
            throw new UnsupportedException('传入的参数必须是对象或字符串类型');
        }

        if (!method_exists($class, $method)) {
            throw new UnsupportedException('该方法不存在: '.(is_object($class) ? get_class($class) : $class).'::'.$method);
        }

        $this->container         = $container;
        $this->parameterResolver = $parameterResolver;
        $this->class             = $class;
        $this->method            = $method;
    }

    public function call(array $parameters = [])
    {
        $methodReflection = new \ReflectionMethod($this->class, $this->method);

        return $methodReflection->invokeArgs(
            $methodReflection->isStatic()
                ? null
                : (is_string($this->class) ? $this->container->get($this->class) : $this->class),
            $this->parameterResolver->resolveParameters($methodReflection, $parameters)
        );
    }
}