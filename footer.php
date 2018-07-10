<?php
/**
 * The template for displaying the footer
 *
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */
$my_theme = wp_get_theme()
?>
            </div><!-- END #content -->
        </div><!-- END #main-part -->
        <footer id="main-footer">
            <div class="footer-navigation">
                <?php wp_nav_menu( array( 'theme_location' => 'footer-menu1' ) ); ?>
                <?php wp_nav_menu( array( 'theme_location' => 'footer-menu2' ) ); ?>
                <div class="clearfix"></div>
            </div>
            <div class="footer-wrapper">
                <div class="subfooter">© zazit-historii.cz <?php echo $my_theme->get( 'Version' ); ?></div>
                <div class="powered subfooter">Powered by <a href="http://ununik.cz/" target="_blank" title="Martin Přibyl (ununik)">ununik</a></div>
                <div class="clearfix"></div>
            </div>
        </footer>
    </div><!-- END .site-inner -->
</div><!-- END #page -->
<?php wp_footer(); ?>
<div id="preload"></div>
<div class="alert_wrappers">
    <?php
    $query = new WP_Query([
        'post_type' => '_alerts',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    if ($query) {
        foreach ($query->posts as $alert) {
            $cookie = get_post_meta($alert->ID, '_alerts_cookie', true);
            $dataType = '';
            if ($cookie == 'on') {
                if(!empty($_COOKIE['alert-'.$alert->ID])) {
                    continue;
                }
                $dataType = 'cookies';
            }
            $type = get_post_meta($alert->ID, '_alerts_type', true);
            $content = get_post_meta($alert->ID, '_alerts_text', true);
            ?>
            <div class="alert <?php echo $type?>">
                <span class="closebtn" data-id="<?php echo $alert->ID ?>" data-type="<?php echo $dataType ;?>">&times;</span>
                <?php echo $content ;?>
            </div>
            <?php
        }
    }
    ?>
</div>
</body>
</html>
