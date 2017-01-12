<?php

namespace Itsmethemojo\File;

use Exception;

class Path
{
    public static function getProjectRoot()
    {
        $currentFileDir = __DIR__ . '/';
        if (!strpos($currentFileDir, '/vendor/')) {
            throw new Exception('unusual file strucure, expecting this classfile somewhere in vendor or src folder');
        }
        return preg_split('/\/vendor\//', $currentFileDir, 2)[0];
    }
}
