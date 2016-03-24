<?php
use iNDIEVOX\MasterSlaveDBAccess\MasterSlaveDBAccess;

class MasterSlaveDBAccessTest extends PHPUnit_Framework_TestCase
{

    protected static $db_config;
    protected static $no_slave_db_config;

    public static function setUpBeforeClass()
    {

        $create_user_table_sql = "CREATE TABLE IF NOT EXISTS `user` (".
                            "`id` int(11) unsigned NOT NULL AUTO_INCREMENT,".
                            "`path` char(30) NOT NULL,".
                            "`is_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',".
                            "`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',".
                            "`modify_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',".
                            "`delete_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',".
                            "PRIMARY KEY (`id`)".
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

        $drop_user_table_sql = "DROP TABLE user";

        $insert_user_sql = "INSERT INTO `user` (`path`, `is_deleted`, `create_time`, `modify_time`, `delete_time`) VALUES".
                                "('fukuball', 0, '2016-12-30 00:00:00', '2016-12-30 16:12:18', '0000-00-00 00:00:00');";

        $select_user_sql = "SELECT * FROM user WHERE `path`='fukuball' ";

        $update_user_sql = "UPDATE `user` SET `path`='fukuball-lin' WHERE `path`='fukuball' ";

        $delete_user_sql = "DELETE `user` WHERE `path`='fukuball' ";


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

        $db_obj = MasterSlaveDBAccess::getInstance(self::$no_slave_db_config);
        $db_obj = MasterSlaveDBAccess::getInstance(self::$no_slave_db_config);
        $db_obj = MasterSlaveDBAccess::getInstance(self::$no_slave_db_config);
        $this->assertEquals('master', $db_obj->current_mode);
        $this->assertEquals('one_time', $db_obj->context_status);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $this->assertRegexp('/slave/', $db_obj->current_mode);
        $this->assertEquals('one_time', $db_obj->context_status);;

    }

    public function testConnectMaster()
    {

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $db_obj->connectMaster();
        $this->assertEquals('master', $db_obj->current_mode);

    }

    public function testConnectSlave()
    {

        /*$db_obj = MasterSlaveDBAccess::getInstance(self::$no_slave_db_config);
        $db_obj->connectSlave();
        $this->assertEquals('master', $db_obj->current_mode);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();*/

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $db_obj->connectSlave();
        $this->assertRegexp('/slave/', $db_obj->current_mode);

    }

}
