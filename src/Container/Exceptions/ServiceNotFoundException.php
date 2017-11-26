<?php

namespace Orange\Container\Exceptions;

/**
 * Class ServiceNotFoundException
 *
 * @package Orange\Container\Exceptions
 */
class ServiceNotFoundException extends ContainerException
{
    /**
     * ServiceNotFoundException constructor.
     *
     * @param string $service
     */
    public function __construct($service)
    {
        parent::__construct(sprintf('Service "%s" not found', $service));
    }
}