<?php

require_once("class.php");

try {

    $lr_db = db::get();

    $lt_images = $lr_db->query("select id from images where deleted = 0 and id > 100000 order by id");

} catch (exception $lr_exception) {
    die($lr_exception->getMessage());
}

foreach ($lt_images as $l_id => $ls_image) {

    try {

        $lr_image = image_manager::get($l_id);

        $ls_info = $lr_image->get_info();

        if ($ls_info["latitude"] != 0 and
            $ls_info["longitude"] != 0
        ) {

            $l_latitude = $ls_info["latitude"];
            $l_longitude = $ls_info["longitude"];

            print_r($l_id . ": +\r\n");

            $lr_db->query("update images set latitude = $l_latitude, longitude = $l_longitude where id = $l_id");

        } else {

//                print_r($l_id . ": -\r\n");

        }

        unset($lr_image);

    } catch (exception $lr_exception) {
        print_r($l_id . ": " . $lr_exception->getMessage() . "\r\n");
    }

}



