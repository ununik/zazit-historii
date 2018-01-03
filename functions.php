<?php
/**
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */

const THEME = 'zazit_historii';

require_once( __DIR__ . '/library/cuztom/cuztom.php' );

require_once __DIR__ . '/inc/ZazitHistorii.php';
require_once __DIR__ . '/inc/ZazitHistoriiBackend.php';
require_once __DIR__ . '/inc/ZazitHistoriiFrontend.php';

//Allow SVG images
function cc_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'cc_mime_types' );
add_filter( 'widget_text', 'do_shortcode' );
add_theme_support( 'post-thumbnails' );
set_post_thumbnail_size( 150, 150, true );
add_image_size( 'event-thumbnail', 300, 200, true );
add_filter('show_admin_bar', '__return_false');