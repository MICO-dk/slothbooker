<?php
/**
 *
 * @package     Sloth_Booker
 * @author      Nina Cecilie Højholdt
 * @copyright   2014 MICO
 * @link        MICO, http://www.mico.dk
 *
 * @wordpress-plugin
 * Plugin Name:     Sloth Booker
 * Description:     Time-slot based booking system
 * Version:         1.0.0
 * Author:          Nina Cecilie Højholdt
 * Author URI:      http://www.fatpandaclub.com
 * Text Domain:     sloth-booker
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


// Load plugin class
require_once(plugin_dir_path(__FILE__) . 'class-sloth-booker.php');


// Run instance of the plugin main class

add_action('plugins_loaded', array('Sloth_Booker', 'get_instance'));



/**
 * Retrieve the date of an sb_booking
 *
 *
 * @return string Date for the event
 */

function get_sb_date($d = '', $post = NULL) {
    
    $post = get_post( $post );

    //check if the post exists
    if($post === NULL) {
        return;
    }

    $date = get_post_meta( $post->ID, 'sb_date', true );

    //get_post_meta returns empty string, if theres nothing to return
    if($date == '') {
        return;
    }

    if ( $d == '' ) {
        $the_date = mysql2date( get_option( 'date_format' ), $date );
    } else {
        $the_date = mysql2date( $d, $date );
    }
    return $date;
}

 
function get_sb_start_time($d = '', $post = NULL) {
    $post = get_post( $post );

    //check if the post exists
    if($post === NULL) {
        return;
    }

    $start_time = get_post_meta( $post->ID, 'sb_start_time', true );

    //get_post_meta returns empty string, if theres nothing to return
    if($start_time == '') {
        return;
    }

    return $start_time;
}

function get_sb_room($d = '', $post = NULL) {
    $post = get_post( $post );

    //check if the post exists
    if($post === NULL) {
        return;
    }

    $room = get_post_meta( $post->ID, 'sb_room', true );

    //get_post_meta returns empty string, if theres nothing to return
    if($room == '') {
        return;
    }

    return $room;
}

function get_sb_user($d = '', $post = NULL) {
    
    $post = get_post( $post );

    //check if the post exists
    if($post === NULL) {
        return;
    }

    $user = get_post_meta( $post->ID, 'sb_user', true );

    //get_post_meta returns empty string, if theres nothing to return
    if($user == '') {
        return;
    }

    return $user;
}

function get_sb_confirmation_status($d = '', $post = NULL) {
    return "confirmation status";
}


/** 
* Get an array of rooms
*
*/
function get_rooms() {

    $room_list;

    $args = array(
        'post_type' => 'lokale',
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);


    if($query->have_posts() ) :

        while ($query->have_posts() ) : $query->the_post(); 

            $room_list[] = get_the_title();

        endwhile;

    endif;

    return $room_list;
}



/*
 * Get the related id. 
 *
 * @return int the id of the related post. this is the post that the event is attached to. If it doesnt exist, it returns its own ID.
 
function get_related_id($post = NULL) {
    $post = get_post( $post );
    //check if the post exists
    if($post === NULL) {
        return;
    }

    $related_id = get_post_meta( $post->ID, 'sb_related_post_id', true );

    //check if the related id exists
    $related_post = get_post($related_id);

    if($related_post && $related_id != '') {
        return $related_id;
    } else {
        return get_the_id();
    }
}

*/



//add_filter('the_content', 'go_sloth');

/*
function createDay() {

    $time = "00";
    $room = "test"; 

    ?>

    <div class="room" id="<?php echo $room?>"> 

    <?php

    // Create container for room time slots
    //$(".timeslot-container").append("<div class='room' id=" + room + "></div>");
    

    // Room title ?>
    <h2><?php echo $room?></h2>

    <?php

    // Create room time slots
    for ($i = 0; $i < 24; $i++) : ?>
        <div class="time-slot" id="<?php echo $time; ?>"></div>
        <?php
        $time++;
        if($time < 10) :
            $time = "0"+$time;
        endif; 

    endfor; ?>
    

    </div>
<?php }



function createCalendar() {
    echo 'hej';
}
*/
?>