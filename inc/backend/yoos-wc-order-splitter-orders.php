<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class WooCommerce_Order_Splitter_Edit_Order {

	public function __construct() {
		add_action('woocommerce_admin_order_data_after_order_details', [$this, 'add_split_order_inline_css']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
	}

	public function add_split_order_inline_css($order) {
		// Check if the order label option is enabled
		if (get_option('order_splitter_order_label', 'yes') !== 'yes') {
			return;
		}

		// Ensure we have a valid order object
		if (!$order) {
			return;
		}

		$original_order_id = $order->get_meta('yoos_original_order');
		$splitted_order_id = $order->get_meta('yoos_splitted_order');

		// If the meta exists, add the custom inline CSS
		if (!empty($original_order_id)) {
			?>
			<style>
				h2.woocommerce-order-data__heading::after {
					content: "O#<?php echo esc_html($original_order_id); ?>";
					font-size: 13px;
					text-shadow: none;
					color: #ffffff;
					background-color: #dba617;
					padding: 5px 10px;
					margin-left: 10px;
					border-radius: 5px;
					display: inline-block;
				}
			</style>
			<?php
		}

		if (!empty($splitted_order_id)) {
			?>
			<style>
				h2.woocommerce-order-data__heading::after {
					content: "S#<?php echo esc_html($splitted_order_id); ?>";
					font-size: 13px;
					text-shadow: none;
					color: #ffffff;
					background-color: #00a32a;
					padding: 5px 10px;
					margin-left: 10px;
					border-radius: 5px;
					display: inline-block;
				}
			</style>
			<?php
		}
	}

	public function enqueue_admin_scripts($hook) {
		// Check if the order label option is enabled
		if (get_option('order_splitter_order_label', 'yes') !== 'yes') {
			return;
		}

		// Load script only on the WooCommerce orders list page
		if ('edit.php' === $hook || 'woocommerce_page_wc-orders' === $hook) {

			wp_enqueue_script('yoohw_custom_admin_script', plugin_dir_url(__FILE__) . '../../js/yoos-wc-order-splitter-orders.js', ['jquery'], '1.1', true);

			// Determine the number of items per page from WooCommerce setting
			$per_page = get_option('woocommerce_admin_orders_per_page', 20);

			// Get the current page number
			$current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

			// Get orders for the current page
			$orders = wc_get_orders([
				'limit' => $per_page,
				'paged' => $current_page,
			]);

			$order_data = [];

			foreach ($orders as $order) {
				$order_data[$order->get_id()] = [
					'original_order' => $order->get_meta('yoos_original_order'),
					'splitted_order' => $order->get_meta('yoos_splitted_order'),
				];
			}

			wp_localize_script('yoohw_custom_admin_script', 'yoohw_order_data', $order_data);
		}
	}
}

// Initialize the class
new WooCommerce_Order_Splitter_Edit_Order();
