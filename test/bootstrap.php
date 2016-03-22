<?php
require_once dirname(dirname(__FILE__))."/src/class/DBAccess.php";
use iNDIEVOX\DBAccess\DBAccess;

function loader($class) {
    $file = $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('loader');
?>