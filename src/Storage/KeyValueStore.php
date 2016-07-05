<?php

namespace Itsmethemojo\Storage;

use Redis;
use Itsmethemojo\File\ConfigReader;

class KeyValueStore
{

    private $redis = null;
    private $config = null;

    public static $ttl = 86400; //60*60*24

    public function __construct($configKey)
    {
        $this->config = ConfigReader::get($configKey, array('host', 'prefix'));
        if (!isset($this->config['port'])) {
            $this->config['port'] = 6379;
        }
        return $this;
    }

    public function connect($redisAlreadyConnected = null)
    {
        if (!$redisAlreadyConnected) {
            $this->redis = new Redis();
            $this->redis->connect($this->config['host'], $this->config['port']);
        } else {
            $this->redis = $redisAlreadyConnected;
        }
        return $this;
    }

    public function set($key, $value, $ttl = 0)
    {
        if (is_array($value) || is_object($value)) {
            $value = "json>" . json_encode($value);
        }
        if (!is_numeric($ttl) || $ttl <= 0) {
            $ttl = KeyValueStore::$ttl;
        }
        return $this->redis->setex($this->config['prefix'].$key, $ttl, $value);
    }

    public function get($key)
    {
        $value = $this->redis->get($this->config['prefix'].$key);
        if (substr($value, 0, 5) === "json>") {
            return json_decode(substr($value, 5), true);
        }
        return $value;
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
