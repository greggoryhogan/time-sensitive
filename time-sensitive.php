<?php 
/*
Plugin Name: Time Sensitive
Description: Create expiring content with shortcodes
Author: Greggory Hogan 
Version: 1.0
Author URI: https://mynameisgregg.com
*/

function register_time_sensitive_scripts() {
	if( !is_admin() ) {
        
        wp_register_style( 'time-sensitive-css', plugins_url().'/time-sensitive/css/time-sensitive.css' );
        wp_enqueue_style( 'time-sensitive-css' );

        wp_enqueue_script( 'timer-js', plugins_url().'/time-sensitive/js/timer.js', array('jquery'),null,true );
        
    }
}
add_action( 'init', 'register_time_sensitive_scripts' );

/* Plugin Functions */
include( plugin_dir_path( __FILE__ ) . 'inc/functions.php');
?>