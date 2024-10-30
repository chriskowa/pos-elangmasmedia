$(document).ready(function() {
    @include('multicurrency::inject.common-js')

    function handlePosRow(row){
        multiCurrencyUpdatePosPrice(row);
        multiCurrencyPosTotalRow();

    }

    //Update values for each row
    function multiCurrencyUpdatePosPrice(row) {
        var m_exchange_rate = $('input#m_exchange_rate').val();

        if (!m_exchange_rate || m_exchange_rate == 1) {
            return true;
        }

        var m_pos_unit_price = __m_read_number(row.find('input.m_pos_unit_price'), true) / m_exchange_rate;
        __m_write_number(row.find('input.m_pos_unit_price'),m_pos_unit_price,true);

        var m_discounted_unit_price = calculateDiscountedUnitPrice(row);
        var m_tax_rate = row
            .find('select.m_tax_id')
            .find(':selected')
            .data('rate');

        var m_unit_price_inc_tax = m_discounted_unit_price + __calculate_amount('percentage', m_tax_rate, m_discounted_unit_price);
        __m_write_number(row.find('input.m_pos_unit_price_inc_tax'), m_unit_price_inc_tax);
        
        var m_qty = __m_read_number(row.find('input.m_pos_quantity'));
        var m_line_total = m_qty * m_unit_price_inc_tax;

        __m_write_number(row.find('input.m_pos_line_total'), m_line_total, false, 2);
        row.find('span.m_pos_line_total_text').text(multiCurrencyTransFromEn(m_line_total, true));

        var discount = __m_read_number(row.find('input.m_row_discount_amount'));

        if (discount > 0) {
            __m_write_number(row.find('input.m_pos_line_total'), m_line_total);
        }

        //var unit_price_inc_tax = __m_read_number(row.find('input.pos_unit_price_inc_tax'));

        __m_write_number(row.find('input.m_item_tax'), m_unit_price_inc_tax - m_discounted_unit_price);

        // var m_line_total = __m_read_number(row.find('input.m_pos_line_total'));
        row.find('span.m_pos_line_total_text').text(m_line_total);
    }

    function calculateDiscountedUnitPrice(row) {
        var m_exchange_rate = $('input#m_exchange_rate').val();
        if (!m_exchange_rate ) {
            m_exchange_rate = 1;
        }
        var m_this_unit_price = __m_read_number(row.find('input.m_pos_unit_price'));
        var m_row_discounted_unit_price = m_this_unit_price;
        var m_row_discount_type = row.find('select.m_row_discount_type').val();
        var m_row_discount_amount = __m_read_number(row.find('input.m_row_discount_amount'));
        
        if (m_row_discount_type == 'fixed') {
            m_row_discounted_unit_price = m_this_unit_price - m_row_discount_amount;
            row.prev('tr').find('input.row_discount_amount').val(m_row_discount_amount * m_exchange_rate);
        } else {
            m_row_discounted_unit_price = __substract_percent(m_this_unit_price, m_row_discount_amount);
            row.prev('tr').find('input.row_discount_amount').val(m_row_discount_amount);
        }

        m_row_discount_type && row.prev('tr').find('select.row_discount_type').val(m_row_discount_type).trigger('change');

        return m_row_discounted_unit_price;
    }

    function multiCurrencyPosTotalRow() {//pos_total_row()
        var m_total_quantity = 0;
        var m_price_total = multiCurrencyGetSubtotal();
        $('table#pos_table tbody tr').each(function() {
            m_total_quantity = m_total_quantity + __m_read_number($(this).find('input.m_pos_quantity'));
        });

        //updating shipping charges
        $('span#m_shipping_charges_amount').text(
            multiCurrencyTransFromEn(__m_read_number($('input#m_shipping_charges_modal')), false)
        );

        $('span.m_total_quantity').each(function() {
            $(this).html(__m_number_f(m_total_quantity));
        });

        //$('span.unit_price_total').html(unit_price_total);
        $('span.m_price_total').html(multiCurrencyTransFromEn(m_price_total, false));
        multiCurrencyCalculateBillingDetails(m_price_total);
    }

    function multiCurrencyCalculateBillingDetails(m_price_total) {
        var m_exchange_rate = $('input#m_exchange_rate').val();
        if (!m_exchange_rate ) {
            m_exchange_rate = 1;
        }
        var m_discount = multiCurrencyPosDiscount(m_price_total);
        if ($('#m_reward_point_enabled').length) {
            m_total_customer_reward = $('#rp_redeemed_amount').val();
            m_discount = parseFloat(m_discount) + parseFloat(m_total_customer_reward);

            if ($('input[name="is_direct_sale"]').length <= 0) {
                $('span#total_discount').text(multiCurrencyTransFromEn(m_discount, false));
            }
        }

        var m_order_tax = multiCurrencyPosOrderTax(m_price_total, m_discount);

        //Add shipping charges.
        var m_shipping_charges = __m_read_number($('input#m_shipping_charges'));
        $('input#shipping_charges').val(m_shipping_charges * m_exchange_rate).trigger('change');

        var m_additional_expense = 0;
        //calculate additional expenses
        if ($('input#additional_expense_value_1').length > 0) {
            m_additional_expense += __m_read_number($('input#m_additional_expense_value_1'));
            $('input#additional_expense_value_1').val($('input#m_additional_expense_value_1').val());
        }
        if ($('input#additional_expense_value_2').length > 0) {
            m_additional_expense += __m_read_number($('input#m_additional_expense_value_2'))
            $('input#additional_expense_value_2').val($('input#m_additional_expense_value_2').val());
        }
        if ($('input#additional_expense_value_3').length > 0) {
            m_additional_expense += __m_read_number($('input#m_additional_expense_value_3'))
            $('input#additional_expense_value_3').val($('input#m_additional_expense_value_3').val());
        }
        if ($('input#additional_expense_value_4').length > 0) {
            m_additional_expense += __m_read_number($('input#m_additional_expense_value_4'))
            $('input#additional_expense_value_4').val($('input#m_additional_expense_value_4').val());
        }

        //Add packaging charge
        var m_packing_charge = 0;
        if ($('#types_of_service_id').length > 0 && 
                $('#types_of_service_id').val()) {
            m_packing_charge = __calculate_amount($('#packing_charge_type').val(), 
                __read_number($('input#packing_charge')), m_price_total);

            $('#packing_charge_text').text(multiCurrencyTransFromEn(m_packing_charge, false));
        }

        var total_payable = m_price_total + m_order_tax - m_discount + m_shipping_charges + m_packing_charge + m_additional_expense;

        var rounding_multiple = $('#amount_rounding_method').val() ? parseFloat($('#amount_rounding_method').val()) : 0;
        var round_off_data = __round(total_payable, rounding_multiple);
        var total_payable_rounded = round_off_data.number;

        var round_off_amount = round_off_data.diff;
        if (round_off_amount != 0) {
            $('span#round_off_text').text(multiCurrencyTransFromEn(round_off_amount, false));
        } else {
            $('span#round_off_text').text(0);
        }
        $('input#round_off_amount').val(round_off_amount);

        __m_write_number($('input#m_final_total_input'), total_payable_rounded);
        $('input#final_total_input').val(total_payable_rounded * m_exchange_rate);
        
        $('span#m_total_payable').text(multiCurrencyTransFromEn(total_payable_rounded, false));

        $('span.m_total_payable_span').text(multiCurrencyTransFromEn(total_payable_rounded, true));

        //Check if edit form then don't update price.
        if ($('form#edit_pos_sell_form').length == 0 && $('form#edit_sell_form').length == 0) {
            __m_write_number($('.m_payment-amount').first(), total_payable_rounded);
        }

        $(document).trigger('invoice_total_calculated');

        multiCurrencyCalculateBalanceDue();
    }

    function multiCurrencyCalculateBalanceDue() {
        var m_exchange_rate = $('input#m_exchange_rate').val();
        if (!m_exchange_rate ) {
            m_exchange_rate = 1;
        }
        var total_payable = __m_read_number($('#m_final_total_input'));
        var total_paying = 0;
        $('#payment_rows_div')
            .find('.m_payment-amount')
            .each(function() {
                // if (parseFloat($(this).val())) {
                    $('.payment-amount').val($(this).val() * m_exchange_rate).trigger('change');
                    total_paying += __read_number($(this));
                // }
            });
        var bal_due = total_payable - total_paying;
        var change_return = 0;

        //change_return
        if (bal_due < 0 || Math.abs(bal_due) < 0.05) {
            __m_write_number($('input#m_change_return'), bal_due * -1);
            $('span.m_change_return_span').text(multiCurrencyTransFromEn(bal_due * -1, true));
            change_return = bal_due * -1;
            bal_due = 0;
        } else {
            __m_write_number($('input#m_change_return'), 0);
            $('span.m_change_return_span').text(multiCurrencyTransFromEn(0, true));
            change_return = 0;
            
        }

        if (change_return !== 0) {
            $('#change_return_payment_data').removeClass('hide');
        } else {
            $('#change_return_payment_data').addClass('hide');
        }

        __m_write_number($('input#total_paying_input'), total_paying);
        $('span.total_paying').text(multiCurrencyTransFromEn(total_paying, true));

        __m_write_number($('input#m_in_balance_due'), bal_due);
        $('span.m_balance_due').text(multiCurrencyTransFromEn(bal_due, true));

        __highlight(bal_due * -1, $('span.m_balance_due'));
        __highlight(change_return * -1, $('span.m_change_return_span'));

        calculate_balance_due();
    }

    function multiCurrencyPosDiscount(total_amount) {
        var m_exchange_rate = $('input#m_exchange_rate').val();
        if (!m_exchange_rate ) {
            m_exchange_rate = 1;
        }
        var m_calculation_type = $('#m_discount_type').val();
        var m_calculation_amount = __m_read_number($('#m_discount_amount'));

        var m_discount = __calculate_amount(m_calculation_type, m_calculation_amount, total_amount);

        $('span#m_total_discount').text(multiCurrencyTransFromEn(m_discount, false));
        if (m_calculation_type == 'fixed') {
            $('#discount_amount').val(m_calculation_amount * m_exchange_rate);
        }else{
            $('#discount_amount').val(m_calculation_amount);
        }

        $('#discount_type').val(m_calculation_type).trigger('change');

        return m_discount;
    }

    function multiCurrencyPosOrderTax(price_total, discount) {
        var tax_rate_id = $('#m_tax_rate_id').val();
        var calculation_type = 'percentage';
        var calculation_amount = __m_read_number($('#m_tax_calculation_amount'));
        var total_amount = price_total - discount;

        if (tax_rate_id) {
            var order_tax = __calculate_amount(calculation_type, calculation_amount, total_amount);
        } else {
            var order_tax = 0;
        }

        $('span#order_tax').text(multiCurrencyTransFromEn(order_tax, false));

        return order_tax;
    }

    function multiCurrencyPosQuantityChange(){
        //Update line total and check for quantity not greater than max quantity
        $('table#pos_table tbody').on('change', 'input.m_pos_quantity', function() {
            if (sell_form_validator) {
                sell_form.valid();
            }
            if (pos_form_validator) {
                pos_form_validator.element($(this));
            }
            // var max_qty = parseFloat($(this).data('rule-max'));
            var m_entered_qty = __m_read_number($(this));

            var m_tr = $(this).parents('tr');

            var m_unit_price_inc_tax = __m_read_number(m_tr.find('input.m_pos_unit_price_inc_tax'));
            var m_line_total = m_entered_qty * m_unit_price_inc_tax;

            __m_write_number(m_tr.find('input.m_pos_line_total'), m_line_total, false, 2);
            m_tr.find('span.m_pos_line_total_text').text(multiCurrencyTransFromEn(m_line_total, true));

            //Change modifier quantity
            m_tr.find('.m_modifier_qty_text').each( function(){
                $(this).text(multiCurrencyTransFromEn(m_entered_qty, false));
            });
            m_tr.find('.m_modifiers_quantity').each( function(){
                $(this).val(m_entered_qty);
            });

            multiCurrencyPosTotalRow();

            multiCurrencyAdjustComboQty(m_tr);

            // Find the corresponding original row and trigger a change on its quantity input
            var originalRow = m_tr.prev('tr'); // Assuming direct sequence
            if(originalRow.find('.pos_quantity').val() != m_entered_qty){
                originalRow.find('.pos_quantity').val(m_entered_qty).trigger('change');
            }
        });
    }

    //Input number
    $(document).on(
        'click',
        '.input-number .m_quantity-up, .input-number .m_quantity-down',
        function () {
            var m_input = $(this).closest('.input-number').find('input');
            var m_qty = __m_read_number(m_input);
            var m_step = 1;
            if (m_input.data('step')) {
                m_step = m_input.data('step');
            }
            var m_min = parseFloat(m_input.data('min'));
            var m_max = parseFloat(m_input.data('max'));

            if ($(this).hasClass('m_quantity-up')) {
                //if max reached return false
                if (typeof m_max != 'undefined' && m_qty + m_step > m_max) {
                    return false;
                }

                __m_write_number(m_input, m_qty + m_step);
                m_input.change();
                $(this).prev('tr').find('.input-number').val(m_qty + m_step).trigger('change');
            } else if ($(this).hasClass('m_quantity-down')) {
                //if max reached return false
                if (typeof m_min != 'undefined' && m_qty - m_step < m_min) {
                    return false;
                }

                __m_write_number(m_input, m_qty - m_step);
                m_input.change();
                $(this).prev('tr').find('.input-number').val(m_qty - m_step).trigger('change');
            }
        }
    );

    function multiCurrencyAdjustComboQty(tr){
        if(tr.find('input.m_product_type').val() == 'combo'){
            var qty = __m_read_number(tr.find('input.m_pos_quantity'));
            var multiplier = __m_getUnitMultiplier(tr);

            tr.find('input.combo_product_qty').each(function(){
                $(this).val($(this).data('unit_quantity') * qty * multiplier);
            });
        }
    }

    function __getUnitMultiplier(row){
        m_multiplier = row.find('select.m_sub_unit').find(':selected').data('multiplier');
        if(m_multiplier == undefined){
            return 1;
        } else {
            return parseFloat(m_multiplier);
        }
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

    function multiCurrencyGetSubtotal() {
        var price_total = 0;

        $('table#pos_table tbody tr').each(function() {
            price_total = price_total + __m_read_number($(this).find('input.m_pos_line_total'));
        });

        //Go through the modifier prices.
        $('input.modifiers_price').each(function() {
            var modifier_price = __m_read_number($(this));
            var modifier_quantity = $(this).closest('.m_product_modifier').find('.m_modifiers_quantity').val();
            var modifier_subtotal = modifier_price * modifier_quantity;
            price_total = price_total + modifier_subtotal;
        });

        return price_total;
    }

    function _m_round_row_to_iraqi_dinnar(row) {
        if (iraqi_selling_price_adjustment) {
            var element = row.find('input.m_pos_unit_price_inc_tax');
            var unit_price = round_to_iraqi_dinnar(__m_read_number(element));
            __m_write_number(element, unit_price);
            element.change();
        }
    }

    // Observe changes happened on the table
    observeTable('#pos_table', true); // Set debug mode to true for the pos table

    //basic cloned sections
    var priceTotalTrClone = $('.price_total').closest('tr');
    cloneAndModifyElement(priceTotalTrClone);

    // var discountTypeDivClone = $('#discount_type').closest('div');
    var discountTypeDivClone = $('#discount_type').parent();
    cloneAndModifyElement(discountTypeDivClone);

    // var discountTableClone = $('#discount_amount').closest('div');
    var discountTableClone = $('#discount_amount').parent();
    cloneAndModifyElement(discountTableClone);

    var totalDiscountDivClone = $('#total_discount').parent();
    cloneAndModifyElement(totalDiscountDivClone);

    var shippingChargesDivClone = $('#shipping_charges').parent();
    cloneAndModifyElement(shippingChargesDivClone);

    var additionalExpenseTableClone = $('#toggle_additional_expense').closest('div');
    cloneAndModifyElement(additionalExpenseTableClone);

    var additionalExpenseDivTableClone = $('#additional_expense_key_1').closest('div');
    cloneAndModifyElement(additionalExpenseDivTableClone);

    var finalTotalDivTableClone = $('#final_total_input').closest('div');
    cloneAndModifyElement(finalTotalDivTableClone);

    var paymentDueTableClone = $('input.payment-amount').closest('div').parent();
    cloneAndModifyElement(paymentDueTableClone);

    var changeReturnClone = $('#change_return').closest('div');
    cloneAndModifyElement(changeReturnClone);

    var balanceDueClone = $('.balance_due').closest('div').parent();
    cloneAndModifyElement(balanceDueClone);

    var posEditDiscountModaleClone = $('#posEditDiscountModal');
    cloneAndModifyElement(posEditDiscountModaleClone);

    var posShippingModalClone = $('#posShippingModal');
    cloneAndModifyElement(posShippingModalClone);

    //oserve quantity change
    multiCurrencyPosQuantityChange()

    //Change in row discount type or discount amount
    $('table#pos_table tbody').on('change','select.m_row_discount_type, input.m_row_discount_amount',function() {
            var tr = $(this).parents('tr');

            //calculate discounted unit price
            var m_discounted_unit_price = calculateDiscountedUnitPrice(tr);

            var m_tax_rate = tr.find('select.m_tax_id').find(':selected').data('rate');
            var m_quantity = __m_read_number(tr.find('input.m_pos_quantity'));

            var m_unit_price_inc_tax = __add_percent(m_discounted_unit_price, m_tax_rate);
            var m_line_total = m_quantity * m_unit_price_inc_tax;

            __m_write_number(tr.find('input.m_pos_unit_price_inc_tax'), m_unit_price_inc_tax);
            __m_write_number(tr.find('input.m_pos_line_total'), m_line_total, false, 2);
            tr.find('span.m_pos_line_total_text').text(multiCurrencyTransFromEn(m_line_total, true));
            // multiCurrencyUpdatePosPrice(tr);
            multiCurrencyPosTotalRow();
            _m_round_row_to_iraqi_dinnar(tr);
        }
    );

    $('select#m_discount_type, input#m_discount_amount, input#m_shipping_charges, \
        input#m_rp_redeemed_amount').change(function() {
        multiCurrencyPosTotalRow();
    });

    $(document).on('change', 'input#m_packing_charge, #m_additional_expense_value_1, #m_additional_expense_value_2, \
    #m_additional_expense_value_3, #m_additional_expense_value_4', function() {
        multiCurrencyPosTotalRow();
    });

    $(document).on('change', '.m_payment-amount', function() {
        multiCurrencyCalculateBalanceDue();
    });

    //Update discount
    $('button#m_posEditDiscountModalUpdate').click(function() {
        var m_exchange_rate = $('input#m_exchange_rate').val();
        if (!m_exchange_rate ) {
            m_exchange_rate = 1;
        }

        //if discount amount is not valid return false
        if (!$("#m_discount_amount_modal").valid()) {
            return false;
        }
        //Close modal
        $('div#m_posEditDiscountModal').modal('hide');

        //Update values
        $('input#m_discount_type').val($('select#m_discount_type_modal').val());
        __m_write_number($('input#m_discount_amount'), __read_number($('input#m_discount_amount_modal')));

        if ($('select#m_discount_type_modal').val() == 'fixed') {
            $('input#discount_amount_modal').val($('input#m_discount_amount_modal').val() * m_exchange_rate);
        }else{
            $('input#discount_amount_modal').val($('input#m_discount_amount_modal').val());
        }

        $('select#discount_type_modal').val($('select#m_discount_type_modal').val()).trigger('change');

        if ($('#m_reward_point_enabled').length) {
            var reward_validation = isValidatRewardPoint();
            if (!reward_validation['is_valid']) {
                toastr.error(reward_validation['msg']);
                $('#rp_redeemed_modal').val(0);
                $('#rp_redeemed_modal').change();
            }
            updateRedeemedAmount();
        }

        multiCurrencyPosTotalRow();
    });

     //Shipping
    $('button#m_posShippingModalUpdate').click(function() {
        var m_exchange_rate = $('input#m_exchange_rate').val();
        if (!m_exchange_rate ) {
            m_exchange_rate = 1;
        }

        //Close modal
        $('div#m_posShippingModal').modal('hide');

        //update shipping Modal details
        $('#shipping_details_modal').val($('#m_shipping_details_modal').val());
        $('#shipping_address_modal').val($('#m_shipping_address_modal').val());
        $('#shipping_status_modal').val($('#m_shipping_status_modal').val());
        $('#delivered_to_modal').val($('#m_delivered_to_modal').val());

        //update shipping details
        $('input#shipping_details').val($('#shipping_details_modal').val());
        $('input#shipping_address').val($('#shipping_address_modal').val());
        $('input#shipping_status').val($('#shipping_status_modal').val());
        $('input#delivered_to').val($('#delivered_to_modal').val());

        //Update shipping charges
        __m_write_number($('input#shipping_charges_modal'),__m_read_number($('input#m_shipping_charges_modal')) * m_exchange_rate);

        $('input#shipping_charges').val($('input#shipping_charges_modal').val()).trigger('change');

        multiCurrencyPosTotalRow();
    });

    $('table#pos_table tbody').on('change', 'input.pos_quantity', function() {
        var cloned_tr = $(this).parents('tr');
        var qty = __m_read_number($(this));
        var clonedRow = cloned_tr.next('tr'); // Assuming direct sequence
        if(clonedRow.find('.m_pos_quantity').val() != qty){
            clonedRow.find('.m_pos_quantity').val(qty).trigger('change');
        }
    });



});