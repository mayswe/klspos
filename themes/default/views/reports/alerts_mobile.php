<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * KLSPOS Low Stock Alerts mobile/app mode.
 */
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$app_language =
    $this->input->get('app_lang', true) ?:
    $this->input->post('app_lang', true);
?>


<style>
/* =========================================================
   KLSPOS - Low Stock Alerts | Responsive ERP Style
   ========================================================= */
.stock-alerts-erp {
    --erp-primary: #2f8191;
    --erp-primary-dark: #276b78;
    --erp-success: #20a464;
    --erp-warning: #f39c12;
    --erp-danger: #e5533d;
    --erp-bg: #f4f7fb;
    --erp-card: #ffffff;
    --erp-border: #dfe6ee;
    --erp-text: #263548;
    --erp-muted: #718096;

    padding: 14px !important;
    background: var(--erp-bg);
    color: var(--erp-text);
    font-family: "Noto Sans Myanmar", "Pyidaungsu", "Myanmar Text",
                 "Noto Sans", Arial, sans-serif;
}

.stock-alerts-erp .erp-shell {
    max-width: 1450px;
    margin: 0 auto;
    overflow: hidden;
    border: 1px solid var(--erp-border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
}

.stock-alerts-erp .erp-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 17px 18px;
    border-bottom: 1px solid var(--erp-border);
    background: linear-gradient(135deg, #ffffff 0%, #f5fafb 100%);
}

.stock-alerts-erp .erp-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.stock-alerts-erp .erp-title-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    border-radius: 10px;
    background: #fff1ee;
    color: var(--erp-danger);
    font-size: 19px;
}

.stock-alerts-erp .erp-page-title {
    margin: 0;
    color: var(--erp-text);
    font-size: 20px;
    font-weight: 800;
    line-height: 1.45;
}

.stock-alerts-erp .erp-page-subtitle {
    margin: 2px 0 0;
    color: var(--erp-muted);
    font-size: 12px;
    line-height: 1.55;
}

.stock-alerts-erp .erp-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stock-alerts-erp .erp-header-actions .btn {
    min-height: 38px;
    border-radius: 7px;
    font-weight: 800;
}

.stock-alerts-erp .erp-content {
    padding: 15px 16px 18px;
}

/* Summary */
.stock-alerts-erp .erp-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 13px;
}

.stock-alerts-erp .erp-stat-card {
    position: relative;
    min-height: 88px;
    overflow: hidden;
    padding: 14px 14px 12px;
    border: 1px solid #e6edf3;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 9px rgba(15, 23, 42, .04);
}

.stock-alerts-erp .erp-stat-card:before {
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    content: "";
    background: var(--erp-primary);
}

.stock-alerts-erp .erp-stat-card.warning:before { background: var(--erp-warning); }
.stock-alerts-erp .erp-stat-card.danger:before  { background: var(--erp-danger); }
.stock-alerts-erp .erp-stat-card.success:before { background: var(--erp-success); }

.stock-alerts-erp .erp-stat-label {
    margin-bottom: 6px;
    color: #7a8793;
    font-size: 12px;
    line-height: 1.45;
}

.stock-alerts-erp .erp-stat-value {
    padding-right: 38px;
    color: #263238;
    font-size: 20px;
    font-weight: 900;
    line-height: 1.2;
    word-break: break-word;
}

.stock-alerts-erp .erp-stat-icon {
    position: absolute;
    right: 14px;
    bottom: 10px;
    color: rgba(0, 0, 0, .09);
    font-size: 30px;
}

/* Filter area */
.stock-alerts-erp .erp-filter-card {
    margin-bottom: 12px;
    padding: 12px;
    border: 1px solid #e2e9ef;
    border-radius: 10px;
    background: #fbfdff;
}

.stock-alerts-erp .erp-filter-grid {
    display: grid;
    grid-template-columns: 1.3fr repeat(2, minmax(0, 1fr));
    gap: 12px;
    align-items: end;
}

.stock-alerts-erp .erp-field {
    min-width: 0;
}

.stock-alerts-erp label {
    display: block;
    min-height: 20px;
    margin-bottom: 5px;
    color: #5d6e80;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.5;
}

.stock-alerts-erp .form-control {
    width: 100%;
    height: 38px;
    border: 1px solid #cfd9e3;
    border-radius: 7px;
    background: #fff;
    box-shadow: none;
}

.stock-alerts-erp .erp-search-box {
    position: relative;
}

.stock-alerts-erp .erp-search-box i {
    position: absolute;
    top: 50%;
    left: 12px;
    z-index: 2;
    transform: translateY(-50%);
    color: #94a3b8;
}

.stock-alerts-erp .erp-search-box .form-control {
    padding-left: 36px;
}

/* Select2 */
.stock-alerts-erp .select2-container {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
}

.stock-alerts-erp select.erp-select2.select2-hidden-accessible,
.stock-alerts-erp select.erp-select2.select2-offscreen {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    border: 0 !important;
    clip: rect(0 0 0 0) !important;
    overflow: hidden !important;
}

/* Select2 v3 */
.stock-alerts-erp .select2-container .select2-choice {
    width: 100% !important;
    height: 38px !important;
    padding: 0 34px 0 11px !important;
    border: 1px solid #cfd9e3 !important;
    border-radius: 7px !important;
    background: #fff !important;
    box-shadow: none !important;
    line-height: 36px !important;
}

.stock-alerts-erp .select2-container .select2-choice > .select2-chosen {
    line-height: 36px !important;
}

.stock-alerts-erp .select2-container .select2-choice .select2-arrow {
    width: 32px !important;
    height: 36px !important;
    border-left: 0 !important;
    background: transparent !important;
}

/* Select2 v4 */
.stock-alerts-erp .select2-container--default .select2-selection--single {
    width: 100% !important;
    height: 38px !important;
    min-height: 38px !important;
    border: 1px solid #cfd9e3 !important;
    border-radius: 7px !important;
    background: #fff !important;
    box-shadow: none !important;
}

.stock-alerts-erp .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    padding-left: 11px !important;
    padding-right: 34px !important;
    line-height: 36px !important;
}

.stock-alerts-erp .select2-container--default
.select2-selection--single
.select2-selection__arrow {
    width: 32px !important;
    height: 36px !important;
}

.select2-drop,
.select2-dropdown,
.select2-container--open {
    z-index: 999999 !important;
}

/* Toolbar */
.stock-alerts-erp .erp-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    padding: 11px 12px;
    border: 1px solid #e2e9ef;
    border-bottom: 0;
    border-radius: 10px 10px 0 0;
    background: #fbfdff;
}

.stock-alerts-erp .erp-toolbar-left,
.stock-alerts-erp .erp-toolbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.stock-alerts-erp .dt-buttons .btn,
.stock-alerts-erp .dt-buttons .dt-button {
    margin-right: 4px !important;
    margin-bottom: 3px !important;
    padding: 7px 11px !important;
    border: 1px solid #dbe3ed !important;
    border-radius: 7px !important;
    background: #f8fafc !important;
    color: #334155 !important;
    box-shadow: none !important;
    font-size: 12px;
    font-weight: 800;
}

.stock-alerts-erp .erp-scroll-hint {
    display: none;
    margin: 0 0 7px;
    color: #6f7e8d;
    font-size: 12px;
}

/* Table */
.stock-alerts-erp .erp-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    border: 1px solid #e2e9ef;
    border-radius: 0 0 10px 10px;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.stock-alerts-erp .dataTables_wrapper,
.stock-alerts-erp .dataTables_scroll,
.stock-alerts-erp .dataTables_scrollHead,
.stock-alerts-erp .dataTables_scrollBody,
.stock-alerts-erp .dataTables_scrollFoot {
    width: 100% !important;
    max-width: 100% !important;
}

.stock-alerts-erp .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.stock-alerts-erp table.dataTable {
    width: 100% !important;
    min-width: 1050px !important;
    margin: 0 !important;
    border-collapse: separate !important;
    border-spacing: 0;
}

.stock-alerts-erp table.dataTable thead th {
    padding: 10px 9px !important;
    border-right: 1px solid #edf2f7 !important;
    border-bottom: 1px solid #dfe6ef !important;
    background: #f3f6f9 !important;
    color: #334155;
    font-size: 12px;
    font-weight: 900;
    vertical-align: middle;
    white-space: nowrap;
}

.stock-alerts-erp table.dataTable tbody td {
    padding: 10px 9px !important;
    border-right: 1px solid #edf2f7 !important;
    border-bottom: 1px solid #edf2f7 !important;
    color: #334155;
    vertical-align: middle;
    white-space: nowrap;
}

.stock-alerts-erp table.dataTable tbody tr:nth-child(even) td {
    background: #fafafa;
}

.stock-alerts-erp table.dataTable tbody tr:hover td {
    background: #f1f9f7 !important;
}

.stock-alerts-erp table.dataTable tfoot th {
    padding: 8px !important;
    border-top: 1px solid #dfe6ef !important;
    border-right: 1px solid #edf2f7 !important;
    background: #f8fafc !important;
    font-weight: 900;
    white-space: nowrap;
}

.stock-alerts-erp .product-thumb {
    display: block;
    width: 38px;
    height: 38px;
    margin: 0 auto;
    overflow: hidden;
    border: 1px solid #dfe6ee;
    border-radius: 8px;
    background: #f8fafc;
}

.stock-alerts-erp .product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.stock-alerts-erp .type-badge,
.stock-alerts-erp .method-badge,
.stock-alerts-erp .stock-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 65px;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 900;
}

.stock-alerts-erp .type-standard { background: #e8f6f7; color: #276b78; }
.stock-alerts-erp .type-combo    { background: #fff4df; color: #9a6700; }
.stock-alerts-erp .type-service  { background: #efe9ff; color: #6941c6; }
.stock-alerts-erp .method-inclusive { background: #e9f8ef; color: #18733c; }
.stock-alerts-erp .method-exclusive { background: #fff1e6; color: #b54708; }
.stock-alerts-erp .stock-zero { background: #fff0ed; color: #b42318; }
.stock-alerts-erp .stock-low  { background: #fff8e8; color: #8a6116; }

.stock-alerts-erp .qty-cell,
.stock-alerts-erp .money-cell {
    display: block;
    text-align: right;
    font-weight: 800;
}

.stock-alerts-erp .erp-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex-wrap: wrap;
    white-space: nowrap;
}

.stock-alerts-erp .dataTables_info {
    padding-top: 11px !important;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

/* Modal */
.stock-alerts-erp .modal-content,
#picModal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 12px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, .22);
}

#picModal #product_image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

@media (max-width: 991px) {
    .stock-alerts-erp .erp-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .stock-alerts-erp .erp-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .stock-alerts-erp .erp-scroll-hint {
        display: block;
    }
}

@media (max-width: 767px) {
    .stock-alerts-erp {
        padding: 7px !important;
    }

    .stock-alerts-erp .erp-page-header {
        align-items: flex-start;
        padding: 13px;
    }

    .stock-alerts-erp .erp-page-title {
        font-size: 17px;
    }

    .stock-alerts-erp .erp-page-subtitle {
        display: none;
    }

    .stock-alerts-erp .erp-content {
        padding: 10px 9px 12px;
    }

    .stock-alerts-erp .erp-summary-grid,
    .stock-alerts-erp .erp-filter-grid {
        grid-template-columns: 1fr;
    }

    .stock-alerts-erp .erp-toolbar {
        display: block;
    }

    .stock-alerts-erp .erp-toolbar-left,
    .stock-alerts-erp .erp-toolbar-right {
        width: 100%;
    }

    .stock-alerts-erp .erp-toolbar-right {
        margin-top: 8px;
    }
}
</style>

<?php if ($is_app_mode) { ?>
<style>
/* =========================================================
   KLSPOS Low Stock Alerts - True Mobile/App Layout
   Applied whenever ?app=1 is present.
   ========================================================= */

/* Remove AdminLTE desktop shell */
.main-header,
.main-sidebar,
.main-footer,
.control-sidebar,
.control-sidebar-bg,
.content-header,
.breadcrumb {
    display: none !important;
}

html,
body,
.wrapper {
    width: 100% !important;
    min-width: 0 !important;
    min-height: 100% !important;
    margin: 0 !important;
    background: #f4f7fb !important;
    overflow-x: hidden !important;
}

.content-wrapper,
.right-side,
.sidebar-mini .content-wrapper,
.sidebar-collapse .content-wrapper {
    width: 100% !important;
    min-width: 0 !important;
    min-height: 100vh !important;
    margin-left: 0 !important;
    padding-top: 0 !important;
    background: #f4f7fb !important;
}

/* Full-width app page */
.content.stock-alerts-erp {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.stock-alerts-erp .erp-shell {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    border-radius: 12px !important;
    box-shadow: none !important;
}

/* Header */
.stock-alerts-erp .erp-page-header {
    display: block !important;
    padding: 13px !important;
}

.stock-alerts-erp .erp-title-wrap {
    width: 100% !important;
}

.stock-alerts-erp .erp-page-title {
    font-size: 17px !important;
    line-height: 1.45 !important;
}

.stock-alerts-erp .erp-page-subtitle {
    display: block !important;
    margin-top: 3px !important;
    font-size: 13px !important;
}

.stock-alerts-erp .erp-header-actions {
    width: 100% !important;
    margin-top: 11px !important;
}

.stock-alerts-erp .erp-header-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    width: 100% !important;
    min-height: 43px !important;
    font-size: 14px !important;
}

/* Content */
.stock-alerts-erp .erp-content {
    padding: 10px !important;
}

/* Summary cards: correct 2 x 2 order */
.stock-alerts-erp .erp-summary-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    grid-auto-flow: row !important;
    gap: 8px !important;
    width: 100% !important;
    margin: 0 0 12px !important;
}

.stock-alerts-erp .erp-stat-card {
    width: 100% !important;
    min-height: 86px !important;
    margin: 0 !important;
    padding: 12px !important;
}

.stock-alerts-erp .erp-stat-label {
    font-size: 13px !important;
    line-height: 1.4 !important;
}

.stock-alerts-erp .erp-stat-value {
    font-size: 18px !important;
    line-height: 1.35 !important;
}

/* Filter controls */
.stock-alerts-erp .erp-filter-card {
    padding: 12px !important;
}

.stock-alerts-erp .erp-filter-grid {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 10px !important;
}

.stock-alerts-erp label {
    font-size: 13px !important;
}

.stock-alerts-erp .form-control,
.stock-alerts-erp .select2-container .select2-choice,
.stock-alerts-erp .select2-container--default .select2-selection--single {
    width: 100% !important;
    height: 42px !important;
    min-height: 42px !important;
    font-size: 14px !important;
}

.stock-alerts-erp .select2-container {
    width: 100% !important;
}

.stock-alerts-erp .select2-container .select2-choice,
.stock-alerts-erp .select2-container .select2-choice > .select2-chosen,
.stock-alerts-erp .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    line-height: 40px !important;
}

.stock-alerts-erp .erp-search-box .form-control {
    padding-left: 38px !important;
}

/* Toolbar */
.stock-alerts-erp .erp-toolbar {
    display: block !important;
    padding: 10px !important;
}

.stock-alerts-erp .erp-toolbar-left,
.stock-alerts-erp .erp-toolbar-right {
    width: 100% !important;
}

.stock-alerts-erp .erp-toolbar-left,
.stock-alerts-erp .dt-buttons {
    display: none !important;
}

.stock-alerts-erp .erp-toolbar-right {
    margin: 0 !important;
    font-size: 12px !important;
}

.stock-alerts-erp .erp-scroll-hint {
    display: block !important;
    font-size: 12px !important;
}

/* Table */
.stock-alerts-erp .erp-table-wrap {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
}

.stock-alerts-erp .dataTables_wrapper,
.stock-alerts-erp .dataTables_scroll,
.stock-alerts-erp .dataTables_scrollHead,
.stock-alerts-erp .dataTables_scrollBody,
.stock-alerts-erp .dataTables_scrollFoot {
    width: 100% !important;
    max-width: 100% !important;
}

.stock-alerts-erp .dataTables_scrollBody {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
}

.stock-alerts-erp table.dataTable {
    width: 100% !important;
    min-width: 1050px !important;
}

.stock-alerts-erp table.dataTable thead th,
.stock-alerts-erp table.dataTable tbody td,
.stock-alerts-erp table.dataTable tfoot th {
    padding: 9px 7px !important;
    font-size: 13px !important;
    white-space: nowrap !important;
}

.stock-alerts-erp .dataTables_info {
    font-size: 12px !important;
}

.stock-alerts-erp .pagination > li > a,
.stock-alerts-erp .pagination > li > span {
    padding: 7px 10px !important;
    font-size: 12px !important;
}

/* Popups above app content */
.bootstrap-datetimepicker-widget,
.select2-drop,
.select2-dropdown,
.select2-container--open {
    z-index: 999999 !important;
}

@media (max-width: 420px) {
    .stock-alerts-erp .erp-summary-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
<?php } ?>


<script>
var stockAlertsTable = null;
</script>

<script type="text/javascript">
$(document).ready(function () {

    function toNumber(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        if (typeof value === 'number') {
            return value;
        }

        return parseFloat(
            String(value)
                .replace(/<[^>]*>/g, '')
                .replace(/,/g, '')
                .replace(/[^0-9.\-]/g, '')
        ) || 0;
    }

    function moneyText(value) {
        return toNumber(value).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function quantityText(value) {
        return toNumber(value).toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 4
        });
    }

    function typeRender(data, type) {
        if (type !== 'display') {
            return data;
        }

        var label = data;
        var cssClass = 'type-standard';

        if (data === 'standard') {
            label = <?= json_encode(lang('standard'), JSON_UNESCAPED_UNICODE); ?>;
            cssClass = 'type-standard';
        } else if (data === 'combo') {
            label = <?= json_encode(lang('combo'), JSON_UNESCAPED_UNICODE); ?>;
            cssClass = 'type-combo';
        } else if (data === 'service') {
            label = <?= json_encode(lang('service'), JSON_UNESCAPED_UNICODE); ?>;
            cssClass = 'type-service';
        }

        return '<span class="type-badge ' + cssClass + '">' + (label || '') + '</span>';
    }

    function imageRender(filename, type) {
        if (type !== 'display') {
            return filename || '';
        }

        if (!filename) {
            return '<span class="product-thumb"><i class="fa fa-picture-o" style="line-height:38px;color:#94a3b8;"></i></span>';
        }

        var encoded = encodeURIComponent(filename);

        return '<a href="<?= base_url('uploads/'); ?>' + encoded + '" class="open-image product-thumb">' +
            '<img src="<?= base_url('uploads/thumbs/'); ?>' + encoded + '" alt="">' +
        '</a>';
    }

    function taxMethodRender(value, type) {
        if (type !== 'display') {
            return value;
        }

        if (String(value) === '0') {
            return '<span class="method-badge method-inclusive">' +
                <?= json_encode(lang('inclusive'), JSON_UNESCAPED_UNICODE); ?> +
            '</span>';
        }

        return '<span class="method-badge method-exclusive">' +
            <?= json_encode(lang('exclusive'), JSON_UNESCAPED_UNICODE); ?> +
        '</span>';
    }

    function quantityRender(value, type) {
        if (type !== 'display') {
            return toNumber(value);
        }

        return '<span class="qty-cell">' + quantityText(value) + '</span>';
    }

    function moneyRender(value, type) {
        if (type !== 'display') {
            return toNumber(value);
        }

        return '<span class="money-cell">' + moneyText(value) + '</span>';
    }

    function stockStatusRender(row) {
        var quantity = toNumber(row.quantity);

        if (quantity <= 0) {
            return '<span class="stock-badge stock-zero">' +
                <?= json_encode(lang('out_of_stock'), JSON_UNESCAPED_UNICODE); ?> +
            '</span>';
        }

        return '<span class="stock-badge stock-low">' +
            <?= json_encode(lang('low_stock'), JSON_UNESCAPED_UNICODE); ?> +
        '</span>';
    }

    function initialiseAlertSelect2() {
        if (!$.fn.select2) {
            return;
        }

        $('.stock-alerts-erp select.erp-select2').each(function () {
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

    function fillFilterOptions(json) {
        var rows = json.data || json.aaData || [];
        var categories = {};
        var types = {};

        $.each(rows, function (_, row) {
            if ($.trim(row.cname || '') !== '') {
                categories[row.cname] = true;
            }

            if ($.trim(row.type || '') !== '') {
                types[row.type] = true;
            }
        });

        if ($('#categoryFilter option').length <= 1) {
            Object.keys(categories).sort().forEach(function (name) {
                $('#categoryFilter').append(
                    $('<option>', {value: name, text: name})
                );
            });
        }

        if ($('#typeFilter option').length <= 1) {
            Object.keys(types).sort().forEach(function (value) {
                var text = value;

                if (value === 'standard') {
                    text = <?= json_encode(lang('standard'), JSON_UNESCAPED_UNICODE); ?>;
                } else if (value === 'combo') {
                    text = <?= json_encode(lang('combo'), JSON_UNESCAPED_UNICODE); ?>;
                } else if (value === 'service') {
                    text = <?= json_encode(lang('service'), JSON_UNESCAPED_UNICODE); ?>;
                }

                $('#typeFilter').append(
                    $('<option>', {value: value, text: text})
                );
            });
        }

        initialiseAlertSelect2();
    }

    function updateSummary(api) {
        var rows = api.rows({search: 'applied'}).data();
        var zeroStock = 0;
        var totalQuantity = 0;
        var stockValue = 0;

        rows.each(function (row) {
            var quantity = toNumber(row.quantity);
            var cost = toNumber(row.cost);

            if (quantity <= 0) {
                zeroStock++;
            }

            totalQuantity += quantity;
            stockValue += quantity * cost;
        });

        $('#summary_alert_items').text(rows.length);
        $('#summary_zero_stock').text(zeroStock);
        $('#summary_quantity').text(quantityText(totalQuantity));
        $('#summary_stock_value').text(moneyText(stockValue));
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'fileData') {
            return true;
        }

        var status = $('#stockStatusFilter').val();

        if (!status) {
            return true;
        }

        var row = stockAlertsTable.row(dataIndex).data();
        var quantity = row ? toNumber(row.quantity) : 0;

        if (status === 'zero') {
            return quantity <= 0;
        }

        if (status === 'low') {
            return quantity > 0;
        }

        return true;
    });

    stockAlertsTable = $('#fileData').DataTable({
        dom: 'Brtip',
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        responsive: false,

        ajax: {
            url: '<?= site_url('reports/get_alerts'); ?>',
            type: 'POST',
            data: function (data) {
                data.<?= $this->security->get_csrf_token_name(); ?> =
                    '<?= $this->security->get_csrf_hash(); ?>';
            },
            dataSrc: function (json) {
                fillFilterOptions(json);
                return json.data || json.aaData || [];
            }
        },

        buttons: [
            {
                extend: 'copyHtml5',
                text: <?= json_encode(lang('copy'), JSON_UNESCAPED_UNICODE); ?>,
                footer: true,
                exportOptions: {columns: [0,1,2,3,4,5,6,7,8]}
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                footer: true,
                exportOptions: {columns: [0,1,2,3,4,5,6,7,8]}
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                footer: true,
                exportOptions: {columns: [0,1,2,3,4,5,6,7,8]}
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {columns: [0,1,2,3,4,5,6,7,8]}
            },
            {
                extend: 'colvis',
                text: <?= json_encode(lang('columns'), JSON_UNESCAPED_UNICODE); ?>
            }
        ],

        columns: [
            {
                data: 'id',
                className: 'text-center'
            },
            {
                data: 'code'
            },
            {
                data: 'pname'
            },
            {
                data: 'cname'
            },
            {
                data: 'quantity',
                render: quantityRender,
                className: 'text-right'
            },
            {
                data: 'alert_quantity',
                render: quantityRender,
                className: 'text-right'
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (type !== 'display') {
                        return toNumber(row.quantity) <= 0 ? 'zero' : 'low';
                    }

                    return stockStatusRender(row);
                },
                className: 'text-center',
                searchable: false
            },
            {
                data: 'cost',
                render: moneyRender,
                searchable: false,
                className: 'text-right'
            },
            {
                data: 'price',
                render: moneyRender,
                searchable: false,
                className: 'text-right'
            },
            {
                data: 'Actions',
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function (data) {
                    return '<div class="erp-actions">' + (data || '') + '</div>';
                }
            }
        ],

        order: [[4, 'asc']],

        footerCallback: function () {
            var api = this.api();

            [4, 5].forEach(function (columnIndex) {
                var total = api
                    .column(columnIndex, {search: 'applied'})
                    .data()
                    .reduce(function (a, b) {
                        return toNumber(a) + toNumber(b);
                    }, 0);

                $(api.column(columnIndex).footer()).html(
                    '<span class="qty-cell">' + quantityText(total) + '</span>'
                );
            });

            updateSummary(api);
        },

        initComplete: function () {
            var api = this.api();

            api.buttons()
                .container()
                .appendTo('#alertDataButtons');

            api.columns.adjust();
        }
    });

    $('#globalSearch').on('keyup change', function (event) {
        var keyCode = event.keyCode || event.which;

        if (
            (keyCode === 13 && stockAlertsTable.search() !== this.value) ||
            (stockAlertsTable.search() !== '' && this.value === '')
        ) {
            stockAlertsTable.search(this.value).draw();
        }
    });

    $('#categoryFilter').on('change', function () {
        stockAlertsTable
            .column(3)
            .search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false)
            .draw();
    });

    $('#stockStatusFilter').on('change', function () {
        stockAlertsTable.draw();
    });

    $('#refreshAlerts').on('click', function () {
        stockAlertsTable.ajax.reload(null, false);
    });

    $('#fileData').on('click', '.open-image', function () {
        $('#product_image').attr('src', $(this).attr('href'));
        $('#picModal').modal('show');
        return false;
    });

    $('#fileData').on('click', '.ap', function () {
        var id = $(this).attr('data-id');

        $.get('<?= site_url('purchases/suggestions'); ?>/' + id)
            .done(function (data) {
                var item = typeof data === 'string' ? JSON.parse(data) : data;
                var spoitems = get('spoitems')
                    ? JSON.parse(get('spoitems'))
                    : {};

                var itemId = Settings.item_addition == 1
                    ? item.item_id
                    : item.id;

                if (spoitems[itemId]) {
                    spoitems[itemId].row.qty =
                        parseFloat(spoitems[itemId].row.qty) + 1;
                } else {
                    spoitems[itemId] = item;
                }

                store('spoitems', JSON.stringify(spoitems));

                $('#custom-alerts')
                    .find('.alert')
                    .removeClass('alert-danger')
                    .addClass('alert-success');

                $('#custom-alerts')
                    .find('.custom-msg')
                    .text(
                        <?= json_encode(lang('po_item_added'), JSON_UNESCAPED_UNICODE); ?> +
                        ' ' + spoitems[itemId].label +
                        ' = ' + spoitems[itemId].row.qty
                    );

                $('#custom-alerts').show();
            });

        return false;
    });

    initialiseAlertSelect2();
    window.setTimeout(initialiseAlertSelect2, 100);

    $(window).on('resize orientationchange', function () {
        window.setTimeout(function () {
            stockAlertsTable.columns.adjust();
            $('.stock-alerts-erp .select2-container').css('width', '100%');
        }, 120);
    });
});
</script>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<section class="content stock-alerts-erp kls-mobile-ui">
    <div class="erp-shell">

        <div class="erp-page-header">
            <div class="erp-title-wrap">
                <div class="erp-title-icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>

                <div>
                    <h1 class="erp-page-title"><?= $page_title; ?></h1>
                    <p class="erp-page-subtitle"><?= lang('stock_alerts_description'); ?></p>
                </div>
            </div>

            <div class="erp-header-actions">
                <button type="button" id="refreshAlerts" class="btn btn-default">
                    <i class="fa fa-refresh"></i>
                    <?= lang('refresh'); ?>
                </button>
            </div>
        </div>

        <div class="erp-content">

            <div class="erp-summary-grid">
                <div class="erp-stat-card warning">
                    <div class="erp-stat-label"><?= lang('alert_items'); ?></div>
                    <div class="erp-stat-value" id="summary_alert_items">0</div>
                    <i class="fa fa-list erp-stat-icon"></i>
                </div>

                <div class="erp-stat-card danger">
                    <div class="erp-stat-label"><?= lang('out_of_stock_items'); ?></div>
                    <div class="erp-stat-value" id="summary_zero_stock">0</div>
                    <i class="fa fa-times-circle erp-stat-icon"></i>
                </div>

                <div class="erp-stat-card">
                    <div class="erp-stat-label"><?= lang('current_stock_quantity'); ?></div>
                    <div class="erp-stat-value" id="summary_quantity">0</div>
                    <i class="fa fa-cubes erp-stat-icon"></i>
                </div>

                <div class="erp-stat-card success">
                    <div class="erp-stat-label"><?= lang('estimated_stock_value'); ?></div>
                    <div class="erp-stat-value" id="summary_stock_value">0.00</div>
                    <i class="fa fa-calculator erp-stat-icon"></i>
                </div>
            </div>

            <div class="erp-filter-card">
                <div class="erp-filter-grid">

                    <div class="erp-field">
                        <label for="globalSearch"><?= lang('search_products'); ?></label>
                        <div class="erp-search-box">
                            <i class="fa fa-search"></i>
                            <input
                                type="text"
                                id="globalSearch"
                                class="form-control"
                                placeholder="<?= html_escape(lang('type_hit_enter')); ?>"
                            >
                        </div>
                    </div>

                    <div class="erp-field">
                        <label for="categoryFilter"><?= lang('category'); ?></label>
                        <select id="categoryFilter" class="form-control select2 erp-select2">
                            <option value=""><?= lang('all_categories'); ?></option>
                        </select>
                    </div>

                    <div class="erp-field">
                        <label for="stockStatusFilter"><?= lang('stock_status'); ?></label>
                        <select id="stockStatusFilter" class="form-control select2 erp-select2">
                            <option value=""><?= lang('all_stock_statuses'); ?></option>
                            <option value="zero"><?= lang('out_of_stock'); ?></option>
                            <option value="low"><?= lang('low_stock'); ?></option>
                        </select>
                    </div>

                </div>
            </div>

            <p class="erp-scroll-hint">
                <i class="fa fa-arrows-h"></i>
                <?= lang('swipe_table_horizontal'); ?>
            </p>

            <div class="erp-toolbar">
                <div class="erp-toolbar-left" id="alertDataButtons"></div>

                <div class="erp-toolbar-right">
                    <span class="text-muted">
                        <i class="fa fa-info-circle"></i>
                        <?= lang('stock_alert_table_help'); ?>
                    </span>
                </div>
            </div>

            <div class="erp-table-wrap">
                <table
                    id="fileData"
                    class="table table-striped table-bordered table-hover"
                    style="width:100%;"
                >
                    <thead>
                        <tr>
                            <th><?= lang('id'); ?></th>
                            <th><?= lang('code'); ?></th>
                            <th><?= lang('name'); ?></th>
                            <th><?= lang('category'); ?></th>
                            <th class="text-right"><?= lang('quantity'); ?></th>
                            <th class="text-right"><?= lang('alert_quantity'); ?></th>
                            <th><?= lang('stock_status'); ?></th>
                            <th class="text-right"><?= lang('cost'); ?></th>
                            <th class="text-right"><?= lang('price'); ?></th>
                            <th class="text-center"><?= lang('actions'); ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty">
                                <?= lang('loading_data_from_server'); ?>
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-right"><?= lang('total'); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

    <div
        class="modal fade"
        id="picModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="picModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-sm">
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

                    <h4 class="modal-title" id="picModalLabel">
                        <?= lang('product_image'); ?>
                    </h4>
                </div>

                <div class="modal-body text-center">
                    <img id="product_image" src="" alt="">
                </div>
            </div>
        </div>
    </div>
</section>
