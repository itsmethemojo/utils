<?php

namespace Itsmethemojo\Error;

use Exception;

class HandlerFunctions
{
    public function throwAllErrorsAsExceptions($errno, $errstr, $errfile, $errline)
    {
        throw new Exception($errstr);
    }
}