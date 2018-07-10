<?php
/**
 * The main template file
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package zazit_historii
 * @subpackage zazit_historii
 * @since Zažít historii 1.0
 */

get_header();
global $frontend;
echo '<div class="content_wrapper">';
echo '<h3 class="headline">'.get_the_title().'</h3>';

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        echo '<div>';
        the_content();
        echo '</div>';
        echo $frontend->show_ad();
    endwhile;
endif;
echo '</div>';
get_footer();