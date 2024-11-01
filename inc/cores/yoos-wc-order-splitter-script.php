<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_Order_Splitter_Script {

	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

		$this->includes();
	}

	public function enqueue_scripts() {
		// Only run in admin area
		if (!is_admin()) {
			return;
		}
	
		// Prevent shop manager from running the scripts if the permission is set to 'no'
		if (current_user_can('shop_manager') && get_option('order_splitter_shop_manager_permission', 'no') === 'no') {
			return;
		}
	
		// Enqueue split order script for all eligible users
		wp_enqueue_script('split-order-js', plugin_dir_url(__FILE__) . '../../js/yoos-wc-order-splitter-split-table.js', array('jquery'), '1.3', true);
	
		// Enqueue bulk return script only for administrators on the Orders page
		$screen = get_current_screen();
		if (current_user_can('administrator') && $screen && $screen->post_type === 'shop_order') {
			wp_enqueue_script('bulk-return-order-js', plugin_dir_url(__FILE__) . '../../js/yoos-wc-order-splitter-bulk-return-action.js', array('jquery'), '1.2', true);
		}
	
		// Shared translation array for both scripts
		$translation_array = array(
			'errorOccurredFetchingOrder' => esc_html__('Error occurred while fetching order items.', 'wc-order-splitter'),
			'unableToFetch' => esc_html__('Unable to fetch order items.', 'wc-order-splitter'),
			'errorOccurred' => esc_html__('Error occurred while fetching order items.', 'wc-order-splitter'),
			'orderSplitSuccess' => esc_html__('Order split successfully. New order ID:', 'wc-order-splitter'),
			'failedToSplitOrder' => esc_html__('Failed to split order.', 'wc-order-splitter'),
			'product' => esc_html__('Product', 'wc-order-splitter'),
			'order' => esc_html__('Order', 'wc-order-splitter'),
			'newOrder' => esc_html__('New order #', 'wc-order-splitter'),
			'quantity' => esc_html__('Quantity', 'wc-order-splitter'),
			'splitQuantity' => esc_html__('Split quantity', 'wc-order-splitter'),
			'splitIt' => esc_html__('Split it', 'wc-order-splitter'),
			'default' => esc_html__('Default', 'wc-order-splitter'),
			'category' => esc_html__('Category', 'wc-order-splitter'),
			'stockStatus' => esc_html__('Stock status', 'wc-order-splitter'),
			'tag' => esc_html__('Tag (Premium)', 'wc-order-splitter'),
			'vendor' => esc_html__('Vendor (Premium)', 'wc-order-splitter'),
			'cancel' => esc_html__('Cancel', 'wc-order-splitter'),
			'splitting' => esc_html__('Splitting...', 'wc-order-splitter'),
			'returnToOriginalOrder' => esc_html__('Return to original order', 'wc-order-splitter'),
			'pleaseSelectAtLeastOneOrder' => esc_html__('Please select at least one order.', 'wc-order-splitter'),
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('split_order_nonce'),
			'bulkReturnOrderNonce' => wp_create_nonce('yoos_handle_bulk_action')
		);
	
		// Localize the shared translation array for both scripts
		wp_localize_script('split-order-js', 'splitOrderTranslations', $translation_array); // Localize for the split-order script
		wp_localize_script('bulk-return-order-js', 'splitOrderTranslations', $translation_array); // Localize for the bulk-return script
	
		// Enqueue the common CSS
		wp_enqueue_style('split-order-css', plugin_dir_url(__FILE__) . '../../css/yoos-wc-order-splitter-style.css', '1.4', true);
	}	

	public function includes() {
		include_once plugin_dir_path(__FILE__) . '../backend/yoos-wc-order-splitter-settings.php';
		include_once plugin_dir_path(__FILE__) . '../backend/yoos-wc-order-splitter-orders.php';
		include_once plugin_dir_path(__FILE__) . '../backend/yoos-wc-order-splitter-orders-bulk-return.php';
		include_once plugin_dir_path(__FILE__) . '../backend/yoos-wc-order-splitter-edit-order-split-button.php';
		include_once plugin_dir_path(__FILE__) . '../backend/yoos-wc-order-splitter-edit-order-return-option.php';
		include_once plugin_dir_path(__FILE__) . '../backend/yoos-wc-order-splitter-edit-order-duplicate-option.php';
	}
}

new WC_Order_Splitter_Script();
