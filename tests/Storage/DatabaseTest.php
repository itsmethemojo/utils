<?php

namespace Itsmethemojo\Test\Storage;

use Itsmethemojo\Storage\Database;
use Itsmethemojo\Storage\KeyValueStore;

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


    public function testKillCache()
    {
        $this->setUpMysqlConfigFile("");
        $this->setUpRedisConfigFile("");
        $database = $this->getDatabaseObjectWithWorkingConfig();

        //mock redis
        $redis = $this
            ->getMockBuilder('\Redis')
            ->setMethods(array("setex","get","incr"))
            ->getMock();
        $redis
            ->expects($this->exactly(2))
            ->method("incr")
            ->with(
                $this->logicalOr(
                    $this->equalTo(DatabaseTest::PREFIX . "tag-" . "peter"),
                    $this->equalTo(DatabaseTest::PREFIX . "tag-" . "hans")
                )
            )
                ->will($this->returnValue(2));
        
                $pdo = $this
                ->getMockBuilder('\PDO')
                ->disableOriginalConstructor()
                ->setMethods(array("setex","get"))
                ->getMock();

                $database->connect($pdo, $redis);

                $database->killCache(["hans","peter"]);
    }

    public function tearDown()
    {
        $this->cleanUpConfigFile();
        parent::tearDown();
    }

    //helper

    private function getDatabaseObjectWithWorkingConfig()
    {
        $this->setUpMysqlConfigFile("host = localhost\nusername = root\npassword = root\ndatabaseName = login");
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
