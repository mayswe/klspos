<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>
<?php
$opening_stock_layout = isset($opening_stock_layout) ? $opening_stock_layout : 'mobile';
$is_mobile_layout = $opening_stock_layout === 'mobile';
?>

<style>
.os-card{overflow:hidden;border:1px solid #e5e7eb;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.06)}
.os-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;border-bottom:1px solid #e5e7eb}
.os-title{margin:0;color:#0f172a;font-size:20px;font-weight:800}
.os-table-wrap{padding:16px}
#openingStockTable{margin:0}
#openingStockTable .form-control{height:42px;border-color:#cbd5e1;border-radius:8px;box-shadow:none}
.os-secondary-wrap{position:relative;min-width:155px}
.os-secondary-input{padding-right:65px!important;border-color:#99d8cf!important;background:#f8fffd!important}
.os-secondary-unit{position:absolute;top:50%;right:11px;max-width:55px;overflow:hidden;transform:translateY(-50%);color:#0f766e;font-size:13px;font-weight:800;text-overflow:ellipsis;white-space:nowrap;pointer-events:none}
.os-not-dual{color:#94a3b8;text-align:center;font-weight:700}
.os-footer{display:flex;gap:10px;padding:0 16px 18px}
.os-help{margin:0 16px 16px;padding:11px 13px;border:1px solid #99f6e4;border-radius:10px;background:#f0fdfa;color:#115e59;font-size:13px;font-weight:700}
.opening-row.has-error{background:#fff7f7}
.os-mobile .os-table-wrap{overflow:visible;width:100%;max-width:none;margin:0;padding:14px}
.os-mobile #openingStockTable,.os-mobile #openingStockTable tbody{display:block;width:100%}
.os-mobile #openingStockTable thead{display:none}
.os-mobile #openingStockTable tbody{display:grid;grid-template-columns:1fr;gap:14px}
.os-mobile #openingStockTable .opening-row{position:relative;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:0;padding:16px;border:1px solid #dbe4ee;border-radius:14px;background:#fff;box-shadow:0 3px 12px rgba(15,23,42,.05)}
.os-mobile #openingStockTable .opening-row td{display:block;width:100%;min-width:0;padding:0;border:0;text-align:left}
.os-mobile #openingStockTable .opening-row td:before{display:block;margin-bottom:6px;color:#64748b;font-size:12px;font-weight:800;line-height:1.3;content:attr(data-label)}
.os-mobile #openingStockTable .opening-row td.os-actions{position:absolute;top:35px;right:16px;left:auto;z-index:3;width:46px!important;min-width:46px!important;height:46px;padding:0!important;pointer-events:none}
.os-mobile #openingStockTable .os-actions:before{display:none}
.os-mobile #openingStockTable .os-actions .removeRow{width:46px;height:46px;padding:0;pointer-events:auto}
.os-mobile #openingStockTable .os-product{grid-column:1/-1;padding-right:58px!important}
.os-mobile #openingStockTable .os-unit{grid-column:span 2}
.os-mobile #openingStockTable .os-location{grid-column:span 2}
.os-mobile #openingStockTable .os-preview{grid-column:1/-1;padding:11px!important;border:1px solid #99f6e4!important;border-radius:9px;background:#f0fdfa;color:#0f766e;font-size:13px;font-weight:800;line-height:1.55;text-align:center}
.os-mobile #openingStockTable .os-preview:before{display:none}
.os-mobile #openingStockTable .select2-container{width:100%!important}
.os-mobile .os-secondary-wrap{width:100%;min-width:0}
.os-mobile #openingStockTable .form-control,.os-mobile #openingStockTable .select2-choice,.os-mobile #openingStockTable .select2-selection--single{height:46px!important;border:1px solid #d1d5db!important;border-radius:9px!important;box-shadow:none!important}
.os-mobile #openingStockTable .select2-choice{padding:0 38px 0 12px!important;line-height:44px!important}
.os-mobile #openingStockTable .select2-chosen{line-height:44px!important}
.os-web .os-table-wrap{overflow-x:auto}
.os-web #openingStockTable{min-width:1180px;table-layout:fixed}
.os-web #openingStockTable th{padding:12px 8px;background:#f8fafc;color:#334155;text-align:center;vertical-align:middle;white-space:nowrap}
.os-web #openingStockTable td{padding:10px 8px;vertical-align:middle}
.os-web #openingStockTable th:nth-child(1){width:52px}
.os-web #openingStockTable th:nth-child(2){width:220px}
.os-web #openingStockTable th:nth-child(3){width:110px}
.os-web #openingStockTable th:nth-child(4){width:190px}
.os-web #openingStockTable th:nth-child(5){width:145px}
.os-web #openingStockTable th:nth-child(6){width:205px}
.os-web #openingStockTable th:nth-child(7){width:165px}
.os-web #openingStockTable th:nth-child(8){width:125px}
.os-web #openingStockTable th:nth-child(9){width:130px}
.os-web .os-preview{color:#0f766e;font-size:13px;font-weight:800;line-height:1.55;text-align:center}
.os-web .os-actions{text-align:center}
.os-web #openingStockTable .select2-container{width:100%!important}
@media(max-width:900px){
    .os-mobile #openingStockTable .opening-row{grid-template-columns:repeat(2,minmax(0,1fr))}
    .os-mobile #openingStockTable .os-unit,.os-mobile #openingStockTable .os-location{grid-column:span 1}
}
@media(max-width:600px){
    .content{padding:8px!important}.os-header{padding:13px 12px}.os-title{font-size:17px}
    .os-mobile .os-table-wrap{padding:10px}
    .os-mobile #openingStockTable .opening-row{grid-template-columns:1fr;padding-left:12px;padding-right:12px}
    .os-mobile #openingStockTable .os-product,.os-mobile #openingStockTable .os-unit,.os-mobile #openingStockTable .os-location{grid-column:1}
    .os-mobile #openingStockTable .os-preview{grid-column:1}
    .os-help{margin:0 10px 12px;font-size:12px}
    .os-footer{padding:0 10px 14px}.os-footer .btn{flex:1}
}
</style>

<?php if ($is_mobile_layout): ?>
<style>
.main-header,.main-sidebar,.main-footer,.control-sidebar,.breadcrumb{display:none!important}
.content-wrapper,.right-side{margin-left:0!important;padding-top:0!important;min-height:100vh!important;background:#f4f7fb!important}
.content{margin:0!important;padding:0 10px 10px!important;background:#f4f7fb!important}
.os-card{width:100%!important;margin:0!important;border-radius:14px!important;box-shadow:none!important}
.os-header{padding:13px 14px!important;background:linear-gradient(135deg,#fff 0%,#f7fbfc 100%)}
.os-table-wrap{padding:14px!important}
.os-help{margin:0 14px 14px!important}
.os-footer{padding:0 14px 14px!important}
.select2-container--open{z-index:99999!important}
</style>
<?php endif; ?>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content kls-mobile-ui">
<div class="row">
<div class="col-xs-12">
<div class="os-card <?= $is_mobile_layout ? 'os-mobile' : 'os-web'; ?>">

    <div class="os-header">
        <h4 class="os-title"><?= html_escape($page_title ?? 'Add Opening Stock'); ?></h4>
        <button type="button" class="btn btn-success" id="addRow">
            <i class="fa fa-plus"></i> <?= lang('add') ?: 'Add Row'; ?>
        </button>
    </div>

    <?= form_open('openingstock/add', 'class="validation" id="openingStockForm" autocomplete="off"'); ?>
    <?php if ($is_mobile_layout): ?>
        <input type="hidden" name="app" value="1">
    <?php endif; ?>

    <div class="os-table-wrap">
        <table class="table table-bordered" id="openingStockTable">
            <thead>
                <tr>
                    <th></th>
                    <th><?= lang('product'); ?></th>
                    <th><?= lang('quantity'); ?></th>
                    <th><?= lang('unit'); ?></th>
                    <th>လုံး / ခွေ</th>
                    <th><?= lang('quantity_base'); ?></th>
                    <th><?= lang('location'); ?></th>
                    <th><?= lang('batch_no'); ?></th>
                    <th><?= lang('cost_per_base'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="opening-row">
                    <td class="os-actions">
                        <button type="button" class="btn btn-danger removeRow" title="<?= lang('delete'); ?>">
                            <i class="fa fa-minus"></i>
                        </button>
                    </td>

                    <td class="os-product" data-label="<?= lang('product'); ?>">
                        <select name="product_id[]" class="form-control select2 product-select" required>
                            <option value=""><?= lang('select_product'); ?></option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= (int) $p->id; ?>">
                                    <?= html_escape($p->name); ?>
                                    <?= !empty($p->code) ? ' (' . html_escape($p->code) . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td class="os-qty" data-label="<?= lang('quantity'); ?>">
                        <input type="number" min="0.000001" step="0.000001"
                               name="primary_qty[]" class="form-control primary-qty" required>
                    </td>

                    <td class="os-unit" data-label="<?= lang('unit'); ?>">
                        <select name="primary_unit_id[]" class="form-control primary-unit" required disabled>
                            <option value="">ယူနစ်ရွေးပါ</option>
                        </select>
                    </td>

                    <td data-label="ဒုတိယယူနစ်">
                        <div class="os-secondary-wrap" style="display:none">
                            <input type="number" min="0" step="0.000001"
                                   name="qty_secondary[]" class="form-control os-secondary-input" value="0">
                            <span class="os-secondary-unit">-</span>
                        </div>
                        <div class="os-not-dual">—</div>
                    </td>

                    <td class="os-preview" data-label="<?= lang('quantity_base'); ?>">
                        <span class="base-preview">ပစ္စည်းရွေးပါ</span>
                        <input type="hidden" name="qty_base[]" class="qty-base" value="0">
                    </td>

                    <td class="os-location" data-label="<?= lang('location'); ?>">
                        <select name="store_id[]" class="form-control select2 store-select" required>
                            <option value=""><?= lang('select_location'); ?></option>
                            <?php foreach ($stores as $w): ?>
                                <option value="<?= (int) $w->id; ?>" <?= (int) $w->id === 1 ? 'selected' : ''; ?>>
                                    <?= html_escape($w->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td class="os-batch" data-label="<?= lang('batch_no'); ?>">
                        <input type="text" name="batch_no[]" class="form-control" value="Opening">
                    </td>

                    <td class="os-cost" data-label="<?= lang('cost_per_base'); ?>">
                        <input type="number" min="0" step="0.000001"
                               name="cost_per_base[]" class="form-control cost-per-base">
                        <small class="cost-unit-label text-muted"></small>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="os-help">
        Dual Unit ဥပမာ — 2 ပေါင် + 3 လုံး ထည့်လျှင် အလေးချိန်ကို
        အောင်စအဖြစ် အလိုအလျောက်ပြောင်းပြီး 3 လုံးကို သီးခြားလက်ကျန်ထားပါမည်။
    </div>

    <div class="os-footer">
        <button type="submit" class="btn btn-primary" id="saveOpeningStock">
            <i class="fa fa-save"></i> <?= lang('save_opening_stock'); ?>
        </button>
        <a href="<?= site_url('openingstock'); ?>" class="btn btn-default"><?= lang('cancel'); ?></a>
    </div>

    <?= form_close(); ?>
</div>
</div>
</div>
</section>

<script>
window.openingStockProductUnits =
    <?= json_encode(
        $product_unit_data ?? [],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ); ?>;

(function ($) {
    'use strict';

    var productData = window.openingStockProductUnits || {};

    function numberValue(value) {
        var parsed = parseFloat(value);
        return isFinite(parsed) ? parsed : 0;
    }

    function formatNumber(value) {
        return numberValue(value)
            .toFixed(6)
            .replace(/\.0+$/, '')
            .replace(/(\.\d*?)0+$/, '$1');
    }

    function rowProductData($row) {
        var id = String($row.find('.product-select').val() || '');
        return productData[id] || {};
    }

    function resetSelect2($row) {
        $row.find('.select2-container').remove();
        $row.find('select.select2')
            .removeClass('select2-hidden-accessible select2-offscreen')
            .removeAttr('data-select2-id')
            .removeData('select2');

        if ($.fn.select2) {
            $row.find('select.select2').select2({width: '100%'});
        }
    }

    function fillUnits($row) {
        var data = rowProductData($row);
        var units = (data.units || []).slice();
        var $unit = $row.find('.primary-unit');

        units.sort(function (a, b) {
            return numberValue(b.multiplier) - numberValue(a.multiplier);
        });

        $unit.empty().append('<option value="">ယူနစ်ရွေးပါ</option>');

        $.each(units, function (_, unit) {
            var label = unit.name || '-';
            if (numberValue(unit.multiplier) !== 1) {
                label += ' (1 = ' + formatNumber(unit.multiplier) + ' ' +
                    (data.base_unit_name || '-') + ')';
            }

            $('<option>')
                .val(unit.id)
                .attr('data-multiplier', numberValue(unit.multiplier) || 1)
                .text(label)
                .appendTo($unit);
        });

        $unit.prop('disabled', !units.length);

        if (units.length) {
            $unit.val(String(units[0].id));
        }

        var isDual = data.is_dual_unit === true || Number(data.is_dual_unit) === 1;
        $row.find('.os-secondary-wrap').toggle(isDual);
        $row.find('.os-not-dual').toggle(!isDual);
        $row.find('.os-secondary-input')
            .prop('disabled', !isDual)
            .val(0);
        $row.find('.os-secondary-unit').text(data.secondary_unit_name || '-');
        $row.find('.cost-unit-label').text(
            data.base_unit_name ? 'တစ် ' + data.base_unit_name + ' ဈေး' : ''
        );

        updatePreview($row);
    }

    function updatePreview($row) {
        var data = rowProductData($row);
        var qty = numberValue($row.find('.primary-qty').val());
        var $selected = $row.find('.primary-unit option:selected');
        var multiplier = numberValue($selected.attr('data-multiplier')) || 1;
        var baseQty = qty * multiplier;
        var selectedName = $selected.text().split(' (1 =')[0] || '-';

        $row.find('.qty-base').val(formatNumber(baseQty));

        if (!data.base_unit_name) {
            $row.find('.base-preview').text('ပစ္စည်းရွေးပါ');
            return;
        }

        var text = formatNumber(qty) + ' ' + selectedName +
            ' = ' + formatNumber(baseQty) + ' ' + data.base_unit_name;

        if (data.is_dual_unit === true || Number(data.is_dual_unit) === 1) {
            text += ' | ' +
                formatNumber($row.find('.os-secondary-input').val()) + ' ' +
                (data.secondary_unit_name || '-');
        }

        $row.find('.base-preview').text(text);
    }

    function clearRow($row) {
        $row.find('.product-select').val('');
        $row.find('.primary-qty').val('');
        $row.find('.primary-unit')
            .empty()
            .append('<option value="">ယူနစ်ရွေးပါ</option>')
            .prop('disabled', true);
        $row.find('.qty-base').val(0);
        $row.find('.os-secondary-input').val(0).prop('disabled', true);
        $row.find('.os-secondary-wrap').hide();
        $row.find('.os-not-dual').show();
        $row.find('.base-preview').text('ပစ္စည်းရွေးပါ');
        $row.find('.cost-per-base').val('');
        $row.find('.cost-unit-label').text('');
        $row.find('input[name="batch_no[]"]').val('Opening');
        $row.find('.store-select').val('1');
    }

    $(function () {
        resetSelect2($('#openingStockTable tbody tr:first'));

        $('#addRow').on('click', function () {
            var $row = $('#openingStockTable tbody tr:first').clone(false, false);
            clearRow($row);
            $('#openingStockTable tbody').append($row);
            resetSelect2($row);
        });

        $(document).on('change', '.product-select', function () {
            fillUnits($(this).closest('.opening-row'));
        });

        $(document).on(
            'input change',
            '.primary-qty, .primary-unit, .os-secondary-input',
            function () {
                updatePreview($(this).closest('.opening-row'));
            }
        );

        $(document).on('click', '.removeRow', function () {
            var $rows = $('#openingStockTable tbody .opening-row');
            if ($rows.length > 1) {
                $(this).closest('.opening-row').remove();
            } else {
                clearRow($rows.first());
                resetSelect2($rows.first());
            }
        });

        $('#openingStockForm').on('submit', function (event) {
            var valid = true;

            $('#openingStockTable tbody .opening-row').each(function () {
                var $row = $(this);
                var rowValid =
                    !!$row.find('.product-select').val() &&
                    !!$row.find('.store-select').val() &&
                    !!$row.find('.primary-unit').val() &&
                    numberValue($row.find('.primary-qty').val()) > 0;

                $row.toggleClass('has-error', !rowValid);
                valid = valid && rowValid;
                updatePreview($row);
            });

            if (!valid) {
                event.preventDefault();
                if (window.bootbox) {
                    bootbox.alert('ပစ္စည်း၊ အရေအတွက်၊ ယူနစ်နှင့် တည်နေရာကို ပြန်စစ်ပါ။');
                }
                return false;
            }

            $('#saveOpeningStock').prop('disabled', true);
            return true;
        });
    });
})(jQuery);
</script>
