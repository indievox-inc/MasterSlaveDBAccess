<?php
use iNDIEVOX\MasterSlaveDBAccess\MasterSlaveDBAccess;

class MasterSlaveDBAccessTest extends PHPUnit_Framework_TestCase
{

    protected static $db_config;
    protected static $no_slave_db_config;

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

        self::$no_slave_db_config = array(
            "database_server" => array(
                "master"=>array(
                    "db_host"=>'localhost',
                    "db_name"=>'homestead',
                    "db_user"=>'root',
                    "db_password"=>''
                )
            ),
            "slave_database_name" => array()
        );

    }

    public static function tearDownAfterClass()
    {
        self::$db_config = null;
        self::$no_slave_db_config = null;
    }

    public function testGetInstance()
    {
        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $this->assertRegexp('/slave/', $db_obj->current_mode);
        $this->assertEquals('one_time', $db_obj->context_status);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

        $db_obj = MasterSlaveDBAccess::getInstance(self::$no_slave_db_config);
        $this->assertEquals('master', $db_obj->current_mode);
        $this->assertEquals('one_time', $db_obj->context_status);
        unset($db_obj);

    }

}
