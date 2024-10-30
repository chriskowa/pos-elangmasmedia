$(document).ready(function() {
    @include('multicurrency::inject.common-js')

    // Function to handle calculation for a row
    function handlePurchaseRow(row) {
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
            __read_number(row.find('.m_purchase_unit_cost_without_discount'), true) / m_exchange_rate;
        __write_number(
            row.find('.m_purchase_unit_cost_without_discount'),
            m_purchase_unit_cost_without_discount,
            true
        );

        var m_purchase_unit_cost = __read_number(row.find('.m_purchase_unit_cost'), true) / m_exchange_rate;
        __write_number(row.find('.m_purchase_unit_cost'), m_purchase_unit_cost, true);

        var m_row_subtotal_before_tax_hidden =
            __read_number(row.find('.m_row_subtotal_before_tax_hidden'), true) / m_exchange_rate;
        row.find('.m_row_subtotal_before_tax').text(
            multiCurrencyTransFromEn(m_row_subtotal_before_tax_hidden, false, true)
        );
        __write_number(
            row.find('input.m_row_subtotal_before_tax_hidden'),
            m_row_subtotal_before_tax_hidden,
            true
        );

        var m_purchase_product_unit_tax =
            __read_number(row.find('.m_purchase_product_unit_tax'), true) / m_exchange_rate;
        __write_number(row.find('input.m_purchase_product_unit_tax'), m_purchase_product_unit_tax, true);
        row.find('.m_purchase_product_unit_tax_text').text(
            multiCurrencyTransFromEn(m_purchase_product_unit_tax, false, true)
        );

        var m_purchase_unit_cost_after_tax =
            __read_number(row.find('.m_purchase_unit_cost_after_tax'), true) / m_exchange_rate;
        __write_number(
            row.find('input.m_purchase_unit_cost_after_tax'),
            m_purchase_unit_cost_after_tax,
            true
        );

        var m_row_subtotal_after_tax_hidden =
            __read_number(row.find('.m_row_subtotal_after_tax_hidden'), true) / m_exchange_rate;
        __write_number(
            row.find('input.m_row_subtotal_after_tax_hidden'),
            m_row_subtotal_after_tax_hidden,
            true
        );
        row.find('.m_row_subtotal_after_tax').text(
            multiCurrencyTransFromEn(m_row_subtotal_after_tax_hidden, false, true)
        );
    }

    function multiCurrencyInlineProfitPer(row) {
        //Update Profit percentage
        var default_sell_price = __read_number(row.find('input.m_default_sell_price'), true);
        var exchange_rate = $('input#m_exchange_rate').val();
        default_sell_price_in_base_currency = default_sell_price / parseFloat(exchange_rate);

        var purchase_after_tax = __read_number(row.find('input.m_purchase_unit_cost_after_tax'), true);
        var profit_percent = __get_rate(purchase_after_tax, default_sell_price_in_base_currency);
        __write_number(row.find('input.m_profit_percent'), profit_percent, true);
    }

    function multiCurrencyUpdateTabelTotal() {
        var m_total_quantity = 0;
        var m_total_st_before_tax = 0;
        var m_total_subtotal = 0;

        $('#purchase_entry_table tbody').find('tr').each(function() {
            if ($(this).find('.m_purchase_quantity').length > 0) {
                m_total_quantity += __read_number($(this).find('.m_purchase_quantity'), true);
                m_total_st_before_tax += __read_number(
                    $(this).find('.m_row_subtotal_before_tax_hidden'),
                    true
                );
                m_total_subtotal += __read_number($(this).find('.m_row_subtotal_after_tax_hidden'), true);
            }
        });

        $('#m_total_quantity').text(__number_f(m_total_quantity, false));
        $('#m_total_st_before_tax').text(multiCurrencyTransFromEn(m_total_st_before_tax, true, true));
        __write_number($('input#m_st_before_tax_input'), m_total_st_before_tax, true);

        $('#m_total_subtotal').text(multiCurrencyTransFromEn(m_total_subtotal, true, true));
        __write_number($('input#m_total_subtotal_input'), m_total_subtotal, true);
    }

    function multiCurrencyUpdateGrandTotal() {
        var m_exchange_rate = $('input#m_exchange_rate').val();
        if (!m_exchange_rate ) {
            m_exchange_rate = 1;
        }
        var m_st_before_tax = __read_number($('input#m_st_before_tax_input'), true);
        var m_total_subtotal = __read_number($('input#m_total_subtotal_input'), true);

        //Calculate Discount
        var m_discount_type = $('select#m_discount_type').val();
        m_discount_type && $('select#discount_type').val(m_discount_type).trigger('change');
        var m_discount_amount = __read_number($('input#m_discount_amount'), true);
        $('input#discount_amount').val(m_discount_amount * m_exchange_rate);
        var m_discount = __calculate_amount(m_discount_type, m_discount_amount, m_total_subtotal);
        $('#m_discount_calculated_amount').text(multiCurrencyTransFromEn(m_discount, true, true));

        //Calculate Tax
        var m_tax_rate = parseFloat($('option:selected', $('#m_tax_id')).data('tax_amount'));
        var m_tax = __calculate_amount('percentage', m_tax_rate, m_total_subtotal - m_discount);
        __write_number($('input#m_tax_amount'), m_tax);
        $('#m_tax_calculated_amount').text(multiCurrencyTransFromEn(m_tax, true, true));

        //Calculate shipping
        var m_shipping_charges = __read_number($('input#m_shipping_charges'), true);
        $('input#shipping_charges').val(m_shipping_charges * m_exchange_rate);
        //calculate additional expenses
        var m_additional_expense_1 = __read_number($('input#m_additional_expense_value_1'), true);
        $('input#additional_expense_value_1').val(m_additional_expense_1);
        var m_additional_expense_2 = __read_number($('input#m_additional_expense_value_2'), true);
        $('input#additional_expense_value_2').val(m_additional_expense_2);
        var m_additional_expense_3 = __read_number($('input#m_additional_expense_value_3'), true);
        $('input#additional_expense_value_3').val(m_additional_expense_3);
        var m_additional_expense_4 = __read_number($('input#m_additional_expense_value_4'), true);
        $('input#additional_expense_value_4').val(m_additional_expense_4);

        //Calculate Final total
        m_grand_total = m_total_subtotal - m_discount + m_tax + m_shipping_charges + 
        m_additional_expense_1 + m_additional_expense_2 + m_additional_expense_3 + m_additional_expense_4;

        __write_number($('input#m_grand_total_hidden'), m_grand_total, true);

        var m_payment = __read_number($('input.m_payment-amount'), true);

        var m_due = m_grand_total - m_payment;
        // __write_number($('input.payment-amount'), grand_total, true);

        $('#m_grand_total').text(multiCurrencyTransFromEn(m_grand_total, true, true));

        $('#m_payment_due').text(multiCurrencyTransFromEn(m_due, true, true));

        update_grand_total();

        //__currency_convert_recursively($(document));
    }

    function multiCurrencyQuantityChange(){
        //On Change of quantity
        $(document).on('change', '.m_purchase_quantity', function() {
            var m_row = $(this).closest('tr');
            var m_quantity = __read_number($(this), true);
            var m_purchase_before_tax = __read_number(m_row.find('input.m_purchase_unit_cost'), true);
            var m_purchase_after_tax = __read_number(
                m_row.find('input.m_purchase_unit_cost_after_tax'),
                true
            );

            //Calculate sub totals
            var m_sub_total_before_tax = m_quantity * m_purchase_before_tax;
            var m_sub_total_after_tax = m_quantity * m_purchase_after_tax;

            m_row.find('.m_row_subtotal_before_tax').text(
                multiCurrencyTransFromEn(m_sub_total_before_tax, false, true)
            );
            __write_number(
                m_row.find('input.m_row_subtotal_before_tax_hidden'),
                m_sub_total_before_tax,
                true
            );

            m_row.find('.m_row_subtotal_after_tax').text(
                multiCurrencyTransFromEn(m_sub_total_after_tax, false, true)
            );
            __write_number(m_row.find('input.m_row_subtotal_after_tax_hidden'), m_sub_total_after_tax, true);

            multiCurrencyUpdateTabelTotal();
            multiCurrencyUpdateGrandTotal();

            // Find the corresponding original row and trigger a change on its quantity input
            var originalRow = m_row.prev('tr'); // Assuming direct sequence
            originalRow.find('.purchase_quantity').val(m_quantity).trigger('change');
        });
    }

    //here is the start of the excute code

    // Observe changes happened on the table
    observeTable('#purchase_entry_table', true); // Set debug mode to true for the purchase table

    //basic cloned sections
    var totalTableClone = $('#total_subtotal_input').closest('table');
    cloneAndModifyElement(totalTableClone);
    
    var discountTableClone = $('#discount_amount').closest('table');
    cloneAndModifyElement(discountTableClone);

    var shippingChargesDivClone = $('#shipping_charges').closest('div');
    cloneAndModifyElement(shippingChargesDivClone);

    var additionalExpenseTableClone = $('#toggle_additional_expense').closest('div');
    cloneAndModifyElement(additionalExpenseTableClone);

    var grandTotalDivTableClone = $('#grand_total').closest('div');
    cloneAndModifyElement(grandTotalDivTableClone);

    var additionalExpenseDivTableClone = $('#additional_expenses_div');
    cloneAndModifyElement(additionalExpenseDivTableClone);

    var paymentDueTableClone = $('input.payment-amount').closest('div').parent();
    cloneAndModifyElement(paymentDueTableClone);

    var paymentDueTableClone = $('#payment_due').closest('div').parent();
    cloneAndModifyElement(paymentDueTableClone);

    //oserve quantity change
    multiCurrencyQuantityChange();

    $(document).on('change', '#m_tax_id, #m_discount_type, #m_discount_amount, input#m_shipping_charges, \
        #m_additional_expense_value_1, #m_additional_expense_value_2, \
        #m_additional_expense_value_3, #m_additional_expense_value_4', function() {
        multiCurrencyUpdateGrandTotal();
    });

    $(document).on('change', 'input.m_payment-amount', function() {
        var m_exchange_rate = $('input#m_exchange_rate').val();
        if (!m_exchange_rate ) {
            m_exchange_rate = 1;
        }
        var m_payment = __read_number($(this), true);
        var m_grand_total = __read_number($('input#m_grand_total_hidden'), true);
        var m_bal = m_grand_total - m_payment;
        $('#m_payment_due').text(multiCurrencyTransFromEn(m_bal, true, true));
        $('input.payment-amount').val(m_payment * m_exchange_rate).trigger('change');
    });

    
    

});
