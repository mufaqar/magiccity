<?php
function my_theme_enqueue_styles() { 
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

// Disable Divi Projects
// 
add_action( 'init', 'cliff_remove_divi_project_post_type' );

if ( ! function_exists( 'cliff_remove_divi_project_post_type' ) ) {
 /**
 * Disable Divi's Project post type.
 *
 * Alternative option for post type (but not taxonomies): Use the 'et_builder_default_post_types' filter.
 * wp-content/themes/Divi/includes/builder/core.php
 *
 * @link https://gist.github.com/cliffordp/718ec5fede29da940b5a2daaeb563817
 */
 function cliff_remove_divi_project_post_type(){
 unregister_post_type( 'project' );
 unregister_taxonomy( 'project_category' );
 unregister_taxonomy( 'project_tag' );
 }
}

// End divi projects code


// Shipping Mehtod Based Order Status

add_action( 'woocommerce_checkout_update_order_meta', 'change_order_status_based_on_shipping_method', 10, 2 );
function change_order_status_based_on_shipping_method( $order_id, $data ) {
  $order = wc_get_order( $order_id );
  $shipping_method = $order->get_shipping_method();
  
  if( $shipping_method  == 'Free Store Pickup (M-F 10AM-4:30PM | Sat 11AM-4:30PM | Closed Sunday)' ) { 
    $order->update_status( 'custom-status' );
}
if( $shipping_method  == 'MCO Delivery (Arrives 2PM-7PM Same-Day If Ordered Before 2PM)' ) { 
    $order->update_status( 'delivery-unfulfil' );
}

}

// Pickup Order Status Change

add_action( 'woocommerce_order_status_changed', 'order_status_change_pickup_to_complted', 9, 3 );
function order_status_change_pickup_to_complted( $order_id, $old_status, $new_status ) {
    $order = wc_get_order( $order_id );
    $old_status = $order->get_status();
    if($old_status == 'pickup'){
        $order->update_status( 'wc-completed' );
        do_action( 'woocommerce_order_status_changed', $order_id, $old_status, $new_status , $order);
    }
    elseif ($old_status == 'ready-delivery') {

       $driver_data =  get_post_meta($order_id, 'lddfw_driverid', true); 

          if($driver_data != '')
          {

              $order->update_status( 'wc-driver-assigned' );
              do_action( 'woocommerce_order_status_changed', $order_id, $old_status, $new_status , $order);
          }

    }

}



// updated order if status is ready for deliver and driver assigned
function update_order_status_from_admin( $order_id ) {
  $order = wc_get_order( $order_id ); 
  $driver_data =  get_post_meta($order_id, 'lddfw_driverid', true); 

      if($driver_data != '')
      {
      // Get the order object
      $order = wc_get_order( $order_id );

      // Get the current order status
      $old_status = $order->get_status();

      $order = wc_get_order( $order_id );

      // Update the order status
      $order->update_status( 'driver-assigned' );

      // Get the new order status
      $new_status = $order->get_status();

      // Trigger the woocommerce_order_status_changed action
      do_action( 'woocommerce_order_status_changed', $order_id, $old_status, $new_status , $order );
      }

}
add_action( 'woocommerce_process_shop_order_meta', 'update_order_status_from_admin', 10, 1 );



/* Display Add to cart button on archives */ 

add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 10);

//******************************** UPDATE ORDER STATUS BY SHIPPING METHOD ***********************************

add_filter( 'woocommerce_register_shop_order_post_statuses', 'register_unfulfilled_custom_order_status' );
 
function register_unfulfilled_custom_order_status( $order_statuses ){
    
   // Status must start with "wc-"
   $order_statuses['wc-custom-status'] = array(                                 
   'label'                     => 'Pickup Unfulfilled',
   'public'                    => false,                                 
   'exclude_from_search'       => false,                                 
   'show_in_admin_all_list'    => true,                                 
   'show_in_admin_status_list' => true,                                 
   'label_count'               => _n_noop( 'Pickup Unfulfilled <span class="count">(%s)</span>', 'Pickup Unfulfilled <span class="count">(%s)</span>', 'woocommerce' ),
   ); 
	
	$order_statuses['wc-shipping-unfulfil'] = array(                                 
   'label'                     => 'Shipping Unfulfilled',
   'public'                    => false,                                 
   'exclude_from_search'       => false,                                 
   'show_in_admin_all_list'    => true,                                 
   'show_in_admin_status_list' => true,                                 
   'label_count'               => _n_noop( 'Shipping Unfulfilled <span class="count">(%s)</span>', 'Shipping Unfulfilled <span class="count">(%s)</span>', 'woocommerce' ),         
   );  
	
	$order_statuses['wc-delivery-unfulfil'] = array(                                 
   'label'                     => 'Delivery Unfulfilled',
   'public'                    => false,                                 
   'exclude_from_search'       => false,                                 
   'show_in_admin_all_list'    => true,                                 
   'show_in_admin_status_list' => true,                                 
   'label_count'               => _n_noop( 'Delivery Unfulfilled <span class="count">(%s)</span>', 'Delivery Unfulfilled <span class="count">(%s)</span>', 'woocommerce' ),         
   );  
	$order_statuses['wc-ready-delivery'] = array(                                 
   'label'                     => 'Ready for Delivery',
   'public'                    => false,                                 
   'exclude_from_search'       => false,                                 
   'show_in_admin_all_list'    => true,                                 
   'show_in_admin_status_list' => true,                                 
   'label_count'               => _n_noop( 'Ready for Delivery <span class="count">(%s)</span>', 'Ready for Delivery <span class="count">(%s)</span>', 'woocommerce' ),         
   );  
	$order_statuses['wc-ready-ship'] = array(                                 
   'label'                     => 'Ready to Ship',
   'public'                    => false,                                 
   'exclude_from_search'       => false,                                 
   'show_in_admin_all_list'    => true,                                 
   'show_in_admin_status_list' => true,                                 
   'label_count'               => _n_noop( 'Ready to Ship <span class="count">(%s)</span>', 'Ready to Ship <span class="count">(%s)</span>', 'woocommerce' ),         
   );  
	$order_statuses['wc-driver-assigned'] = array(                                 
   'label'                     => 'Driver Assigned',
   'public'                    => false,                                 
   'exclude_from_search'       => false,                                 
   'show_in_admin_all_list'    => true,                                 
   'show_in_admin_status_list' => true,                                 
   'label_count'               => _n_noop( 'Driver Assigned <span class="count">(%s)</span>', 'Driver Assigned <span class="count">(%s)</span>', 'woocommerce' ),         
   ); 
   
   $order_statuses['wc-pickup'] = array(                                 
    'label'                     => 'Pickup',
    'public'                    => false,                                 
    'exclude_from_search'       => false,                                 
    'show_in_admin_all_list'    => true,                                 
    'show_in_admin_status_list' => true,                                 
    'label_count'               => _n_noop( 'Pickup <span class="count">(%s)</span>', 'Pickup <span class="count">(%s)</span>', 'woocommerce' ),         
    ); 

    $order_statuses['wc-pickup'] = array(                                 
      'label'                     => 'Picked Up',
      'public'                    => false,                                 
      'exclude_from_search'       => false,                                 
      'show_in_admin_all_list'    => true,                                 
      'show_in_admin_status_list' => true,                                 
      'label_count'               => _n_noop( 'Picked Up <span class="count">(%s)</span>', 'Picked Up <span class="count">(%s)</span>', 'woocommerce' ),         
      ); 

      $order_statuses['wc-ready-pickup'] = array(                                 
        'label'                     => 'Ready for Pickup',
        'public'                    => false,                                 
        'exclude_from_search'       => false,                                 
        'show_in_admin_all_list'    => true,                                 
        'show_in_admin_status_list' => true,                                 
        'label_count'               => _n_noop( 'Ready for Pickup <span class="count">(%s)</span>', 'Ready for Pickup <span class="count">(%s)</span>', 'woocommerce' ),         
        ); 
     
  
    
   
   return $order_statuses;
}



 

// // 2. Show Order Status in the Dropdown @ Single Order and "Bulk Actions" @ Orders
 
add_filter( 'wc_order_statuses', 'show_unfulfilled_custom_order_status' );
 
function show_unfulfilled_custom_order_status( $order_statuses ) {      
  $order_statuses['wc-custom-status'] = 'Pickup Unfulfilled';
	$order_statuses['wc-shipping-unfulfil'] = 'Shipping Unfulfilled';
	$order_statuses['wc-delivery-unfulfil'] = 'Delivery Unfulfilled';
	$order_statuses['wc-ready-delivery'] = 'Ready for Delivery';
	$order_statuses['wc-ready-ship'] = 'Ready to Ship'; 
	$order_statuses['wc-driver-assigned'] = 'Driver Assigned';
  $order_statuses['wc-ready-pickup'] = 'Ready for Pickup';
  $order_statuses['wc-pickup'] = 'Picked Up';

    return $order_statuses;
}
 
add_filter( 'bulk_actions-edit-shop_order', 'get_unfulfilled_custom_order_status_bulk' );
 
function get_unfulfilled_custom_order_status_bulk( $bulk_actions ) {
   // Note: "mark_" must be there instead of "wc"
   
  $bulk_actions['mark_custom-status'] = 'Change status to pickup unfulfilled';
	$bulk_actions['mark_shipping-unfulfil'] = 'Change status to shipping unfulfilled ';
	$bulk_actions['mark_delivery-unfulfil'] = 'Change status to delivery unfulfilled';
	$bulk_actions['mark_ready-delivery'] = 'Change status to ready for delivery';
	$bulk_actions['mark_ready-ship'] = 'Change status to ready to ship';
	$bulk_actions['mark_driver-assigned'] = 'Change status to driver assigned';
  $bulk_actions['mark_ready-pickup'] = 'Change status to Ready for Pickup';
  $bulk_actions['mark_pickup'] = 'Change status to Picked Up';

    return $bulk_actions;
}

/**

* Add the field to the checkout page

*/



// // 3. Set Custom Order Status @ WooCommerce Checkout Process
 
//add_action( 'woocommerce_payment_complete', 'thankyou_change_unfulfilled_order_status' );
 
function thankyou_change_unfulfilled_order_status( $order_id ){
   if( ! $order_id ) return;
   $order = wc_get_order( $order_id );
	
	// Get the WC_Order_Item_Shipping object data
    foreach($order->get_shipping_methods() as $shipping_item ){
		
        // When "express delivery" method is used, we change the order to "on-hold" status
        if( (strpos( $shipping_item->get_method_title(), "Local Pickup" ) !== false || strpos( $shipping_item->get_method_title(), "Local pickup" ) !== false) && ! $order->has_status('custom-status')){
            // Status without the "wc-" prefix
   			$order->update_status( 'custom-status' );
            break;
        }
		if( strpos( $shipping_item->get_method_title(), "USPS" ) !== false && ! $order->has_status('shipping-unfulfil')){
            // Status without the "wc-" prefix
   			$order->update_status( 'shipping-unfulfil' );
            break;
        }
		if( strpos( $shipping_item->get_method_title(), "Local Delivery" ) !== false && ! $order->has_status('delivery-unfulfil')){
            // Status without the "wc-" prefix
   			$order->update_status( 'delivery-unfulfil' );
            break;
        }
    }
}

//*********************************************************************************************************************

//************************** ADD DESIRED COLOURS TO ORDER STATUSES IN ADMIN PANEL ******************************
add_action('admin_head', 'styling_admin_order_list' );
function styling_admin_order_list() {
    global $pagenow, $post;

    if( $pagenow != 'edit.php') return; // Exit
    if( get_post_type($post->ID) != 'shop_order' ) return; // Exit

    // HERE we set your custom status
    $order_status = 'delivery-unfulfil'; // <==== HERE
	$shipping_unfulfil = 'shipping-unfulfil';
	$custom_status = 'custom-status'; // pickup unfulfilled
//	$completed = 'completed';
}
//  echo "<style>
//         .order-status.status-".sanitize_title( $order_status )." {
//             background: #EFE590;
//             color: #B37400;
//         }
// 		.order-status.status-".sanitize_title( $shipping_unfulfil )." {
//             background: #EFE590;
//             color: #B37400;
//         }
// 		.order-status.status-".sanitize_title( $custom_status )." {
//             background: #EFE590;
//             color: #B37400;
//         }
// 		.order-status.status-".sanitize_title( $completed )." {
//             background: #7ADF9C;
//             color: #355E3B;
//         }
// </style>";


//checkout fields placeholders

add_filter('woocommerce_checkout_fields', 'njengah_override_checkout_fields');

function njengah_override_checkout_fields($fields)

 {

	unset($fields['billing']['billing_address_2']);

	$fields['billing']['billing_company']['placeholder'] = 'Company name (optional)';
	$fields['billing']['billing_first_name']['placeholder'] = 'First Name *';
	$fields['billing']['billing_last_name']['placeholder'] = 'Last Name *';
	$fields['billing']['billing_email']['placeholder'] = 'Email Address *';
	$fields['billing']['billing_phone']['placeholder'] = 'Phone *';
	$fields['billing']['billing_address_1']['placeholder'] = 'House number and street name *';
	$fields['billing']['billing_city']['placeholder'] = 'Town / City *';
	$fields['billing']['billing_postcode']['placeholder'] = 'Postcode / ZIP *';
	
 	$fields['shipping']['shipping_first_name']['placeholder'] = 'First Name *';
 	$fields['shipping']['shipping_last_name']['placeholder'] = 'Last Name *';
	$fields['shipping']['shipping_address_1']['placeholder'] = 'House number and street name *';
	$fields['shipping']['shipping_address_2']['placeholder'] = 'Apartment, suite, unit, etc. (optional)';
	$fields['shipping']['shipping_city']['placeholder'] = 'Town / City *';
	$fields['shipping']['shipping_postcode']['placeholder'] = 'Postcode / ZIP *';
	$fields['shipping']['shipping_company']['placeholder'] = 'Company Name (optional)';
	

 	return $fields;

 }

// Hide checkout labels // 

// WooCommerce Checkout Fields Hook
add_filter('woocommerce_checkout_fields','custom_wc_checkout_fields_no_label');

// Our hooked in function - $fields is passed via the filter!
// Action: remove label from $fields
function custom_wc_checkout_fields_no_label($fields) {
    // loop by category
    foreach ($fields as $category => $value) {
        // loop by fields
        foreach ($value as $field => $property) {
            // remove label property
            unset($fields[$category][$field]['label']);
        }
    }
     return $fields;
}
//Add date and Time Stamps on the WooCommerce order columns

/* Add column “Order Notes” on orders page to display Customer's Notes */
add_filter('manage_edit-shop_order_columns', 'add_customer_note_column_header');
function add_customer_note_column_header($columns) {

$new_columns = (is_array($columns)) ? $columns : array();

$new_columns['order_customer_note'] = 'Order Notes';

return $new_columns;
}
/* End of adding “Order Notes” column */
add_action('admin_print_styles', 'add_customer_note_column_style');
function add_customer_note_column_style() {
$css = '.widefat .column-order_customer_note { width: 15%; }';
wp_add_inline_style('woocommerce_admin_styles', $css);
}

/* Add Customer's Notes to the “Order Notes” column */
add_action('manage_shop_order_posts_custom_column', 'add_customer_note_column_content');
function add_customer_note_column_content($column) {

global $post, $the_order;

if(empty($the_order) || $the_order->get_id() != $post->ID) {
$the_order = wc_get_order($post->ID);
}

$customer_note = $the_order->get_customer_note();
if($column == 'order_customer_note') {
echo('<span class="order-customer-note">' . $customer_note . '</span>');
}

}
// cart by sxm
// 
add_shortcode ('woo_cart_but', 'woo_cart_but' );
/**
 * Create Shortcode for WooCommerce Cart Menu Item
 */
function woo_cart_but() {
	ob_start();
 
        $cart_count = WC()->cart->cart_contents_count; // Set variable for cart item count
        $cart_url = wc_get_cart_url();  // Set Cart URL
  
        ?>
        <li><a class="menu-item cart-contents" href="<?php echo $cart_url; ?>" title="My Basket">
	    <?php
        if ( $cart_count > 0 ) {
       ?>
            <span class="cart-contents-count"><?php echo $cart_count; ?></span>
        <?php
        }
        ?>
        </a></li>
        <?php
	        
    return ob_get_clean();
 
}

// Hide uxm
// 
function hid_e_plug() {
  echo '<style>
    .plugins [data-slug="worker"],
	.plugins [data-slug="popups-for-divi"]{
        display: none;
	}

   
    
  </style>';
}
add_action('admin_head', 'hid_e_plug');


// Checkout Place order button
add_action( 'wp_footer', 'custom_checkout_jquery_script' );
function custom_checkout_jquery_script() {
  if ( is_checkout() && ! is_wc_endpoint_url() ) :
?>
  <script type="text/javascript">
  jQuery( function($){
	$(document).ready(function(){
		jQuery('.woocommerce #payment #place_order').addClass('uxm_pre');
	});
//     jQuery('.woocommerce #payment #place_order.uxm_pre').on('submit', function(event) {
//         //jQuery('button#place_order').text('Please Wait');
//         event.preventDefault();
		
// 		jQuery('.woocommerce #payment #place_order.uxm_pre').removeClass('uxm_pre');
		
// 		jQuery('html, body').animate({
// 			scrollTop: $("body.woocommerce-checkout div#order_review").offset().top
// 		}, 2000);
//      });
  });
</script>
 <?php
  endif;
}

function disable_shipping_calc_on_cart( $show_shipping ) {
    if( is_cart() ) {
        return false;
    }
    return $show_shipping;
}
add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99 );