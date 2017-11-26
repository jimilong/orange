<?php

namespace Orange\Container;

/**
 * Interface FactoryInterface
 *
 * @package Orange\Container
 */
interface FactoryInterface
{
    /**
     * @param array $arguments
     * @return mixed
     */
    public function make(array $arguments = []);
}