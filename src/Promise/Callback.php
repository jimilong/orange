<?php
/**
 * Created by PhpStorm.
 * User: longmin
 * Date: 17/12/23
 * Time: 下午3:21
 */
namespace Orange\Promise;

class Callback
{
    private $state;
    private $executor;

    public function __construct($state, callable $executor)
    {
        $this->state = $state;
        $this->executor = $executor;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getExecutor()
    {
        return $this->executor;
    }
}