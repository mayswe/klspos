<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>
<?php
$is_app_mode = !empty($is_app_mode)
    || $this->input->get('app') == 1
    || $this->input->post('app') == 1
    || $this->input->get('mobile') == 1
    || $this->input->post('mobile') == 1;

$app_language = $this->input->get('app_lang', true)
    ?: ($this->input->post('app_lang', true) ?: $Settings->selected_language);

$app_query = $is_app_mode
    ? '?app=1&app_lang=' . rawurlencode($app_language)
    : '';

$L = function ($key, $fallback) {
    $line = lang($key);
    return ($line !== false && $line !== null && $line !== $key)
        ? $line
        : $fallback;
};

$active_language = strtolower(trim((string) $app_language));
$is_myanmar_language = in_array(
    $active_language,
    ['burmese', 'myanmar', 'my', 'mm', 'my-mm'],
    true
);

/*
 * Generic language keys such as name, code, category and unit return short
 * labels like “အမည်”, “ကုဒ်”, “အမျိုးအစား” and “ယူနစ်”.
 * This page needs the exact Product-specific labels, so they are defined
 * separately here instead of using those generic language keys.
 */
$labels = $is_myanmar_language
    ? [
        'product_information'    => 'ကုန်ပစ္စည်းအချက်အလက်',
        'unit_specification'     => 'ကုန်ပစ္စည်းယူနစ်သတ်မှတ်ချက်',
        'required_fields_marked' => '* ပါသောအကွက်များ ဖြည့်ရန်လိုအပ်သည်',
        'name'                   => 'ကုန်ပစ္စည်းအမည်',
        'brand'                  => 'ကုန်အမှတ်တံဆိပ် (Brand)',
        'add_brand'              => 'Brand အသစ်ထည့်ရန်',
        'select_brand'           => 'Brand ရွေးချယ်ရန်',
        'code_barcode'           => 'ကုန်ပစ္စည်းကုဒ်/ဘားကုဒ်',
        'category'               => 'ကုန်ပစ္စည်းအုပ်စု',
        'unit'                   => 'ကုန်ပစ္စည်းယူနစ် (အသေးဆုံးယူနစ်)',
        'alert_quantity'         => 'ကုန်ပစ္စည်းအနည်းဆုံးလက်ကျန်သတိပေးအရေအတွက်',
        'image'                  => 'ပုံ',
        'details'                => 'မှတ်ချက်',
        'media_and_details'      => 'ပုံနှင့် မှတ်ချက်',
        'add_category'           => 'အုပ်စုအသစ်ထည့်ရန်',
        'add_unit'               => 'ယူနစ်အသစ်ထည့်ရန်',
        'list_products'          => 'ကုန်ပစ္စည်းစာရင်း',
        'select_category'        => 'ကုန်ပစ္စည်းအုပ်စု ရွေးချယ်ရန်',
        'select_unit'            => 'ကုန်ပစ္စည်းယူနစ် ရွေးချယ်ရန်',
        'enter_product_name'     => 'ကုန်ပစ္စည်းအမည် ရိုက်ထည့်ပါ',
        'brand_name'             => 'Brand အမည်',
        'brand_code'             => 'Brand ကုဒ်',
        'enter_brand'            => 'ဥပမာ - Premier၊ Ve Ve၊ Coca-Cola',
        'save_brand'             => 'Brand သိမ်းဆည်းမည်',
        'enter_product_code'     => 'ကုန်ပစ္စည်းကုဒ် သို့မဟုတ် ဘားကုဒ်ဖတ်၍ ထည့်ပါ။',
        'can_use_barcode'        => 'ကုန်ပစ္စည်းကုဒ် သို့မဟုတ် ဘားကုဒ်ဖတ်၍ ထည့်ပါ။ ကုတ်အလိုအလျောက်ကို နှိပ်ပြီးလည်း ထည့်နိုင်ပါသည်။',
        'stock_alert_help'       => 'လက်ကျန်ပမာဏသည် ဤအရေအတွက်အောက်ရောက်လျှင် သတိပေးမည်။',
        'image_help'             => 'ကုန်ပစ္စည်းပုံ မထည့်လိုပါက ချန်ထားနိုင်သည်။',
        'details_placeholder'    => 'ကုန်ပစ္စည်းနှင့်ပတ်သက်သော မှတ်ချက် ရေးသားပါ',
        'cancel'                 => 'မလုပ်တော့ပါ',
        'save_add_another'       => 'သိမ်းပြီး နောက်တစ်ခုထပ်ထည့်ရန်',
        'create'                 => 'သိမ်းဆည်းမည်',
        'close'                  => 'ပိတ်ရန်',
        'category_name'          => 'ကုန်ပစ္စည်းအုပ်စုအမည်',
        'category_code'          => 'ကုန်ပစ္စည်းအုပ်စုကုဒ်',
        'enter_category_name'    => 'ကုန်ပစ္စည်းအုပ်စုအမည် ရိုက်ထည့်ပါ',
        'auto_if_empty'          => 'မဖြည့်ပါက အလိုအလျောက်ထုတ်ပေးမည်',
        'save_category'          => 'အုပ်စုသိမ်းဆည်းမည်',
        'unit_name'              => 'ယူနစ်အမည်',
        'unit_code'              => 'ယူနစ်ကုဒ်',
        'unit_name_example'      => 'ဥပမာ - ခု၊ ဘူး၊ ကတ်တွန်',
        'save_unit'              => 'ယူနစ်သိမ်းဆည်းမည်',
    ]
    : [
        'product_information'    => 'Product Information',
        'unit_specification'     => 'Product Unit Specification',
        'required_fields_marked' => 'Fields marked with * are required',
        'name'                   => 'Product Name',
        'brand'                  => 'Brand',
        'add_brand'              => 'Add Brand',
        'select_brand'           => 'Select Brand',
        'code_barcode'           => 'Product Code / Barcode',
        'category'               => 'Product Category',
        'unit'                   => 'Product Unit',
        'alert_quantity'         => 'Minimum Stock Alert Quantity',
        'image'                  => 'Image',
        'details'                => 'Notes',
        'media_and_details'      => 'Image and Notes',
        'add_category'           => 'Add Category',
        'add_unit'               => 'Add Unit',
        'list_products'          => 'Product List',
        'select_category'        => 'Select Product Category',
        'select_unit'            => 'Select Product Unit',
        'enter_product_name'     => 'Enter product name',
        'brand_name'             => 'Brand Name',
        'brand_code'             => 'Brand Code',
        'enter_brand'            => 'Example: Premier, Ve Ve, Coca-Cola',
        'save_brand'             => 'Save Brand',
        'enter_product_code'     => 'Enter product code or scan a barcode',
        'can_use_barcode'        => 'You can use either a product code or a barcode in this field.',
        'stock_alert_help'       => 'An alert will appear when stock falls below this quantity.',
        'image_help'             => 'Leave this blank when no product image is required.',
        'details_placeholder'    => 'Write notes about this product',
        'cancel'                 => 'Cancel',
        'save_add_another'       => 'Save and Add Another',
        'create'                 => 'Save Product',
        'close'                  => 'Close',
        'category_name'          => 'Category Name',
        'category_code'          => 'Category Code',
        'enter_category_name'    => 'Enter category name',
        'auto_if_empty'          => 'Generated automatically when left blank',
        'save_category'          => 'Save Category',
        'unit_name'              => 'Unit Name',
        'unit_code'              => 'Unit Code',
        'unit_name_example'      => 'Example: Piece, Bottle, Carton',
        'save_unit'              => 'Save Unit',
    ];

$unit_dropdown = ['' => $labels['select_unit']];
foreach ($units as $unit) {
    $unit_dropdown[$unit->id] = $unit->name;
}

$category_dropdown = ['' => $labels['select_category']];
foreach ($categories as $category) {
    $category_dropdown[$category->id] = $category->name;
}

$brand_dropdown = ['' => $labels['select_brand']];
foreach ((isset($brands) && is_array($brands)) ? $brands : [] as $brand) {
    $brand_dropdown[$brand->id] = $brand->name;
}

$product_page_labels = [
    'request_failed_try_again'   => $L('request_failed_try_again', $is_myanmar_language ? 'တောင်းဆိုမှု မအောင်မြင်ပါ။ ထပ်မံကြိုးစားပါ။' : 'Request failed. Please try again.'),
    'checking_code'              => $L('checking_code', $is_myanmar_language ? 'ကုဒ်စစ်ဆေးနေသည်...' : 'Checking code...'),
    'code_already_exists'        => $L('code_already_exists', $is_myanmar_language ? 'ဒီကုဒ်ကို အသုံးပြုပြီးသားဖြစ်သည်။' : 'This code already exists.'),
    'code_available'             => $L('code_available', $is_myanmar_language ? 'ဒီကုဒ်ကို အသုံးပြုနိုင်သည်။' : 'This code is available.'),
    'unable_check_code'          => $L('unable_check_code', $is_myanmar_language ? 'ကုဒ်စစ်ဆေး၍ မရပါ။' : 'Unable to check this code.'),
    'generating'                 => $L('generating', $is_myanmar_language ? 'ထုတ်ပေးနေသည်...' : 'Generating...'),
    'auto_code'                  => $L('auto_code', $is_myanmar_language ? 'ကုဒ်အလိုအလျောက်ထုတ်ရန်' : 'Generate Code'),
    'product_code_exists_change' => $L('product_code_exists_change', $is_myanmar_language ? 'ကုန်ပစ္စည်းကုဒ် ရှိပြီးသားဖြစ်သောကြောင့် ပြောင်းလဲပေးပါ။' : 'The product code already exists. Please use another code.'),
];
?>

<script>
    var site_url = <?= json_encode(site_url(), JSON_UNESCAPED_SLASHES); ?>;
    var csrfName = <?= json_encode($this->security->get_csrf_token_name()); ?>;
    var csrfHash = <?= json_encode($this->security->get_csrf_hash()); ?>;
    var productPageLabels = <?= json_encode($product_page_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<style>
    .content {
        background: #f4f7fb;
        padding-top: 18px;
    }

    .erp-page {
        max-width: 1180px;
        margin: 0 auto;
    }

    .erp-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
        overflow: hidden;
        margin-bottom: 22px;
    }

    .erp-page-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 18px 22px;
        border-bottom: 1px solid #e6edf3;
        background: linear-gradient(135deg, #fff 0%, #f7fbfc 100%);
    }

    .erp-page-title {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        margin: 0;
        color: #111827;
        font-size: 20px;
        font-weight: 800;
        line-height: 1.35;
    }

    .erp-page-title i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 12px;
        background: #e6f4f1;
        color: #0f766e;
        font-size: 19px;
    }

    .erp-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .erp-listing-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 42px;
        padding: 9px 15px !important;
        border: 1px solid #0f766e !important;
        border-radius: 10px !important;
        background: linear-gradient(135deg, #148a82 0%, #0f766e 100%) !important;
        color: #fff !important;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none !important;
        white-space: nowrap;
        box-shadow: 0 7px 16px rgba(15, 118, 110, .17);
    }

    .erp-body {
        padding: 24px 26px 0;
    }

    .erp-section {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 20px 20px 4px;
        margin-bottom: 22px;
        background: #fff;
    }

    .erp-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eef2f7;
    }

    .erp-section-title i {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #eefdf8;
        color: #0f766e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .erp-section-note {
        margin-left: auto;
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
    }

    .erp-required {
        color: #dc2626;
        font-weight: 800;
    }

    .erp-help {
        display: block;
        margin-top: 5px;
        color: #6b7280;
        font-size: 12px;
        font-weight: 400;
    }

    .erp-form .form-group {
        margin-bottom: 20px;
    }

    .erp-form label,
    .erp-form .control-label {
        font-size: 13px;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .erp-form .form-control,
    .erp-form .select2-container .select2-choice,
    .erp-form .select2-container--default .select2-selection--single {
        height: 46px !important;
        border-radius: 9px !important;
        border: 1px solid #d1d5db !important;
        box-shadow: none !important;
        font-size: 14px;
        color: #111827;
    }

    .erp-form .form-control:focus {
        border-color: #0f766e !important;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, .12) !important;
    }

    .erp-form textarea.form-control {
        min-height: 130px;
        height: auto !important;
    }

    .erp-form .select2-container {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    .erp-form select.select2-hidden-accessible,
    .erp-form select.select2-offscreen {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        border: 0 !important;
        clip: rect(0 0 0 0) !important;
        overflow: hidden !important;
    }

    .erp-form .select2-container .select2-choice {
        width: 100% !important;
        height: 46px !important;
        min-height: 46px !important;
        padding: 0 38px 0 12px !important;
        background: #fff !important;
        line-height: 44px !important;
    }

    .erp-form .select2-container .select2-choice > .select2-chosen {
        line-height: 44px !important;
    }

    .erp-form .select2-container .select2-choice .select2-arrow {
        width: 36px !important;
        height: 44px !important;
        border-left: 0 !important;
        background: transparent !important;
    }

    .erp-form .select2-container--default .select2-selection--single {
        width: 100% !important;
        height: 46px !important;
        min-height: 46px !important;
        background: #fff !important;
    }

    .erp-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 12px !important;
        padding-right: 38px !important;
        line-height: 44px !important;
    }

    .erp-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        width: 36px !important;
        height: 44px !important;
    }

    .select2-drop,
    .select2-dropdown,
    .select2-container--open {
        z-index: 999999 !important;
    }

    .erp-input-group {
        display: flex;
        gap: 8px;
    }

    .erp-input-group .form-control {
        flex: 1;
    }

    .erp-input-group .btn {
        height: 46px;
        white-space: nowrap;
    }

    .quick-add-link {
        float: right;
        font-size: 12px;
        font-weight: 800;
        color: #0f766e;
        cursor: pointer;
    }

    .code-status {
        display: block;
        min-height: 18px;
        margin-top: 6px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    .code-status:empty {
        display: none;
        min-height: 0;
        margin-top: 0;
    }

    .code-status.ok { color: #15803d; }
    .code-status.bad { color: #dc2626; }
    .code-status.checking { color: #ca8a04; }

    .erp-form .form-group.validation-cleared .form-control {
        border-color: #22a447 !important;
    }

    .erp-form .form-group.validation-cleared label.error,
    .erp-form .form-group.validation-cleared .help-block.validation-error,
    .erp-form .form-group.validation-cleared .form-error {
        display: none !important;
    }

    .erp-file-box {
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 12px;
        padding: 14px;
    }

    .erp-file-box input[type="file"] {
        width: 100%;
        padding: 10px;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 9px;
    }

    .erp-actions {
        position: sticky;
        bottom: 0;
        z-index: 20;
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: 16px 26px;
        margin: 22px -26px 0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        box-shadow: 0 -8px 18px rgba(15, 23, 42, .04);
    }

    .erp-form .btn {
        border-radius: 9px !important;
        padding: 10px 18px !important;
        font-weight: 800;
    }

    .erp-form .btn-sm,
    .erp-form .btn-xs {
        padding: 6px 10px !important;
        border-radius: 7px !important;
    }

    .erp-form .btn-primary,
    .erp-form .btn-success {
        background: #0f766e !important;
        border-color: #0f766e !important;
        color: #fff !important;
    }

    .erp-form .btn-default {
        background: #f3f4f6 !important;
        border-color: #d1d5db !important;
        color: #374151 !important;
    }

    .erp-form .btn-danger {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
    }

    @media (max-width: 767px) {
        .erp-page-header {
            gap: 9px;
            padding: 13px 14px;
        }

        .erp-page-title {
            gap: 9px;
            font-size: 16px;
        }

        .erp-page-title i {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
            border-radius: 10px;
            font-size: 16px;
        }

        .erp-listing-btn {
            min-height: 38px;
            padding: 8px 11px !important;
            font-size: 11px;
        }

        .erp-body {
            padding: 16px 14px 0;
        }

        .erp-section {
            padding: 15px 14px 4px;
            margin-bottom: 14px;
        }

        .erp-section-note {
            display: none;
        }

        .erp-input-group {
            display: block;
        }

        .erp-input-group .btn {
            width: 100%;
            margin-top: 8px;
        }

        #code_status {
            width: 100%;
            clear: both;
        }

        .quick-add-link {
            float: none;
            display: inline-block;
            margin-left: 8px;
        }

        .erp-actions {
            margin-left: -14px;
            margin-right: -14px;
            padding: 12px 14px;
            display: block;
        }

        .erp-actions .btn {
            width: 100%;
            margin-bottom: 8px;
        }

        .erp-actions .btn:last-child {
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
        margin-left: 0 !important;
        padding-top: 0 !important;
        min-height: 100vh !important;
        background: #f4f7fb !important;
    }

    .content {
        padding: 0 10px 10px !important;
        margin: 0 !important;
    }

    .erp-page {
        max-width: 100% !important;
        margin: 0 !important;
    }

    .erp-card {
        border-radius: 14px !important;
        margin-bottom: 0 !important;
        box-shadow: none !important;
    }

    .erp-page-header {
        padding: 13px 14px !important;
    }

    .erp-body {
        padding: 14px !important;
    }

    .erp-section {
        padding: 15px 14px 4px !important;
        margin-bottom: 14px !important;
    }

    .erp-actions {
        margin: 15px -14px 0 !important;
        padding: 12px 14px !important;
    }

    .modal-dialog {
        width: auto !important;
        margin: 15px !important;
    }
</style>
<?php } ?>

<section class="content">
    <div class="erp-page">
        <div class="erp-card erp-form">

            <div class="erp-page-header" style="display:none;">
                <h4 class="erp-page-title">
                    <i class="fa fa-cube"></i>
                    <span><?= html_escape($page_title); ?></span>
                </h4>

                <div class="erp-header-actions">
                    <a href="<?= site_url('products') . $app_query; ?>" class="erp-listing-btn">
                        <i class="fa fa-list"></i>
                        <span><?= html_escape($labels['list_products']); ?></span>
                    </a>
                </div>
            </div>

            <div class="erp-body">
                <?= form_open_multipart('products/add', 'class="validation" id="product_add_form"'); ?>

                <input type="hidden" name="save_action" id="save_action" value="save">

                <!--
                    မပြတော့သည့် မူလအကွက်များအတွက် Controller/Database compatible default values
                -->
                <input type="hidden" name="type" value="standard">
                <input type="hidden" name="barcode_symbology" value="code128">
                <input type="hidden" name="cost" value="0">
                <input type="hidden" name="price" value="0">
                <input type="hidden" name="product_tax" value="0">
                <input type="hidden" name="tax_method" value="0">

                <?php if ($is_app_mode) { ?>
                    <input type="hidden" name="app" value="1">
                    <input type="hidden" name="app_lang" value="<?= html_escape($app_language); ?>">
                <?php } ?>

                <!-- ကုန်ပစ္စည်းအချက်အလက် -->
                <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-group-lg">
                                <label for="name">
                                    <?= html_escape($labels['name']); ?>
                                    <span class="erp-required">*</span>
                                </label>
                                <?= form_input(
                                    'name',
                                    set_value('name'),
                                    'class="form-control tip" id="name" required="required" placeholder="' .
                                    html_escape($labels['enter_product_name']) . '"'
                                ); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-group-lg">
                                <label for="code">
                                    <?= html_escape($labels['code_barcode']); ?>
                                    <span class="erp-required">*</span>
                                </label>

                                <div class="erp-input-group">
                                    <?= form_input(
                                        'code',
                                        set_value('code'),
                                        'class="form-control tip" id="code" required="required" autocomplete="off" placeholder="' .
                                        html_escape($labels['enter_product_code']) . '"'
                                    ); ?>
                                    <span class="erp-help"><?= html_escape($labels['can_use_barcode']); ?></span>
                                    <button type="button" class="btn btn-default" id="btn_generate_code">
                                        <i class="fa fa-magic"></i>
                                        <?= html_escape($product_page_labels['auto_code']); ?>
                                    </button>
                                </div>

                                
                                <span id="code_status" class="code-status" aria-live="polite"></span>

                                
                                
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-group-lg">
                                <label for="category">
                                    <?= html_escape($labels['category']); ?>
                                    <span class="erp-required">*</span>

                                    <span class="quick-add-link" data-toggle="modal" data-target="#quickCategoryModal">
                                        <i class="fa fa-plus"></i>
                                        <?= html_escape($labels['add_category']); ?>
                                    </span>
                                </label>

                                <?= form_dropdown(
                                    'category',
                                    $category_dropdown,
                                    set_value('category'),
                                    'class="form-control select2 erp-select2 tip" id="category" required="required" style="width:100%;"'
                                ); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-group-lg">
                                <label for="brand">
                                    <?= html_escape($labels['brand']); ?>

                                    <?php if (!empty($Admin) || !empty($Owner)) { ?>
                                        <span class="quick-add-link" data-toggle="modal" data-target="#quickBrandModal">
                                            <i class="fa fa-plus"></i>
                                            <?= html_escape($labels['add_brand']); ?>
                                        </span>
                                    <?php } ?>
                                </label>

                                <?= form_dropdown(
                                    'brand_id',
                                    $brand_dropdown,
                                    set_value('brand_id'),
                                    'class="form-control select2 erp-select2 tip" id="brand_id" style="width:100%;"'
                                ); ?>
                            </div>
                        </div>
                    </div>

                <!-- ကုန်ပစ္စည်းယူနစ်သတ်မှတ်ချက် -->
                <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-group-lg">
                                <label for="unit">
                                    <?= html_escape($labels['unit']); ?>
                                    <span class="erp-required">*</span>

                                    <span class="quick-add-link" data-toggle="modal" data-target="#quickUnitModal">
                                        <i class="fa fa-plus"></i>
                                        <?= html_escape($labels['add_unit']); ?>
                                    </span>
                                </label>

                                <?= form_dropdown(
                                    'unit',
                                    $unit_dropdown,
                                    set_value('unit'),
                                    'class="form-control select2 erp-select2 tip" id="unit" required="required" style="width:100%;"'
                                ); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-group-lg">
                                <label for="alert_quantity">
                                    <?= html_escape($labels['alert_quantity']); ?>
                                </label>

                                <?= form_input(
                                    'alert_quantity',
                                    set_value('alert_quantity', 0),
                                    'class="form-control tip" id="alert_quantity" required="required" placeholder="0"  min="0" step="1" inputmode="numeric"'
                                ); ?>

                                <span class="erp-help"><?= html_escape($labels['stock_alert_help']); ?></span>
                            </div>
                        </div>
                    </div>

                <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-group-lg erp-file-box">
                                <label for="image"><?= html_escape($labels['image']); ?></label>
                                <input type="file" name="userfile" id="image">
                                <span class="erp-help"><?= html_escape($labels['image_help']); ?></span>
                            </div>
                        </div>
                </div>
                
                <div class="form-group form-group-lg">
                        <label for="details"><?= html_escape($labels['details']); ?></label>
                        <?= form_textarea(
                            'details',
                            set_value('details'),
                            'class="form-control tip redactor" id="details" placeholder="' .
                            html_escape($labels['details_placeholder']) . '"'
                        ); ?>
                    </div>
                    
                <!-- ပုံနှင့် မှတ်ချက် -->
                <div class="erp-actions">
                        <a href="<?= site_url('products') . $app_query; ?>" class="btn btn-default">
                            <i class="fa fa-times"></i>
                            <?= html_escape($labels['cancel']); ?>
                        </a>

                        <button type="submit" name="create" value="create" data-action="add_another"
                                class="btn btn-success save-product-btn">
                            <i class="fa fa-plus-circle"></i>
                            <?= html_escape($labels['save_add_another']); ?>
                        </button>

                        <button type="submit" name="create" value="create" data-action="save"
                                class="btn btn-primary save-product-btn">
                            <i class="fa fa-save"></i>
                            <?= html_escape($labels['create']); ?>
                        </button>
                    </div>

                <?= form_close(); ?>
            </div>
        </div>
    </div>
</section>

<!-- Quick Add Category Modal -->
<div class="modal fade" id="quickCategoryModal" tabindex="-1" role="dialog" aria-labelledby="quickCategoryModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="quick_category_form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= html_escape($labels['close']); ?>">&times;</button>
                    <h4 class="modal-title" id="quickCategoryModalLabel">
                        <i class="fa fa-folder"></i>
                        <?= html_escape($labels['add_category']); ?>
                    </h4>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>
                            <?= html_escape($labels['category_name']); ?>
                            <span class="erp-required">*</span>
                        </label>
                        <input type="text" name="name" id="quick_category_name" class="form-control" required
                               placeholder="<?= html_escape($labels['enter_category_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label><?= html_escape($labels['category_code']); ?></label>
                        <input type="text" name="code" id="quick_category_code" class="form-control"
                               placeholder="<?= html_escape($labels['auto_if_empty']); ?>">
                    </div>

                    <div id="quick_category_msg"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?= html_escape($labels['cancel']); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        <?= html_escape($labels['save_category']); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (!empty($Admin) || !empty($Owner)) { ?>
<!-- Quick Add Brand Modal -->
<div class="modal fade" id="quickBrandModal" tabindex="-1" role="dialog" aria-labelledby="quickBrandModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="quick_brand_form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= html_escape($labels['close']); ?>">&times;</button>
                    <h4 class="modal-title" id="quickBrandModalLabel">
                        <i class="fa fa-tag"></i>
                        <?= html_escape($labels['add_brand']); ?>
                    </h4>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>
                            <?= html_escape($labels['brand_name']); ?>
                            <span class="erp-required">*</span>
                        </label>
                        <input type="text" name="name" id="quick_brand_name" class="form-control"
                               maxlength="100" required placeholder="<?= html_escape($labels['enter_brand']); ?>">
                    </div>

                    <div class="form-group">
                        <label><?= html_escape($labels['brand_code']); ?></label>
                        <input type="text" name="code" id="quick_brand_code" class="form-control"
                               maxlength="50" placeholder="<?= html_escape($labels['auto_if_empty']); ?>">
                    </div>

                    <div id="quick_brand_msg"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?= html_escape($labels['cancel']); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        <?= html_escape($labels['save_brand']); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<!-- Quick Add Unit Modal -->
<div class="modal fade" id="quickUnitModal" tabindex="-1" role="dialog" aria-labelledby="quickUnitModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="quick_unit_form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?= html_escape($labels['close']); ?>">&times;</button>
                    <h4 class="modal-title" id="quickUnitModalLabel">
                        <i class="fa fa-balance-scale"></i>
                        <?= html_escape($labels['add_unit']); ?>
                    </h4>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>
                            <?= html_escape($labels['unit_name']); ?>
                            <span class="erp-required">*</span>
                        </label>
                        <input type="text" name="name" id="quick_unit_name" class="form-control" required
                               placeholder="<?= html_escape($labels['unit_name_example']); ?>">
                    </div>

                    <div class="form-group">
                        <label><?= html_escape($labels['unit_code']); ?></label>
                        <input type="text" name="code" id="quick_unit_code" class="form-control"
                               placeholder="<?= html_escape($labels['auto_if_empty']); ?>">
                    </div>

                    <div id="quick_unit_msg"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <?= html_escape($labels['cancel']); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        <?= html_escape($labels['save_unit']); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" charset="utf-8">
    var codeIsDuplicate = false;
    var checkCodeTimer = null;

    $(document).ready(function () {

        function initialiseProductSelect2(context, forceRebuild) {
            if (!$.fn.select2) {
                return;
            }

            var $context = context ? $(context) : $('.erp-form');
            var $selects = $context.is('select.erp-select2')
                ? $context
                : $context.find('select.erp-select2');

            $selects.each(function () {
                var $select = $(this);
                var isInitialised =
                    !!$select.data('select2') ||
                    $select.hasClass('select2-hidden-accessible') ||
                    $select.hasClass('select2-offscreen');

                if (isInitialised && !forceRebuild) {
                    $select.next('.select2-container').css('width', '100%');
                    return;
                }

                if (isInitialised) {
                    try {
                        $select.select2('destroy');
                    } catch (destroyError) {}
                }

                $select.siblings('.select2-container').remove();
                $select
                    .removeClass('select2-hidden-accessible select2-offscreen')
                    .removeAttr('data-select2-id')
                    .css('width', '100%');

                $select.find('option').removeAttr('data-select2-id');

                try {
                    $select.select2({
                        width: '100%',
                        dropdownAutoWidth: true,
                        minimumResultsForSearch: 0
                    });
                } catch (select2Error) {
                    try {
                        $select.select2();
                        $select.next('.select2-container').css('width', '100%');
                    } catch (fallbackError) {}
                }
            });
        }

        window.setTimeout(function () {
            initialiseProductSelect2($('.erp-form'), true);
        }, 100);

        function updateCsrf(res) {
            if (res && res.csrf_hash) {
                csrfHash = res.csrf_hash;
            }
        }

        function postAjax(url, data, successCallback, errorCallback) {
            data[csrfName] = csrfHash;

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (res) {
                    updateCsrf(res);
                    if (successCallback) {
                        successCallback(res);
                    }
                },
                error: function (xhr) {
                    if (errorCallback) {
                        errorCallback(xhr);
                    } else {
                        bootbox.alert(productPageLabels.request_failed_try_again);
                    }
                }
            });
        }

        function setSelectValue(selector, value) {
            var $select = $(selector);
            $select.val(String(value));

            try {
                if ($select.data('select2')) {
                    $select.trigger('change.select2');
                }
            } catch (error) {}

            try {
                $select.select2('val', value);
            } catch (error) {
                $select.trigger('change');
            }
        }

        function setCodeStatus(type, message) {
            $('#code_status')
                .removeClass('ok bad checking')
                .addClass(type || '')
                .html(message || '');
        }

        function clearProductCodeValidation(revalidate) {
            var $field = $('#code');
            var $form = $('#product_add_form');
            var $group = $field.closest('.form-group');
            var fieldId = $field.attr('id');
            var fieldName = $field.attr('name');

            if ($field.length === 0) {
                return;
            }

            $field
                .removeClass('error invalid is-invalid')
                .attr('aria-invalid', 'false');

            if ($field[0] && typeof $field[0].setCustomValidity === 'function') {
                $field[0].setCustomValidity('');
            }

            $group
                .removeClass('has-error has-danger error')
                .addClass('validation-cleared');

            if (fieldId) {
                $group.find('label.error[for="' + fieldId + '"]').remove();
            }

            if (fieldName) {
                $group.find(
                    '.help-block[data-bv-for="' + fieldName + '"],' +
                    'small[data-bv-for="' + fieldName + '"],' +
                    '.form-error[data-field="' + fieldName + '"]'
                ).remove();
            }

            $group.find('label.error, .validation-error, .form-error').remove();

            $group.find('.help-block').filter(function () {
                var message = $.trim($(this).text()).toLowerCase();
                return message === 'please enter a value'
                    || message === 'this field is required'
                    || message === 'required';
            }).remove();

            if (revalidate !== true || $.trim($field.val()) === '') {
                return;
            }

            try {
                var jqueryValidator = $form.data('validator');
                if (jqueryValidator && typeof jqueryValidator.element === 'function') {
                    jqueryValidator.element($field[0]);
                }
            } catch (jqueryValidateError) {}

            try {
                var bootstrapValidator = $form.data('bootstrapValidator');
                if (
                    bootstrapValidator
                    && typeof bootstrapValidator.revalidateField === 'function'
                ) {
                    bootstrapValidator.revalidateField(fieldName);
                }
            } catch (bootstrapValidatorError) {}

            try {
                var formValidation = $form.data('formValidation');
                if (
                    formValidation
                    && typeof formValidation.revalidateField === 'function'
                ) {
                    formValidation.revalidateField(fieldName);
                }
            } catch (formValidationError) {}
        }

        function applyGeneratedProductCode(generatedCode) {
            var $code = $('#code');

            clearTimeout(checkCodeTimer);

            $code
                .val($.trim(String(generatedCode || '')))
                .trigger('input')
                .trigger('change');

            clearTimeout(checkCodeTimer);
            clearProductCodeValidation(true);
            checkProductCode();
        }

        function checkProductCode() {
            var code = $.trim($('#code').val());

            if (code === '') {
                codeIsDuplicate = false;
                setCodeStatus('', '');
                $('.save-product-btn').prop('disabled', false);
                $('#code').closest('.form-group').removeClass('validation-cleared');
                return;
            }

            clearProductCodeValidation(false);

            setCodeStatus(
                'checking',
                '<i class="fa fa-spinner fa-spin"></i> ' + productPageLabels.checking_code
            );

            postAjax(site_url + 'products/check_code', { code: code }, function (res) {
                if (res.status === 'success') {
                    if (res.exists) {
                        codeIsDuplicate = true;
                        setCodeStatus(
                            'bad',
                            '<i class="fa fa-times-circle"></i> ' + productPageLabels.code_already_exists
                        );
                        $('.save-product-btn').prop('disabled', true);
                    } else {
                        codeIsDuplicate = false;
                        setCodeStatus(
                            'ok',
                            '<i class="fa fa-check-circle"></i> ' + productPageLabels.code_available
                        );
                        clearProductCodeValidation(true);
                        $('.save-product-btn').prop('disabled', false);
                    }
                } else {
                    codeIsDuplicate = false;
                    setCodeStatus(
                        'bad',
                        res.message ? res.message : productPageLabels.unable_check_code
                    );
                    $('.save-product-btn').prop('disabled', false);
                }
            });
        }

        $('#code').on('input keyup change blur', function () {
            var $field = $(this);

            clearTimeout(checkCodeTimer);

            if ($.trim($field.val()) !== '') {
                clearProductCodeValidation(false);
            } else {
                $field.closest('.form-group').removeClass('validation-cleared');
            }

            checkCodeTimer = setTimeout(checkProductCode, 450);
        });

        $('#btn_generate_code').on('click', function () {
            var $button = $(this);

            $button
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> ' + productPageLabels.generating);

            $.ajax({
                url: site_url + 'products/generate_code',
                type: 'GET',
                dataType: 'json',
                success: function (res) {
                    var generatedCode = (res && res.status === 'success' && res.code)
                        ? res.code
                        : 'P' + new Date().getTime();

                    applyGeneratedProductCode(generatedCode);
                },
                error: function () {
                    applyGeneratedProductCode('P' + new Date().getTime());
                },
                complete: function () {
                    $button
                        .prop('disabled', false)
                        .html('<i class="fa fa-magic"></i> ' + productPageLabels.auto_code);
                }
            });
        });

        $('.save-product-btn').on('click', function () {
            $('#save_action').val($(this).data('action'));
        });

        $('#product_add_form').on('submit', function (e) {
            var $code = $('#code');
            var trimmedCode = $.trim($code.val());

            $code.val(trimmedCode);

            if (trimmedCode !== '') {
                clearProductCodeValidation(true);
            }

            if (codeIsDuplicate) {
                e.preventDefault();
                bootbox.alert(productPageLabels.product_code_exists_change);
                $code.trigger('focus');
                return false;
            }
        });

        $('#quick_category_form').on('submit', function (e) {
            e.preventDefault();

            postAjax(site_url + 'products/quick_add_category', {
                name: $('#quick_category_name').val(),
                code: $('#quick_category_code').val()
            }, function (res) {
                if (res.status === 'success') {
                    $('#category').append(
                        $('<option>', {
                            value: res.id,
                            text: res.name
                        })
                    );

                    setSelectValue('#category', res.id);
                    $('#quick_category_form')[0].reset();
                    $('#quickCategoryModal').modal('hide');
                    $('#quick_category_msg').html('');
                } else {
                    $('#quick_category_msg').html(
                        '<div class="alert alert-danger">' + res.message + '</div>'
                    );
                }
            });
        });

        $('#quick_unit_form').on('submit', function (e) {
            e.preventDefault();

            postAjax(site_url + 'products/quick_add_unit', {
                name: $('#quick_unit_name').val(),
                code: $('#quick_unit_code').val()
            }, function (res) {
                if (res.status === 'success') {
                    $('#unit').append(
                        $('<option>', {
                            value: res.id,
                            text: res.name
                        })
                    );

                    setSelectValue('#unit', res.id);
                    $('#quick_unit_form')[0].reset();
                    $('#quickUnitModal').modal('hide');
                    $('#quick_unit_msg').html('');
                } else {
                    $('#quick_unit_msg').html(
                        '<div class="alert alert-danger">' + res.message + '</div>'
                    );
                }
            });
        });

        $('#quick_brand_form').on('submit', function (e) {
            e.preventDefault();

            postAjax(site_url + 'products/quick_add_brand', {
                name: $('#quick_brand_name').val(),
                code: $('#quick_brand_code').val()
            }, function (res) {
                if (res.status === 'success') {
                    $('#brand_id').append(
                        $('<option>', {
                            value: res.id,
                            text: res.name
                        })
                    );

                    setSelectValue('#brand_id', res.id);
                    $('#quick_brand_form')[0].reset();
                    $('#quickBrandModal').modal('hide');
                    $('#quick_brand_msg').html('');
                } else {
                    $('#quick_brand_msg').html(
                        '<div class="alert alert-danger">' + res.message + '</div>'
                    );
                }
            });
        });

        $(window).on('resize orientationchange', function () {
            window.setTimeout(function () {
                $('.erp-form .select2-container').css('width', '100%');
            }, 100);
        });
    });
    
    // ALERT QUANTITY NUMERIC ONLY 
    document.getElementById('alert_quantity').addEventListener('keydown', function (e) {
        // Allow: backspace, delete, tab, escape, enter, arrows
        const allowedKeys = [
            'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
            'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'
        ];
    
        if (allowedKeys.includes(e.key)) {
            return;
        }
    
        // Allow only 0-9
        if (!/^[0-9]$/.test(e.key)) {
            e.preventDefault();
        }
    });
</script>