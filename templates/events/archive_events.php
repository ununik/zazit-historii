<?php
$ages_get = '';
$search_get = '';

if (isset($_REQUEST[ __( 'ages', THEME ) ])) {
    $ages_get = $_REQUEST[ __( 'ages', THEME ) ];
    $query = $frontend->get_all_events_from_date_to_date(
        strtotime('today', current_time('timestamp')),
        0,
        $_REQUEST[ __( 'ages', THEME ) ]
    );
} else if (isset($_REQUEST[__( 'search', THEME )])) {
    $search_get = $_REQUEST[__( 'search', THEME )];
    $query = $frontend->get_all_events_from_date_to_date(
        strtotime('today', current_time('timestamp')),
        0,
        [],
        $_REQUEST[__( 'search', THEME )]
    );
} if (isset($_GET[ __( 'ages', THEME ) ])) {
    $ages_get = $_GET[ __( 'ages', THEME ) ];
    $query = $frontend->get_all_events_from_date_to_date(
        strtotime('today', current_time('timestamp')),
        0,
        $_GET[ __( 'ages', THEME ) ]
    );
} else if (isset($_GET[__( 'search', THEME )])) {
    $search_get = $_GET[__( 'search', THEME )];
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

echo '<div class="events_wr">';
if ( $query && count( $query->posts ) != 0 ) :
    echo '<div class="events-cards-wrapper">';
    echo '<div id="show_hide_map">SHOW MAP</div>';
    echo '<div class="map map-hide">';
    echo '<div id="g-map"></div>';
    echo '</div>';
    echo '<div class="events-rows">';
    $i = 0;
    $more_events =  true;
    foreach ($query->posts as $event) :
        echo $frontend->archiveEventsTemplate($event);
        $i++;

        if ( $i%$frontend->countEventsForAd() == 0 ) {
            echo $frontend->show_ad();
        }
    endforeach;
    echo '</div>';
    if ($query->found_posts <= $frontend->events_on_page) {
        $more_events = false;
    }
    if ($more_events) :
        ?>
        <div class="loader loader-hide"></div>
        <div class="button-primary next_event_page" data-page="2" data-ages="<?php echo $ages_get?>" data-search="<?php echo $search_get; ?>"><?php echo __('Show more'); ?></div>
        <?php
    endif;
        if ($i%$frontend->countEventsForAd() != 0) {
            echo $frontend->show_ad();
        }
    echo '</div>'; // END .events-rows
    echo '</div>';// END .events-cards-wrapper
else:
    include (__DIR__.'/archive_noEvents.php');
endif;