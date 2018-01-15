<?php
class ZazitHistoriiFrontend extends ZazitHistorii
{
    private $defaultEventColor = 'black';
    private $defaultEventBGColor = 'white';
    private $countEventsForAd = 24;
    private $googleMapKey = 'AIzaSyCYT9mAEwA8nisjkytdVrX8K0JXJGjf9yQ';

    public function __construct()
    {
        parent::__construct();
        wp_enqueue_media();
        wp_enqueue_script(THEME . '_script', get_template_directory_uri() . '/assets/scripts/scripts.js', array('jquery'), '20160816', true);
        wp_enqueue_script(THEME . '_maps', 'http://maps.google.com/maps/api/js?sensor=false&libraries=places&key='.$this->googleMapKey, array('jquery'), '20160816', false);
        wp_enqueue_script(THEME . '_maps_cluster', get_template_directory_uri() . '/assets/scripts/markerclusterer.js', array('jquery'), '20160816', false);
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script(THEME . '_dropzone', get_template_directory_uri() . '/assets/scripts/dropzone.js', array('jquery'), '20160816', false);


        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

        wp_enqueue_script(THEME . '_maps_picker', get_template_directory_uri() . '/assets/scripts/jquery-locationpicker-plugin/src/locationpicker.jquery.js', array('jquery'), '20160816', false);
        wp_enqueue_style('main_styles', get_template_directory_uri() . '/style.css', array(), '1.0.0');



        /* Import templates */
        add_filter('single_template', array($this, 'import_single_template'));
        add_filter('archive_template', array($this, 'import_archive_template'));
        /* Import templates */

        add_action('pre_get_posts', array($this, 'parse_request'), 10, 2);

        add_filter('pre_get_posts', array($this, 'custom_front_page'));

        add_action('wp_ajax_map_data', array($this, 'map_data'));
        add_action('wp_ajax_nopriv_map_data', array($this, 'map_data'));

        add_action('wp_ajax_upload_img', array($this, 'upload_img'));
        add_action('wp_ajax_delete_attachment', array($this, 'delete_attachment'));

        add_action('wp_ajax_ad_click', array($this, 'ad_click'));
        add_action('wp_ajax_nopriv_ad_click', array($this, 'ad_click'));

        add_action('wp_head', array( $this, 'define_constants_for_scripts' ) );

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
        }
        return $single_template;
    }

    public function import_archive_template($archive_template)
    {
        global $post;

        if ($post->post_type == '_events' && is_post_type_archive('_events')) {
            $archive_template = dirname(__FILE__) . '/../templates/events/archive.php';
        }
        return $archive_template;
    }

    public function get_all_events_from_date_to_date($date1, $date2 = 0, $ages = [], $search = '', $limit = -1)
    {
        $args['post_type'] = '_events';
        $args['posts_per_page'] = $limit;
        $args['order'] = 'ASC';
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_event_date_from';
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
                array(
                    'taxonomy' => '_ages',
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

    public function show_navigation_from_terms($terms, $current_item_slug = '')
    {
        $return = '<ul class="term-navigation">';
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
     
    <input type="submit" name="submit" value="Register"/>
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
                '',
                50
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
                'footer-menu' => __( 'Footer Menu' ),
            )
        );
    }
}
$frontend = new ZazitHistoriiFrontend();