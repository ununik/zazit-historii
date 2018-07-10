<?php
if (get_current_user_id() == 0) {
    return include('404.php');
}

if (isset($_GET['user_nickname'])) {
    $usr = get_users(array(
        'meta_key' => 'nickname',
        'meta_value' => $_GET['user_nickname'],
        'meta_compare' => '='
    ));
    if ($usr) {
        $user_id = $usr[0]->ID;
    } else {
        return include('404.php');
    }
} else if (get_current_user_id() == 0) {
    return include('404.php');
} else {
    $user_id = get_current_user_id();
}

$current_user_logged = false;
if ($user_id == get_current_user_id()) {
    $current_user_logged = true;
}

get_header();
global $frontend;
?>
<div class="content_wrapper">
    <div class="profile_header">
        <div class="profil_image_wrapper">
            <img src="<?php
            $file = get_user_meta( $user_id, '_profil_image', true );
            if ($file != 0) {
                echo wp_get_attachment_image_url($file, 'thumbnail');
            } else {
                echo get_avatar_url($user_id);
            }
            ?>"></div>
        <h3 class="profil_name"><?php echo $frontend->get_user_name( $user_id ); ?></h3>
        <?php if ($current_user_logged) : ?>
            <a class="edit" href="<?php echo get_home_url().'/edit-profil/'; ?>" title="<?php echo __('Edit profil', THEME)?>"><?php echo __('Edit profil', THEME)?></a>
        <?php endif; ?>
    </div>
    <div class="clearfix">
        <?php
        $events = $frontend->getAllEventsForUser($user_id);
        if (count($events->posts) > 0) {
            echo '<h4>'.__('My events', THEME).'</h4>';
            echo '<ul>';
            foreach ($events->posts as $event) {
                echo '<li>';
                echo '<a href="'.get_the_permalink($event->ID).'">';
                echo $frontend->get_date_from_timestamps((int) get_post_meta( $event->ID, '_event_date_from', true ), (int) get_post_meta( $event->ID, '_event_date_to', true ));
                echo ' - ';
                echo get_the_title($event->ID);
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
</div>
<?php
get_footer();