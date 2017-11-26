<?php

namespace Orange\Container;

/**
 * Interface ServiceProviderInterface
 *
 * @package Orange\Container
 */
interface ServiceProviderInterface
{
    /**
     * @param Container $container
     * @return mixed
     */
    public function register(Container $container);
}