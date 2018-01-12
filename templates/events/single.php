<?php
/**
 * The events single template
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */

global $post, $frontend;
if (get_current_user_id() == 0 && get_post_meta( get_the_ID(), '_event_only_for_registrated_users', true ) == 'on') {
    return include(__DIR__ . '/../../404.php');
}
get_header();
?>
    <h3><?php the_title() ?></h3>

<?php
if ((int) $post->post_author == (int) get_current_user_id() && get_current_user_id() != 0) :
    ?>
        <a class="edit" href="<?php echo add_query_arg('event', get_post_field( 'post_name', get_post() ), get_home_url().'/edit-event/')     ; ?>" title="<?php echo __('Edit', THEME); ?>"><?php echo __('Edit', THEME); ?></a>
        <a class="remove" data-remove="<?php echo __('Do you really want to remove this item?');?>" href="<?php echo add_query_arg(array ('event'=>get_post_field( 'post_name', get_post() ), 'remove' => 1 ), get_home_url().'/edit-event/')     ; ?>" title="<?php echo __('Remove', THEME); ?>"><?php echo __('Remove', THEME); ?></a>
    <?php
endif;
?>
<div>
<?php echo $frontend->get_date_from_timestamps((int) get_post_meta( get_the_ID(), '_event_date_from', true ), (int) get_post_meta( get_the_ID(), '_event_date_to', true )); ?>
</div>
<div>
    <?php
    $themes = get_the_terms( get_the_ID(), '_themes' );
    if ($themes) {
        foreach ($themes as $theme) {
            $img = get_term_meta($theme->term_id, '_themes_image', true);
            $img_url = '';
            if ($img && $img != 0 && $img != '') {
                $img_url = wp_get_attachment_image_src($img);
            }
            if ($img_url == '') {
                echo '<div title="' . $theme->name . '">' . $theme->name . '</div>';
            } else {
                echo '<div class="theme-icon" style="background-image: url(' . $img_url[0] . ');" title="' . $theme->name . '"></div>';
            }
        }
    }
    ?>
</div>
<div>
<?php echo get_post_meta( get_the_ID(), '_event_description', true ); ?>
</div>
<?php
echo '<div id="g-map"></div>';
?>
<div>
    <?php
    $published = get_the_date('j.n.Y H:i');
    $last_change = get_the_modified_date('j.n.Y H:i');
    $author_name = $frontend->get_user_name($post->post_author);

    if ($published) {
        echo '<div>' . __('Published', THEME) . ' ' . $published . '</div>';
        if ($last_change && $last_change != $published) {
            echo '<div>' . __('Modified', THEME) . ' ' . $last_change . '</div>';
        }
    }
    if ( get_current_user_id() != 0 ) {
        echo '<div>' . __('Created by', THEME) . ' <a href="' . get_home_url() . '/profil/' . get_user_meta($post->post_author, 'nickname', true) . '/" title="' . __('Profil', THEME) . ' ' . $author_name . '">' . $author_name . '</a></div>';
    }
    ?>
</div>
<?php

echo $frontend->show_ad();

get_footer();

?>
<script>
    $ = jQuery;
    $.post('<?php echo get_home_url(); ?>/wp-admin/admin-ajax.php', {
        action: 'map_data',
        <?php
        echo "id: ".get_the_ID().',';
        ?>
    }).then(initAutocomplete)
</script>