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
                ),
                "slave3"=>array(
                    "db_host"=>'localhost',
                    "db_name"=>'homestead',
                    "db_user"=>'root',
                    "db_password"=>'',
                ),
                "slave4"=>array(
                    "db_host"=>'localhost',
                    "db_name"=>'homestead',
                    "db_user"=>'root',
                    "db_password"=>'',
                )
            ),
            "slave_database_name" => array(
                'slave1',
                'slave2',
                'slave3',
                'slave4'
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

    protected function setUp()
    {

        $create_user_table_sql = "CREATE TABLE IF NOT EXISTS user (".
                            "id int(11) unsigned NOT NULL AUTO_INCREMENT,".
                            "path char(30) NOT NULL,".
                            "is_deleted tinyint(1) unsigned NOT NULL DEFAULT '0',".
                            "create_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',".
                            "modify_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',".
                            "delete_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',".
                            "PRIMARY KEY (id)".
                        ") ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $param = array();
        $db_obj->createCommand(
            $create_user_table_sql,
            $param
        );
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    protected function tearDown()
    {

        $drop_user_table_sql = "DROP TABLE user";

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $param = array();
        $db_obj->dropCommand(
            $drop_user_table_sql,
            $param
        );
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();


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
        $this->assertEquals('one_time', $db_obj->context_status);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testConnectMaster()
    {

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $db_obj->connectMaster();
        $this->assertEquals('master', $db_obj->current_mode);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testConnectSlave()
    {

        $db_obj = MasterSlaveDBAccess::getInstance(self::$no_slave_db_config);
        $this->assertEquals('master', $db_obj->current_mode);
        $db_obj->connectSlave();
        $this->assertEquals('master', $db_obj->current_mode);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $db_obj->connectSlave();
        $this->assertRegexp('/slave/', $db_obj->current_mode);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testChangeMode()
    {

        $db_obj = MasterSlaveDBAccess::getInstance(self::$no_slave_db_config);
        $this->assertEquals('master', $db_obj->current_mode);
        $db_obj->changeMode(array('mode'=>'slave'));
        $this->assertEquals('master', $db_obj->current_mode);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $this->assertRegexp('/slave/', $db_obj->current_mode);
        $db_obj->changeMode(array('mode'=>'master'));
        $this->assertEquals('master', $db_obj->current_mode);
        $db_obj->changeMode(array('mode'=>'slave'));
        $this->assertRegexp('/slave/', $db_obj->current_mode);
        $db_obj->changeMode(array('mode'=>'master'));
        $db_obj->changeMode(array('mode'=>'slave'));
        $db_obj->changeMode(array('mode'=>'master'));
        $db_obj->changeMode(array('mode'=>'slave'));
        $db_obj->changeMode(array('mode'=>'master'));
        $db_obj->changeMode(array('mode'=>'slave'));
        $db_obj->changeMode(array('mode'=>'master'));
        $db_obj->changeMode(array('mode'=>'slave'));
        $this->assertRegexp('/slave/', $db_obj->current_mode);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testForceSwitchMaster()
    {

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $this->assertRegexp('/slave/', $db_obj->current_mode);
        MasterSlaveDBAccess::forceSwitchMaster();
        $this->assertEquals('master', $db_obj->current_mode);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testForceSwitchMasterWholeContext()
    {

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $this->assertRegexp('/slave/', $db_obj->current_mode);
        MasterSlaveDBAccess::forceSwitchMasterWholeContext();
        $this->assertEquals('master', $db_obj->current_mode);
        $this->assertEquals('whole_context', $db_obj->context_status);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testInsertCommand()
    {

        $insert_sql = "INSERT INTO user ".
            "(id, path, is_deleted, create_time, modify_time, delete_time) ".
            "VALUES ".
            "(:id, :path, :is_deleted, :create_time, :modify_time, :delete_time);";

        $param = array(
            ":id"           => '1',
            ":path"         => 'fukuball',
            ":is_deleted"   => '0',
            ":create_time"  => '2016-12-30 00:00:00',
            ":modify_time"  => '2016-12-30 16:12:18',
            ":delete_time"  => '0000-00-00 00:00:00'
        );

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $insert_id = $db_obj->insertCommand($insert_sql, $param);
        $this->assertEquals('1', $insert_id);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testSelectCommand()
    {

        $insert_sql = "INSERT INTO user ".
            "(id, path, is_deleted, create_time, modify_time, delete_time) ".
            "VALUES ".
            "(:id, :path, :is_deleted, :create_time, :modify_time, :delete_time);";

        $param = array(
            ":id"           => '1',
            ":path"         => 'fukuball',
            ":is_deleted"   => '0',
            ":create_time"  => '2016-12-30 00:00:00',
            ":modify_time"  => '2016-12-30 16:12:18',
            ":delete_time"  => '0000-00-00 00:00:00'
        );

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $insert_id = $db_obj->insertCommand($insert_sql, $param);
        $this->assertEquals('1', $insert_id);

        $select_sql = "SELECT * FROM user WHERE id=:id ";

        $param = array(
            ":id" => '1'
        );

        MasterSlaveDBAccess::forceSwitchMaster();
        $query_result = $db_obj->selectCommand($select_sql, $param);
        $this->assertEquals('1', $query_result[0]["id"]);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testUpdateCommand()
    {

        $insert_sql = "INSERT INTO user ".
            "(id, path, is_deleted, create_time, modify_time, delete_time) ".
            "VALUES ".
            "(:id, :path, :is_deleted, :create_time, :modify_time, :delete_time);";

        $param = array(
            ":id"           => '1',
            ":path"         => 'fukuball',
            ":is_deleted"   => '0',
            ":create_time"  => '2016-12-30 00:00:00',
            ":modify_time"  => '2016-12-30 16:12:18',
            ":delete_time"  => '0000-00-00 00:00:00'
        );

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $insert_id = $db_obj->insertCommand($insert_sql, $param);
        $this->assertEquals('1', $insert_id);

        $update_sql = "UPDATE user SET path='fukuball-lin' WHERE id=:id ";

        $param = array(
            ":id" => '1'
        );

        $affected_rows = $db_obj->updateCommand($update_sql, $param);
        $this->assertEquals(1, $affected_rows);

        $select_sql = "SELECT * FROM user WHERE id=:id ";

        $param = array(
            ":id" => '1'
        );

        MasterSlaveDBAccess::forceSwitchMaster();
        $query_result = $db_obj->selectCommand($select_sql, $param);
        $this->assertEquals('fukuball-lin', $query_result[0]["path"]);
        unset($db_obj);
        MasterSlaveDBAccess::destroyInstance();

    }

    public function testDeleteCommand()
    {

        $insert_sql = "INSERT INTO user ".
            "(id, path, is_deleted, create_time, modify_time, delete_time) ".
            "VALUES ".
            "(:id, :path, :is_deleted, :create_time, :modify_time, :delete_time);";

        $param = array(
            ":id"           => '1',
            ":path"         => 'fukuball',
            ":is_deleted"   => '0',
            ":create_time"  => '2016-12-30 00:00:00',
            ":modify_time"  => '2016-12-30 16:12:18',
            ":delete_time"  => '0000-00-00 00:00:00'
        );

        $db_obj = MasterSlaveDBAccess::getInstance(self::$db_config);
        $insert_id = $db_obj->insertCommand($insert_sql, $param);
        $this->assertEquals('1', $insert_id);

        $select_sql = "SELECT * FROM user WHERE id=:id ";

        $param = array(
            ":id" => '1'
        );

        MasterSlaveDBAccess::forceSwitchMaster();
        $query_result = $db_obj->selectCommand($select_sql, $param);
        $this->assertEquals('1', $query_result[0]["id"]);

        $delete_sql = "DELETE FROM user WHERE id=:id ";

        $param = array(
            ":id" => '1'
        );

        $affected_rows = $db_obj->deleteCommand($delete_sql, $param);
        $this->assertEquals(1, $affected_rows);

        $select_sql = "SELECT * FROM user WHERE id=:id ";

        $param = array(
            ":id" => '1'
        );

        MasterSlaveDBAccess::forceSwitchMaster();
        $query_result = $db_obj->selectCommand($select_sql, $param);
        $this->assertEquals(0, count($query_result));

    }
}
