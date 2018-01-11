<?php
$event_name = '';
$city = '';
$from = time();
$to = time();
$email = '';
$tel = '';
$link = '';
$lat = 50.05;
$lng = 14.28;
$description = '';
$image_url = '';
$visible = 'on';
$ages_terms = [];
$themes_terms = [];
$error = [];

$new_event = true;

if (isset($_GET['event'])) {
    $new_event = false;
    $the_slug = $_GET['event'];
    $args = array(
        'name'        => $the_slug,
        'post_type'   => '_events',
        'post_status' => 'publish',
        'numberposts' => 1
    );
    $my_posts = get_posts($args);
    if( !$my_posts ) :
        header('Location:'.get_home_url().'/new-event/');
    else:
        if ( $my_posts[0]->post_author != get_current_user_id() ) {
            header('Location:'.get_home_url().'/new-event/');
        }

        if (isset($_GET['remove']) && $_GET['remove'] == 1) {
            wp_delete_post( $my_posts[0]->ID );
            header("Location:" . home_url());
            exit();
        }

        $event_name = get_the_title($my_posts[0]->ID);
        $city = get_post_meta( $my_posts[0]->ID, '_event_city', true );
        $from = get_post_meta( $my_posts[0]->ID, '_event_date_from', true );
        $to = get_post_meta( $my_posts[0]->ID, '_event_date_to', true );
        $email = get_post_meta( $my_posts[0]->ID, '_event_email', true );
        $tel = get_post_meta( $my_posts[0]->ID, '_event_tel', true );
        $link = get_post_meta( $my_posts[0]->ID, '_event_link', true );
        $lat = (float) get_post_meta( $my_posts[0]->ID, '_event_place_lat', true );
        $lng = (float) get_post_meta( $my_posts[0]->ID, '_event_place_lng', true );
        $description = get_post_meta( $my_posts[0]->ID, '_event_description', true );
        $visible = get_post_meta( $my_posts[0]->ID, '_event_only_for_registrated_users', true );

        $ages = get_the_terms( $my_posts[0]->ID, '_ages' );
        if ($ages && count($ages) > 0 ) {
            foreach ($ages as $age) {
                $ages_terms[] = $age->term_id;
            }
        }
        $themes = get_the_terms( $my_posts[0]->ID, '_themes' );
        if ($themes && count($themes) > 0 ) {
            foreach ($themes as $themes) {
                $themes_terms[] = $themes->term_id;
            }
        }
    endif;
}

if (isset($_POST['event_save'])) {
    $event_name = htmlspecialchars($_POST['event_name']);
    $city = htmlspecialchars($_POST['event_city']);
    $from = strtotime($_POST['event_from']);
    $to = strtotime($_POST['event_to']);
    $email = htmlspecialchars($_POST['event_email']);
    $tel = htmlspecialchars($_POST['event_phone']);
    $link = htmlspecialchars($_POST['event_link']);
    $lat = (float) htmlspecialchars($_POST['lat_value']);
    $lng = (float) htmlspecialchars($_POST['lng_value']);
    $description = $_POST['description'];
    if (isset($_POST['visible'])) {
        $visible = $_POST['visible'];
    } else {
        $visible = 'off';
    }

    if (isset($_POST['ages'])) {
        foreach ($_POST['ages'] as $age) {
            $ageTerm = get_term_by('slug', $age, '_ages');
            $ages_terms[] = $ageTerm->term_id;
        }
    }

    if (isset($_POST['themes'])) {
        foreach ($_POST['themes'] as $theme) {
            $themeTerm = get_term_by('slug', $theme, '_themes');
            $themes_terms[] = $themeTerm->term_id;
        }
    }

    if ($to == 0) {
        $to = $from;
    }

    if ($to < $from) {
        $from = $save;
        $from = $to;
        $to = $save;
    }

    // Validation
    if ( strlen($event_name) == 0 ) {
        $error[] = __( 'Event name must be filled.', THEME );
    } else if ( strlen($event_name) >= 255 ) {
        $error[] = __( 'Event name is too long.', THEME );
    }

    if ( $from == 0 ) {
        $error[] = __( 'At least one date must be filled.', THEME );
    }

    if ( count($ages_terms) == 0 ) {
        $error[] = __( 'At least one age epoch must be filled.', THEME );
    }

    if ( count($error) == 0 ) {
        if ($new_event) :
            $new_post = array(
                'post_title' => $event_name,
                'post_status' => 'pending',
                'post_type' => '_events',
                'comment_status' => 'open',
                'post_author' => get_current_user_id()
            );

            $pid = wp_insert_post($new_post);
        else:
            $pid = $my_posts[0]->ID;
        endif;

        wp_set_post_terms($pid, $ages_terms, '_ages', false);
        wp_set_post_terms($pid, $themes_terms, '_themes', false);

        if ( ! add_post_meta( $pid, '_event_date_from', $from, true) ) {
            update_post_meta ( $pid, '_event_date_from', $from );
        }
        if ( ! add_post_meta( $pid, '_event_date_to', $to, true) ) {
            update_post_meta ( $pid, '_event_date_to', $to );
        }
        if ( ! add_post_meta( $pid, '_event_city', $city, true) ) {
            update_post_meta ( $pid, '_event_city', $city );
        }
        if ( ! add_post_meta( $pid, '_event_email', $email, true) ) {
            update_post_meta ( $pid, '_event_email', $email );
        }
        if ( ! add_post_meta( $pid, '_event_tel', $tel, true) ) {
            update_post_meta ( $pid, '_event_tel', $tel );
        }
        if ( ! add_post_meta( $pid, '_event_link', $link, true) ) {
            update_post_meta ( $pid, '_event_link', $link );
        }
        if ( ! add_post_meta( $pid, '_event_place_lat', $lat, true) ) {
            update_post_meta ( $pid, '_event_place_lat', $lat );
        }
        if ( ! add_post_meta( $pid, '_event_place_lng', $lng, true) ) {
            update_post_meta ( $pid, '_event_place_lng', $lng );
        }
        if ( ! add_post_meta( $pid, '_event_description', $description, true) ) {
            update_post_meta ( $pid, '_event_description', $description );
        }
        if ( ! add_post_meta( $pid, '_event_only_for_registrated_users', $visible, true) ) {
            update_post_meta ( $pid, '_event_only_for_registrated_users', $visible );
        }

        $update_post = array(
            'ID' => $pid,
            'post_status' => 'publish',
            'post_type' => '_events',
            'post_title' => $event_name,
            'post_name' => date('Y-m-d', $from) . ' - ' . $event_name
        );

        wp_update_post($update_post);

        header("Location:" . get_the_permalink( $pid ));
        exit();
    }
}
global $frontend;

if ( count($error) > 0 ) {
    echo '<ul class="errors">';
    foreach ($error as $error) {
        echo '<li>'.$error.'</li>';
    }
    echo '</ul>';
}
?>
    <form action="" method="post">
        <div class="login_form half_page">
            <p>
                <label for="name"><?php echo __( 'Event name', THEME ); ?> *</label>
                <input type="text" id="event_name" name="event_name" value="<?php echo $event_name; ?>" required>
            </p>
            <p>
                <label for="visible"><?php echo __( 'Event visible only for registrated users', THEME ); ?></label>
                <input type="checkbox" id="visible" name="visible" <?php if ($visible == 'on') echo 'checked'; ?>>
            </p>
            <p>
                <label for="from"><?php echo __( 'Date of start', THEME ); ?> *</label>
                <input type="text" id="event_from" class="date_picker" name="event_from" value="<?php echo date('j.n.Y', $from); ?>" required>
            </p>
            <p>
                <label for="event_to"><?php echo __( 'Date of end', THEME ); ?></label>
                <input type="text" id="event_to" class="date_picker" name="event_to" value="<?php echo date('j.n.Y', $to); ?>">
            </p>
            <p>
                <label for="event_city"><?php echo __( 'City', THEME ); ?></label>
                <input type="text" id="event_city" name="event_city" value="<?php echo $city; ?>">
            </p>
            <p>
                <label for="event_email"><?php echo __( 'Organisator email', THEME ); ?></label>
                <input type="email" id="event_email" name="event_email" value="<?php echo $email; ?>">
            </p>
            <p>
                <label for="event_phone"><?php echo __( 'Organisator phone', THEME ); ?></label>
                <input type="text" id="event_phone" name="event_phone" value="<?php echo $tel; ?>">
            </p>
            <p>
                <label for="event_link"><?php echo __( 'Website', THEME ); ?></label>
                <input type="text" id="event_link" name="event_link" value="<?php echo $link; ?>">
            </p>
        </div>
        <div class="login_form half_page">
            <p>
            <label><?php echo __( 'Ages', THEME ); ?> *<span>(<?php echo __( 'At least one', THEME ); ?>)</span></label>
            <?php
            $ages = $frontend->get_all_ages_list( false );
            /*if (isset($_GET['ages'])) {
                $current_age = $_GET['ages'];
            }*/
            $list_of_ages = $frontend->show_checkboxes_from_terms( $ages, $ages_terms );
            echo $list_of_ages;
            ?>
            </p>
            <p>
                <label><?php echo __('Themes', THEME);?></label>
                <?php
                $themes = get_terms(array(
                    'taxonomy' => '_themes',
                    'hide_empty' => false,
                ));
                if ($themes) {
                    echo '<ul>';
                    foreach ($themes as $theme) {
                        echo '<li><input type="checkbox" name="themes[]" value="'.$theme->slug.'" ';
                        if (in_array( $theme->term_id, $themes_terms )) {
                            echo ' checked ';
                        }
                        echo '> '.$theme->name.'</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </p>
        </div>
        <div class="full_page">
            <p>
                <label><?php echo __('Description', THEME); ?></label>
                <?php wp_editor($description, 'description', array(
                    'wpautop' => false,
                    'teeny' => true,
                    'media_buttons' => false,
                )); ?>
            </p>
        </div>
        <input type="hidden" id="lat_value" name="lat_value">
        <input type="hidden" id="lng_value" name="lng_value">
        <input type="hidden" id="location_name_value" name="location_name_value">
        <div id="map_picker"></div>

        <input type="submit" name="event_save" value="<?php echo __( 'Save', THEME ) ?>">
    </form>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.date_picker').datepicker({
                dateFormat : 'd.m.yy'
            });
        });
        $ = jQuery;
        $('#map_picker').locationpicker(
            {
                location: {
                    latitude: <?php echo $lat; ?>,
                    longitude: <?php echo $lng; ?>
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