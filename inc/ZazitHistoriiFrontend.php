<?php
class ZazitHistoriiFrontend extends ZazitHistorii
{
    private $defaultEventColor = '#444';
    private $defaultEventBGColor = 'white';
    private $countEventsForAd = 60;
    private $googleMapKey = 'AIzaSyCYT9mAEwA8nisjkytdVrX8K0JXJGjf9yQ';
    public $events_on_page = 60;
    static $breadcrumbChildren = [];

    public function __construct()
    {
        parent::__construct();
        wp_enqueue_media();                     
        wp_enqueue_script(THEME . '_script', get_template_directory_uri() . '/assets/scripts/scripts.js', array('jquery'), '20160816', true);
        wp_enqueue_script(THEME . '_maps', 'http://maps.google.com/maps/api/js?v=3.0&sensor=false&libraries=places&key='.$this->googleMapKey, array('jquery'), '20160816', false);
        wp_enqueue_script(THEME . '_maps_cluster', get_template_directory_uri() . '/assets/scripts/markerclusterer.js', array('jquery'), '20160816', false);
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script(THEME . '_dropzone', get_template_directory_uri() . '/assets/scripts/dropzone.js', array('jquery'), '20160816', false);


        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

        wp_enqueue_script(THEME . '_maps_picker', get_template_directory_uri() . '/assets/scripts/jquery-locationpicker-plugin/src/locationpicker.jquery.js', array('jquery'), '20160816', false);
        $my_theme = wp_get_theme();
        wp_enqueue_style('main_styles', get_template_directory_uri() . '/style.css', array(), $my_theme->get( 'Version' ));

        /* Import templates */
        add_filter('single_template', array($this, 'import_single_template'));
        add_filter('archive_template', array($this, 'import_archive_template'));
        /* Import templates */

        add_action('pre_get_posts', array($this, 'parse_request'), 10, 2);

        add_filter('pre_get_posts', array($this, 'custom_front_page'));

        add_action('wp_ajax_map_data', array($this, 'map_data'));
        add_action('wp_ajax_nopriv_map_data', array($this, 'map_data'));

        add_action('wp_ajax_places_map_data', array($this, 'places_map_data'));
        add_action('wp_ajax_nopriv_places_map_data', array($this, 'places_map_data'));

        add_action('wp_ajax_next_event_page', array($this, 'next_event_page'));
        add_action('wp_ajax_next_event_pagea', array($this, 'next_event_page'));

        add_action('wp_ajax_upload_img', array($this, 'upload_img'));
        add_action('wp_ajax_delete_attachment', array($this, 'delete_attachment'));

        add_action('wp_ajax_ad_click', array($this, 'ad_click'));
        add_action('wp_ajax_nopriv_ad_click', array($this, 'ad_click'));

        add_action('wp_head', array( $this, 'define_constants_for_scripts' ) );
        add_action('wp_head', array( $this, 'add_meta_tags' ) );

        add_filter( 'wp_title', array( $this, 'site_title' ), 10, 2 );

        add_action( 'init', array( $this, 'register_my_menus' ) );
    }

    public function import_single_template($single_template)
    {
        global $post;

        switch ($post->post_type) {
            case '_events':
                $single_template = dirname(__FILE__) . '/../templates/events/single.php';
                break;
            case '_encyklopedy':
                $single_template = dirname(__FILE__) . '/../templates/encyklopedy/single.php';
                break;
            case '_places':
                $single_template = dirname(__FILE__) . '/../templates/places/single.php';
                break;
        }
        return $single_template;
    }

    public function import_archive_template($archive_template)
    {
        global $post;

        if ($post->post_type == '_events' && is_post_type_archive('_events')) {
            $archive_template = dirname(__FILE__) . '/../templates/events/archive.php';
        }

        if ($post->post_type == '_places' && is_post_type_archive('_places')) {
            $archive_template = dirname(__FILE__) . '/../templates/places/archive.php';
        }

        return $archive_template;
    }

    public function get_all_events_from_date_to_date($date1, $date2 = 0, $ages = [], $search = '', $limit = 0, $page = 1)
    {
        if ($limit == 0) {
            $limit = $this->events_on_page;
        }
        $args['post_type'] = '_events';
        $args['posts_per_page'] = $limit;
        $args['order'] = 'ASC';
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_event_date_from';
        $args['paged'] = $page;
        if (get_current_user_id() == 0) {
            $meta_query = [
                [
                    'key' => '_event_date_to',
                    'value' => $date1 - 1,
                    'compare' => '>'
                ],
                [
                    'key' => '_event_only_for_registrated_users',
                    'value' => 'on',
                    'compare' => '!='
                ]

            ];
        } else {
            $meta_query = [
                [
                    'key' => '_event_date_to',
                    'value' => $date1 - 1,
                    'compare' => '>'
                ]
            ];
        }
        $args['meta_query'] = $meta_query;

        if (count($ages) > 0) {
            $args['tax_query'] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => '_ages',
                    'field' => 'slug',
                    'terms' => $ages,
                ),
                array(
                    'taxonomy' => '_themes',
                    'field' => 'slug',
                    'terms' => $ages,
                ),
            );
        }

        if ($search != '') {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);

        return $query;
    }

    public function get_date_from_timestamps($date1, $date2 = 0)
    {
        if ($date2 == 0) {
            $date2 = $date1;
        }
        if (date('j.n.Y', $date1) == date('j.n.Y', $date2)) {
            return date('j. n. Y', $date1);
        }

        if (date('n.Y', $date1) == date('n.Y', $date2)) {
            return date('j.', $date1) . ' - ' . date('j. n. Y', $date2);
        }

        if (date('Y', $date1) == date('Y', $date2)) {
            return date('j. n. ', $date1) . ' - ' . date('j. n. Y', $date2);
        }

        return date('j. n. Y', $date1) . ' - ' . date('j. n. Y', $date2);
    }

    public function get_all_ages_list( $hide_empty = true )
    {
        $terms = $this->get_ages_by_parent(0, $hide_empty);

        return $terms;
    }

    private function get_ages_by_parent($parent, $hide_empty)
    {
        $terms = get_terms(
            [
                'taxonomy' => '_ages',
                'parent' => $parent,
                'meta_key' => '_ages_year',
                'orderby' => '_ages_year',
                'hide_empty' => $hide_empty
            ]
        );

        $return = [];
        foreach ($terms as $term) {
            $return[$term->term_id]['data'] = $term;
            $return[$term->term_id]['children'] = $this->get_ages_by_parent($term->term_id, $hide_empty);
        }

        return $return;
    }

    public function show_navigation_from_posts($post_type, $parent = 0, $fistElement = false)
    {
        global $post;

        $return = '';
        $query = new WP_Query([
            'post_parent' => $parent,
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'menu_order'
        ]);

        if ($query && count($query->posts) > 0) {
            if ( $parent != 0 ) {
                $return .= '<div class="parent_toggle"';
                if (!$fistElement) {
                    $return .= ' style="display: none;" ';
                }
                $return .= '></div>';
                $return .= '<ul class="term-navigation">';
            } else {
                $return .= '<ul class="term-navigation main-parent">';
            }

            foreach ($query->posts as $postData) {
                $return .= '<li>';
                $return .= '<div class="inner_link';
                if ( $parent == 0 ) {
                    $return .= ' parent_link ';
                } else {
                    $return .= ' child_link ';
                }
                $return .= '"><a href="'.get_the_permalink($postData->ID).'" class="link ';
                if ( get_the_ID() == $postData->ID ) {
                    $return .= 'current_link';
                }
                $return .= '">';
                $return .= get_the_title($postData->ID);
                $return .= '</a>';

                $return .= '</div>';
                if ($parent == 0 ) {
                    $return .= $this->show_navigation_from_posts($post_type, $postData->ID, true);
                } else {
                    $return .= $this->show_navigation_from_posts($post_type, $postData->ID);
                }

                $return .= '</li>';
            }
            $return .= '</ul>';
        }
        return $return;
    }

    public function show_navigation_from_terms($terms, $current_item_slug = '')
    {
        if (count($terms) == 0) {
            return '';
        }
        $return = '<ul class="term-navigation main-parent">';
        foreach ($terms as $term) {
            $events = $this->get_all_events_from_date_to_date(strtotime('today')-1, 0, [$term['data']->slug]);
            $count_of_events = $events->post_count;
            if ($count_of_events == 0) {
                continue;
            }
            $return .= '<li>';
            $return .= '<div class="inner_link';
            if ( $term['data']->parent == 0 ) {
                $return .= ' parent_link ';
            } else {
                $return .= ' child_link ';
            }
            $return .= '"><a href="' . get_post_type_archive_link('_events') . $term['data']->slug . '/"';

            $return .= ' class="link ';
            if ( $current_item_slug == $term['data']->slug ) {
                $return .= 'current_link';
            }
            $return .= '" ';
            $return .= '>';
            $return .= $term['data']->name;
            $return .= '<span class="count_of_events">('.$count_of_events.')</span></a></div>';
            if ( count($term['children']) > 0 ) {
                $return .= '<div class="parent_toggle"></div>';
            }
            $return .= $this->show_navigation_from_terms($term['children'], $current_item_slug);
            $return .= '</li>';
        }
        $return .= '</ul>';

        return $return;
    }

    public function show_checkboxes_from_terms($terms, $current_items_slug = array() )
    {
        $return = '<ul class="term-navigation">';
        foreach ($terms as $term) {
            $checked = false;
            foreach ($current_items_slug as $current_item_slug) {
                if ($current_item_slug == $term['data']->term_id) {
                    $checked = true;
                    break;
                }
            }
            $return .= '<li>';
            if ($checked) {
                $return .= '<input type="checkbox" name="ages[]" value="' . $term['data']->slug . '" checked>';
            } else {
                $return .= '<input type="checkbox" name="ages[]" value="' . $term['data']->slug . '">';
            }
            $return .= $term['data']->name;
            $return .= $this->show_checkboxes_from_terms($term['children'], $current_items_slug);
            $return .= '</li>';
        }
        $return .= '</ul>';

        return $return;
    }

    /**
     * TODO: return only last child - now it's first term
     */
    public function get_last_term_child($terms)
    {
        $return = null;

        if (count($terms) > 0) {
            if (count($terms) > 1) {
                $return = $terms[count($terms) - 1];
            } else {
                $return = $terms[0];
            }
        }

        return $return;
    }

    /**
     * TODO: add new CPT - ads + create ads template
     */
    public function show_ad()
    {
        $args['post_type'] = '_advertisments';
        $args['posts_per_page'] = 1;
        $args['orderby'] = 'rand';

        $query = new WP_Query($args);

        if ($query) {
            $views = (int)get_post_meta($query->posts[0]->ID, '_ad_views', true);
            $views++;
            update_post_meta($query->posts[0]->ID, '_ad_views', $views);
            $link = get_post_meta($query->posts[0]->ID, '_ad_link', true);

            if ($link && $link != '') {
                $return = '<div class="partner-wrapper" data-id="'.$query->posts[0]->ID.'"><a href="' . $link . '" target="_blank">' . $query->posts[0]->post_content . '</a></div>';
            } else {
                $return = '<div class="partner-wrapper" data-id="'.$query->posts[0]->ID.'">' . $query->posts[0]->post_content . '</div>';
            }
        } else {
            $return = '<div class="partner-wrapper" data-id="0"></div>';
        }

        return $return;
    }

    public function ad_click()
    {
        $clicks = (int)get_post_meta((int) $_POST['ad_id'], '_ad_clicks', true);
        $clicks++;
        update_post_meta((int) $_POST['ad_id'], '_ad_clicks', $clicks);
        die();
    }

    public function get_default_event_color()
    {
        return $this->defaultEventColor;
    }

    public function get_default_event_bgcolor()
    {
        return $this->defaultEventBGColor;
    }

    public function countEventsForAd()
    {
        return (int)$this->countEventsForAd;
    }

    public function registration_form($username, $password, $email, $website, $first_name, $last_name, $nickname, $bio)
    {

        echo '
    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
    <p>
    <label for="username">'.__('Username', THEME).' <strong>*</strong></label>
    <input type="text" name="username" value="' . (isset($_POST['username']) ? $username : null) . '">
    </p>
    
    <p>
    <label for="email">'.__('Email', THEME).' <strong>*</strong></label>
    <input type="text" name="email" value="' . (isset($_POST['email']) ? $email : null) . '">
    </p>
     
    <p>
    <label for="password">'.__('Password', THEME).' <strong>*</strong></label>
    <input type="password" name="password" value="' . (isset($_POST['password']) ? $password : null) . '">
    </p>

    <p>
    <input type="checkbox" name="rules" value="agree">';
        printf(__('Do you agree with our <a href="%s" target="_blank">rules</a>?', THEME), get_the_permalink( 17 ) );

        echo '<strong>*</strong>
    </p>
     
    <input type="submit" name="submit" value="Register" class="button-primary"/>
    </form>
    ';
    }

    public function complete_registration()
    {
        global $reg_errors, $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio;
        if (1 > count($reg_errors->get_error_messages())) {
            $userdata = array(
                'user_login' => $username,
                'user_email' => $email,
                'user_pass' => $password,
                'user_url' => $website,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'nickname' => $nickname,
                'description' => $bio,
            );
            $user = wp_insert_user($userdata);
            echo 'Registration complete. Now you can log in.';
        }
    }

    public function custom_registration_function()
    {
        if (isset($_POST['submit'])) {
            $this->registration_validation(
                $_POST['username'],
                $_POST['password'],
                $_POST['email'],
                $_POST['website'],
                $_POST['fname'],
                $_POST['lname'],
                $_POST['nickname'],
                $_POST['bio'],
                isset($_POST['rules']) ? $_POST['rules'] : ''
            );

            // sanitize user form input
            global $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio;
            $username = sanitize_user($_POST['username']);
            $password = esc_attr($_POST['password']);
            $email = sanitize_email($_POST['email']);
            $website = esc_url($_POST['website']);
            $first_name = sanitize_text_field($_POST['fname']);
            $last_name = sanitize_text_field($_POST['lname']);
            $nickname = sanitize_text_field($_POST['nickname']);
            $bio = esc_textarea($_POST['bio']);

            // call @function complete_registration to create the user
            // only when no WP_error is found
            $this->complete_registration(
                $username,
                $password,
                $email,
                $website,
                $first_name,
                $last_name,
                $nickname,
                $bio
            );
        }

        $this->registration_form(
            $username,
            $password,
            $email,
            $website,
            $first_name,
            $last_name,
            $nickname,
            $bio
        );
    }

    public function registration_validation($username, $password, $email, $website, $first_name, $last_name, $nickname, $bio, $rules)
    {
        global $reg_errors;
        $reg_errors = new WP_Error;

        if (empty($username) || empty($password) || empty($email)) {
            $reg_errors->add('field', __('Required form field is missing', THEME));
        }

        if (4 > strlen($username)) {
            $reg_errors->add('username_length', __('Username too short. At least 4 characters is required', THEME));
        }

        if (username_exists($username)) {
            $reg_errors->add('user_name', __('Sorry, that username already exists!', THEME));
        }

        if (!validate_username($username)) {
            $reg_errors->add('username_invalid', __('Sorry, the username you entered is not valid', THEME));
        }

        if (5 > strlen($password)) {
            $reg_errors->add('password', __('Password length must be greater than 5', THEME));
        }

        if (!is_email($email)) {
            $reg_errors->add('email_invalid', __('Email is not valid', THEME));
        }

        if (email_exists($email)) {
            $reg_errors->add('email', __('Email Already in use', THEME));
        }

        if (email_exists($email)) {
            $reg_errors->add('email', __('Email Already in use', THEME));
        }
        if ($rules != 'agree') {
            $reg_errors->add('rule', __('You must agree with our rules', THEME));
        }

        if (is_wp_error($reg_errors)) {
            foreach ($reg_errors->get_error_messages() as $error) {
                echo '<div class="error_message">';
                echo '<strong>ERROR:</strong>';
                echo $error . '<br/>';
                echo '</div>';
            }
        }
    }

    public function get_user_name($user_id)
    {
        $user = get_user_by('id', $user_id);
        $nickname = get_user_meta( $user_id, 'nickname', true );

        if ($user->user_firstname == '' && $user->user_lastname == '') {
            if ($nickname == '') {
                return $user->user_login;
            } else {
                return $nickname;
            }
        }

        if ($nickname == '') {
            return $user->user_firstname . ' ' . $user->user_lastname;
        } else {
            return $user->user_firstname . ' ' . $user->user_lastname .' ('.$nickname.')';
        }
    }

    public function parse_request($query)
    {
        global $wp_query;

        // EVENT AGES
        $matches = null;
        preg_match('/'.__('events', THEME).'\/(.*)\//', $_SERVER["REQUEST_URI"], $matches, PREG_OFFSET_CAPTURE);

        if ((isset($matches[1][0]) && $matches[1][0] != '') && !isset($_GET['custom'])) {
            $_GET['custom'] = true;
            if (!is_numeric(substr($matches[1][0], 0, 4))) {
                $_GET[ __( 'ages', THEME ) ] = $matches[1][0];
                $_GET['ages'] = $matches[1][0];
                $wp_query = new WP_Query(
                    array(
                        'post_type' => '_events',
                        'action_trigger' => true
                    )
                );
                $wp_query->is_archive = true;
                $wp_query->is_post_type_archive = true;
                $wp_query->is_singular = false;

                return $wp_query;
            }
        }

        // EVENT AGES
        $matches = null;
        preg_match('/profil\/(.*)\//', $_SERVER["REQUEST_URI"], $matches, PREG_OFFSET_CAPTURE);

        if ((isset($matches[1][0]) && $matches[1][0] != '') && !isset($_GET['custom'])) {
            $_GET['custom'] = true;
            $_GET['user_nickname'] = $matches[1][0];
            //$_SERVER["REQUEST_URI"] = substr($_SERVER["REQUEST_URI"], 0, strlen($_SERVER["REQUEST_URI"]) - strlen($matches[0][0])).'/profil/';
            include __DIR__.'/../page-profil.php';
            exit();
        }
    }

    public function custom_front_page($query)
    {
        global $wp_query;

        if (is_front_page() && !isset($_GET['custom'])) {
            $_GET['custom'] = true;
            $wp_query = new WP_Query(
                array(
                    'post_type' => '_events',
                    'action_trigger' => true
                )
            );
            $wp_query->is_archive = true;
            $wp_query->is_post_type_archive = true;
            $wp_query->is_singular = false;

            return $wp_query;
        }
        return $query;
    }

    public function map_data()
    {
        $detail = false;
        if (isset($_REQUEST['ages'])) {
            $events = $this->get_all_events_from_date_to_date(
                strtotime('today', current_time('timestamp')),
                0,
                $_REQUEST['ages'],
                ''
            );
        } else if($_REQUEST['search']) {
            $events = $this->get_all_events_from_date_to_date(
                strtotime('today', current_time('timestamp')),
                0,
                [],
                $_REQUEST['search']
            );
        } else if($_REQUEST['id']) {
            $events = new WP_Query(
                array(
                    'post_type' => '_events',
                    'p' => $_REQUEST['id']
                )
            );
            $detail = true;
        } else {
            $events = $this->get_all_events_from_date_to_date(
                strtotime('today', current_time('timestamp'))
            );
        }


        $return = [];
        foreach ($events->posts as $event) {
            $map = get_post_meta( $event->ID, 'event_map_location', true );
            if ($map != 'on') {
                continue;
            }
            $lat = (float) get_post_meta( $event->ID, '_event_place_lat', true );
            $lng = (float) get_post_meta( $event->ID, '_event_place_lng', true );

            $data = [];
            if ($lat && $lng && (float) $lat != 0 && (float) $lng != 0 ) {
                $data['location']['lat'] = $lat;
                $data['location']['lng'] = $lng;
                $data['name'] = get_the_title($event->ID);
                $data['link'] = get_the_permalink($event->ID);
                $data['city'] =  get_post_meta( $event->ID, '_event_city', true );
                $data['date'] = $this->get_date_from_timestamps((int) get_post_meta( $event->ID, '_event_date_from', true ), (int) get_post_meta( $event->ID, '_event_date_to', true ));

                $return[] = $data;
            }
        }

        header('Content-type: application/json');
        echo json_encode(['response' => $return, 'home_path' => get_template_directory_uri(), 'detail' => $detail]);
        die();
    }

    public function places_map_data()
    {
        if($_REQUEST['id']) {
            $events = new WP_Query(
                array(
                    'post_type' => '_places',
                    'post_status' => 'publish',
                    'p' => $_REQUEST['id']
                )
            );
        } else {
            $events = new WP_Query(
                array(
                    'post_type' => '_places',
                    'post_status' => 'publish',
                    'posts_per_page'=>-1
                )
            );
        }


        $return = [];
        foreach ($events->posts as $event) {
            $map = get_post_meta( $event->ID, '_place_map_location', true );
            if ($map != 'on') {
                continue;
            }
            $lat = (float) get_post_meta( $event->ID, '_place_lat', true );
            $lng = (float) get_post_meta( $event->ID, '_place_lng', true );

            $data = [];
            if ($lat && $lng && (float) $lat != 0 && (float) $lng != 0 ) {
                $data['location']['lat'] = $lat;
                $data['location']['lng'] = $lng;
                $data['name'] = get_the_title($event->ID);
                $data['link'] = get_the_permalink($event->ID);
                $data['city'] =  '';
                $data['date'] = '';

                $return[] = $data;
            }
        }

        header('Content-type: application/json');
        echo json_encode(['response' => $return, 'home_path' => get_template_directory_uri(), 'detail' => true]);
        die();
    }

    public function getAllEventsForUser( $user_id )
    {
        $args['post_type'] = '_events';
        $args['posts_per_page'] = -1;
        $args['author'] = $user_id;
        $args['order'] = 'DESC';
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_event_date_from';

        $query = new WP_Query($args);

        return $query;
    }

    public function define_constants_for_scripts()
    {
        echo '<script type="text/javascript">
           var ajax_url = "'.admin_url().'/admin-ajax.php";
         </script>';
    }

    public function site_title($name, $split) {
        $name = get_bloginfo('name') . $name;
        return $name;
    }

    public function upload_img()
    {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $movefile = media_handle_upload('file', 0);

        if ($movefile && !isset($movefile['error'])) {
            echo $movefile;
        } else {
            echo $movefile['error'];
        }
        die();
    }

    public function delete_attachment()
    {
        if (isset($_POST['fileId'])) {
            if (wp_delete_attachment($_POST['fileId'], true)) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
        die();
    }

    public function register_my_menus()
    {
        register_nav_menus(
            array(
                'main-menu' => __( 'Main Menu' ),
                'footer-menu1' => __( 'Footer Menu 1' ),
                'footer-menu2' => __( 'Footer Menu 2' ),
            )
        );
    }

    public function add_meta_tags()
    {
        global $post;

        if ($post->post_type == '_events') {
            if (is_single()) {
                $date = $this->get_date_from_timestamps((int) get_post_meta( get_the_ID(), '_event_date_from', true ), (int) get_post_meta( get_the_ID(), '_event_date_to', true ));
                echo '<meta name="title" content="'.get_the_title().' ('.$date.')">';
                echo '<meta property="og:title" content="'.get_the_title().' ('.$date.')" />';

                echo '<meta property="og:image" content="'.get_template_directory_uri().'/assets/images/logo/logo.png'.'" />';
            }
        }
    }

    public function next_event_page()
    {
        if ( $_REQUEST['age'] != '' ) {
            $query = $this->get_all_events_from_date_to_date(
                strtotime('today', current_time('timestamp')),
                0,
                $_REQUEST['age'],
                '',
                0,
                $_REQUEST['page']
            );
        } else if ($_REQUEST['search']) {
            $query = $this->get_all_events_from_date_to_date(
                strtotime('today', current_time('timestamp')),
                0,
                [],
                $_REQUEST['search'],
                0,
                $_REQUEST['page']
            );
        } else {
            $query = $this->get_all_events_from_date_to_date(
                strtotime('today', current_time('timestamp')),
                0,
                [],
                '',
                0,
                $_REQUEST['page']
            );
        }


        $results = [];
        $i = 0;
        $content = '';
        if (count($query->posts) == 0 ) {
            echo json_encode(['content' => $content, 'next_page' => false]);
            die();
        }

        foreach ($query->posts as $event) {
            $content .= $this->archiveEventsTemplate($event);
        }
        //$content .= $this->show_ad();
        $nextPage = true;
        if($query->found_posts <= $this->events_on_page * $this->events_on_page * $_REQUEST['page']) {
            $nextPage = false;
        }

        echo json_encode(['content' => $content, 'next_page' => $nextPage]);
        wp_die();
        die();
    }

    public function get_nearest_places( $lat, $lng, $limit = 10)
    {

        //((ACOS(SIN(' . $lat . ' * PI() / 180) * SIN(**map_lat** * PI() / 180) + COS(' . $lat . ' * PI() / 180) * COS(**map_lat** * PI() / 180) * COS((' . $lng . ' - **map_lng**) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS "distance"
        global $wpdb;
//SELECT * ((ACOS(SIN(' . $lat . ' * PI() / 180) * SIN(lat* PI() / 180) + COS(' . $lat . ' * PI() / 180) * COS(lat* PI() / 180) * COS((' . $lng . ' - lng) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS "distance" FROM `zazit_h_posts` RIGHT JOIN ((SELECT *, `meta_value` as lat FROM `zazit_h_postmeta` WHERE `meta_key` = '_place_lat') as lat, (SELECT *, `meta_value` as lng FROM `zazit_h_postmeta` WHERE `meta_key` = '_place_lng') as lng  )  ON (`zazit_h_posts`.`ID` = lat.`post_id`) OR (`zazit_h_posts`.`ID` = lng.`post_id`) WHERE  `zazit_h_posts`.`post_type` = '_places' AND `zazit_h_posts`.`post_status` = 'publish' GROUP  BY `zazit_h_posts`.`ID` ORDER BY `distance` ASC
        $sql = "SELECT *, (acos
    (   cos($lat * pi() / 180)*cos($lng* pi() / 180)*cos(lat * pi() / 180)*cos(lng * pi() / 180)
      + cos($lat * pi() / 180)*sin($lng* pi() / 180)*cos(lat * pi() / 180)*sin(lng * pi() / 180)
      + sin($lat * pi() / 180)*sin(lat* pi() / 180)
    ) * 6372.795) AS `distance` FROM `zazit_h_posts`
    
RIGHT JOIN (
(SELECT `post_id`, `meta_value` as lat FROM `zazit_h_postmeta` WHERE `meta_key` = '_place_lat') as lat
) ON (`zazit_h_posts`.`ID` = lat.`post_id`)
RIGHT JOIN (
(SELECT `post_id`, `meta_value` as lng FROM `zazit_h_postmeta` WHERE `meta_key` = '_place_lng') as lng
) ON (`zazit_h_posts`.`ID` = lng.`post_id`)
     
     WHERE  `zazit_h_posts`.`post_type` = '_places' AND `zazit_h_posts`.`post_status` = 'publish' GROUP  BY `zazit_h_posts`.`ID` ORDER BY `distance` ASC LIMIT $limit;";
        $results = $GLOBALS['wpdb']->get_results(
            $sql, OBJECT
        );

        return $results;
    }

    public function get_nearest_events( $lat, $lng, $limit = 10, $enddate = 0)
    {

        if ($enddate == 0) {
            $enddate = time() + 3600*24*365;
        }
        //((ACOS(SIN(' . $lat . ' * PI() / 180) * SIN(**map_lat** * PI() / 180) + COS(' . $lat . ' * PI() / 180) * COS(**map_lat** * PI() / 180) * COS((' . $lng . ' - **map_lng**) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS "distance"
        global $wpdb;
//SELECT * ((ACOS(SIN(' . $lat . ' * PI() / 180) * SIN(lat* PI() / 180) + COS(' . $lat . ' * PI() / 180) * COS(lat* PI() / 180) * COS((' . $lng . ' - lng) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS "distance" FROM `zazit_h_posts` RIGHT JOIN ((SELECT *, `meta_value` as lat FROM `zazit_h_postmeta` WHERE `meta_key` = '_place_lat') as lat, (SELECT *, `meta_value` as lng FROM `zazit_h_postmeta` WHERE `meta_key` = '_place_lng') as lng  )  ON (`zazit_h_posts`.`ID` = lat.`post_id`) OR (`zazit_h_posts`.`ID` = lng.`post_id`) WHERE  `zazit_h_posts`.`post_type` = '_places' AND `zazit_h_posts`.`post_status` = 'publish' GROUP  BY `zazit_h_posts`.`ID` ORDER BY `distance` ASC
        $sql = "SELECT *, (acos
    (   cos($lat * pi() / 180)*cos($lng* pi() / 180)*cos(lat * pi() / 180)*cos(lng * pi() / 180)
      + cos($lat * pi() / 180)*sin($lng* pi() / 180)*cos(lat * pi() / 180)*sin(lng * pi() / 180)
      + sin($lat * pi() / 180)*sin(lat* pi() / 180)
    ) * 6372.795) AS `distance` FROM `zazit_h_posts` 
     
RIGHT JOIN (
(SELECT `post_id`, `meta_value` as lat FROM `zazit_h_postmeta` WHERE `meta_key` = '_event_place_lat') as lat
) ON (`zazit_h_posts`.`ID` = lat.`post_id`)
RIGHT JOIN (
(SELECT `post_id`, `meta_value` as lng FROM `zazit_h_postmeta` WHERE `meta_key` = '_event_place_lng') as lng
) ON (`zazit_h_posts`.`ID` = lng.`post_id`)
RIGHT JOIN (
(SELECT `post_id`, `meta_value` as dateto FROM `zazit_h_postmeta` WHERE `meta_key` = '_event_date_to') as dateto
) ON (`zazit_h_posts`.`ID` = dateto.`post_id`)
RIGHT JOIN (
(SELECT `post_id`, `meta_value` as map FROM `zazit_h_postmeta` WHERE `meta_key` = 'event_map_location') as map
) ON (`zazit_h_posts`.`ID` = map.`post_id`)
     
     WHERE  `zazit_h_posts`.`post_type` = '_events' AND `zazit_h_posts`.`post_status` = 'publish' AND dateto.`dateto` > ".time()." AND dateto.`dateto` < ".$enddate." AND map.`map` = 'on'
     
     GROUP  BY `zazit_h_posts`.`ID` ORDER BY `distance` ASC LIMIT $limit;";
        $results = $GLOBALS['wpdb']->get_results(
            $sql, OBJECT
        );

        return $results;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    static function getBreadcrumb($connector = '&gt;')
    {
        global $post;

        $breadcrumb = [];

        if (is_archive()) {
            $breadcrumb[] = '<a href="'.get_post_type_archive_link( get_post_type() ).'">'
                . post_type_archive_title( '', false )
                . '</a>';
        } else {
            $obj = get_post_type_object( get_post_type() );
            if($obj->name != 'page' || $obj->name != '') {
                $breadcrumb[] = '<a href="' . get_post_type_archive_link(get_post_type()) . '">'
                    . $obj->labels->name
                    . '</a>';
            }

            self::getNotArchiveParts(get_the_ID());
            for($i = count(self::$breadcrumbChildren)-1; $i >= 0; $i--) {
                $breadcrumb[] = self::$breadcrumbChildren[$i];
            }
        }

        $breadcrumbData = [];
        foreach ($breadcrumb as $breadcrumbEntity) {
            $breadcrumbData[] = '<span class="breadcrumb_entity">'.$breadcrumbEntity.'</span>';
        }

        return implode($connector, $breadcrumbData);
    }

    public function getNotArchiveParts($id)
    {
        $title = get_the_title($id);
        if (!empty($title)) {
            self::$breadcrumbChildren[] = '<a href="' . get_the_permalink($id) . '">'
                . $title
                . '</a>';

            $parent = wp_get_post_parent_id($id);
            if ($parent) {
                self::getNotArchiveParts($parent);
            }
        }
    }

    public function archiveEventsTemplate( $event )
    {
        $return = '';
        $ageTerms = get_the_terms( $event->ID, '_ages' );
        $age = $this->get_last_term_child( $ageTerms );
        $city = get_post_meta( $event->ID, '_event_city', true );
        $thumbnail = '';
        $thumbnail = get_the_post_thumbnail_url($event->ID, 'event-thumbnail');
        if ( !$thumbnail || $thumbnail == '' ) {
            $img_id = get_term_meta( $age->term_id, '_ages_default_image', true );
            if ( $img_id ) {
                $thumbnail = wp_get_attachment_image_src($img_id, 'event-thumbnail');
                $thumbnail = $thumbnail[0];
            } else {
                $img_id = get_term_meta( $age->parent, '_ages_default_image', true );
                if ( $img_id ) {
                    $thumbnail = wp_get_attachment_image_src($img_id, 'event-thumbnail');
                    $thumbnail = $thumbnail[0];
                }
            }
        }

        $date = $this->get_date_from_timestamps((int) get_post_meta( $event->ID, '_event_date_from', true ), (int) get_post_meta( $event->ID, '_event_date_to', true ));
        $city = get_post_meta( $event->ID, '_event_city', true );
        $organisator = get_post_meta( $event->ID, 'event_organisator', true );
        $map = get_post_meta( $event->ID, 'event_map_location', true );
        $lat = get_post_meta( $event->ID, '_event_place_lat', true );
        $lng = get_post_meta( $event->ID, '_event_place_lng', true );


        $return .= '<a href="' . get_permalink( $event->ID ) .'" class="events_wrapper" title="' . get_the_title( $event->ID ) . ' (' . $date . ')">';
        $return .= '<div class="event-card" ontouchstart="this.classList.toggle(\'hover\');">';
        $return .= '<div class="flipper">';
        $return .= '<div class="front">';
        $return .= '<div class="archive_events_thumbnail" style="background-image: url(' . $thumbnail . ');"></div>';
        $return .= '<h4>' . get_the_title( $event->ID ) . '</h4>';
        $return .= '<div class="archive_date archive_meta">' . $date . '</div>';
        if ($city) {
            $return .= '<div class="archive_city archive_meta">' . $city . '</div>';
        }
        $return .= '</div>';
        $return .= '<div class="back">';
        $return .= '<h4>' . get_the_title( $event->ID ) . '</h4>';
        $return .= '<div class="archive_events_back_meta">';

        $return .= '<div class="single_event_date">';
        $return .= '<div class="icon"></div>';
        $return .= $date;
        $return .= '</div>';

        if ($city) {
            $return .= '<div class="single_event_place">';
            $return .= '<div class="icon"></div>';
            $return .= $city;
            $return .= '</div>';
        }

        if ($map && $lat && $lng) {
            $return .= '<div class="single_event_gps">';
            $return .= '<div class="icon"></div>';
            $return .= 'N:'.round($lat, 5) .', E:'.round($lat, 5);
            $return .= '</div>';
        }

        if ( $organisator && $organisator != '') :
            $return .= '<div class="single_event_organisator">';
            $return .= '<div class="icon"></div>';
            $return .= $organisator;
            $return .= '</div>';
        endif;

        $return .= '</div>';
        $return .= '</div>';
        $return .= '</div>';
        $return .= '</div>';
        $return .= '</a>';

        return $return;
    }
}
$frontend = new ZazitHistoriiFrontend();