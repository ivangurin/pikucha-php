<?php

class image_manager
{

    static public function create($i_path)
    {

        return new image(false, $i_path);

    }

    static public function get_by_data(array $is_data)
    {

        return new image($is_data);

    }

    static public function get_count($i_deleted = false)
    {

        $lr_db = db::get();

        if ($i_deleted === false)
            $t_images = $lr_db->query("SELECT count(*) FROM images");
        else
            $t_images = $lr_db->query("SELECT count(*) FROM images WHERE deleted = $i_deleted");

        $s_data = current($t_images);

        return $s_data["count(*)"];

    }

    static public function get_count_by_user($i_user_id, $i_deleted = false)
    {

        $lr_db = db::get();

        if (empty($i_user_id))
            throw new exception("User not selected");

        if ($i_deleted === false)
            $t_images = $lr_db->query("SELECT count(*) FROM images WHERE UserID = $i_user_id");
        else
            $t_images = $lr_db->query("SELECT count(*) FROM images WHERE UserID = $i_user_id AND Deleted = $i_deleted");

        $s_data = current($t_images);

        return $s_data["count(*)"];

    }

    static public function get_active($i_rows = 0, $i_offset = 0)
    {

        return image_manager::get_all($i_rows, $i_offset, 0);

    }

    static public function get_all($i_rows = false, $i_offset = false, $i_deleted = false, $i_order = false)
    {

        $lr_db = db::get();

        $l_query = "SELECT id FROM images";

        if ($i_deleted !== false)
            $l_query .= " where deleted = $i_deleted";

        if ($i_order !== false)
            $l_query .= " order by " . $i_order;
        else
            $l_query .= " order by id desc";

        if ($i_rows != 0)
            $l_query .= " limit $i_rows";

        if ($i_offset != 0)
            $l_query .= " offset $i_offset";

        $t_ids = $lr_db->query($l_query);

        $t_images = array();

        if (!is_array($t_ids))
            return $t_images;

        foreach ($t_ids as $s_id) {
            $t_images[] = $s_id["id"];
        }

        return $t_images;

    }

    static public function get_deleted($i_rows = 0, $i_offset = 0)
    {

        return image_manager::get_all($i_rows, $i_offset, 1);

    }

    static public function get_removed($i_rows = 0, $i_offset = 0)
    {

        return image_manager::get_all($i_rows, $i_offset, 2);

    }

    static public function get_by_user($i_user, $i_deleted = true, $i_rows = 0, $i_offset = 0)
    {

        if (empty($i_user))
            throw new exception("User not set");

        $lr_db = db::get();

        $l_query = "select * from images where userid = '" . db::escape($i_user) . "'";

        if ($i_deleted === true)
            $l_query .= " and deleted <= 1";
        else
            $l_query .= " and deleted = 0";

        $l_query .= " order by id asc";

        if ($i_rows != 0)
            $l_query .= " limit $i_rows";

        if ($i_offset != 0)
            $l_query .= " offset $i_offset";

        $t_images = $lr_db->query($l_query);

        if (!is_array($t_images))
            $t_images = array();

        return $t_images;

    }

    static public function get_by_album($i_album)
    {

        if (empty($i_album))
            throw new exception("Album not set");

        $lr_db = db::get();

        $l_query = "select * from images where albumid = '" . db::escape($i_album) . "'";
        $l_query .= " and deleted = 0";
        $l_query .= " order by id asc";

        $t_images = $lr_db->query($l_query);

        if (!is_array($t_images))
            $t_images = array();

        return $t_images;

    }

    static public function get_prev($i_image_id, $i_album_id = "")
    {

        if (empty($i_image_id))
            return false;

        $lr_db = db::get();

        if (empty($i_album_id))
            $t_ids = $lr_db->query("SELECT id FROM images WHERE id < $i_image_id AND deleted = 0 ORDER BY id DESC LIMIT 1");
        else
            $t_ids = $lr_db->query("SELECT id FROM images WHERE albumid = $i_album_id AND id < $i_image_id AND deleted = 0 ORDER BY id DESC LIMIT 1");

        if (!is_array($t_ids))
            return false;

        if (count($t_ids) == 0)
            return false;

        $s_id = current($t_ids);
        $lr_image = image_manager::get($s_id["id"]);

        return $lr_image;
    }

    static public function get($i_id)
    {

        if (empty($i_id))
            throw new exception("Image not set");

        $l_id = $i_id;

        if ($l_id{0} == "i")
            $l_id = base::base62_decode(substr($l_id, 1));

        return new image($l_id);

    }

    static public function get_next($i_image_id, $i_album_id = "")
    {

        if (empty($i_image_id))
            return false;

        $lr_db = db::get();

        if (empty($i_album_id))
            $t_ids = $lr_db->query("SELECT id FROM images WHERE id > $i_image_id AND deleted = 0 ORDER BY id ASC LIMIT 1");
        else
            $t_ids = $lr_db->query("SELECT id FROM images WHERE albumid = $i_album_id AND id > $i_image_id AND deleted = 0 ORDER BY id ASC LIMIT 1");

        if (!is_array($t_ids))
            return false;

        if (count($t_ids) == 0)
            return false;

        $ls_id = current($t_ids);

        $lr_image = image_manager::get($ls_id["id"]);

        return $lr_image;
    }

    static public function empty_trash($i_user = false)
    {

        $lr_db = db::get();

        if ($i_user === false)
            $lr_db->query("UPDATE images SET deleted = 2, date_deleted = now() WHERE deleted = 1");
        else
            $lr_db->query("UPDATE images SET deleted = 2, date_deleted = now() WHERE userid = $i_user AND deleted = 1");

    }

    static public function delete_old_images()
    {

        $lr_db = db::get();

        $lr_db->query(
            "UPDATE images
                JOIN users ON users.id = images.userid
                SET
                    images.deleted = 2,
                    images.date_deleted = NOW()
                WHERE
                    images.date_created < NOW() - INTERVAL 1 YEAR AND
                    images.date_showed = 0 AND
                    images.deleted = 0 AND
                    users.balance = 0");

        $lr_db->query(
            "UPDATE images
                JOIN users ON users.id = images.userid
                SET
                    images.deleted = 2,
                    images.date_deleted = NOW()
                WHERE
                    images.date_showed != 0 AND
                    images.date_showed < NOW() - INTERVAL 1 YEAR AND
                    images.deleted = 0 AND
                    users.balance = 0");

    }

    static public function delete_images($i_host)
    {


        $lt_images = image_manager::get_for_delete($i_host);

        $l_counter = 0;
        $l_deleted = 0;
        $l_errors = 0;
        $l_message = "";

        foreach ($lt_images as $ls_image) {

            $l_counter++;

            try {

                $lr_image = image_manager::get($ls_image["id"]);

                $lr_image->delete(3);

                $l_text = $ls_image["id"] . ": OK\n";

                $l_deleted++;

            } catch (exception $lr_exception) {

                $l_errors++;

                $l_text = $ls_image["id"] . ": " . $lr_exception->getMessage() . "\n";

                $l_message .= $l_text;

            }

            print_r($l_counter . ". " . $l_text . "\n");
        }

        if ($l_errors) {

            $l_message = "Errors: " . $l_errors . "\n\n" . $l_message;

            mail::report($i_host . ": Errors on nigthly delete images", $l_message);

        }

    }

    static public function get_for_delete($i_host)
    {

        $lr_db = db::get();

        $lt_images = $lr_db->query("SELECT id FROM images WHERE host = '$i_host' AND deleted = 2 ORDER BY id");

        if (!is_array($lt_images))
            $lt_images = array();
        return $lt_images;

    }

}

class image
{

    public
        $id;
    private
        $s_data = array(),
        $updkz,
        $r_image;

    public function __construct($i_data = false, $i_path = false)
    {

        // Get db instance
        $lr_db = db::get();

        if ($i_data === false) {

            if (!file_exists($i_path))
                throw new exception("File $i_path not found");

            try {
                $this->read($i_path);
            } catch (exception $r_exception) {
                throw new exception("This is not image");
            }

            // Set insert indicator
            $this->updkz = "I";

            // Get list of image fields
            $t_fields = $lr_db->query("show fields from images");

            // Set default values
            foreach ($t_fields as $s_field) {
                $this->s_data[$s_field["Field"]] = "";
                switch ($s_field["Field"]) {
                    case "Date_Created":
                        $this->s_data[$s_field["Field"]] = date("Y-m-d H:i:s");
                        break;

                    case "Thumbnail_Dimension":
                        $this->s_data[$s_field["Field"]] = conf::get("thumbnail_dimension");
                        break;

                    case "Thumbnail_Size":
                        $this->s_data[$s_field["Field"]] = 0;
                        break;

                    case "Deleted":
                        $this->s_data[$s_field["Field"]] = 0;
                        break;

                    case "Spam":
                        $this->s_data[$s_field["Field"]] = 0;
                        break;

                    case "Counter":
                        $this->s_data[$s_field["Field"]] = 0;
                        break;

                }
            }

            // Auto orientation
            image_service::orient($this->r_image);

            // Get info
            $ls_info = $this->get_info();

            $this->s_data["UserID"] = "0";
            $this->s_data["AlbumID"] = "0";

            $this->s_data["Host"] = http::get_host();

            $this->s_data["Name"] = file::get_name($i_path);
            $this->s_data["Format"] = $ls_info["format"];
            $this->s_data["Width"] = $ls_info["width"];
            $this->s_data["Height"] = $ls_info["height"];
            $this->s_data["Size"] = $ls_info["size"];
            $this->s_data["latitude"] = $ls_info["latitude"];
            $this->s_data["longitude"] = $ls_info["longitude"];

            if ($ls_info["format"] != "JPEG" &&
                $ls_info["format"] != "PNG" &&
                $ls_info["format"] != "BMP" &&
                $ls_info["format"] != "GIF"
            ) {
                $this->convert("JPEG");
            }

        } else {

            if (is_array($i_data)) {

                // Set data
                $this->s_data = $i_data;

            } else {

                // Get data
                $t_images = $lr_db->query("select * from images where id = '" . db::escape($i_data) . "'");

                if (!is_array($t_images))
                    throw new exception("Image $i_data not found");

                if (count($t_images) != 1)
                    throw new exception("Image $i_data not found");

                $this->s_data = current($t_images);
            }

            $this->id = $this->s_data["ID"];

        }

    }

    public function read($i_path)
    {
        try {
            $this->r_image = image_service::read($i_path);
        } catch (exception $r_exception) {
            throw new exception("Error on read image: " . $r_exception->getMessage());
        }
    }

    public function get_info()
    {

        try {

            if (!is_object($this->r_image))
                $this->read($this->get_path());

            if (is_object($this->r_image))
                return image_service::get_info($this->r_image);

        } catch (exception $r_exception) {
            throw new exception("Error on get image info: " . $r_exception->getMessage());
        }

        return false;

    }

    public function get_path()
    {

        $lr_host = host::get($this->get_host());

        return $lr_host->get_path_content() . image_service::get_path($this->id) . "/" . $this->id;

    }

    public function get_host()
    {
        return $this->s_data["Host"];
    }

    public function convert($i_format)
    {

        if ($this->get_format() == $i_format)
            return true;

        if ($this->get_format() == "GIF")
            return false;

        try {

            if (!is_object($this->r_image))
                $this->read($this->get_path());

            if (is_object($this->r_image)) {

                image_service::convert($this->r_image, $i_format);

                $this->set_info();

                if (empty($this->updkz))
                    $this->updkz = "U";

            }

        } catch (exception $r_exception) {
            throw new exception("Error on image convert: " . $r_exception->getMessage());
        }

        return false;

    }

    public function get_format()
    {
        return $this->s_data["Format"];
    }

    public function set_info()
    {

        try {
            $ls_info = $this->get_info();
        } catch (exception $r_exception) {
            throw new exception("Error on set image info: " . $r_exception->getMessage());
        }

        $s_data = $this->get_data();

        $s_data["Format"] = $ls_info["format"];
        $s_data["Width"] = $ls_info["width"];
        $s_data["Height"] = $ls_info["height"];
        $s_data["Size"] = $ls_info["size"];

        $this->set_data($s_data);

    }

    public function get_data()
    {
        return $this->s_data;
    }

    public function set_data($is_data)
    {

        // No changes
        if ($this->s_data == $is_data)
            return true;

        // Set update flag
        if (empty($this->updkz))
            $this->updkz = "U";

        // Set data
        $this->s_data = $is_data;

        // Set update time
        if ($this->updkz == "U")
            $this->s_data["Date_Changed"] = date("YmdHis");

        return true;
    }

    function __destruct()
    {

        try {

            if (is_object($this->r_image))
                $this->r_image->destroy();

        } catch (exception $r_exception) {
            throw new exception("Error on destruct image: " . $r_exception->getMessage());
        }


    }

    public function get_id()
    {
        return $this->id;
    }

    public function get_album_url()
    {

        try {

            $lr_album = album_manager::get($this->get_album());

            return $lr_album->get_url();

        } catch (exception $r_exception) {

            return false;

        }

    }

    public function get_album()
    {
        return $this->s_data["AlbumID"];
    }

    public function set_name($i_name)
    {
        $ls_data = $this->get_data();
        $ls_data["Name"] = $i_name;
        $this->set_data($ls_data);
    }

    public function get_dimension_text()
    {
        $l_dimension = $this->s_data["Width"] . "&nbsp;x&nbsp;" . $this->s_data["Height"];
        return $l_dimension;
    }

    public function get_date_created()
    {
        return $this->s_data["Date_Created"];
    }

    public function get_date_showed()
    {
        return $this->s_data["Date_Showed"];
    }

    public function get_public()
    {

        $ls_data["user"] = $this->get_user();
        $ls_data["id"] = $this->id;
        $ls_data["code"] = $this->get_code();
        $ls_data["host"] = $this->get_host();
        $ls_data["album"] = $this->get_album();
        $ls_data["name"] = $this->get_name();
        $ls_data["width"] = $this->get_width();
        $ls_data["height"] = $this->get_height();
        $ls_data["size"] = $this->get_size();
        $ls_data["size_text"] = $this->get_size_text();
        $ls_data["latitude"] = $this->s_data["latitude"];
        $ls_data["longitude"] = $this->s_data["longitude"];
        $ls_data["description"] = $this->get_description();
        $ls_data["thumbnail_dimension"] = $this->get_thumbnail_dimension();
        $ls_data["thumbnail_size"] = $this->get_thumbnail_size();
        $ls_data["thumbnail_text"] = $this->get_thumbnail_text();
        $ls_data["date_created_text"] = $this->get_date_created_text();
        $ls_data["date_showed_text"] = $this->get_date_showed_text();
        $ls_data["date_deleted_text"] = $this->get_date_deleted_text();
        $ls_data["title"] = $this->get_title();
        $ls_data["title_ext"] = $this->get_title_ext();
        $ls_data["counter"] = $this->get_counter();
        if ($this->was_deleted())
            $ls_data["deleted"] = "1";
        else
            $ls_data["deleted"] = "0";
        $ls_data["url"] = $this->get_url();
        $ls_data["url_direct"] = $this->get_url_original(true);
        $ls_data["url_thumbnail"] = $this->get_url_thumbnail();
        $ls_data["bbcode"] = $this->get_bbcode();
        $ls_data["html"] = $this->get_html();

        return $ls_data;

    }

    public function get_user()
    {
        return $this->s_data["UserID"];
    }

    public function get_code()
    {
        return "i" . base::base62_encode($this->id);
    }

    public function get_name()
    {
        return $this->s_data["Name"] . "." . strtolower($this->get_format());
    }

    public function get_width()
    {
        return $this->s_data["Width"];
    }

    public function get_height()
    {
        return $this->s_data["Height"];
    }

    public function get_size()
    {
        return $this->s_data["Size"];
    }

    public function get_size_text()
    {
        if ($this->s_data["Size"] >= 1024 * 1024)
            $l_size = "&nbsp;" . round($this->s_data["Size"] / 1024 / 1024, 2) . "&nbsp;Mb";
        elseif ($this->s_data["Size"] >= 1024)
            $l_size = "&nbsp;" . round($this->s_data["Size"] / 1024, 2) . "&nbsp;Kb"; else
            $l_size = "&nbsp;" . $this->s_data["Size"] . "&nbsp;bytes";
        return $l_size;
    }

    public function get_description($i_safe = false)
    {

        $l_description = $this->s_data["Description"];

        if ($i_safe)
            $l_description = text::safe($l_description);

        return $l_description;

    }

    public function get_thumbnail_dimension()
    {
        return $this->s_data["Thumbnail_Dimension"];
    }

    public function get_thumbnail_size()
    {
        return $this->s_data["Thumbnail_Size"];
    }

    public function get_thumbnail_text()
    {
        return $this->s_data["Thumbnail_Text"];
    }

    public function get_date_created_text()
    {

        $ls_month = array("01" => "Янв", "02" => "Фев", "03" => "Мар", "04" => "Апр", "05" => "Май", "06" => "Июн", "07" => "Июл", "08" => "Авг", "09" => "Снт", "10" => "Окт", "11" => "Нбр", "12" => "Дек");

        $ls_now = service::parse_date();

        $ls_created = service::parse_date($this->s_data["Date_Created"]);

        if ($ls_now["year"] == $ls_created["year"]) {

            if ($ls_now["month"] == $ls_created["month"] && $ls_now["day"] == $ls_created["day"]) {
                return $ls_created["hours"] . ":" . $ls_created["minutes"];
            } else {
                return $ls_created["day"] . " " . $ls_month[$ls_created["month"]];
            }

        } else {

            return $ls_month[$ls_created["month"]] . "'" . substr($ls_created["year"], 2);

        }

    }

    public function get_date_showed_text()
    {
        $ls_showed = service::parse_date($this->s_data["Date_Showed"]);
        return $ls_showed["day"] . "." . $ls_showed["month"] . "." . $ls_showed["year"];
    }

    public function get_date_deleted_text()
    {
        $ls_deleted = service::parse_date($this->s_data["Date_Deleted"]);
        return $ls_deleted["day"] . "." . $ls_deleted["month"] . "." . $ls_deleted["year"];
    }

    public function get_title($i_safe = false)
    {

        $l_title = $this->get_description($i_safe);

        if (empty($l_title))
            $l_title = $this->get_name();

        return $l_title;

    }

    public function get_title_ext($i_safe = false)
    {

        $l_title = $this->get_description($i_safe);

        if (empty($l_title))
            $l_title = $this->s_data["src_title"];

        if (empty($l_title))
            $l_title = $this->get_name();

        return $l_title;

    }

    public function get_counter()
    {
        return $this->s_data["Counter"];
    }

    public function was_deleted($i_deleted = 1)
    {

        if ($this->s_data["Deleted"] >= $i_deleted)
            return true;

        return false;

    }

    public function get_url($i_real = false)
    {

        if ($i_real)
            return "http://" . $this->get_host() . "/" . $this->get_code();
        else
            return "http://" . conf::get("host") . "/" . $this->get_code();

    }

    public function get_url_original($i_real = false)
    {
        return $this->get_url($i_real) . "/" . $this->get_fake_name();
    }

    public function get_fake_name()
    {
        return urlencode($this->get_name());
        //return "image." . strtolower($this->get_format());
    }

    public function get_url_thumbnail($i_real = false)
    {
        return $this->get_url($i_real) . "/thumbnail/" . $this->get_fake_name();
    }

    public function get_bbcode()
    {
        return "[url=" . $this->get_url() . "][img]" . $this->get_url_thumbnail() . "[/img][/url]";
    }

    public function get_html()
    {
        return "<a href='" . $this->get_url() . "' title='" . $this->get_title() . "' target='_blank'><img src='" . $this->get_url_thumbnail() . "' border='0' alt='" . $this->get_name() . "'></a>";
    }

    public function delete($i_deleted = 1)
    {

        if ($this->was_deleted($i_deleted))
            throw new exception("Image alredy was deleted");

        $this->s_data["Deleted"] = $i_deleted;
        $this->s_data["Date_Deleted"] = date("YmdHis");

        $lr_db = db::get();
        $lr_db->query("update images set deleted = $i_deleted, date_deleted = now() where id = '" . $this->id . "'");

        $this->delete_thumbnail();

        if ($i_deleted == 3)
            file::delete($this->get_path());

        return true;
    }

    public function delete_thumbnail()
    {
        file::delete($this->get_path_thumbnail());
    }

    public function get_path_thumbnail()
    {

        $lr_host = host::get($this->get_host());

        return $lr_host->get_path_cache() . "/" . $this->id;

    }

    public function restore()
    {

        if ($this->is_active())
            return true;

        if ($this->was_deleted(3))
            throw new exception("Image can not be restore");

        $this->s_data["Deleted"] = 0;
        $this->s_data["Date_Deleted"] = date("YmdHis");

        $lr_db = db::get();
        $lr_db->query("update images set deleted = 0, date_deleted = now() where id = '" . $this->id . "'");

        return true;

    }

    public function is_active()
    {

        if ($this->s_data["Deleted"] == 0)
            return true;

        return false;

    }

    public function save()
    {

        // Get db instance
        $lr_db = db::get();

        if ($this->updkz == "I") {

            $l_query = "insert into images (UserID,
                    AlbumID,
                    Host,
                    Format,
                    Name,
                    Width,
                    Height,
                    Size,
                    latitude,
                    longitude,
                    Description,
                    Thumbnail_Dimension,
                    Thumbnail_Size,
                    Thumbnail_Text,
                    Deleted,
                    Date_Created,
                    Spam,
                    Counter,
                    ip )
                    value('" . db::escape($this->s_data["UserID"]) . "',
                          '" . db::escape($this->s_data["AlbumID"]) . "',
                          '" . db::escape($this->s_data["Host"]) . "',
                          '" . db::escape($this->s_data["Format"]) . "',
                          '" . db::escape($this->s_data["Name"]) . "',
                          '" . db::escape($this->s_data["Width"]) . "',
                          '" . db::escape($this->s_data["Height"]) . "',
                          '" . db::escape($this->s_data["Size"]) . "',
                          " . db::escape($this->s_data["latitude"]) . ",
                          " . db::escape($this->s_data["longitude"]) . ",
                          '" . db::escape($this->s_data["Description"]) . "',
                          '" . db::escape($this->s_data["Thumbnail_Dimension"]) . "',
                          '" . db::escape($this->s_data["Thumbnail_Size"]) . "',
                          '" . db::escape($this->s_data["Thumbnail_Text"]) . "',
                          '" . db::escape($this->s_data["Deleted"]) . "',
                          '" . db::escape($this->s_data["Date_Created"]) . "',
                          '" . db::escape($this->s_data["Spam"]) . "',
                          '" . db::escape($this->s_data["Counter"]) . "',
                          '" . db::escape($this->s_data["ip"]) . "')";

            $lr_db->query($l_query);

            $this->set_id($lr_db->get_id());

            try {
                $this->write();
            } catch (exception $r_exception) {
                throw new exception("On image create: " . $r_exception->getMessage());
            }

            $this->create_thumbnail();
        }

        if ($this->updkz == "U") {

            $l_query = "update images set";
            $l_query .= "  UserID               = '" . db::escape($this->s_data["UserID"]) . "'";
            $l_query .= ", AlbumID              = '" . db::escape($this->s_data["AlbumID"]) . "'";
            $l_query .= ", Format               = '" . db::escape($this->s_data["Format"]) . "'";
            $l_query .= ", Name                 = '" . db::escape($this->s_data["Name"]) . "'";
            $l_query .= ", Width                = '" . db::escape($this->s_data["Width"]) . "'";
            $l_query .= ", Height               = '" . db::escape($this->s_data["Height"]) . "'";
            $l_query .= ", Size                 = '" . db::escape($this->s_data["Size"]) . "'";
            $l_query .= ", latitude             = '" . db::escape($this->s_data["latitude"]) . "'";
            $l_query .= ", longitude            = '" . db::escape($this->s_data["longitude"]) . "'";
            $l_query .= ", Description          = '" . db::escape($this->s_data["Description"]) . "'";
            $l_query .= ", Thumbnail_Dimension  = '" . db::escape($this->s_data["Thumbnail_Dimension"]) . "'";
            $l_query .= ", Thumbnail_Size       = '" . db::escape($this->s_data["Thumbnail_Size"]) . "'";
            $l_query .= ", Thumbnail_Text       = '" . db::escape($this->s_data["Thumbnail_Text"]) . "'";
            $l_query .= ", Deleted              = '" . db::escape($this->s_data["Deleted"]) . "'";
            $l_query .= ", Spam                 = '" . db::escape($this->s_data["Spam"]) . "'";

            if (!empty($this->s_data["Date_Changed"]))
                $l_query .= ", Date_Changed  = '" . db::escape($this->s_data["Date_Changed"]) . "'";

            if (!empty($this->s_data["Date_Deleted"]))
                $l_query .= ", Date_Deleted  = '" . db::escape($this->s_data["Date_Deleted"]) . "'";

            $l_query .= " where id = '" . $this->id . "'";

            $lr_db->query($l_query);

            try {
                $this->write();
            } catch (exception $r_exception) {
                throw new exception("Error on image write: " . $r_exception->getMessage());
            }

            $this->delete_thumbnail();
        }

        $this->updkz = "";

        return true;

    }

    private function set_id($i_id)
    {

        $this->id = $i_id;

        $this->s_data["ID"] = $i_id;

    }

    public function write()
    {

        if (is_object($this->r_image)) {

            try {

                image_service::write($this->r_image, $this->get_path());

                //image_service::destroy($this->r_image);

            } catch (exception $r_exception) {
                throw new exception("Error on image write: " . $r_exception->getMessage());
            }

        }

    }

    public function create_thumbnail()
    {

        if (file_exists($this->get_path_thumbnail()))
            return true;

        try {

            if (!is_object($this->r_image))
                $this->read($this->get_path());

            if (is_object($this->r_image))
                image_service::create_thumbnail($this->r_image, $this->get_path_thumbnail(), $this->get_thumbnail_dimension(), $this->make_thumbnail_text());

        } catch (exception $r_exception) {
            throw new exception("Error on create thumbnail: " . $r_exception->getMessage());
        }

        return true;

    }

    public function make_thumbnail_text()
    {

        if ($this->get_thumbnail_size()) {

            if (round($this->get_size() / 1024 / 1024, 2) >= 1)
                $l_size = round($this->get_size() / 1024 / 1024, 2) . "Mb";
            elseif (round($this->get_size() / 1024) >= 1)
                $l_size = round($this->get_size() / 1024) . "Kb"; else
                $l_size = $this->get_size() . "b";

            $l_text = $this->get_width() . " x " . $this->get_height() . " " . $l_size;

        } else {

            $l_text = $this->get_thumbnail_text();

        }

        return $l_text;

    }

    public function resize($i_dimension)
    {
        try {

            if (!is_object($this->r_image))
                $this->read($this->get_path());

            if (is_object($this->r_image)) {

                image_service::resize($this->r_image, $i_dimension);

                $this->set_info();

                if (empty($this->updkz))
                    $this->updkz = "U";

            }

        } catch (exception $r_exception) {
            throw new exception("Error on image resize: " . $r_exception->getMessage());
        }

    }

    public function rotate($i_degrees)
    {

        try {

            if (!is_object($this->r_image))
                $this->read($this->get_path());

            if (is_object($this->r_image)) {

                image_service::rotate($this->r_image, $i_degrees);

                $this->set_info();

                if (empty($this->updkz))
                    $this->updkz = "U";

            }

        } catch (exception $r_exception) {
            throw new exception("Error on image rotate: " . $r_exception->getMessage());
        }

    }

    public function show($i_size = "")
    {

        $l_path = "";

        if (empty($i_size)) {

            if (pikucha::can_show($this->get_user()) == true) {

                if (http::get_host() != $this->get_host()) {

                    http::location($this->get_url_original(true));

                    return true;

                }

                $this->count();

                $l_path = $this->get_path_download_original();

            } else {

                if (http::get_host() != $this->get_host()) {

                    http::location($this->get_url_thumbnail(true));

                    return true;

                }

                $this->create_thumbnail();

                $l_path = $this->get_path_download_thumbnail();

            }

        } elseif ($i_size == "thumbnail") {

            if (http::get_host() != "beta.pikucha.ru" &&
                http::get_host() != $this->get_host()
            ) {
                http::location($this->get_url_thumbnail(true));
                return true;
            }

            $this->create_thumbnail();

            $l_path = $this->get_path_download_thumbnail();

        } else {
            http::forbidden();
        }

        $this->update_src();

        http::download($l_path);

        return true;

    }

    public function count()
    {

        if ($this->was_deleted())
            return false;

        if (service::is_bot())
            return false;

        $lr_db = db::get();
        $lr_db->query("update images set date_showed = now(), counter = counter + 1 where id = '" . $this->id . "'");

        return true;

    }

    public function get_path_download_original()
    {

        $lr_host = host::get($this->get_host());

        return $lr_host->get_path_content_download() . image_service::get_path($this->id) . "/" . $this->id;
    }

    public function get_path_download_thumbnail()
    {

        $lr_host = host::get($this->get_host());

        return $lr_host->get_path_cache_download() . "/" . $this->id;

    }

    public function update_src()
    {

        if (!empty($this->s_data["src_title"]))
            return true;

        $l_url = image_service::get_src_url();

        if (empty($l_url))
            return false;

        $l_title = image_service::get_src_title();

        if (empty($l_title))
            return false;

        $lr_db = db::get();
        $lr_db->query(sprintf("UPDATE images SET src_url = '%s', src_title = '%s' WHERE id = '%s'", db::escape($l_url), db::escape($l_title), $this->id));

        return true;

    }

}

class image_service
{

    static function get_path($i_id = 0)
    {
        $l_id = $i_id;

        $m = bcdiv($l_id, 1000000);

        $l_id = $l_id - $m * 1000000;

        $n = bcdiv($l_id, 100000);

        return "/" . $m . "/" . $n;

    }

    static function read($i_path)
    {

        if (!file::exist($i_path))
            throw new exception("File not found");

        $lr_image = new Imagick();

        $lr_image->readImage($i_path);

        $l_format = $lr_image->getImageFormat();

        if (empty($l_format))
            throw new exception("This is not image");

        return $lr_image;

    }

    static function write(Imagick $ir_image, $i_path)
    {

        file::delete($i_path);

        $ir_image->writeImages($i_path, true);

    }

    static function get_format(Imagick $ir_image)
    {
        return $ir_image->getImageFormat();
    }

    static function convert(Imagick $ir_image, $i_format)
    {
        $ir_image->setImageFormat($i_format);
    }

    static function resize(Imagick $ir_image, $i_dimension)
    {
        $ir_image->thumbnailImage($i_dimension, $i_dimension, true);
    }

    static function orient(Imagick $ir_image)
    {

        $l_orientation = $ir_image->getImageOrientation();

        $l_oriented = false;

        switch ($l_orientation) {
            case imagick::ORIENTATION_UNDEFINED:
                return;

            case imagick::ORIENTATION_TOPLEFT:
                return;

            case imagick::ORIENTATION_TOPRIGHT:
                $ir_image->flipImage();
                self::rotate($ir_image, 180);
                $l_oriented = true;
                break;

            case imagick::ORIENTATION_BOTTOMRIGHT:
                self::rotate($ir_image, 180);
                $l_oriented = true;
                break;

            case imagick::ORIENTATION_BOTTOMLEFT:
                $ir_image->flipImage();
                $l_oriented = true;
                break;

            case imagick::ORIENTATION_LEFTTOP:
                $ir_image->flipImage();
                self::rotate($ir_image, 90);
                $l_oriented = true;
                break;

            case imagick::ORIENTATION_RIGHTTOP:
                self::rotate($ir_image, 90);
                $l_oriented = true;
                break;

            case imagick::ORIENTATION_RIGHTBOTTOM:
                $ir_image->flipImage();
                self::rotate($ir_image, -90);
                $l_oriented = true;
                break;

            case imagick::ORIENTATION_LEFTBOTTOM:
                self::rotate($ir_image, -90);
                $l_oriented = true;
                break;

        }

        if ($l_oriented)
            $ir_image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);

    }

    static function rotate(Imagick $ir_image, $i_degrees)
    {
        $ir_image->rotateImage(new ImagickPixel(), $i_degrees);
    }

    static function create_thumbnail(Imagick $ir_image, $i_to, $i_dimension, $i_text = "")
    {

        if (!is_object($ir_image))
            return false;

        // Delete old thumbnail
        file::delete($i_to);

        $ls_info = self::get_info($ir_image);

        $lr_image = (object)$ir_image->clone();

        if ($lr_image->getImageFormat() == "GIF") {

            $lt_images = $lr_image->coalesceImages();

            foreach ($lt_images as $lr_image) {

                $lr_image = (object)$lr_image;

                if ($lr_image->getImageWidth() > $i_dimension ||
                    $lr_image->getImageHeight() > $i_dimension
                ) {

                    $lr_image->thumbnailImage($i_dimension, $i_dimension, true);

                }

                if (!empty($i_text))
                    self::create_bar($lr_image, $i_text);

            }

            $lr_image = $lt_images;

        } else {

            if ($ls_info["width"] > $i_dimension || $ls_info["height"] > $i_dimension) {

                if ($lr_image->getImageFormat() != "PNG")
                    $lr_image->setImageFormat("JPEG");

                $lr_image->thumbnailImage($i_dimension, $i_dimension, true);

            }

            if (!empty($i_text))
                self::create_bar($lr_image, $i_text);


        }

        $lr_image->writeImages($i_to, true);
        $lr_image->Destroy();

        return true;

    }

    static function get_info(Imagick $ir_image)
    {

        $ls_exif = $ir_image->getImageProperties("exif:*");

        $ls_info["type"] = $ir_image->getFormat();
        $ls_info["format"] = $ir_image->getImageFormat();
        $ls_info["size"] = $ir_image->getImageSize();

        if ($ls_info["format"] == 'GIF') {

            $lt_images = $ir_image->coalesceImages();

            foreach ($lt_images as $lr_image) {

                $lr_image = (object)$lr_image;

                $ls_info["width"] = $lr_image->getImageWidth();
                $ls_info["height"] = $lr_image->getImageHeight();

                break;

            }

        } else {
            $ls_info["width"] = $ir_image->getImageWidth();
            $ls_info["height"] = $ir_image->getImageHeight();
        }

        if ($ls_info["size"] == 0)
            $ls_info["size"] = strlen($ir_image->getImagesBlob());

        $ls_info["latitude"] = self::get_latitude($ls_exif);
        $ls_info["longitude"] = self::get_longitude($ls_exif);

        return $ls_info;

    }

    static public function get_latitude($is_exif)
    {

        if (isset($is_exif["exif:GPSLatitude"]) && isset($is_exif["exif:GPSLatitudeRef"]))
            return self::eval_gps($is_exif["exif:GPSLatitude"], $is_exif["exif:GPSLatitudeRef"]);
        else
            return 0;

    }

    static public function eval_gps($i_coordinate, $i_hemisphere)
    {

        $ls_coordinate = explode(",", $i_coordinate);

        for ($i = 0; $i < 3; $i++) {

            $l_part = explode('/', $ls_coordinate[$i]);

            if (count($l_part) == 1) {
                $ls_coordinate[$i] = $l_part[0];
            } else if (count($l_part) == 2) {
                $ls_coordinate[$i] = floatval($l_part[0]) / floatval($l_part[1]);
            } else {
                $ls_coordinate[$i] = 0;
            }

        }

        list($l_degrees, $l_minutes, $l_seconds) = $ls_coordinate;

        $l_sign = ($i_hemisphere == "W" || $i_hemisphere == "S") ? -1 : 1;

        return $l_sign * ($l_degrees + $l_minutes / 60 + $l_seconds / 3600);

    }

    static public function get_longitude($is_exif)
    {

        if (isset($is_exif["exif:GPSLongitude"]) && isset($is_exif["exif:GPSLongitudeRef"]))
            return self::eval_gps($is_exif["exif:GPSLongitude"], $is_exif["exif:GPSLongitudeRef"]);
        else
            return 0;

    }

    static public function create_bar(Imagick $ir_image, $i_text)
    {

        $lr_text = new ImagickDraw();

        $lr_text->setFontSize(12);
        $lr_text->setFontFamily("Arial");
        $lr_text->setTextAntialias(true);
        $lr_text->setFillColor(new ImagickPixel("white"));
        $lr_text->setTextAlignment(2);
        $lr_text->annotation($ir_image->getImageWidth() / 2, 12, $i_text);

        $lr_bar = new Imagick();

        $lr_bar->newImage($ir_image->getImageWidth(), 15, "black", "jpeg");
        $lr_bar->drawImage($lr_text);

        $ir_image->compositeImage($lr_bar, imagick::COMPOSITE_OVER, 0, $ir_image->getImageHeight() - 15);

    }

    static public function destroy(Imagick $ir_image)
    {
        $ir_image->Destroy();
    }

    static public function get_src_title()
    {

        $l_url = self::get_src_url();

        if (empty($l_url))
            return "";

        return http::get_title($l_url);

    }

    static public function get_src_url()
    {

        $l_url = http::get_referrer(true);

        if (empty($l_url))
            return "";

        if (service::is_pikucha($l_url) == true)
            return "";

        if (service::is_search($l_url) == true)
            return "";

        return $l_url;

    }

}