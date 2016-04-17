<?php

namespace Itsmethemojo\Storage;

use Itsmethemojo\File\ConfigReader;
use Itsmethemojo\Storage\QueryParameters;
use Itsmethemojo\Storage\KeyValueStore;
use PDO;
use Exception;

class Database
{

    /** @var PDO **/
    private $database = null;

    /** @var KeyValueStore **/
    private $keyValueStore = null;

    /** @var mixed**/
    private $configuration = array();

    //TODO make configs overwrite-able
    public function __construct()
    {
        //TODO rename that, it is not lazy
        $this->redisLazyLoadConfig();
        $this->redisLazyConnect();
    }

    public function read($tags, $query, QueryParameters $parameters = null, $notSaveIfEmptyResult = false, $ttl = 0)
    {
        if (count($tags) === 0) {
            return $this->mysqlFetch($query, $parameters);
        }
        //check md5 performance
        $key = $this->getTagsPrefix($tags);
        $toHash = $query;
        if ($parameters !== null) {
            $toHash .= implode('-', $parameters->toArray());
        }

        $key .= md5($toHash);
        $cached = $this->keyValueStore->getComplex($key);

        if (!$cached) {
            $cached = $this->mysqlFetch($query, $parameters);
            if (!$notSaveIfEmptyResult) {
                $this->keyValueStore->setComplex($key, $cached, $ttl);
            }
        }
        return $cached;
    }

    public function modify($invalidateTags, $query, QueryParameters $parameters = null)
    {
        $this->incrementTags($invalidateTags);
        $this->mysqlFetch($query, $parameters);
    }

    public function killCache($invalidateTags)
    {
        $this->incrementTags($invalidateTags);
    }

    public function putInStore($key, $value, $ttl)
    {
        return $this->keyValueStore->setComplex($key, $value, $ttl);
    }

    public function getFromStore($key)
    {
        return $this->keyValueStore->getComplex($key);
    }
    //========================================
    //mysql functions

    private function mysqlFetch($query, QueryParameters $parameters = null)
    {
        $this->mysqlLazyConnect();

        $stmt = $this->database->prepare(
            str_replace(
                '#',
                $this->configuration['mysql']['tablePrefix'],
                $query
            )
        );

        $stmt->execute(
            $parameters === null ? array() : $parameters->toArray()
        );

        //TODO check this array more proper
        if (is_array($stmt->errorInfo()) && $stmt->errorInfo()[1] != null) {
            throw new Exception($stmt->errorInfo()[2]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function mysqlLazyLoadConfig()
    {
        if (!array_key_exists('mysql', $this->configuration)) {
            $this->configuration['mysql'] = ConfigReader::get(
                'mysql',
                array('username', 'password', 'host', 'databaseName')
            );
            if (!array_key_exists('tablePrefix', $this->configuration['mysql'])) {
                $this->configuration['mysql']['tablePrefix'] = '';
            }
        }
    }

    private function mysqlLazyConnect()
    {
        $this->mysqlLazyLoadConfig();
        if ($this->database === null) {

            $port = 3306;
            if (array_key_exists('port', $this->configuration['mysql'])) {
                $port = $this->configuration['mysql']['port'];
            }

            $this->database = new PDO(
                'mysql:host=' . $this->configuration['mysql']['host'] .
                ';port=' . $port .
                ';dbname=' . $this->configuration['mysql']['databaseName'] .
                ';charset=utf8',
                $this->configuration['mysql']['username'],
                $this->configuration['mysql']['password']
            );
        }
    }

    //========================================
    //redis functions

    private function redisLazyLoadConfig()
    {
        if (!array_key_exists('redis', $this->configuration)) {
            $this->configuration['redis'] = ConfigReader::get('redis', array('host', 'prefix'));
        }
    }

    private function redisLazyConnect()
    {
        $this->redisLazyLoadConfig();
        if ($this->database === null) {

            if (!array_key_exists('port', $this->configuration['redis'])) {
                $this->configuration['redis']['port'] = 6379;
            }

            $this->keyValueStore = new KeyValueStore();
            $this->keyValueStore->setConfig($this->configuration['redis']);
            $this->keyValueStore->connect();
        }
    }

    private function getTagsPrefix($tags)
    {
        $tagsPrefix = '';
        $tagCounts = $this->keyValueStore->mGet($this->transformTags($tags));
        for ($index = 0; $index < count($tags); $index++) {
            if ($tagCounts[$index] === false) {
                $this->keyValueStore->set($tags[$index], 1);
                $tagCounts[$index] = 1;
            }
            $tagsPrefix .= $tags[$index] . $tagCounts[$index] . '-';
        }
        return $tagsPrefix;
    }

    private function transformTags($tags)
    {
        $transformedTags = array();
        foreach ($tags as $tag) {
            //TODO validate tags (length, characters)
            $transformedTags[] = 'tag-' . $tag;
        }
        return $transformedTags;
    }

    private function incrementTags($tags)
    {
        foreach ($this->transformTags($tags) as $tag) {
            $this->keyValueStore->incr($tag);
        }
    }
}
