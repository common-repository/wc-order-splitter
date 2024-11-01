<?php
/**
 * Plugin Name: WooCommerce Order Splitter
 * Plugin URI: https://wordpress.org/plugins/wc-order-splitter/
 * Description: A plugin helps you to simply split an order by product and quantity.
 * Version: 1.3.6
 * Author: YoOhw.com
 * Author URI: https://yoohw.com
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Text Domain: wc-order-splitter
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
	exit;
}

class WooCommerce_Order_Splitter {
	
	public function __construct() {
		add_action('plugins_loaded', array($this, 'load_textdomain'));
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_action_links']);

		$this->includes();
	}

	public function load_textdomain() {
		load_plugin_textdomain('wc-order-splitter', false, basename(dirname(__FILE__)) . '/languages/');
	}

	public function includes() {
		include_once plugin_dir_path(__FILE__) . 'inc/cores/yoos-wc-order-splitter-notices.php';
		include_once plugin_dir_path(__FILE__) . 'inc/cores/yoos-wc-order-splitter-script.php';
	}

	public function add_action_links($links) {
		$settings_link = '<a href="admin.php?page=wc-settings&tab=orders">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
}

// Initialize the plugin
new WooCommerce_Order_Splitter();
