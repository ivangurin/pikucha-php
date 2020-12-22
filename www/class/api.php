<?php

class api
{

    static public function get_conf()
    {

        $ls_conf["email"] = conf::get("email");
        $ls_conf["host"] = conf::get("host");
        $ls_conf["host_upload"] = conf::get("host_upload");
        $ls_conf["max_file_size"] = conf::get("max_file_size");
        $ls_conf["thumbnail_dimension"] = conf::get("thumbnail_dimension");
        $ls_conf["thumbnail_dimension_min"] = conf::get("thumbnail_dimension_min");
        $ls_conf["thumbnail_dimension_max"] = conf::get("thumbnail_dimension_max");

        $ls_conf["image_create"] = conf::get("image_create");
        if ($ls_conf["image_create"] == 0)
            $ls_conf["image_create_description"] = conf::get("image_create_description");

        $ls_conf["image_change"] = conf::get("image_change");
        if ($ls_conf["image_change"] == 0)
            $ls_conf["image_change_description"] = conf::get("image_change_description");

        $ls_conf["image_display"] = conf::get("image_display");
        if ($ls_conf["image_display"] == 0)
            $ls_conf["image_display_description"] = conf::get("image_display_description");

        return $ls_conf;

    }

    static public function user_create(array $is_input)
    {

        $l_name = "Anonymus";
        if(isset($is_input["name"]) && !empty($is_input["name"]))
            $l_name = $is_input["name"];

        try {

            $lr_user = user_manager::create();

            $lr_user->set_name($l_name);

            $lr_user->save();

            $ls_data["token"] = $lr_user->signin();

            return $ls_data;

        } catch (exception $lr_exception) {
            mail::report("Error on create user", $lr_exception->getMessage());
            throw new exception("Internal error!");
        }

    }

    static public function user_registrate($is_input)
    {

        $lr_user = user_manager::registrate($is_input["name"], $is_input["email"], $is_input["password"]);

        $ls_data["token"] = $lr_user->signin();

        return $ls_data;

    }

    static public function user_signin($is_input)
    {

        try {
            $l_token = user_manager::signin($is_input["email"], $is_input["password"]);
        } catch (exception $r_exception) {
            throw new exception("Пользователь не найден");
        }

        $ls_data["token"] = $l_token;

        return $ls_data;

    }

    static public function user_signout($i_token)
    {

        $lr_user = user_manager::get_by_token($i_token);

        $lr_user->signout($i_token);

    }

    static public function user_remind($is_input)
    {

        try {
            $lr_user = user_manager::get_by_email($is_input["email"]);
        } catch (exception $r_exception) {
            throw new exception("Пользователь не найден");
        }

        $lr_user->remind();

    }

    static public function get_user_by_token($i_token)
    {

        $lr_user = user_manager::get_by_token($i_token);

        return $lr_user->get_public(true);

    }

    static public function get_user_by_id($i_id)
    {

        $lr_user = user_manager::get($i_id);

        return $lr_user->get_public(false);

    }

    static public function user_change($i_token, $is_input)
    {

        $lr_user = user_manager::get_by_token($i_token);

        if (empty($is_input["name"]))
            throw new exception("Укажите свое имя");

        if (empty($is_input["email"]))
            throw new exception("Укажите свою почту");

        if (!preg_match("/^[a-z_0-9\-\.]+@[a-z_0-9\-\.]+\.[a-z]{2,6}$/i", $is_input["email"]))
            throw new exception("Укажите правильную почту");

        if ($is_input["email"] != $lr_user->get_email()) {

            if ($lr_user->get_password() != "") {
                if (empty($is_input["password"]))
                    throw new exception("При изменении почты укажите свой пароль");
                if ($lr_user->get_password() != $is_input["password"])
                    throw new exception("Пароль то не тот");
            }

            try {
                $lr_registered = user_manager::get_by_email($is_input["email"]);
            } catch (exception $r_exception) {
                $lr_registered = "";
            }

            if (is_object($lr_registered))
                if ($lr_registered->id != $lr_user->id)
                    throw new exception("С такой почтой уже кто-то зарегистрирован");
        }

        if (empty($is_input["thumbnail_dimension"]))
            $is_input["thumbnail_dimension"] = conf::get("thumbnail_dimension");

        if ($is_input["thumbnail_dimension"] < conf::get("thumbnail_dimension_min"))
            $is_input["thumbnail_dimension"] = conf::get("thumbnail_dimension");

        if ($is_input["thumbnail_dimension"] > conf::get("thumbnail_dimension_max"))
            $is_input["thumbnail_dimension"] = conf::get("thumbnail_dimension");

        if (empty($is_input["thumbnail_size"]))
            $is_input["thumbnail_size"] = "0";

        if (!empty($is_input["convert"]))
            $is_input["convert"] = "1";

        $ls_user = $lr_user->get_data();

        $ls_user["Name"] = $is_input["name"];
        $ls_user["Email"] = $is_input["email"];
        if (!empty($is_input["password"]))
            $ls_user["Password"] = $is_input["password"];
        $ls_user["Thumbnail_Dimension"] = $is_input["thumbnail_dimension"];
        $ls_user["Thumbnail_Size"] = $is_input["thumbnail_size"];
        $ls_user["Bmp2Jpg"] = $is_input["convert"];
        $lr_user->set_data($ls_user);

        $lr_user->save();

        return $lr_user->get_public(true);

    }

    static public function get_image($i_id)
    {

        $lr_image = image_manager::get($i_id);

        if ($lr_image->was_deleted())
            throw new exception("Изображение было удалено");

        return $lr_image->get_public();

    }

    static public function upload_images($i_token, $is_input)
    {

        if (!isset($is_input["files"]))
            throw new exception("Не указаны изображения для загрузки");

        if (isset($is_input["thumbnail_dimension"]))
            if ($is_input["thumbnail_dimension"] < conf::get("thumbnail_dimension_min") ||
                $is_input["thumbnail_dimension"] > conf::get("thumbnail_dimension_max")
            )
                $is_input["thumbnail_dimension"] = conf::get("thumbnail_dimension");

        $lr_user = user_manager::get_by_token($i_token);

        if (isset($is_input["album"])) {

            $lr_album = album_manager::get($is_input["album"]);

            if ($lr_album->get_user() != $lr_user->get_id())
                throw new exception("Album access denied");

        }

        $lt_data = array();

        foreach ($is_input["files"] as $ls_file) {

            switch ($ls_file["error"]) {
                case "0":

                    try {

                        $lr_image = image_manager::create($ls_file["tmp_name"]);

                        $ls_image = $lr_image->get_data();

                        if (isset($lr_user))
                            $ls_image["UserID"] = $lr_user->id;

                        if (isset($lr_album))
                            $ls_image["AlbumID"] = $lr_album->id;

                        $ls_image["Thumbnail_Dimension"] = $lr_user->get_thumbnail_dimension();
                        $ls_image["Thumbnail_Size"] = $lr_user->get_thumbnail_size();
                        $ls_image["Host"] = $_SERVER["HTTP_HOST"];
                        $ls_image["Name"] = file::get_name($ls_file["name"]);

                        if (isset($is_input["description"]))
                            $ls_image["Description"] = $is_input["description"];

                        if (isset($is_input["thumbnail_dimension"]))
                            $ls_image["Thumbnail_Dimension"] = $is_input["thumbnail_dimension"];

                        if (isset($is_input["thumbnail_size"]))
                            $ls_image["Thumbnail_Size"] = $is_input["thumbnail_size"];

                        if (isset($is_input["thumbnail_text"]))
                            $ls_image["Thumbnail_Text"] = $is_input["thumbnail_text"];

                        if (isset($is_input["ip"]))
                            $ls_image["ip"] = $is_input["ip"];

                        $lr_image->set_data($ls_image);

                        if ($lr_user->is_convert())
                            $lr_image->convert("JPEG");

                        if (isset($is_input["resize"]))
                            $lr_image->resize($is_input["resize"]);

                        if (isset($is_input["rotate"]))
                            $lr_image->rotate($is_input["rotate"]);

                        $lr_image->save();

                        $lt_data[] = $lr_image->get_public();

                    } catch (exception $r_exception) {

                        $ls_data["error"] = 1;
                        $ls_data["text"] = $ls_file["name"] . ": " . $r_exception->getMessage();
                        $lt_data[] = $ls_data;

                        mail::report("Create image error", $ls_file["name"] . ": " . $r_exception->getMessage());

                        break;

                    }

                    file::delete($ls_file["tmp_name"]);

                    break;

                case "1":

                    $ls_data["error"] = 1;
                    $ls_data["text"] = "Превышен максимальный размер для файла " . $ls_file["name"];
                    $lt_data[] = $ls_data;

                    break;

                case "2":

                    $ls_data["error"] = 1;
                    $ls_data["text"] = "Превышен максимальный размер для файла " . $ls_file["name"];
                    $lt_data[] = $ls_data;

                    break;

                case "3":

                    $ls_data["error"] = 1;
                    $ls_data["text"] = "Файл " . $ls_file["name"] . " был закачен с ошибками";
                    $lt_data[] = $ls_data;

                    break;

                case "4":

                    $ls_data["error"] = 1;
                    $ls_data["text"] = "Не указан файл для загрузки";
                    $lt_data[] = $ls_data;

                    break;

            }

        }

        return $lt_data;
    }

    static public function change_images($i_token, $is_input)
    {

        if (!isset($is_input["images"]))
            throw new exception("Images not set");

        $lr_user = user_manager::get_by_token($i_token);

        if (isset($is_input["album"]) && !empty($is_input["album"])) {

            $lr_album = album_manager::get($is_input["album"]);

            if ($lr_album->get_user() != $lr_user->get_id())
                throw new exception("Album access denied");

        }

        $lt_data = array();

        foreach ($is_input["images"] as $l_image) {

            try {

                $lr_image = image_manager::get($l_image);

                if ($lr_image->get_user() != $lr_user->get_id())
                    throw new exception("Image $l_image access denied");

                if ($lr_image->was_deleted())
                    throw new exception("Image $l_image was deleted!");

                $ls_image = $lr_image->get_data();

                if (isset($lr_album))
                    $ls_image["AlbumID"] = $lr_album->id;

                if (isset($is_input["description"]))
                    $ls_image["Description"] = $is_input["description"];

                if (isset($is_input["thumbnail_dimension"]))
                    $ls_image["Thumbnail_Dimension"] = $is_input["thumbnail_dimension"];

                if (isset($is_input["thumbnail_size"]))
                    $ls_image["Thumbnail_Size"] = $is_input["thumbnail_size"];

                if (isset($is_input["thumbnail_text"]))
                    $ls_image["Thumbnail_Text"] = $is_input["thumbnail_text"];

                $lr_image->set_data($ls_image);

                if (isset($is_input["resize"]))
                    $lr_image->resize($is_input["resize"]);

                if (isset($is_input["rotate"]))
                    $lr_image->rotate($is_input["rotate"]);

                $lr_image->save();

                $lt_data[] = $lr_image->get_public();

            } catch (exception $lr_exception) {

                $lt_data[] = array("error" => 1, "text" => $lr_exception->getMessage());

            }

        }

        return $lt_data;
    }

    static public function save_image($i_token, $is_input)
    {

        if (!isset($is_input["image"]))
            throw new exception("Image id not set");

        if (!isset($is_input["files"]))
            throw new exception("Image data not upload");

        $ls_file = current($is_input["files"]);

        if ($ls_file["error"] != 0)
            throw new exception("Image data uploaded with errors");

        $lr_user = user_manager::get_by_token($i_token);

        $lr_image = image_manager::get($is_input["image"]);

        if ($lr_image->get_user() != $lr_user->get_id())
            throw new exception("Image access denied");

        $lr_image->read($ls_file["tmp_name"]);
        $lr_image->set_name($ls_file["name"]);
        $lr_image->set_info();
        $lr_image->save();

        return ("<!doctype html><html><head>" .
                "<script>if(parent){ parent.image_update('" . json_encode($lr_image->get_public()) . "'); " .
                "parent.pixlr.overlay.hide();}</script>" .
                "</head><body></body></html>");

    }

    static public function delete_images($i_token, $is_input)
    {

        if (!isset($is_input["images"]))
            throw new exception("Images not set");

        $lr_user = user_manager::get_by_token($i_token);

        $lt_data = array();

        foreach ($is_input["images"] as $l_image) {

            try {

                $lr_image = image_manager::get($l_image);

                if ($lr_image->get_user() != $lr_user->get_id())
                    throw new exception("Image $lr_image->id access denied");

                if ($lr_image->was_deleted())
                    throw new exception("Image $lr_image->id was deleted!");

                $lr_image->delete();

                $lt_data[] = $lr_image->get_public();

            } catch (exception $lr_exception) {
                $lt_data[] = array("error" => 1, "text" => $lr_exception->getMessage());
            }

        }

        return $lt_data;

    }

    static public function restore_images($i_token, $is_input)
    {

        if (!isset($is_input["images"]))
            throw new exception("Images not set");

        $lr_user = user_manager::get_by_token($i_token);

        $lt_data = array();

        foreach ($is_input["images"] as $l_image) {

            try {

                $lr_image = image_manager::get($l_image);

                if ($lr_image->get_user() != $lr_user->get_id())
                    throw new exception("Image $lr_image->id access denied");

                if (!$lr_image->was_deleted())
                    throw new exception("Image $lr_image->id wasn't deleted!");

                $lr_image->restore();

                $lt_data[] = $lr_image->get_public();

            } catch (exception $lr_exception) {
                $lt_data[] = array("error" => 1, "text" => $lr_exception->getMessage());
            }

        }

        return $lt_data;
    }

    static public function get_user_images($i_token)
    {

        $lr_user = user_manager::get_by_token($i_token);

        $lt_images = image_manager::get_by_user($lr_user->id);

        $lt_data = array();

        foreach ($lt_images as $ls_image) {

            try {

                $lr_image = image_manager::get_by_data($ls_image);

                $lt_data[$lr_image->id] = $lr_image->get_public();

            } catch (exception $r_exception) {

            }

        }

        return $lt_data;
    }

    static public function get_album_images($i_id)
    {

        $lr_album = album_manager::get($i_id);

        $lt_images = $lr_album->get_images();

        $lt_data = array();

        foreach ($lt_images as $ls_image) {

            try {

                $lr_image = image_manager::get_by_data($ls_image);

                $lt_data[$lr_image->id] = $lr_image->get_public();

            } catch (exception $r_exception) {

            }

        }

        return $lt_data;
    }

    static public function show_image($is_input)
    {

        if (!conf::get("image_display"))
            http::forbidden();

        try {
            $lr_image = image_manager::get($is_input["image"]);
        } catch (exception $r_exception) {
            http::not_found();
            die;
        }

        if ($lr_image->was_deleted()) {
            http::not_found();
        }

        $l_size = "";
        if (isset($is_input["size"]))
            $l_size = $is_input["size"];

        $lr_image->show($l_size);

    }

    static public function empty_trash($i_token)
    {

        $lr_user = user_manager::get_by_token($i_token);

        image_manager::empty_trash($lr_user->id);

    }

    static public function create_album($i_token, $is_input)
    {

        if (!isset($is_input["name"]) || empty($is_input["name"]))
            throw new exception("Укажите имя альбома");

        $lr_user = user_manager::get_by_token($i_token);

        $lr_album = album_manager::create();

        $ls_album = $lr_album->get_data();

        $ls_album["UserID"] = $lr_user->get_id();
        $ls_album["Name"] = $is_input["name"];

        if (isset($is_input["description"]))
            $ls_album["Description"] = $is_input["description"];

        $lr_album->set_data($ls_album);

        $lr_album->save();

        return $lr_album->get_public();
    }

    static public function change_album($i_token, $is_input)
    {

        if (!isset($is_input["album"]) || empty($is_input["album"]))
            throw new exception("Album ID was not set");

        if (!isset($is_input["name"]) || empty($is_input["name"]))
            throw new exception("Album name was not set");

        $lr_user = user_manager::get_by_token($i_token);

        $lr_album = album_manager::get($is_input["album"]);

        if ($lr_album->get_user() != $lr_user->get_id())
            throw new exception("Album access denied");

        $ls_album = $lr_album->get_data();

        $ls_album["Name"] = $is_input["name"];

        if (isset($is_input["description"]))
            $ls_album["Description"] = $is_input["description"];

        $lr_album->set_data($ls_album);

        $lr_album->save();

        return $lr_album->get_public();

    }

    static public function delete_album($i_token, $is_input)
    {

        if (!isset($is_input["album"]) || empty($is_input["album"]))
            throw new exception("Album ID was not set");

        $lr_user = user_manager::get_by_token($i_token);

        $lr_album = album_manager::get($is_input["album"]);

        if ($lr_album->get_user() != $lr_user->get_id())
            throw new exception("Album access denied");

        $lr_album->delete();

    }

    static public function get_album($i_id)
    {

        $lr_album = album_manager::get($i_id);

        return $lr_album->get_public();
    }

    static public function get_user_albums($i_token)
    {

        $lr_user = user_manager::get_by_token($i_token);

        $lt_albums = album_manager::get_by_user($lr_user->id);

        $lt_data = array();

        foreach ($lt_albums as $ls_album) {

            $lr_album = album_manager::get_by_data($ls_album);

            $lt_data[$lr_album->id] = $lr_album->get_public();

        }

        return $lt_data;
    }

    static public function get_all_images($i_token, $is_input)
    {

        try {

            $lr_user = user_manager::get_by_token($i_token);

            if ($lr_user->is_root() == false)
                throw new exception("Now allowed");

        } catch (exception $r_exception) {
            http::forbidden();
        }

        $l_page = 1;
        if (isset($is_input["page"]))
            $l_page = $is_input["page"];

        $l_rows = 100;
        if (isset($is_input["rows"]))
            $l_rows = $is_input["rows"];

        $l_offset = $l_page * $l_rows - $l_rows;

        $lt_images = image_manager::get_all($l_rows, $l_offset, 0);

        $lt_data = array();

        foreach ($lt_images as $l_image) {
            try {
                $lr_image = image_manager::get($l_image);
                $lt_data[] = $lr_image->get_public();
            } catch (exception $r_exception) {

            }
        }

        return $lt_data;
    }

    static public function user_vk_signin($is_input)
    {

        $l_vk_id = vk::get_user_id($is_input["expire"], $is_input["mid"], $is_input["secret"], $is_input["sid"], $is_input["sig"]);

        if ($l_vk_id === false) {
            throw new exception("VK auth failed");
        }

        try {

            $lr_user = user_manager::get_by_vk_id($l_vk_id);

            if ($lr_user === false) {

                $lr_user = user_manager::create();

                $lr_user->set_vk_id($l_vk_id);

            }

            $lr_user->set_name($is_input["first_name"]);

            $lr_user->save();

            $ls_data["token"] = $lr_user->signin();

            return $ls_data;

        } catch (exception $lr_exception) {
            mail::report("Error on VK signin", $lr_exception->getMessage());
            throw new exception("Internal error!");
        }

    }

    static public function user_vk_link($i_token, $is_input)
    {

        try {
            $lr_user = user_manager::get_by_token($i_token);
        } catch (exception $lr_exception) {
            throw new exception("Bad token. User not found");
        }

        $l_vk_id = vk::get_user_id($is_input["expire"], $is_input["mid"], $is_input["secret"], $is_input["sid"], $is_input["sig"]);

        if ($l_vk_id === false) {
            throw new exception("VK auth failed");
        }

        try {

            $lr_db = db::get();

            $l_name = $is_input["first_name"] . " " . $is_input["last_name"];

            $lr_user->set_name($l_name);

            $lr_user->set_vk_id($l_vk_id);

            $lr_db->query("update users set vk_id = '' where vk_id = '" . db::escape($l_vk_id) . "'");

            $lr_user->save();

        } catch (exception $lr_exception) {

            mail::report("Error on VK link", $lr_exception->getMessage());
            throw new exception("Internal error!");

        }

        return true;

    }

    static public function send_mail($is_data)
    {

        try {
            $lr_user = user_manager::get_by_token($is_data["token"]);
        } catch (exception $lr_exception) {
            throw new exception("Bad token. User not found");
        }

        if ($lr_user->is_root() == false) {
            throw new exception("Forbidden");
        }

        $lt_list = user_static::get_emails();

        foreach ($lt_list as $ls_list) {

            if (empty($ls_list["name"])) {
                $l_to = $ls_list["email"];
            } else {
                $l_to = $ls_list["name"] . " <" . $ls_list["email"] . ">";
            }

            mail::spam($l_to, $is_data["subject"], $is_data["message"]);

        }

    }

    static public function get_input()
    {

        $ls_input = array();

        try {
            $ls_input["user"] = request::any("user", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["id"] = request::any("id", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["image"] = request::any("image", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["album"] = request::any("album", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["name"] = request::post("name", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["email"] = request::post("email", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["password"] = request::post("password", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["title"] = request::any("title", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["type"] = request::any("type", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["size"] = request::any("size", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["description"] = request::post("description", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["thumbnail_dimension"] = (int)request::post("thumbnail_dimension", true);
            if (!is_numeric($ls_input["thumbnail_dimension"]))
                throw new exception();
        } catch (exception $r_exception) {
            unset($ls_input["thumbnail_dimension"]);
        }

        try {
            $ls_input["thumbnail_size"] = request::post("thumbnail_size", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["thumbnail_text"] = request::post("thumbnail_text", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["rotate"] = request::post("rotate", true);
            switch ($ls_input["rotate"]) {
                case "left":
                    $ls_input["rotate"] = -90;
                    break;
                case "right":
                    $ls_input["rotate"] = 90;
                    break;
                case "footup":
                    $ls_input["rotate"] = 180;
                    break;
                default:
                    throw new exception();
            }
        } catch (exception $r_exception) {
            unset($ls_input["rotate"]);
        }

        try {
            $ls_input["resize"] = request::post("resize", true);
            if (!is_numeric($ls_input["resize"]))
                throw new exception();
        } catch (exception $r_exception) {
            unset($ls_input["resize"]);
        }

        try {
            $ls_input["convert"] = request::post("convert", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["images"] = request::post("images", true);
            $ls_input["images"] = json_decode($ls_input["images"]);
        } catch (exception $r_exception) {
            unset($ls_input["images"]);
        }

        try {
            $ls_input["files"] = request::files();
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["page"] = request::any("page", true);
        } catch (exception $r_exception) {

        }

        try {
            $ls_input["rows"] = request::any("rows", true);
        } catch (exception $r_exception) {

        }

        $ls_input["ip"] = http::get_ip();

        return $ls_input;

    }

}