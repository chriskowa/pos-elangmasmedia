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
                if (/^(fa|fas|far|fab|btn|col|container|d-|justify|align|text|border|bg-|table|nav|modal|dropdown|input|form|alert|pagination|badge|form-control|input-sm|input-lg|visible|hidden|active|disabled|carousel|glyph|accordion|panel|list-group|hide|select2|row|pull-right|paid_on|number|lead|close).*/.test(cls)) {
                    return cls;  // Return the class unchanged
                }
                // For all other classes, prepend 'm_' as well
                return 'm_' + cls;
            }).join(' ');
            elem.attr('class', modifiedClasses);
        }

        // Modify data-target attribute to start with #m_
        if (elem.attr('data-target')) {
            var targetValue = elem.attr('data-target');
            elem.attr('data-target', targetValue.replace(/^#/, '#m_'));
        }
    });

    // Modify id of the main element passed start with m_
    clonedElement.attr('id', 'm_' + clonedElement.attr('id'));

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
function updateAllRows() {
    // Check if elements with class .m_purchase_quantity exist
    if ($('.m_purchase_quantity').length > 0) {
    	$('.m_purchase_quantity').each(function() {
            row = $(this).closest('tr');
            handlePurchaseRow(row);
        });
    }
    // Check if elements with class .m_pos_quantity exist
    else if ($('.m_pos_quantity').length > 0) {
        $('.m_pos_quantity').each(function() {
            row = $(this).closest('tr');
            handlePosRow(row);
        });
    } else {
        // Neither class is found
        console.log("No elements with class .m_pos_quantity or .m_purchase_quantity exist on this page.");
    }
}

function handleRow(row){
	// Check if elements with class .m_purchase_quantity exist
    if ($('.m_purchase_quantity').length > 0) {
        handlePurchaseRow(row);
    }
    // Check if elements with class .m_pos_quantity exist
    else if ($('.m_pos_quantity').length > 0) {
        handlePosRow(row);
    } else {
        // Neither class is found
        console.log("No elements with class .m_pos_quantity or .m_purchase_quantity exist on this page.");
    }
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

$(document).on('click', '#m_toggle_additional_expense', function() {
    $('#m_additional_expenses_div').toggle();
});


//observe Multi currency change
handleMultiCurrencyChange();