<?php

namespace Orange\Container;

/**
 * Interface ContainerInterface
 *
 * @package Orange\Container
 */
interface ContainerInterface
{
    /**
     * @param $id
     * @return mixed
     */
    public function get($id);

    /**
     * @param $id
     * @return mixed
     */
    public function has($id);

    /**
     * @param ServiceProviderInterface $serviceProvider
     * @return mixed
     */
    public function register(ServiceProviderInterface $serviceProvider);
}