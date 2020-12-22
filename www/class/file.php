<?php

class file
{

    static function get_name($i_path)
    {
        return file::pathinfo($i_path, PATHINFO_FILENAME);
    }

    static function pathinfo($i_path, $i_option = false)
    {

        if (strpos($i_path, "/") !== false)
            $l_basename = end(explode("/", $i_path));
        elseif (strpos($i_path, "\\") !== false)
            $l_basename = end(explode("\\", $i_path)); else
            $l_basename = $i_path;

        if (empty($l_basename))
            return false;

        if ($l_basename != $i_path)
            $l_dirname = substr($i_path, 0, strlen($i_path) - strlen($l_basename) - 1);
        else
            $l_dirname = "";

        if (strpos($l_basename, ".") !== false) {
            $l_extension = end(explode(".", $i_path));
            $l_filename = substr($l_basename, 0, strlen($l_basename) - strlen($l_extension) - 1);
        } else {
            $l_extension = "";
            $l_filename = $l_basename;
        }

        if ($i_option === false)
            return array(
                "dirname" => $l_dirname,
                "basename" => $l_basename,
                "extension" => $l_extension,
                "filename" => $l_filename
            );
        elseif ($i_option == PATHINFO_DIRNAME)
            return $l_dirname; elseif ($i_option == PATHINFO_BASENAME)
            return $l_basename; elseif ($i_option == PATHINFO_FILENAME)
            return $l_filename; elseif ($i_option == PATHINFO_EXTENSION)
            return $l_extension;

        return false;

    }

    static function get_extension($i_path)
    {
        return file::pathinfo($i_path, PATHINFO_EXTENSION);
    }

    static function get_size($i_path)
    {
        if (file_exists($i_path))
            return filesize($i_path);
        return false;
    }

    static function exist($i_path)
    {
        if (file_exists($i_path))
            return true;
        return false;
    }

    static function replace($i_from, $i_to)
    {

        if (empty($i_from) || empty($i_to))
            return false;

        file::create_directory($i_to);

        file::delete($i_to);

        return rename($i_from, $i_to);

    }

    static function create_directory($i_path)
    {

        $s_info = file::pathinfo($i_path);

        if (is_dir($s_info["dirname"]))
            return true;

        return mkdir($s_info["dirname"]);

    }

    static function delete($i_path)
    {

        if (file_exists($i_path))
            return unlink($i_path);

        return true;

    }

    static function delete_directory($i_path)
    {

        $s_info = file::pathinfo($i_path);

        if (is_dir($s_info["dirname"]))

            return rmdir($s_info["dirname"]);

        return true;

    }

}