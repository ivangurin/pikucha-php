<?php

class cache
{

    static public
        $t_data = array();

    static public function clear()
    {
        self::$t_data = array();
    }

    static public function set($i_name, $i_key, $i_value)
    {
        self::$t_data[$i_name][$i_key] = $i_value;
    }

    static public function get($i_name, $i_key)
    {

        if (isset(self::$t_data[$i_name][$i_key]))
            return self::$t_data[$i_name][$i_key];

        throw new exception("Key " . $i_key . " of " . $i_name . " not found");

    }

}