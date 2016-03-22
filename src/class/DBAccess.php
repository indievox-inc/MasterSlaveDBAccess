<?php
/**
 * DBAccess.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@indievox.com>
 * @license  MIT Licence
 * @version  GIT: <indievox-inc/DBAccess>
 * @link     https://github.com/indievox-inc/DBAccess
 */

namespace iNDIEVOX\DBAccess;

/**
 * DBAccess
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@indievox.com>
 * @license  MIT Licence
 * @version  Release: <0.0.1>
 * @link     https://github.com/indievox-inc/DBAccess
 */
class DBAccess
{

    protected static $db_obj;
    protected static $instance_count = 0;
    public           $current_mode;
    public           $context_status;
    protected        $db_config = array();
    protected        $db_name;
    protected        $db_connection;

    protected        $db_name_poll = array();
    protected        $db_connection_poll = array();

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

            self::$db_obj = new DBAccess($db_config);

        }

        $this_db_obj = self::$db_obj;

        // round robin switch slave connection
        if (   $this_db_obj->context_status=='one_time'
            && $this_db_obj->current_mode!='master'
            && $this_db_obj->current_mode!='random'
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

                if (   !$this_db_obj->db_connection_poll[$slave_db_choose]
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

}// end of class DBAccess
