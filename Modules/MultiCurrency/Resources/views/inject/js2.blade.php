$(document).ready(function() {
    // Function to configure and start observing a table
    function observeTable(tableId, debugMode = false) {
        var targetNode = document.querySelector(tableId + ' tbody');

        if (!targetNode) {
            if (debugMode) {
                console.error('Table with ID ' + tableId + ' not found.');
            }
            return;
        }

        $(tableId + ' tbody tr').each(function() {
            cloneAndModifyElement($(this), debugMode);
        });

        var observer = new MutationObserver(function(mutationsList, observer) {
            mutationsList.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.target.nodeName.toLowerCase() === 'tbody') {
                    mutation.addedNodes.forEach(function(node) {
                        if ($(node).is('tr') && !$(node).hasClass('is-cloned') && !$(node).hasClass('cloned-element')) {
                            if (debugMode) {
                                console.log('New <tr> element detected in ' + tableId + '.');
                                console.log('Inserted node:', node);
                            }
                            
                            var originalRow = $(node);

                            disconnectObserver();
                            cloneAndModifyElement($(node), debugMode);
                            connectObserver();

                            if (debugMode) {
                                console.log('Original row:', originalRow);
                            }
                        }
                    });
                }
            });
        });

        function disconnectObserver() {
            observer.disconnect();
        }

        function connectObserver() {
            observer.observe(targetNode, { childList: true });
        }

        connectObserver();
    }

    //clone function and handle change id, name, class, and select2 intite issues
    function cloneAndModifyElement(originalElement, debugMode = false) {
        // Check if the element or any of its similar siblings have already been cloned
        if (originalElement.hasClass('is-cloned')) {
            if (debugMode) {
                console.log('Element already cloned.');
            }
            return; // Exit the function if already cloned
        }

        var clonedElement = originalElement.clone();

        // Mark the original and cloned elements
        originalElement.addClass('is-cloned');
        clonedElement.addClass('cloned-element');
        
        // Update all IDs, names, and classes within the cloned row
        clonedElement.find('[id], [name], [for], [class]').each(function() {
            var elem = $(this);

            // Prepend 'm_' to IDs, unless the ID starts with 'select2'
            var elemId = elem.attr('id');
            if (elemId) { // Check if elemId is not undefined or null
                if (!elemId.startsWith('select2')) {
                    elem.attr('id', 'm_' + elemId);
                }
            }

            // Prepend 'm_' to names
            if (elem.attr('name')) {
                elem.attr('name', 'm_' + elem.attr('name'));
            }

            // Prepend 'm_' to the 'for' attribute in labels if any
            if (elem.attr('for')) {
                elem.attr('for', 'm_' + elem.attr('for'));
            }

            // Modify specific classes starting with 'row_'
            if (elem.attr('class')) {
                var classes = elem.attr('class').split(/\s+/);
                var modifiedClasses = classes.map(cls => {
                    // Check if the class starts with 'row_'
                    if (cls.startsWith("row_")) {
                        return 'm_' + cls;  // Prepend 'm_' to classes starting with 'row_'
                    }
                    // Check if the class matches common Bootstrap or framework patterns
                    if (/^(fa|fas|far|fab|btn|col|container|d-|justify|align|text|border|bg-|table|nav|modal|dropdown|input|form|alert|pagination|badge|form-control|input-sm|input-lg|visible|hidden|active|disabled|carousel|glyph|accordion|panel|list-group|hide|select2|row|pull-right|paid_on).*/.test(cls)) {
                        return cls;  // Return the class unchanged
                    }
                    // For all other classes, prepend 'm_' as well
                    return 'm_' + cls;
                }).join(' ');
                elem.attr('class', modifiedClasses);
            }
        });

        // Insert the modified clone after the original and optionally hide the original row
        originalElement.after(clonedElement);
        if (debugMode) {
            console.log('Cloned row:', clonedElement);
            console.log('Cloned row appended after the original row.');
        }

        // Capture styles from original Select2 and reinitialize Select2 with those styles on the clone
        originalElement.find('.select2-hidden-accessible').each(function() {
            var originalSelect = $(this);
            var originalSelect2Container = originalSelect.data('select2').$container;
            var originalWidth = originalSelect2Container.width();
            var originalStyles = {
                width: originalWidth,
                height: originalSelect2Container.css('height'),
                lineHeight: originalSelect2Container.css('line-height'),
                borderColor: originalSelect2Container.find('.select2-selection').css('border-color')
            };

            clonedElement.find('.select2-hidden-accessible').each(function() {
                var $select = $(this);
                // Safely destroy Select2 only if it's initialized
                if ($select.data('select2')) {
                    $select.select2('destroy');
                } else {
                    if (debugMode) {
                        console.log('Select2 not initialized on this element, skipping destroy.');
                    }
                }
            }).end().find('.select2-container').remove();

            // Reinitialize Select2 on cloned element
            var newSelect = clonedElement.find('#' + 'm_' + originalSelect.attr('id'));
            newSelect.select2(); // Initialize Select2

            // Apply captured styles to the new Select2 container
            var newSelect2Container = newSelect.data('select2').$container;
            newSelect2Container.css({
                'width': originalStyles.width + 'px', // Ensure width is the same
                'height': originalStyles.height,
                'line-height': originalStyles.lineHeight
            });

            newSelect2Container.find('.select2-selection').css({
                'border-color': originalStyles.borderColor
            });
        });
        // Reinitialize Select2 for select elements that had the plugin
        clonedElement.find('.select2-hidden-accessible').on('change', function() {
            if (debugMode) {
                console.log('Select2 value changed on cloned element!');
            }
            // Additional logic for handling changes can be implemented here
        });
        handleRow(clonedElement)
        originalElement.hide(); // Uncomment if you want to hide the original row
    }

    //handle multi currency change rate
    function handleMultiCurrencyChange(){
        $('#m_currency_id').on('change', function(){
            var currencyId = $(this).val();
            var rate = 1;
            $.ajax({
                url: '/exchange-rate/' + currencyId,
                method: 'POST',
                data: {id: currencyId},
                success: function(response){
                    if (response.success) {
                        if(response.data.type === 'api'){
                            // Call fetchApiCurrencyRate with a callback function to handle the response
                            fetchApiCurrencyRate(currencyId).done(function(apiResponse) {
                                if (apiResponse.success) {
                                    rate = apiResponse.rate;
                                    console.log("Return API rate:", rate);
                                    updateCurrencyFields(response.data, rate);
                                    updateAllRows();
                                }
                            });
                        }else{
                            rate = response.data.exchange_rate;
                            updateCurrencyFields(response.data, rate);
                            updateAllRows();
                        }
                    } else {
                        // Handle the scenario when success is false
                        console.error("Failed to fetch data: " + response.message);
                    }
                },
                error: function(xhr, status, error){
                    // Handle errors if any
                    console.error("Error: " + xhr.responseText);
                }
            });
        });
    }

    // Function to fetch currency rate from API
    function fetchApiCurrencyRate(currencyId) {
        // Create a deferred object
        var deferred = $.Deferred();

        $.ajax({
            url: '/api-currency-rate/' + currencyId,
            method: 'GET',
            success: function(apiResponse) {
                if (apiResponse.success) {
                    var rate = apiResponse.rates[apiResponse.code];
                    if (rate !== undefined) {
                        // Resolve the deferred object with success and the rate value
                        deferred.resolve({ success: true, rate: rate });
                    } else {
                        console.error("API currency rate fetch failed: Rate for the code is undefined");
                        // Reject the deferred object with an error message
                        deferred.reject("Rate for the code is undefined");
                    }
                } else {
                    console.error("API currency rate fetch failed.");
                    // Reject the deferred object with an error message
                    deferred.reject("API currency rate fetch failed");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching API currency rate: " + xhr.responseText);
                // Reject the deferred object with an error message
                deferred.reject("Error fetching API currency rate");
            }
        });

        // Return the promise associated with the deferred object
        return deferred.promise();
    }

    function updateCurrencyFields(response, rate) {
        $('#m_exchange_rate').val(rate);
        $('#m_code').val(response.code);
        $('#m_symbol').val(response.symbol);
        $('#m_thousand').val(response.thousand_separator);
        $('#m_decimal').val(response.decimal_separator);
    }

    //update all table calculation
    function updateAllRows(){
        $('.m_purchase_quantity').each(function() {
            row = $(this).closest('tr');
            handleRow(row);
        });
    }

    // Function to handle calculation for a row
    function handleRow(row) {
        multiCurrencyUpdateRowPrice(row);
        multiCurrencyInlineProfitPer(row);
        multiCurrencyUpdateTabelTotal();
        multiCurrencyUpdateGrandTotal();
    }

    //update row price based on multi currency exchange rate
    function multiCurrencyUpdateRowPrice(row) {
        var m_exchange_rate = $('input#m_exchange_rate').val();

        if (!m_exchange_rate || m_exchange_rate == 1) {
            return true;
        }

        var m_purchase_unit_cost_without_discount =
            __m_read_number(row.find('.m_purchase_unit_cost_without_discount'), true) / m_exchange_rate;
        __m_write_number(
            row.find('.m_purchase_unit_cost_without_discount'),
            m_purchase_unit_cost_without_discount,
            true
        );

        var m_purchase_unit_cost = __m_read_number(row.find('.m_purchase_unit_cost'), true) / m_exchange_rate;
        __m_write_number(row.find('.m_purchase_unit_cost'), m_purchase_unit_cost, true);

        var m_row_subtotal_before_tax_hidden =
            __m_read_number(row.find('.m_row_subtotal_before_tax_hidden'), true) / m_exchange_rate;
        row.find('.m_row_subtotal_before_tax').text(
            multiCurrencyTransFromEn(m_row_subtotal_before_tax_hidden, false, true)
        );
        __m_write_number(
            row.find('input.m_row_subtotal_before_tax_hidden'),
            m_row_subtotal_before_tax_hidden,
            true
        );

        var m_purchase_product_unit_tax =
            __m_read_number(row.find('.m_purchase_product_unit_tax'), true) / m_exchange_rate;
        __m_write_number(row.find('input.m_purchase_product_unit_tax'), m_purchase_product_unit_tax, true);
        row.find('.m_purchase_product_unit_tax_text').text(
            multiCurrencyTransFromEn(m_purchase_product_unit_tax, false, true)
        );

        var m_purchase_unit_cost_after_tax =
            __m_read_number(row.find('.m_purchase_unit_cost_after_tax'), true) / m_exchange_rate;
        __m_write_number(
            row.find('input.m_purchase_unit_cost_after_tax'),
            m_purchase_unit_cost_after_tax,
            true
        );

        var m_row_subtotal_after_tax_hidden =
            __m_read_number(row.find('.m_row_subtotal_after_tax_hidden'), true) / m_exchange_rate;
        __m_write_number(
            row.find('input.m_row_subtotal_after_tax_hidden'),
            m_row_subtotal_after_tax_hidden,
            true
        );
        row.find('.m_row_subtotal_after_tax').text(
            multiCurrencyTransFromEn(m_row_subtotal_after_tax_hidden, false, true)
        );
    }

    function multiCurrencyTransFromEn(
        input,
        show_symbol = true,
        use_page_currency = false,
        precision = __currency_precision,
        is_quantity = false
    ) {
        var m_s = $('#m_symbol').val();
        if ($('#m_symbol').val()) {
            console.log("M symbol presented");
            var s = m_s;
            var thousand = $('#m_thousand').val();
            var decimal = $('#m_decimal').val();
        } else {
            console.log("Default symbol presented");
            var s = __currency_symbol;
            var thousand = __currency_thousand_separator;
            var decimal = __currency_decimal_separator;
        }

        symbol = '';
        var format = '%s%v';
        if (show_symbol) {
            symbol = s;
            format = '%s %v';
            if (__currency_symbol_placement == 'after') {
                format = '%v %s';
            }
        }

        if (is_quantity) {
            precision = __quantity_precision;
        }

        return accounting.formatMoney(input, symbol, precision, thousand, decimal, format);
    }

    //Read input and convert it into natural number
    function __m_read_number(input_element, use_page_currency = false) {
        return __m_number_uf(input_element.val(), use_page_currency);
    }

    function __m_number_uf(input, use_page_currency = false) {
        if (use_page_currency && __currency_decimal_separator) {
            var decimal = $('#m_decimal').val();
        } else {
            var decimal = __currency_decimal_separator;
        }

        return accounting.unformat(input, decimal);
    }

    function __m_write_number(
        input_element,
        value,
        use_page_currency = false,
        precision = __currency_precision
    ) {
        if(input_element.hasClass('input_quantity')) {
            precision = __quantity_precision;
        }

        input_element.val(__m_number_f(value, false, use_page_currency, precision));
    }

    function __m_number_f(
        input,
        show_symbol = false,
        use_page_currency = false,
        precision = __currency_precision
    ) {
        return multiCurrencyTransFromEn(input, show_symbol, use_page_currency, precision);
    }

    function multiCurrencyInlineProfitPer(row) {
        //Update Profit percentage
        var m_default_sell_price = __m_read_number(row.find('input.m_default_sell_price'), true);
        var m_exchange_rate = $('input#m_exchange_rate').val();
        m_default_sell_price_in_base_currency = m_default_sell_price / parseFloat(m_exchange_rate);

        var m_purchase_after_tax = __m_read_number(row.find('input.m_purchase_unit_cost_after_tax'), true);
        var m_profit_percent = __get_rate(m_purchase_after_tax, m_default_sell_price_in_base_currency);
        __m_write_number(row.find('input.m_profit_percent'), m_profit_percent, true);
    }

    function multiCurrencyUpdateTabelTotal() {
        var m_total_quantity = 0;
        var m_total_st_before_tax = 0;
        var m_total_subtotal = 0;

        $('#purchase_entry_table tbody').find('tr').each(function() {
            if ($(this).find('.m_purchase_quantity').length > 0) {
                m_total_quantity += __m_read_number($(this).find('.m_purchase_quantity'), true);
                m_total_st_before_tax += __m_read_number(
                    $(this).find('.m_row_subtotal_before_tax_hidden'),
                    true
                );
                m_total_subtotal += __m_read_number($(this).find('.m_row_subtotal_after_tax_hidden'), true);
            }
        });

        $('#m_total_quantity').text(__m_number_f(m_total_quantity, false));
        $('#m_total_st_before_tax').text(multiCurrencyTransFromEn(m_total_st_before_tax, true, true));
        __m_write_number($('input#m_st_before_tax_input'), m_total_st_before_tax, true);

        $('#m_total_subtotal').text(multiCurrencyTransFromEn(m_total_subtotal, true, true));
        __m_write_number($('input#m_total_subtotal_input'), m_total_subtotal, true);
    }

    function multiCurrencyUpdateGrandTotal() {
        var m_st_before_tax = __m_read_number($('input#m_st_before_tax_input'), true);
        var m_total_subtotal = __m_read_number($('input#m_total_subtotal_input'), true);

        //Calculate Discount
        var m_discount_type = $('select#m_discount_type').val();
        m_discount_type && $('select#discount_type').val(m_discount_type).trigger('change');
        var m_discount_amount = __m_read_number($('input#m_discount_amount'), true);
        m_discount_amount && $('input#discount_amount').val(m_discount_amount);
        var m_discount = __calculate_amount(m_discount_type, m_discount_amount, m_total_subtotal);
        $('#m_discount_calculated_amount').text(multiCurrencyTransFromEn(m_discount, true, true));

        //Calculate Tax
        var m_tax_rate = parseFloat($('option:selected', $('#m_tax_id')).data('tax_amount'));
        var m_tax = __calculate_amount('percentage', m_tax_rate, m_total_subtotal - m_discount);
        __m_write_number($('input#m_tax_amount'), m_tax);
        $('#m_tax_calculated_amount').text(multiCurrencyTransFromEn(m_tax, true, true));

        //Calculate shipping
        var m_shipping_charges = __m_read_number($('input#m_shipping_charges'), true);
        $('input#shipping_charges').val(m_shipping_charges);
        //calculate additional expenses
        var m_additional_expense_1 = __m_read_number($('input#m_additional_expense_value_1'), true);
        $('input#additional_expense_value_1').val(m_additional_expense_1);
        var m_additional_expense_2 = __m_read_number($('input#m_additional_expense_value_2'), true);
        $('input#additional_expense_value_2').val(m_additional_expense_2);
        var m_additional_expense_3 = __m_read_number($('input#m_additional_expense_value_3'), true);
        $('input#additional_expense_value_3').val(m_additional_expense_3);
        var m_additional_expense_4 = __m_read_number($('input#m_additional_expense_value_4'), true);
        $('input#additional_expense_value_4').val(m_additional_expense_4);

        //Calculate Final total
        m_grand_total = m_total_subtotal - m_discount + m_tax + m_shipping_charges + 
        m_additional_expense_1 + m_additional_expense_2 + m_additional_expense_3 + m_additional_expense_4;

        __m_write_number($('input#m_grand_total_hidden'), m_grand_total, true);

        var m_payment = __m_read_number($('input.m_payment-amount'), true);

        var m_due = m_grand_total - m_payment;
        // __m_write_number($('input.payment-amount'), grand_total, true);

        $('#m_grand_total').text(multiCurrencyTransFromEn(m_grand_total, true, true));

        $('#m_payment_due').text(multiCurrencyTransFromEn(m_due, true, true));

        update_grand_total();

        //__currency_convert_recursively($(document));
    }

    function multiCurrencyQuantityChange(){
        //On Change of quantity
        $(document).on('change', '.m_purchase_quantity', function() {
            var m_row = $(this).closest('tr');
            var m_quantity = __m_read_number($(this), true);
            var m_purchase_before_tax = __m_read_number(m_row.find('input.m_purchase_unit_cost'), true);
            var m_purchase_after_tax = __m_read_number(
                m_row.find('input.m_purchase_unit_cost_after_tax'),
                true
            );

            //Calculate sub totals
            var m_sub_total_before_tax = m_quantity * m_purchase_before_tax;
            var m_sub_total_after_tax = m_quantity * m_purchase_after_tax;

            m_row.find('.m_row_subtotal_before_tax').text(
                multiCurrencyTransFromEn(m_sub_total_before_tax, false, true)
            );
            __m_write_number(
                m_row.find('input.m_row_subtotal_before_tax_hidden'),
                m_sub_total_before_tax,
                true
            );

            m_row.find('.m_row_subtotal_after_tax').text(
                multiCurrencyTransFromEn(m_sub_total_after_tax, false, true)
            );
            __m_write_number(m_row.find('input.m_row_subtotal_after_tax_hidden'), m_sub_total_after_tax, true);

            multiCurrencyUpdateTabelTotal();
            multiCurrencyUpdateGrandTotal();

            // Find the corresponding original row and trigger a change on its quantity input
            var originalRow = m_row.prev('tr'); // Assuming direct sequence
            originalRow.find('.purchase_quantity').val(m_quantity).trigger('change');
        });
    }

    //here is the start of the excute code

    // Call the function for each table with its respective configuration
    observeTable('#purchase_entry_table', true); // Set debug mode to true for the purchase table
    observeTable('#pos_table', true); // Set debug mode to true for the pos table

    //basic cloned sections
    var totalTableClone = $('#total_subtotal_input').closest('table');
    cloneAndModifyElement(totalTableClone);
    
    var discountTableClone = $('#discount_amount').closest('table');
    cloneAndModifyElement(discountTableClone);

    var shippingChargesTableClone = $('#shipping_charges').closest('.box-body');
    cloneAndModifyElement(shippingChargesTableClone);

    var paymentDueTableClone = $('input.payment-amount').closest('div').parent();
    cloneAndModifyElement(paymentDueTableClone);

    var paymentDueTableClone = $('#payment_due').closest('div').parent();
    cloneAndModifyElement(paymentDueTableClone);

    //observe Multi currency change
    handleMultiCurrencyChange();

    //oserve quantity change
    multiCurrencyQuantityChange();

    $(document).on('change', '#m_tax_id, #m_discount_type, #m_discount_amount, input#m_shipping_charges, \
        #m_additional_expense_value_1, #m_additional_expense_value_2, \
        #m_additional_expense_value_3, #m_additional_expense_value_4', function() {
        multiCurrencyUpdateGrandTotal();
    });

    $(document).on('change', 'input.m_payment-amount', function() {
        var m_payment = __m_read_number($(this), true);
        var m_grand_total = __m_read_number($('input#m_grand_total_hidden'), true);
        var m_bal = m_grand_total - m_payment;
        $('#m_payment_due').text(multiCurrencyTransFromEn(m_bal, true, true));
        $('input.payment-amount').val(m_payment).trigger('change')
    });
    

});
