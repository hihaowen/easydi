<?php

namespace DI;

use DI\Exception\NotFoundException;

class DefinitionPool
{
    private static $pool = [];

    /**
     * @param Definition $definition
     *
     * @return void
     */
    public function add(Definition $definition)
    {
        self::$pool[$definition->name()] = $definition;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, self::$pool);
    }

    /**
     * @param string $name
     *
     * @return Definition
     * @throws NotFoundException
     */
    public function get(string $name)
    {
        if ($this->has($name)) {
            return self::$pool[$name];
        }

        throw new NotFoundException('未定义的Definition: '.$name);
    }
}