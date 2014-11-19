<?php
/**
 * Sloth Booker
 *
 * @package     Sloth_Booker
 * @author      Nina Cecilie Højholdt
 * @copyright   2014 MICO
 * @link        MICO, http://www.mico.dk
 */



// Main class
class Sloth_Booker {


    // Plugin identifier
    protected $plugin_slug = 'sloth-booker';


    // Unique db identifier
    protected $plugin_db_prefix = 'sb';


    // Instance of this class
    protected static $instance = null;

    public $wp_bookings = null;

    // Constructor
    private function __construct() {

        //populate the $all_events variable with the events from wordpress, by running the populate_bookings() function
        add_action( 'wp_loaded', array($this, 'populate_bookings'));

        //populate the $all_rooms variable with the rooms from wordpress, by running the populate_rooms() function
        add_action( 'wp_loaded', array($this, 'populate_rooms'));

        // Load styles and scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_moment_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_booking_scripts'));

        // Register post type
        add_action('init', array($this, 'register_post_type'));

        // Booking post type: Add metabox to the post type
        add_action( 'add_meta_boxes', array( $this, 'add_booking_meta_box') );
        // Booking post type: Save booking metabox data
        add_action( 'save_post', array( $this, 'save_booking_meta_box_data') );


        
        // Settings page: Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_settings_menu' ) );
        // Settings page: Add an action link (with activate and delete) pointing to the settings page. NOTE: the hook must have this format: 'plugin_action_links_PLUGINFOLDER/PLUGINFILE.php'
        add_filter( 'plugin_action_links_'. $this->plugin_slug .'/'. $this->plugin_slug .'.php', array( $this, 'add_action_links' ) );
        // Settings page: Add and register the settings section and fields
        add_action('admin_init', array( $this, 'add_plugin_settings' ));

        
        // Load booking as JSON
        add_action( 'wp_ajax_bookings_json', array($this, 'bookings_json_callback') );
        // Insert booking via ajax 
        add_action( 'wp_ajax_insert_booking', array($this, 'insert_booking_callback') );
        // Delete booking via ajax
        add_action( 'wp_ajax_delete_booking', array($this, 'delete_booking_callback') );

        add_action('wp_ajax_get_current_user', array($this, 'get_current_user_callback'));

        add_action('wp_ajax_populate_bookings', array($this, 'populate_bookings'));




        add_action('wp_ajax_sloth_booker_ajax', array($this, 'sloth_booker_ajax'));
        //add_action('wp_ajax_nopriv_my_user_vote', array($this, 'my_must_login'));

        add_action('the_content', array($this, 'test_populate'));

        add_action('the_content', array($this, 'sloth_render'));
        

        
    }

    // Return plugin slug
    public function get_plugin_slug() {
        return $this->plugin_slug;
    }

    // Return instance of class
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( self::$instance == null ) {
            self::$instance = new self;
        }
        return self::$instance;
    }



    /*--------------------------------------------------------------
        REGISTER & ENQUEUE STYLES+SCRIPTS
    --------------------------------------------------------------*/

    // Register and enqueue styles
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_slug . '-plugin-style', plugins_url('assets/css/main.css', __FILE__) );
    }

    // Register and enqueue scripts
    public function enqueue_scripts() {
        //wp_enqueue_script($this->plugin_slug . '-plugin-script', plugins_url('assets/js/createCalendar.js', __FILE__), array('jquery'));

    }

    // Register and enqueue scripts related to moment.js
    public function enqueue_moment_scripts() {
        wp_enqueue_script('moment', plugins_url('assets/moment/moment.js', __FILE__), array('jquery'));
    }

    public function enqueue_booking_scripts() {
        wp_register_script( $this->plugin_slug . '-plugin-script-booking', plugins_url('assets/js/main.js', __FILE__), array('jquery') );
        //wp_register_script( $this->plugin_slug . '-plugin-script-calendar', plugins_url('assets/js/createCalendar.js', __FILE__), array('jquery') );


        wp_localize_script( $this->plugin_slug . '-plugin-script-booking', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( $this->plugin_slug . '-plugin-script-booking' );
        //wp_enqueue_script( $this->plugin_slug . '-plugin-script-calendar' );

        // Pass all the events as json (string) to the main script via the variable wp_bookings
        wp_localize_script( $this->plugin_slug . '-plugin-script-booking', 'wp_bookings', $this->all_events);

        // Pass all the rooms as json (string) to the main script via the variable wp_rooms
        wp_localize_script( $this->plugin_slug . '-plugin-script-booking', 'wp_rooms', $this->all_rooms);

    }

    
    /*--------------------------------------------------------------
        REGISTER POST TYPES
    --------------------------------------------------------------*/

    public function register_post_type() {
        $labels = array(
            'name'               => _x( 'Lokaler', 'post type general name', $this->plugin_slug ),
            'singular_name'      => _x( 'Lokale', 'post type singular name', $this->plugin_slug ),
            'menu_name'          => _x( 'Lokaler', 'admin menu', $this->plugin_slug ),
            'name_admin_bar'     => _x( 'Lokale', 'add new on admin bar', $this->plugin_slug ),
            'add_new'            => _x( 'Add New', 'lokale', $this->plugin_slug ),
            'add_new_item'       => __( 'Add New Lokale', $this->plugin_slug ),
            'new_item'           => __( 'New Lokale', $this->plugin_slug ),
            'edit_item'          => __( 'Edit Lokale', $this->plugin_slug ),
            'view_item'          => __( 'View Lokaler', $this->plugin_slug ),
            'all_items'          => __( 'All Lokaler', $this->plugin_slug ),
            'search_items'       => __( 'Search Lokaler', $this->plugin_slug ),
            'parent_item_colon'  => __( 'Parent Lokale:', $this->plugin_slug ),
            'not_found'          => __( 'No lokaler found.', $this->plugin_slug ),
            'not_found_in_trash' => __( 'No lokaler found in trash.', $this->plugin_slug )       
        );
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'exclude_from_search'=> true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => array( 'slug' => _x( 'lokale', 'URL slug', $this->plugin_slug ) ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
            //'menu_icon'          => 'dashicons-calendar'
        );

        register_post_type( 'lokale', $args );

        $labels = array(
            'name'               => _x( 'Bookings', 'post type general name', $this->plugin_slug ),
            'singular_name'      => _x( 'Booking', 'post type singular name', $this->plugin_slug ),
            'menu_name'          => _x( 'Bookings', 'admin menu', $this->plugin_slug ),
            'name_admin_bar'     => _x( 'Booking', 'add new on admin bar', $this->plugin_slug ),
            'add_new'            => _x( 'Add New', 'booking', $this->plugin_slug ),
            'add_new_item'       => __( 'Add New Booking', $this->plugin_slug ),
            'new_item'           => __( 'New Booking', $this->plugin_slug ),
            'edit_item'          => __( 'Edit Booking', $this->plugin_slug ),
            'view_item'          => __( 'View Booking', $this->plugin_slug ),
            'all_items'          => __( 'All Bookings', $this->plugin_slug ),
            'search_items'       => __( 'Search Bookings', $this->plugin_slug ),
            'parent_item_colon'  => __( 'Parent Booking:', $this->plugin_slug ),
            'not_found'          => __( 'No bookings found.', $this->plugin_slug ),
            'not_found_in_trash' => __( 'No bookings found in trash.', $this->plugin_slug )       
        );
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'exclude_from_search'=> true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => array( 'slug' => _x( 'booking', 'URL slug', $this->plugin_slug ) ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
            //'menu_icon'          => 'dashicons-calendar'
        );

        register_post_type( 'booking', $args );
    }



    /*--------------------------------------------------------------
        META BOXES
    --------------------------------------------------------------*/

    /**
     * Add meta box to the booking post type
     *
     */
    public function add_booking_meta_box() {
        add_meta_box(
            //$id, HTML id-attribute
            'booking',
            //$title
            __( 'Booking info', $this->plugin_slug ),
            //$callback
            array( $this, 'display_booking_meta_box' ),
            //$post_type
            'booking',
            //$context
            'normal',
            //$priority
            'default',
            //$callback_args, adds additional arsg to $post, which is passed by default.
            null
        );
    }

    /**
     * Render the booking meta box.
     *
     * @param       int     $post_id    The object for the current post/page.
     */
    public function display_booking_meta_box($post) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( $this->plugin_db_prefix . '_booking_meta_box', $this->plugin_db_prefix . '_booking_meta_box_nonce' );
        include_once( 'views/meta-box-bookingform.php' );
    }

    function save_booking_meta_box_data($post_id) {

        // Checks save status
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $_POST[ $this->plugin_db_prefix . '_booking_meta_box_nonce' ] ) && wp_verify_nonce( $_POST[$this->plugin_db_prefix. '_booking_meta_box_nonce'], $this->plugin_db_prefix . '_booking_meta_box' ) ) ? 'true' : 'false';
        $is_correct_permission = current_user_can( 'edit_posts', $post_id ) ? 'true' : 'false';
    
        // Exits script depending on save status
        if ( $is_autosave || $is_revision || !$is_valid_nonce || !$is_correct_permission ) {

            return;
        }

        // Safe to save (lol) now

        // Date
        if ( isset( $_POST[ $this->plugin_db_prefix . '_date'] ) ) {
            $date = $_POST[ $this->plugin_db_prefix . '_date'];
        }

        // Time
        if ( isset( $_POST[ $this->plugin_db_prefix . '_start_time'] ) ) {
            $start_time = $_POST[$this->plugin_db_prefix . '_start_time'];
        }

        // Room
        if ( isset( $_POST[ $this->plugin_db_prefix . '_room'] ) ) {
            $room = $_POST[$this->plugin_db_prefix . '_room'];
            $room = strtolower($room);
        }

        // user
        if ( isset( $_POST[ $this->plugin_db_prefix . '_user'] ) ) {
            $user = $_POST[$this->plugin_db_prefix . '_user'];
        }

        if(isset($date) && isset($start_time)) :

            $args = array(
                'post_type' => 'booking',
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => $this->plugin_db_prefix . '_date',
                        'value' => $date,
                        'compare' => '==',
                    ),
                    array(
                        'key' => $this->plugin_db_prefix . '_start_time',
                        'value' => $start_time,
                        'compare' => '==',
                    ),
                ),
                
            );

            $query = new WP_Query($args);

            if($query->have_posts() ) :

                while($query->have_posts()) : $query->the_post();

                    $otherUser = get_post_meta(get_the_ID(), $this->plugin_db_prefix . '_user', true);
                    $otherRoom = get_post_meta(get_the_ID(), $this->plugin_db_prefix . '_room', true);

                    if($otherUser == $user) :

                        wp_mail('nh@mico.dk', 'sloth', 'users are the same!');
                        return;

                    elseif($otherRoom == $room) :

                        wp_mail('nh@mico.dk', 'sloth', 'rooms are the same');
                        return;

                    endif;

                endwhile;

                wp_reset_postdata();

            else :

                wp_mail('nh@mico.dk', 'sloth', 'all good!');

                // If nothing matches
                
                if ($date) {
                    update_post_meta( $post_id, $this->plugin_db_prefix . '_date', $date);
                }

                
                if ($start_time) {
                    update_post_meta( $post_id, $this->plugin_db_prefix . '_start_time', $start_time);
                }

            
                if ($room) {
                    update_post_meta( $post_id, $this->plugin_db_prefix . '_room', $room);
                }

            
                if ($user) {
                    update_post_meta( $post_id, $this->plugin_db_prefix . '_user', $user);
                }

            endif;


        endif;
    }



    // SE http://wordpress.stackexchange.com/questions/42013/prevent-post-from-being-published-if-custom-fields-not-filled



    /*--------------------------------------------------------------
        SETTINGS MENU
    --------------------------------------------------------------*/


    /**
     * Register the administration menus for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_settings_menu() {
        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         */
        $this->plugin_screen_hook_suffix = add_options_page( 
            //$page_title
            __('Sloth Booker Settings', $this->plugin_slug),
            //$menu_title
            __('Sloth Booker', $this->plugin_slug),
            //$capability
            'manage_options',
            //$menu_slug
            $this->plugin_slug. '-settings',
            //$callback
            array( $this, 'display_plugin_admin_page' )
        );
        
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once( 'views/settings.php' );
    }


    /**
     * Add settings action link to the plugins page.
     *
     * @since       1.0.0
     * @param       array   $links      an array of links to desplay on the plugin page
     */
    public function add_action_links( $links ) {

        return array_merge(
            array(
                'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '-settings' . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
            ),
            $links
        );

    }

    /**
     * Add the plugin settings sections and fields. 
     * NOTE: as long as we only have one section, we dont need a title and description for the section.
     *
     * @since  1.0.0
     */
    
    public function add_plugin_settings() {
        
        // First, we register a section. This is necessary since all future options must belong to one.
        add_settings_section(
            // ID used to identify this section and with which to register options
            $this->plugin_slug . '-settings',
            // Title to be displayed on the administration page. we dont need this right now
            null,
            // Callback used to render the description of the section. we dont need this right now
            null,
            // Page on which to add this section of options
            $this->plugin_slug . '-settings'
        );

        // Force year in get_date_range()
        add_settings_field( 
            // ID used to identify the field throughout the plugin
            $this->plugin_db_prefix . '_example_settings',
            // The label to the left of the option interface element
            __('Example', 'sloth_booker'),
            // The name of the function responsible for rendering the option interface
            array($this, 'display_example_settings'),
            // The page on which this option will be displayed
            $this->plugin_slug . '-settings',
            // The name of the section to which this field belongs
            $this->plugin_slug . '-settings',
            // The array of arguments to pass to the callback. In this case, just a description.
            array('')
        );

        register_setting(
            //group name. security. Must match the settingsfield() on form page
            $this->plugin_db_prefix . 'sloth_booker',
            //name of field
            $this->plugin_db_prefix . '_example_settings'
        );

    }

    /**
     * Render the example_settings field
     *
     * @since    1.0.0
     * @param    $args      Optional arguments passed by the add_settings_field function.
     */
    public function display_example_settings($args) {
        include_once( 'views/example_settings.php' );
    }



    function sloth_render() {
        include 'slothbooker-render.php';
    }




    /*--------------------------------------------------------------
        JSON 
    --------------------------------------------------------------*/


    // Load all bookings as JSON

    public function booking_json_callback() {

        // Setup WP query to get all bookings
        $args = array(
            'post_type' => 'booking',
            'post_status' => 'any',
            'posts_per_page' => -1
        );

        $query = new WP_Query($args);

        // Empty array for the bookings
        $bookings_json = array();

        // Populate array
        if ($query->have_posts() ) :

            while ($query->have_posts() ) :

                $query->the_post();

                $bookings_json[] = array(
                    'id' => get_the_id(),
                    'date' => get_sb_date(),
                    'start_time' => get_sb_start_time(),
                    'room' => get_sb_room(),
                    'user' => get_sb_user(),
                    'confirmation_status' => get_sb_confirmation_status(),

                );

            endwhile;

        endif;

        // Echo the JSON-formatted array to js.
        echo json_encode($bookings_json);


        die();
    }


    // Retrieve all bookings as json

    public function get_bookings_json() {
        
        $args = array(
            'post_type' => 'booking',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );

        $query = new WP_Query($args);

        // Empty array for the bookings
        $bookings_json = array();

        // Populate array
        if ($query->have_posts() ) :

            while ($query->have_posts() ) :

                $query->the_post();

                $bookings_json[] = array(
                    'id' => get_the_id(),
                    'date' => get_sb_date(),
                    'start_time' => get_sb_start_time(),
                    'room' => get_sb_room(),
                    'user' => get_sb_user(),
                    'confirmation_status' => get_sb_confirmation_status(),
                    //'className' => array('sb-booking', 'booking-' . get_the_id() ),
                );

            endwhile;

        endif;

        //var_dump(json_encode($bookings_json));

        // return the JSON-formatted array to js.
        return json_encode($bookings_json);

        die();

    }

    /**
     * Insert a new booking via ajax
     *
     */
    public function insert_booking_callback() {

        $new_booking_data = $_POST['booking_data'];

        $id = isset($_POST['booking_data']['booking_id']) ? $_POST['booking_data']['booking_id'] : '';

        // Set booking date
        if ( isset( $_POST['booking_data']['booking_date'] ) ) {
            $booking_date = $_POST['booking_data']['booking_date'];
        } else {
            $booking_date = '';
        }

        // Set booking start time
        if ( isset( $_POST['booking_data']['booking_start_time'] ) ) {
            $booking_start_time = $_POST['booking_data']['booking_start_time'];
        } else {
            $booking_start_time = '';
        }

        // Seet booking room
        if ( isset( $_POST['booking_data']['booking_room'] ) ) {
            $booking_room = $_POST['booking_data']['booking_room'];
        } else {
            $booking_room = '';
        }

        // Seet booking user
        if ( isset( $_POST['booking_data']['booking_user'] ) ) {
            $booking_room = $_POST['booking_data']['booking_user'];
        } else {
            $user = wp_get_current_user();
            $booking_user = $user->ID;
        }
        
        $post = array(
            'post_ID' => $id,
            'post_status' => 'publish',
            'post_type' => 'booking',
            );

        $new_booking_id = wp_insert_post($post, true);

        update_post_meta($new_booking_id, $this->plugin_db_prefix . '_date', $booking_date);
        update_post_meta($new_booking_id, $this->plugin_db_prefix . '_start_time', $booking_start_time);
        update_post_meta($new_booking_id, $this->plugin_db_prefix . '_room', $booking_room);
        update_post_meta($new_booking_id, $this->plugin_db_prefix . '_user', $booking_user);

        update_post_meta($new_booking_id, $this->plugin_db_prefix . '_booked', true);

        global $post;

        $post = get_post($new_booking_id);

        //always die on ajax events.
        die();
    }


    /**
     * Delete a single booking based on a passed id
     *
     * 
     */
    public function delete_booking_callback() {

        wp_delete_post( $_POST['booking_id'] );
        
        //always die on ajax events.
        die();
    }

    /**
     * Get the current user name
     *
     * 
     */
    public function get_current_user_callback() {

        $current_user_obj = wp_get_current_user();

        echo $current_user = $current_user_obj->user_login;
        
        //always die on ajax events.
        die();
    }


    /**
     * Populate our variable with all the events, 
     * 
     * This allows us to pass them to js with localize_script in a later action.
     *
     * @since  1.0.0
     */
    public function populate_bookings() {
        $this->all_events = $this->get_bookings_json();

        $wp_bookings = $this->all_events;


    }

    public function get_rooms() {
        $args = array(
            'post_type' => 'lokale',
            'post_status' => 'publish',
        );

        $rooms = get_posts($args);

        return json_encode($rooms);
    }

    public function populate_rooms() {
        $this->all_rooms = $this->get_rooms();
    }

    public function test_populate() {
        $this->all_events = $this->get_bookings_json();
        //echo $this->all_events;
    }


}
