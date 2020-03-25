<?php 
/*
Plugin Name: WooCommerce to EDD CSV Generator
Description: This plugin provides connversion functionality from WooCommerce to Easy Digital Downloads
Author: Greggory Hogan 
Version: 1.0
Author URI: https://mynameisgregg.com
*/

/* Scripts and Stylesheets */
function eos_admin_css() {
    wp_register_style( 'wc-edd-admin-css', plugins_url().'/wc-to-edd/css/admin.css' );
    wp_enqueue_style( 'wc-edd-admin-css' );
}
add_action('admin_init', 'eos_admin_css');

/* Plugin Settings */
include( plugin_dir_path( __FILE__ ) . 'inc/plugin_settings.php');

/* Plugin Functions */
include( plugin_dir_path( __FILE__ ) . 'inc/functions.php');
?>