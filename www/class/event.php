<?php

class event {

    // Event on begin
    static public function on_begin() {

//        if (http::get_host() == "beta.pikucha.ru")
//            error_reporting(E_ALL);
//
//        if (http::get_host() == "b.pikucha.ru")
//            error_reporting(E_ALL);

        error_reporting(E_ERROR);

        // Default timezone
        date_default_timezone_set("Europe/Moscow");

    }

    // Event on end
    static public function on_end() {
        
    }

}