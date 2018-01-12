<?php
if (get_current_user_id() == 0) {
    return include('404.php');
}
global $current_user;

if (isset($_POST['profil_save'])) {
    global $wp;
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $nickname = htmlspecialchars($_POST['nickname']);
    $email = htmlspecialchars($_POST['email']);

    $user_data = wp_update_user( array( 'ID' => get_current_user_id(), 'user_email' => $email ) );

    update_user_meta( get_current_user_id(), 'nickname', $nickname );
    update_user_meta( get_current_user_id(), 'first_name', $firstname );
    update_user_meta( get_current_user_id(), 'last_name', $lastname );


    header("Location:".home_url( $wp->request ));
    exit();
}

get_currentuserinfo();


get_header();
global $frontend;
?>
<h3>Profil - <?php echo $frontend->get_user_name( get_current_user_id() ); ?></h3>
<form action="" method="post">
    <div class="login_form half_page">
        <p>
            <label for="nickname"><?php echo __('Nickname', THEME);?></label>
            <input type="text" name="nickname" id="nickname" value="<?php echo get_user_meta( get_current_user_id(), 'nickname', true ) ?>">
        </p>
        <p>
            <label for="firstname"><?php echo __('Firstname', THEME);?></label>
            <input type="text" name="firstname" id="firstname" value="<?php echo get_user_meta( get_current_user_id(), 'first_name', true ) ?>">
        </p>
        <p>
            <label for="lastname"><?php echo __('Lastname', THEME);?></label>
            <input type="text" name="lastname" id="lastname" value="<?php echo get_user_meta( get_current_user_id(), 'last_name', true ) ?>">
        </p>
        <p>
            <label for="email"><?php echo __('Email', THEME);?></label>
            <input type="text" name="email" id="email" value="<?php echo $current_user->user_email; ?>">
        </p>
    </div>
    <div class="login_form half_page">
    </div>
    <input type="submit" value="<?php echo __('Save', THEME);?>" name="profil_save">
</form>
<?php
get_footer();