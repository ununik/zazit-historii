<?php
/**
 * The events archive template
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */
get_header();
if (isset($_GET[ __( 'ages', THEME ) ])) {
    $query = $frontend->get_all_events_from_date_to_date(
        strtotime('today', current_time('timestamp')),
        0,
        $_GET[ __( 'ages', THEME ) ]
    );
} else if (isset($_GET[__( 'search', THEME )])) {
    $query = $frontend->get_all_events_from_date_to_date(
        strtotime('today', current_time('timestamp')),
        0,
        [],
        $_GET[__( 'search', THEME )]
    );
} else {
    $query = $frontend->get_all_events_from_date_to_date(
        strtotime('today', current_time('timestamp'))
    );
}
if ( isset( $_GET[ __( 'search', THEME ) ] ) ) {
    ?>
    <div class="archive_title">
    <?php echo __( 'Search', THEME ) ?>:<span><?php echo $_GET[__( 'search', THEME )]; ?></span>
    </div>
    <?php
}
if ( isset( $_GET[ __( 'ages', THEME ) ] ) ) {
    $age = get_term_by( 'slug', $_GET[ __( 'ages', THEME )], '_ages' );

    if ($age) {
        ?>
        <div class="archive_title">
        <?php echo $age->name; ?>
        </div>
        <?php
    }
}

if ( count( $query->posts ) != 0 ) :
    echo '<div class="events-cards-wrapper">';
    //echo '<div id="filters">filters</div>';
    echo '<div id="g-map"></div>';
    $i = 0;
    foreach ($query->posts as $event) :
        $i++;
        $ageTerms = get_the_terms( $event->ID, '_ages' );
        $age = $frontend->get_last_term_child( $ageTerms );
        $city = get_post_meta( $event->ID, '_event_city', true );
        $thumbnail = '';
        $thumbnail = get_the_post_thumbnail_url($event->ID, 'event-thumbnail');
        if ( !$thumbnail || $thumbnail == '' ) {
            $img_id = get_term_meta( $age->term_id, '_ages_default_image', true );
            if ( $img_id ) {
                $thumbnail = wp_get_attachment_image_src($img_id, 'event-thumbnail');
                $thumbnail = $thumbnail[0];
            } else {
                $img_id = get_term_meta( $age->parent, '_ages_default_image', true );
                if ( $img_id ) {
                    $thumbnail = wp_get_attachment_image_src($img_id, 'event-thumbnail');
                    $thumbnail = $thumbnail[0];
                }
            }
        }

        ?><a href="<?php echo get_permalink( $event->ID ); ?>" class="events_wrapper">
            <div class="event_thumbnail" style="<?php if ($thumbnail != '') { ?>background-image: url(<?php echo $thumbnail; ?>); <?php } ?>"></div>
            <div
                <?php
                $color = get_term_meta( $age->term_id, '_ages_color', true );
                $parent_color = get_term_meta( $age->parent, '_ages_color', true );
                if( $color && $color != '') {
                    $color = $color;
                } else if($parent_color && $parent_color != '') {
                    $color = $parent_color;
                } else {
                    $color = $frontend->get_default_event_color();

                }

                $bgcolor = get_term_meta( $age->term_id, '_ages_bgcolor', true );
                $parent_bgcolor = get_term_meta( $age->parent, '_ages_bgcolor', true );
                if( $bgcolor && $bgcolor != '') {
                    $bgcolor = $bgcolor;
                } else if($parent_bgcolor && $parent_bgcolor != '') {
                    $bgcolor = $parent_bgcolor;
                } else {
                    $bgcolor = $frontend->get_default_event_bgcolor();

                }

                echo 'style="color: '.$color.'; background-color: '.$bgcolor.'"';
                ?>
               class="events_data <?php if ( $city && $city != '' ) { echo 'events_data_city'; }?>"">

                <h3 class="events_title"><?php echo get_the_title( $event->ID ) ?></h3>

                <div class="events_meta <?php if ( $city && $city != '' ) { echo 'events_meta_city'; }?>"><?php
                    if ( $city && $city != '' ) {
                        echo '<div class="events_city">'.$city.'</div>';
                    }
                    echo $frontend->get_date_from_timestamps((int) get_post_meta( $event->ID, '_event_date_from', true ), (int) get_post_meta( $event->ID, '_event_date_to', true ));
                    ?></div>
            </div>
        </a><?php
        if ( $i%$frontend->countEventsForAd() == 0 ) {
            echo $frontend->show_ad();
        }
    endforeach;
        if ($i%$frontend->countEventsForAd() != 0) {
            echo $frontend->show_ad();
        }
    echo '</div>';// END .events-cards-wrapper
else:
    echo __( 'No event found', THEME ); //TODO: add nothing found template
endif;

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