<?php

class value
{

    static public function set(&$i_key, &$i_value)
    {

        // Get db instance
        $r_db = db::get();

        // Set value
        $r_db->query("insert into pikucha.values ( pikucha.values.key, pikucha.values.value) value('$i_key', '$i_value')");

        $l_id = $r_db->get_id();

        if ($l_id == 0) {
            mail::report("Error on create value", "Key: $i_key, Value: $i_value");
            throw new exception("Error on create value");
        }

    }

    static public function get(&$i_key)
    {

        // Get db instance
        $r_db = db::get();

        // Get value
        $s_value = $r_db->select_single("select pikucha.values.value from pikucha.values where pikucha.values.key = '$i_key' limit 1");

        if (isset($s_value["value"]))
            return $s_value["value"];

        throw new exception("Value with key $i_key not found");

    }

}