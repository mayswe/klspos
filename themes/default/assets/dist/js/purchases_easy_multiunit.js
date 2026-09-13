/*
 * KLSPOS Easy Multi-Unit Purchase Entry
 * Replace the old purchases.min.js include on the Purchase Add page with this file.
 *
 * UI model:
 *   Quantity + Purchase Unit + Unit Cost
 *
 * Submitted fields remain compatible with the existing purchase controller:
 *   product_id[], primary_qty[], primary_unit[], secondary_qty[],
 *   secondary_unit[], cost[], transportation[]
 */
(function ($) {
    'use strict';

    var spoitems = window.spoitems || {};
    var productUnits = window.product_units || {};
    var productConversions = window.product_unit_conversions || window.product_conversions || {};
    var productUnitPrices = window.product_unit_prices || {};
    var rawAllUnits = window.all_units || {};
    var unitMap = {};

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : value).html();
    }

    function numberValue(value, fallback) {
        var parsed = parseFloat(value);
        return isFinite(parsed) ? parsed : (fallback || 0);
    }

    function formatNumber(value, decimals) {
        var parsed = numberValue(value, 0);
        var fixed = parsed.toFixed(typeof decimals === 'number' ? decimals : 4);
        return fixed.replace(/\.0+$/, '').replace(/(\.\d*?)0+$/, '$1');
    }

    function formatMoneySafe(value) {
        if (typeof window.formatMoney === 'function') {
            return window.formatMoney(numberValue(value, 0));
        }
        return numberValue(value, 0).toFixed(2);
    }

    function formatDecimalSafe(value) {
        if (typeof window.formatDecimal === 'function') {
            return window.formatDecimal(numberValue(value, 0));
        }
        return numberValue(value, 0).toFixed(2);
    }

    function localStore(key, value) {
        if (typeof window.store === 'function') {
            window.store(key, value);
        } else {
            localStorage.setItem(key, value);
        }
    }

    function localGet(key) {
        if (typeof window.get === 'function') {
            return window.get(key);
        }
        return localStorage.getItem(key);
    }

    function localRemove(key) {
        if (typeof window.remove === 'function') {
            window.remove(key);
        } else {
            localStorage.removeItem(key);
        }
    }

    function normalizeUnitMap() {
        unitMap = {};

        $.each(rawAllUnits, function (key, unit) {
            if (unit && typeof unit === 'object') {
                var id = unit.id || unit.unit_id || key;
                if (!id) return;
                unitMap[String(id)] = {
                    id: String(id),
                    name: unit.name || unit.unit_name || unit.label || unit.text || String(id),
                    code: unit.code || unit.unit_code || ''
                };
            } else if (key && unit) {
                unitMap[String(key)] = {
                    id: String(key),
                    name: String(unit),
                    code: ''
                };
            }
        });
    }

    function unitName(unitId) {
        var unit = unitMap[String(unitId)] || null;
        return unit ? unit.name : '';
    }

    function sourceForProduct(source, productId) {
        return source[productId] || source[String(productId)] || {};
    }

    function normalizeConversion(productId, unitId, value) {
        var id = unitId;
        var multiplier = 1;
        var operator = '*';
        var name = '';

        if (value && typeof value === 'object') {
            id = value.unit_id || value.secondary_unit_id || value.to_unit_id || value.id || unitId;
            multiplier = numberValue(
                value.operation_value !== undefined ? value.operation_value :
                (value.value !== undefined ? value.value : value.multiplier),
                1
            );
            operator = value.operator || value.operation || '*';
            name = value.unit_name || value.name || value.label || value.text || '';
        } else if (value !== undefined && value !== null && value !== '') {
            multiplier = numberValue(value, 1);
        }

        if (operator === '/') {
            multiplier = multiplier > 0 ? (1 / multiplier) : 1;
        }

        multiplier = multiplier > 0 ? multiplier : 1;

        return {
            id: String(id),
            multiplier: multiplier,
            name: name || unitName(id)
        };
    }

    function collectProductUnits(item, productId) {
        item.row = item.row || {};
        var found = {};
        var result = [];
        var baseUnitId = item.row.base_unit_id || item.row.primary_unit_id || item.row.unit_id || '';
        var baseUnitName = item.row.base_unit_name || unitName(baseUnitId);

        function addUnit(id, name, multiplier) {
            id = String(id || '');
            if (!id || found[id]) return;

            found[id] = true;
            result.push({
                id: id,
                name: name || unitName(id) || '-',
                multiplier: numberValue(multiplier, 1) > 0 ? numberValue(multiplier, 1) : 1
            });
        }

        if (baseUnitId) {
            addUnit(baseUnitId, baseUnitName, 1);
        }

        var sources = [
            item.units || {},
            sourceForProduct(productUnits, productId)
        ];

        $.each(sources, function (_, source) {
            $.each(source, function (key, value) {
                var id = key;
                var name = '';

                if (value && typeof value === 'object') {
                    id = value.id || value.unit_id || value.product_unit_id || value.secondary_unit_id || key;
                    name = value.name || value.unit_name || value.label || value.text || unitName(id);
                } else {
                    name = value || unitName(id);
                }

                addUnit(id, name, String(id) === String(baseUnitId) ? 1 : 1);
            });
        });

        var conversionSources = [
            item.unit_conversions || {},
            sourceForProduct(productConversions, productId)
        ];

        $.each(conversionSources, function (_, source) {
            $.each(source, function (key, value) {
                var conversion = normalizeConversion(productId, key, value);

                if (!baseUnitId && conversion.multiplier === 1) {
                    baseUnitId = conversion.id;
                    baseUnitName = conversion.name;
                }

                if (found[conversion.id]) {
                    $.each(result, function (index, existing) {
                        if (String(existing.id) === String(conversion.id)) {
                            existing.multiplier = conversion.multiplier;
                            if (conversion.name) existing.name = conversion.name;
                            return false;
                        }
                    });
                } else {
                    addUnit(conversion.id, conversion.name, conversion.multiplier);
                }
            });
        });

        if (!baseUnitId && result.length) {
            var oneToOne = null;
            $.each(result, function (_, candidate) {
                if (numberValue(candidate.multiplier, 1) === 1) {
                    oneToOne = candidate;
                    return false;
                }
            });
            baseUnitId = (oneToOne || result[result.length - 1]).id;
            baseUnitName = (oneToOne || result[result.length - 1]).name;
        }

        if (baseUnitId && !found[String(baseUnitId)]) {
            addUnit(baseUnitId, baseUnitName, 1);
        }

        result.sort(function (a, b) {
            if (b.multiplier !== a.multiplier) return b.multiplier - a.multiplier;
            return String(a.name).localeCompare(String(b.name));
        });

        item.row.base_unit_id = baseUnitId || '';
        item.row.base_unit_name = baseUnitName || unitName(baseUnitId) || '-';
        item.row.available_purchase_units = result;

        return result;
    }

    function conversionMultiplier(item, unitId) {
        var multiplier = 1;
        $.each(item.row.available_purchase_units || [], function (_, unit) {
            if (String(unit.id) === String(unitId)) {
                multiplier = numberValue(unit.multiplier, 1);
                return false;
            }
        });
        return multiplier > 0 ? multiplier : 1;
    }

    function unitLabel(item, unit) {
        var label = unit.name || '-';
        var baseName = item.row.base_unit_name || '-';
        if (numberValue(unit.multiplier, 1) !== 1) {
            label += ' (1 = ' + formatNumber(unit.multiplier, 4) + ' ' + baseName + ')';
        }
        return label;
    }

    function buildUnitOptions(item, selectedUnitId) {
        var html = '';
        $.each(item.row.available_purchase_units || [], function (_, unit) {
            html += '<option value="' + escapeHtml(unit.id) + '"' +
                (String(unit.id) === String(selectedUnitId) ? ' selected' : '') +
                ' data-multiplier="' + escapeHtml(unit.multiplier) + '">' +
                escapeHtml(unitLabel(item, unit)) + '</option>';
        });
        return html;
    }

    function defaultPurchaseUnit(item) {
        var units = item.row.available_purchase_units || [];
        var selected = item.row.purchase_unit || item.row.primary_unit || '';

        if (selected) {
            var exists = false;
            $.each(units, function (_, unit) {
                if (String(unit.id) === String(selected)) {
                    exists = true;
                    return false;
                }
            });
            if (exists) return String(selected);
        }

        return units.length ? String(units[0].id) : String(item.row.base_unit_id || '');
    }

    function configuredUnitCost(productId, unitId) {
        var source = sourceForProduct(productUnitPrices, productId);
        var value = source[unitId] || source[String(unitId)];

        if (value && typeof value === 'object') {
            value = value.cost || value.purchase_cost || value.price || value.unit_price || 0;
        }

        return numberValue(value, 0);
    }

    function suggestedUnitCost(item, productId, unitId) {
        var configured = configuredUnitCost(productId, unitId);
        if (configured > 0) return configured;

        var baseCost = numberValue(item.row.base_cost, 0);
        if (!baseCost) {
            baseCost = numberValue(item.row.cost, 0);
            item.row.base_cost = baseCost;
        }

        return baseCost * conversionMultiplier(item, unitId);
    }

    function baseQuantity(item) {
        return numberValue(item.row.purchase_qty, 0) * conversionMultiplier(item, item.row.purchase_unit);
    }

    function lineSubtotal(item) {
        return numberValue(item.row.purchase_qty, 0) * numberValue(item.row.cost, 0);
    }

    function linePreview(item) {
        var selectedName = unitName(item.row.purchase_unit);
        $.each(item.row.available_purchase_units || [], function (_, unit) {
            if (String(unit.id) === String(item.row.purchase_unit)) {
                selectedName = unit.name;
                return false;
            }
        });

        return formatNumber(item.row.purchase_qty, 4) + ' ' + (selectedName || '-') +
            ' = ' + formatNumber(baseQuantity(item), 4) + ' ' + (item.row.base_unit_name || '-');
    }

    function uniqueItemKey(productId) {
        return String(productId) + '_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
    }

    function itemKeyForNewLine(item, productId) {
        if (window.Settings && Number(window.Settings.item_addition) === 1) {
            return uniqueItemKey(productId);
        }
        return String(productId);
    }

    function persistItems() {
        localStore('spoitems', JSON.stringify(spoitems));
        window.spoitems = spoitems;
    }

    function calculateTransportTotal() {
        var total = 0;
        $.each(spoitems, function (_, item) {
            total += numberValue(item.row.transportation, 0);
        });
        return total;
    }

    function calculateGrandTotal() {
        var total = 0;
        $.each(spoitems, function (_, item) {
            total += lineSubtotal(item);
        });

        $('#gtotal, #erp_grand_total').text(formatMoneySafe(total));
        $('#advance_deducted').val(formatDecimalSafe(total));
        $('#delivery').val(formatDecimalSafe(calculateTransportTotal()));
        return total;
    }

    function emptyRowHtml() {
        return '<tr class="po-empty-row"><td colspan="8" class="text-center text-muted po-empty-cell">' +
            '<i class="fa fa-search"></i> ' + escapeHtml(window.easyPurchaseLabels.emptyItems) +
            '</td></tr>';
    }

    function loadItems() {
        var stored = localGet('spoitems');
        if (stored) {
            try {
                spoitems = JSON.parse(stored) || {};
            } catch (error) {
                spoitems = {};
            }
        }

        var $tbody = $('#poTable tbody');
        $tbody.empty();
        var hasItems = false;

        $.each(spoitems, function (itemKey, item) {
            item = item || {};
            item.row = item.row || {};

            var productId = item.row.id || item.id || item.item_id;
            if (!productId) return;

            hasItems = true;
            collectProductUnits(item, productId);

            item.row.purchase_qty = numberValue(
                item.row.purchase_qty !== undefined ? item.row.purchase_qty : item.row.primary_qty,
                1
            );
            item.row.purchase_unit = defaultPurchaseUnit(item);
            item.row.primary_qty = item.row.purchase_qty;
            item.row.primary_unit = item.row.purchase_unit;
            item.row.secondary_qty = 0;
            item.row.secondary_unit = 0;
            item.row.transportation = numberValue(item.row.transportation, 0);

            if (!item.row.cost || item.row.recalculate_cost === true) {
                item.row.cost = suggestedUnitCost(item, productId, item.row.purchase_unit);
                item.row.recalculate_cost = false;
            }

            var productName = item.row.name || item.label || '-';
            var productCode = item.row.code || item.code || '-';
            var subtotal = lineSubtotal(item);
            var rowId = 'po_row_' + String(itemKey).replace(/[^a-zA-Z0-9_-]/g, '_');

            var html = '';
            html += '<tr id="' + escapeHtml(rowId) + '" class="purchase-item-row" data-item-key="' + escapeHtml(itemKey) + '" data-product-id="' + escapeHtml(productId) + '">';
            html += '<td class="po-product-cell">';
            html += '<input type="hidden" name="product_id[]" value="' + escapeHtml(productId) + '">';
            html += '<div class="po-product-name">' + escapeHtml(productName) + '</div>';
            html += '<div class="po-product-code">' + escapeHtml(productCode) + '</div>';
            html += '</td>';

            html += '<td data-label="' + escapeHtml(window.easyPurchaseLabels.quantity) + '">';
            html += '<input type="number" class="form-control purchase-qty" min="0.0001" step="0.0001" value="' + escapeHtml(formatNumber(item.row.purchase_qty, 4)) + '" required>';
            html += '<input type="hidden" name="primary_qty[]" class="submit-primary-qty" value="' + escapeHtml(formatNumber(item.row.purchase_qty, 4)) + '">';
            html += '<input type="hidden" name="secondary_qty[]" value="0">';
            html += '</td>';

            html += '<td data-label="' + escapeHtml(window.easyPurchaseLabels.unit) + '">';
            html += '<select class="form-control purchase-unit" required>' + buildUnitOptions(item, item.row.purchase_unit) + '</select>';
            html += '<input type="hidden" name="primary_unit[]" class="submit-primary-unit" value="' + escapeHtml(item.row.purchase_unit) + '">';
            html += '<input type="hidden" name="secondary_unit[]" value="0">';
            html += '</td>';

            html += '<td data-label="' + escapeHtml(window.easyPurchaseLabels.unitCost) + '">';
            html += '<input type="number" class="form-control purchase-cost" name="cost[]" min="0" step="0.01" value="' + escapeHtml(numberValue(item.row.cost, 0).toFixed(2)) + '" required>';
            html += '</td>';

            html += '<td class="po-base-preview" data-label="' + escapeHtml(window.easyPurchaseLabels.baseQuantity) + '">';
            html += '<div class="base-preview-text">' + escapeHtml(linePreview(item)) + '</div>';
            html += '<input type="hidden" name="converted_base_qty[]" class="converted-base-qty" value="' + escapeHtml(formatNumber(baseQuantity(item), 4)) + '">';
            html += '</td>';

            html += '<td class="text-right po-subtotal" data-label="' + escapeHtml(window.easyPurchaseLabels.subtotal) + '">';
            html += '<span class="ssubtotal" data-subtotal="' + escapeHtml(subtotal) + '">' + formatMoneySafe(subtotal) + '</span>';
            html += '</td>';

            html += '<td data-label="' + escapeHtml(window.easyPurchaseLabels.transportation) + '">';
            html += '<input type="number" class="form-control transportation" name="transportation[]" min="0" step="0.01" value="' + escapeHtml(numberValue(item.row.transportation, 0).toFixed(2)) + '">';
            html += '</td>';

            html += '<td class="text-center po-delete-cell">';
            html += '<button type="button" class="btn btn-sm btn-danger spodel" title="' + escapeHtml(window.easyPurchaseLabels.delete) + '"><i class="fa fa-times"></i></button>';
            html += '</td>';
            html += '</tr>';

            $tbody.append(html);
        });

        if (!hasItems) {
            $tbody.html(emptyRowHtml());
        }

        persistItems();
        calculateGrandTotal();
    }

    function addOrderItem(item) {
        item = item || {};
        item.row = item.row || {};

        var productId = item.row.id || item.id || item.item_id;
        if (!productId) return false;

        if (!item.row.id) item.row.id = productId;
        if (!item.row.code) item.row.code = item.code || '';
        if (!item.row.name) item.row.name = item.label || item.name || '';

        if (item.units) productUnits[String(productId)] = item.units;
        if (item.unit_prices) productUnitPrices[String(productId)] = item.unit_prices;
        if (item.unit_conversions) productConversions[String(productId)] = item.unit_conversions;

        var itemKey = itemKeyForNewLine(item, productId);

        if (spoitems[itemKey] && !(window.Settings && Number(window.Settings.item_addition) === 1)) {
            spoitems[itemKey].row.purchase_qty = numberValue(spoitems[itemKey].row.purchase_qty, 0) + 1;
        } else {
            item.row.base_cost = numberValue(item.row.cost, 0);
            collectProductUnits(item, productId);
            item.row.purchase_unit = defaultPurchaseUnit(item);
            item.row.purchase_qty = 1;
            item.row.primary_qty = 1;
            item.row.primary_unit = item.row.purchase_unit;
            item.row.secondary_qty = 0;
            item.row.secondary_unit = 0;
            item.row.transportation = 0;
            item.row.cost = suggestedUnitCost(item, productId, item.row.purchase_unit);
            spoitems[itemKey] = item;
        }

        persistItems();
        loadItems();
        return true;
    }

    function updateItemFromRow($row) {
        var itemKey = String($row.data('item-key'));
        var item = spoitems[itemKey];
        if (!item) return;

        var productId = item.row.id;
        var oldUnitId = String(item.row.purchase_unit || '');
        var newUnitId = String($row.find('.purchase-unit').val() || '');
        var unitChanged = oldUnitId !== newUnitId;

        item.row.purchase_qty = numberValue($row.find('.purchase-qty').val(), 0);
        item.row.purchase_unit = newUnitId;
        item.row.primary_qty = item.row.purchase_qty;
        item.row.primary_unit = newUnitId;
        item.row.secondary_qty = 0;
        item.row.secondary_unit = 0;
        item.row.transportation = numberValue($row.find('.transportation').val(), 0);

        if (unitChanged) {
            item.row.cost = suggestedUnitCost(item, productId, newUnitId);
            $row.find('.purchase-cost').val(numberValue(item.row.cost, 0).toFixed(2));
        } else {
            item.row.cost = numberValue($row.find('.purchase-cost').val(), 0);
        }

        $row.find('.submit-primary-qty').val(formatNumber(item.row.purchase_qty, 4));
        $row.find('.submit-primary-unit').val(newUnitId);
        $row.find('.converted-base-qty').val(formatNumber(baseQuantity(item), 4));
        $row.find('.base-preview-text').text(linePreview(item));

        var subtotal = lineSubtotal(item);
        $row.find('.ssubtotal')
            .attr('data-subtotal', subtotal)
            .text(formatMoneySafe(subtotal));

        persistItems();
        calculateGrandTotal();
    }

    function setupAutocomplete() {
        if (!$.fn.autocomplete) return;

        $('#add_item').autocomplete({
            source: (window.base_url || '') + 'purchases/suggestions',
            minLength: 1,
            autoFocus: false,
            delay: 200,
            response: function (event, ui) {
                var content = ui.content || [];

                if (!content.length || (content.length === 1 && Number(content[0].id) === 0)) {
                    if ($.trim($(this).val()) !== '') {
                        bootbox.alert(window.lang && window.lang.no_match_found ? window.lang.no_match_found : window.easyPurchaseLabels.noMatch);
                    }
                    $(this).val('');
                    return;
                }

                if (content.length === 1 && Number(content[0].id) !== 0) {
                    ui.item = content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item && Number(ui.item.id) !== 0) {
                    addOrderItem(ui.item);
                    $(this).val('');
                }
            }
        });

        $('#add_item').on('keypress', function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                $(this).autocomplete('search');
            }
        });
    }

    function validateItems() {
        var valid = true;
        var hasItems = false;

        $('#poTable tbody .purchase-item-row').each(function () {
            hasItems = true;
            var $row = $(this);
            var qty = numberValue($row.find('.purchase-qty').val(), 0);
            var unit = $row.find('.purchase-unit').val();
            var cost = numberValue($row.find('.purchase-cost').val(), -1);

            $row.toggleClass('has-error', qty <= 0 || !unit || cost < 0);
            if (qty <= 0 || !unit || cost < 0) valid = false;
        });

        if (!hasItems) {
            bootbox.alert(window.easyPurchaseLabels.addAtLeastOne);
            return false;
        }

        if (!valid) {
            bootbox.alert(window.easyPurchaseLabels.checkItems);
        }

        return valid;
    }

    $(function () {
        normalizeUnitMap();

        if (localStorage.getItem('remove_spo')) {
            localRemove('spoitems');
            localStorage.removeItem('remove_spo');
        }

        loadItems();
        setupAutocomplete();
        $('#add_item').focus();

        if ($.fn.inputmask) {
            $('#date').inputmask('yyyy-mm-dd hh:mm', {placeholder: 'yyyy-mm-dd hh:mm'});
        }

        $(document).on('change keyup', '.purchase-qty, .purchase-unit, .purchase-cost, .transportation', function () {
            updateItemFromRow($(this).closest('.purchase-item-row'));
        });

        $(document).on('click', '.spodel', function () {
            var $row = $(this).closest('.purchase-item-row');
            var itemKey = String($row.data('item-key'));
            delete spoitems[itemKey];
            persistItems();
            loadItems();
        });

        $('#reset').on('click', function () {
            var message = window.lang && window.lang.r_u_sure ? window.lang.r_u_sure : window.easyPurchaseLabels.resetConfirm;
            bootbox.confirm(message, function (confirmed) {
                if (!confirmed) return;
                localRemove('spoitems');
                spoitems = {};
                loadItems();
                $('#add_item').focus();
            });
        });

        $('#purchase_add_form').on('submit', function (event) {
            $('#poTable tbody .purchase-item-row').each(function () {
                updateItemFromRow($(this));
            });

            if (!validateItems()) {
                event.preventDefault();
                return false;
            }
        });
    });

    window.add_order_item = addOrderItem;
    window.loadItems = loadItems;
    window.calculateGrandTotal = calculateGrandTotal;
})(jQuery);
