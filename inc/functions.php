<?php 
/*
Functions to add to plugin functionality
*/

/**
 * Get All orders IDs for a given product ID.
 *
 * @param  integer  $product_id (required)
 * @param  array    $order_status (optional) Default is 'wc-completed'
 *
 * @return array
 */

function convert_content() {
    $args = array(
        'posts_per_page'   => -1,
        'orderby'          => 'name',
        'order'            => 'DESC',
        'post_type'        => 'download',
        'suppress_filters' => true,
        'post_status' => array('publish', 'pending', 'draft', 'future')
    );
    $products = get_posts( $args );

    if($products) {

        foreach($products as $product) {
            $product_id = $product->ID;
            $product_post = array(
                'ID'           => $product->ID,
                'post_content' => html_entity_decode($product->post_content),
            );
            wp_update_post( $product_post );
        }
    }
    echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd" class="wcedd_button">Download HTML Updated</a> '; 
}
function create_edd_csv() {
    $products = wc_get_products( array(
        'limit' =>  999,
        'orderby' => 'name',
        'order' => 'DESC',
        'status' => 'publish',
    ) );
    

    $args = array(
        'posts_per_page'   => -1,
        'orderby'          => 'name',
        'order'            => 'DESC',
        'post_type'        => 'product',
        'suppress_filters' => true,
        'post_status' => array('publish', 'pending', 'draft', 'future')
    );
    $products = get_posts( $args );

    if($products) {
        
        $edd_product = array(
            'ID',
            'Slug',
            'Name',
            'Date Created',
            'Author',
            'Description',
            'Excerpt',
            'Status',
            'Categories',
            'Tags',
            'Price',
            'Files',
            'File Download Limit',
            'Featured Image',
            'SKU',
            'Notes',
            'Sales',
            'Earnings',
            'Gallery Images'
        );
        
        $edd_products[] = $edd_product;

        foreach($products as $product) {
            $product_id = $product->ID;
            $temp_product = wc_get_product( $product_id );

            //Categories
            $product_categories = wp_get_object_terms($product_id,'product_cat');
            $catories_list = '';
            if(!empty($product_categories)) {
                foreach($product_categories as $product_category) {
                    $term_name = $product_category->name;
                    if(!term_exists($term_name,'download_category')) {
                        wp_insert_term($term_name,'download_category');
                    }
                    $catories_list .= $term_name.' | ';
                }
                //remove those last spaces and pipes from string
                $catories_list = substr($catories_list, 0, -3);
            }

            //Tags
            $product_tags = wp_get_object_terms($product_id,'product_tag');
            $tags_list = '';
            if(!empty($product_tags)) {
                foreach($product_tags as $product_tag) {
                    $term_name = $product_tag->name;
                    if(!term_exists($term_name,'download_tag')) {
                        wp_insert_term($term_name,'download_tag');
                    }
                    $tags_list .= $term_name.' | ';
                }
                //remove those last spaces and pipes from string
                $tags_list = substr($tags_list, 0, -3);
            }
            
            //Downloads
            $item_downloads = $temp_product->get_downloads();
            $product_files = '';
            foreach ($item_downloads as $item_download){
                $product_files .= $item_download->get_file().' | ';
            }
            //remove those last spaces and pipes from string
            $product_files = substr($product_files, 0, -3);
            
            //Featured Image
            $product_image_url = wp_get_attachment_url( $temp_product->get_image_id() );
            if($product_image_url) {
                $edd_product_image_url = str_replace('youthmin.local:8890','youthmin.dream.press',$product_image_url); 
                $edd_product_image_url = str_replace('/wp-content/uploads/','/wp-content/uploads/edd/',$edd_product_image_url);
            } else {
                $edd_product_image_url = 'https://youthmin.dream.press/wp-content/uploads/edd/2020/03/woocommerce-placeholder.png';
            }
            
            //Sales and Earnings
            //We don't need to calculate this because it is updated during the payments import
            $sales = 0;
            $earnings = 0;

            $author = get_user_by('id',$product->post_author);
            
            //Gallery
            $attachment_ids = $temp_product->get_gallery_attachment_ids();
            $gallery_ids = '';
            if ( $attachment_ids ) {
                foreach ( $attachment_ids as $attachment_id ) {
                    $gallery_url = wp_get_attachment_url($attachment_id);
                    $gallery_url = str_replace('youthmin.local:8890','youthmin.dream.press', $gallery_url);
                    $gallery_url  = str_replace('/wp-content/uploads/','/wp-content/uploads/edd/',$gallery_url );
                    $gallery_ids .= $gallery_url .',';
                }
                //remove those last characters and slash from string
                $gallery_ids = substr($gallery_ids, 0, -1);
            }

            $edd_product = array(
                $product_id,
                $product->post_name,
                $product->post_title,
                $product->post_date,
                $author->user_login,
                htmlentities($product->post_content),
                htmlentities($product->post_excerpt),
                $product->post_status,
                $catories_list,
                $tags_list,
                $temp_product->get_price(),
                $product_files,
                $temp_product->get_download_limit(),
                $edd_product_image_url,
                $temp_product->get_sku(),
                $temp_product->get_purchase_note(),
                $sales,
                $earnings,
                $gallery_ids
            );

            $edd_products[] = $edd_product;
            
        }

        //Now write to CSV
        $products_file = plugin_dir_path( __FILE__ ) . 'wc-to-edd-products-export.csv';
        $products_file_handle = fopen($products_file, 'w') or die("<p>Whoops! Can't open file</p>");
        $count = -1; //-1 because of header
        foreach ($edd_products as $edd_product) {
            fputcsv($products_file_handle, $edd_product);
            $count++;
        }
        fclose($products_file_handle);

        echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd" class="wcedd_button">Successfully wrote '.$count.' products to file. Click to Prevent Processing Again</a> '; 
    }
}

function update_roles() {
    $args = array(
        'number' => -1,
        'role__not_in' => array('administrator','contributor','subscriber','customer'),
    );

    $customers = get_users($args);
    foreach($customers as $customer) {
        $user = new WP_User($customer->ID);
        //print_r($user);
        $role = getUserRole($customer->ID   );
        echo 'Role: '.$role.'<br>';
        //if($role == 'customer') {
		$user->set_role( 'shop_vendor' );
    }
} 
		

function getUserRole($id) {
	$user = new WP_User( $id );
	$user_id = $user->ID;
	$user_role = $user->roles;
	if(is_array($user_role)) {
		$user_role = array_shift($user_role);
	} else {
		$user_role = 'blank';
	}
	return $user_role;
}

function update_images() {
    //Use CSV to Import Images
    $products_file = plugin_dir_path( __FILE__ ) . 'wc-to-edd-products-export.csv';
    $row = 2; //skip header
    if (($handle = fopen($products_file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            //echo "<p> $num fields in line $row: <br /></p>\n";
            $row++;
            $slug = $data[1];
            if ( $post = get_page_by_path( $slug, OBJECT, 'download' ) ) {
                $post_id = $post->ID;
                /*$image = $data[13];
                $image_id = attachment_url_to_postid($image);
                
                if($image_id) {
                    if ( $post = get_page_by_path( $slug, OBJECT, 'download' ) ) {
                        $post_id = $post->ID;
                        set_post_thumbnail( $post_id, $image_id );
                    }
                }*/

                $gallery = $data[18];
                if($gallery != '') {
                    $gallery_images = explode(',',$gallery);
                    foreach($gallery_images as $image) {
                        $image_id = attachment_url_to_postid($image);
                        add_post_meta($post_id, 'vdw_gallery_id', $image_id);
                        
                    }
                }
                
            }

        }
        fclose($handle);
    }

    echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd" class="wcedd_button">Successfully updated images. Click to Prevent Processing Again</a> '; 
}

function delete_edd_products() {
    global $wpdb;
    
    $posts = $wpdb->prefix.'posts';
    $post_meta = $wpdb->prefix.'postmeta';
    $term_relationships = $wpdb->prefix.'term_relationships';
    
    $count = $wpdb->query("DELETE FROM $posts WHERE post_type='download'");
    $wpdb->query("DELETE FROM $post_meta WHERE post_id NOT IN (SELECT id FROM $posts)");
    $wpdb->query("DELETE FROM $term_relationships WHERE object_id NOT IN (SELECT id FROM $posts)");

    if($count > 0) {
        $deleted_text = 'Deleted '.$count.' Downloads!';
    }
    else {
        $deleted_text = 'No Downloads to delete!';
    }

    echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd" class="wcedd_button">'.$deleted_text.' Click to Prevent Deleting Again</a> '; 
}

function delete_payment_history() {
    global $wpdb;

    $posts = $wpdb->prefix.'posts';
    $post_meta = $wpdb->prefix.'postmeta';
    $term_relationships = $wpdb->prefix.'term_relationships';
    
    $count = $wpdb->query("DELETE FROM $posts WHERE post_type='edd_payment'");
    $wpdb->query("DELETE FROM $post_meta WHERE post_id NOT IN (SELECT id FROM $posts)");
    $wpdb->query("DELETE FROM $term_relationships WHERE object_id NOT IN (SELECT id FROM $posts)");

    if($count > 0) {
        $deleted_text = 'Deleted '.$count.' Payments!';
    }
    else {
        $deleted_text = 'No Payments to delete!';
    }

    echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd" class="wcedd_button">'.$deleted_text.' Click to Prevent Deleting Again</a> '; 
}

function delete_edd_customers() {
    global $wpdb;
    $table  = $wpdb->prefix . 'edd_customers';
    $delete = $wpdb->query("TRUNCATE TABLE $table");
    echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd" class="wcedd_button">Deleted Customers! Click to Prevent Deleting Again</a> '; 
}

function create_edd_customers() {
    global $wpdb;
    $table  = $wpdb->prefix . 'edd_customers';
    
    $args = array(
        'number' => -1
    );

    $customers = get_users($args);
    $count = 0;
    foreach($customers as $customer) {
        $customer_data = get_userdata( $customer->ID );
        $edd_customer = array(
            'user_id' => $customer->ID,
            'email' => $customer->user_email,
            'name' => $customer->display_name,
            'purchase_value' => 0,
            'purchase_count' => 0,
            'payment_ids' => '',
            'notes' => '',
            'date_created' => $customer_data->user_registered
        );
        $wpdb->insert($table, $edd_customer);
        $count++;
    }

    echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd" class="wcedd_button">Created '.$count.' customers. Click to Prevent Processing Again</a> '; 
    
}

function create_edd_orders() {
    global $wpdb;
    $table  = $wpdb->prefix . 'edd_customers';

    $payment_header = array(
        'Email',
        'Customer ID',
        'First Name',
        'Last Name',
        'Address',
        'Address (Line 2)',
        'City',
        'State',
        'Country',
        'Zip / Postal Code',
        'Products (Verbose)',
        'Products (Raw)',
        'Amount ($)',
        'Tax ($)',
        'Discount Code',
        'Payment Method',
        'Transaction ID',
        'Purchase Key',
        'Date',
        'User',
        'Currency',
        'IP Address',
        'Mode (Live|Test)',
        'Status',
        'Country Name',
    );

    $edd_payments[] = $payment_header;

    $orders = $wpdb->get_results("
        SELECT order_items.ID   
        FROM {$wpdb->prefix}posts as order_items
        WHERE post_type = 'shop_order'
    ");
    
    foreach($orders as $order_info) {
        $order_id = $order_info->ID;
        $order = wc_get_order( $order_id );
        if ( $order ) {
            $status = $order->get_status();
            if($status == 'completed') {
                $status = 'complete';
            }
            if($status != 'auto-draft') {

                $email = $order->get_billing_email();
                
                $user_id_query = $wpdb->get_col("
                    SELECT order_items.id
                    FROM {$wpdb->prefix}edd_customers as order_items
                    WHERE order_items.email = '$email'
                ");
                if(!empty($user_id_query)) {
                    $customer_id = $user_id_query[0];
                } else {
                    $customer_id = '';
                }
                
                $items = $order->get_items();
                $pretty_items = '';
                $raw_items = '';
                foreach($items as $item) {
                    //formatted for multiple items in one order
                    $pretty_items .= html_entity_decode($item['name']) .' - $'.$item['total'].' / ';
                    $raw_items .= html_entity_decode($item['name']).'|'.$item['total'].'{0} / ';
                }
                //remove those last characters and slash from string
                $pretty_items = substr($pretty_items, 0, -3);
                $raw_items = substr($raw_items, 0, -3);

                $edd_payment = array(
                    $email,
                    $customer_id,
                    $order->get_billing_first_name(),
                    $order->get_billing_last_name(),
                    $order->get_billing_address_1(),
                    $order->get_billing_address_2(),
                    $order->get_billing_city(),
                    $order->get_billing_state(),
                    $order->get_billing_country(),
                    $order->get_billing_postcode(),
                    $pretty_items,
                    $raw_items,
                    $order->get_total(),
                    $order->get_total_tax(),
                    'none',
                    $order->get_payment_method_title(),
                    $order->get_payment_method_title(),
                    str_replace('wc_order_','',$order->get_order_key()),
                    $order->get_date_created(),
                    $order->get_billing_first_name() .' '.$order->get_billing_last_name(),
                    '',
                    get_post_meta( $order_id, '_customer_ip_address', true ),
                    '',
                    $status,
                    $order->get_billing_country()
                );

                $edd_payments[] = $edd_payment;
            }
        }
    }    

    //Now write to CSV
    $payments_file = plugin_dir_path( __FILE__ ) . 'wc-to-edd-payments-export.csv';
    $payments_file_handle = fopen($payments_file, 'w') or die("<p>Whoops! Can't open file</p>");

    $write = '';
    $count = -1; //-1 because of header
    foreach ($edd_payments as $edd_payment) {
        fputcsv($payments_file_handle, $edd_payment);
        $count++;
    }

    fclose($payments_file_handle);

    echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=wc-to-edd" class="wcedd_button">Successfully wrote '.$count.' payments to file. Click to Prevent Processing Again</a>';
}
?>