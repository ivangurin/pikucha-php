<?php

require_once("class.php");

error_reporting(E_ALL);

print_r("beta");

$lr_db = db::get();

phpinfo();


//print_r($_SERVER);

//$l_query = "insert into tokens ( id, user_id, created_at) value('%s', '%s', '%s')";
//
//$l_query = sprintf($l_query, "a1b2c3", 123, date("YmdHis"));
//
//printf($l_query);


//try {
//
//    $memcache = new Memcache;
//
//    $memcache->connect('127.0.0.1', 11211) or die("Could not connect");
//
//    $memcache->set("key", "value", false, 60);
//
//    echo($memcache->get("key"));
//
//    $memcache->close();
//
//
//} catch (exception $lr_exception) {
//
//    die($lr_exception->getMessage());
//
//}



