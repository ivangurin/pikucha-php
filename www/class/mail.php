<?php

class mail
{

    static function report($i_subject = "", $i_message = "")
    {

        $l_from = "robot <info@pikucha.ru>";
        $l_to = "administrator <info@pikucha.ru>";

        mail::send($l_from, $l_to, $i_subject, $i_message);

    }

    static function send($i_from, $i_to, $i_subject = "", $i_message = "")
    {

        mail($i_to, $i_subject, $i_message, "From: $i_from", "");

    }

    static function spam($i_to, $i_subject = "", $i_message = "")
    {

        $l_from = "Pikucha Team <info@pikucha.ru>";

        mail::send($l_from, $i_to, $i_subject, $i_message);

    }

}