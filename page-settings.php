<?php
if (get_current_user_id() == 0) {
    return include('404.php');
}
get_header();
global $frontend;
?>
    <div class="content_wrapper">
        <h3><?php echo __('Settings', THEME) ?></h3>
    </div>
<?php
get_footer();