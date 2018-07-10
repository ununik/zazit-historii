<?php
/**
 * The main template file
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */

get_header();
global $frontend;
echo '<div class="content_wrapper">';
echo '<h3>'.__( 'Places', THEME).'</h3>';

//TODO: fiter
?>
    <div id="g-map"></div>
<?php

echo $frontend->show_ad();
echo '</div>';
get_footer();
?>

<script>
    $ = jQuery;
    $.post('<?php echo get_home_url(); ?>/wp-admin/admin-ajax.php', {
        action: 'places_map_data'
    }).then(initAutocomplete)
</script>
