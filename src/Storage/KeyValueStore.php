<?php

namespace Itsmethemojo\Storage;

use Redis;
use Exception;

class KeyValueStore
{

    private $redis = null;
    private $config = null;

    public function setConfig($config)
    {
        if (!array_key_exists('host', $config)
            || !array_key_exists('port', $config)
            || !array_key_exists('prefix', $config)
        ) {
            throw new Exception("incomplete redis config");
        }
        $this->config = $config;
        return $this;
    }

    public function connect()
    {
        $this->redis = new Redis();
        $this->redis->connect($this->config['host'], $this->config['port']);
        return $this;
    }

    public function setComplex($key, $value, $ttl = 0)
    {
        //because redis cant cache objects we have to encode shit
        $toSave = json_encode($this->config['prefix'].$key);
        return $this->set($toSave, $value, $ttl);
    }

    public function getComplex($key)
    {
        return json_decode($this->get($key), true);
    }

    public function set($key, $value, $ttl = 0)
    {
        if ($ttl==0) {
            //TODO everything should expire? or?
            $ttl = 24*60*60;
        }
        return $this->redis->setex($this->config['prefix'].$key, $ttl, $value);
    }

    public function get($key)
    {
        return $this->redis->get($this->config['prefix'].$key);
    }

    public function mGet($keys)
    {
        return $this->redis->mGet($keys);
    }

    public function incr($key)
    {
        return $this->redis->incr($key);
    }
}
