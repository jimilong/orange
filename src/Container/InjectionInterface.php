<?php

namespace Orange\Container;

/**
 * Interface InjectionInterface
 *
 * @package Orange\Container
 */
interface InjectionInterface
{
    /**
     * @param $instance
     * @return mixed
     */
    public function injectOn($instance);

    /**
     * @param $method
     * @return mixed
     */
    public function withMethod($method);

    /**
     * @param $method
     * @return mixed
     */
    public function withStatic($method);

    /**
     * @param array $arguments
     * @return mixed
     */
    public function withArguments(array $arguments);
}