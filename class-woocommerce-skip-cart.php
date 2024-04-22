<?php
/**
 * Plugin Name:       WooCommerce Skip Cart
 * Plugin URI:        https://tadamus.com/products/woocommerce-skip-cart/
 * Description:       Skips the cart and takes the customer directly to check out while keeping the cart empty for the next purchases and also can set orders as complete automatically
 * Version:           1.1.0
 * Requires at least: 5.2
 * Requires PHP:      8.0
 * Author:            Tadamus
 * Author URI:        https://tadamus.com
 * Requires Plugins:    woocommerce
 *
 * @package woocommerce-skip-cart
 */

/**
 * Main class of the plugin
 */
class WooCommerce_Skip_Cart {

	/**
	 * The version of the plugin
	 *
	 * @var string
	 */
	public string $version = '1.1.0';

	/**
	 * Construct the object
	 */
	public function __construct() {
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'redirect_to_checkout' ) );
		add_action( 'wp_head', array( $this, 'clear_cart' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'auto_complete_order' ) );
				add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'wp_ajax_smwoo-settings-form', array( $this, 'ajax_callback' ) );
	}

	/**
	 * Add the menu page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function menu(): void {
		add_submenu_page( 'woocommerce', 'WooCommerce Skip Cart', 'Skip Cart', 'manage_options', 'smwoo-skip-cart', array( $this, 'page_callback' ) );
	}

	/**
	 * Callback for the admin page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function page_callback(): void {
		wp_enqueue_script( 'smwoo-skip-cart', plugin_dir_url( __FILE__ ) . 'assets/js/settings.js', array( 'jquery' ), $this->version, array(), true );
		wp_localize_script( 'smwoo-skip-cart', 'smAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_style( 'smwoo-skip-cart', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), $this->version );
		?>
			<h1><?php echo esc_html__( 'WooCommerce Skip Cart', 'woocommerce-skip-cart' ); ?></h1>
			<div id="smwoo-notification-wrap">
				<p id="smwoo-notification-paragraph"></p>
			</div>
			<form id="smwoo-settings-form">
				<input type="hidden" name="action" id="smwoo-settings-action" value="smwoo-settings-form">
				<input type="hidden" name="nonce" id="smwoo-settings-nonce" value="<?php echo esc_attr( wp_create_nonce( 'smwoo-settings-form' ) ); ?>">
				<div class="smwoo-form-group">
					<input type="checkbox" name="smwoo-skip-cart" id="smwoo-skip-cart" value="true" <?php echo ( get_option( 'smwoo-skip-cart' ) === 'true' ) ? 'checked' : ''; ?>>
					<label for="smwoo-skip-cart"><?php echo esc_html__( 'Skip cart when the buy button is pressed', 'woocommerce-skip-cart' ); ?></label>
				</div>
				<div class="smwoo-form-group">
					<input type="checkbox" name="smwoo-clear-cart" id="smwoo-clear-cart" value="true" <?php echo ( get_option( 'smwoo-clear-cart' ) === 'true' ) ? 'checked' : ''; ?>>
					<label for="smwoo-clear-cart"><?php echo esc_html__( 'Clear the cart when checkout is closed', 'woocommerce-skip-cart' ); ?></label>
				</div>
				<div class="smwoo-form-group">
					<input type="checkbox" name="smwoo-complete-order" id="smwoo-complete-order" value="true" <?php echo ( get_option( 'smwoo-complete-order' ) === 'true' ) ? 'checked' : ''; ?>>
					<label for="smwoo-complete-order"><?php echo esc_html__( 'Automatically set orders as \'complete\'', 'woocommerce-skip-cart' ); ?></label>
				</div>

				<input type="submit" id="smwoo-settings-submit" class="button button-primary">
			</form>
		<?php
	}

	/**
	 * Callback for the ajax request
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function ajax_callback(): void {
		if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['skip'] ) || ! isset( $_POST['clear'] ) || ! isset( $_POST['complete'] ) ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'smwoo-settings-form' ) ) {
			update_option( 'smwoo-skip-cart', sanitize_text_field( wp_unslash( $_POST['skip'] ) ) );
			update_option( 'smwoo-clear-cart', sanitize_text_field( wp_unslash( $_POST['clear'] ) ) );
			update_option( 'smwoo-complete-order', sanitize_text_field( wp_unslash( $_POST['complete'] ) ) );

			wp_send_json_success(
				array(
					'message' => __( 'Settings were saved!', 'woocommerce-skip-cart' ),
				),
				'200'
			);

			return;
		}

		wp_send_json_error(
			array(
				'message' => __( 'There was a problem, please contact support', 'woocommerce-skip-cart' ),
			)
		);
	}

	/**
	 * Redirect to the checkout page
	 *
	 * @since 1.0.0
	 */
	public function redirect_to_checkout() {
		if ( get_option( 'smwoo-skip-cart' ) === 'true' ) {
			return wc_get_checkout_url();
		}
	}

	/**
	 * Clear the cart
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clear_cart(): void {
		if ( wc_get_page_id( 'cart' ) === get_the_ID() || wc_get_page_id( 'checkout' ) === get_the_ID() ) {
			return;
		}

		if ( get_option( 'smwoo-clear-cart' ) === 'true' ) {
			WC()->cart->empty_cart( true );
		}
	}

	/**
	 * Automatically set order to complete
	 *
	 * @param float|int|string $order_id The order id.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function auto_complete_order( float|int|string $order_id ): void {
		if ( ! $order_id ) {
			return;
		}

		if ( get_option( 'smwoo-complete-order' ) === 'true' ) {
			$order = wc_get_order( $order_id );
			$order->update_status( 'completed' );
		}
	}
}

new WooCommerce_Skip_Cart();
