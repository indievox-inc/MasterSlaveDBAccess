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
    protected        $db_name;
    protected        $db_connection;

    protected        $db_name_poll;
    protected        $db_connection_poll;

}// end of class DBAccess
