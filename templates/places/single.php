<?php
/**
 * The events single template
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */

global $post, $frontend;
get_header();
?>
<div class="content_wrapper">
    <h3><?php the_title() ?></h3>
    <div class="single_meta">
        <?php
        $link = get_post_meta( get_the_ID(), '_place_web', true );
        if ( $link && $link != '') :
            ?>
            <div class="single_event_link single_meta_data">
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
        <?php endif;
        $email = get_post_meta( get_the_ID(), '_place_mail', true );
        if ( $email && $email != '') :
            ?>
            <div class="single_event_email single_meta_data">
                <div class="icon"></div>
                <a href="mailto:<?php echo $email; ?>" target="_blank"><?php echo $email ?></a>
            </div>
        <?php endif;
        $tel = get_post_meta( get_the_ID(), '_place_tel', true );
        if ( $tel && $tel != '') :
            ?>
            <div class="single_event_tel single_meta_data">
                <div class="icon"></div>
                <a href="tel:<?php echo $tel; ?>" target="_blank"><?php echo $tel;?></a>
            </div>
            <?php
        endif;
        ?>
    </div>
    <?php echo wpautop(get_post_meta( get_the_ID(), '_place_description', true )); ?>
    <div class="share_entry">
    <h4><?php echo __('Share:', THEME); ?></h4>
    <?php
    echo do_shortcode('[addtoany url="'.get_the_permalink().'" title="'.get_the_title().'"]'); ?>
    </div>

    <?php
    $map = get_post_meta( get_the_ID(), '_place_map_location', true );
    if ($map == 'on') {
        $lat = (float)get_post_meta(get_the_ID(), '_place_lat', true);
        $lng = (float)get_post_meta(get_the_ID(), '_place_lng', true);
        echo '<div>';
        $events = $frontend->get_nearest_events($lat, $lng, 10);

        if ($events) {
            echo '<h3>Nejbližší události</h3>';
        }

        foreach ($events as $event) {
            echo '<div>';
            echo '<a href="'.get_the_permalink($event->ID).'">';
            echo get_the_title($event->ID);
            echo ' (';
            echo $frontend->get_date_from_timestamps((int) get_post_meta( $event->ID, '_event_date_from', true ), (int) get_post_meta( $event->ID, '_event_date_to', true ));
            echo ')</a> - ';
            echo round($event->distance).' km';

    echo '</div>';
        }
        echo '</div>';
        echo '<div id="g-map"></div>';
    }

    $sources = get_post_meta(get_the_ID(), '_place_sources', true);
    if($sources){
        echo '<h4>'.__('Sources', THEME).':</h4>';
        echo '<ul class="sources">';
        foreach ($sources as $source) {
            if (!filter_var($source, FILTER_VALIDATE_URL) === false) {
                $source = '<a href="'.$source.'" target="_blank">'.$source.'</a>';
            }
            echo '<li>'.$source.'</li>';
        }
        echo '</ul>';
    }

    echo '<div style="margin: 20px 0px; clear: both; padding-top: 10px;">' . $frontend->show_ad() . '</div>';
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
        action: 'places_map_data',
        <?php
        echo "id: ".get_the_ID().',';
        ?>
    }).then(initAutocomplete)
    <?php } ?>
</script>