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
echo '<h3>'.get_the_title().'</h3>';

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        echo '<div>'.get_the_content().'</div>';
        echo $frontend->show_ad();
    endwhile;
endif;
echo '</div>';
get_footer();