<?php

class text
{

    static public function slice($input, $slice)
    {

        $arg = explode(':', $slice);

        $start = intval($arg[0]);

        if ($start < 0) {
            $start += strlen($input);
        }

        if (count($arg) === 1) {
            return substr($input, $start, 1);
        }

        if (trim($arg[1]) === '') {
            return substr($input, $start);
        }

        $end = intval($arg[1]);

        if ($end < 0) {
            $end += strlen($input);
        }

        return substr($input, $start, $end - $start);

    }

    static public function safe($i_text)
    {

        $l_text = $i_text;
        $l_text = str_replace("<", "&lt;", $l_text);
        $l_text = str_replace(">", "&gt;", $l_text);
        $l_text = str_replace("\"", "&quot;", $l_text);
        $l_text = str_replace("'", "&#039;", $l_text);

        return $l_text;

    }

}