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

$expense_list_url = site_url('purchases/expenses') . $app_query;
?>

<style>
/* =========================================================
   KLSPOS Add Expense
   Same visual system as Add Product
   ========================================================= */
.content {
    padding-top: 18px;
    background: #f4f7fb;
}

.erp-page {
    max-width: 1280px;
    margin: 0 auto;
}

.erp-card {
    overflow: hidden;
    margin-bottom: 22px;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
}

.erp-page-header {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 14px;
    padding: 18px 22px;
    border-bottom: 1px solid #e6edf3;
    background: linear-gradient(135deg, #ffffff 0%, #f7fbfc 100%);
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

.erp-page-title span {
    min-width: 0;
    overflow-wrap: anywhere;
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
    box-shadow: inset 0 0 0 1px rgba(15, 118, 110, .07);
}

.erp-header-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    min-width: 0;
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
    color: #ffffff !important;
    font-size: 13px;
    font-weight: 800;
    line-height: 1.25;
    text-decoration: none !important;
    white-space: nowrap;
    box-shadow: 0 7px 16px rgba(15, 118, 110, .17);
    transition: transform .16s ease, box-shadow .16s ease;
}

.erp-listing-btn:hover,
.erp-listing-btn:focus {
    border-color: #115e59 !important;
    background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important;
    color: #ffffff !important;
    text-decoration: none !important;
    transform: translateY(-1px);
    box-shadow: 0 9px 20px rgba(15, 118, 110, .24);
    outline: 0;
}

.erp-body {
    padding: 24px 26px 0;
}

.erp-section {
    position: relative;
    margin-bottom: 22px;
    padding: 20px 20px 4px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #ffffff;
}

.erp-section.erp-date-section {
    z-index: 50;
    overflow: visible;
}

.erp-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eef2f7;
    color: #0f172a;
    font-size: 16px;
    font-weight: 800;
}

.erp-section-title i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 10px;
    background: #eefdf8;
    color: #0f766e;
}

.erp-section-note {
    margin-left: auto;
    color: #64748b;
    font-size: 12px;
    font-weight: 500;
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
    line-height: 1.5;
}

.erp-form .form-group {
    margin-bottom: 20px;
}

.erp-form label,
.erp-form .control-label {
    display: block;
    margin-bottom: 8px;
    color: #1f2937;
    font-size: 13px;
    font-weight: 800;
    line-height: 1.45;
}

.erp-form .form-control,
.erp-form .select2-container .select2-choice,
.erp-form .select2-container--default .select2-selection--single {
    width: 100%;
    height: 46px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 9px !important;
    background: #ffffff !important;
    color: #111827;
    box-shadow: none !important;
    font-size: 14px;
}

.erp-form .form-control {
    padding: 10px 12px;
}

.erp-form textarea.form-control {
    min-height: 145px;
    resize: vertical;
}

.erp-form .form-control:focus,
.erp-form .select2-container-active .select2-choice,
.erp-form .select2-container--focus .select2-selection--single,
.erp-form .select2-container--open .select2-selection--single {
    border-color: #0f766e !important;
    box-shadow: 0 0 0 3px rgba(15, 118, 110, .12) !important;
    outline: 0;
}

/* Select2 */
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
    min-height: 46px !important;
    padding: 0 38px 0 12px !important;
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
    min-height: 46px !important;
}

.erp-form .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    padding-left: 12px !important;
    padding-right: 38px !important;
    line-height: 44px !important;
}

.erp-form .select2-container--default
.select2-selection--single
.select2-selection__arrow {
    width: 36px !important;
    height: 44px !important;
}

.select2-drop,
.select2-dropdown,
.select2-container--open,
.bootstrap-datetimepicker-widget.dropdown-menu {
    z-index: 999999 !important;
}

/* Quick-add pattern matches Add Product */
.quick-add-link {
    float: right;
    color: #0f766e;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
}

.quick-add-link:hover,
.quick-add-link:focus {
    color: #115e59;
    text-decoration: none;
}

.erp-file-box {
    padding: 14px;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
}

.erp-file-box input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 9px;
    background: #ffffff;
}

.erp-total-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-height: 46px;
    padding: 10px 12px;
    border: 1px solid #cfe3e6;
    border-radius: 9px;
    background: #eef8f9;
}

.erp-total-label {
    color: #52707a;
    font-size: 12px;
    font-weight: 800;
}

.erp-total-value {
    color: #115e59;
    font-size: 19px;
    font-weight: 900;
    white-space: nowrap;
}

/* Sticky action bar matches Add Product */
.erp-actions {
    position: sticky;
    bottom: 0;
    z-index: 20;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin: 22px -20px 0;
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
    background: #ffffff;
    box-shadow: 0 -8px 18px rgba(15, 23, 42, .04);
}

.erp-form .btn {
    padding: 10px 18px !important;
    border-radius: 9px !important;
    font-weight: 800;
}

.erp-form .btn-primary,
.erp-form .btn-success {
    border-color: #0f766e !important;
    background: #0f766e !important;
    color: #ffffff !important;
}

.erp-form .btn-primary:hover,
.erp-form .btn-success:hover,
.erp-form .btn-primary:focus,
.erp-form .btn-success:focus {
    border-color: #115e59 !important;
    background: #115e59 !important;
    color: #ffffff !important;
}

.erp-form .btn-default {
    border-color: #d1d5db !important;
    background: #f3f4f6 !important;
    color: #374151 !important;
}

.erp-form .btn-danger {
    border-color: #dc2626 !important;
    background: #dc2626 !important;
    color: #ffffff !important;
}

/* Modal */
.expense-category-modal .modal-dialog {
    max-width: 500px;
}

.expense-category-modal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 14px;
    box-shadow: 0 16px 45px rgba(15, 23, 42, .25);
}

.expense-category-modal .modal-header,
.expense-category-modal .modal-footer {
    padding: 14px 16px;
    border-color: #e5ebf1;
    background: #f8fafc;
}

.expense-category-modal .modal-title {
    color: #263548;
    font-size: 16px;
    font-weight: 800;
}

.expense-category-modal .modal-body {
    padding: 16px;
}

.expense-category-modal .form-group {
    margin-bottom: 14px;
}

.expense-category-modal label {
    display: block;
    margin-bottom: 6px;
    color: #2d3d50;
    font-size: 14px;
    font-weight: 800;
}

.expense-category-modal .form-control {
    min-height: 42px;
    border: 1px solid #ccd6e1;
    border-radius: 9px;
    box-shadow: none;
}

.expense-category-modal .form-control:focus {
    border-color: #0f766e;
    box-shadow: 0 0 0 3px rgba(15, 118, 110, .10);
}

.expense-category-modal .quick-category-message {
    display: none;
    margin-bottom: 12px;
    padding: 9px 11px;
    border-radius: 7px;
    font-size: 12px;
    line-height: 1.5;
}

.expense-category-modal .quick-category-message.error {
    display: block;
    border: 1px solid #f2c6c1;
    background: #fff2f0;
    color: #b42318;
}

.expense-category-modal .quick-category-message.success {
    display: block;
    border: 1px solid #bde2cb;
    background: #edf9f1;
    color: #18733c;
}

@media (max-width: 767px) {
    .erp-page-header {
        grid-template-columns: minmax(0, 1fr) auto;
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
        border-radius: 9px !important;
        font-size: 11px;
    }

    .erp-body {
        padding: 18px 16px 0;
    }

    .erp-section {
        margin-bottom: 14px;
        padding: 15px 14px 4px;
        border-radius: 13px;
    }

    .erp-section-title {
        margin-bottom: 14px;
        font-size: 15px;
    }

    .erp-section-note {
        display: none;
    }

    .quick-add-link {
        float: none;
        display: inline-block;
        margin-left: 8px;
    }

    .erp-actions {
        display: block;
        margin-right: -14px;
        margin-left: -14px;
        padding: 12px 14px;
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
    min-height: 100vh !important;
    margin-left: 0 !important;
    padding-top: 0 !important;
    background: #f4f7fb !important;
}

.content {
    margin: 0 !important;
    padding: 0 10px 10px !important;
    background: #f4f7fb !important;
}

.erp-page {
    max-width: 100% !important;
    margin: 0 !important;
}

.erp-card {
    margin-bottom: 0 !important;
    border-radius: 14px !important;
    box-shadow: none !important;
}

.erp-page-header {
    grid-template-columns: minmax(0, 1fr) auto !important;
    gap: 9px !important;
    padding: 13px 14px !important;
}

.erp-page-title {
    gap: 9px !important;
    min-width: 0 !important;
    font-size: 16px !important;
}

.erp-page-title i {
    width: 38px !important;
    height: 38px !important;
    flex: 0 0 38px !important;
    border-radius: 10px !important;
    font-size: 16px !important;
}

.erp-listing-btn {
    min-height: 38px !important;
    padding: 8px 11px !important;
    border-radius: 9px !important;
    font-size: 11px !important;
}

.erp-body {
    padding: 14px !important;
}

.erp-section {
    margin-bottom: 14px !important;
    padding: 15px 14px 4px !important;
    border-radius: 13px !important;
}

.erp-actions {
    margin: 15px -14px 0 !important;
    padding: 12px 14px !important;
}

.erp-actions .btn {
    width: 100% !important;
    margin-bottom: 8px !important;
}

.erp-actions .btn:last-child {
    margin-bottom: 0 !important;
}

.modal-dialog {
    width: auto !important;
    margin: 15px !important;
}

body,
html {
    background: #f4f7fb !important;
}
</style>
<?php } ?>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content expense-add-erp kls-mobile-ui">
    <div class="erp-page">
        <div class="erp-card erp-form">

            <div class="erp-page-header">
                <h4 class="erp-page-title">
                    <i class="fa fa-money"></i>
                    <span><?= html_escape($page_title); ?></span>
                </h4>

                <div class="erp-header-actions">
                    <a
                        href="<?= html_escape($expense_list_url); ?>"
                        class="erp-listing-btn"
                    >
                        <i class="fa fa-list"></i>
                        <span><?= html_escape(lang('expenses')); ?></span>
                    </a>
                </div>
            </div>

            <div class="erp-body">
                <?= form_open_multipart(
                    'purchases/add_expense',
                    'id="expense_add_form" class="validation"'
                ); ?>

                <?php if ($is_app_mode) { ?>
                    <input type="hidden" name="app" value="1">
                    <input
                        type="hidden"
                        name="app_lang"
                        value="<?= html_escape($app_language); ?>"
                    >
                <?php } ?>

                <!-- Expense Information -->
                <div class="erp-section erp-date-section">
                    <div class="erp-section-title">
                        <i class="fa fa-file-text-o"></i>
                        <span><?= html_escape(lang('expense_information')); ?></span>
                        <span class="erp-section-note">
                            <?= html_escape(lang('required_fields_marked')); ?>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-group-lg">
                                <label for="date">
                                    <?= html_escape(lang('date')); ?>
                                    <span class="erp-required">*</span>
                                </label>

                                <?= form_input(
                                    'date',
                                    set_value('date'),
                                    'class="form-control datetimepicker"
                                     id="date"
                                     required="required"
                                     autocomplete="off"'
                                ); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-group-lg">
                                <label for="category">
                                    <?= html_escape(lang('category')); ?>
                                    <span class="erp-required">*</span>

                                    <span
                                        class="quick-add-link"
                                        data-toggle="modal"
                                        data-target="#expenseCategoryModal"
                                        title="<?= html_escape(
                                            lang('add_new_expense_category')
                                        ); ?>"
                                    >
                                        <i class="fa fa-plus"></i>
                                        <?= html_escape(lang('add_new')); ?>
                                    </span>
                                </label>

                                <?php
                                $category_options = [
                                    '' => lang('select') . ' ' . lang('category')
                                ];

                                foreach ($categories as $category) {
                                    $category_options[$category->id] =
                                        $category->name;
                                }
                                ?>

                                <?= form_dropdown(
                                    'category',
                                    $category_options,
                                    set_value('category'),
                                    'class="form-control select2 erp-select2"
                                     id="category"
                                     required="required"
                                     style="width:100%;"'
                                ); ?>

                                <span class="erp-help">
                                    <?= html_escape(
                                        lang('quick_add_expense_category_help')
                                    ); ?>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group form-group-lg erp-file-box">
                                <label for="attachment">
                                    <?= html_escape(lang('attachment')); ?>
                                </label>

                                <input
                                    id="attachment"
                                    type="file"
                                    name="userfile"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expense Amount -->
                <div class="erp-section">
                    <div class="erp-section-title">
                        <i class="fa fa-calculator"></i>
                        <span>
                            <?= html_escape(
                                lang('expense_amount_information')
                            ); ?>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-group-lg">
                                <label for="reference">
                                    <?= html_escape(lang('expense_title')); ?>
                                    <span class="erp-required">*</span>
                                </label>

                                <?= form_input(
                                    'reference',
                                    set_value('reference'),
                                    'class="form-control"
                                     id="reference"
                                     required="required"
                                     autocomplete="off"
                                     placeholder="' .
                                     html_escape(lang('enter_expense_title')) .
                                     '"'
                                ); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group form-group-lg">
                                <label for="quantity">
                                    <?= html_escape(lang('quantity')); ?>
                                    <span class="erp-required">*</span>
                                </label>

                                <input
                                    name="quantity"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    inputmode="decimal"
                                    id="quantity"
                                    value="<?= html_escape(
                                        set_value('quantity', '1')
                                    ); ?>"
                                    class="form-control"
                                    required="required"
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group form-group-lg">
                                <label for="amount">
                                    <?= html_escape(lang('amount')); ?>
                                    <span class="erp-required">*</span>
                                </label>

                                <input
                                    name="amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    inputmode="decimal"
                                    id="amount"
                                    value="<?= html_escape(
                                        set_value('amount', '0.00')
                                    ); ?>"
                                    class="form-control"
                                    required="required"
                                >
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group form-group-lg">
                                <label for="total">
                                    <?= html_escape(lang('total')); ?>
                                    <span class="erp-required">*</span>
                                </label>

                                <div class="erp-total-box">
                                    <span class="erp-total-label">
                                        <?= html_escape(lang('total')); ?>
                                    </span>

                                    <span class="erp-total-value" id="total_display">
                                        <?= html_escape(
                                            set_value('total', '0.00')
                                        ); ?>
                                    </span>
                                </div>

                                <input
                                    name="total"
                                    type="hidden"
                                    id="total"
                                    value="<?= html_escape(
                                        set_value('total', '0.00')
                                    ); ?>"
                                >

                                <span class="erp-help">
                                    <?= html_escape(
                                        lang('expense_total_calculation_help')
                                    ); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="base_amount_mmk"
                        id="base_amount_mmk"
                        value="<?= html_escape(
                            set_value('base_amount_mmk', '0.00')
                        ); ?>"
                    >
                </div>

                <!-- Additional Information -->
                <div class="erp-section">
                    <div class="erp-section-title">
                        <i class="fa fa-sticky-note-o"></i>
                        <span>
                            <?= html_escape(lang('additional_information')); ?>
                        </span>
                    </div>

                    <div class="form-group form-group-lg">
                        <label for="note">
                            <?= html_escape(lang('note')); ?>
                        </label>

                        <?= form_textarea(
                            'note',
                            set_value('note'),
                            'class="form-control"
                             id="note"
                             rows="6"'
                        ); ?>
                    </div>

                    <div class="erp-actions">
                        <a
                            href="<?= html_escape($expense_list_url); ?>"
                            class="btn btn-default"
                        >
                            <i class="fa fa-times"></i>
                            <?= html_escape(lang('cancel')); ?>
                        </a>

                        <button
                            type="button"
                            id="reset_expense_form"
                            class="btn btn-danger"
                        >
                            <i class="fa fa-refresh"></i>
                            <?= html_escape(lang('reset')); ?>
                        </button>

                        <button
                            type="submit"
                            name="create"
                            class="btn btn-primary"
                        >
                            <i class="fa fa-save"></i>
                            <?= html_escape(lang('save_expense')); ?>
                        </button>
                    </div>
                </div>

                <?= form_close(); ?>
            </div>
        </div>
    </div>
</section>

<!-- Quick Add Expense Category Modal -->
<div
    class="modal fade expense-category-modal"
    id="expenseCategoryModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="expenseCategoryModalLabel"
>
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="<?= html_escape(lang('close')); ?>"
                >
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title" id="expenseCategoryModalLabel">
                    <i class="fa fa-plus-circle"></i>
                    <?= lang('add_new_expense_category'); ?>
                </h4>
            </div>

            <div class="modal-body">
                <div
                    id="quick_category_message"
                    class="quick-category-message"
                    role="alert"
                ></div>

                <div class="form-group">
                    <label for="quick_category_name">
                        <?= lang('category_name'); ?>
                        <span class="required-star">*</span>
                    </label>

                    <input
                        type="text"
                        id="quick_category_name"
                        class="form-control"
                        maxlength="150"
                        autocomplete="off"
                        placeholder="<?= html_escape(lang('category_name')); ?>"
                    >
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="quick_category_code">
                        <?= lang('category_code'); ?>
                    </label>

                    <input
                        type="text"
                        id="quick_category_code"
                        class="form-control"
                        maxlength="50"
                        autocomplete="off"
                        placeholder="<?= html_escape(lang('category_code_optional')); ?>"
                    >

                    <div class="erp-field-help">
                        <?= lang('category_code_auto_help'); ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= lang('cancel'); ?>
                </button>

                <button
                    type="button"
                    id="save_quick_category"
                    class="btn btn-primary"
                >
                    <i class="fa fa-save"></i>
                    <?= lang('save_category'); ?>
                </button>
            </div>

        </div>
    </div>
</div>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

<script>
$(function () {
    if ($.fn.datetimepicker) {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            showClear: true,
            showClose: true,
            useCurrent: false,
            widgetPositioning: {
                horizontal: 'auto',
                vertical: 'bottom'
            }
        });
    }

    function initialiseExpenseSelect2() {
        if (!$.fn.select2) {
            return;
        }

        $('.expense-add-erp select.erp-select2').each(function () {
            var $select = $(this);

            var alreadyInitialised =
                !!$select.data('select2') ||
                $select.hasClass('select2-hidden-accessible') ||
                $select.hasClass('select2-offscreen');

            if (alreadyInitialised) {
                $select.next('.select2-container').css('width', '100%');
                return;
            }

            try {
                $select.select2({
                    width: '100%',
                    dropdownAutoWidth: true,
                    minimumResultsForSearch: 0
                });
            } catch (error) {
                $select.select2();
                $select.next('.select2-container').css('width', '100%');
            }
        });
    }

    var expenseCategoryCsrfName =
        <?= json_encode($this->security->get_csrf_token_name()); ?>;

    var expenseCategoryCsrfHash =
        <?= json_encode($this->security->get_csrf_hash()); ?>;

    function showQuickCategoryMessage(type, message) {
        $('#quick_category_message')
            .removeClass('error success')
            .addClass(type)
            .text(message || '')
            .show();
    }

    function clearQuickCategoryMessage() {
        $('#quick_category_message')
            .removeClass('error success')
            .text('')
            .hide();
    }

    function updatePageCsrfToken(newHash) {
        if (!newHash) {
            return;
        }

        expenseCategoryCsrfHash = newHash;

        $('input[name="' + expenseCategoryCsrfName + '"]')
            .val(newHash);
    }

    function selectNewExpenseCategory(categoryId, categoryName) {
        var $category = $('#category');
        var id = String(categoryId);
        var optionExists = $category.find('option').filter(function () {
            return String($(this).val()) === id;
        }).length > 0;

        if (!optionExists) {
            $category.append(
                $('<option>', {
                    value: id,
                    text: categoryName
                })
            );
        }

        $category.val(id);

        /*
         * Update Select2 v3 and v4.
         */
        try {
            $category.trigger('change.select2');
        } catch (error) {}

        $category.trigger('change');
    }

    $('#expenseCategoryModal').on('show.bs.modal', function () {
        clearQuickCategoryMessage();
        $('#quick_category_name').val('');
        $('#quick_category_code').val('');
    });

    $('#expenseCategoryModal').on('shown.bs.modal', function () {
        $('#quick_category_name').trigger('focus');
    });

    $('#quick_category_name, #quick_category_code').on('keydown', function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            $('#save_quick_category').trigger('click');
        }
    });

    $('#save_quick_category').on('click', function () {
        var $button = $(this);
        var name = $.trim($('#quick_category_name').val());
        var code = $.trim($('#quick_category_code').val());

        clearQuickCategoryMessage();

        if (!name) {
            showQuickCategoryMessage(
                'error',
                <?= json_encode(lang('category_name_required'), JSON_UNESCAPED_UNICODE); ?>
            );

            $('#quick_category_name').trigger('focus');
            return;
        }

        var postData = {
            name: name,
            code: code
        };

        postData[expenseCategoryCsrfName] = expenseCategoryCsrfHash;

        $button
            .prop('disabled', true)
            .html(
                '<i class="fa fa-spinner fa-spin"></i> ' +
                <?= json_encode(lang('saving'), JSON_UNESCAPED_UNICODE); ?>
            );

        $.ajax({
            url: <?= json_encode(site_url('purchases/quick_add_expense_category')); ?>,
            type: 'POST',
            dataType: 'json',
            data: postData,

            success: function (response) {
                updatePageCsrfToken(response.csrf_hash);

                if (!response || response.status !== 'success') {
                    showQuickCategoryMessage(
                        'error',
                        (response && response.message)
                            ? response.message
                            : <?= json_encode(lang('category_save_failed'), JSON_UNESCAPED_UNICODE); ?>
                    );

                    return;
                }

                selectNewExpenseCategory(
                    response.category.id,
                    response.category.name
                );

                showQuickCategoryMessage(
                    'success',
                    response.message ||
                    <?= json_encode(lang('category_saved_successfully'), JSON_UNESCAPED_UNICODE); ?>
                );

                window.setTimeout(function () {
                    $('#expenseCategoryModal').modal('hide');
                }, 350);
            },

            error: function (xhr) {
                var response = xhr.responseJSON || {};

                updatePageCsrfToken(response.csrf_hash);

                showQuickCategoryMessage(
                    'error',
                    response.message ||
                    <?= json_encode(lang('request_failed'), JSON_UNESCAPED_UNICODE); ?>
                );
            },

            complete: function () {
                $button
                    .prop('disabled', false)
                    .html(
                        '<i class="fa fa-save"></i> ' +
                        <?= json_encode(lang('save_category'), JSON_UNESCAPED_UNICODE); ?>
                    );
            }
        });
    });

    function calculateExpenseTotal() {
        var quantity = parseFloat($('#quantity').val()) || 0;
        var amount = parseFloat($('#amount').val()) || 0;
        var total = quantity * amount;

        $('#total').val(total.toFixed(2));
        $('#total_display').text(total.toFixed(2));
        $('#base_amount_mmk').val(total.toFixed(2));
    }

    initialiseExpenseSelect2();
    window.setTimeout(initialiseExpenseSelect2, 100);

    $('#quantity, #amount').on('input change keyup', calculateExpenseTotal);

    $('#reset_expense_form').on('click', function () {
        var form = document.getElementById('expense_add_form');

        if (form) {
            form.reset();
        }

        $('#category').val('').trigger('change');
        $('#quantity').val('1');
        $('#amount').val('0.00');
        $('#total').val('0.00');
        $('#total_display').text('0.00');
        $('#base_amount_mmk').val('0.00');
    });

    calculateExpenseTotal();
});
</script>