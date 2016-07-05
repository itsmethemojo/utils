<?php

namespace Itsmethemojo\Test\Storage;

use Itsmethemojo\Storage\KeyValueStore;
use Itsmethemojo\File\ConfigReader;

class KeyValueStoreTest extends \PHPUnit_Framework_TestCase
{

    const PREFIX = "pre_";
    const CONFIG_FILE_NAME = "unittests-redis";

    public function tearDown()
    {
        $this->cleanUpConfigFile();
        parent::tearDown();
    }

    /**
     * @expectedException Itsmethemojo\Exception\MissingConfigurationException
     */
    public function testSetConfigWithEmptyData()
    {
        $this->setUpConfigFile("");
        $store = $this->getKeyValueStore();
    }

    /**
     * @expectedException Itsmethemojo\Exception\MissingConfigurationException
     */
    public function testSetConfigWithIncompleData1()
    {

        $this->setUpConfigFile("host = a");
        $store = $this->getKeyValueStore();
    }

    /**
     * @expectedException Itsmethemojo\Exception\MissingConfigurationException
     */
    public function testSetConfigWithIncompleData2()
    {

        $this->setUpConfigFile("prefix = " . KeyValueStoreTest::PREFIX);
        $store = $this->getKeyValueStore();
    }

    public function testSetWithDefaultDuration()
    {

        $store = $this->getKeyValueStoreWithCompleteConfigData();

        $redis = $this->getRedisMock();
        $redis
            ->expects($this->once())
            ->method("setex")
            ->with(KeyValueStoreTest::PREFIX."name", KeyValueStore::$ttl, "hans");

        $store->connect($redis);

        $store->set("name", "hans");

    }

    public function testSetWithComplexValue()
    {
        
        $store = $this->getKeyValueStoreWithCompleteConfigData();
        
        $redis = $this->getRedisMock();
        $redis->expects($this->once())->method("setex")->with(
            KeyValueStoreTest::PREFIX . "names",
            400,
            'json>["hans","peter"]'
        );

        $store->connect($redis);

        $store->set("names", ["hans","peter"], 400);

    }

    public function testGetWithArray()
    {

        $store = $this->getKeyValueStoreWithCompleteConfigData();

        $redis = $this->getRedisMock();
        $redis
            ->expects($this->once())
            ->method("get")
            ->with(KeyValueStoreTest::PREFIX . "names")
            ->will($this->returnValue('json>["hans","peter"]'));


        $store->connect($redis);

        $this->assertEquals(["hans","peter"], $store->get("names"));

    }
    
    public function testGetWithObject()
    {

        $store = $this->getKeyValueStoreWithCompleteConfigData();

        $redis = $this->getRedisMock();
        $redis
            ->expects($this->once())
            ->method("get")
            ->with(KeyValueStoreTest::PREFIX . "names")
            ->will($this->returnValue('json>{"name":"hans","age":22,"friends":[{"name":"peter","age":21}]}'));


        $store->connect($redis);

        $this->assertEquals(
            [
                "name" => "hans" ,
                "age" => 22, "friends" => [
                    [
                        "name" => "peter" ,
                        "age" => 21
                        ]
                ]
            ],
            $store->get("names")
        );

    }

    private function getKeyValueStore()
    {
        return new KeyValueStore(KeyValueStoreTest::CONFIG_FILE_NAME);
    }
    
    private function getKeyValueStoreWithCompleteConfigData()
    {
        $this->setUpConfigFile("host = a\nprefix = " . KeyValueStoreTest::PREFIX);
        return $this->getKeyValueStore();
    }


    private function getRedisMock()
    {
        return $this->getMockBuilder('\Redis')->setMethods(array("setex","get"))->getMock();
    }

    private function setUpConfigFile($dataAsString)
    {
        file_put_contents(
            ConfigReader::getRootPath()."/config/".KeyValueStoreTest::CONFIG_FILE_NAME.".ini",
            $dataAsString
        );
    }

    private function cleanUpConfigFile()
    {
        unlink(ConfigReader::getRootPath()."/config/".KeyValueStoreTest::CONFIG_FILE_NAME.".ini");
    }
}
