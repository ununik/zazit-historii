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
    $file = (int) $_POST['file_upload_data'];

    $user_data = wp_update_user( array( 'ID' => get_current_user_id(), 'user_email' => $email ) );

    update_user_meta( get_current_user_id(), 'nickname', $nickname );
    update_user_meta( get_current_user_id(), 'first_name', $firstname );
    update_user_meta( get_current_user_id(), 'last_name', $lastname );
    update_user_meta( get_current_user_id(), '_profil_image', $file );

    header("Location:".home_url( $wp->request ));
    exit();
}

get_currentuserinfo();

$file = get_user_meta( get_current_user_id(), '_profil_image', true );

get_header();
global $frontend;
?>
<div class="content_wrapper">
<h3>Profil - <?php echo $frontend->get_user_name( get_current_user_id() ); ?></h3>
<form action="" method="post">
    <div class="login_form half_page">
        <p>
            <label for="name"><?php echo __( 'Profil image', THEME ); ?></label>
        <div id="file_upload"><span class="file_upload_button"></span></div>
        <input type="hidden" id="file_upload_data" name="file_upload_data" value="<?php echo $file;?>">
        <?php if ($file != 0) {
            $thumbnail = wp_get_attachment_image_url($file, 'thumbnail');
            ?>
            <div class="remove_image_wrapper img_wapper"><img data-dz-thumbnail="" alt="<?php echo basename( $thumbnail ); ?>" src="<?php echo $thumbnail  ?>"><button type="button" class="remove_image" data-id="<?php echo $file;?>">✕</button></div>
        <?php } ?>
        </p>
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
</div>
<?php
get_footer();
?>
<script>
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
