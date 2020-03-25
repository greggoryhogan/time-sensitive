<?php 
/*
Functions related to overall plugin functionality
*/
function wc_edd_admin_pages() {
    add_menu_page('WC to EDD','WC to EDD', 'manage_options','wc-to-edd','wc_edd_options_page','dashicons-admin-generic',5);
}
add_action( 'admin_menu', 'wc_edd_admin_pages' );    
    
function wc_edd_options_page() { ?>
    <div class="wrap">
        <?php screen_icon(); ?>
        <h1>WooCommerce to Easy Digital Downloads</h1>

        <h2>Delete Existing EDD Data</h2>
        <p>Delete existing EDD products and users so you can import fresh data. These actions cannot be undone.</p>
        <?php 
        if(isset($_GET['delete_edd_products'])) {
            delete_edd_products();
        } else {
            echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd&delete_edd_products=1" class="wcedd_button">Delete Products</a> '; 
        } ?>
        <?php 
        if(isset($_GET['delete_edd_customers'])) {
            delete_edd_customers();
        } else {
            echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd&delete_edd_customers=1" class="wcedd_button">Delete Customers</a>'; 
        } ?>
        <?php 
        if(isset($_GET['delete_edd_payment_history'])) {
            delete_payment_history();
        } else {
            echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd&delete_edd_payment_history=1" class="wcedd_button">Delete Payment History</a>'; 
        } ?>

        <div class="wcedd-spacer"></div>

        <h2>Map WooCommerce Data to EDD</h2>
        <p>Create csv files ready to import into EDD using product data from Woocommmerce and copy all WordPress users into EDD Customer Database.</p>
        <?php 
        $max = ini_get('max_execution_time'); 
        if($max < 600) {
            echo '<p>Your server is set to have a max execution time of '.$max.' seconds. Depending on the number of WooCommerce orders you have, you may need to increase this time to more than 600.</p>';
        } ?>
        <?php 
        $products_file = get_bloginfo('url').'/wp-content/plugins/wc-to-edd/inc/wc-to-edd-products-export.csv';
        $payments_file = get_bloginfo('url').'/wp-content/plugins/wc-to-edd/inc/wc-to-edd-payments-export.csv'; 
        ?>
      
        <?php 
        if(isset($_GET['create_csv'])) {
            create_edd_csv();
        } else {
            echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd&create_csv=1" class="wcedd_button">1. Create CSV of Products</a> ';
        }
        if(isset($_GET['create_customers'])) {
            create_edd_customers();
        } else {
            echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd&create_customers=1" class="wcedd_button">2. Create Customers</a> ';
        }
        if(isset($_GET['create_orders'])) {
            create_edd_orders();
        } else {
            echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd&create_orders=1" class="wcedd_button">3. Create CSV of Payment History</a>';
        }
        ?>

        <p>Looking for your .csv files?<br>Products file: <?php echo '<a href="'.$products_file.'" target="_blank">'.$products_file.'</a>'; ?><br>Payments file: <?php echo '<a href="'.$payments_file.'" target="_blank">'.$payments_file.'</a>'; ?></p>
        
    </div>
<?php
}
?>