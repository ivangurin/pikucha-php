<?php

require_once("class.php");

$path = http::get_path();

if ($path == "/") {

    service::check_host();

    require_once("workplace.html");

} elseif ($path == "/api") {

    require_once("api.php");

} elseif ($path == "/report") {

    service::check_host();

    require_once("report.html");

// Album
} elseif (
    preg_match("/^\/(a[0-9a-zA-Z]+)$/", $path, $ls_matches) ||
    preg_match("/^\/album\/([0-9]+)$/", $path, $ls_matches)
) {

    service::check_host();

    request::get("album", $ls_matches[1]);

    if (service::is_bot())
        require_once("display.php");
    else
        require_once("display.html");

// Image
} elseif (
    preg_match("/^\/(i[0-9a-zA-Z]+)$/", $path, $ls_matches) ||
    preg_match("/^\/image\/([0-9]+)$/", $path, $ls_matches) ||
    preg_match("/^\/([0-9]+)$/", $path, $ls_matches)
) {

    service::check_host();

    request::get("image", $ls_matches[1]);

    if (service::is_bot())
        require_once("display.php");
    else
        require_once("display.html");

// Thumbnail image
} elseif (
    preg_match("/^\/([0-9]+)\/([a-z]+)\/(.+\..+)$/", $path, $ls_matches) ||
    preg_match("/^\/(i[0-9a-zA-Z]+)\/([a-z]+)\/(.+\..+)$/", $path, $ls_matches)
) {

    request::get("object", "image");
    request::get("action", "show");
    request::get("image", $ls_matches[1]);
    request::get("size", $ls_matches[2]);

    require_once("api.php");

// Full image
} elseif (
    preg_match("/^\/([0-9]+)\/([^\/]+\..+)$/", $path, $ls_matches) ||
    preg_match("/^\/(i[0-9a-zA-Z]+)\/([^\/]+\..+)$/", $path, $ls_matches)
) {

    request::get("object", "image");
    request::get("action", "show");
    request::get("image", $ls_matches[1]);

    require_once("api.php");

} else {

    http::not_found();

}