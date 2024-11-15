<?php

namespace DI\Definitions;

use DI\Definition;

class ObjectDefinition implements Definition
{
    /**
     * 类名
     *
     * @var string
     */
    protected $className;

    /**
     * 是否能被共享
     *
     * @var bool
     */
    protected $isShareable;

    /**
     * 构造方法参数
     *
     * @var array
     */
    protected $constructParameters = [];

    /**
     * 成员方法参数
     *
     * @var array
     */
    protected $methodParameters = [];

    /**
     * 成员属性值集合
     *
     * @var array
     */
    protected $properties = [];

    public function __construct(string $className, bool $isShareable = false)
    {
        $this->className = $className;

        $this->isShareable = $isShareable;
    }

    public function name(): string
    {
        return $this->className;
    }

    public function isShareable(): bool
    {
        return $this->isShareable;
    }

    public function setConstructParameters(array $parameters = [])
    {
        $this->constructParameters = $parameters;

        return $this;
    }

    public function getConstructParameters()
    {
        return $this->constructParameters;
    }

    public function setMethodParameters(string $method, array $parameters = [])
    {
        $this->methodParameters[$method] = $parameters;

        return $this;
    }

    public function getMethodParameters()
    {
        return $this->methodParameters;
    }

    public function setProperty(string $name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }

    public function getProperties()
    {
        return $this->properties;
    }
}