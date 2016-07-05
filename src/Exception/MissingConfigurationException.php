<?php

namespace Itsmethemojo\Exception;

class MissingConfigurationException extends \Exception
{

    public function __construct(
        $file = "",
        $key = "",
        $code = 0,
        \Exception $previous = null
    ) {
    
        if ($file ==="" && $key === "") {
            parent::__construct("missing configuration", $code, $previous);
        } elseif ($key === "") {
            parent::__construct("missing configuration in \"" . $file . ".ini\"", $code, $previous);
        } else {
            parent::__construct("missing key \"". $key ."\" in \"" . $file . ".ini\"", $code, $previous);
        }
    }
}
