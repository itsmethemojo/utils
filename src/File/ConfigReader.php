<?php

namespace Itsmethemojo\File;

use Exception;

class ConfigReader
{

    public static function get($key, $mustHaveKeys = array())
    {

        $filePath = ConfigReader::getRootPath().'/config/'.$key.'.ini';

        if (!file_exists($filePath)) {
            throw new Exception('missing '.$key.'.ini');
        }

        $data = parse_ini_file($filePath);
        foreach ($mustHaveKeys as $mustHaveKey) {
            if (!array_key_exists($mustHaveKey, $data)) {
                throw new Exception('key '.$mustHaveKey.' is missing in '.$key.'.ini');
            }
        }

        return $data;
    }

    public static function getRootPath()
    {
        $currentFileDir = __DIR__ . '/';
        if (strpos($currentFileDir, '/vendor/')) {
            return preg_split('/\/vendor\//', $currentFileDir, 2)[0];
        } else {
            throw new Exception('unusual file strucure, expecting this classfile somewhere in vendor folder');
        }
    }
}
