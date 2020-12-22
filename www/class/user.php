<?php

class user_manager
{

    static public function get_by_token($i_token)
    {

        $l_id = user_manager::get_id_by_token($i_token);

        return user_manager::get($l_id);

    }

    static public function get_id_by_token($i_token)
    {

        if (empty($i_token))
            throw new exception("Token not set");

        try {
            return cache::get("user_static::get_id_by_token", $i_token);
        } catch (exception $e) {

        }

        $l_query = "SELECT user_id
                        FROM
                            tokens
                        WHERE
                            guid = '%s' AND
                            signin_ip = '%s' AND
                            signin_at >= now() - INTERVAL 7 DAY AND
                            signout_at = 0";

        $l_query = sprintf($l_query, db::escape($i_token), http::get_ip());

        $lr_db = db::get();

        $lt_users = $lr_db->query($l_query);

        if (!is_array($lt_users))
            throw new exception("User not found");

        if (count($lt_users) == 0)
            throw new exception("User not found");

        if (count($lt_users) > 1) {
            mail::report("There are users with same token", "token: " . $i_token);
            throw new exception("Internal error");
        }

        $ls_user = current($lt_users);

        cache::set("user_static::get_id_by_token", $i_token, $ls_user["user_id"]);

        return $ls_user["user_id"];

    }

    static public function get($i_id)
    {

        if (empty($i_id))
            throw new exception("User ID was not set");

        try {
            return cache::get("user", $i_id);
        } catch (exception $exc) {

        }

        $lr_user = new user($i_id);

        cache::set("user", $i_id, $lr_user);

        return $lr_user;

    }

    static public function get_by_vk_id($i_vk_id)
    {

        $l_id = user_manager::get_id_by_vk_id($i_vk_id);

        if ($l_id === false)
            return false;

        return user_manager::get($l_id);

    }

    static public function get_id_by_vk_id($i_vk_id)
    {

        if (empty($i_vk_id))
            throw new exception("VK user is empty");

        try {
            return cache::get("vk_id2id", $i_vk_id);
        } catch (exception $e) {

        }

        $lr_db = db::get();

        $lt_users = $lr_db->query("select id from users where vk_id = '" . db::escape($i_vk_id) . "'");

        if (!is_array($lt_users))
            return false;

        if (count($lt_users) == 0)
            return false;

        //if (count($lt_users) > 1){
        //    mail::report("There are users with same email", "Email: " . $i_email);
        //    throw new exception("Internal error exist");
        //}

        $ls_user = current($lt_users);

        cache::set("vk_id2id", $i_vk_id, $ls_user["id"]);

        return $ls_user["id"];

    }

    static public function registrate($i_name, $i_email, $i_password)
    {

        if (empty($i_name))
            throw new exception("User name not set");

        if (empty($i_email))
            throw new exception("User email not set");

        if (empty($i_password))
            throw new exception("User password not set");

        try {
            $r_user = user_manager::get_by_email($i_email);
        } catch (exception $r_exception) {
            $r_user = "";
        }

        if (is_object($r_user))
            throw new exception("Пользователь с почтой $i_email уже зарегистрирован");

        $r_user = user_manager::create();

        $s_user = $r_user->get_data();

        $s_user["Name"] = $i_name;
        $s_user["Email"] = $i_email;
        $s_user["Password"] = $i_password;

        $r_user->set_data($s_user);

        $r_user->save();

        return $r_user;

    }

    static public function get_by_email($i_email)
    {

        $l_id = user_manager::get_id_by_email($i_email);

        return user_manager::get($l_id);

    }

    static public function get_id_by_email($i_email)
    {

        if (empty($i_email))
            throw new exception("User email no set");

        try {
            return cache::get("get_id_by_email", $i_email);
        } catch (exception $exc) {

        }

        $lr_db = db::get();

        $lt_users = $lr_db->query("select id from users where email = '" . db::escape($i_email) . "'");

        if (!is_array($lt_users))
            throw new exception("User not found");

        if (count($lt_users) == 0)
            throw new exception("User not found");

        if (count($lt_users) > 1) {
            mail::report("There are users with same email", "Email: " . $i_email);
            throw new exception("Internal error exist");
        }

        $ls_user = current($lt_users);

        cache::set("get_id_by_email", $i_email, $ls_user["id"]);

        return $ls_user["id"];

    }

    static public function create()
    {
        return new user();
    }

    static public function signin($i_email, $i_password)
    {

        if (empty($i_email))
            throw new exception("User email not set");

        if (empty($i_password))
            throw new exception("User password not set");

        $lr_user = user_manager::get_by_email($i_email);

        if (md5($lr_user->get_password()) != $i_password)
            throw new exception("Password is wrong");

        if ($lr_user->was_deleted() === true)
            throw new exception("User was deleted");

        return $lr_user->signin();

    }

}

class user
{

    public
        $id,
        $s_data = array();
    private
        $mv_updkz;

    public function __construct($i_id = false)
    {

        $r_db = db::get();

        if ($i_id == false) {

            $this->mv_updkz = "I";

            $t_fields = $r_db->query("show fields from users");

            foreach ($t_fields as $s_field) {

                $this->s_data[$s_field["Field"]] = "";

                switch ($s_field["Field"]) {
                    case "Deleted":
                        $this->s_data[$s_field["Field"]] = "0";
                        break;
                    case "Date_Created":
                        $this->s_data[$s_field["Field"]] = date("YmdHis");
                        break;
                    case "Thumbnail_Dimension":
                        $this->s_data[$s_field["Field"]] = conf::get("thumbnail_dimension");
                        break;
                    case "Thumbnail_Size":
                        $this->s_data[$s_field["Field"]] = "0";
                        break;
                    case "Bmp2Jpg":
                        $this->s_data[$s_field["Field"]] = "1";
                        break;
                }
            }
        } else {

            $t_users = $r_db->query("select * from users where id = '" . db::escape($i_id) . "'");

            if (!is_array($t_users))
                throw new exception("User $i_id not found");

            if (count($t_users) != 1)
                throw new exception("User $i_id not found");

            $this->id = $i_id;
            $this->s_data = current($t_users);
        }
    }

    public function get_id()
    {
        return $this->s_data["ID"];
    }

    public function get_balance()
    {
        return $this->s_data["balance"];
    }

    public function is_show_size()
    {
        if ($this->s_data["Thumbnail_Size"] == "1")
            return true;
        return false;
    }

    public function is_convert()
    {
        if ($this->s_data["Bmp2Jpg"] == "1")
            return true;
        return false;
    }

    public function get_public($i_personal)
    {

        $ls_data["id"] = $this->id;
        $ls_data["name"] = $this->get_name();

        if ($i_personal == true) {

            $ls_data["email"] = $this->get_email();
            $ls_data["thumbnail_dimension"] = $this->get_thumbnail_dimension();
            $ls_data["thumbnail_size"] = $this->get_thumbnail_size();
            $ls_data["convert"] = $this->get_convert();

        }

        return $ls_data;
    }

    public function get_name()
    {
        return $this->s_data["Name"];
    }

    public function get_email()
    {
        return $this->s_data["Email"];
    }

    public function get_thumbnail_dimension()
    {
        return $this->s_data["Thumbnail_Dimension"];
    }

    public function get_thumbnail_size()
    {
        return $this->s_data["Thumbnail_Size"];
    }

    public function get_convert()
    {
        return $this->s_data["Bmp2Jpg"];
    }

    public function is_root()
    {

        if ($this->id == conf::get("root_user_id"))
            return true;

        return false;

    }

    public function set_name($i_name)
    {
        $ls_data = $this->get_data();
        $ls_data["Name"] = $i_name;
        $this->set_data($ls_data);
    }

    public function get_data()
    {
        return $this->s_data;
    }

    public function set_data($is_user)
    {

        if ($this->mv_updkz == "D")
            return false;

        if ($this->s_data == $is_user)
            return true;

        if (empty($this->mv_updkz))
            $this->mv_updkz = "U";

        $this->s_data = $is_user;

        if ($this->mv_updkz == "U")
            $this->s_data["Date_Changed"] = date("YmdHis");

        return true;
    }

    public function set_vk_id($i_vk_id)
    {
        $ls_data = $this->get_data();
        $ls_data["vk_id"] = $i_vk_id;
        $this->set_data($ls_data);
    }

    public function delete()
    {

        if ($this->s_data["Deleted"])
            return true;

        if ($this->mv_updkz == "D")
            return true;

        $this->mv_updkz = "D";

        $this->s_data["Deleted"] = "1";
        $this->s_data["Date_Deleted"] = date("YmdHis");

        return true;
    }

    public function was_deleted()
    {
        if ($this->mv_updkz == "D" || $this->s_data["Deleted"])
            return true;
        else
            return false;
    }

    public function signin()
    {

        $l_token = md5("some_salt" . uniqid() . time());

        $l_query = "INSERT INTO tokens (guid, user_id, signin_at, signin_ip) VALUE('%s', '%s', '%s', '%s')";

        $l_query = sprintf($l_query, $l_token, $this->id, date("YmdHis"), http::get_ip());

        $lr_db = db::get();

        $lr_db->query($l_query);

        return $l_token;

    }

    public function save()
    {

        // Get db instance
        $r_db = db::get();

        // Insert
        if ($this->mv_updkz == "I") {

            $l_query = "INSERT INTO users (
                Name,
                Email,
                Password,
                Date_Created,
                Thumbnail_Size,
                Thumbnail_Dimension,
                Bmp2Jpg,
                vk_id) VALUE('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";

            $l_query = sprintf($l_query,
                db::escape($this->s_data["Name"]),
                db::escape($this->s_data["Email"]),
                db::escape($this->s_data["Password"]),
                db::escape($this->s_data["Date_Created"]),
                db::escape($this->s_data["Thumbnail_Size"]),
                db::escape($this->s_data["Thumbnail_Dimension"]),
                db::escape($this->s_data["Bmp2Jpg"]),
                db::escape($this->s_data["vk_id"]));

            $r_db->query($l_query);

            $this->id = $r_db->get_id();

            $this->s_data["ID"] = $this->id;

            if ($this->id == 0)
                throw new exception("Error on create user. User ID is zero.");
        }

        // Update
        if ($this->mv_updkz == "U" && $this->s_data["ID"] != 0) {

            $l_query = " update users set";
            $l_query .= "  Name     = '" . db::escape($this->s_data["Name"]) . "'";
            $l_query .= ", Email    = '" . db::escape($this->s_data["Email"]) . "'";
            $l_query .= ", Password = '" . db::escape($this->s_data["Password"]) . "'";
            $l_query .= ", Deleted  = '" . db::escape($this->s_data["Deleted"]) . "'";

            if (!empty($this->s_data["Date_Changed"]))
                $l_query .= ", Date_Changed = '" . db::escape($this->s_data["Date_Changed"]) . "'";

            $l_query .= ", Thumbnail_Size      = '" . db::escape($this->s_data["Thumbnail_Size"]) . "'";
            $l_query .= ", Thumbnail_Dimension = '" . db::escape($this->s_data["Thumbnail_Dimension"]) . "'";
            $l_query .= ", Bmp2Jpg             = '" . db::escape($this->s_data["Bmp2Jpg"]) . "'";
            $l_query .= ", vk_id               = '" . db::escape($this->s_data["vk_id"]) . "'";

            $l_query .= " where ID = '" . db::escape($this->s_data["ID"]) . "'";

            $r_db->query($l_query);
        }

        // Delete
        if ($this->mv_updkz == "D" && $this->s_data["ID"] != 0) {

            $l_query = "update users set";
            $l_query .= "  Deleted       = '" . db::escape($this->s_data["Deleted"]) . "'";
            $l_query .= ", Date_Deleted  = '" . db::escape($this->s_data["Date_Deleted"]) . "'";
            $l_query .= " where ID       = '" . db::escape($this->s_data["ID"]) . "'";

            $r_db->query($l_query);
        }

        $this->mv_updkz = "";
    }

    public function signout($i_token)
    {

        $l_query = "UPDATE tokens SET signout_at = '%s', signout_ip = '%s' WHERE guid = '%s'";

        $l_query = sprintf($l_query, date("YmdHis"), http::get_ip(), db::escape($i_token));

        $lr_db = db::get();

        $lr_db->query($l_query);

        return true;

    }

    public function remind()
    {

        $l_to = $this->get_name() . " <" . $this->get_email() . ">";

        $l_subject = "Вспомнить все";

        $l_body = "Вот же мой пароль: " . $this->get_password() . "\r\n\r\n--\r\nPikucha Team";

        mail::spam($l_to, $l_subject, $l_body);

    }

    public function get_password()
    {
        return $this->s_data["Password"];
    }

}

class user_static
{
    static function get_emails()
    {

        $lr_db = db::get();

        return $lr_db->query("SELECT id, name, email FROM users WHERE email <> '';");

    }

}