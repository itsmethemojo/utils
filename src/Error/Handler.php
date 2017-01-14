<?php

namespace Itsmethemojo\Error;

use Itsmethemojo\Error\HandlerFunctions;

class Handler
{
    protected $error;

    function __construct()
    {
        $this->error = new HandlerFunctions();
    }

    function throwAllErrorsAsExceptions(){
        error_reporting(E_ALL | E_STRICT);
        set_error_handler(array($this->error, 'throwAllErrorsAsExceptions'));
    }
}