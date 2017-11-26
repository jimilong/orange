<?php

namespace Orange\Container;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Class DependDetection
 *
 * @package Orange\Container
 */
class DependDetection
{
    /**
     * @param $obj
     * @param $method
     * @return array
     */
    public static function detectionObjectArgs($obj, $method)
    {
        $reflection = new ReflectionMethod($obj, $method);
        return static::detectionArgs($reflection);
    }

    /**
     * @param Closure $closure
     * @return array
     */
    public static function detectionClosureArgs(Closure $closure)
    {
        $reflection = new ReflectionFunction($closure);
        return static::detectionArgs($reflection);
    }

    /**
     * @param ReflectionFunctionAbstract $reflectionFunctionAbstract
     * @return array
     */
    public static function detectionArgs(ReflectionFunctionAbstract $reflectionFunctionAbstract)
    {
        if (0 >= $reflectionFunctionAbstract->getNumberOfParameters()) {
            unset($reflectionFunctionAbstract);
            return [];
        }

        $args = [];
        foreach ($reflectionFunctionAbstract->getParameters() as $index => $parameter) {;
            if (($class = $parameter->getClass()) instanceof ReflectionClass) {
                $args[$index] = $class->getName();
            }
        }
        unset($reflectionFunctionAbstract);
        return $args;
    }
}