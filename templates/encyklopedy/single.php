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
        echo '<div>';
        the_content();

        $sources = get_post_meta(get_the_ID(), '_text_sources', true);
        if($sources){
            echo '<h4>'.__('Sources', THEME).':</h4>';
            echo '<ul class="sources">';
            foreach ($sources as $source) {
                if (!filter_var($source, FILTER_VALIDATE_URL) === false) {
                    $source = '<a href="'.$source.'" target="_blank">'.$source.'</a>';
                }
                echo '<li>'.$source.'</li>';
            }
            echo '</ul>';
        }

        $parent = wp_get_post_parent_id(get_the_ID());
        if ($parent && $parent != 0) {
            echo '<h4>'.__('Parent', THEME).':</h4>';
            echo '<a href="'.get_the_permalink($parent).'">';
            echo get_the_title($parent);
            echo '</a>';
        }

        $args['post_type'] = '_encyklopedy';
        $args['posts_per_page'] = -1;
        $args['orderby'] = 'menu_order';
        $args['post_parent__in'] = [get_the_ID()];

        $query = new WP_Query($args);
        if ($query && count($query->posts) > 0) {
            echo '<h4>'.__('Children', THEME).':</h4>';
            foreach ($query->posts as $child) {
                echo '<a href="'.get_the_permalink($child->ID).'">';
                echo get_the_title($child->ID);
                echo '</a>';
            }
        }

        echo '</div>';
        echo $frontend->show_ad();
    endwhile;
endif;
echo '</div>';
get_footer();