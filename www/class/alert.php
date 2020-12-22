<?php

class alert
{

    static private
        $go_alert;
    private
        $t_alerts = array();

    public function __construct()
    {

    }

    static public function get()
    {
        if (empty(alert::$go_alert))
            alert::$go_alert = new alert();
        return alert::$go_alert;
    }

    public function __destruct()
    {

    }

    public function add($i_type, $i_message, $i_par1 = "", $i_par2 = "", $i_par3 = "", $i_par4 = "")
    {

        $s_alert = array("id" => "",
            "date" => date("YmdHis"),
            "type" => $i_type,
            "message" => $i_message,
            "parameter1" => $i_par1,
            "parameter2" => $i_par2,
            "parameter3" => $i_par3,
            "parameter4" => $i_par4,
            "ip" => "",
            "user_agent" => "");

        if (isset($_SERVER["REMOTE_ADDR"]))
            $s_alert["ip"] = $_SERVER["REMOTE_ADDR"];

        if (isset($_SERVER["HTTP_USER_AGENT"]))
            $s_alert["user_agent"] = $_SERVER["HTTP_USER_AGENT"];

        $this->t_alerts[] = $s_alert;

    }

    public function show()
    {
        print_r($this->t_alerts);
    }

    public function save()
    {

        if (count($this->t_alerts) == 0)
            return true;

        $r_db = db::get();

        foreach ($this->t_alerts as $l_index => $s_alert) {
            if (empty($s_alert["id"])) {
                $r_db->query("insert into Alerts (date, type, message, parameter1, parameter2, parameter3, parameter4, ip, user_agent)
                                      value( '" . $s_alert["date"] . "',
                                             '" . $s_alert["type"] . "',
                                             '" . $s_alert["message"] . "',
                                             '" . $s_alert["parameter1"] . "',
                                             '" . $s_alert["parameter2"] . "',
                                             '" . $s_alert["parameter3"] . "',
                                             '" . $s_alert["parameter4"] . "',
                                             '" . $s_alert["ip"] . "',
                                             '" . $s_alert["user_agent"] . "' )");
                $this->t_alerts[$l_index]["id"] = $r_db->get_id();
            }
        }

        return true;

    }

}