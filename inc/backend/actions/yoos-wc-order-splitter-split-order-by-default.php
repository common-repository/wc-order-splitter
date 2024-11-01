<?php

defined('ABSPATH') || exit;

class WooCommerce_Order_Splitter_Split_Order {

	public function __construct() {
		add_action('wp_ajax_get_order_items', array($this, 'get_order_items_callback'));
		add_action('wp_ajax_split_order', array($this, 'order_splitter_callback'));
	}

	public function get_order_items_callback() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'split_order_nonce')) {
			wp_send_json_error('Nonce verification failed');
			wp_die();
		}
	
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
			wp_die();
		}
	
		$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
		$order = wc_get_order($order_id);
		$items_data = array();
	
		if ($order) {
			foreach ($order->get_items() as $item_id => $item) {
				$product = $item->get_product();
				$categories = [];
	
				if ($product) {
					// Get categories for the product
					$categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
	
					// If the product is a variation, get categories for the parent product
					if ($product->is_type('variation')) {
						$parent_id = $product->get_parent_id();
						$parent_categories = wp_get_post_terms($parent_id, 'product_cat', ['fields' => 'names']);
						$categories = array_merge($categories, $parent_categories);
					}
	
					// Remove duplicate categories
					$categories = array_unique($categories);
				}
	
				$stock_status = $product ? $product->get_stock_status() : '';
	
				// Map stock status to human-readable format
				$stock_status_map = array(
					'instock' => 'In stock',
					'outofstock' => 'Out of stock',
					'onbackorder' => 'On backorder',
					'onpreorder' => 'On preorder'
				);
	
				$stock_status_display = isset($stock_status_map[$stock_status]) ? $stock_status_map[$stock_status] : $stock_status;
	
				$items_data[] = array(
					'id' => $item_id,
					'sku' => $product ? $product->get_sku() : '',
					'name' => $item->get_name(),
					'quantity' => $item->get_quantity(),
					'category' => implode(', ', $categories),
					'stock_status' => $stock_status_display,
				);
			}
			wp_send_json_success($items_data);
		} else {
			wp_send_json_error(esc_html__('Order not found', 'wc-order-splitter'));
		}
		wp_die();
	}	   

	private function adjust_original_order_quantities($original_order_id, $split_data) {
		$original_order = wc_get_order($original_order_id);
		if (!$original_order) {
			return esc_html__('Original order not found', 'wc-order-splitter');
		}

		foreach ($original_order->get_items() as $item_id => $item) {
			if (isset($split_data[$item_id]) && isset($split_data[$item_id]['quantity']) && $split_data[$item_id]['quantity'] > 0) {
				$original_quantity = $item->get_quantity();
				$split_quantity = intval($split_data[$item_id]['quantity']);
				$new_quantity = $original_quantity - $split_quantity;

				if ($new_quantity > 0) {
					// Update the item quantity and line totals
					$item->set_quantity($new_quantity);
					$item->set_subtotal($item->get_subtotal() / $original_quantity * $new_quantity);
					$item->set_total($item->get_total() / $original_quantity * $new_quantity);
					$item->save();
				} else {
					// Remove the item from the order if the new quantity is 0
					$original_order->remove_item($item_id);
				}
			}
		}

		$original_order->calculate_totals();
		$original_order->save();
	}

	public function order_splitter_callback() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'split_order_nonce')) {
			wp_send_json_error('Nonce verification failed');
			wp_die();
		}

		if (!current_user_can('edit_posts')) {
			wp_send_json_error('Insufficient permissions');
			wp_die();
		}

		$original_order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

		$split_data_raw = isset($_POST['split_data']) && is_array($_POST['split_data']) ? array_map(function ($item) {
			return $item;
		}, $_POST['split_data']) : array();

		$split_data = array();
		$total_original_quantity = 0;
		$total_split_quantity = 0;

		foreach ($split_data_raw as $item_id => $data) {
			$item_id = intval($item_id);
			if (isset($data['quantity']) && isset($data['order'])) {
				$quantity = intval($data['quantity']);
				$order_number = sanitize_text_field($data['order']);
				if ($item_id > 0 && $quantity > 0) {
					$split_data[$order_number][$item_id] = array('quantity' => $quantity);
					$total_split_quantity += $quantity;
				}
			}
		}

		if (empty($split_data)) {
			wp_send_json_error('No quantities set for splitting.');
			wp_die();
		}

		$original_order = wc_get_order($original_order_id);
		if (!$original_order) {
			wp_send_json_error(esc_html__('Original order not found', 'wc-order-splitter'));
			wp_die();
		}

		foreach ($original_order->get_items() as $item_id => $item) {
			if ($item->get_type() === 'line_item') {
				$total_original_quantity += $item->get_quantity();
			}
		}

		if ($total_split_quantity >= $total_original_quantity) {
			wp_send_json_error('You cannot split all of the items in the order.');
			wp_die();
		}

		$new_order_ids = array();

		foreach ($split_data as $order_number => $items) {
			// Create a new order
			$new_order = wc_create_order();
			if (is_wp_error($new_order)) {
				wp_send_json_error('Failed to create a new order: ' . $new_order->get_error_message());
				wp_die();
			}

			$new_order_status = $original_order->get_status();
			$new_order->set_status($new_order_status);
			$new_order->set_address($original_order->get_address('billing'), 'billing');
			$new_order->set_address($original_order->get_address('shipping'), 'shipping');
			$new_order->set_payment_method($original_order->get_payment_method());
			$new_order->set_payment_method_title($original_order->get_payment_method_title());
			
			// Copy customer user
			$new_order->set_customer_id($original_order->get_customer_id());

			// Copy customer note
			if ($original_order->get_customer_note()) {
				$new_order->set_customer_note($original_order->get_customer_note());
			}

			foreach ($original_order->get_items() as $item_id => $item) {
				if (isset($items[$item_id]) && $items[$item_id]['quantity'] > 0) {
					$new_quantity = intval($items[$item_id]['quantity']);

					// Create a new item with the same properties as the original
					$new_item = new WC_Order_Item_Product();
					$new_item->set_props(array(
						'product_id'   => $item->get_product_id(),
						'variation_id' => $item->get_variation_id(),
						'quantity'     => $new_quantity,
						'subtotal'     => $item->get_subtotal() / $item->get_quantity() * $new_quantity,
						'total'        => $item->get_total() / $item->get_quantity() * $new_quantity,
						'name'         => $item->get_name()
					));

					// Copy metadata and tax data
					foreach ($item->get_meta_data() as $meta) {
						$new_item->add_meta_data($meta->key, $meta->value);
					}

					$new_order->add_item($new_item);
				}
			}

			// Conditionally copy shipping line items
			if ('yes' !== get_option('order_splitter_exclude_shipping_fee', 'no')) {
				foreach ($original_order->get_shipping_methods() as $shipping_item) {
					$new_shipping_item = new WC_Order_Item_Shipping();
					$new_shipping_item->set_props(array(
						'method_title' => $shipping_item->get_method_title(),
						'method_id'    => $shipping_item->get_method_id(),
						'total'        => $shipping_item->get_total(),
						'taxes'        => $shipping_item->get_taxes(),
					));
					$new_order->add_item($new_shipping_item);
				}
			}

			// Handle email settings based on 'Disable Order Email' setting
			$email_setting = get_option('order_splitter_disable_split_order_email', 'none');
			switch ($email_setting) {
				case 'for_customers':
					add_filter('woocommerce_email_recipient_customer_on_hold_order', '__return_empty_string');
					add_filter('woocommerce_email_recipient_customer_processing_order', '__return_empty_string');
					break;
				case 'for_administrators':
					add_filter('woocommerce_email_recipient_new_order', '__return_empty_string');
					break;
				case 'for_everyone':
					add_filter('woocommerce_email_recipient_customer_on_hold_order', '__return_empty_string');
					add_filter('woocommerce_email_recipient_customer_processing_order', '__return_empty_string');
					add_filter('woocommerce_email_recipient_new_order', '__return_empty_string');
					break;
			}			

			$new_order->calculate_totals();
			$new_order->save();

			$new_order_ids[] = $new_order->get_id();

			// Set metadata to track original and split orders
			$original_splitted_orders = $original_order->get_meta('yoos_splitted_order', true);
			if (!empty($original_splitted_orders)) {
				$original_splitted_orders .= ',' . $new_order->get_id();
			} else {
				$original_splitted_orders = $new_order->get_id();
			}
			$original_order->update_meta_data('yoos_splitted_order', $original_splitted_orders);
			$original_order->save();

			$new_order->update_meta_data('yoos_original_order', $original_order_id);
			$new_order->save();

			// Add note to the new order
			$new_order->add_order_note(
				sprintf(
					__('This order has been split from the original order #%s.', 'wc-order-splitter'),
					$original_order_id
				),
				false
			);
		}

		// Add note to original order
		if (!empty($new_order_ids)) {
			$original_order->add_order_note(
				sprintf(
					__('This order has been split to order #%s.', 'wc-order-splitter'),
					implode(', ', $new_order_ids)
				),
				false
			);
			$original_order->save();
		}

		// Flatten the split data for adjustment
		$flattened_split_data = [];
		foreach ($split_data as $order_number => $items) {
			foreach ($items as $item_id => $item_data) {
				$flattened_split_data[$item_id] = $item_data;
			}
		}

		$this->adjust_original_order_quantities($original_order_id, $flattened_split_data);

		wp_send_json_success(array('new_order_ids' => $new_order_ids));
		wp_die();
	}
}

// Initialize the plugin
new WooCommerce_Order_Splitter_Split_Order();
