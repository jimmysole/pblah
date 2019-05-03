<?php

namespace Forum\Model\Exceptions;


class ForumException extends \Exception
{
    public function __construct($message = null, $code = null, $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }
}