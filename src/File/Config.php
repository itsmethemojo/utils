<?php

namespace Itsmethemojo\File;

use Exception;
use Itsmethemojo\File\Path;

class Config
{

    public static function get($key, $mustHaveKeys = array())
    {

        $filePath = Path::getProjectRoot().'/config/'.$key.'.ini';

        if (!file_exists($filePath)) {
            throw new Exception('missing config file \'' . $key . '.ini\'');
        }

        $data = parse_ini_file($filePath);
        foreach ($mustHaveKeys as $mustHaveKey) {
            if (!array_key_exists($mustHaveKey, $data)) {
                throw new Exception('config file \'' . $key . '.ini\' must have key \'' . $mustHaveKey . '\'');
            }
        }

        return $data;
    }
}
