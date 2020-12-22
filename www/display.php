<?php

$l_album = request::get("album");
$l_image = request::get("image");

$lr_album = "";
$lt_images = "";

$lr_image = "";
$lr_prev = "";
$lr_next = "";
$l_title = "";

try {
    if (!empty($l_album)) {

        $lr_album = album_manager::get($l_album);

        $l_title = $lr_album->get_name(true);

        $lt_images = $lr_album->get_images();

    } elseif (!empty($l_image)) {

        $lr_image = image_manager::get($l_image);

        $lr_prev = image_manager::get_prev($lr_image->get_id());

        $lr_next = image_manager::get_next($lr_image->get_id());

        $l_title = $lr_image->get_title_ext();
    }

} catch (exception $r_exception) {
    http::location();
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="keywords" content="<?php echo($l_title); ?>"/>
    <meta name="description" content="<?php echo($l_title); ?>"/>
    <title><?php echo($l_title); ?></title>
    <link rel="stylesheet" href="css/styles.css"/>
    <link rel="icon" href="favicon.ico"/>
</head>
<body>
<?php if ($l_album) { ?>
    <table border="0" style="width: 100%">
        <tr>
            <td>
                <table border="0" style="width: 100%">
                    <tr>
                        <td style="padding-left: 25px; padding-right: 25px;"><a href="/">Закачать</a></td>
                        <td style="width: 100%"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 25px; padding-bottom: 25px;  text-align: center; ">
                <?php
                foreach ($lt_images as $ls_image) {
                    $lr_image = image_manager::get($ls_image["ID"]);
                    ?>
                    <a href="<?php echo($lr_image->get_url()); ?>">
                        <img id="image" class="image" src="<?php echo($lr_image->get_url_thumbnail()); ?>"
                             alt="<?php echo($lr_image->get_title_ext()) ?>"
                             title="<?php echo($lr_image->get_title_ext()) ?>"/>
                    </a>
                <?php } ?>
            </td>
        </tr>
    </table>
<?php } else { ?>
    <table border="0" style="width: 100%">
        <tr>
            <td>
                <table border="0" style="width: 100%">
                    <tr>
                        <td style="padding-left: 25px; padding-right: 25px;"><a href="/">Закачать</a></td>
                        <td style="width: 100%"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" style="width: 100%">
                    <tr>
                        <td style="padding-top: 25px; padding-bottom: 25px;  text-align: center; ">
                            <img id="image" class="image" src="<?php echo($lr_image->get_url_original()); ?>"
                                 width="<?php echo($lr_image->get_width()) ?>"
                                 height="<?php echo($lr_image->get_height()) ?>"
                                 alt="<?php echo($lr_image->get_title_ext()) ?>"
                                 title="<?php echo($lr_image->get_title_ext()) ?>"/>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" style="width: 100%">
                    <tr>
                        <td style="text-align: center">

                            <?php if ($lr_prev) { ?>
                                &larr; <a href="<?php echo($lr_prev->get_url()) ?>"
                                          title="<?php echo($lr_prev->get_title_ext()) ?>">Предыдущая</a>
                            <?php } else { ?>
                                &larr; Предыдущая
                            <?php } ?>

                            <?php

                            $lr_album = false;

                            if ($lr_image->get_album() != 0) {

                                try {
                                    $lr_album = album_manager::get($lr_image->get_album());
                                } catch (exception $r_exception) {

                                }

                            }

                            if ($lr_album) {

                                ?>
                                | <a href="<?php echo($lr_album->get_url()); ?>"
                                     title="<?php echo($lr_album->get_name()); ?>">В альбом</a>
                            <?php } ?>

                            <?php if ($lr_next) { ?>
                                | <a href="<?php echo($lr_next->get_url()) ?>"
                                     title="<?php echo($lr_next->get_title_ext()); ?>">Следующая</a> &rarr;
                            <?php } else { ?>
                                | Следующая &rarr;
                            <?php } ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?php } ?>
</body>
</html>
