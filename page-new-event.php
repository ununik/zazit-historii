<?php
get_header();
global $frontend;

    if (isset($_POST['event_save'])) {
        $event_name = htmlspecialchars($_POST['event_name']);
        $city = htmlspecialchars($_POST['event_city']);
        $from = strtotime($_POST['event_from']);
        $to = strtotime($_POST['event_to']);
        $email = strtotime($_POST['event_email']);
        $tel = strtotime($_POST['event_phone']);
        $link = strtotime($_POST['event_link']);
        $lat = htmlspecialchars($_POST['lat_value']);
        $lng = htmlspecialchars($_POST['lng_value']);

        if ($to == 0) {
            $to = $from;
        }

        if ($to > $from) {
            $from = $save;
            $from = $to;
            $to = $save;
        }

        $new_post = array(
            'post_title' => $event_name,
            'post_status' => 'pending',
            'post_type' => '_events',
            'comment_status' => 'open',
            'post_author' => get_current_user_id()
        );

        $pid = wp_insert_post($new_post);

        add_post_meta($pid, '_event_date_from', $from, true);
        add_post_meta($pid, '_event_date_to', $to, true);
        add_post_meta($pid, '_event_city', $city, true);
        add_post_meta($pid, '_event_email', $email, true);
        add_post_meta($pid, '_event_tel', $tel, true);
        add_post_meta($pid, '_event_link', $link, true);
        add_post_meta($pid, '_event_place_lat', $lat, true);
        add_post_meta($pid, '_event_place_lng', $lng, true);

        $update_post = array(
            'ID' => $pid,
            'post_status' => 'publish',
            'post_type' => '_events',
            'post_name'    => date('Y-m-d', $from ) . ' - ' . $event_name
        );

        wp_update_post($update_post);
    }

    echo '<h3>'.__( 'New event', THEME ).'</h3>';
    ?>
    <form action="" method="post">
        <div class="login_form half_page">
            <p>
                <label for="name"><?php echo __( 'Event name', THEME ); ?> *</label>
                <input type="text" id="event_name" name="event_name" required>
            </p>
            <p>
                <label for="from"><?php echo __( 'Date of start', THEME ); ?> *</label>
                <input type="text" id="event_from" name="event_from" required>
            </p>
            <p>
                <label for="event_to"><?php echo __( 'Date of end', THEME ); ?></label>
                <input type="text" id="event_to" name="event_to">
            </p>
            <p>
                <label for="event_city"><?php echo __( 'City', THEME ); ?></label>
                <input type="text" id="event_city" name="event_city">
            </p>
            <p>
                <label for="event_email"><?php echo __( 'Organisator email', THEME ); ?></label>
                <input type="text" id="event_email" name="event_email">
            </p>
            <p>
                <label for="event_phone"><?php echo __( 'Organisator phone', THEME ); ?></label>
                <input type="text" id="event_phone" name="event_phone">
            </p>
            <p>
                <label for="event_link"><?php echo __( 'Website', THEME ); ?></label>
                <input type="text" id="event_link" name="event_link">
            </p>
        </div>
        <input type="hidden" id="lat_value" name="lat_value">
        <input type="hidden" id="lng_value" name="lng_value">
        <input type="hidden" id="location_name_value" name="location_name_value">
        <div id="map_picker"></div>

        <input type="submit" name="event_save" value="<?php echo __( 'Save', THEME ) ?>">
    </form>
    <script>
        $ = jQuery;
        $('#map_picker').locationpicker(
            {
                location: {
                    latitude: 50.05,
                    longitude: 14.28
                },
                zoom: 7,
                inputBinding: {
                    latitudeInput: $('#lat_value'),
                    longitudeInput: $('#lng_value'),
                    locationNameInput: $('#location_name_value'),
                },
                markerIcon: '<?php echo get_template_directory_uri() . '/assets/images/icons/map_sword.svg' ?>',
            }
        );
    </script>
    <?php
get_footer();