<?php

require_once("class.php");

try {
    image_manager::empty_trash();
} catch (exception $lr_exception) {
    mail::report("Error on empty trash", $lr_exception->getMessage());
}

try {
    image_manager::delete_old_images();
} catch (exception $lr_exception) {
    mail::report("Error on delete old images", $lr_exception->getMessage());
}

try {
    image_manager::delete_images("u.pikucha.ru");
} catch (exception $lr_exception) {
    mail::report("Error on delete images", $lr_exception->getMessage());
}

try {
    image_manager::delete_images("b.pikucha.ru");
} catch (exception $lr_exception) {
    mail::report("Error on delete images", $lr_exception->getMessage());
}

try {
    image_manager::delete_images("beta.pikucha.ru");
} catch (exception $lr_exception) {
    mail::report("Error on delete images", $lr_exception->getMessage());
}

try {
    pikucha::send_stat();
} catch (exception $lr_exception) {
    mail::report("Error on send statistic", $lr_exception->getMessage());
}

try {
    file_put_contents("sitemap.xml", pikucha::get_sitemap());
} catch (exception $lr_exception) {
    mail::report("Error on create sitemap file", $lr_exception->getMessage());
}