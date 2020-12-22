<?php

class pikucha
{

    static public function can_show($i_user_id)
    {

        $l_referrer = http::get_referrer();

        if (empty($l_referrer))
            return true;

        if (service::is_bot() === true)
            return true;

        if (service::is_pikucha() === true)
            return true;

        if (service::is_search() === true)
            return true;

        if (pikucha::is_allowed() === true)
            return true;

        try {

            $lr_user = user_manager::get($i_user_id);

            if ($lr_user->get_balance() > 0)
                return true;

        } catch (exception $lr_exception) {

        }

        return false;
    }

    static public function is_allowed()
    {

        return true;

//        $l_referrer = http::get_referrer();
//
//        if (preg_match("/.*rutracker.*/i", $l_referrer))
//            return true;
//
//        if (preg_match("/.*rutor.*/i", $l_referrer))
//            return true;
//
//        return false;

    }

    static public function send_stat()
    {

        $lr_db = db::get();

        $lt_uploaded = $lr_db->query("select count(*) as counter, sum(size) as size from images where date_created >= now() - interval 1 day;");
        $ls_uploaded = current($lt_uploaded);

        $lt_deleted = $lr_db->query("select count(*) as counter, sum(size) as size from images where deleted = 3 and date_deleted >= now() - interval 1 day;");
        $ls_deleted = current($lt_deleted);

        $lt_showed = $lr_db->query("select count(*) as counter, sum(size) as size from images where date_showed >= now() - interval 1 day;");
        $ls_showed = current($lt_showed);

        $l_message = "Uploaded: " . $ls_uploaded["counter"] . "(" . round($ls_uploaded["size"] / 1024 / 1024 / 1024, 2) . " Gb)" . "\n";
        $l_message .= "Deleted: " . $ls_deleted["counter"] . "(" . round($ls_deleted["size"] / 1024 / 1024 / 1024, 2) . " Gb)" . "\n";
        $l_message .= "Showed: " . $ls_showed["counter"] . "(" . round($ls_showed["size"] / 1024 / 1024 / 1024, 2) . " Gb)" . "\n";

        mail::report("Statistics", $l_message);

    }

    static public function  get_sitemap()
    {

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . "\r\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">" . "\r\n";

        $t_images = image_manager::get_all(100);

        foreach ($t_images as $image) {

            $r_image = image_manager::get($image);

            $xml .= "  <url>" . "\r\n";
            $xml .= "    <loc>" . $r_image->get_url() . "</loc>" . "\r\n";
            $xml .= "  </url>" . "\r\n";
        }

        $xml .= "</urlset>";

        return $xml;

    }

}