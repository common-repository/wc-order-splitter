<?php

defined('ABSPATH') || exit;

class WooCommerce_Order_Splitter_Settings {

	private $version = '1.3.2';

	public function __construct() {
		add_filter('woocommerce_settings_tabs_array', array($this, 'add_orders_settings_tab'), 30);
		add_action('woocommerce_settings_tabs_orders', array($this, 'order_splitter_settings_tab'), 9);
		add_action('woocommerce_update_options_orders', array($this, 'update_order_splitter_settings'));
		register_activation_hook(__FILE__, array($this, 'set_default_settings'));
		register_activation_hook(__FILE__, array($this, 'on_activation'));
		add_action('plugins_loaded', array($this, 'check_for_updates'));
	}

	public function add_orders_settings_tab($settings_tabs) {
		$settings_tabs['orders'] = __('Orders', 'wc-order-splitter');
		return $settings_tabs;
	}

	public function order_splitter_settings_tab() {
		woocommerce_admin_fields($this->get_split_order_settings());
	}

	public function get_split_order_settings() {
		if (current_user_can('shop_manager') && get_option('order_splitter_shop_manager_permission', 'no') === 'no') {
			echo '<div class="notice notice-error"><p>' . __('You are not allowed to access Order Splitter settings.', 'wc-order-splitter') . '</p></div>';
			return array();
		}
		
		$settings = array(
			'section_title' => array(
				'name'     => __('Order splitter', 'wc-order-splitter'),
				'type'     => 'title',
				'id'       => 'order_splitter_section_title',
				'desc'     => '<span class="yo-premium"><i class="dashicons dashicons-lock"></i> Upgrade to Premium version for more features such as Automate Splitter, By Tags, By Vendors, Advanced Duplicate and more... <a href="https://yoohw.com/product/woocommerce-order-splitter-premium/" target="_blank" class="premium-label">Upgrade</a></span>',
			),
			'order_status' => array(
				'name'     => __('Allowed status', 'wc-order-splitter'),
				'type'     => 'multiselect',
				'desc_tip' => __('Choose order statuses that allow for splitting and duplication.', 'wc-order-splitter'),
				'id'       => 'order_splitter_status_allowed',
				'options'  => wc_get_order_statuses(),
				'default'  => array('wc-processing'),
				'custom_attributes' => array(
					'data-placeholder' => __('Select order statuses', 'wc-order-splitter')
				),
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width:300px;',
			),
			'exclude_shipping' => array(
				'name'     => __('Excluded fee', 'wc-order-splitter'),
				'type'     => 'checkbox',
				'desc'     => __('Exclude shipping fees for the split order.', 'wc-order-splitter'),
				'id'       => 'order_splitter_exclude_shipping_fee',
				'default'  => 'no',
			),
			'email_option' => array(
				'name'     => __('Disable email', 'wc-order-splitter'),
				'type'     => 'select',
				'desc_tip' => __('Select who should not receive split order emails.', 'wc-order-splitter'),
				'id'       => 'order_splitter_disable_split_order_email',
				'default'  => 'none',
				'options'  => array(
					'none'         => __('None', 'wc-order-splitter'),
					'for_customers' => __('For customers', 'wc-order-splitter'),
					'for_administrators' => __('For administrators', 'wc-order-splitter'),
					'for_everyone' => __('For everyone', 'wc-order-splitter'),
				),
			),
			'allow_split_orders' => array(
				'name'     => __('Permission', 'wc-order-splitter'),
				'type'     => 'checkbox',
				'desc'     => __('Enable the shop manager to split orders.', 'wc-order-splitter'),
				'id'       => 'order_splitter_shop_manager_permission',
				'default'  => 'no',
			),
			'order_label' => array(
				'name'     => __('Order labels', 'wc-order-splitter'),
				'type'     => 'checkbox',
				'desc'     => __('Enable the labels for split orders.', 'wc-order-splitter'),
				'id'       => 'order_splitter_order_label',
				'default'  => 'yes',
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id'   => 'order_splitter_section_end'
			)
		);
		return apply_filters('order_splitter_settings', $settings);
	}

	public function update_order_splitter_settings() {
		woocommerce_update_options($this->get_split_order_settings());
	}

	public function set_default_settings() {
		$default_settings = array(
			'order_splitter_status_allowed' => array('wc-processing'),
			'order_splitter_exclude_shipping_fee' => 'no',
			'order_splitter_disable_split_order_email' => 'none',
			'order_splitter_shop_manager_permission' => 'no',
			'order_splitter_order_label' => 'yes',
		);

		foreach ($default_settings as $key => $value) {
			if (get_option($key, false) === false) {
				add_option($key, $value);
			}
		}
	}

    public function on_activation() {
        $this->update_options();
        update_option('wc_order_splitter_version', $this->version);
    }

    public function check_for_updates() {
        $installed_version = get_option('wc_order_splitter_version');

        if ($installed_version !== $this->version) {
            $this->update_options();
            update_option('wc_order_splitter_version', $this->version);
        }
    }

	public function update_options() {
		$options_to_update = array(
			'new_order_email_option' => 'order_splitter_disable_split_order_email',
			'new_order_exclude_shipping' => 'order_splitter_exclude_shipping_fee',
			'order_splitter_shop_manager_permission' => 'order_splitter_shop_manager_permission'
		);
	
		foreach ($options_to_update as $old_option => $new_option) {
			$old_value = get_option($old_option);
			if (false !== $old_value) {
				update_option($new_option, $old_value);
				delete_option($old_option);
			}
		}
	}
}

// Initialize the plugin settings
new WooCommerce_Order_Splitter_Settings();
