<?php

namespace DI\Invokers;

use DI\Invoker;
use DI\ParameterResolver;
use DI\Exception\UnsupportedException;
use Psr\Container\ContainerInterface;

class ClosureInvoker implements Invoker
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
     * @var string|\Closure
     */
    protected $closure;

    /**
     * @var string
     */
    protected $method;

    public function __construct(
        ContainerInterface $container,
        ParameterResolver $parameterResolver,
        $closure
    ) {
        if (!is_string($closure) && (!$closure instanceof \Closure)) {
            throw new UnsupportedException('传入的参数必须是字符串或闭包类型');
        }

        if (is_string($closure) && !function_exists($closure)) {
            throw new UnsupportedException('该函数不存在: '.$closure);
        }

        $this->container         = $container;
        $this->parameterResolver = $parameterResolver;
        $this->closure           = $closure;
    }

    public function call(array $parameters = [])
    {
        $functionReflection = new \ReflectionFunction($this->closure);

        return $functionReflection->invokeArgs(
            $this->parameterResolver->resolveParameters($functionReflection, $parameters)
        );
    }
}
