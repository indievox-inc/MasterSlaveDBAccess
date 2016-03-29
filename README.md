# Master Slave DBAccess

[![Latest Stable Version](https://poser.pugx.org/indievox/master-slave-db-access/v/stable)](https://packagist.org/packages/indievox/master-slave-db-access)
[![Build Status](https://travis-ci.org/indievox-inc/MasterSlaveDBAccess.svg?branch=master)](https://travis-ci.org/indievox-inc/MasterSlaveDBAccess)
[![codecov.io](https://codecov.io/github/indievox-inc/MasterSlaveDBAccess/coverage.svg?branch=master)](https://codecov.io/github/indievox-inc/MasterSlaveDBAccess?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/a4789015127043baa6d5636af6964809)](https://www.codacy.com/app/hub/MasterSlaveDBAccess)

簡易的 MySQL database 伺服器存取類別，使用 POD 及 singleton pattern 實作，支援 Master-Slave 架構。

Simple MySQL database server access class use PDO and singleton pattern, support Master-Slave database cluster.

# Usage

## Install by composer

```php
composer require indievox/master-slave-db-access:dev-master
```

## Include auto load

```php
require_once "/path/to/your/vendor/autoload.php";
use iNDIEVOX\MasterSlaveDBAccess\MasterSlaveDBAccess;
```

## Config your database connections

Here's the example of database connection config

```php

$db_config = array(
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

```

## Get database access instance

```php

$db_obj = MasterSlaveDBAccess::getInstance($db_config);

```

## Use database access instance to excute query

```php

$select_sql = "SELECT id, email FROM user WHERE id=:id ";
$param = array(
    ":id" => '1'
);
$query_result = $db_obj->selectCommand($select_sql, $param);

foreach ($query_result as $query_result_data) {
    echo $query_result_data["id"];
    echo $query_result_data["email"];
}

```

## Force read master database when you need

```php

MasterSlaveDBAccess::forceSwitchMaster();
$select_sql = "SELECT id, email FROM user WHERE id=:id ";
$param = array(
    ":id" => '1'
);
$query_result = $db_obj->selectCommand($select_sql, $param);

foreach ($query_result as $query_result_data) {
    echo $query_result_data["id"];
    echo $query_result_data["email"];
}

```

# License

The MIT License (MIT)

Copyright (c) 2015 fukuball

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.