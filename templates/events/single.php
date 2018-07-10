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
<div class="content_wrapper single_event_content">
    <h3 class="headline"><?php the_title() ?></h3>

    <div class="single_event_box single_event_meta">

        <?php
        if ((int) $post->post_author == (int) get_current_user_id() && get_current_user_id() != 0) :
            ?>
            <div class="edit_buttons">
                <a class="edit icon" href="<?php echo add_query_arg('event', get_post_field( 'post_name', get_post() ), get_home_url().'/edit-event/')     ; ?>" title="<?php echo __('Edit', THEME); ?>"><?php echo __('Edit', THEME); ?></a>
                <a class="remove icon" data-remove="<?php echo __('Do you really want to remove this item?', THEME);?>" href="<?php echo add_query_arg(array ('event'=>get_post_field( 'post_name', get_post() ), 'remove' => 1 ), get_home_url().'/edit-event/')     ; ?>" title="<?php echo __('Remove', THEME); ?>"><?php echo __('Remove', THEME); ?></a>
            </div>
            <?php
        endif;
        ?>

        <div class="single_event_date">
            <div class="icon"></div>
            <?php echo $frontend->get_date_from_timestamps((int) get_post_meta( get_the_ID(), '_event_date_from', true ), (int) get_post_meta( get_the_ID(), '_event_date_to', true )); ?>
        </div>

        <?php
        $organisator = get_post_meta( get_the_ID(), 'event_organisator', true );
        if ( $organisator && $organisator != '') :
            ?>
            <div class="single_event_organisator">
                <div class="icon"></div>
                <?php echo $organisator; ?>
            </div>
            <?php
        endif;
        ?>

        <?php
        $link = get_post_meta( get_the_ID(), '_event_link', true );
        if ( $link && $link != '') :
        ?>
        <div class="single_event_link">
            <div class="icon"></div>
            <a href="<?php echo $link; ?>" target="_blank"><?php
                $url_parts = parse_url($link);
                if (isset($url_parts['host'])) {
                    echo $url_parts['host'];
                } else {
                    echo $link;
                }
                ?></a>
        </div>
        <?php
        endif;
        ?>

        <?php
        if ( get_current_user_id() != 0) :
            $email = get_post_meta( get_the_ID(), '_event_email', true );
            if ( $email && $email != '') :
                ?>
                <div class="single_event_email">
                    <div class="icon"></div>
                    <a href="mailto:<?php echo $email; ?>" target="_blank"><?php echo $email ?></a>
                </div>
            <?php endif;

            $tel = get_post_meta( get_the_ID(), '_event_tel', true );
            if ( $tel && $tel != '') :
                ?>
                <div class="single_event_tel">
                    <div class="icon"></div>
                    <a href="tel:<?php echo $tel; ?>" target="_blank"><?php echo $tel;?></a>
                </div>
                <?php
            endif;
        endif;
        ?>

        <div class="single_event_thumbnail">
            <a href="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" data-fancybox="images"><?php
                the_post_thumbnail('large');
                ?>
            </a>
        </div>

    </div>
    <div class="single_event_box single_event_description">
        <?php echo wpautop(get_post_meta( get_the_ID(), '_event_description', true )); ?>
    </div>

    <?php
    echo '<div style="margin: 20px 0px; clear: both; padding-top: 10px;">' . $frontend->show_ad() . '</div>';
    ?>

    <div class="share_entry">
        <?php if (get_post_meta( get_the_ID(), '_event_only_for_registrated_users', true ) == 'on') {
            echo '<div>'.__('Sharing is not possible for not public events', THEME).'</div>';
        } else { ?>
            <h4><?php echo __('Share:', THEME); ?></h4>
            <?php
            $date = $frontend->get_date_from_timestamps((int) get_post_meta( get_the_ID(), '_event_date_from', true ), (int) get_post_meta( get_the_ID(), '_event_date_to', true ));

            echo do_shortcode('[addtoany url="'.get_the_permalink().'" title="'.get_the_title().' ('.$date.')"]'); ?>
        <?php } ?>
    </div>

    <?php
    $map = get_post_meta( get_the_ID(), 'event_map_location', true );
    if ($map == 'on') {
        $lat = (float)get_post_meta(get_the_ID(), '_event_place_lat', true);
        $lng = (float)get_post_meta(get_the_ID(), '_event_place_lng', true);
        $mista = $frontend->get_nearest_places($lat, $lng);
        if ($mista) :
            ?>
            <div>
                <h4>Nejbližší významná místa</h4>
                <?php
                foreach ($mista as $misto) {
                    echo '<div><a href="' . get_the_permalink($misto->ID) . '">' . get_the_title($misto->ID) . ' (' . round($misto->distance) .' km)</a></div>';
                }
                ?>
            </div>
        <?php endif;

    }?>

    <?php
    if ($map == 'on') {
        echo '<div id="g-map"></div>';
    }
    ?>
</div>
<?php

get_footer();

?>
<script>
    $ = jQuery;
    <?php
    if ($map == 'on') {
    ?>
    $.post('<?php echo get_home_url(); ?>/wp-admin/admin-ajax.php', {
        action: 'map_data',
        <?php
        echo "id: ".get_the_ID().',';
        ?>
    }).then(initAutocomplete)
    <?php } ?>
</script>