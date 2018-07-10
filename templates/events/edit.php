<?php
$event_name = '';
$city = '';
$from = time();
$to = 0;
$organisator = '';
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
$file = 0;
$map_location = 'off';

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
        $file = (int) get_post_thumbnail_id($my_posts[0]->ID);
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
        $organisator = get_post_meta( $my_posts[0]->ID, 'event_organisator', true );
        $map_location = get_post_meta( $my_posts[0]->ID, 'event_map_location', true );

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
    $file = (int) $_POST['file_upload_data'];
    $city = htmlspecialchars($_POST['event_city']);
    $organisator = htmlspecialchars($_POST['event_organisator']);
    $from = strtotime($_POST['event_from']);
    $to = strtotime($_POST['event_to']);
    $email = htmlspecialchars($_POST['event_email']);
    $tel = htmlspecialchars($_POST['event_phone']);
    $link = htmlspecialchars($_POST['event_link']);
    $lat = (float) htmlspecialchars($_POST['lat_value']);
    $lng = (float) htmlspecialchars($_POST['lng_value']);
    $map_location = htmlspecialchars($_POST['map_location']);
    $description = $_POST['description'];
    if (isset($_POST['visible'])) {
        $visible = $_POST['visible'];
    } else {
        $visible = 'off';
    }

    if (isset($_POST['ages'])) {
        $ages_terms = [];
        foreach ($_POST['ages'] as $age) {
            $ageTerm = get_term_by('slug', $age, '_ages');
            $ages_terms[] = $ageTerm->term_id;
        }
    }

    if (isset($_POST['themes'])) {
        $themes_terms = [];
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

        set_post_thumbnail( $pid, $file );
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
        if ( ! add_post_meta( $pid, 'event_organisator', $organisator, true) ) {
            update_post_meta ( $pid, 'event_organisator', $organisator );
        }
        if ( ! add_post_meta( $pid, 'event_map_location', $map_location, true) ) {
            update_post_meta ( $pid, 'event_map_location', $map_location );
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

echo '<div><a href="'.get_the_permalink( 64 ).'" target="_blank">'.__('How to create a new event.', THEME).'</a></div>';

if ( count($error) > 0 ) {
    echo '<ul class="errors">';
    foreach ($error as $error) {
        echo '<li>'.$error.'</li>';
    }
    echo '</ul>';
}
?>
    <form action="" method="post" enctype="multipart/form-data">
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
                <input type="text" id="event_to" class="date_picker" name="event_to" value="<?php if($to != 0) {echo date('j.n.Y', $to);} ?>">
            </p>
            <p>
                <label for="event_city"><?php echo __( 'City', THEME ); ?></label>
                <input type="text" id="event_city" name="event_city" value="<?php echo $city; ?>">
            </p>
            <p>
                <label for="event_organisator"><?php echo __( 'Organisator', THEME ); ?></label>
                <input type="text" id="event_organisator" name="event_organisator" value="<?php echo $organisator; ?>">
            </p>
            <p>
                <label for="event_email"><?php echo __( 'Organisator email', THEME ); ?></label>
                <div class="sublabel"><?php echo __( 'Visible only for registered users', THEME ); ?></div>
                <input type="email" id="event_email" name="event_email" value="<?php echo $email; ?>">
            </p>
            <p>
                <label for="event_phone"><?php echo __( 'Organisator phone', THEME ); ?></label>
                <div class="sublabel"><?php echo __( 'Visible only for registered users', THEME ); ?></div>
                <input type="text" id="event_phone" name="event_phone" value="<?php echo $tel; ?>">
            </p>
            <p>
                <label for="event_link"><?php echo __( 'Website', THEME ); ?></label>
                <input type="text" id="event_link" name="event_link" value="<?php echo $link; ?>">
            </p>

            <p>
                <label for="name"><?php echo __( 'Event image', THEME ); ?></label>
            <div id="file_upload"><span class="file_upload_button"></span></div>
            <input type="hidden" id="file_upload_data" name="file_upload_data" value="<?php echo $file;?>">
            <?php if ($file != 0) {
                $thumbnail = wp_get_attachment_image_url($file, 'event-thumbnail');
                ?>
                <div class="remove_image_wrapper img_wapper"><img data-dz-thumbnail="" alt="<?php echo basename( $thumbnail ); ?>" src="<?php echo $thumbnail  ?>"><button type="button" class="remove_image" data-id="<?php echo $file;?>">✕</button></div>
            <?php } ?>
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
            <p>
                <label><?php echo __('Description', THEME); ?></label>
                <?php wp_editor($description, 'description', array(
                    'wpautop' => true,
                    'teeny' => false,
                    'media_buttons' => false,
                )); ?>
            </p>
        </div>
        <div class="full_page">
            <p>
                <label><?php echo __('Add map location', THEME); ?></label>
                <input type="checkbox" class="map_location" name="map_location" <?php if ($map_location == 'on') { echo 'checked';} ?>>
            </p>
            <div id="map_picker" data-maker="<?php echo get_template_directory_uri() . '/assets/images/icons/map_sword.svg' ?>"></div>
        </div>
        <input type="hidden" id="lat_value" name="lat_value" value="<?php echo $lat; ?>">
        <input type="hidden" id="lng_value" name="lng_value"  value="<?php echo $lng; ?>">
        <input type="hidden" id="location_name_value" name="location_name_value">

        <input type="submit" class="button-primary" name="event_save" value="<?php echo __( 'Save', THEME ) ?>">
    </form>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.date_picker').datepicker({
                dateFormat : 'd.m.yy'
            });
        });
        $ = jQuery;
        

        var myDropzone = new Dropzone("#file_upload", {
            acceptedFiles: 'image/*',
            HiddenFilesPath : 'body',
            ignoreHiddenFiles: true,
            url: ajax_url,
            params: {
                action: 'upload_img'
            },
            maxFiles: 1,
            thumbnailWidth: 200,
            thumbnailHeight: 120,
            previewTemplate: '<div class="dz-preview img_wapper"><img data-dz-thumbnail /><button type="button" data-dz-remove>\u2715</button></div>',
            success: function success(file, response) {
                if ($('.remove_image') !== 'undefined') {
                    $.post(ajax_url, {
                        action: 'delete_attachment',
                        fileId: $('.remove_image').data('id')
                    }).always(function () {
                        $preview = $('.remove_image_wrapper');
                        $preview.fadeOut(function () {
                            return $preview.off().remove()
                        })
                    })
                }
                $('#file_upload_data').val(response);
            },
            removedfile: function removedfile(file) {
                var $field = $('.js-img-drop[data-name="' + file.name + '"]');
                var fileId = $field.val();
                var $preview = $('img[alt="' + file.name + '"]').parent();
                $preview.animate({
                    opacity: 0.5
                }, 150);
                $field.val('').addClass('empty').attr('data-name', '');
                $.post(ajax_url, {
                    action: 'delete_attachment',
                    fileId: fileId
                }).always(function() {
                    $preview.fadeOut(function() {
                        return $preview.off().remove()
                    })
                })
                $('#file_upload_data').val('');
            },
            init: function() {
                this.on("maxfilesexceeded", function(file){
                    this.removeAllFiles();
                    $('.dz-preview').remove();
                    this.addFile(file);
                });
            }
        });
    </script>