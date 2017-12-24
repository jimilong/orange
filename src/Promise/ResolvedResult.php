<?php
namespace Orange\Promise;

class ResolvedResult extends Result
{
    public function __construct($value)
    {
        parent::__construct($value, State::REJECTED);
    }
}