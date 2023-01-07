<?php
/**
 * Plugin Name:       WooCommerce Skip Cart
 * Plugin URI:        https://sorinmarta.com/projects/woocommerce-skip-cart
 * Description:       Skips the cart and takes the customer directly to checkout while keeping the cart empty for the next purchases
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Sorin Marta
 * Author URI:        https://sorinmarta.com
 */

class WooCommerce_Skip_Cart
{
    public function __construct()
    {
        add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'redirect_to_checkout' ) );
        add_action( 'wp_head', array( $this, 'clear_cart' ) );
        add_action( 'woocommerce_thankyou' , array($this, 'auto_complete_order'));
    }

    public function redirect_to_checkout()
    {
        return WC()->cart->get_checkout_url();
    }

    public function clear_cart(): void
    {
        if ( wc_get_page_id( 'cart' ) == get_the_ID() || wc_get_page_id( 'checkout' ) == get_the_ID() ) {
            return;
        }

        WC()->cart->empty_cart( true );
    }

    public function auto_complete_order( $order_id ) : void
    {
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );
        $order->update_status( 'completed' );
    }
}

new WooCommerce_Skip_Cart();