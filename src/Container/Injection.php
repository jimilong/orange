<?php

namespace Orange\Container;

use Orange\Container\Support\ContainerAware;
use ReflectionClass;

/**
 * Class Injection
 *
 * @package Orange\Container
 */
class Injection implements FactoryInterface, InjectionInterface
{
    use ContainerAware;

    /**
     * @var mixed
     */
    protected $object;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var bool
     */
    protected $isStatic = false;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Injection constructor.
     *
     * @param null $service
     */
    public function __construct($service = null)
    {
        if (null !== $service) {
            $this->injectOn($service);
        }
    }

    /**
     * @param $service
     * @return $this
     */
    public function injectOn($service)
    {
        $this->object = $service;

        $this->arguments = [];
        $this->isStatic = false;
        $this->method = null;

        return $this;
    }

    /**
     * @return Injection
     */
    public function withConstruct()
    {
        return $this->withMethod('__construct');
    }

    /**
     * @param $name
     * @return $this
     */
    public function withMethod($name)
    {
        $this->method = $name;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function withStatic($name)
    {
        $this->method = $name;

        $this->isStatic = true;

        return $this;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function withArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @param array $arguments
     * @return object
     */
    public function getInstance(array $arguments = [])
    {
        return (new ReflectionClass($this->object))->newInstanceArgs($arguments);
    }

    /**
     * @param array $arguments
     * @return mixed
     */
    public function make(array $arguments = [])
    {
        if (empty($this->arguments)) {
            if (is_callable($this->object)) {
                $injections = DependDetection::detectionClosureArgs($this->object);
            } else {
                $injections = DependDetection::detectionObjectArgs($this->object, $this->method);
            }

            foreach ($injections as $injection) {
                $this->arguments[] = $this->container->get($injection);
            }
        }

        $arguments = array_merge($this->arguments, $arguments);

        if (is_callable($this->object)) {
            return call_user_func_array($this->object, $arguments);
        }

        if ($this->isStatic) {
            return call_user_func_array($this->object . '::' . $this->method, $arguments);
        }

        if ('__construct' === $this->method) {
            return $this->getInstance($arguments);
        }

        $obj = $this->object;

        if (!is_object($obj)) {
            $obj = new $obj;
        }

        if (empty($this->method)) {
            return $obj;
        }

        return call_user_func_array([$obj, $this->method], $arguments);
    }
}