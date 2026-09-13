<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$app_language =
    !empty($app_language)
        ? $app_language
        : (
            $this->input->get('app_lang', true) ?:
            (
                $this->input->post('app_lang', true) ?:
                $Settings->selected_language
            )
        );

$app_query = $is_app_mode
    ? '?app=1&app_lang=' . rawurlencode($app_language)
    : '';

$active_language = strtolower(trim((string) $app_language));
$is_myanmar_language = in_array(
    $active_language,
    ['myanmar', 'burmese', 'mm', 'my', 'my-mm'],
    true
);

$T = function ($myanmar, $english) use ($is_myanmar_language) {
    return $is_myanmar_language ? $myanmar : $english;
};

$currency_symbol =
    isset($Settings) && isset($Settings->symbol)
        ? $Settings->symbol
        : '';
?>

<style>
    .content {
        padding-top: 18px;
        background: #f4f7fb;
    }

    .sp-page {
        max-width: 1050px;
        margin: 0 auto;
    }

    .sp-card {
        overflow: hidden;
        margin-bottom: 22px;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
    }

    .sp-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 18px 22px;
        border-bottom: 1px solid #e6edf3;
        background: linear-gradient(135deg, #ffffff 0%, #f7fbfc 100%);
    }

    .sp-title {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        margin: 0;
        color: #111827;
        font-size: 20px;
        font-weight: 900;
        line-height: 1.35;
    }

    .sp-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 12px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 18px;
    }

    .sp-back-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 40px;
        padding: 8px 13px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 9px !important;
        background: #f8fafc !important;
        color: #334155 !important;
        font-size:16px;
        font-weight: 800;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .sp-body {
        padding: 22px;
    }

    .sp-alert {
        margin-bottom: 16px;
        padding: 12px 14px;
        border-radius: 10px;
        font-size:16px;
        font-weight: 700;
    }

    .sp-alert-danger {
        border: 1px solid #fecaca;
        background: #fff1f2;
        color: #b91c1c;
    }

    .sp-alert-success {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .sp-product-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .sp-summary-item {
        min-width: 0;
        padding: 13px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
    }

    .sp-summary-label {
        margin-bottom: 5px;
        color: #64748b;
        font-size:16px;
        font-weight: 800;
    }

    .sp-summary-value {
        overflow-wrap: anywhere;
        color: #0f172a;
        font-size:16px;
        font-weight: 900;
    }

    .sp-info {
        display: flex;
        gap: 10px;
        margin-bottom: 17px;
        padding: 12px 14px;
        border: 1px solid #bfdbfe;
        border-radius: 11px;
        background: #eff6ff;
        color: #1e40af;
        font-size:16px;
        line-height: 1.6;
    }

    .sp-info i {
        margin-top: 2px;
        flex: 0 0 auto;
    }

    .sp-calculator {
        margin-bottom: 17px;
        padding: 15px;
        border: 1px solid #dbe4ec;
        border-radius: 13px;
        background: #f8fafc;
    }

    .sp-calculator-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
    }

    .sp-calculator-row {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 12px;
    }

    .sp-control-group {
        min-width: 160px;
    }

    .sp-control-label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 16px;
        font-weight: 800;
    }

    /* ယူနစ်အားလုံးအတွက် အမြတ်ရာခိုင်နှုန်းထည့်သည့် Highlight Box */
    .sp-markup-highlight {
        position: relative;
        padding: 14px 16px 13px;
        border: 2px solid #10b981;
        border-radius: 12px;
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 100%);
        box-shadow: 0 5px 14px rgba(16, 185, 129, .13);
    }

    .sp-markup-highlight .sp-control-label {
        margin-bottom: 9px;
        color: #065f46;
        font-size: 15px;
        font-weight: 900;
    }

    .sp-markup-highlight-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-left: 7px;
        padding: 3px 8px;
        border-radius: 999px;
        background: #059669;
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        vertical-align: middle;
    }

    .sp-markup-highlight #sp_quick_markup {
        border: 2px solid #10b981 !important;
        background: #ffffff !important;
        color: #064e3b !important;
        font-size: 16px !important;
        font-weight: 900 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .10) !important;
    }

    .sp-markup-highlight #sp_quick_markup:focus {
        border-color: #047857 !important;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .20) !important;
    }

    .sp-markup-highlight .help-block {
        color: #047857;
        font-weight: 800;
    }

    .sp-control-select {
        height: 40px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 9px !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-weight: 800 !important;
        box-shadow: none !important;
    }

    .sp-quick-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .sp-quick-btn {
        min-height: 40px;
        padding: 7px 12px !important;
        border: 1px solid #bfdbfe !important;
        border-radius: 9px !important;
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        font-size: 16px !important;
        font-weight: 900 !important;
    }

    .sp-quick-btn:hover,
    .sp-quick-btn:focus {
        border-color: #2563eb !important;
        background: #dbeafe !important;
    }

    .sp-calculator-help {
        margin-top: 11px;
        color: #64748b;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.55;
    }

    .sp-table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 13px;
        background: #ffffff;
    }

    .sp-table {
        width: 100%;
        min-width: 920px;
        margin: 0 !important;
    }

    .sp-table thead th {
        padding: 12px 10px !important;
        border-bottom: 1px solid #dfe7ee !important;
        background: #f8fafc;
        color: #334155;
        font-size:16px;
        font-weight: 900;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    .sp-table tbody td {
        padding: 12px 10px !important;
        color: #334155;
        font-size:16px;
        vertical-align: middle !important;
    }

    .sp-unit-name {
        color: #0f172a;
        font-size:16px;
        font-weight: 900;
    }

    .sp-unit-code {
        margin-top: 3px;
        color: #64748b;
        font-size:16px;
        font-weight: 700;
    }

    .sp-base-badge {
        display: inline-block;
        margin-top: 5px;
        padding: 4px 7px;
        border-radius: 999px;
        background: #dcfce7;
        color: #15803d;
        font-size:16px;
        font-weight: 900;
    }

    .sp-price-group {
        position: relative;
        width: 135px;
        min-width: 135px;
        max-width: 135px;
    }

    .sp-price-prefix {
        position: absolute;
        top: 50%;
        left: 12px;
        z-index: 2;
        transform: translateY(-50%);
        color: #64748b;
        font-size:16px;
        font-weight: 800;
        pointer-events: none;
    }

    .sp-price-input {
        width: 135px !important;
        height: 38px !important;
        padding: 6px 9px 6px 28px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 9px !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 16px !important;
        font-weight: 900 !important;
        text-align: right;
        box-shadow: none !important;
    }

    .sp-price-input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
    }

    .sp-percent-group {
        position: relative;
        width: 100px;
        min-width: 100px;
        max-width: 100px;
    }

    .sp-percent-input {
        width: 100px !important;
        height: 38px !important;
        padding: 6px 25px 6px 8px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 9px !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 16px !important;
        font-weight: 900 !important;
        text-align: right;
        box-shadow: none !important;
    }

    .sp-percent-input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
    }

    .sp-percent-suffix {
        position: absolute;
        top: 50%;
        right: 9px;
        transform: translateY(-50%);
        color: #64748b;
        font-weight: 900;
        pointer-events: none;
    }

    /* အပေါ်ဆုံး ယူနစ်အားလုံးအတွက် အမြတ်ရာခိုင်နှုန်း input */
    .sp-calculator .sp-percent-group {
        width: 180px;
        min-width: 180px;
        max-width: 180px !important;
    }

    .sp-calculator #sp_quick_markup {
        width: 180px !important;
        text-align: left;
    }

    .sp-profit-sub {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 16px;
        font-weight: 700;
        white-space: nowrap;
    }

    .sp-profit.loss .sp-profit-sub {
        color: #dc2626;
    }

    .sp-row-warning {
        display: none;
        margin-top: 5px;
        color: #dc2626;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.35;
    }

    tr.sp-loss-row .sp-row-warning {
        display: block;
    }

    .sp-profit {
        color: #15803d;
        font-weight: 900;
        text-align: right;
    }

    .sp-profit.loss {
        color: #dc2626;
    }

    .sp-actions {
        position: sticky;
        bottom: 0;
        z-index: 20;
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin: 20px -22px -22px;
        padding: 14px 22px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
        box-shadow: 0 -8px 18px rgba(15, 23, 42, .04);
    }

    .sp-actions .btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 42px;
        padding: 9px 16px !important;
        border-radius: 9px !important;
        font-weight: 900;
    }

    .sp-save-btn {
        border-color: #1d4ed8 !important;
        background: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .sp-save-stay-btn {
        border-color: #0f766e !important;
        background: #0f766e !important;
        color: #ffffff !important;
    }

    @media (max-width: 900px) {
        .sp-product-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .sp-header {
            padding: 13px 14px;
        }

        .sp-title {
            gap: 9px;
            font-size: 16px;
        }

        .sp-title-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            border-radius: 10px;
            font-size: 15px;
        }

        .sp-back-btn span {
            display: none;
        }

        .sp-back-btn {
            width: 38px;
            min-width: 38px;
            padding: 0 !important;
        }

        .sp-body {
            padding: 14px;
        }

        .sp-product-summary {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .sp-summary-item {
            padding: 10px;
        }

        .sp-table {
            min-width: 900px;
        }

        .sp-calculator {
            padding: 12px;
        }

        .sp-calculator-row,
        .sp-control-group {
            width: 100%;
        }

        .sp-calculator .sp-percent-group {
            width: 160px;
            min-width: 160px;
            max-width: 160px !important;
        }

        .sp-calculator #sp_quick_markup {
            width: 160px !important;
        }

        .sp-price-input,
        .sp-percent-input {
            min-height: 40px;
        }

        .sp-quick-buttons {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            width: 100%;
        }

        .sp-quick-btn {
            padding: 7px 4px !important;
        }

        .sp-actions {
            display: block;
            margin: 15px -14px -14px;
            padding: 12px 14px;
        }

        .sp-actions .btn {
            width: 100%;
            margin-bottom: 8px;
        }

        .sp-actions .btn:last-child {
            margin-bottom: 0;
        }
    }
</style>

<?php if ($is_app_mode) { ?>
<style>
    .main-header,
    .main-sidebar,
    .main-footer,
    .control-sidebar,
    .breadcrumb {
        display: none !important;
    }

    .content-wrapper,
    .right-side {
        min-height: 100vh !important;
        margin-left: 0 !important;
        padding-top: 0 !important;
        background: #f4f7fb !important;
    }

    .content {
        margin: 0 !important;
        padding: 0 10px 10px !important;
    }

    .sp-page {
        max-width: 100% !important;
        margin: 0 !important;
    }

    .sp-card {
        margin-bottom: 0 !important;
        border-radius: 14px !important;
        box-shadow: none !important;
    }
</style>
<?php } ?>

<section class="content">
    <div class="sp-page">
        <div class="sp-card">
            <div class="sp-header" style="display:none;">
                <h4 class="sp-title">
                    <span class="sp-title-icon">
                        <i class="fa fa-money"></i>
                    </span>
                    <span><?= html_escape($page_title); ?></span>
                </h4>

                <a
                    href="<?= site_url('products') . $app_query; ?>"
                    class="sp-back-btn"
                >
                    <i class="fa fa-arrow-left"></i>
                    <span><?= html_escape($T('ကုန်ပစ္စည်းစာရင်း', 'Product List')); ?></span>
                </a>
            </div>

            <div class="sp-body">
                <?php if (!empty($error)) { ?>
                    <div class="sp-alert sp-alert-danger">
                        <i class="fa fa-exclamation-circle"></i>
                        <?= $error; ?>
                    </div>
                <?php } ?>

                <?php if (!empty($message)) { ?>
                    <div class="sp-alert sp-alert-success">
                        <i class="fa fa-check-circle"></i>
                        <?= $message; ?>
                    </div>
                <?php } ?>

                <div class="sp-product-summary">
                    <div class="sp-summary-item">
                        <div class="sp-summary-label">
                            <?= html_escape($T('ကုန်ပစ္စည်းကုဒ်', 'Product Code')); ?>
                        </div>
                        <div class="sp-summary-value">
                            <?= html_escape($product->code); ?>
                        </div>
                    </div>

                    <div class="sp-summary-item">
                        <div class="sp-summary-label">
                            <?= html_escape($T('ကုန်ပစ္စည်းအမည်', 'Product Name')); ?>
                        </div>
                        <div class="sp-summary-value">
                            <?= html_escape($product->name); ?>
                        </div>
                    </div>

                    <div class="sp-summary-item">
                        <div class="sp-summary-label">
                            <?= html_escape($T('အသေးဆုံးယူနစ်', 'Base Unit')); ?>
                        </div>
                        <div class="sp-summary-value">
                            <?= html_escape($product->base_unit_name ?: '-'); ?>
                        </div>
                    </div>

                    <div class="sp-summary-item">
                        <div class="sp-summary-label">
                            <?= html_escape($T('အသေးဆုံးယူနစ်ဝယ်ဈေး', 'Base Purchase Price')); ?>
                        </div>
                        <div class="sp-summary-value">
                            
                            <?= number_format((float) $product->cost, 2); ?>
                        </div>
                    </div>
                </div>

                <div class="sp-info">
                    <i class="fa fa-info-circle"></i>
                    <div>
                        <?= html_escape(
                            $T(
                                'အသေးဆုံးယူနစ်နှင့် သတ်မှတ်ထားသော ဆက်စပ်ယူနစ်အားလုံးအတွက် ရောင်းဈေးထည့်နိုင်ပါသည်။',
                                'Enter a selling price for the base unit and every configured linked unit. The base-unit price is also copied to products.price automatically.'
                            )
                        ); ?>
                    </div>
                </div>

                <?= form_open(
                    'products/selling_prices/' . (int) $product->id,
                    [
                        'class'      => 'sp-price-form',
                        'id'         => 'selling_price_form',
                        'novalidate' => 'novalidate',
                    ]
                ); ?>

                <input type="hidden" name="save_action" id="save_action" value="list">

                <?php if ($is_app_mode) { ?>
                    <input type="hidden" name="app" value="1">
                    <input
                        type="hidden"
                        name="app_lang"
                        value="<?= html_escape($app_language); ?>"
                    >
                <?php } ?>

                <div class="sp-calculator">
                    <div class="sp-calculator-title">
                        <i class="fa fa-calculator"></i>
                        <span><?= html_escape($T('အမြတ်ရာခိုင်နှုန်းဖြင့် အမြန်တွက်ရန်', 'Quick Price Calculator')); ?></span>
                    </div>

                    <div class="sp-calculator-row">
                        <div class="sp-control-group" style="display:none;">
                            <label class="sp-control-label" for="sp_round_to">
                                <?= html_escape($T('ရောင်းဈေးလုံးမည့်ပုံစံ', 'Price Rounding')); ?>
                            </label>
                            <select id="sp_round_to" class="form-control sp-control-select">
                                <option value="0"><?= html_escape($T('မလုံးပါ', 'No rounding')); ?></option>
                                <option value="1"><?= html_escape($T('အနီးဆုံး 1 ကျပ်', 'Nearest 1')); ?></option>
                                <option value="10"><?= html_escape($T('အနီးဆုံး 10 ကျပ်', 'Nearest 10')); ?></option>
                                <option value="50"><?= html_escape($T('အနီးဆုံး 50 ကျပ်', 'Nearest 50')); ?></option>
                                <option value="100"><?= html_escape($T('အနီးဆုံး 100 ကျပ်', 'Nearest 100')); ?></option>
                            </select>
                        </div>

                        <div class="sp-control-group sp-markup-highlight" style="flex:1;">
                            <label class="sp-control-label" for="sp_quick_markup">
                                <?= html_escape($T('ယူနစ်အားလုံးအတွက် အမြတ်ရာခိုင်နှုန်း', 'Markup for All Units')); ?>
                                <span class="sp-markup-highlight-badge">
                                    <i class="fa fa-hand-o-right"></i>
                                    <?= html_escape($T('ဒီမှာထည့်ပါ', 'Enter here')); ?>
                                </span>
                            </label>

                            <div class="sp-percent-group" style="max-width:100%;">
                                <input
                                    type="number"
                                    id="sp_quick_markup"
                                    class="form-control sp-percent-input"
                                    min="0"
                                    step="0.01"
                                    inputmode="decimal"
                                    autocomplete="off"
                                    placeholder="<?= html_escape($T('ဥပမာ 12.5', 'Example: 12.5')); ?>"
                                    aria-label="<?= html_escape($T('ယူနစ်အားလုံးအတွက် အမြတ်ရာခိုင်နှုန်း', 'Markup for all units')); ?>"
                                >
                                <span class="sp-percent-suffix">%</span>
                            </div>

                            <small class="help-block" style="margin-top:6px;">
                                <?= html_escape($T(
                                    'ရာခိုင်နှုန်းကို ရိုက်ထည့်သည်နှင့် ယူနစ်အားလုံး၏ ရောင်းဈေး အလိုအလျောက်ပြောင်းပါမည်။',
                                    'Selling prices update automatically while you type.'
                                )); ?>
                            </small>
                        </div>
                    </div>

                    
                </div>

                <div class="sp-table-wrap">
                    <table class="table table-bordered sp-table">
                        <thead>
                            <tr>
                                <th><?= html_escape($T('ယူနစ်', 'Unit')); ?></th>
                                <th><?= html_escape($T('ယူနစ်ဆက်စပ်မှု', 'Conversion')); ?></th>
                                <th class="text-right">
                                    <?= html_escape($T('ခန့်မှန်းဝယ်ဈေး', 'Estimated Cost')); ?>
                                </th>
                                <th><?= html_escape($T('အမြတ်တင်နှုန်း (%)', 'Markup %')); ?></th>
                                <th><?= html_escape($T('အမြတ်နှုန်း (%)', 'Margin %')); ?></th>
                                <th><?= html_escape($T('ရောင်းဈေး', 'Selling Price')); ?></th>
                                <th class="text-right">
                                    <?= html_escape($T('အမြတ်ငွေ', 'Profit')); ?>
                                </th>
                            </tr>
                        </thead>

                        <tbody>

<?php foreach ($unit_rows as $unit_row) { ?>

    <tr>

        <!-- =================================================
             UNIT
        ================================================== -->
        <td>

            <input
                type="hidden"
                name="unit_id[]"
                value="<?= (int) $unit_row->unit_id; ?>"
            >

            <div class="sp-unit-name">

                <?= html_escape(
                    $unit_row->unit_name ?: '-'
                ); ?>

            </div>


            



        </td>


        <!-- =================================================
             CONVERSION
        ================================================== -->
        <td>

            <?= html_escape(
                $unit_row->conversion_text
            ); ?>

        </td>


        <!-- =================================================
             ESTIMATED COST
        ================================================== -->
        <td class="text-right">

            <?= number_format((float) $unit_row->estimated_cost, 2); ?>

        </td>


        <!-- =================================================
             MARKUP
        ================================================== -->
        <td>
            <?php
            $row_cost = (float) $unit_row->estimated_cost;
            $row_price = (float) $unit_row->selling_price;
            $row_profit = $row_price - $row_cost;
            $row_markup = $row_cost > 0 ? ($row_profit / $row_cost) * 100 : 0;
            $row_margin = $row_price > 0 ? ($row_profit / $row_price) * 100 : 0;
            ?>
            <div class="sp-percent-group">
                <input
                    type="number"
                    class="form-control sp-percent-input sp-markup-input"
                    value="<?= html_escape(number_format($row_markup, 2, '.', '')); ?>"
                    step="0.01"
                    inputmode="decimal"
                    aria-label="Markup percentage"
                >
                <span class="sp-percent-suffix">%</span>
            </div>
        </td>


        <!-- =================================================
             MARGIN
        ================================================== -->
        <td>
            <div class="sp-percent-group">
                <input
                    type="number"
                    class="form-control sp-percent-input sp-margin-input"
                    value="<?= html_escape(number_format($row_margin, 2, '.', '')); ?>"
                    step="0.01"
                    max="99.99"
                    inputmode="decimal"
                    aria-label="Margin percentage"
                >
                <span class="sp-percent-suffix">%</span>
            </div>
        </td>


        <!-- =================================================
             SELLING PRICE
        ================================================== -->
        <td>

            <div class="sp-price-group">

                


                <input
                    type="number"
                    name="unit_price[]"
                    class="form-control sp-price-input"

                    value="<?= html_escape(
                        number_format(
                            (float) $unit_row->selling_price,
                            2,
                            '.',
                            ''
                        )
                    ); ?>"

                    min="0"
                    step="0.0001"

                    required="required"

                    inputmode="decimal"

                    data-cost="<?= html_escape(
                        number_format(
                            (float) $unit_row->estimated_cost,
                            2,
                            '.',
                            ''
                        )
                    ); ?>"
                >

            </div>

            <div class="sp-row-warning">
                <i class="fa fa-exclamation-triangle"></i>
                <?= html_escape($T('ရောင်းဈေးသည် ဝယ်ဈေးထက် နည်းနေပါသည်။', 'Selling price is below cost.')); ?>
            </div>

        </td>


        <!-- =================================================
             PROFIT
        ================================================== -->
        <td
            class="sp-profit <?= (
                (float) $unit_row->selling_price -
                (float) $unit_row->estimated_cost
            ) < 0 ? 'loss' : ''; ?>"
            data-profit-cell
        >

            <span data-profit-amount>
                
                <?= number_format($row_profit, 2); ?>
            </span>
            

        </td>

    </tr>

<?php } ?>

</tbody>
                    </table>
                </div>

                <div class="sp-actions">
                    <a
                        href="<?= site_url('products') . $app_query; ?>"
                        class="btn btn-default"
                    >
                        <i class="fa fa-times"></i>
                        <?= html_escape($T('မလုပ်တော့ပါ', 'Cancel')); ?>
                    </a>

                    <button
                        type="button"
                        data-save-action="stay"
                        class="btn sp-save-stay-btn sp-submit-price-btn"
                    >
                        <i class="fa fa-save"></i>
                        <?= html_escape(
                            $T(
                                'သိမ်းပြီး ဆက်ပြင်မည်',
                                'Save and Continue'
                            )
                        ); ?>
                    </button>

                    <button
                        type="button"
                        data-save-action="list"
                        class="btn sp-save-btn sp-submit-price-btn"
                    >
                        <i class="fa fa-check-circle"></i>
                        <?= html_escape(
                            $T(
                                'သိမ်းပြီး စာရင်းသို့',
                                'Save and Return'
                            )
                        ); ?>
                    </button>
                </div>

                <?= form_close(); ?>
            </div>
        </div>
    </div>
</section>

<script>
(function ($) {
    'use strict';

    function numberValue(value) {
        var cleaned = String(value === undefined || value === null ? '' : value)
            .replace(/,/g, '')
            .replace(/[^0-9.\-]/g, '');

        return parseFloat(cleaned) || 0;
    }

    function formatMoney(value) {
        return numberValue(value).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    var currencySymbol = <?= json_encode(
        $currency_symbol,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ); ?>;

    function formatPercent(value) {
        var number = Number(value);
        return isFinite(number) ? number.toFixed(2) : '0.00';
    }

    function roundedPrice(value) {
        var roundTo = numberValue($('#sp_round_to').val());
        var price = Math.max(0, Number(value) || 0);

        if (roundTo > 0) {
            price = Math.round(price / roundTo) * roundTo;
        }

        return price;
    }

    function setPriceValue($input, value) {
        var price = roundedPrice(value);
        $input.val(price.toFixed(2));
        return price;
    }

    function updateRowFromPrice($row) {
        var $priceInput = $row.find('.sp-price-input');
        var price = numberValue($priceInput.val());
        var cost = numberValue($priceInput.data('cost'));
        var profit = price - cost;
        var markup = cost > 0 ? (profit / cost) * 100 : 0;
        var margin = price > 0 ? (profit / price) * 100 : 0;
        var isLoss = price < cost;
        var $profitCell = $row.find('[data-profit-cell]');

        $row.find('.sp-markup-input').val(formatPercent(markup));
        $row.find('.sp-margin-input').val(formatPercent(margin));

        $row.toggleClass('sp-loss-row', isLoss);
        $profitCell.toggleClass('loss', isLoss);
        $profitCell.find('[data-profit-amount]').text(
             formatMoney(profit)
        );
        $profitCell.find('[data-profit-percent]').text(
            'Markup ' + formatPercent(markup) + '% · ' +
            'Margin ' + formatPercent(margin) + '%'
        );
    }

    function updatePriceFromMarkup($row, markup) {
        var $priceInput = $row.find('.sp-price-input');
        var cost = numberValue($priceInput.data('cost'));
        var price = cost > 0 ? cost * (1 + (markup / 100)) : 0;

        setPriceValue($priceInput, price);
        updateRowFromPrice($row);
    }

    function updatePriceFromMargin($row, margin) {
        var $priceInput = $row.find('.sp-price-input');
        var cost = numberValue($priceInput.data('cost'));

        if (margin >= 100) {
            $row.find('.sp-margin-input')
                .css('border-color', '#dc2626')
                .attr('title', <?= json_encode(
                    $T('Margin သည် 100% ထက် ငယ်ရပါမည်။', 'Margin must be below 100%.'),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ); ?>);
            return;
        }

        $row.find('.sp-margin-input').css('border-color', '').removeAttr('title');

        var price = cost > 0 ? cost / (1 - (margin / 100)) : 0;
        setPriceValue($priceInput, price);
        updateRowFromPrice($row);
    }

    $('.sp-price-input').each(function () {
        updateRowFromPrice($(this).closest('tr'));
    });

    $(document).on('input change', '.sp-price-input', function () {
        updateRowFromPrice($(this).closest('tr'));
    });

    $(document).on('input change', '.sp-markup-input', function () {
        var $input = $(this);
        updatePriceFromMarkup($input.closest('tr'), numberValue($input.val()));
    });

    $(document).on('input change', '.sp-margin-input', function () {
        var $input = $(this);
        updatePriceFromMargin($input.closest('tr'), numberValue($input.val()));
    });

    $(document).on('input change', '#sp_quick_markup', function () {
        var $input = $(this);
        var rawValue = $.trim($input.val());

        /* Field ကိုရှင်းထားချိန်မှာ ရောင်းဈေးများကို 0 မပြောင်းပါ။ */
        if (rawValue === '') {
            $input.css('border-color', '').removeAttr('title');
            return;
        }

        var markup = Number(rawValue);

        if (!isFinite(markup) || markup < 0) {
            $input
                .css('border-color', '#dc2626')
                .attr('title', <?= json_encode(
                    $T(
                        'အမြတ်ရာခိုင်နှုန်းကို 0 သို့မဟုတ် 0 ထက်ကြီးသော ဂဏန်းထည့်ပါ။',
                        'Enter zero or a positive markup percentage.'
                    ),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ); ?>);
            return;
        }

        $input.css('border-color', '').removeAttr('title');

        $('.sp-table tbody tr').each(function () {
            updatePriceFromMarkup($(this), markup);
        });
    });

    $('#sp_round_to').on('change', function () {
        $('.sp-price-input').each(function () {
            var $input = $(this);
            setPriceValue($input, numberValue($input.val()));
            updateRowFromPrice($input.closest('tr'));
        });
    });

    function validateSellingPrices() {
        var isValid = true;
        var $firstInvalid = null;

        $('.sp-price-input').each(function () {
            var $input = $(this);
            var rawValue = $.trim($input.val());
            var price = parseFloat(rawValue);

            $input.css('border-color', '');

            if (
                rawValue === '' ||
                isNaN(price) ||
                !isFinite(price) ||
                price < 0
            ) {
                isValid = false;
                $input.css('border-color', '#dc2626');

                if ($firstInvalid === null) {
                    $firstInvalid = $input;
                }
            }
        });

        if (!isValid) {
            if ($firstInvalid) {
                $firstInvalid.trigger('focus');
            }

            bootbox.alert(
                <?= json_encode(
                    $T(
                        'ရောင်းဈေးအားလုံးကို သုည သို့မဟုတ် သုညထက်ကြီးသော ဂဏန်းဖြင့် ဖြည့်ပါ။',
                        'Enter zero or a positive number for every selling price.'
                    ),
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ); ?>
            );
        }

        return isValid;
    }

    $('.sp-submit-price-btn').on('click', function () {
        var form = document.getElementById('selling_price_form');
        var $button = $(this);

        if (!form || !validateSellingPrices()) {
            return false;
        }

        $('#save_action').val($button.data('save-action') || 'list');

        $('.sp-submit-price-btn')
            .prop('disabled', true)
            .addClass('disabled');

        $button.html(
            '<i class="fa fa-spinner fa-spin"></i> ' +
            <?= json_encode(
                $T('သိမ်းဆည်းနေသည်...', 'Saving...'),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ); ?>
        );

        if (
            window.HTMLFormElement &&
            HTMLFormElement.prototype.submit
        ) {
            HTMLFormElement.prototype.submit.call(form);
        } else {
            form.submit();
        }

        return false;
    });

    $('#selling_price_form').on('submit', function (event) {
        if (!validateSellingPrices()) {
            event.preventDefault();
            return false;
        }
    });
})(jQuery);
</script>