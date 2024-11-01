=== Order Splitter - Split & Duplicate orders for WooCommerce===
Contributors: yoohw
Tags: split order, split, clone, duplicate order, clone order
Requires at least: 6.3
Tested up to: 6.6.2
WC tested up to: 9.3.3
Requires PHP: 7.2
Stable tag: 1.3.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin helps you to simply split an order by product and quantity.

== Description ==
WooCommerce Order Splitter plugin allows you to split an order into multiple orders within the WooCommerce admin panel. This is particularly useful for managing large orders that need to be divided for processing.

[Premium version](https://yoohw.com/product/woocommerce-order-splitter-premium/) | [Documentation](https://yoohw.com/docs/category/woocommerce-order-splitter/) | [Support](https://yoohw.com/support/)  | [Demo](https://sandbox.yoohw.com/create-sandbox-user/)

== Features ==
* **Split by Quantity**: Easily split an order items into new multiple orders with specified quantities.
* **Split by Category**: Split orders based on product categories to streamline processing and fulfillment.
* **Split by Stock status**: Split orders according to the stock status of products, ensuring accurate handling of available and backordered items.
* **Duplicate an Order**: Simply duplicate an order at the Order Actions selection.
* **Returning Split Orders**: Return the single or multiple split orders back to the original order.
* **Flexible Options**: Controllable order status, shipping fee, email sending for the new split order.

== Premium Features ==
* **Automate Splitter**: Automatically split orders based on predefined rules, saving time and reducing manual intervention during high-volume sales or specific order scenarios.
* **Split Order Notification**: Automatically notify customers and admins when an order is split, providing split details and order updates to streamline communication and improve transparency.
* **Split by Tag**: Split orders by product tags, allowing for more precise order management and fulfillment strategies tailored to your business needs.
* **Split by Vendor**: Split orders based on vendor, ensuring each vendor receives a separate order for the products they are responsible for, streamlining fulfillment and simplifying multi-vendor management.
* **Duplicate Orders**: Bulk duplicate the orders within just a click, able to set order statues can be duplicated and duplicated order status should be.
* **Inventory supported**: Ensure accurate inventory management during the splitting process, with real-time updates that reflect stock levels after each order is split.
* **Additional tax classes**: Copied the item additional tax classes to the new orders.
* **Fully order meta**: Add all order meta from the original order to new orders.
* **Alot more**: Access a range of advanced features designed to enhance your order management, including custom split criteria, and seamless integration with third-party tools and plugins.

[Explore the Premium version here](https://yoohw.com/product/woocommerce-order-splitter-premium/)

== Plugin Integrations ==

[Pre-Orders for WooCommerce by YOS](https://wordpress.org/plugins/pre-orders-wc/): Split by Product stock status feature.
[WCFM Marketplace – Multivendor Marketplace for WooCommerce](https://wordpress.org/plugins/wc-multivendor-marketplace/): Split by Vendor feature.

== Installation ==
1. **Upload Plugin**: Upload the `wc-order-splitter` folder to the `/wp-content/plugins/` directory.
2. **Activate Plugin**: Activate the plugin through the 'Plugins' menu in WordPress.
3. **Prerequisites**: Ensure that WooCommerce is installed and activated.

== Usage ==
1. **Open Order**: Navigate to the WooCommerce order edit page for any order that you wish to split.
2. **Split Order**: Click on the "Split order" button. If the order status is selected in the settings, a new section will appear allowing you to specify quantities for the split.
3. **Specify Quantities**: Select orders and enter the quantities for each item that you want to move to the new orders.
4. **Create New Order**: Click the "Split it" button. The plugin will create new orders with the specified items and adjust the quantities in the original order.
5. **Settings**: Remember to set the options as you wish before split an order (WooCommerce > Settings > Orders)

== Screenshots ==

1. Intuitive and easy to use split order table.
2. Bulk return split orders to original one at Orders page.
3. Return split order and duplicate order at Edit Order page.
4. Split, orginal order labels at Orders page.
5. General Settings page.
6. Automation Settings page.
7. Nofitication Settings page.
8. New split order notification for the customer.

== Changelog ==

= 1.3.6 (Oct 27, 2024) =
* Fixed: Shop manager unable to open the split table.
* Fixed: Order actions are disable at order edit page.
* Fixed: Minor errors.
* Improved: Language file updated.

= 1.3.5 (Oct 22, 2024) =
* Fixed: Shop manager permission.
* Improved: Label updated.

= 1.3.4 (Sep 9, 2024) =
* Fixed: Shipping fee, permission options errors.

= 1.3.3 (Sep 5, 2024) =
* Improved: Minor improving.

= 1.3.2 (Aug 26, 2024) =
* New: Development - Added filter hooks for order meta.
* Fixed: The disable email option conflicts with WooCommerce option.
* Fixed: The new order status and copy logic when duplicating.
* Improved: Customer user included in the new orders.
* Improved: Some language strings are updated.

= 1.3.1 (Jul 26, 2024) =
* New: Duplicate order in Order actions in Edit Order page. 
* Fixed: The bulk return action only displays at the Orders page.
* Improved: The labels are updated.
* Improved: A minor typo issue was removed.

= 1.3.0 (Jul 17, 2024) =
* New: Split orders by category or stock status.
* New: Bulk return action is now working with HPOS.
* Fixed: Disable order email option affected to all the new orders.
* Improved: Moved the split container after the order total for better display.
* Improved: Added notes for the original and split orders.
* Improved: Added the settings url into the plugins page.
* Improved: Added the settings notice for the first installment.
* Improved: Removed unnecessary translation strings.

= 1.2.4 (Jul 9, 2024) =
* New: Option to enable/disable the order labels (WooCommerce > Settings > Orders)
* Fixed: Set the default settings for the first install.

= 1.2.3 =
* Improved: Optimize the orginal & splitted labels at Orders page.

= 1.2.2 =
* New: Added the original & splitted labels at Orders & Edit order pages.
* New: Added Bulk return action at Orders page.
* Improved: Remove splitted order ids when returns to the original order.
* Improved: Added the logic to handle the email sending actions for the sites using the filter to defer WooCommerce transactional emails.

= 1.2.1 =
* New: Split to multiple orders.
* Improved: Minor bug fixed.

= 1.2.0 =
* New: Brand new OOP codes under the hood.
* New: Permission option for shop manager.
* Improved: Avoid to split all quantities in the order.
* Improved: Small bugs fixed.

= 1.1.1 =
* New: Return the order back to the original.
* New: Set order statuses that allow to split or return.
Note: Only the 'processing' status has set as default. You have to go to the settings to set more if you wish.

### 1.1.0
* Order Splitter Settings added.
* Exclude shipping fee optional.
* Disable order email optional.
* Minor improving.

= 1.0.1 =
* A serious error fixed.

= 1.0 =
* First released.