<?php

class conf
{

    static private
        $r_conf;

    private
        $s_data = array();

    public function __construct()
    {

        try {
            $r_db = db::get();
        } catch (exception $r_exception) {
            throw new exception($r_exception->getMessage());
        }

        $lt_values = $r_db->query("select * from configuration");

        $ls_data = array();
        if (count($lt_values) > 0) {
            foreach ($lt_values as $ls_value) {
                $ls_data[$ls_value["Variable"]] = $ls_value["Value"];
            }
        }

        if (http::get_host() == "beta.pikucha.ru") {
            $ls_data["host"] = "beta.pikucha.ru";
            $ls_data["host_upload"] = "b.pikucha.ru";
        }

        $this->s_data = $ls_data;

        self::$r_conf = $this;

    }

    public static function get($i_name = "")
    {

        if (!is_object(self::$r_conf))
            self::$r_conf = new conf;

        return self::$r_conf->$i_name;

    }

    public function __get($i_name)
    {

        if (isset($this->s_data[$i_name]))
            return $this->s_data[$i_name];

        throw new exception("Configuration variable $i_name not found");

    }

}