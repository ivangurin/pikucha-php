<?php

class db
{

    static private
        $r_db;
    private
        $r_link,
        $debug = false;

    static public function get()
    {

        if (!is_object(self::$r_db)) {

            global $s_conf;

            try {

                self::$r_db = new db();

                self::$r_db->connect($s_conf["db_host"], $s_conf["db_socket"], $s_conf["db_login"], $s_conf["db_password"], $s_conf["db_database"], $s_conf["db_codepage"]);

            } catch (exception $r_exception) {

                self::$r_db = "";

                mail::report("Database connection error", $r_exception->getMessage());

                die($r_exception->getMessage());

                throw new exception("Internal error");

            }

        }

        return self::$r_db;

    }

    public function connect($i_host, $i_socket, $i_login, $i_password, $i_database, $i_codepage = "")
    {

        $this->r_link = mysqli_init();

        if (!$this->r_link)
            throw new exception("Error on get MySQLi instance");

        $this->r_link->options(MYSQLI_INIT_COMMAND, 'set auto_increment_increment = 1');

        if ($this->r_link->error)
            throw new exception($this->r_link->error);

        $this->r_link->options(MYSQLI_INIT_COMMAND, 'set auto_increment_offset = 1');

        if ($this->r_link->error)
            throw new exception($this->r_link->error);

        $this->r_link->real_connect($i_host, $i_login, $i_password, $i_database, null, $i_socket);

        if ($this->r_link->error)
            throw new exception($this->r_link->error);

        $this->r_link->set_charset($i_codepage);

        if ($this->r_link->error)
            throw new exception($this->r_link->error);

    }

    static function escape($i_query)
    {

        $lr_db = db::get();

        return $lr_db->r_link->real_escape_string($i_query);
    }

    public function start()
    {
        $this->query("start transaction");
    }

    public function query($i_query)
    {

        if ($this->debug)
            print_r("Request: " . $i_query . "\n");

        $lr_result = $this->r_link->query($i_query, MYSQLI_STORE_RESULT);

        if ($this->r_link->error){

            mail::report("Database query error", "Request: " . $i_query . "\n Error: " . $this->r_link->error);

            if ($this->debug)
                print_r("Response FALSE. Error: " . $this->r_link->error . "\n");

            throw new exception("Database query error. Mail to administrator was sent. Thank you.");

        }

        if ($this->debug)
            print_r("Response TRUE. Info: " .$this->r_link->info . "\n");

        if ($lr_result === true)
            retrun;

        $lt_response = array();

        if ($lr_result->num_rows != 0) {

            while ($ls_row = $lr_result->fetch_assoc()) {

                if (isset($ls_row["id"])) {

                    $lt_response[$ls_row["id"]] = $ls_row;

                } elseif (isset($ls_row["ID"])) {

                    $lt_response[$ls_row["ID"]] = $ls_row;

                } else {

                    $lt_response[] = $ls_row;

                }

            }

        }

        return ($lt_response);

    }

    public function commit()
    {
        $this->query("commit");
    }

    public function rollback()
    {
        $this->query("rollback");
    }

    public function select_single($i_query)
    {

        $l_result = $this->query($i_query);

        if (is_array($l_result))
            return ($l_result[0]);

        return false;
    }

    public function get_id()
    {
        return $this->r_link->insert_id;
    }

    public function debug_on()
    {
        $this->debug = true;
    }

    public function debug_off()
    {
        $this->debug = false;
    }

}