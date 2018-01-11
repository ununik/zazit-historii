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
            <div class="footer-wrapper">
                <div class="subfooter">© zazit-historii.cz <?php echo $my_theme->get( 'Version' ); ?></div>
                <div class="powered subfooter">Powered by <a href="http://ununik.cz/" target="_blank" title="Martin Přibyl (ununik)">ununik</a></div>
            </div>
        </footer>
    </div><!-- END .site-inner -->
</div><!-- END #page -->

<?php wp_footer(); ?>
</body>
</html>
