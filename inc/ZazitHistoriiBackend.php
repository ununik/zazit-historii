<?php
use Gizburdt\Cuztom\Cuztom;
use Gizburdt\Cuztom\Entities\PostType;
use Gizburdt\Cuztom\Entities\Sidebar;
use Gizburdt\Cuztom\Entities\Taxonomy;
use Gizburdt\Cuztom\Support\Guard;

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class ZazitHistoriiBackend extends ZazitHistorii
{
    /**
     * @var Events CPT
     */
    private $_events;

    private $_ads;

    public function __construct()
    {
        parent::__construct();
        
        /* Register CPTs */
        add_action( 'init', array( $this, 'register_events_cpt' ) );
        add_action( 'init', array( $this, 'register_ads_cpt' ) );

        global $wp_rewrite;
        $wp_rewrite->flush_rules();
        /* END Register CPTs */

        /* Register Taxonomy metaboxes */
        add_action( 'init', array( $this, 'register_ages_metaboxes' ) );
        /* END Register Taxonomy metaboxes */

        /* Save post actions */
        add_action( 'save_post', array( $this, 'save_events_hook' ) );
        /* END Save post actions */
    }

    public function register_events_cpt()
    {
        $this->_events = new PostType(
            '_events',
            [
                'labels'             => [
                    'name'                  => __( 'Events', THEME ),
                    'singular_name'         => __( 'Event', THEME ),
                    'menu_name'             => __( 'Events', THEME ),
                    'add_new'               => __( 'Add new', THEME ),
                    'add_new_item'          => __( 'Add new event', THEME ),
                    'new_item'              => __( 'New event', THEME ),
                    'edit_item'             => __( 'Edit event', THEME ),
                    'view_item'             => __( 'View event', THEME ),
                    'all_items'             => __( 'All events', THEME ),
                    'search_items'          => __( 'Search for event', THEME ),
                    'not_found'             => __( 'No event found', THEME ),
                    'not_found_in_trash'    => __( 'No event found in the trash', THEME ),
                    'featured_image'        => __( 'Event image', THEME ),
                    'set_featured_image'    => __( 'Set event image', THEME ),
                    'remove_featured_image' => __( 'Remove event image', THEME ),
                    'use_featured_image'    => __( 'Use event image', THEME ),
                ],
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => $this->_menu,
                'query_var'          => true,
                'rewrite'            => [
                    'slug'          => __( 'events', THEME ),
                ],
                'capability_type'    => 'post',
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => 1,
                'supports'           => [ 'title', 'thumbnail' ],
            ]
        );

        $this->_events->addMetaBox(
            '_events',
            [
                'title' =>  __( 'Detail', THEME ),
                'fields' => [
                    [
                        'id'     => '_data_tabs',
                        'type'   => 'tabs',
                        'panels' => [
                            [
                                'id'     => '_data_tabs_panel_1',
                                'title'  => __( 'Basic informations', THEME ),
                                'fields' => [
                                    [
                                        'id'    => '_event_date_from',
                                        'type'  => 'datetime',
                                        'label' => __( 'Date from', THEME ),
                                        'args'  => [
                                            'date_format' => 'mm/dd/yy'
                                        ]
                                    ],
                                    [
                                        'id'    => '_event_date_to',
                                        'type'  => 'datetime',
                                        'label' => __( 'Date to', THEME ),
                                        'args'  => [
                                            'date_format' => 'mm/dd/yy'
                                        ]
                                    ],
                                    [
                                        'id'    => '_event_place',
                                        'type'  => 'text',
                                        'label' => __( 'Place', THEME ),
                                    ],
                                    [
                                        'id'    => '_event_city',
                                        'type'  => 'text',
                                        'label' => __( 'City', THEME ),
                                    ],
                                    [
                                        'id'    => '_event_place_lat',
                                        'type'  => 'text',
                                        'label' => __( 'Lat', THEME ),
                                    ],
                                    [
                                        'id'    => '_event_place_lng',
                                        'type'  => 'text',
                                        'label' => __( 'Lng', THEME ),
                                    ],
                                ]
                            ],
                            [
                                'id'     => '_data_tabs_panel_2',
                                'title'  => __( 'Contact', THEME ),
                                'fields' => [
                                    [
                                        'id'    => '_event_email',
                                        'type'  => 'text',
                                        'label' => __( 'Email', THEME ),
                                    ],
                                    [
                                        'id'    => '_event_tel',
                                        'type'  => 'text',
                                        'label' => __( 'Phone', THEME ),
                                    ],
                                    [
                                        'id'    => '_event_link',
                                        'type'  => 'text',
                                        'label' => __( 'Link URL', THEME ),
                                    ],
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        );
    }

    public function register_ads_cpt()
    {
        $this->_ads = new PostType(
            '_advertisments',
            [
                'labels'             => [
                    'name'                  => __( 'Ads', THEME ),
                    'singular_name'         => __( 'Ad', THEME ),
                    'menu_name'             => __( 'Ads', THEME ),
                    'add_new'               => __( 'Add new', THEME ),
                    'add_new_item'          => __( 'Add new ad', THEME ),
                    'new_item'              => __( 'New ad', THEME ),
                    'edit_item'             => __( 'Edit ad', THEME ),
                    'view_item'             => __( 'View ad', THEME ),
                    'all_items'             => __( 'All ads', THEME ),
                    'search_items'          => __( 'Search for ad', THEME ),
                    'not_found'             => __( 'No ad found', THEME ),
                    'not_found_in_trash'    => __( 'No ad found in the trash', THEME ),
                ],
                'public'             => true,
                'publicly_queryable' => false,
                'show_ui'            => true,
                //'show_in_menu'       => $this->_menu,
                'query_var'          => true,
                'rewrite'            => [
                    'slug'          => __( 'ads', THEME ),
                ],
                'capability_type'    => 'post',
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => 1,
                'supports'           => [ 'title', 'editor' ],
            ]
        );

        $this->_ads->addMetaBox(
            '_advertisments',
            [
                'title' =>  __( 'Detail', THEME ),
                'fields' => [
                    [
                        'id'     => '_data_tabs',
                        'type'   => 'tabs',
                        'panels' => [
                            [
                                'id'     => '_data_tabs_panel_1',
                                'title'  => __( 'Basic informations', THEME ),
                                'fields' => [
                                    [
                                        'id'    => '_ad_link',
                                        'type'  => 'text',
                                        'label' => __( 'Link', THEME ),
                                    ],
                                    [
                                        'id'    => '_ad_views',
                                        'type'  => 'text',
                                        'label' => __( 'Views', THEME ),
                                    ],
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        );
    }

    public function register_ages_metaboxes()
    {
        register_cuztom_term_meta( 'custom_ages', '_ages',  [
                'fields' => [
                    [
                        'id'    => '_ages_default_image',
                        'type'  => 'image',
                        'label' => __( 'Default image', THEME ),
                    ],
                    [
                        'id'    => '_ages_short_name',
                        'type'  => 'text',
                        'label' => __( 'Short name', THEME ),
                        'description' => __( 'This short name appears in the cards image.', THEME ),
                    ],
                    [
                        'id'    => '_ages_year',
                        'type'  => 'text',
                        'label' => __( 'Year of beginning', THEME ),
                    ],
                    [
                        'id'    => '_ages_color',
                        'type'  => 'color',
                        'label' => __( 'Text color', THEME ),
                    ],
                    [
                        'id'    => '_ages_bgcolor',
                        'type'  => 'color',
                        'label' => __( 'Background color', THEME ),
                    ],
                ]
            ]
        );
    }

    public function save_events_hook( $post_id )
    {
        if ($_POST['post_type'] == '_events' && !isset($_POST['updated'])) {
            if (isset($_POST['cuztom']['_event_date_from'])) {
                if (!isset($_POST['cuztom']['_event_date_to']) || $_POST['cuztom']['_event_date_to'] == 0 || $_POST['cuztom']['_event_date_to'] == '') {
                    $_POST['cuztom']['_event_date_to'] = $_POST['cuztom']['_event_date_from'];
                }
                if ( strtotime( $_POST['cuztom']['_event_date_from'] ) > strtotime( $_POST['cuztom']['_event_date_to'] ) ) {
                    $save = $_POST['cuztom']['_event_date_from'];
                    $_POST['cuztom']['_event_date_from'] = $_POST['cuztom']['_event_date_to'];
                    $_POST['cuztom']['_event_date_to'] = $save;
                }
                $_POST['updated'] = true;
                wp_update_post(
                    [
                        'ID'            => $post_id,
                        'post_name'    => date('Y-m-d', strtotime( $_POST['cuztom']['_event_date_from'] ) ) . ' - ' . $_POST['post_title']
                    ]
                );
            }
        }
    }
}

$backend = new \ZazitHistoriiBackend();