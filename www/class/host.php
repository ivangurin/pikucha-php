<?php

class host {

    private
        $s_data = array();

    static public function get($i_host = "") {

        if (empty($i_host))
            $l_host = http::get_host();
        else
            $l_host = $i_host;

        try {
            return cache::get("host::get", $i_host);
        } catch (exception $e) {

        }

        $lr_host = new host($l_host);

        cache::set("host::get", $i_host, $lr_host);

        return $lr_host;

    }

    public function __construct($i_host = "") {

        // Set host
        if (empty($i_host))
            $l_host = http::get_host();
        else
            $l_host = $i_host;

        $l_host = db::escape($l_host);

        // Get db instance
        $lr_db = db::get();

        // Get host data
        $ls_data = $lr_db->select_single("select * from hosts where host = '$l_host' limit 1");

        if ($ls_data === false)
            throw new exception("Host $i_host not found");

        $this->s_data = $ls_data;

    }

    // Get path content
    public function get_host() {
        return $this->s_data["host"];
    }

    // Get path content
    public function get_path_content() {
        return $this->s_data["path"] . $this->s_data["path_content"];
    }

    // Get path content download
    public function get_path_content_download() {
        return $this->s_data["path_download"] . $this->s_data["path_content"];
    }

    // Get path cache
    public function get_path_cache() {
        return $this->s_data["path"] . $this->s_data["path_cache"];
    }

    // Get path cache download
    public function get_path_cache_download() {
        return $this->s_data["path_download"] . $this->s_data["path_cache"];
    }

}