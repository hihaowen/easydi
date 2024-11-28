<?php

namespace DI;

use DI\Definitions\ClosureDefinition;
use DI\Definitions\DefinitionResolver;
use DI\Definitions\ObjectDefinition;
use DI\Definitions\Resolver\ClosureCreator;
use DI\Definitions\Resolver\ObjectCreator;
use DI\Exception\Exception;
use DI\Exception\NotFoundException;
use DI\Exception\UnsupportedException;
use DI\Invokers\ClosureInvoker;
use DI\Invokers\ObjectInvoker;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $sharedEntries = [];

    /**
     * @var DefinitionPool
     */
    protected $definitionPool;

    /**
     * @var ParameterResolver
     */
    protected $parameterResolver;

    /**
     * Container constructor.
     *
     * @param DefinitionPool|null $definitionPool
     */
    public function __construct(DefinitionPool $definitionPool = null)
    {
        $this->definitionPool    = $definitionPool ?: new DefinitionPool();
        $this->parameterResolver = new ParameterResolver($this);

        $this->sharedEntries[self::class]               = $this;
        $this->sharedEntries[ContainerInterface::class] = $this;
    }

    /**
     * @param string|Definition $name
     * @param mixed|null        $value
     *
     * @return $this
     */
    public function set($name, $value = null)
    {
        if ($name instanceof Definition) {
            $this->definitionPool->add($name);
        } else {
            $this->sharedEntries[$name] = $value;
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     * @throws NotFoundException
     * @throws UnsupportedException
     */
    public function get(string $name)
    {
        if (array_key_exists($name, $this->sharedEntries)) {
            return $this->sharedEntries[$name];
        }

        $definition = $this->definitionPool->get($name);

        $value = $this->dispatchCreator($definition)->resolve();

        if ($definition->isShareable()) {
            $this->sharedEntries[$name] = $value;
        }

        return $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        if (array_key_exists($name, $this->sharedEntries)) {
            return true;
        }

        return $this->definitionPool->has($name);
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed
     * @throws Exception
     * @throws NotFoundException
     */
    public function make(string $name, array $parameters = [])
    {
        if (!$this->definitionPool->has($name)) {
            throw new NotFoundException('不存在的条目: '.$name);
        }

        $definition = $this->definitionPool->get($name);

        return $this->dispatchCreator($definition)->resolve($parameters);
    }

    /**
     * 调用
     *
     * @param mixed $callable
     * @param array $parameters
     *
     * @return mixed
     * @throws NotFoundException
     * @throws UnsupportedException
     */
    public function call($callable, array $parameters = [])
    {
        if (is_array($callable) && (count($callable) === 2) && is_string($callable[1])) {
            if (is_object($callable[0]) || is_string($callable[0])) {
                $className = is_string($callable[0]) ? $callable[0] : get_class($callable[0]);
                if ($this->definitionPool->has($className)) {
                    $definition = $this->definitionPool->get($className);
                    if ($definition instanceof ObjectDefinition) {
                        $parameters += $definition->getMethodParameters()[$callable[1]] ?? [];
                    }
                }

                return (new ObjectInvoker($this, $this->parameterResolver, $callable[0], $callable[1]))->call(
                    $parameters
                );
            }
        }

        if (is_string($callable)) {
            if ($this->definitionPool->has($callable)) {
                $definition = $this->definitionPool->get($callable);
                if ($definition instanceof ClosureDefinition) {
                    $parameters += $definition->getParameters();
                }

                return (new ClosureInvoker($this, $this->parameterResolver, $definition->closure()))->call($parameters);
            }
        }

        if ($callable instanceof \Closure) {
            $functionReflection = new \ReflectionFunction($callable);

            return call_user_func_array(
                $callable,
                $this->parameterResolver->resolveParameters($functionReflection, $parameters)
            );
        }

        throw new UnsupportedException('不支持调用的条目: '.print_r($callable, true));
    }

    /**
     * @param Definition $definition
     *
     * @return DefinitionResolver
     * @throws UnsupportedException
     */
    private function dispatchCreator(Definition $definition)
    {
        switch (true) {
            case $definition instanceof ObjectDefinition:
                return new ObjectCreator($this, $definition, $this->parameterResolver);
            case $definition instanceof ClosureDefinition:
                return new ClosureCreator($definition);
            default:
                throw new UnsupportedException('暂不支持的Definition类型: '.get_class($definition));
        }
    }
}