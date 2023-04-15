<?php
/**
 * Plugin Name:       WooCommerce Skip Cart
 * Plugin URI:        https://sorinmarta.com/projects/woocommerce-skip-cart
 * Description:       Skips the cart and takes the customer directly to check out while keeping the cart empty for the next purchases and also can set orders as complete automatically
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      8.0
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
		add_action('admin_menu', array($this, 'menu'));
        add_action('wp_ajax_smwoo-settings-form', array($this, 'ajax_callback'));
    }

	public function menu(): void
	{
		add_submenu_page('woocommerce', 'WooCommerce Skip Cart', 'Skip Cart', 'manage_options', 'smwoo-skip-cart', array($this, 'page_callback'));

	}

	public function page_callback(): void
	{
		wp_enqueue_script('smwoo-skip-cart', plugin_dir_url(__FILE__) . 'assets/js/settings.js', array('jquery'), false, true);
		wp_localize_script( 'smwoo-skip-cart', 'smAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		wp_enqueue_style('smwoo-skip-cart', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), false);
		?>
			<h1>WooCommerce Skip Cart</h1>
            <div id="smwoo-notification-wrap">
                <p id="smwoo-notification-paragraph"></p>
            </div>
			<form id="smwoo-settings-form">
				<input type="hidden" name="action" id="smwoo-settings-action" value="smwoo-settings-form">
				<input type="hidden" name="nonce" id="smwoo-settings-nonce" value="<?php echo wp_create_nonce('smwoo-settings-form'); ?>">
				<div class="smwoo-form-group">
					<input type="checkbox" name="smwoo-skip-cart" id="smwoo-skip-cart" value="true" <?php echo (get_option('smwoo-skip-cart') === 'true') ? 'checked' : ''; ?>>
					<label for="smwoo-skip-cart">Skip cart when the buy button is pressed</label>
				</div>
				<div class="smwoo-form-group">
					<input type="checkbox" name="smwoo-clear-cart" id="smwoo-clear-cart" value="true" <?php echo (get_option('smwoo-clear-cart') === 'true') ? 'checked' : ''; ?>>
					<label for="smwoo-clear-cart">Clear the cart when checkout is closed</label>
				</div>
				<div class="smwoo-form-group">
					<input type="checkbox" name="smwoo-complete-order" id="smwoo-complete-order" value="true" <?php echo (get_option('smwoo-complete-order') === 'true') ? 'checked' : ''; ?>>
					<label for="smwoo-complete-order">Automatically set orders as 'complete'</label>
				</div>

				<input type="submit" id="smwoo-settings-submit" class="button button-primary">
			</form>
		<?php
	}

    public function ajax_callback(): void
    {
        if(defined('DOING_AJAX') && DOING_AJAX && wp_verify_nonce($_POST['nonce'], 'smwoo-settings-form')){
            $this->maybe_add_option('smwoo-skip-cart', $_POST['skip']);
            $this->maybe_add_option('smwoo-clear-cart', $_POST['clear']);
            $this->maybe_add_option('smwoo-complete-order', $_POST['complete']);

	        wp_send_json_success(array(
		        'message' => 'Settings were saved!'
	        ), '200');

            return;
        }

        wp_send_json_error(array(
                'message' => 'There was a problem, please contact support'
        ));
    }

    public function redirect_to_checkout()
    {
        if(get_option('smwoo-skip-cart') === 'true') return wc_get_checkout_url();
    }

    public function clear_cart(): void
    {
        if ( wc_get_page_id( 'cart' ) == get_the_ID() || wc_get_page_id( 'checkout' ) == get_the_ID() ) {
            return;
        }

        if(get_option('smwoo-clear-cart') === 'true') WC()->cart->empty_cart( true );
    }

    public function auto_complete_order( $order_id ) : void
    {
        if ( ! $order_id ) {
            return;
        }

        if(get_option('smwoo-complete-order') === 'true'){
	        $order = wc_get_order( $order_id );
	        $order->update_status( 'completed' );
        }
    }

    private function maybe_add_option(string $tag, $value): void
    {
	    $option = get_option($tag, $value);

        if($option && $option != $value){
            update_option($tag, $value);
        }else{
            add_option($tag, $value);
        }
    }
}

new WooCommerce_Skip_Cart();