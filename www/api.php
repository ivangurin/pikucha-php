<?php

require_once("class.php");

event::on_begin();

try {
    $object = request::any("object", true);
    $action = request::any("action", true);
} catch (exception $r_exception) {
    die(json_encode(array("error" => 1, "text" => "Bad request")));
}

$token = request::any("token");

http::allow();

$ls_input = api::get_input();

switch ($object) {

    case "service":

        switch ($action) {
            case "status":

                die(json_encode(array("OK")));

            case "get_conf":

                try {
                    $ls_data["conf"] = api::get_conf();
                    die(json_encode($ls_data));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "get_images":

                try {
                    die(json_encode(api::get_all_images($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "send_mail":

                try {

                    $ls_data["token"] = request::any("token", true);
                    $ls_data["subject"] = request::any("subject", true);
                    $ls_data["message"] = request::any("message", true);

                    api::send_mail($ls_data);

                    die(json_encode(array("OK")));

                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

        }

        break;

    case "user":

        switch ($action) {
            case "create":

                $ls_input["name"] = request::any("name");

                try {
                    die(json_encode(api::user_create($ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "change":

                try {
                    die(json_encode(api::user_change($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "get":

                try {

                    $ls_data["user"] = api::get_user_by_token($token);

                    if(request::any("images") == "true")
                        $ls_data["images"] = api::get_user_images($token);

                    if(request::any("albums") == "true")
                        $ls_data["albums"] = api::get_user_albums($token);

                    die(json_encode($ls_data));

                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "registrate":

                try {
                    die(json_encode(api::user_registrate($ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "remind":

                try {
                    api::user_remind($ls_input);
                    die(json_encode(array("OK")));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "signin":

                try {
                    die(json_encode(api::user_signin($ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "signout":

                try {

                    api::user_signout($token);

                    die(json_encode(array("OK")));

                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "vk_signin":

                try {

                    $ls_data["expire"] = request::any("expire");
                    $ls_data["mid"] = request::any("mid");
                    $ls_data["secret"] = request::any("secret");
                    $ls_data["sid"] = request::any("sid");
                    $ls_data["sig"] = request::any("sig");
                    $ls_data["nickname"] = request::any("nickname");
                    $ls_data["first_name"] = request::any("first_name");
                    $ls_data["last_name"] = request::any("last_name");

                    die(json_encode(api::user_vk_signin($ls_data)));

                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "vk_link":

                try {

                    $ls_data["expire"] = request::any("expire");
                    $ls_data["mid"] = request::any("mid");
                    $ls_data["secret"] = request::any("secret");
                    $ls_data["sid"] = request::any("sid");
                    $ls_data["sig"] = request::any("sig");
                    $ls_data["nickname"] = request::any("nickname");
                    $ls_data["first_name"] = request::any("first_name");
                    $ls_data["last_name"] = request::any("last_name");

                    api::user_vk_link($token, $ls_data);

                    die(json_encode(array("OK")));

                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

        }

        break;

    case "image":

        switch ($action) {

            case "upload":

                try {
                    die(json_encode(api::upload_images($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "change":

                try {
                    die(json_encode(api::change_images($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "save":

                try {
                    die(api::save_image($token, $ls_input));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "delete":

                try {
                    die(json_encode(api::delete_images($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "restore":

                try {
                    die(json_encode(api::restore_images($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "get":

                try {

                    $l_id = request::any("id");


                    if (empty($l_id))
                        throw new exception("Image ID was not set");

                    $ls_data["image"] = api::get_image($l_id);

                    try {
                        if (request::any("user") == "true")
                            $ls_data["user"] = api::get_user_by_id($ls_data["image"]["user"]);
                    } catch (exception $r_exception) {

                    }

                    try {
                        if (request::any("album") == "true")
                            $ls_data["album"] = api::get_album($ls_data["image"]["album"]);
                    } catch (exception $r_exception) {

                    }

                    die(json_encode($ls_data));


                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "empty":

                try {

                    api::empty_trash($token);

                    die(json_encode(array("OK")));

                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "show":

                api::show_image($ls_input);

        }

        break;

    case "album":

        switch ($action) {

            case "create":

                try {
                    die(json_encode(api::create_album($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "change":

                try {
                    die(json_encode(api::change_album($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "delete":

                try {
                    api::delete_album($token, $ls_input);
                    die(json_encode(array("OK")));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

            case "get":

                try {

                    $l_id = request::any("id");

                    if (empty($l_id))
                        throw new exception("Album ID was not set");

                    $ls_data["album"] = api::get_album($l_id);

                    try {
                        if (request::any("user") == "true")
                            $ls_data["user"] = api::get_user_by_id($ls_data["album"]["user"]);
                    } catch (exception $r_exception) {

                    }

                    try {
                        if (request::any("images") == "true")
                            $ls_data["images"] = api::get_album_images($ls_data["album"]["id"]);
                    } catch (exception $r_exception) {

                    }

                    die(json_encode($ls_data));

                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

        }

        break;

    case "report":

        switch ($action) {
            case "get_images":

                try {
                    die(json_encode(api::get_all_images($token, $ls_input)));
                } catch (exception $r_exception) {
                    die(json_encode(array("error" => 1, "text" => $r_exception->getMessage())));
                }

                break;

        }

        break;

    default:

        http::forbidden();

        break;

}