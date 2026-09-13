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
    (
        $this->input->post('app_lang', true) ?:
        $Settings->selected_language
    );

$app_query = $is_app_mode
    ? '?app=1&app_lang=' . rawurlencode($app_language)
    : '';

$active_product_language = strtolower(trim((string) $app_language));
$is_myanmar_language = in_array(
    $active_product_language,
    array('myanmar', 'burmese', 'mm', 'my'),
    true
);

$unit_conversion_label = $is_myanmar_language
    ? 'ယူနစ်ဆက်စပ်မှု'
    : 'Unit Conversion';

$manage_unit_conversion_label = $is_myanmar_language
    ? 'ယူနစ်ဆက်စပ်မှု သတ်မှတ်ရန်'
    : 'Manage Unit Conversion';

$base_quantity_label = $is_myanmar_language
    ? 'အရေအတွက်'
    : 'Base Unit Quantity';

$base_unit_label = $is_myanmar_language
    ? 'ယူနစ်'
    : 'Base Unit';

$purchase_price_label = $is_myanmar_language
    ? 'ဝယ်ဈေး'
    : 'Purchase Price';

$selling_price_label = $is_myanmar_language
    ? 'ရောင်းဈေး'
    : 'Selling Price';

$set_selling_price_label = $is_myanmar_language
    ? 'ရောင်းဈေးထည့်ရန်'
    : 'Set Selling Price';

$delete_confirm_message = $is_myanmar_language
    ? 'ဤကုန်ပစ္စည်းကို ဖျက်ရန် သေချာပါသလား?'
    : 'Are you sure you want to delete this product?';

$actions_label = lang('actions');
if (
    empty($actions_label) ||
    $actions_label === 'actions'
) {
    $actions_label = $is_myanmar_language
        ? 'လုပ်ဆောင်မှုများ'
        : 'Actions';
}
?>

<style type="text/css">
    /* KLSPOS ERP Product List Style */
    .content {
        background: #f4f7fb;
        padding-top: 18px;
    }

    .erp-page {
        max-width: 1400px;
        margin: 0 auto;
    }

    .erp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-bottom: 22px;
    }

    .erp-page-header {
        padding: 22px 26px;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .erp-page-title {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .erp-page-title i {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #e6f4f1;
        color: #0f766e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .erp-page-subtitle {
        margin-top: 6px;
        color: #6b7280;
        font-size:16px;
    }

    .erp-header-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
    }

    .erp-body {
        padding: 22px 26px 26px;
    }

    .erp-alert {
        border: 1px solid #fde68a;
        border-left: 5px solid #f59e0b;
        background: #fffbeb;
        color: #92400e;
        border-radius: 12px;
        padding: 13px 15px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size:16px;
    }

    .erp-alert i {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #fef3c7;
        color: #d97706;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .erp-alert a {
        color: #92400e;
        font-weight: 700;
        text-decoration: none;
    }

    .erp-summary-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .erp-summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .erp-summary-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #eefdf8;
        color: #0f766e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 18px;
    }

    .erp-summary-label {
        color: #64748b;
        font-size:16px;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .erp-summary-value {
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
        line-height: 1.3;
    }

    .erp-toolbar {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        padding: 14px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .erp-table-wrap {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #ffffff;
        padding: 14px;
    }

    #prTables {
        margin-bottom: 0 !important;
        width: 100% !important;
    }

    #prTables thead tr th {
        background: #f8fafc;
        color: #1f2937;
        font-weight: 800;
        font-size:16px;
        text-transform: uppercase;
        letter-spacing: .02em;
        border-bottom: 1px solid #e5e7eb !important;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    #prTables tbody tr td {
        vertical-align: middle !important;
        font-size:16px;
        color: #334155;
    }

    #prTables tbody tr:hover {
        background: #f8fafc;
    }

    #prTables td:first-child {
        padding: 6px;
    }

    #prTables .qty-cell,
    #prTables .price-cell {
        text-align: right;
        font-weight: 800;
        white-space: nowrap;
    }

    #prTables .unit-cell,
    #prTables .action-cell {
        text-align: center;
        white-space: nowrap;
    }

    #prTables .column-filter-row th {
        background: #ffffff;
        border-top: 0;
        border-bottom: 1px solid #e5e7eb;
        padding: 5px !important;
    }

    #prTables .column-filter-row input {
        width: 100%;
        height: 32px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 5px 8px;
        font-size: 16px;
        font-weight: 500;
        background: #ffffff;
    }

    #prTables .column-filter-row input:focus {
        outline: none;
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.10);
    }

    .erp-product-img {
        width: 42px;
        height: 42px;
        margin: 0 auto;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #f8fafc;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .erp-product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .erp-no-img {
        color: #94a3b8;
        font-size: 18px;
    }

    .label {
        border-radius: 999px;
        padding: 5px 9px;
        font-size:16px;
        font-weight: 800;
    }

    .btn {
        border-radius: 9px !important;
        font-weight: 800;
    }

    .btn-primary,
    .btn-success {
        background: #0f766e !important;
        border-color: #0f766e !important;
        color: #ffffff !important;
    }

    .btn-primary:hover,
    .btn-success:hover {
        background: #115e59 !important;
        border-color: #115e59 !important;
    }

    .btn-default {
        background: #f3f4f6 !important;
        border-color: #d1d5db !important;
        color: #374151 !important;
    }

    .dropdown-menu {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
        padding: 8px;
    }

    .dropdown-menu > li > a {
        border-radius: 8px;
        padding: 8px 12px;
        font-size:16px;
    }

    .dropdown-menu > li > a:hover {
        background: #f0fdfa;
        color: #0f766e;
    }

    .dataTables_wrapper .dt-buttons {
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dt-buttons .btn,
    .dataTables_wrapper .dt-buttons button {
        border-radius: 8px !important;
        background: #f8fafc !important;
        border: 1px solid #d1d5db !important;
        color: #334155 !important;
        padding: 7px 10px !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        margin-right: 5px;
    }

    .dataTables_filter input,
    .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 5px 8px;
    }

    .dataTables_info {
        color: #64748b;
        font-size:16px;
        font-weight: 600;
        padding-top: 12px !important;
    }

    .pagination > li > a,
    .pagination > li > span {
        border-radius: 8px !important;
        margin: 0 2px;
        color: #0f766e;
        border-color: #e5e7eb;
    }

    .pagination > .active > a,
    .pagination > .active > span {
        background: #0f766e !important;
        border-color: #0f766e !important;
        color: #ffffff !important;
    }

    #picModal .modal-content {
        border-radius: 16px;
        overflow: hidden;
    }

    #picModal .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }

    #product_image {
        margin: 0 auto;
        border-radius: 12px;
    }

    @media (max-width: 991px) {
        .erp-summary-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .erp-page-header {
            display: block;
        }

        .erp-header-actions {
            margin-top: 14px;
            justify-content: flex-start;
        }
    }

    @media (max-width: 767px) {
        .erp-body {
            padding: 14px;
        }

        .erp-summary-row {
            grid-template-columns: 1fr;
        }

        .erp-toolbar {
            display: block;
        }

        .erp-table-wrap {
            overflow-x: auto;
        }

        #prTables {
            min-width: 1020px;
        }

        .erp-header-actions .btn,
        .erp-header-actions .btn-group {
            width: 100%;
            margin-bottom: 8px;
        }

        .erp-header-actions .btn-group .btn {
            width: 100%;
        }
    }
    
    <?php if ($is_app_mode) { ?>
/* FINAL OVERRIDE: KLSPOS Mobile App Product List */
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
    padding: 10px !important;
    margin: 0 !important;
    background: #f4f7fb !important;
}

.erp-page {
    max-width: 100% !important;
    margin: 0 !important;
}

.erp-card {
    border-radius: 14px !important;
    box-shadow: none !important;
    margin-bottom: 0 !important;
}

.erp-page-header {
    display: block !important;
    padding: 14px !important;
}

.erp-page-title {
    font-size: 17px !important;
}

.erp-page-subtitle {
    font-size: 16px !important;
}

.erp-body {
    padding: 12px !important;
}

.erp-summary-row {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
}

.erp-summary-card {
    padding: 10px !important;
}

.erp-summary-card:nth-child(3),
.erp-summary-card:nth-child(4) {
    display: none !important;
}

.erp-summary-icon {
    width: 34px !important;
    height: 34px !important;
    font-size: 16px !important;
}

.erp-summary-label {
    font-size:15px !important;
}

.erp-summary-value {
    font-size: 12px !important;
}

.erp-toolbar {
    display: block !important;
    padding: 10px !important;
    margin-bottom: 10px !important;
}

.erp-toolbar .btn {
    padding: 7px 9px !important;
    font-size: 16px !important;
}

.dataTables_wrapper .dt-buttons {
    display: none !important;
}

/* We use the existing responsive wrapper for horizontal scrolling.
   DataTables' scroll clone created the misplaced footer/blank band. */
.dataTables_scrollFoot,
.dataTables_scrollFootInner {
    display: none !important;
}

/* Never allow DataTables/legacy markup to show a footer row on this page. */
#prTables tfoot,
.dataTables_scrollFoot {
    display: none !important;
}

/* Column filters live outside THEAD so hidden columns cannot shift them. */
.erp-column-filters {
    display: grid;
    grid-template-columns: repeat(4, minmax(150px, 1fr));
    gap: 8px;
    margin: 0 0 10px;
    padding: 9px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

.erp-column-filter {
    position: relative;
}

.erp-column-filter i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

.erp-column-filter input {
    width: 100%;
    height: 36px;
    padding: 6px 9px 6px 30px;
    border: 1px solid #d7dee8;
    border-radius: 8px;
    background: #fff;
    outline: none;
}

.erp-column-filter input:focus {
    border-color: #2f80ed;
    box-shadow: 0 0 0 2px rgba(47, 128, 237, .10);
}

@media (max-width: 1024px) {
    .erp-column-filters {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
        padding: 7px;
    }

    .erp-column-filter input {
        height: 34px;
        font-size: 16px;
    }
}

.erp-table-wrap {
    padding: 8px !important;
    overflow-x: auto !important;
}

/* Important: override old 980px */
#prTables {
    min-width: 1020px !important;
    width: 100% !important;
}

#prTables thead th {
    font-size:15px !important;
    padding: 7px 5px !important;
    white-space: nowrap !important;
}

#prTables tbody td {
    font-size: 16px !important;
    padding: 7px 5px !important;
    white-space: nowrap !important;
}

#prTables .btn {
    padding: 5px 7px !important;
    font-size:15px !important;
}

.dataTables_length,
.dataTables_filter,
.dataTables_info,
.dataTables_paginate {
    font-size: 16px !important;
}

.dataTables_length select {
    height: 28px !important;
    padding: 2px 5px !important;
}
<?php } ?>

/* =========================================================
   ERP Product Listing Header + Add Product Action
   ========================================================= */
.erp-page-header {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto !important;
    align-items: center !important;
    gap: 12px !important;
}

.erp-header-title-wrap,
.erp-page-title,
.erp-page-title span {
    min-width: 0;
}

.erp-page-title span {
    overflow-wrap: anywhere;
}

.erp-header-actions {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 8px !important;
    margin: 0 !important;
    flex-wrap: wrap !important;
}

.erp-add-product-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    min-height: 42px !important;
    padding: 9px 15px !important;
    border: 1px solid #0f766e !important;
    border-radius: 10px !important;
    background: linear-gradient(135deg, #148a82 0%, #0f766e 100%) !important;
    color: #ffffff !important;
    font-size: 16px !important;
    font-weight: 800 !important;
    line-height: 1.25 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-shadow: 0 7px 16px rgba(15, 118, 110, .17) !important;
}

.erp-add-product-btn:hover,
.erp-add-product-btn:focus {
    border-color: #115e59 !important;
    background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important;
    color: #ffffff !important;
    text-decoration: none !important;
    transform: translateY(-1px);
    box-shadow: 0 9px 20px rgba(15, 118, 110, .24) !important;
    outline: 0;
}

@media (max-width: 991px) {
    .erp-page-header {
        grid-template-columns: minmax(0, 1fr) auto !important;
        padding: 15px !important;
    }

    .erp-page-title {
        gap: 9px !important;
        font-size: 17px !important;
        line-height: 1.3 !important;
    }

    .erp-page-title i {
        width: 40px !important;
        height: 40px !important;
        flex: 0 0 40px !important;
        border-radius: 10px !important;
        font-size: 17px !important;
    }

    .erp-page-subtitle {
        display: none !important;
    }

    .erp-header-actions {
        justify-content: flex-end !important;
        flex-wrap: nowrap !important;
    }

    .erp-header-actions .btn,
    .erp-header-actions .btn-group,
    .erp-header-actions .btn-group .btn {
        width: auto !important;
        margin: 0 !important;
    }

    .erp-add-product-btn {
        min-height: 39px !important;
        padding: 8px 11px !important;
        border-radius: 9px !important;
        font-size: 16px !important;
    }
}

@media (max-width: 520px) {
    .erp-page-header {
        gap: 7px !important;
        padding: 12px !important;
    }

    .erp-page-title {
        font-size: 16px !important;
    }

    .erp-page-title i {
        width: 36px !important;
        height: 36px !important;
        flex-basis: 36px !important;
        font-size: 16px !important;
    }

    .erp-store-switcher {
        display: none !important;
    }

    .erp-add-product-btn {
        min-height: 36px !important;
        padding: 7px 9px !important;
        font-size:15px !important;
    }
}


/* =========================================================
   Product Toolbar - Language Ready + Single Row
   ========================================================= */
.erp-toolbar.erp-toolbar-inline {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) auto !important;
    align-items: center !important;
    gap: 10px !important;
    flex-wrap: nowrap !important;
}

.erp-toolbar-inline .erp-toolbar-actions {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
}

.erp-toolbar-inline .erp-refresh-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    min-height: 42px !important;
    padding: 8px 13px !important;
    white-space: nowrap !important;
}

@media (max-width: 767px) {
    .erp-toolbar.erp-toolbar-inline {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto !important;
        align-items: center !important;
        gap: 7px !important;
        padding: 8px !important;
    }

    .erp-toolbar-inline .erp-refresh-btn {
        min-height: 38px !important;
        padding: 7px 10px !important;
        font-size:15px !important;
        margin: 0 !important;
    }
}

@media (max-width: 420px) {
    .erp-toolbar-inline .erp-refresh-btn span {
        display: none !important;
    }

    .erp-toolbar-inline .erp-refresh-btn {
        width: 38px !important;
        min-width: 38px !important;
        padding: 0 !important;
    }
}


/* =========================================================
   Product Actions - Purchase Index style individual buttons
   ========================================================= */
#prTables .action-cell {
    min-width: 248px;
    width: 248px;
    padding: 10px !important;
    white-space: normal !important;
}

.erp-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 7px;
    width: 230px;
    min-width: 230px;
    margin: 0 auto;
}

.erp-actions .erp-action-btn {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 7px !important;
    min-width: 0 !important;
    min-height: 42px !important;
    margin: 0 !important;
    padding: 7px 9px !important;
    border: 1px solid #d8e2ea !important;
    border-radius: 9px !important;
    background: #ffffff !important;
    color: #334155 !important;
    font-size:15px !important;
    font-weight: 800 !important;
    line-height: 1.25 !important;
    text-align: left !important;
    text-decoration: none !important;
    white-space: normal !important;
    box-shadow: none !important;
    overflow: hidden;
}

.erp-actions .erp-action-btn:last-child:nth-child(odd) {
    grid-column: 1 / -1;
    justify-content: center !important;
}

.erp-actions .erp-action-icon {
    width: 27px;
    height: 27px;
    flex: 0 0 27px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #edf2f7;
    color: #475569;
    font-size:16px;
}

.erp-actions .erp-action-label {
    display: block;
    min-width: 0;
    overflow-wrap: anywhere;
}

.erp-actions .erp-action-btn:hover,
.erp-actions .erp-action-btn:focus {
    transform: translateY(-1px);
    text-decoration: none !important;
    outline: 0 !important;
    box-shadow: 0 5px 12px rgba(15, 23, 42, .09) !important;
}

/* View / details */
.erp-actions .erp-action-view {
    border-color: #cfe0ff !important;
    color: #2563eb !important;
    background: #f8fbff !important;
}

.erp-actions .erp-action-view .erp-action-icon {
    background: #e6efff;
    color: #2563eb;
}

/* Edit */
.erp-actions .erp-action-edit {
    border-color: #d7e2ea !important;
    color: #334155 !important;
    background: #ffffff !important;
}

.erp-actions .erp-action-edit .erp-action-icon {
    background: #e8eef3;
    color: #475569;
}

/* Selling price */
.erp-actions .erp-action-price {
    border-color: #bfdbfe !important;
    color: #1d4ed8 !important;
    background: #f4f8ff !important;
}

.erp-actions .erp-action-price .erp-action-icon {
    background: #dbeafe;
    color: #1d4ed8;
}

/* Unit conversion / stock */
.erp-actions .erp-action-unit,
.erp-actions .erp-action-stock {
    border-color: #bce8cc !important;
    color: #15803d !important;
    background: #f3fff7 !important;
}

.erp-actions .erp-action-unit .erp-action-icon,
.erp-actions .erp-action-stock .erp-action-icon {
    background: #dff7e7;
    color: #15803d;
}

/* Barcode / print / copy */
.erp-actions .erp-action-print,
.erp-actions .erp-action-copy {
    border-color: #f0e2bd !important;
    color: #8a6411 !important;
    background: #fffdf7 !important;
}

.erp-actions .erp-action-print .erp-action-icon,
.erp-actions .erp-action-copy .erp-action-icon {
    background: #fbf1d8;
    color: #8a6411;
}

/* Delete */
.erp-actions .erp-action-delete {
    border-color: #ffc3cb !important;
    color: #dc3545 !important;
    background: #fff8f9 !important;
}

.erp-actions .erp-action-delete .erp-action-icon {
    background: #ffe2e6;
    color: #dc3545;
}

.erp-table-wrap,
.erp-table-wrap .table-responsive {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
}

@media (max-width: 767px) {
    #prTables .action-cell {
        min-width: 230px !important;
        width: 230px !important;
        padding: 7px !important;
    }

    .erp-actions {
        width: 216px;
        min-width: 216px;
        gap: 6px;
    }

    .erp-actions .erp-action-btn {
        min-height: 38px !important;
        padding: 6px 7px !important;
        font-size:15px !important;
    }

    .erp-actions .erp-action-icon {
        width: 24px;
        height: 24px;
        flex-basis: 24px;
        font-size:16px;
    }
}
/* =========================================================
   PRODUCT TABLE - TABLET / MOBILE APP COMPACT DESIGN
   ========================================================= */

/* Table Header */
#prTables thead th {
    padding: 10px 8px !important;
    height: 44px !important;
    vertical-align: middle !important;
    white-space: nowrap;
}

/* Product Rows */
#prTables tbody td {
    padding: 8px 8px !important;
    height: 58px !important;
    min-height: 58px !important;
    vertical-align: middle !important;
    line-height: 1.3 !important;
}

/* Remove unnecessary large row height */
#prTables tbody tr {
    height: 58px !important;
}

/* =========================
   TABLET
   ========================= */
@media (min-width: 768px) and (max-width: 1199px) {

    #prTables tbody td {
        padding: 6px 6px !important;
        height: 56px !important;
    }

    #prTables tbody tr {
        height: 56px !important;
    }

    #prTables thead th {
        padding: 8px 6px !important;
        height: 42px !important;
    }

    /* Action buttons compact */
    #prTables .btn {
        min-height: 34px !important;
        height: 34px !important;
        padding: 5px 9px !important;
        margin: 2px !important;
        line-height: 22px !important;
        border-radius: 7px !important;
    }

    /* Action button icons */
    #prTables .btn i {
        margin-right: 3px;
    }

    /* Action area */
    #prTables td:last-child {
        padding: 5px !important;
        white-space: nowrap;
    }
}


/* =========================
   MOBILE
   ========================= */
@media (max-width: 767px) {

    #prTables tbody td {
        padding: 7px 6px !important;
        height: auto !important;
        min-height: 52px !important;
    }

    #prTables tbody tr {
        height: auto !important;
    }

    #prTables thead th {
        padding: 8px 6px !important;
    }

    #prTables .btn {
        min-height: 32px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        margin: 1px !important;
        border-radius: 7px !important;
    }
}

/* =========================================================
   KLSPOS APP / TABLET COMPACT ROWS
   767px DevTools viewport must be included here too.
   The action links use .erp-action-btn (not Bootstrap .btn), so they
   need their own compact rules.
   ========================================================= */
@media (max-width: 1024px) {
    #prTables tbody tr {
        height: 52px !important;
    }

    #prTables tbody td {
        height: 52px !important;
        min-height: 0 !important;
        padding: 5px 6px !important;
        vertical-align: middle !important;
        line-height: 1.2 !important;
    }

    #prTables .action-cell {
        width: 190px !important;
        min-width: 190px !important;
        padding: 5px 6px !important;
        white-space: nowrap !important;
    }

    #prTables .erp-actions {
        display: flex !important;
        flex-flow: row nowrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        width: auto !important;
        min-width: 0 !important;
        margin: 0 !important;
    }

    #prTables .erp-actions .erp-action-btn,
    #prTables .erp-actions .erp-action-btn:last-child:nth-child(odd) {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 52px !important;
        width: 52px !important;
        min-width: 52px !important;
        max-width: 52px !important;
        height: 52px !important;
        min-height: 52px !important;
        padding: 0 !important;
        margin: 0 !important;
        gap: 0 !important;
        border-radius: 8px !important;
        grid-column: auto !important;
        overflow: visible !important;
    }

    #prTables .erp-actions .erp-action-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 30px !important;
        height: 30px !important;
        flex: 0 0 30px !important;
        border-radius: 7px !important;
        font-size: 16px !important;
    }

    #prTables .erp-actions .erp-action-label {
        display: none !important;
    }

    #prTables .column-filter-row th {
        padding: 4px !important;
        height: 38px !important;
    }

    #prTables .column-filter-row input {
        height: 29px !important;
        padding: 3px 5px !important;
        font-size: 16px !important;
        border-radius: 6px !important;
    }
}
/* Action buttons = icon only */
#prTables .erp-actions .erp-action-label {
    display: none !important;
}

/* Action Buttons - Finger Friendly */
#prTables .erp-actions {
    display: flex !important;
    flex-wrap: nowrap !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 7px !important;
}

#prTables .erp-actions .erp-action-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;

    width: 46px !important;
    min-width: 46px !important;
    height: 46px !important;
    min-height: 46px !important;

    padding: 0 !important;
    margin: 0 !important;

    border-radius: 7px !important;
    font-size: 18px !important;
}

#prTables .erp-actions .erp-action-btn i {
    font-size: 18px !important;
}

/* Text မပြဘဲ Icon ပဲ */
#prTables .erp-actions .erp-action-label {
    display: none !important;
}

/* Action column ကို button တွေအတွက် နေရာပေး */
#prTables .action-cell {
    min-width: 170px !important;
    width: 170px !important;
    white-space: nowrap !important;
}

/* Selling price action: always show a clear text button instead of an icon. */
#prTables .erp-selling-price-button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 138px !important;
    min-height: 42px !important;
    padding: 9px 14px !important;
    border: 1px solid #93c5fd !important;
    border-radius: 8px !important;
    background: #eff6ff !important;
    color: #1d4ed8 !important;
    font-size: 15px !important;
    font-weight: 800 !important;
    line-height: 1.25 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-shadow: none !important;
}

#prTables .erp-selling-price-button:hover,
#prTables .erp-selling-price-button:focus {
    border-color: #2563eb !important;
    background: #dbeafe !important;
    color: #1e40af !important;
    text-decoration: none !important;
}

.product-search-help {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    margin-bottom: 10px;
    background: #eef6ff;
    border: 1px solid #cfe2ff;
    border-left: 4px solid #337ab7;
    border-radius: 5px;
    color: #444;
}

.product-search-help > i {
    font-size: 18px;
    color: #337ab7;
}

.product-search-help strong {
    display: block;
    color: #286090;
    margin-bottom: 2px;
}

.product-search-help span {
    font-size: 16px;
    color: #666;
}
</style>

<section class="content">
    <div class="erp-page">
        <div class="erp-card">

            

            <div class="erp-body">

                

                
<div class="product-search-help">
    <i class="fa fa-search"></i>
    <div>
        <strong>ကုန်ပစ္စည်းရှာဖွေရန်</strong>
        <span>
            ရှာဖွေလိုသော ကုန်ပစ္စည်း၏ ကုဒ်၊ အမည်နှင့် ယူနစ်တို့ကို ရိုက်ထည့်ပြီး ရှာဖွေနိုင်ပါသည်။
        </span>
    </div>
</div>
                

                <div class="erp-column-filters" id="product-column-filters">
                    <div class="erp-column-filter">
                        <i class="fa fa-barcode"></i>
                        <input type="text" class="erp-and-filter-input" data-column="2" placeholder="<?= html_escape(lang('code')); ?>">
                    </div>
                    <div class="erp-column-filter">
                        <i class="fa fa-cube"></i>
                        <input type="text" class="erp-and-filter-input" data-column="3" placeholder="<?= html_escape(lang('name')); ?>">
                    </div>
                    <div class="erp-column-filter">
                        <i class="fa fa-balance-scale"></i>
                        <input type="text" class="erp-and-filter-input" data-column="5" placeholder="<?= html_escape($base_unit_label); ?>">
                    </div>
                </div>

                <div class="erp-table-wrap">
                    <div class="table-responsive">
                        <table id="prTables" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><?= lang("id"); ?></th>
                                    <th><?= lang("image"); ?></th>
                                    <th><?= lang("code"); ?></th>
                                    <th><?= lang("name"); ?></th>
                                    <th><?= lang("category"); ?></th>
                                    <th><?= html_escape($base_unit_label); ?></th>
                                    <th><?= html_escape($purchase_price_label); ?></th>
                                    <th><?= html_escape($selling_price_label); ?></th>
                                    <th><?= html_escape($actions_label); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Image Modal -->
                <div class="modal fade" id="picModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">
                                    <i class="fa fa-times"></i>
                                </button>
                                <button type="button" class="close mr10" onclick="window.print();">
                                    <i class="fa fa-print"></i>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">Product Image</h4>
                            </div>
                            <div class="modal-body text-center">
                                <img class="img-responsive" id="product_image" src="" alt="" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
$(document).ready(function() {

    function image(n) {
        if (n !== null && n !== '') {
            return '<div class="erp-product-img">' +
                   '<a href="<?= base_url(); ?>uploads/' + n + '" class="open-image">' +
                   '<img src="<?= base_url(); ?>uploads/thumbs/' + n + '" class="img-responsive"></a></div>';
        }

        return '<div class="erp-product-img"><i class="fa fa-picture-o erp-no-img"></i></div>';
    }

    function numericValue(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        if (typeof value === 'number') {
            return value;
        }

        var cleaned = String(value)
            .replace(/<[^>]*>/g, '')
            .replace(/,/g, '')
            .replace(/[^0-9.\-]/g, '');

        return parseFloat(cleaned) || 0;
    }

    function quantityFormat(value, type) {
        var num = numericValue(value);

        if (type !== 'display') {
            return num;
        }

        var output = num % 1 === 0
            ? num.toFixed(0)
            : num.toFixed(2);

        return '<span class="text-primary" style="font-weight:800;">' +
            output +
        '</span>';
    }

    function currencyFormat(value, type) {
        var num = numericValue(value);

        if (type !== 'display') {
            return num;
        }

        return '<span style="font-weight:800;">' +
            num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) +
        '</span>';
    }

    function productPurchasePrice(row) {
        if (!row) {
            return 0;
        }

        var fields = [
            'cost',
            'purchase_price',
            'purchase_cost',
            'base_cost',
            'unit_cost',
            'pcost',
            'product_cost'
        ];

        for (var i = 0; i < fields.length; i++) {
            if (
                row[fields[i]] !== undefined &&
                row[fields[i]] !== null &&
                row[fields[i]] !== ''
            ) {
                return row[fields[i]];
            }
        }

        return 0;
    }

    function productSellingPrice(row) {
        if (!row) {
            return 0;
        }

        var fields = [
            'selling_price',
            'sale_price',
            'price',
            'unit_price',
            'product_price'
        ];

        for (var i = 0; i < fields.length; i++) {
            if (
                row[fields[i]] !== undefined &&
                row[fields[i]] !== null &&
                row[fields[i]] !== ''
            ) {
                return row[fields[i]];
            }
        }

        return 0;
    }

    function actionTypeAndIcon($link) {
        var href = String($link.attr('href') || '').toLowerCase();
        var title = String($link.attr('title') || '').toLowerCase();
        var text = String($link.text() || '').toLowerCase();
        var iconClass = String($link.find('i').first().attr('class') || '').toLowerCase();
        var combined = href + ' ' + title + ' ' + text + ' ' + iconClass;

        if (
            combined.indexOf('selling_prices') !== -1 ||
            combined.indexOf('selling-price') !== -1 ||
            combined.indexOf('set selling price') !== -1 ||
            combined.indexOf('ရောင်းဈေးထည့်') !== -1 ||
            combined.indexOf('ရောင်းစျေးထည့်') !== -1
        ) {
            return { type: 'price', icon: 'fa fa-tag' };
        }

        if (
            combined.indexOf('delete') !== -1 ||
            combined.indexOf('remove') !== -1 ||
            combined.indexOf('trash') !== -1 ||
            combined.indexOf('ဖျက်') !== -1
        ) {
            return { type: 'delete', icon: 'fa fa-trash' };
        }

        if (
            combined.indexOf('edit') !== -1 ||
            combined.indexOf('pencil') !== -1 ||
            combined.indexOf('ပြင်') !== -1
        ) {
            return { type: 'edit', icon: 'fa fa-pencil' };
        }

        if (
            combined.indexOf('barcode') !== -1 ||
            combined.indexOf('print') !== -1 ||
            combined.indexOf('ဘားကုဒ်') !== -1 ||
            combined.indexOf('ပုံနှိပ်') !== -1
        ) {
            return { type: 'print', icon: 'fa fa-barcode' };
        }

        if (
            combined.indexOf('copy') !== -1 ||
            combined.indexOf('duplicate') !== -1 ||
            combined.indexOf('ကူး') !== -1
        ) {
            return { type: 'copy', icon: 'fa fa-copy' };
        }

        if (
            combined.indexOf('unit') !== -1 ||
            combined.indexOf('conversion') !== -1 ||
            combined.indexOf('balance-scale') !== -1 ||
            combined.indexOf('ယူနစ်') !== -1
        ) {
            return { type: 'unit', icon: 'fa fa-balance-scale' };
        }

        if (
            combined.indexOf('stock') !== -1 ||
            combined.indexOf('quantity') !== -1 ||
            combined.indexOf('inventory') !== -1 ||
            combined.indexOf('လက်ကျန်') !== -1
        ) {
            return { type: 'stock', icon: 'fa fa-cubes' };
        }

        if (
            combined.indexOf('view') !== -1 ||
            combined.indexOf('detail') !== -1 ||
            combined.indexOf('eye') !== -1 ||
            combined.indexOf('ကြည့်') !== -1
        ) {
            return { type: 'view', icon: 'fa fa-eye' };
        }

        return {
            type: 'view',
            icon: iconClass ? iconClass : 'fa fa-link'
        };
    }

    function isProductPictureAction($link) {
        var href = String($link.attr('href') || '').toLowerCase();
        var title = String($link.attr('title') || '').toLowerCase();
        var textValue = String($link.text() || '').toLowerCase();
        var classes = String($link.attr('class') || '').toLowerCase();
        var iconClass = String($link.find('i').first().attr('class') || '').toLowerCase();
        var combined = href + ' ' + title + ' ' + textValue + ' ' + classes + ' ' + iconClass;

        var isEditOrDelete =
            combined.indexOf('edit') !== -1 ||
            combined.indexOf('delete') !== -1 ||
            combined.indexOf('ပြင်') !== -1 ||
            combined.indexOf('ဖျက်') !== -1;

        if (isEditOrDelete) {
            return false;
        }

        return (
            classes.indexOf('open-image') !== -1 ||
            href.indexOf('/uploads/') !== -1 ||
            combined.indexOf('view_image') !== -1 ||
            combined.indexOf('product_image') !== -1 ||
            combined.indexOf('picture') !== -1 ||
            combined.indexOf('photo') !== -1 ||
            combined.indexOf('ပုံကြည့်') !== -1 ||
            combined.indexOf('ပုံကြည့်') !== -1 ||
            (
                combined.indexOf('image') !== -1 &&
                combined.indexOf('view') !== -1
            )
        );
    }

    function styleActionLink($original) {
        var $link = $original.clone();
        var info = actionTypeAndIcon($link);
        var $labelSource = $link.clone();

        $labelSource.find('i, .fa, .glyphicon, .caret').remove();

        var label = $.trim($labelSource.text());
        if (!label) {
            label = $.trim($link.attr('title') || '<?= html_escape($actions_label); ?>');
        }

        var originalClasses = $.trim($link.attr('class') || '');

        $link
            .attr(
                'class',
                originalClasses +
                ' erp-action-btn erp-action-' +
                info.type
            )
            .removeAttr('style')
            .attr('aria-label', label)
            .empty()
            .append(
                $('<span>', { 'class': 'erp-action-icon' }).append(
                    $('<i>', { 'class': info.icon })
                )
            )
            .append(
                $('<span>', {
                    'class': 'erp-action-label',
                    text: label
                })
            );

        return $('<div>').append($link).html();
    }

    function buildProductActions(data, type, row) {
        if (type !== 'display') {
            return row && row.pid ? row.pid : '';
        }

        var productId = row && row.pid ? row.pid : '';
        if (!productId) {
            return '';
        }

        var sellingPriceUrl =
            '<?= site_url('products/selling_prices/'); ?>' +
            encodeURIComponent(productId) +
            '<?= $is_app_mode
                ? '?app=1&app_lang=' . rawurlencode($app_language)
                : ''; ?>';

        var $sellingPriceLink = $('<a>', {
            href: sellingPriceUrl,
            'class': 'erp-selling-price-button',
            title: '<?= html_escape($set_selling_price_label); ?>',
            'aria-label': '<?= html_escape($set_selling_price_label); ?>',
            text: '<?= html_escape($set_selling_price_label); ?>'
        });

        return '<div class="erp-actions">' +
            $('<div>').append($sellingPriceLink).html() +
            '</div>';
    }

    var isAppMode = <?= $is_app_mode ? 'true' : 'false'; ?>;

    var table = $('#prTables').DataTable({
    'ajax': {
        url: '<?= site_url('products/get_products/'.$store->id); ?>',
        type: 'POST',
        data: function(d) {
            d.<?=$this->security->get_csrf_token_name();?> =
                "<?=$this->security->get_csrf_hash()?>";
        }
    },

    "columns": [
        {
            "data": "pid",
            "visible": false,
            "type": "num"
        },
        {
            "data": "image",
            "searchable": false,
            "orderable": false,
            "render": image,
            "className": "text-center"
        },
        {
            "data": "code"
        },
        {
            "data": "pname"
        },
        {
            "data": "cname"
        },
        {
            "data": "base_unit_name",
            "defaultContent": "-",
            "className": "unit-cell"
        },
        {
            "data": null,
            "render": function (data, type, row) {
                return currencyFormat(productPurchasePrice(row), type);
            },
            "searchable": false,
            "className": "price-cell purchase-price-cell"
        },
        {
            "data": null,
            "render": function (data, type, row) {
                return currencyFormat(productSellingPrice(row), type);
            },
            "searchable": false,
            "className": "price-cell selling-price-cell"
        },
        {
            "data": "Actions",
            "render": buildProductActions,
            "searchable": false,
            "orderable": false,
            "className": "action-cell"
        }
    ],

    "buttons": [
        {
            extend: 'copyHtml5',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'excelHtml5',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'csvHtml5',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'pdfHtml5',
            exportOptions: { columns: ':visible' },
            orientation: 'landscape',
            pageSize: 'A4'
        },
        {
            extend: 'colvis',
            text: 'Columns'
        }
    ],

    /* နောက်ဆုံးထည့်ထားသည့် Product ကို အပေါ်ဆုံးပြရန် */
    "order": [[0, 'desc']],

    /* Browser မှ သိမ်းထားသော အရင် sorting ကို မသုံးရန် */
    "stateSave": false,

    "pageLength": 10,
    "orderCellsTop": true,
    /* Native .table-responsive handles horizontal scrolling more cleanly. */
    "scrollX": false,
    "autoWidth": false,

    "initComplete": function() {
        var api = this.api();
        var filterTimer = null;

        function applyAndFilters() {
            var changed = false;

            $('#product-column-filters .erp-and-filter-input[data-column]').each(function() {
                var columnIndex = parseInt($(this).attr('data-column'), 10);
                var value = $.trim($(this).val());
                var column = api.column(columnIndex);

                if (column.search() !== value) {
                    column.search(value);
                    changed = true;
                }
            });

            if (changed) {
                api.draw();
            }
        }

        $('#product-column-filters .erp-and-filter-input[data-column]').each(function() {
            var $input = $(this);

            $input
                .on('click', function(e) {
                    e.stopPropagation();
                })
                .on('input search keyup change clear', function() {
                    clearTimeout(filterTimer);
                    filterTimer = setTimeout(applyAndFilters, 180);
                });
        });
    }
});
    
    if (isAppMode) {
        /*
         * Mobile: hide only image and category.
         * Base unit, purchase price, selling price and actions remain visible.
         */
        table.columns([1, 4]).visible(false, false);

        setTimeout(function() {
            table.columns.adjust().draw(false);
        }, 300);
    }

    $('#prTables').on('click', '.open-image', function(e) {
        e.preventDefault();

        var a_href = $(this).attr('href');
        var rowData = table.row($(this).closest('tr')).data();
        var code = rowData && rowData.code ? rowData.code : '';

        $('#myModalLabel').text(code);
        $('#product_image').attr('src', a_href);
        $('#picModal').modal();
    });

    /* Delete ကို တန်းမဖျက်ဘဲ အတည်ပြုချက် အရင်မေးရန် */
    $('#prTables').on('click', '.erp-action-delete', function(e) {
        var confirmed = window.confirm(
            <?= json_encode($delete_confirm_message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        );

        if (!confirmed) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }

        return true;
    });

});
</script>