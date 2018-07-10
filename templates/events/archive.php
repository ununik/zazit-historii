<?php
/**
 * The events archive template
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */
get_header();
include 'archive_events.php';
get_footer();


?>
<script>
    $ = jQuery;
    $.post('<?php echo get_home_url(); ?>/wp-admin/admin-ajax.php', {
        action: 'map_data',
        <?php
        if ( isset( $_GET[ __( 'ages', THEME ) ] ) ) {
            echo "ages: '{$_GET[ __( 'ages', THEME ) ]}'";
        }
        if ( isset( $_GET[ __( 'search', THEME ) ] ) ) {
            echo "search: '{$_GET[ __( 'search', THEME ) ]}'";
        }
        ?>
    }).then(initAutocomplete)
</script>