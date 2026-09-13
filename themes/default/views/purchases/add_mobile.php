<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$app_language =
    $this->input->get('app_lang', true) ?:
    ($this->input->post('app_lang', true) ?: $Settings->selected_language);

$app_query = $is_app_mode
    ? '?app=1&app_lang=' . rawurlencode($app_language)
    : '';

$active_language = strtolower(trim((string) $app_language));

$is_myanmar =
    in_array($active_language, ['myanmar', 'burmese', 'mm', 'my'], true) ||
    stripos($active_language, 'myanmar') !== false ||
    stripos($active_language, 'burmese') !== false;

$T = function ($mm, $en) use ($is_myanmar) {
    return $is_myanmar ? $mm : $en;
};


// =========================================================
// Supplier Options
// =========================================================
$supplier_options = [
    '' => $T(
        'ပေးသွင်းသူ ရွေးချယ်ရန်',
        'Select Supplier'
    )
];

foreach ($suppliers as $supplier) {
    $supplier_options[$supplier->id] = $supplier->name;
}


// =========================================================
// Store Options
// =========================================================
$store_options = [
    '' => $T(
        'တည်နေရာ ရွေးချယ်ရန်',
        'Select Location'
    )
];

foreach ($stores as $store) {
    $store_options[$store->id] = $store->name;
}


$session_store_id =
    (int) $this->session->userdata('store_id');

$default_store_id =
    set_value(
        'store',
        $session_store_id ?: ''
    );


// =========================================================
// Received Options
// =========================================================
$received_options = [
    1 => $T(
        'လက်ခံပြီး',
        'Received'
    ),

    0 => $T(
        'မလက်ခံရသေး',
        'Not Received Yet'
    ),
];


// =========================================================
// Labels
// =========================================================
$easy_purchase_labels = [

    'quantity' =>
        $T(
            'ဝယ်ယူအရေအတွက်',
            'Purchase Quantity'
        ),

    'unit' =>
        $T(
            'ဝယ်ယူယူနစ်',
            'Purchase Unit'
        ),

    'unitCost' =>
        $T(
            'ရွေးထားသောယူနစ်၏ ဝယ်ဈေး',
            'Cost per Selected Unit'
        ),

    'baseQuantity' =>
        $T(
            'အခြေခံယူနစ်ဖြင့် အရေအတွက်',
            'Quantity in Base Unit'
        ),

    'subtotal' =>
        $T(
            'ကျသင့်ငွေ',
            'Subtotal'
        ),

    'transportation' =>
        $T(
            'သယ်ယူပို့ဆောင်ခ',
            'Transportation'
        ),

    'delete' =>
        $T(
            'ဖျက်ရန်',
            'Delete'
        ),

    'emptyItems' =>
        $T(
            'အပေါ်ရှိ အကွက်မှ ကုန်ပစ္စည်းရှာပြီး ထည့်ပါ။',
            'Search for a product above to add it.'
        ),

    'noMatch' =>
        $T(
            'ကိုက်ညီသော ကုန်ပစ္စည်း မတွေ့ပါ။',
            'No matching product was found.'
        ),

    'addAtLeastOne' =>
        $T(
            'အနည်းဆုံး ကုန်ပစ္စည်းတစ်မျိုး ထည့်ပါ။',
            'Add at least one product.'
        ),

    'checkItems' =>
        $T(
            'ကုန်ပစ္စည်းအရေအတွက်၊ ယူနစ်နှင့် ဝယ်ဈေးများကို ပြန်စစ်ပါ။',
            'Check the quantity, unit, and cost for each item.'
        ),

    'resetConfirm' =>
        $T(
            'ထည့်ထားသော ကုန်ပစ္စည်းအားလုံးကို ရှင်းမှာ သေချာပါသလား။',
            'Clear all entered purchase items?'
        ),

    'selectDate' =>
        $T(
            'ရက်စွဲနှင့် အချိန် ရွေးချယ်ပါ။',
            'Please select date and time.'
        ),

    'selectSupplier' =>
        $T(
            'ပေးသွင်းသူ ရွေးချယ်ပါ။',
            'Please select a supplier.'
        ),

    'selectStore' =>
        $T(
            'သိုလှောင်မည့်တည်နေရာ ရွေးချယ်ပါ။',
            'Please select a receiving location.'
        ),

    'saving' =>
        $T(
            'သိမ်းနေသည်...',
            'Saving...'
        ),

    'savePurchase' =>
        $T(
            'ဝယ်ယူမှု သိမ်းရန်',
            'Save Purchase'
        ),
];
?>


<style>

.content {
    padding-top: 18px;
    background: #f4f7fb;
}

.easy-po-page {
    max-width: 1320px;
    margin: 0 auto;
}

.easy-po-card {
    overflow: hidden;
    margin-bottom: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
}

.dual-qty-extra {
    position: relative;
    display: block;
    margin-top: 8px;
    padding-left: 30px;
}

.dual-qty-plus {
    position: absolute;
    top: 50%;
    left: 5px;
    transform: translateY(-50%);
    color: #0f766e;
    font-size: 20px;
    font-weight: 800;
    line-height: 1;
}

#poTable .secondary-purchase-qty {
    width: 100%;
    min-width: 0;
    padding-right: 72px;
    border-color: #99d8cf;
    background: #f8fffd;
}

.dual-qty-unit {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    max-width: 62px;
    overflow: hidden;
    color: #0f766e;
    font-size: 13px;
    font-weight: 700;
    line-height: 1;
    text-overflow: ellipsis;
    white-space: nowrap;
    pointer-events: none;
}


/* =========================================================
   HEADER
========================================================= */

.easy-po-header {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 12px;
    padding: 17px 21px;
    border-bottom: 1px solid #e6edf3;
    background: linear-gradient(
        135deg,
        #fff 0%,
        #f7fbfc 100%
    );
}

.easy-po-title {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
    margin: 0;
    color: #111827;
    font-size: 20px;
    font-weight: 900;
}

.easy-po-title i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    border-radius: 11px;
    background: #e6f4f1;
    color: #0f766e;
}

.easy-po-subtitle {
    margin-top: 5px;
    color: #64748b;
    font-size: 16px;
}

.easy-po-list-btn {
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
    font-weight: 800;
    text-decoration: none !important;
    white-space: nowrap;
}


/* =========================================================
   BODY
========================================================= */

.easy-po-body {
    padding: 20px 22px 0;
}

.easy-po-section {
    margin-bottom: 17px;
    padding: 17px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #fff;
}

.easy-po-section-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 15px;
    padding-bottom: 11px;
    border-bottom: 1px solid #eef2f7;
    color: #0f172a;
    font-size: 16px;
    font-weight: 900;
}

.easy-po-section-title i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 31px;
    height: 31px;
    border-radius: 9px;
    background: #eefdf8;
    color: #0f766e;
}


/* =========================================================
   FORM
========================================================= */

.easy-po-form label {
    margin-bottom: 7px;
    color: #1f2937;
    font-size: 16px;
    font-weight: 800;
}

.easy-po-required {
    color: #dc2626;
}

.easy-po-form .form-group {
    margin-bottom: 15px;
}

.easy-po-form .form-control,
.easy-po-form .select2-container .select2-choice,
.easy-po-form
.select2-container--default
.select2-selection--single {

    width: 100% !important;
    height: 44px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 9px !important;
    background: #fff !important;
    color: #111827;
    box-shadow: none !important;
}

.easy-po-form .form-control:focus,
.easy-po-form
.select2-container-active
.select2-choice,
.easy-po-form
.select2-container--focus
.select2-selection--single,
.easy-po-form
.select2-container--open
.select2-selection--single {

    border-color: #0f766e !important;

    box-shadow:
        0 0 0 3px
        rgba(15, 118, 110, .12)
        !important;
}

.easy-po-form .select2-container {
    display: block !important;
    width: 100% !important;
}

.easy-po-form
.select2-container
.select2-choice,
.easy-po-form
.select2-container
.select2-choice > .select2-chosen {

    line-height: 42px !important;
}

.easy-po-form
.select2-container--default
.select2-selection--single
.select2-selection__rendered {

    line-height: 42px !important;
}

.easy-po-form
.select2-container--default
.select2-selection--single
.select2-selection__arrow {

    height: 42px !important;
}


/* =========================================================
   TOP GRID
========================================================= */

.easy-po-top-grid {
    display: grid;
    grid-template-columns:
        repeat(4, minmax(0, 1fr));
    gap: 12px;
}


/* =========================================================
   SEARCH
========================================================= */

.easy-po-search-box {
    position: relative;
    padding: 13px;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
}

.easy-po-search-box > i {
    position: absolute;
    top: 57px;
    left: 28px;
    z-index: 3;
    color: #94a3b8;
}

.easy-po-search-box input {
    padding-left: 41px;
}

.easy-po-search-help {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 16px;
}

.easy-po-guide {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin: 12px 0 0;
    padding: 10px 12px;
    border: 1px solid #ccfbf1;
    border-radius: 10px;
    background: #f0fdfa;
    color: #115e59;
    font-size: 13px;
    line-height: 1.65;
}


/* =========================================================
   TABLE
========================================================= */

.easy-po-table-wrap {
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    -webkit-overflow-scrolling: touch;
}

#poTable {
    width: 100%;
    min-width: 1120px;
    margin: 0;
    border: 0;
}

#poTable th,
#poTable td {
    padding: 9px;
    border-color: #e5e7eb;
    vertical-align: middle;
    font-size: 16px;
}

#poTable thead th {
    background: #f8fafc;
    color: #334155;
    font-weight: 900;
    text-align: center;
    white-space: nowrap;
}

#poTable tfoot th {
    background: #f8fafc;
}

#poTable .form-control {
    height: 40px !important;
    min-width: 105px;
    padding: 6px 9px;
}

#poTable td[data-label] {
    height: 58px;
}

#poTable .purchase-qty,
#poTable .secondary-purchase-qty,
#poTable .purchase-cost,
#poTable .transportation {
    text-align: left;
}

#poTable .purchase-unit {
    min-width: 185px;
}

.po-product-name {
    color: #0f172a;
    font-size: 16px;
    font-weight: 900;
}

.po-product-code {
    margin-top: 3px;
    color: #64748b;
    font-size: 16px;
}

.po-base-preview {
    min-width: 185px;
    color: #0f766e;
    font-weight: 800;
    text-align: center;
}

.po-subtotal {
    min-width: 115px;
    color: #0f172a;
    font-size: 16px !important;
    font-weight: 900;
}

.po-delete-cell .btn {
    width: 34px;
    height: 34px;
    padding: 0 !important;
    border-radius: 8px !important;
}

.purchase-item-row.has-error {
    background: #fff1f2 !important;
}

.po-empty-cell {
    padding: 28px !important;
}


/* =========================================================
   TOTAL
========================================================= */

.easy-po-total-row {
    display: grid;
    grid-template-columns:
        repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-top: 13px;
}

.easy-po-summary-box {
    padding: 13px 15px;
    border: 1px solid #e5e7eb;
    border-radius: 11px;
    background: #f8fafc;
}

.easy-po-summary-box.total-box {
    border-color: #99f6e4;
    background: #f0fdfa;
}

.easy-po-summary-label {
    color: #64748b;
    font-size: 16px;
    font-weight: 800;
}

.easy-po-summary-value {
    margin-top: 4px;
    color: #0f172a;
    font-size: 20px;
    font-weight: 900;
}

.total-box .easy-po-summary-value {
    color: #0f766e;
}


/* =========================================================
   EXTRA DETAILS
========================================================= */

.easy-po-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 36px;
    padding: 7px 11px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #f8fafc;
    color: #475569;
    font-size: 16px;
    font-weight: 800;
}

.easy-po-extra-body {
    display: block;
    margin-top: 14px;
}

.easy-po-file {
    padding: 11px;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
}

.easy-po-form textarea.form-control {
    min-height: 105px;
    height: auto !important;
}


/* =========================================================
   ACTIONS
========================================================= */

.easy-po-actions {
    position: sticky;
    bottom: 0;
    z-index: 20;

    display: flex;
    justify-content: flex-end;
    gap: 9px;

    margin: 18px -22px 0;
    padding: 14px 22px;

    border-top: 1px solid #e5e7eb;

    background: #fff;

    box-shadow:
        0 -8px 18px
        rgba(15, 23, 42, .04);
}

.easy-po-actions .btn {
    min-height: 42px;
    padding: 9px 16px !important;
    border-radius: 9px !important;
    font-weight: 800;
}

.easy-po-actions .btn-primary,
.easy-po-actions .btn-success {

    border-color: #0f766e !important;
    background: #0f766e !important;
    color: #fff !important;
}

/* =========================================================
   Radio Button
========================================================= */
.easy-po-radio-group {
    display: flex;
    gap: 14px;
    align-items: center;
    min-height: 44px;
}

.easy-po-radio-option {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 0;
    padding: 9px 14px;
    border: 1px solid #d1d5db;
    border-radius: 9px;
    background: #fff;
    font-weight: 700;
    cursor: pointer;
}

.easy-po-radio-option input[type="radio"] {
    margin: 0;
}

.easy-po-radio-option:has(input:checked) {
    border-color: #0f766e;
    background: #f0fdfa;
    color: #0f766e;
}


/* =========================================================
   Z INDEX
========================================================= */

.select2-drop,
.select2-dropdown,
.select2-container--open,
.bootstrap-datetimepicker-widget.dropdown-menu,
.ui-autocomplete {

    z-index: 999999 !important;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 991px) {

    .easy-po-top-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }
    
    
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 767px) {

    .content {
        padding-top: 0;
    }

    .easy-po-header {
        padding: 13px 14px;
    }

    .easy-po-title {
        font-size: 16px;
    }

    .easy-po-title i {
        width: 37px;
        height: 37px;
        flex-basis: 37px;
    }

    .easy-po-subtitle,
    .easy-po-list-btn span {
        display: none;
    }

    .easy-po-list-btn {
        width: 38px;
        min-width: 38px;
        padding: 0 !important;
    }

    .easy-po-body {
        padding: 13px 13px 0;
    }

    .easy-po-section {
        padding: 13px;
    }

    /*.easy-po-top-grid {*/
    /*    grid-template-columns: 1fr;*/
    /*    gap: 3px;*/
    /*}*/
    .easy-po-top-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .easy-po-total-row {
        grid-template-columns: 1fr;
        gap: 7px;
    }

    .easy-po-actions {
        display: block;
        margin-right: -13px;
        margin-left: -13px;
        padding: 11px 13px;
    }

    .easy-po-actions .btn {
        width: 100%;
        margin-bottom: 7px;
    }

    .easy-po-actions .btn:last-child {
        margin-bottom: 0;
    }
}


/* =========================================================
   APP MODE
========================================================= */

<?php if ($is_app_mode) { ?>

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
    padding: 0 8px 8px !important;
}

.easy-po-page {

    max-width: 100% !important;
}

.easy-po-card {

    margin-bottom: 0 !important;
    border-radius: 14px !important;
    box-shadow: none !important;
}

<?php } ?>

</style>


<section class="content">

    <div class="easy-po-page">

        <div class="easy-po-card easy-po-form">


            <!-- =================================================
                 HEADER
            ================================================== -->

            <div class="easy-po-header" style="display:none;">

                <div>

                    <h4 class="easy-po-title">

                        <i class="fa fa-shopping-cart"></i>

                        <span>

                            <?= html_escape(
                                $T(
                                    'ကုန်ပစ္စည်းဝယ်ယူမှု ထည့်ရန်',
                                    'Add Purchase'
                                )
                            ); ?>

                        </span>

                    </h4>


                    <div class="easy-po-subtitle">

                        <?= html_escape(
                            $T(
                                'အရေအတွက်၊ ဝယ်ယူယူနစ်နှင့် အဲဒီယူနစ်ရဲ့ ဝယ်ဈေးကိုသာ ရိုက်ထည့်ပါ။',
                                'Enter the quantity, purchase unit, and cost for that unit.'
                            )
                        ); ?>

                    </div>

                </div>


                <a
                    href="<?= site_url('purchases') . $app_query; ?>"
                    class="easy-po-list-btn"
                >

                    <i class="fa fa-list"></i>

                    <span>

                        <?= html_escape(
                            $T(
                                'ဝယ်ယူမှုစာရင်း',
                                'Purchase List'
                            )
                        ); ?>

                    </span>

                </a>

            </div>


            <div class="easy-po-body">


                <!-- =================================================
                     IMPORTANT:
                     class="validation" removed
                     novalidate added
                ================================================== -->

                <?= form_open_multipart(
                    'purchases/add',
                    'id="purchase_add_form" novalidate autocomplete="off"'
                ); ?>


                <?php if ($is_app_mode) { ?>

                    <input
                        type="hidden"
                        name="app"
                        value="1"
                    >

                    <input
                        type="hidden"
                        name="app_lang"
                        value="<?= html_escape($app_language); ?>"
                    >

                <?php } ?>



                <!-- =================================================
                     PURCHASE INFORMATION
                ================================================== -->

                <div class="easy-po-section-title" style="display:none;">

                        <i class="fa fa-file-text-o"></i>

                        <?= html_escape(
                            $T(
                                'ဝယ်ယူမှုအချက်အလက်',
                                'Purchase Information'
                            )
                        ); ?>

                    </div>


                    <div class="easy-po-top-grid">


                        <!-- DATE -->

                        <div class="form-group">

                            <label for="date">

                                <?= html_escape(
                                    $T(
                                        'ရက်စွဲနှင့် အချိန်',
                                        'Date and Time'
                                    )
                                ); ?>

                                <span class="easy-po-required">*</span>

                            </label>


                            <?= form_input(

                                'date',

                                isset($_POST['date'])
                                    ? $_POST['date']
                                    : '',

                                'class="form-control datetimepicker"
                                 id="date"
                                 autocomplete="off"'

                            ); ?>

                        </div>



                        <!-- SUPPLIER -->

                        <div class="form-group">

                            <label for="supplier">

                                <?= html_escape(
                                    $T(
                                        'ကုန်ပစ္စည်းသွင်းသူ',
                                        'Supplier'
                                    )
                                ); ?>

                                <span class="easy-po-required">*</span>

                            </label>


                            <?= form_dropdown(

                                'supplier',

                                $supplier_options,

                                set_value('supplier'),

                                'class="form-control select2"
                                 id="supplier"
                                 style="width:100%;"'

                            ); ?>

                        </div>



                        <!-- STORE -->

                        <div class="form-group">

                            <label for="store">

                                <?= html_escape(
                                    $T(
                                        'သိုလှောင်မည့်တည်နေရာ',
                                        'Receiving Location'
                                    )
                                ); ?>

                                <span class="easy-po-required">*</span>

                            </label>


                            <?= form_dropdown(

                                'store',

                                $store_options,

                                $default_store_id,

                                'class="form-control select2"
                                 id="store"
                                 style="width:100%;"'

                            ); ?>

                        </div>



                        <!-- REFERENCE -->

                        <div class="form-group">

                            <label for="reference">

                                <?= html_escape(
                                    $T(
                                        'ဘောင်ချာနံပါတ်/ရည်ညွှန်းအမှတ်',
                                        'Voucher / Reference No.'
                                    )
                                ); ?>

                            </label>


                            <?= form_input(

                                'reference',

                                set_value('reference'),

                                'class="form-control"
                                 id="reference"
                                 autocomplete="off"'

                            ); ?>

                        </div>


                    </div>



                <!-- =================================================
                     ITEMS
                ================================================== -->

                <div class="easy-po-section-title" style="display:none;">

                        <i class="fa fa-cubes"></i>

                        <?= html_escape(
                            $T(
                                'ဝယ်ယူသည့် ကုန်ပစ္စည်းများ',
                                'Purchase Items'
                            )
                        ); ?>

                    </div>



                    <!-- PRODUCT SEARCH -->

                    <div class="easy-po-search-box">

                        <i class="fa fa-search"></i>


                        <label for="add_item">

                            <?= html_escape(
                                $T(
                                    'ကုန်ပစ္စည်းရှာရန်',
                                    'Search Product'
                                )
                            ); ?>

                        </label>


                        <input
                            type="text"
                            id="add_item"
                            class="form-control"
                            placeholder="<?= html_escape(
                                $T(
                                    'အမည်၊ ကုဒ် သို့မဟုတ် ဘားကုဒ်ဖြင့် ရှာရန်',
                                    'Search by name, code, or barcode'
                                )
                            ); ?>"
                            autocomplete="off"
                        >


                        <div class="easy-po-search-help">

                            <i class="fa fa-barcode"></i>

                            <?= html_escape(
                                $T(
                                    'ဘားကုဒ် scanner ဖြင့်လည်း ထည့်နိုင်ပါသည်။',
                                    'You can also add products using a barcode scanner.'
                                )
                            ); ?>

                        </div>

                    </div>

                    <!-- TABLE -->

                    <div
                        class="easy-po-table-wrap"
                        style="margin-top:13px;"
                    >

                        <table
                            id="poTable"
                            class="table table-striped table-bordered"
                        >

                            <thead>

                                <tr>

                                    <th style="width:20%;">

                                        <?= html_escape(
                                            $T(
                                                'ကုန်ပစ္စည်း',
                                                'Product'
                                            )
                                        ); ?>

                                    </th>


                                    <th style="width:10%;">

                                        <?= html_escape(
                                            $T(
                                                'အရေအတွက်',
                                                'Quantity'
                                            )
                                        ); ?>

                                    </th>


                                    <th style="width:19%;">

                                        <?= html_escape(
                                            $T(
                                                'ဝယ်ယူယူနစ်',
                                                'Purchase Unit'
                                            )
                                        ); ?>

                                    </th>


                                    <th style="width:13%;">

                                        <?= html_escape(
                                            $T(
                                                'တစ်ယူနစ်ဝယ်ဈေး',
                                                'Unit Cost'
                                            )
                                        ); ?>

                                    </th>


                                    <th style="width:17%;">

                                        <?= html_escape(
                                            $T(
                                                'အခြေခံယူနစ်ဖြင့်',
                                                'Base Quantity'
                                            )
                                        ); ?>

                                    </th>


                                    <th style="width:11%;">

                                        <?= html_escape(
                                            $T(
                                                'ကျသင့်ငွေ',
                                                'Subtotal'
                                            )
                                        ); ?>

                                    </th>


                                    <th style="width:8%;">

                                        <?= html_escape(
                                            $T(
                                                'သယ်ယူခ',
                                                'Transport'
                                            )
                                        ); ?>

                                    </th>


                                    <th style="width:2%;">

                                        <i class="fa fa-trash"></i>

                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <tr class="po-empty-row">

                                    <td
                                        colspan="8"
                                        class="text-center text-muted po-empty-cell"
                                    >

                                        <?= html_escape(
                                            $easy_purchase_labels['emptyItems']
                                        ); ?>

                                    </td>

                                </tr>

                            </tbody>


                            <tfoot>

                                <tr>

                                    <th
                                        colspan="5"
                                        class="text-right"
                                    >

                                        <?= html_escape(
                                            $T(
                                                'စုစုပေါင်း',
                                                'Total'
                                            )
                                        ); ?>

                                    </th>


                                    <th class="text-right">

                                        <span id="gtotal">
                                            0.00
                                        </span>

                                    </th>

                                    <th></th>

                                    <th></th>

                                </tr>

                            </tfoot>

                        </table>

                    </div>


                    <!-- GUIDE -->

                    <div class="easy-po-guide">

                        <i class="fa fa-info-circle"></i>

                        <div>

                            <?= html_escape(

                                $T(

                                    'ဥပမာ — ပါရာစီတမော ၂ ဖာ ဝယ်ပါက အရေအတွက် တွင် ၂ ကိုထည့်ပါ၊ ယူနစ် တွင် ဖာ ကိုရွေးချယ်ပြီး တစ်ဖာ၏ ဝယ်ဈေး ကို ဖြည့်သွင်းပါ။ စနစ်က ၁ ဖာ = ၁,၀၀၀ လုံး ဟု သတ်မှတ်ထားသဖြင့် ၂ ဖာ = ၂,၀၀၀ လုံး ကို အလိုအလျောက် တွက်ချက်၍ ပြသပေးမည်ဖြစ်သည်။',

                                    'Example — for two cartons, enter quantity 2, select Carton, and enter the cost per carton. The form shows the converted base quantity automatically.'

                                )

                            ); ?>

                        </div>

                    </div>
                    
                    <!-- TOTAL SUMMARY -->

                    <div class="easy-po-total-row">

                        <!-- DELIVERY -->
                        <div class="easy-po-summary-box">

                            <label
                                for="delivery"
                                class="easy-po-summary-label"
                            >

                                <?= html_escape(
                                    $T(
                                        'သယ်ယူပို့ဆောင်ခ စုစုပေါင်း',
                                        'Total Transportation'
                                    )
                                ); ?>

                            </label>


                            <input
                                type="number"
                                class="form-control"
                                id="delivery"
                                name="delivery"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            >

                        </div>
                        
                        
                        
                        <!-- GRAND TOTAL -->
                        <div class="easy-po-summary-box total-box">

                            <div class="easy-po-summary-label">

                                <?= html_escape(
                                    $T(
                                        'စုစုပေါင်းကျသင့်ငွေ',
                                        'Grand Total'
                                    )
                                ); ?>

                            </div>


                            <div
                                class="easy-po-summary-value"
                                id="erp_grand_total"
                            >
                                0.00
                            </div>

                        </div>
                        
                        <!-- PAID -->
                        <div class="easy-po-summary-box">

                            <label
                                for="paid"
                                class="easy-po-summary-label"
                            >
                                <?= html_escape(
                                    $T(
                                        'ယခုပေးချေမည့်ငွေ',
                                        'Paid Now'
                                    )
                                ); ?>
                            </label>
                        
                            <input
                                type="number"
                                class="form-control"
                                id="paid"
                                name="paid"
                                
                                value="<?= set_value('paid'); ?>"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                            >
                        
                        </div>
                

                    </div>



                
                    <div
                        class="easy-po-extra-body"
                        id="easy_po_extra_body"
                    >


                        <div class="row">


                            <!-- RECEIVED -->

                            <div class="col-sm-4">
                                <div class="form-group">
                            
                                    <label>
                            
                                        <?= html_escape(
                                            $T(
                                                'ပစ္စည်းလက်ခံမှု',
                                                'Receiving Status'
                                            )
                                        ); ?>
                            
                                    </label>
                            
                            
                                    <div class="easy-po-radio-group">
                            
                                        <?php
                                        $selected_received = set_value('received', 1);
                            
                                        foreach ($received_options as $value => $label) :
                                        ?>
                            
                                            <label class="easy-po-radio-option">
                            
                                                <input
                                                    type="radio"
                                                    name="received"
                                                    value="<?= html_escape($value); ?>"
                                                    <?= ((string) $selected_received === (string) $value) ? 'checked' : ''; ?>
                                                >
                            
                                                <span><?= html_escape($label); ?></span>
                            
                                            </label>
                            
                                        <?php endforeach; ?>
                            
                                    </div>
                            
                                </div>
                            </div>



                            <!-- FILE -->

                            <div class="col-sm-4">

                                <div class="form-group easy-po-file">

                                    <label for="attachment">

                                        <?= html_escape(
                                            $T(
                                                'ဘောင်ချာပုံတင်ရန်',
                                                'Voucher Attachment'
                                            )
                                        ); ?>

                                    </label>


                                    <input
                                        type="file"
                                        name="userfile"
                                        id="attachment"
                                    >

                                </div>

                            </div>



                            <!-- NOTE -->

                            <div class="col-sm-4">

                                <div class="form-group">

                                    <label for="note">

                                        <?= html_escape(
                                            $T(
                                                'မှတ်ချက်',
                                                'Note'
                                            )
                                        ); ?>

                                    </label>


                                    <?= form_textarea(

                                        'note',

                                        set_value('note'),

                                        'class="form-control"
                                         id="note"
                                         rows="3"'

                                    ); ?>

                                </div>

                            </div>


                        </div>

                    </div>
                



                

                <div class="easy-po-actions">


                    <a
                        href="<?= site_url('purchases') . $app_query; ?>"
                        class="btn btn-default"
                    >

                        <i class="fa fa-times"></i>

                        <?= html_escape(
                            $T(
                                'မလုပ်တော့ပါ',
                                'Cancel'
                            )
                        ); ?>

                    </a>



                    <button
                        type="button"
                        id="reset"
                        class="btn btn-danger"
                    >

                        <i class="fa fa-refresh"></i>

                        <?= html_escape(
                            $T(
                                'အကုန်ရှင်းရန်',
                                'Reset'
                            )
                        ); ?>

                    </button>



                    <!-- IMPORTANT: type=button -->

                    <button
                        type="button"
                        id="save_purchase_btn"
                        class="btn btn-primary"
                    >

                        <i class="fa fa-save"></i>

                        <?= html_escape(
                            $easy_purchase_labels['savePurchase']
                        ); ?>

                    </button>


                </div>


                <?= form_close(); ?>


            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     JS DATA
========================================================= -->

<script>

window.spoitems =
    window.spoitems || {};


if (localStorage.getItem('remove_spo')) {

    localStorage.removeItem('spoitems');

    localStorage.removeItem(
        'remove_spo'
    );
}


window.product_units =
    <?= json_encode(
        $product_units ?? [],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;


window.product_conversions =
    <?= json_encode(
        $product_conversions ?? [],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;


window.product_unit_prices =
    <?= json_encode(
        $product_unit_prices ?? [],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;


window.product_unit_conversions =
    <?= json_encode(
        $product_unit_conversions ?? [],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;


window.all_units =
    <?= json_encode(
        $this->site->getAllUnits(),
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;


window.easyPurchaseLabels =
    <?= json_encode(
        $easy_purchase_labels,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ); ?>;


window.easyPurchaseUnitDataUrl =
    <?= json_encode(
        site_url(
            'purchases/product_unit_data/'
        ),
        JSON_UNESCAPED_SLASHES
    ); ?>;

</script>

<!-- =========================================================
     PRODUCT SELECTION FALLBACK
     Main purchase JS က selected product ကို row မထည့်နိုင်သည့်အခါ
     unit data ဖြည့်ပြီး spoitems မှ table ကို ပြန်တည်ဆောက်ပေးသည်။
========================================================= -->
<script>
$(function () {
    'use strict';

    var productFallbackBusy = false;

    function fallbackReadItems() {
        var raw = null;

        try {
            if (typeof window.get === 'function') {
                raw = window.get('spoitems');
            }
        } catch (ignored) {}

        if (!raw) {
            try {
                raw = localStorage.getItem('spoitems');
            } catch (ignored) {}
        }

        if (!raw) {
            return {};
        }

        if (typeof raw === 'object') {
            return raw || {};
        }

        try {
            return JSON.parse(raw) || {};
        } catch (ignored) {
            return {};
        }
    }

    function fallbackSaveItems(items) {
        var json = JSON.stringify(items || {});

        try {
            if (typeof window.store === 'function') {
                window.store('spoitems', json);
            } else {
                localStorage.setItem('spoitems', json);
            }
        } catch (error) {
            try {
                localStorage.setItem('spoitems', json);
            } catch (ignored) {}
        }

        window.spoitems = items || {};
    }

    function fallbackProductId(item) {
        item = item || {};
        item.row = item.row || {};

        return String(
            item.row.id ||
            item.product_id ||
            item.item_id ||
            item.id ||
            item.value ||
            ''
        );
    }

    function fallbackFindKey(items, productId) {
        var foundKey = null;

        $.each(items || {}, function (key, storedItem) {
            if (fallbackProductId(storedItem) === String(productId)) {
                foundKey = key;
                return false;
            }
        });

        return foundKey;
    }

    function fallbackEnsureQuantity(item) {
        item = item || {};
        item.row = item.row || {};

        if (item.row.qty !== undefined) {
            item.row.qty = Math.max(1, parseFloat(item.row.qty) || 0);
        } else if (item.row.quantity !== undefined) {
            item.row.quantity = Math.max(1, parseFloat(item.row.quantity) || 0);
        } else if (item.qty !== undefined) {
            item.qty = Math.max(1, parseFloat(item.qty) || 0);
        } else {
            item.row.qty = 1;
        }

        return item;
    }

    function fallbackRender(items) {
        fallbackSaveItems(items);

        if (typeof window.loadItems === 'function') {
            window.loadItems();

            window.setTimeout(function () {
                $('#add_item').val('').trigger('focus');

                if (typeof window.updateErpPurchaseGrandTotal === 'function') {
                    window.updateErpPurchaseGrandTotal();
                }

                productFallbackBusy = false;
            }, 80);

            return;
        }

        productFallbackBusy = false;
        bootbox.alert(
            <?= json_encode(
                $T(
                    'ကုန်ပစ္စည်းစာရင်းတည်ဆောက်သည့် JavaScript မဖွင့်နိုင်ပါ။ purchases_easy_multiunit_conversion_fixed.js ဖိုင်တင်ထားခြင်းရှိ/မရှိ စစ်ပါ။',
                    'The purchase-item JavaScript could not be loaded. Check that purchases_easy_multiunit_conversion_fixed.js exists and is accessible.'
                ),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ); ?>
        );
    }

    function fallbackAddSelectedProduct(selectedItem, rowsBefore) {
        if (productFallbackBusy || !selectedItem) {
            return;
        }

        /*
         * Main handler ကို အရင်အလုပ်လုပ်ခွင့်ပေးမည်။ Row ဝင်ပြီးသားဆို
         * fallback က ထပ်မထည့်ဘဲ ရပ်မည်။
         */
        window.setTimeout(function () {
            var rowsAfter = $('#poTable tbody .purchase-item-row').length;

            if (rowsAfter > rowsBefore) {
                $('#add_item').val('');
                return;
            }

            var productId = fallbackProductId(selectedItem);

            if (!productId || productId === '0') {
                return;
            }

            productFallbackBusy = true;

            var items = fallbackReadItems();
            var existingKey = fallbackFindKey(items, productId);

            /*
             * Main handler က storage ထဲထည့်ပြီး render ပဲမလုပ်နိုင်ခဲ့လျှင်
             * quantity မတိုးဘဲ ရှိပြီးသား data ဖြင့် တန်းပြန်ဆွဲမည်။
             */
            if (existingKey !== null) {
                fallbackRender(items);
                return;
            }

            var itemKey = productId;

            function storeAndRender(hydratedItem) {
                var finalItem = hydratedItem || selectedItem;
                finalItem = fallbackEnsureQuantity(finalItem);
                items[itemKey] = finalItem;
                fallbackRender(items);
            }

            if (typeof window.fetchProductUnitData === 'function') {
                var callbackCalled = false;

                window.fetchProductUnitData(selectedItem, function (hydratedItem) {
                    if (callbackCalled) {
                        return;
                    }

                    callbackCalled = true;
                    storeAndRender(hydratedItem);
                });

                /* Network callback မပြန်လာလျှင် UI မပိတ်နေစေရန် */
                window.setTimeout(function () {
                    if (!callbackCalled) {
                        callbackCalled = true;
                        storeAndRender(selectedItem);
                    }
                }, 1500);

                return;
            }

            storeAndRender(selectedItem);
        }, 120);
    }

    $('#add_item')
        .off('autocompleteselect.easyPurchaseFallback')
        .on('autocompleteselect.easyPurchaseFallback', function (event, ui) {
            var rowsBefore = $('#poTable tbody .purchase-item-row').length;
            fallbackAddSelectedProduct(ui && ui.item ? ui.item : null, rowsBefore);
        });
});
</script>



<!-- =========================================================
     DATE TIME
========================================================= -->

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js"></script>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>



<!-- =========================================================
     PURCHASE JS
========================================================= -->

<script src="<?= $assets ?>dist/js/purchases_easy_multiunit_conversion_fixed.js?v=<?= time(); ?>"></script>



<!-- =========================================================
     PAGE JS
========================================================= -->

<script>

$(function () {


    // =====================================================
    // SELECT2
    // =====================================================

    function rebuildMainSelect2() {

        if (!$.fn.select2) {
            return;
        }


        $('.easy-po-form select.select2')
            .each(function () {


                var $select =
                    $(this);


                var selectedValue =
                    $select.val();


                try {

                    $select.select2(
                        'destroy'
                    );

                } catch (ignored) {}


                $select
                    .siblings(
                        '.select2-container'
                    )
                    .remove();


                $select
                    .removeClass(
                        'select2-hidden-accessible select2-offscreen'
                    )
                    .removeAttr(
                        'data-select2-id aria-hidden tabindex'
                    )
                    .css(
                        'width',
                        '100%'
                    );


                $select
                    .find('option')
                    .removeAttr(
                        'data-select2-id'
                    );


                try {

                    $select.select2({

                        width:
                            '100%',

                        dropdownAutoWidth:
                            true,

                        minimumResultsForSearch:
                            0
                    });

                } catch (error) {

                    try {

                        $select.select2();

                    } catch (ignored) {}

                }


                if (
                    selectedValue !== null
                ) {

                    $select.val(
                        selectedValue
                    );


                    try {

                        $select.trigger(
                            'change.select2'
                        );

                    } catch (ignored) {}


                    try {

                        $select.select2(
                            'val',
                            selectedValue
                        );

                    } catch (ignored) {}

                }


                $select
                    .next(
                        '.select2-container'
                    )
                    .css(
                        'width',
                        '100%'
                    );

            });
    }


    window.setTimeout(
        rebuildMainSelect2,
        120
    );



    // =====================================================
    // DATE TIME
    // =====================================================

    if ($.fn.datetimepicker) {

        $('.datetimepicker')
            .datetimepicker({

                format:
                    'YYYY-MM-DD HH:mm'

            });
    }



    // =====================================================
    // EXTRA DETAILS
    // =====================================================

    

});

</script>


<!-- =========================================================
     DELIVERY + GRAND TOTAL FIX
========================================================= -->
<script>
$(function () {

    function erpParseAmount(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        var number = parseFloat(
            String(value)
                .replace(/,/g, '')
                .replace(/[^\d.\-]/g, '')
        );

        return isNaN(number) ? 0 : number;
    }

    function erpFormatAmount(value) {
        var number = erpParseAmount(value);

        return number.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /*
     * IMPORTANT:
     * #gtotal ထဲမှာ row transportation[] ပေါင်းပြီးသားဖြစ်ပါတယ်။
     *
     * ထို့ကြောင့် Grand Total တွက်ရာတွင် transportation[] ကို
     * တစ်ကြိမ်နုတ်ပြီး delivery ကို တစ်ကြိမ်သာ ပြန်ပေါင်းပါမည်။
     *
     * Grand Total
     * = (#gtotal - SUM(transportation[])) + delivery
     *
     * ဥပမာ:
     *   Product subtotal = 10,000
     *   transportation[] = 1,000
     *   #gtotal          = 11,000
     *   delivery         = 1,000
     *
     *   Grand Total = (11,000 - 1,000) + 1,000
     *               = 11,000
     */
    window.updateErpPurchaseGrandTotal = function () {
        var currentTotal = erpParseAmount(
            $('#gtotal').text()
        );
        
        var rowTransportationTotal = 0;

        $('input[name="transportation[]"]').each(function () {
            rowTransportationTotal += erpParseAmount(
                $(this).val()
            );
        });
     
        var manualDelivery = erpParseAmount(
            $('#delivery').val()
        );
        
        var delivery =
            rowTransportationTotal > 0
                ? rowTransportationTotal
                : manualDelivery;


        var productOnlyTotal = currentTotal;

        /*
         * Floating / temporary render state ကြောင့် negative မဖြစ်စေရန်
         */
        if (productOnlyTotal < 0) {
            productOnlyTotal = 0;
        }
        
        var grandTotal =
            productOnlyTotal + delivery;
        
        if (rowTransportationTotal > 0) {
            $('#delivery').val(
                rowTransportationTotal.toFixed(2)
            );
        }

        $('#erp_grand_total').text(
            erpFormatAmount(grandTotal)
        );
        
        // ===== LOG =====
    console.log('ERP Grand Total Breakdown:', {
        gtotal_raw: currentTotal,
        rowTransportationTotal: rowTransportationTotal,
        productOnlyTotal: productOnlyTotal,
        delivery: delivery,
        grandTotal: grandTotal
    });
    // ================
    
        return grandTotal;
    };

    /*
     * Delivery ရိုက်ထည့်တာနဲ့ ချက်ချင်း update
     */
    $('#delivery').on(
        'input change keyup blur',
        function () {
            updateErpPurchaseGrandTotal();
        }
    );

    /*
     * External purchase JS က #gtotal ကို update လုပ်သည့်အခါ
     * Grand Total ကိုပါ အလိုအလျောက် update လုပ်ရန်။
     */
    var totalNode = document.getElementById('gtotal');

    if (totalNode && window.MutationObserver) {
        var totalObserver = new MutationObserver(function () {
            updateErpPurchaseGrandTotal();
        });

        totalObserver.observe(totalNode, {
            childList: true,
            characterData: true,
            subtree: true
        });
    }

    /*
     * Quantity / Unit / Cost / Transportation row changes
     * ဖြစ်သည့်အခါ external JS အရင်တွက်ပြီးမှ Grand Total ပြန်တွက်မည်။
     */
    $('#poTable').on(
        'input change keyup',
        'input, select',
        function () {
            window.setTimeout(function () {
                updateErpPurchaseGrandTotal();
            }, 0);
        }
    );

    /*
     * Initial display
     */
    updateErpPurchaseGrandTotal();
});
</script>


<!-- =========================================================
     REFRESH UNIT RESTORE FIX
     Refresh ပြီးနောက် localStorage ထဲက purchase item များ၏
     unit data ကို server မှ ပြန်ဖြည့်ပြီး Select2 ကို ပြန်တည်ဆောက်မည်။
========================================================= -->
<script>
$(function () {

    var refreshUnitRepairRunning = false;

    function cleanAndInitPurchaseUnitSelect2() {
        if (!$.fn.select2) {
            return;
        }

        $('#poTable select.purchase-unit').each(function () {
            var $select = $(this);
            var selectedValue = $select.val();

            /*
             * Stale / duplicated Select2 UI ကို ဖယ်ရှားပါ။
             */
            try {
                if (
                    $select.data('select2') ||
                    $select.hasClass('select2-hidden-accessible') ||
                    $select.hasClass('select2-offscreen')
                ) {
                    $select.select2('destroy');
                }
            } catch (ignored) {}

            $select.siblings('.select2-container').remove();

            try {
                $select.removeData('select2');
            } catch (ignored) {}

            $select
                .removeClass(
                    'select2-hidden-accessible select2-offscreen'
                )
                .removeAttr(
                    'data-select2-id aria-hidden tabindex'
                )
                .css('width', '100%');

            $select.find('option').removeAttr('data-select2-id');

            /*
             * option ရှိမှ Select2 ပြန်တည်ဆောက်မည်။
             */
            if ($select.find('option').length === 0) {
                return;
            }

            try {
                $select.select2({
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: 0
                });
            } catch (error) {
                try {
                    $select.select2();
                } catch (ignored) {
                    return;
                }
            }

            if (
                selectedValue !== null &&
                selectedValue !== undefined &&
                selectedValue !== ''
            ) {
                $select.val(String(selectedValue));

                try {
                    $select.trigger('change.select2');
                } catch (ignored) {}

                try {
                    $select.select2(
                        'val',
                        String(selectedValue)
                    );
                } catch (ignored) {}
            }

            $select
                .next('.select2-container')
                .css('width', '100%');
        });
    }


    function getStoredPurchaseItems() {
        var raw = null;

        /*
         * KLSPOS get() helper ရှိလျှင် အရင်သုံးမည်။
         */
        try {
            if (typeof window.get === 'function') {
                raw = window.get('spoitems');
            }
        } catch (ignored) {}

        if (!raw) {
            raw = localStorage.getItem('spoitems');
        }

        if (!raw) {
            return {};
        }

        if (typeof raw === 'object') {
            return raw || {};
        }

        try {
            return JSON.parse(raw) || {};
        } catch (error) {
            return {};
        }
    }


    function saveStoredPurchaseItems(items) {
        var json = JSON.stringify(items || {});

        try {
            if (typeof window.store === 'function') {
                window.store('spoitems', json);
            } else {
                localStorage.setItem('spoitems', json);
            }
        } catch (error) {
            localStorage.setItem('spoitems', json);
        }

        window.spoitems = items || {};
    }


    function purchaseUnitsAreMissing() {
        var missing = false;

        $('#poTable tbody .purchase-item-row').each(function () {
            var $select = $(this).find('select.purchase-unit');

            if (
                $select.length &&
                $select.find('option').length === 0
            ) {
                missing = true;
                return false;
            }
        });

        return missing;
    }


    function repairPurchaseUnitsAfterRefresh() {
        if (refreshUnitRepairRunning) {
            return;
        }

        var items = getStoredPurchaseItems();
        var keys = Object.keys(items || {});

        if (!keys.length) {
            cleanAndInitPurchaseUnitSelect2();
            return;
        }

        /*
         * Unit options အားလုံးရှိနေပြီဆို Select2 ကိုသာ clean/rebuild လုပ်မည်။
         */
        if (!purchaseUnitsAreMissing()) {
            cleanAndInitPurchaseUnitSelect2();
            return;
        }

        /*
         * Main purchase JS က export လုပ်ထားသော function မရှိလျှင်
         * လက်ရှိ DOM ကို မဖျက်ပါနှင့်။
         */
        if (
            typeof window.fetchProductUnitData !== 'function' ||
            typeof window.loadItems !== 'function'
        ) {
            console.warn(
                'KLSPOS: fetchProductUnitData/loadItems is not available.'
            );
            cleanAndInitPurchaseUnitSelect2();
            return;
        }

        refreshUnitRepairRunning = true;

        var pending = 0;

        $.each(items, function (itemKey, item) {
            item = item || {};
            item.row = item.row || {};

            var productId =
                item.row.id ||
                item.id ||
                item.item_id ||
                '';

            if (!productId) {
                return;
            }

            pending++;

            window.fetchProductUnitData(
                item,
                function (hydratedItem) {
                    items[itemKey] =
                        hydratedItem || item;

                    pending--;

                    if (pending <= 0) {
                        saveStoredPurchaseItems(items);

                        /*
                         * Hydrated units ဖြင့် table row များကို ပြန်တည်ဆောက်။
                         */
                        window.loadItems();

                        window.setTimeout(function () {
                            cleanAndInitPurchaseUnitSelect2();
                            refreshUnitRepairRunning = false;
                        }, 80);
                    }
                }
            );
        });

        if (pending === 0) {
            refreshUnitRepairRunning = false;
            cleanAndInitPurchaseUnitSelect2();
        }
    }


    /*
     * Existing purchase JS က loadItems() ပြီးသွားချိန်ကို စောင့်ပြီး စစ်မည်။
     */
    window.setTimeout(
        repairPurchaseUnitsAfterRefresh,
        250
    );

    window.setTimeout(
        repairPurchaseUnitsAfterRefresh,
        700
    );
});
</script>
