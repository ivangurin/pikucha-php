<?php

class album_manager
{

    static public function create()
    {

        return new album();

    }

    static public function get($i_id)
    {

        if (empty($i_id))
            throw new exception("Album not set");

        $l_id = $i_id;

        if ($l_id{0} == "a")
            $l_id = base::base62_decode(substr($l_id, 1));

        return new album($l_id);

    }

    static public function get_by_data($is_data)
    {

        return new album($is_data);

    }

    static public function get_by_user($i_user)
    {

        if (empty($i_user) || !is_numeric($i_user) || $i_user == "0")
            throw new exception("User not set");

        $lr_db = db::get();

        $lt_albums = $lr_db->query("select * from albums where userid = '" . db::escape($i_user) . "' and deleted = 0");

        return $lt_albums;
    }

}

class album
{

    public
        $id;
    private
        $s_data = array(),
        $updkz = "",
        $increase = false;

    public function __construct($i_data = false)
    {

        $lr_db = db::get();

        if ($i_data === false) {

            $this->updkz = "I";

            $lt_fields = $lr_db->query("show fields from albums");

            foreach ($lt_fields as $ls_field) {

                $this->s_data[$ls_field["Field"]] = "";

                switch ($ls_field["Field"]) {
                    case "Deleted":
                        $this->s_data[$ls_field["Field"]] = "0";
                        break;
                    case "Date_Created":
                        $this->s_data[$ls_field["Field"]] = date("YmdHis");
                        break;
                    case "Counter":
                        $this->s_data[$ls_field["Field"]] = "0";
                        break;
                }
            }

        } else {

            if (is_numeric($i_data)) {

                $l_id = db::escape($i_data);

                $t_albums = $lr_db->query("select * from albums where id = '$l_id'");

                if (!is_array($t_albums))
                    throw new exception("Album $l_id not found");

                if (count($t_albums) != 1)
                    throw new exception("Album $l_id not found");

                $this->s_data = current($t_albums);

            } elseif (is_array($i_data)) {

                $this->s_data = $i_data;

            } else {

                throw new exception("Erorr on get album");

            }

            $this->id = $this->s_data["ID"];

        }

    }

    public function get_id()
    {
        return $this->id;
    }

    public function get_password()
    {
        return $this->s_data["Password"];
    }

    public function get_images()
    {
        return image_manager::get_by_album($this->id);
    }

    public function get_data()
    {
        return $this->s_data;
    }

    public function get_public()
    {

        $ls_data["user"] = $this->get_user();
        $ls_data["id"] = $this->id;
        $ls_data["code"] = $this->get_code();
        $ls_data["name"] = $this->get_name();
        $ls_data["description"] = $this->get_description();
        $ls_data["counter"] = $this->get_counter();
        $ls_data["url"] = $this->get_url();
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
        return "a" . base::base62_encode($this->id);
    }

    public function get_name($i_save = false)
    {
        if ($i_save)
            return text::safe($this->s_data["Name"]);
        else
            return $this->s_data["Name"];
    }

    public function get_description($i_save = false)
    {
        if ($i_save)
            return text::safe($this->s_data["Description"]);
        else
            return $this->s_data["Description"];
    }

    public function get_counter()
    {
        return $this->s_data["Counter"];
    }

    public function get_url()
    {
        return "http://" . $_SERVER["HTTP_HOST"] . "/" . $this->get_code();
    }

    public function get_bbcode()
    {
        return "[url=" . $this->get_url() . "]" . $this->get_name() . "[/url]";
    }

    public function get_html()
    {
        return "<a href='" . $this->get_url() . "' title='" . $this->get_description() . "' target='_blank'>" . $this->get_name() . "</a>";
    }

    public function set_data($is_data)
    {

        if ($this->updkz == "D")
            return false;

        // No changes
        if ($this->s_data == $is_data)
            return true;

        // Set update flag
        if (empty($this->updkz))
            $this->updkz = "U";

        // Set old data
        $s_data = $this->s_data;

        // Set data
        $this->s_data = $is_data;

        // Set update time
        if ($this->updkz == "U")
            $this->s_data["Date_Changed"] = date("YmdHis");

        // Password Hash
        if (empty($this->s_data["Password"]))
            $this->s_data["Password_Hash"] = "";
        else {
            if ($this->s_data["Password"] != $s_data["Password"])
                $this->s_data["Password_Hash"] = "h" . substr(md5("h" . $this->s_data["Password"] . microtime()), 0, 9);
        }

        return true;
    }

    public function delete()
    {

        if ($this->s_data["Deleted"])
            return true;

        $this->updkz = "D";

        $this->s_data["Deleted"] = "1";
        $this->s_data["Date_Deleted"] = date("YmdHis");

        $this->save();

        return true;
    }

    public function save()
    {

        $lr_db = db::get();

        if ($this->updkz == "I") {

            $l_query = "insert into albums (UserID,
                                         Name,
                                         Description,
                                         Password,
                                         Password_Hash,
                                         Deleted,
                                         Date_Created,
                                         Counter)
                      value('" . db::escape($this->s_data["UserID"]) . "',
                            '" . db::escape($this->s_data["Name"]) . "',
                            '" . db::escape($this->s_data["Description"]) . "',
                            '" . db::escape($this->s_data["Password"]) . "',
                            '" . db::escape($this->s_data["Password_Hash"]) . "',
                            '" . db::escape($this->s_data["Deleted"]) . "',
                            '" . db::escape($this->s_data["Date_Created"]) . "',
                            '" . db::escape($this->s_data["Counter"]) . "' )
                           ";

            $lr_db->query($l_query);

            $this->id = $lr_db->get_id();
            $this->s_data["ID"] = $this->id;

            if ($this->id == 0)
                throw new exception("Error on create album. Album ID is zero.");
        }

        if ($this->updkz == "U") {

            $l_query = " update albums set";
            $l_query .= " UserID         = '" . db::escape($this->s_data["UserID"]) . "'";
            $l_query .= ", Name          = '" . db::escape($this->s_data["Name"]) . "'";
            $l_query .= ", Description   = '" . db::escape($this->s_data["Description"]) . "'";
            $l_query .= ", Password      = '" . db::escape($this->s_data["Password"]) . "'";
            $l_query .= ", Password_Hash = '" . db::escape($this->s_data["Password_Hash"]) . "'";
            $l_query .= ", Deleted       = '" . db::escape($this->s_data["Deleted"]) . "'";
            //if (!empty($this->s_data["Date_Created"]))
            //    $l_query .= ", Date_Created  = '" . db::escape($this->s_data["Date_Created"]) . "'";
            if (!empty($this->s_data["Date_Changed"]))
                $l_query .= ", Date_Changed  = '" . db::escape($this->s_data["Date_Changed"]) . "'";
            if (!empty($this->s_data["Date_Deleted"]))
                $l_query .= ", Date_Deleted  = '" . db::escape($this->s_data["Date_Deleted"]) . "'";
            $l_query .= " where ID = '" . $this->id . "'";

            $lr_db->query($l_query);
        }

        if ($this->updkz == "D") {

            $l_query = "update albums set";
            $l_query .= "  Deleted       = '" . $this->s_data["Deleted"] . "'";
            $l_query .= ", Date_Deleted  = '" . $this->s_data["Date_Deleted"] . "'";
            $l_query .= " where ID = '" . $this->s_data["ID"] . "'";

            $lr_db->query($l_query);

            $l_query = "update images set AlbumID = 0";
            $l_query .= " where UserID  = '" . $this->s_data["UserID"] . "'";
            $l_query .= "   and AlbumID = '" . $this->s_data["ID"] . "'";

            $lr_db->query($l_query);
        }

        if ($this->increase) {
            $lr_db->query("update albums set date_showed = now(), counter = counter + 1 where id = '" . $this->id . "'");
            $this->increase = false;
        }

        $this->updkz = "";
    }

    public function increase_counter()
    {

        if ($this->was_deleted())
            return false;

        if (service::is_bot()) {
            return false;
        }

        $this->increase = true;

        $this->save();

        return true;

    }

    public function was_deleted()
    {

        if ($this->updkz == "D" || $this->s_data["Deleted"])
            return true;
        else
            return false;

    }

}