<?php

class http
{

    static public function location($i_path = "/")
    {
        header("Location: $i_path");
        die;
    }

    static public function download($i_path)
    {
        header("HTTP/1.1 200 OK");
        header("Content-Type: image/jpeg");
        header("X-Accel-Redirect: " . $i_path);
        header("Connection: close");
        die;
    }

    static public function ok()
    {
        header("HTTP/1.1 200 OK");
        header("Content-Type: image/jpeg");
        header("Connection: close");
        die;
    }

    static public function forbidden()
    {
        header("HTTP/1.1 403 Forbidden");
        header("Connection: close");
        die;
    }

    static public function not_found()
    {
        header("HTTP/1.0 404 File not found", true, 404);
        header("Connection: close");
        die;
    }

    static public function allow()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: x-requested-with, content-type, accept");
    }

    static public function get_host()
    {

        $l_host = "";
        if (isset($_SERVER["HTTP_HOST"]))
            $l_host = $_SERVER["HTTP_HOST"];

        return $l_host;

    }

    static public function get_path()
    {

        $l_uri = self::get_uri();

        $ls_uri = parse_url($l_uri);

        $l_path = "";
        if (isset($ls_uri["path"]))
            $l_path = $ls_uri["path"];

        return $l_path;

    }

    static public function get_url()
    {

        return sprintf("http://%s%s", self::get_host(), self::get_uri());

    }

    static public function get_uri()
    {

        $l_uri = "";
        if (isset($_SERVER["REQUEST_URI"]))
            $l_uri = $_SERVER["REQUEST_URI"];

        return $l_uri;

    }

    static public function get_query()
    {

        $l_query = "";
        if (isset($_SERVER["QUERY_STRING"]))
            $l_query = $_SERVER["QUERY_STRING"];

        return $l_query;

    }

    static public function get_referrer($i_get = false)
    {

        if ($i_get == true)
            if (isset($_GET["referrer"]))
                if (!empty($_GET["referrer"]))
                    return urldecode($_GET["referrer"]);

        if (isset($_SERVER["HTTP_REFERER"]))
            return $_SERVER["HTTP_REFERER"];

        return "";

    }

    static public function get_agent()
    {

        $l_agent = "";
        if (isset($_SERVER["HTTP_USER_AGENT"]))
            $l_agent = $_SERVER["HTTP_USER_AGENT"];

        return $l_agent;

    }

    static public function get_ip()
    {

        $l_ip = "127.0.0.1";

        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {

            $l_ip = $_SERVER["HTTP_CF_CONNECTING_IP"];

        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {

            $l_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];

        } elseif (isset($_SERVER["REMOTE_ADDR"])) {

            $l_ip = $_SERVER["REMOTE_ADDR"];

        }

        return $l_ip;

    }

    static public function get_title($i_url)
    {

        $l_page = @file_get_contents($i_url, FILE_TEXT, null, null, 10240);

        if ($l_page === false)
            return "";

        preg_match("/\<title\>(.*)\<\/title\>/", $l_page, $lt_matches);

        $l_title = "";
        if (isset($lt_matches[1]))
            $l_title = $lt_matches[1];

        if (empty($l_title))
            return "";

        $l_encoded_title = mb_convert_encoding($l_title, "UTF-8", "UTF-8");

        if ($l_title != $l_encoded_title)
            $l_encoded_title = mb_convert_encoding($l_title, "UTF-8", "CP1251");

        return $l_encoded_title;

    }

}