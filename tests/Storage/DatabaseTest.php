<?php

namespace Itsmethemojo\Test\Storage;

use Itsmethemojo\Storage\Database;
use Itsmethemojo\File\ConfigReader;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{

    const MYSQL_CONFIG_FILE = "unittests-mysql";
    const REDIS_CONFIG_FILE = "unittests-redis";

    const PREFIX = "pre_";

    /**
     * @expectedException Itsmethemojo\Exception\MissingConfigurationException
     */
    public function testSetConfigWithEmptyData()
    {
        $this->setUpMysqlConfigFile("");
        $this->setUpRedisConfigFile("");
        $this->getDatabaseObject();
    }

    public function tearDown()
    {
        $this->cleanUpConfigFile();
        parent::tearDown();
    }

    //helper

    private function getDatabaseObjectWithWorkingConfig()
    {
        $this->setUpMysqlConfigFile("");
        $this->setUpRedisConfigFile("host = a\nprefix = " . DatabaseTest::PREFIX);
        return new Database(
            DatabaseTest::MYSQL_CONFIG_FILE,
            DatabaseTest::REDIS_CONFIG_FILE
        );
    }

    private function getDatabaseObject()
    {
        return new Database(
            DatabaseTest::MYSQL_CONFIG_FILE,
            DatabaseTest::REDIS_CONFIG_FILE
        );
    }

    private function setUpMysqlConfigFile($dataAsString)
    {
        file_put_contents(
            ConfigReader::getRootPath()."/config/".DatabaseTest::MYSQL_CONFIG_FILE.".ini",
            $dataAsString
        );
    }

    private function setUpRedisConfigFile($dataAsString)
    {
        file_put_contents(
            ConfigReader::getRootPath()."/config/".DatabaseTest::REDIS_CONFIG_FILE.".ini",
            $dataAsString
        );
    }

    private function cleanUpConfigFile()
    {
        unlink(ConfigReader::getRootPath()."/config/".DatabaseTest::MYSQL_CONFIG_FILE.".ini");
        unlink(ConfigReader::getRootPath()."/config/".DatabaseTest::REDIS_CONFIG_FILE.".ini");
    }
}
