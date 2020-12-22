<?php

$s_conf = array(
    "host" => "",
    "db_host" => "localhost",
    "db_socket" => "/var/run/mysqld.pikucha/mysqld.sock",
    "db_login" => "pikucha",
    "db_password" => "8qwz1ofDvueoL8NHyb4EHSp7z",
    "db_database" => "pikucha",
    "db_codepage" => "UTF8"
);

if (isset($_SERVER["HTTP_HOST"]))
    $s_conf["host"] = $_SERVER["HTTP_HOST"];
