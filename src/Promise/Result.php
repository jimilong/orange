<?php
namespace Orange\Promise;

class Result
{
    private $value;
    private $state;

    public function __construct($value, $state)
    {
        $this->value = $value;
        $this->state = $state;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getState()
    {
        return $this->state;
    }
}