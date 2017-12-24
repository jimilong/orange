<?php
namespace Orange\Promise;

class RejectedResult extends Result
{
    public function __construct($value)
    {
        parent::__construct($value, State::REJECTED);
    }
}