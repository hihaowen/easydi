<?php

namespace DI;

interface Definition
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * 是否能被共享
     *
     * @return bool
     */
    public function isShareable(): bool;
}