<?php

namespace Orange\Container\Support;

use Orange\Container\ContainerInterface;

/**
 * Class ContainerAware
 *
 * @package Orange\Container
 */
trait ContainerAware
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}