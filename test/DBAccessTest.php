<?php
use iNDIEVOX\DBAccess\DBAccess;

class DBAccessTest extends PHPUnit_Framework_TestCase
{

    protected static $db_config;

    public static function setUpBeforeClass()
    {
        self::$db_config = array(
            "database_server" => array(
                "master"=>array(
                    "db_host"=>'localhost',
                    "db_name"=>'homestead',
                    "db_user"=>'root',
                    "db_password"=>''
                ),
                "slave1"=>array(
                    "db_host"=>'localhost',
                    "db_name"=>'homestead',
                    "db_user"=>'root',
                    "db_password"=>''
                ),
                "slave2"=>array(
                    "db_host"=>'localhost',
                    "db_name"=>'homestead',
                    "db_user"=>'root',
                    "db_password"=>'',
                )
            ),
            "slave_database_name" => array(
                'slave1',
                'slave2'
            )
        );
    }

    public static function tearDownAfterClass()
    {
        self::$db_config = NULL;
    }

    public function testGetInstance()
    {
        $db_obj = DBAccess::getInstance(self::$db_config);
        $this->assertEquals($db_obj->context_status, 'one_time');
    }

}
