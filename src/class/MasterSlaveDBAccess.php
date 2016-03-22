<?php
/**
 * MasterSlaveDBAccess.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@indievox.com>
 * @license  MIT Licence
 * @version  GIT: <indievox-inc/MasterSlaveDBAccess>
 * @link     https://github.com/indievox-inc/MasterSlaveDBAccess
 */

namespace iNDIEVOX\MasterSlaveDBAccess;

use \PDO;

/**
 * MasterSlaveDBAccess
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@indievox.com>
 * @license  MIT Licence
 * @version  Release: <0.0.1>
 * @link     https://github.com/indievox-inc/MasterSlaveDBAccess
 */
class MasterSlaveDBAccess
{

    protected static $db_obj;
    protected static $instance_count = 0;
    public $current_mode;
    public $context_status;
    protected $db_config = array();
    protected $db_name;
    protected $db_connection;
    protected $db_name_poll = array();
    protected $db_connection_poll = array();

    /**
     * Method getInstance to get db_obj
     *
     * @param array $db_config # database config
     *
     * @return object $db_obj
     */
    public static function getInstance($db_config)
    {

        if (!self::$db_obj || !isset(self::$db_obj) || empty(self::$db_obj)) {

            self::$db_obj = new MasterSlaveDBAccess($db_config);

        }

        $this_db_obj = self::$db_obj;

        // round robin switch slave connection
        if ($this_db_obj->context_status == 'one_time'
         && $this_db_obj->current_mode != 'master'
         && $this_db_obj->current_mode != 'random'
        ) {

            if (!empty($this_db_obj->db_config["slave_database_name"])) {

                $slave_database_count = count($this_db_obj->db_config["slave_database_name"]);
                $slave_index = array_search(
                    $this_db_obj->current_mode,
                    $this_db_obj->db_config["slave_database_name"]
                );
                $new_slave_index = $slave_index+1;

                if ($new_slave_index < $slave_database_count) {

                    $options = array('mode' => $this_db_obj->db_config["slave_database_name"][$new_slave_index]);
                    $slave_db_choose = $this_db_obj->db_config["slave_database_name"][$new_slave_index];

                } else {

                    $options = array('mode' => $this_db_obj->db_config["slave_database_name"][0]);
                    $slave_db_choose = $this_db_obj->db_config["slave_database_name"][0];

                }

                if (!$this_db_obj->db_connection_poll[$slave_db_choose]
                 || !isset($this_db_obj->db_connection_poll[$slave_db_choose])
                 || empty($this_db_obj->db_connection_poll[$slave_db_choose])
                ) {

                    $this_db_obj->connectSlave($options);

                } else {

                    // switch to this slave
                    $this_db_obj->current_mode  = $slave_db_choose;
                    $this_db_obj->db_name       = $this_db_obj->db_name_poll[$slave_db_choose];
                    $this_db_obj->db_connection = $this_db_obj->db_connection_poll[$slave_db_choose];

                }

            }// end if (!empty($slave_database_name))

        }

        return $this_db_obj;

    }// end function getInstance

    /**
     * Method forceSwitchMaster
     *
     * @return void
     */
    public static function forceSwitchMaster()
    {

        // force read master
        $db_change_mode_options = array('mode'=>'master');
        self::$db_obj->changeMode($db_change_mode_options);
        self::$db_obj->context_status = "one_time";

    }// end function forceSwitchMaster

    /**
     * Method forceSwitchMaster
     *
     * @return void
     */
    public static function forceSwitchMasterWholeContext()
    {

        // force read master
        $db_change_mode_options = array('mode'=>'master');
        self::$db_obj->changeMode($db_change_mode_options);
        self::$db_obj->context_status = "whole_context";

    }// end function forceSwitchMaster

    /**
     * Method __construct initialize instance
     *
     * @param array $db_config # database config
     *
     * @return void
     */
    private function __construct($db_config)
    {

        $this->db_config            = $db_config;
        $this->context_status       = 'one_time';
        $this->db_name_poll         = array();
        $this->db_connection_poll   = array();

        if (!empty($this->db_config["slave_database_name"])) {

            $this->connectSlave(
                array('mode'=>'random')
            );

        } else {

            $this->connectMaster();

        }

        self::$instance_count++;

    }// end function __construct

    /**
     * Method connectMaster
     *
     * @return void
     */
    public function connectMaster()
    {

        // connect master
        $m_db_host       = $this->db_config['database_server']['master']['db_host'];
        $m_db_name       = $this->db_config['database_server']['master']['db_name'];
        $m_db_user       = $this->db_config['database_server']['master']['db_user'];
        $m_db_password   = $this->db_config['database_server']['master']['db_password'];

        try {

            $this->db_connection_poll['master']
                = new PDO(
                    'mysql:host=' . $m_db_host . ';dbname=' . $m_db_name,
                    $m_db_user,
                    $m_db_password
                );

            $this->db_name_poll['master'] = $m_db_name;
            $this->db_connection_poll['master']->query("SET NAMES UTF8");

            // switch to master
            $this->current_mode  = 'master';
            $this->db_name       = $this->db_name_poll['master'];
            $this->db_connection = $this->db_connection_poll['master'];

        } catch (PDOException $e) {

            throw new RuntimeException();

        }

    }// end function connectMaster

    /**
     * Method connectSlave
     *
     * @param array $options['mode'] # input options
     *
     * @return void
     */
    public function connectSlave($options = array())
    {

        $defaults = array('mode'=>'random');

        $options = array_merge($defaults, $options);

        if (!empty($this->db_config["slave_database_name"])) {

            // connect slave
            switch ($options['mode']) {
                case 'random':
                    $slave_db_choose
                        = $this->db_config["slave_database_name"][array_rand($this->db_config["slave_database_name"])];
                    break;
                default:
                    $slave_db_choose
                        = $options['mode'];
                    break;
            }


            $s_db_host       = $this->db_config['database_server'][$slave_db_choose]['db_host'];
            $s_db_name       = $this->db_config['database_server'][$slave_db_choose]['db_name'];
            $s_db_user       = $this->db_config['database_server'][$slave_db_choose]['db_user'];
            $s_db_password   = $this->db_config['database_server'][$slave_db_choose]['db_password'];

            try {

                $this->db_connection_poll[$slave_db_choose]
                    = new PDO(
                        'mysql:host=' . $s_db_host . ';dbname=' . $s_db_name,
                        $s_db_user,
                        $s_db_password
                    );

                $this->db_name_poll[$slave_db_choose] = $s_db_name;
                $this->db_connection_poll[$slave_db_choose]->query("SET time_zone='+8:00'");
                $this->db_connection_poll[$slave_db_choose]->query("SET NAMES UTF8");

                // switch to slave
                $this->current_mode  = $slave_db_choose;
                $this->db_name       = $this->db_name_poll[$slave_db_choose];
                $this->db_connection = $this->db_connection_poll[$slave_db_choose];

            } catch (PDOException $e) {

                throw new RuntimeException();

            }

        } else {

            $this->connectMaster();

        }

    }// end function connectSlave
}// end of class MasterSlaveDBAccess
