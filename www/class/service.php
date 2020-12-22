<?php

class service
{

    static public function is_bot()
    {


        if (isset($_GET["bot"]))
            return true;

        $l_agent = http::get_agent();

        if (preg_match("/.*bot.*/i", $l_agent))
            return true;

        return false;

    }

    static public function is_pikucha($i_url = "")
    {

        if (empty($i_url))
            $l_url = http::get_referrer();
        else
            $l_url = $i_url;

        if (preg_match("/.*pikucha.*/i", $l_url))
            return true;

        return false;

    }

    static public function is_search($i_url = "")
    {

        if (empty($i_url))
            $l_url = http::get_referrer();
        else
            $l_url = $i_url;

        if (preg_match("/.*yandex.*/i", $l_url))
            return true;

        if (preg_match("/.*rambler.*/i", $l_url))
            return true;

        if (preg_match("/.*google.*/i", $l_url))
            return true;

        if (preg_match("/.*yahoo.*/i", $l_url))
            return true;

        if (preg_match("/.*bing.*/i", $l_url))
            return true;

        return false;

    }

    static public function get_browser()
    {

        $l_agent = http::get_agent();

        if (preg_match("/.*opera.*/i", $l_agent))
            return "Opera";

        if (preg_match("/.*chrome.*/i", $l_agent))
            return "Chrome";

        if (preg_match("/.*safari.*/i", $l_agent))
            return "Safari";

        if (preg_match("/.*msie.*/i", $l_agent))
            return "IE";

        if (preg_match("/.*mozilla.*/i", $l_agent))
            return "Firefox";

        return false;
    }

    static public function get_server()
    {
        return php_uname("n");
    }

    static public function parse_date($i_timestamp = "")
    {

        if (empty($i_timestamp))
            $l_timestamp = date("Y-m-d H:i:s");
        else
            $l_timestamp = $i_timestamp;

        $ls_timestamp["year"] = substr($l_timestamp, 0, 4);
        $ls_timestamp["month"] = substr($l_timestamp, 5, 2);
        $ls_timestamp["day"] = substr($l_timestamp, 8, 2);
        $ls_timestamp["hours"] = substr($l_timestamp, 11, 2);
        $ls_timestamp["minutes"] = substr($l_timestamp, 14, 2);
        $ls_timestamp["seconds"] = substr($l_timestamp, 17, 2);

        if ($ls_timestamp["day"]{0} == "0")
            $ls_timestamp["day"] = substr($ls_timestamp["day"], 1);

        return $ls_timestamp;
    }

    static public function check_host()
    {

        $l_host = http::get_host();

        if ($l_host != "pikucha.ru" &&
            $l_host != "beta.pikucha.ru"){

            $l_url = http::get_url();

            if($l_host == "b.pikucha.ru")
                $l_url = str_replace($l_host, "beta.pikucha.ru", $l_url);
            else
                $l_url = str_replace($l_host, "pikucha.ru", $l_url);

            http::location($l_url);

            die;

        }

    }

}