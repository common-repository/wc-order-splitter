<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_Order_Splitter_Notices {

	public function __construct() {
		add_action('admin_notices', [$this, 'check_woocommerce_active']);
		add_action('admin_notices', [$this, 'admin_notice']);
		add_action('wp_ajax_dismiss_wc_order_splitter_notice', [$this, 'dismiss_notice']);
		add_action('wp_ajax_never_show_wc_order_splitter_notice', [$this, 'never_show_notice']);
		add_action('admin_notices', [$this, 'first_time_notice']);
		add_action('wp_ajax_dismiss_first_time_notice', [$this, 'dismiss_first_time_notice']);

		add_action('admin_notices', [$this, 'new_feature_notice']); // New feature notice action
		add_action('wp_ajax_dismiss_new_feature_notice', [$this, 'dismiss_new_feature_notice']); // New AJAX handler
	}

	public function check_woocommerce_active() {
		if (!is_plugin_active('woocommerce/woocommerce.php')) {
			$this->yoos_missing_wc_notice();
		}
	}

	public function yoos_missing_wc_notice() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php echo esc_html__('WooCommerce Order Splitter requires WooCommerce to be installed and activated.', 'wc-order-splitter'); ?></p>
		</div>
		<?php
	}

	public function admin_notice() {
		$user_id = get_current_user_id();
		$activation_time = get_user_meta($user_id, 'wc_order_splitter_activation_time', true);
		$current_time = current_time('timestamp');

		if (get_user_meta($user_id, 'wc_order_splitter_never_show_again', true) === 'yes') {
			return;
		}

		if (!$activation_time) {
			update_user_meta($user_id, 'wc_order_splitter_activation_time', $current_time);
			return;
		}

		$time_since_activation = $current_time - $activation_time;
		$days_since_activation = floor($time_since_activation / DAY_IN_SECONDS);

		if ($days_since_activation >= 1 && (($days_since_activation - 1) % 90 === 0)) {
			if (get_user_meta($user_id, 'wc_order_splitter_notice_dismissed', true) !== 'yes') {
				echo '<div class="notice notice-info is-dismissible">
					<p>Thank you for using WooCommerce Order Splitter! Please support us by <a href="https://wordpress.org/plugins/wc-order-splitter/#reviews" target="_blank">leaving a review</a> <span style="color: #e26f56;">&#9733;&#9733;&#9733;&#9733;&#9733;</span> to keep updating & improving.</p>
					<p><a href="#" onclick="dismissForever()">Never show this again</a></p>
				</div>';
				add_action('admin_footer', [$this, 'wc_order_splitter_admin_footer_scripts']);
			}
		}
	}

	public function new_feature_notice() {
		$user_id = get_current_user_id();
		$new_feature_dismissed = get_user_meta($user_id, 'wc_order_splitter_new_feature_dismissed', true);

		if ($new_feature_dismissed !== 'yes') {
			echo '<div class="notice notice-success is-dismissible">
				<p>Exciting News! We’ve just added a new <strong>Split Order Notification</strong> feature. Now, keep customers and admins informed about split order details automatically! <a href="https://yoohw.com/product/woocommerce-order-splitter-premium/" target="_blank">Upgrade to Premium now</a>.</p>
				<p><a href="#" onclick="WC_order_splitter_Admin_Notice.dismissNewFeatureNotice()">Dismiss</a></p>
			</div>';
			add_action('admin_footer', [$this, 'wc_order_splitter_admin_footer_scripts']);
		}
	}

	public function wc_order_splitter_admin_footer_scripts() {
		?>
		<script type="text/javascript">
			var WC_order_splitter_Admin_Notice = {
				dismissForever() {
					jQuery.ajax({
						url: ajaxurl,
						type: "POST",
						data: {
							action: "never_show_wc_order_splitter_notice",
						},
						success: function(response) {
							jQuery(".notice.notice-info").hide();
						}
					});
				},
				dismissNewFeatureNotice() {
					jQuery.ajax({
						url: ajaxurl,
						type: "POST",
						data: {
							action: "dismiss_new_feature_notice",
						},
						success: function(response) {
							jQuery(".notice.notice-success").hide();
						}
					});
				}
			};
			jQuery(document).on("click", ".notice.is-dismissible", function(){
				jQuery.ajax({
					url: ajaxurl,
					type: "POST",
					data: {
						action: "dismiss_wc_order_splitter_notice",
					}
				});
			});
		</script>
		<?php
	}

	public function dismiss_notice() {
		$user_id = get_current_user_id();
		update_user_meta($user_id, 'wc_order_splitter_notice_dismissed', 'yes');
	}

	public function never_show_notice() {
		$user_id = get_current_user_id();
		update_user_meta($user_id, 'wc_order_splitter_never_show_again', 'yes');
	}

	public function dismiss_new_feature_notice() {
		$user_id = get_current_user_id();
		update_user_meta($user_id, 'wc_order_splitter_new_feature_dismissed', 'yes');
	}

	public function first_time_notice() {
		$user_id = get_current_user_id();
		$first_time_notice_dismissed = get_user_meta($user_id, 'wc_order_splitter_first_time_notice_dismissed', true);

		if ($first_time_notice_dismissed !== 'yes') {
			$activation_time = get_option('wc_order_splitter_activation_time', false);
			if (!$activation_time) {
				update_option('wc_order_splitter_activation_time', current_time('timestamp'));
				echo '<div class="notice notice-info is-dismissible">
					<p>Thank you for installing WooCommerce Order Splitter! Please <a href="'. esc_url(admin_url('admin.php?page=wc-settings&tab=orders')) .'">visit the Settings page</a> to configure the plugin.</p>
					<p><a href="#" onclick="wc_order_splitter_Admin_Notice.dismissFirstTimeNotice()">Dismiss</a></p>
				</div>';
				add_action('admin_footer', [$this, 'wc_order_splitter_admin_footer_scripts']);
			}
		}
	}

	public function dismiss_first_time_notice() {
		$user_id = get_current_user_id();
		update_user_meta($user_id, 'wc_order_splitter_first_time_notice_dismissed', 'yes');
	}
}

new WC_Order_Splitter_Notices();
