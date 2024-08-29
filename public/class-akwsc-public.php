<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/akashSinghtkd
 * @since      1.0.0
 *
 * @package    Akwsc
 * @subpackage Akwsc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Akwsc
 * @subpackage Akwsc/public
 * @author     Akash Singh <akashsinghtkd01@gmail.com>
 */
class Akwsc_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Akwsc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Akwsc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/akwsc-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Akwsc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Akwsc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/akwsc-public.js', array('jquery'), $this->version, true);

		// Localize the script with ajaxurl
		wp_localize_script($this->plugin_name, 'akwsc', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce(AKWSC_NONCE_KEY),
			'action' => [
				'stock_check' => 'stock_check_action'
			],
			'message' => [
				'enter_valid_number' 		=> Akwsc_Plugin_Messages::get_error_message('enter_valid_number'),
				'group_product_not_valid' 	=> Akwsc_Plugin_Messages::get_error_message('group_product_not_valid'),
				'product_id_invalid' 		=> Akwsc_Plugin_Messages::get_error_message('product_id_invalid'),
				'unexpected_error' 			=> Akwsc_Plugin_Messages::get_error_message('unexpected_error'),
				'variable_not_found' 		=> Akwsc_Plugin_Messages::get_error_message('variable_not_found'),
			]
		));
	}

	/**
	 * Display a "Check Availability" button for simple products.
	 *
	 * This method checks if the current product is of type 'simple' or variable. 
	 * If so, it outputs a button with an ID and class for checking availability. 
	 * This button is used to trigger a JavaScript function to check
	 * product stock availability status.
	 *
	 * @global WC_Product $product The current product object.
	 * @since    1.0.0
	 * @return void
	 */
	public function akwsc_add_stock_check_availability_button()
	{
		global $product;

		$product_type = $product->get_type();
		$is_product_detail_page = is_product();

		// Define the default allow product types
		$default_product_types = array('simple', 'variable', 'grouped');

		// Allow others to modify the product types
		$product_types = apply_filters('akwsc_allow_product_types', $default_product_types);

		// Only show button for specific post type
		if (in_array($product_type, $product_types, true)) {
			$product_id = $product->get_id();
			?>
			<div>
				<?php if (! $is_product_detail_page) : ?>
					<input class="akwsc_quantity_input" type="number" name="akwsc_quantity_input" value="1" min="1" />
				<?php endif; ?>
				<button
					id="akwsc-check-availability-button"
					type="button"
					class="akwsc-check-availability-button"
					data-akwsc-is-product-detail-page="<?php echo esc_attr($is_product_detail_page); ?>"
					data-akwsc-product-id="<?php echo esc_attr($product_id); ?>"
					data-akwsc-product-type="<?php echo esc_attr($product_type); ?>">
					<?php esc_html_e('Check Availability', AKWSC_TEXT_DOMAIN); ?>
				</button>
				<span id="akwsc-loading-text" style="display:none;">
					<?php esc_html_e('Checking...', AKWSC_TEXT_DOMAIN); ?>
				</span>
				<div id="akwsc-message-box"></div>
			</div>
			<?php
		}
	}


	/**
	 * Handles AJAX requests for stock checking.
	 *
	 * This method validates the AJAX request, checks nonce security, sanitizes input data,
	 * and performs stock checks based on the product type. It sends appropriate JSON responses
	 * based on success or failure.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function akwsc_stock_check_action_callback()
	{
		// Validate the AJAX request and nonce
		if (!defined('DOING_AJAX') || !DOING_AJAX || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], AKWSC_NONCE_KEY)) {
			wp_send_json_error(['message' => Akwsc_Plugin_Messages::get_error_message('invalid_request')]);
		}

		try {
			// Sanitize and validate input
			$product_id 	= filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
			$products_data 	= isset($_POST['products']) ? $_POST['products'] : [];
			$requested_qty 	= filter_input(INPUT_POST, 'requested_qty', FILTER_SANITIZE_NUMBER_INT);

			// Check for missing or invalid fields
			if (!$product_id) {
				$missing_fields = [];
				if (!$product_id) $missing_fields[] = 'Product ID';

				wp_send_json_error(['message' => Akwsc_Plugin_Messages::get_error_message('empty_data', $missing_fields)]);
			}

			if (empty($products_data) && !$requested_qty) {
				wp_send_json_error(['message' => Akwsc_Plugin_Messages::get_error_message('empty_data', ['Requested products'])]);
			}

			// Get WooCommerce product
			$product = wc_get_product($product_id);
			if (!$product) {
				wp_send_json_error(['message' => Akwsc_Plugin_Messages::get_error_message('product_not_found')]);
			}

			// Handle stock check based on product type
			if ($product->is_type('grouped')) {
				$this->handle_grouped_product_stock_check($products_data);
			} else {
				$this->handle_non_grouped_product_stock_check($product, $requested_qty);
			}
		} catch (Exception $e) {
			// Log error and send a generic error response
			error_log('Error in akwsc_stock_check_action_callback: ' . $e->getMessage());
			wp_send_json_error(['message' => 'An unexpected error occurred. Please try again later.']);
		}
	}

	/**
	 * Handles stock check for grouped products.
	 *
	 * This method iterates through the provided product data, checks the stock status of each
	 * child product in a grouped product, and constructs appropriate messages based on stock availability.
	 * It then sends a JSON response indicating whether sufficient stock is available or not.
	 *
	 * @param array	$products_data  Array of child products' data, each containing 'product_id' and 'requested_qty'.
	 * 
	 * @return void
	 */
	private function handle_grouped_product_stock_check($products_data)
	{
		$messages = [];
		$sufficient_stock = true;

		foreach ($products_data as $product_data) {
			// Sanitize requested 
			$product_data['product_id'] 	= filter_var($product_data['product_id'], FILTER_SANITIZE_NUMBER_INT);
			$product_data['requested_qty'] 	= filter_var($product_data['requested_qty'], FILTER_SANITIZE_NUMBER_INT);

			$child_id 		= isset($product_data['product_id']) ? intval($product_data['product_id']) : 0;
			$requested_qty 	= isset($product_data['requested_qty']) ? intval($product_data['requested_qty']) : 0;

			if ($child_id && $requested_qty > 0) {
				$child_product = wc_get_product($child_id);
				if ($child_product) {
					$child_name = $child_product->get_name();
					if ($child_product->is_in_stock()) {
						if ($child_product->managing_stock()) {
							$stock_quantity = intval($child_product->get_stock_quantity());
							if ($stock_quantity < $requested_qty) {
								$messages[] = "{$child_name}: Sorry, we only have {$stock_quantity} units in stock.";
								$sufficient_stock = false;
							}
						} else {
							// Assume unlimited stock if not managing stock
							continue;
						}
					} else {
						$messages[] = "{$child_name}: Sorry, this item is out of stock.";
						$sufficient_stock = false;
					}
				} else {
					$messages[] = "Product with ID {$child_id} not found.";
					$sufficient_stock = false;
				}
			}
		}

		if ($sufficient_stock) {
			wp_send_json_success(['message' => Akwsc_Plugin_Messages::get_message_for_stock_check_action_ajax('stock_available')]);
		} else {
			wp_send_json_error(['message' => implode('</br>', $messages)]);
		}
	}

	/**
	 * Handles stock check for non-grouped products.
	 *
	 * This method checks the stock status of a single non-grouped product based on the requested quantity.
	 * It sends a JSON response indicating whether sufficient stock is available or not, or if stock management is disabled.
	 *
	 * @param WC_Product $product        The non-grouped product to check.
	 * @param int        $requested_qty The quantity of the product requested.
	 * 
	 * @return void
	 */
	private function handle_non_grouped_product_stock_check($product, $requested_qty)
	{
		if ($product->is_in_stock()) {
			if ($product->managing_stock()) {
				if ($requested_qty < 1) {
					wp_send_json_error(['message' => Akwsc_Plugin_Messages::get_error_message('enter_valid_number')]);
				}

				$stock_quantity = intval($product->get_stock_quantity());
				if ($stock_quantity >= $requested_qty) {
					wp_send_json_success(['message' => Akwsc_Plugin_Messages::get_message_for_stock_check_action_ajax('stock_available')]);
				} else {
					wp_send_json_error(['message' => Akwsc_Plugin_Messages::get_message_for_stock_check_action_ajax('limited_stock_available', $stock_quantity)]);
				}
			} else {
				// Stock management is disabled; assume unlimited stock
				wp_send_json_success(['message' => Akwsc_Plugin_Messages::get_message_for_stock_check_action_ajax('stock_available')]);
			}
		} else {
			wp_send_json_error(['message' => Akwsc_Plugin_Messages::get_message_for_stock_check_action_ajax('out_of_stock')]);
		}
	}
}
