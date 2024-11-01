jQuery(document).ready(function($) {
    // Adding a slight delay to ensure elements are present
    setTimeout(function() {
        // Iterate through each order row
        $('tr.type-shop_order').each(function() {
            var orderId;

            // Check for HPOS or legacy ID format
            if ($(this).attr('id').startsWith('post-')) {
                orderId = $(this).attr('id').replace('post-', '');
            } else if ($(this).attr('id').startsWith('order-')) {
                orderId = $(this).attr('id').replace('order-', '');
            }

            // Check if orderId is a valid number
            if (!isNaN(orderId)) {
                // Normalize order ID to integer for matching
                orderId = parseInt(orderId, 10);

                // Check if the order ID exists in the localized data
                if (yoohw_order_data.hasOwnProperty(orderId)) {
                    var orderData = yoohw_order_data[orderId];

                    // Add class based on meta value
                    if (orderData.original_order) {
                        $(this).find('a.order-view').addClass('yoohw-split-order').attr('data-original-order', orderData.original_order);
                    }

                    if (orderData.splitted_order) {
                        $(this).find('a.order-view').addClass('yoohw-splitted-order').attr('data-splitted-order', orderData.splitted_order);
                    }
                }
            }
        });

        // Add custom CSS
        $('<style>')
            .prop('type', 'text/css')
            .html('\
                tr.type-shop_order a.order-view.yoohw-split-order::after {\
                    content: "O#" attr(data-original-order);\
                    font-size: 10px;\
                    text-shadow: none;\
                    color: #ffffff;\
                    background-color: #dba617;\
                    padding: 0 5px;\
                    margin-left: 5px;\
                    border-radius: 5px;\
                    display: inline-block;\
                }\
                tr.type-shop_order a.order-view.yoohw-splitted-order::after {\
                    content: "S#" attr(data-splitted-order);\
                    font-size: 10px;\
                    text-shadow: none;\
                    color: #ffffff;\
                    background-color: #00a32a;\
                    padding: 0 5px;\
                    margin-left: 5px;\
                    border-radius: 5px;\
                    display: inline-block;\
                }')
            .appendTo('head');
    }, 500); // Adjust the delay as needed
});