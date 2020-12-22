<?php

class sms {

  static public function sendme($i_message) {
    try {
      return sms::send("pikucha.ru", "79057313777", $i_message);
    } catch (exception $r_exception) {
      throw new exception($r_exception->getMessage());
    }    
  }

  static public function send($i_from, $i_to, $i_message = "") {

    $l_login = "iagurin";
    $l_secret = "hI!Bro";
    $l_message = urlencode($i_message);

    $l_seed = md5("SEED" . microtime());
    $l_sign = md5($l_message . $i_to . $l_seed . $l_secret);

    $l_request = "http://sms.u-play.ru/send-md5.php?";
    $l_request .= "from=" . $i_from;
    $l_request .= "&to=" . $i_to;
    $l_request .= "&msg=" . $l_message;
    $l_request .= "&seed=" . $l_seed;
    $l_request .= "&login=" . $l_login;
    $l_request .= "&sign=" . $l_sign;

    $l_response = file_get_contents($l_request);

    if ($l_response != "OK") {
      throw new exception($l_response);
    }

    return true;
  }

}