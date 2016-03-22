<?php
require_once dirname(dirname(__FILE__))."/src/class/MasterSlaveDBAccess.php";
use iNDIEVOX\MasterSlaveDBAccess\MasterSlaveDBAccess;

function loader($class) {
    $file = $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('loader');
?>