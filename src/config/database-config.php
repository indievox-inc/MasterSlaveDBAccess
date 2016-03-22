<?php

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
