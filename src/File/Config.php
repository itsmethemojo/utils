<?php

namespace Itsmethemojo\File;

use Exception;
use Itsmethemojo\File\Path;

class Config
{

    public static function get($key, $mustHaveKeys = array())
    {

        $filePath = Path::getRootPath().'/config/'.$key.'.ini';

        if (!file_exists($filePath)) {
            throw new Exception($key);
        }

        $data = parse_ini_file($filePath);
        foreach ($mustHaveKeys as $mustHaveKey) {
            if (!array_key_exists($mustHaveKey, $data)) {
                throw new Exception($key, $mustHaveKey);
            }
        }

        return $data;
    }
}
