<?php

namespace DI\Definitions;

use DI\Definition;

class ClosureDefinition implements Definition
{
    /**
     * 名称
     *
     * @var string
     */
    protected $name;

    /**
     * @var string|\Closure
     */
    protected $closure;

    /**
     * 是否能被共享
     *
     * @var bool
     */
    protected $isShareable;

    /**
     * 成员方法参数
     *
     * @var array
     */
    protected $parameters = [];

    public function __construct(string $name, $closure, bool $isShareable = false)
    {
        $this->name = $name;

        $this->closure = $closure;

        $this->isShareable = $isShareable;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isShareable(): bool
    {
        return $this->isShareable;
    }

    public function closure()
    {
        return $this->closure;
    }

    public function setParameter(string $key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}