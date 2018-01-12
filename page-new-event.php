<?php
if (get_current_user_id() == 0) {
    return include('404.php');
}

ob_start();
include(__DIR__ . '/templates/events/edit.php');
$inner_part = ob_get_clean();

get_header();
echo '<h3>'.__( 'New event', THEME ).'</h3>';
echo $inner_part;
get_footer();