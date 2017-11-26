<?php

namespace Orange\Container\Exceptions;

class InjectionNotFoundException extends ContainerException
{
    public function __construct($service)
    {
        parent::__construct(sprintf('Injection service "%s" not found', $service));
    }
}