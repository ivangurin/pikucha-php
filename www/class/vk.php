<?php

class vk
{

    static public
        $id = "3532196",
        $key = "HZVwQniVFNIKMCcN39Wc",
        $scope = "notify, notifications, friends, offline";

    static function get_user_id($i_expire, $i_mid, $i_secret, $i_sid, $i_sig)
    {

        $l_text = "expire=" . $i_expire;
        $l_text .= "mid=" . $i_mid;
        $l_text .= "secret=" . $i_secret;
        $l_text .= "sid=" . $i_sid;
        $l_text .= self::$key;

        $l_sig = md5($l_text);

        if ($l_sig != $i_sig) {
            return false;
        }

        return $i_mid;

    }

}