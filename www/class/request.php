<?php

class request {

    static public function get($i_name, $i_value = "") {

        if(empty($i_value)){

            $l_value = "";
            if (isset($_GET[$i_name]))
                $l_value = $_GET[$i_name];

            return $l_value;

        }else{
            $_REQUEST[$i_name] = $_GET[$i_name] = $i_value;
            return true;
        }

    }

    static public function post($i_name, $i_strict = false) {
        if (isset($_POST[$i_name]))
            return $_POST[$i_name];
        if ($i_strict)
            throw new exception("$i_name not found");
        else
            return "";
    }

    static public function any($i_name, $i_strict = false) {
        if (isset($_REQUEST[$i_name]))
            return $_REQUEST[$i_name];
        if ($i_strict)
            throw new exception("$i_name not found");
        else
            return "";
    }

    static public function checkbox($i_name, $i_strict = false) {
        if (isset($_REQUEST[$i_name]))
            return 1;
        if ($i_strict)
            throw new exception("$i_name not found");
        else
            return 0;
    }

    static public function files() {

        if (count($_FILES) == 0)
            throw new exception("Files not upload");

        $et_files = array();
        foreach ($_FILES as $t_files) {
            if (isset($t_files["name"]) && is_array($t_files["name"])) {
                foreach ($t_files["name"] as $l_index => $l_name) {
                    $s_file = array();
                    $s_file["name"] = $t_files["name"][$l_index];
                    $s_file["type"] = $t_files["type"][$l_index];
                    $s_file["tmp_name"] = $t_files["tmp_name"][$l_index];
                    $s_file["error"] = $t_files["error"][$l_index];
                    $s_file["size"] = $t_files["size"][$l_index];
                    $et_files[] = $s_file;
                }
            } else {
                $et_files[] = $t_files;
            }
        }

        if (count($et_files) == 1 && $et_files[0]["error"] == 4)
            throw new exception("Files not upload");

        return $et_files;
    }

}
