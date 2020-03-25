<?php 
/*
Plugin Name: WooCommerce to EDD CSV Generator
Description: This plugin provides connversion functionality from WooCommerce to Easy Digital Downloads
Author: Greggory Hogan 
Version: 0.1
Author URI: https://mynameisgregg.com
*/

/* Scripts and Stylesheets */
function eos_admin_css() {
    wp_register_style( 'wc-edd-admin-css', plugins_url().'/wc-to-edd/css/admin.css' );
    wp_enqueue_style( 'wc-edd-admin-css' );
    //wp_enqueue_script( 'eos-scripts', plugins_url() . '/eos-mentor/js/app.js', array('jquery'), null ,true ); 
    //wp_localize_script('eos-scripts', 'settings', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('admin_init', 'eos_admin_css');

/* Plugin Settings */
include( plugin_dir_path( __FILE__ ) . 'inc/plugin_settings.php');

/* Plugin Settings */
include( plugin_dir_path( __FILE__ ) . 'inc/functions.php');

/* AJAX Functions */
include( plugin_dir_path( __FILE__ ) . 'inc/ajax.php');
?>