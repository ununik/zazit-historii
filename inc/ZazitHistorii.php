<?php
use Gizburdt\Cuztom\Cuztom;
use Gizburdt\Cuztom\Entities\PostType;
use Gizburdt\Cuztom\Entities\Sidebar;
use Gizburdt\Cuztom\Entities\Taxonomy;
use Gizburdt\Cuztom\Support\Guard;

class ZazitHistorii
{
    public function __construct()
    {
        /* Register menu */
        register_nav_menus( [
            'main-menu' => __( 'Main Menu', THEME ),
        ] );
        /* END Register menu */

        /* Register taxonomies */
        add_action( 'init', array( $this, 'register_ages_taxonomy' ) );
        /* END Register taxonomies */
    }

    public function register_ages_taxonomy()
    {
        $args = [
            'label' => __( 'Ages', THEME ),
            'rewrite' => array( 'slug' => __( 'ages', THEME ) ),
            'hierarchical' => true,
        ];

        register_cuztom_taxonomy( '_ages', '_events', $args );
    }
}