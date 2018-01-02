<?php
get_header();
if (!is_user_logged_in()) {
    if(isset($_GET['redirect'])) {
        $redirect_url = $_GET['redirect'];
    } else {
        $redirect_url = get_home_url();
    }

    echo '<div class="registration_form">';
    echo '<h3>'.__( 'Registration', THEME ).'</h3>';
    $frontend->custom_registration_function();
    echo '</div>';

    echo '<div class="login_form">';
    echo '<h3>'.__( 'Log In', THEME ).'</h3>';
    wp_login_form(array(
        'redirect'          => $redirect_url,
        'label_username'    => __( 'Username or Email Address', THEME ),
        'label_password'    => __( 'Password', THEME ),
        'label_remember'    => __( 'Remember Me', THEME ),
        'label_log_in'      => __( 'Log In', THEME ),
    ));
    echo '</div>';
}
get_footer();