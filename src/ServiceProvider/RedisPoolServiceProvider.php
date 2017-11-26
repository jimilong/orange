<?php

namespace Orange\ServiceProvider;

use Orange\Container\ServiceProviderInterface;
use Orange\Container\Container;
use Orange\Async\Pool\RedisPool;

class RedisPoolServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the service provider.
     *
     * @return object
     */
    public function register(Container $container)
    {
        $container->add('redisPool', new RedisPool());
    }

    public function getName()
    {
        return 'redisPool';
    }
}