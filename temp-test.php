<?php /* Template Name: New */
get_header();

$orders = get_posts( array(
    'numberposts' => -1,
    'post_type'   => 'shop_order',
    'post_status' => 'wc-pickup',
));



foreach ( $orders as $order_data ) {
    $order = wc_get_order( $order_data->ID );
    $order->update_status( 'completed' );
    echo $order_data->ID . "<br/>";
}







get_footer();