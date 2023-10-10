<?php
header('Content-type: text/css');
require_once __DIR__."/../../../wp-load.php";

$settings = get_option('w3exabe_settings');
$line_height = 'line-height: 20px';

if (isset($settings['rowheight']) && $settings['rowheight'] == '2') {
    $line_height = 'line-height: 36px';
} elseif (isset($settings['rowheight']) && $settings['rowheight'] == '3') {
    $line_height = 'line-height: 54px';
}
?>

.slick-cell {
    <?php echo $line_height; ?>
}
