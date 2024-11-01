jQuery(document).ready(function($) {
    $('.split-order').on('click', function() {
        var orderId = woocommerce_admin_meta_boxes.post_id;

        // Toggle the display of the split order container
        var container = $('#split-order-container');
        if (container.is(':visible')) {
            container.hide();
            return;
        }

        // AJAX request to get order items
        $.ajax({
            url: splitOrderTranslations.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_order_items',
                order_id: orderId,
                nonce: splitOrderTranslations.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayOrderItems(response.data, container);
                } else {
                    alert(splitOrderTranslations.unableToFetch);
                }
            },
            error: function() {
                alert(splitOrderTranslations.errorOccurredFetchingOrder);
            }
        });
    });

    function displayOrderItems(items, container) {
        var splitMethod = $('#split-method').val() || 'default'; // Default value
        var showCategory = splitMethod === 'category';
        var showStockStatus = splitMethod === 'stock-status';

        var html = '<table class="woocommerce_order_items">';
        html += '<thead><tr><th class="product-column">' + splitOrderTranslations.product + '</th>';
        if (showCategory) {
            html += '<th class="category-column">' + splitOrderTranslations.category + '</th>';
        }
        if (showStockStatus) {
            html += '<th class="stock-status-column">' + splitOrderTranslations.stockStatus + '</th>';
        }
        html += '<th class="order-column">' + splitOrderTranslations.order + '</th><th class="quantity-column">' + splitOrderTranslations.quantity + '</th><th class="split-quantity-column">' + splitOrderTranslations.splitQuantity + '</th></tr></thead><tbody>';
    
        $.each(items, function(index, item) {
            html += '<tr>';
            html += '<td>' + item.name + '</td>';
            if (showCategory) {
                html += '<td>' + item.category + '</td>';
            }
            if (showStockStatus) {
                html += '<td>' + item.stock_status + '</td>';
            }
            html += '<td><select name="split_order[' + item.id + ']"' + (splitMethod !== 'default' ? ' disabled' : '') + '>';
            for (var i = 1; i <= 10; i++) {
                html += '<option value="' + splitOrderTranslations.newOrder + i + '">' + splitOrderTranslations.newOrder + i + '</option>';
            }
            html += '</select></td>';
            html += '<td>' + item.quantity + '</td>';
            html += '<td><input type="number" name="split_quantity[' + item.id + ']" min="0" max="' + item.quantity + '"' + (splitMethod !== 'default' ? ' disabled' : '') + '></td>';
            html += '</tr>';
        });
    
        html += '</tbody></table>';
        html += '<button type="button" class="button button-secondary cancel-split-order">' + splitOrderTranslations.cancel + '</button>';
        html += '<select id="split-method" class="split-method">';
        html += '<option value="default">' + splitOrderTranslations.default + '</option>';
        html += '<option value="category">' + splitOrderTranslations.category + '</option>';
        html += '<option value="stock-status">' + splitOrderTranslations.stockStatus + '</option>';
        html += '<option value="tags" disabled>' + splitOrderTranslations.tag + '</option>';
        html += '<option value="vendor" disabled>' + splitOrderTranslations.vendor + '</option>';
        html += '</select>';
        html += '<button type="button" class="button button-primary split-order-confirm">' + splitOrderTranslations.splitIt + '</button>';
    
        // Populate the container with the table and show it
        container.html(html).show();

        // Set the dropdown to the previously selected value
        $('#split-method').val(splitMethod);
    }

    // Event handler to disable/enable split quantity and order fields based on split method selection
    $(document).on('change', '#split-method', function() {
        var splitMethod = $(this).val();
        if (splitMethod !== 'default') {
            $('#split-order-container input[name^="split_quantity"]').prop('disabled', true);
            $('#split-order-container select[name^="split_order"]').prop('disabled', true);
        } else {
            $('#split-order-container input[name^="split_quantity"]').prop('disabled', false);
            $('#split-order-container select[name^="split_order"]').prop('disabled', false);
        }

        // Re-display order items to add/remove the category or stock status column
        var orderId = woocommerce_admin_meta_boxes.post_id;
        $.ajax({
            url: splitOrderTranslations.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_order_items',
                order_id: orderId,
                nonce: splitOrderTranslations.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayOrderItems(response.data, $('#split-order-container'));
                } else {
                    alert(splitOrderTranslations.unableToFetch);
                }
            },
            error: function() {
                alert(splitOrderTranslations.errorOccurredFetchingOrder);
            }
        });
    });

    // Event handler for the Cancel button
    $(document).on('click', '.cancel-split-order', function() {
        $('#split-order-container').hide();
    });
        
    // Event handler for 'Split it' button
    $(document).on('click', '.split-order-confirm', function() {
        var splitMethod = $('#split-method').val();
        var action = splitMethod === 'category' ? 'split_order_by_category' : (splitMethod === 'stock-status' ? 'split_order_by_stock_status' : 'split_order');
        var $button = $(this);
        var originalButtonText = $button.text();

        // Update button text to "Splitting..." and disable the button
        $button.text(splitOrderTranslations.splitting).prop('disabled', true);

        var orderId = woocommerce_admin_meta_boxes.post_id;
        var splitData = {};

        $('#split-order-container input[name^="split_quantity"]').each(function() {
            var itemId = $(this).attr('name').match(/\[(\d+)\]/)[1];
            var quantity = parseInt($(this).val(), 10);
            var selectedOrder = $('select[name="split_order[' + itemId + ']"]').val();

            if (quantity > 0) {
                splitData[itemId] = {
                    quantity: quantity,
                    order: selectedOrder
                };
            }
        });

        // AJAX request to create a new order with the split items
        $.ajax({
            url: splitOrderTranslations.ajaxUrl,
            type: 'POST',
            data: {
                action: action,
                order_id: orderId,
                split_data: splitData,
                nonce: splitOrderTranslations.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(splitOrderTranslations.orderSplitSuccess + ' ' + response.data.new_order_ids.join(', '));
                    window.location.reload();
                } else {
                    alert(response.data || splitOrderTranslations.failedToSplitOrder);
                }
                $button.text(originalButtonText).prop('disabled', false);
            },
            error: function() {
                alert(splitOrderTranslations.errorOccurred);
                $button.text(originalButtonText).prop('disabled', false);
            }
        });
    });
});
