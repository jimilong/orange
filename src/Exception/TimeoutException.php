<?php

namespace Orange\Exception;

class TimeoutException extends \Exception
{
    protected $message = 'time out';
    protected $code = 504;
}