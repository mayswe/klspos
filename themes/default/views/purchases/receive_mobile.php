<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$purchase_id = (int) $purchase->id;

$total_ordered   = 0;
$total_received  = 0;
$total_remaining = 0;

foreach ($items as $row) {
    $ordered   = (float) ($row->primary_qty ?? 0);
    $received  = (float) ($row->received_primary_qty ?? 0);
    $remaining = max(0, $ordered - $received);

    $total_ordered   += $ordered;
    $total_received  += $received;
    $total_remaining += $remaining;
}

$store_options = [];

if (!empty($stores)) {
    foreach ($stores as $store) {
        $store_options[(int) $store->id] = $store->name;
    }
}

$default_store_id = isset($_POST['receive_store_id'])
    ? (int) $_POST['receive_store_id']
    : (int) ($purchase->store_id ?? 0);

/*
|--------------------------------------------------------------------------
| Mobile / App Mode
|--------------------------------------------------------------------------
*/
$app_language =
    $this->input->get('app_lang', true) ?:
    (
        $this->input->post('app_lang', true) ?:
        ($Settings->selected_language ?? 'myanmar')
    );

$app_query =
    '?app=1&app_lang=' . rawurlencode($app_language);

$back_url =
    site_url('purchases') . $app_query;

$receive_post_url =
    site_url('purchases/receive_products') . $app_query;
?>

<style>
.receive-page {
    max-width: 1180px;
    margin: 0 auto;
    padding-bottom: 28px;
}

.receive-page .receive-page-card {
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .06);
}

.receive-page .receive-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-bottom: 1px solid #e2e8f0;
    background: #fff;
}

.receive-page .receive-page-title {
    margin: 0;
    color: #1e293b;
    font-size: 20px;
    font-weight: 800;
}

.receive-page .receive-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 38px;
    padding: 8px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #475569;
    font-weight: 700;
    text-decoration: none !important;
}

.receive-page .receive-back-btn:hover {
    background: #f8fafc;
    color: #1e293b;
}

.receive-page .receive-page-body {
    padding: 12px 14px 14px;
    background: #f8fafc;
}

.receive-page .receive-info-line {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 6px 12px;
    margin-bottom: 8px;
    padding: 8px 10px;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    background: #eff6ff;
    color: #334155;
    font-size: 16px;
    line-height: 1.4;
}

.receive-page .receive-info-line span {
    min-width: 0;
}

.receive-page .receive-info-item {
    display: flex;
    align-items: flex-start;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.receive-page .receive-info-label {
    display: block;
    color: #334155;
    font-weight: 800;
    line-height: 1.35;
}

.receive-page .receive-info-value {
    display: block;
    max-width: 100%;
    color: #475569;
    font-weight: normal;
    line-height: 1.35;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.receive-page .receive-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 7px;
    margin-bottom: 8px;
}

.receive-page .summary-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    min-width: 0;
    padding: 8px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
}

.receive-page .summary-card.received {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.receive-page .summary-card.remaining {
    border-color: #fed7aa;
    background: #fff7ed;
}

.receive-page .summary-label {
    display: block;
    margin-bottom: 0;
    color: #64748b;
    font-size: 16px;
    font-weight: 700;
}

.receive-page .summary-value {
    flex: 0 0 auto;
    color: #1e293b;
    font-size: 20px;
    font-weight: 900;
}

.receive-page .receive-form-row {
    display: grid;
    grid-template-columns: minmax(210px, .8fr) minmax(220px, 1fr) minmax(260px, 1.4fr);
    gap: 8px;
    margin-bottom: 8px;
}

.receive-page .form-group {
    margin-bottom: 0;
}

.receive-page label {
    display: block;
    margin-bottom: 3px;
    color: #334155;
    font-size: 16px;
    font-weight: 800;
}

.receive-page .form-control {
    height: 42px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-shadow: none;
    background: #fff;
}

.receive-page .form-control:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 2px rgba(96, 165, 250, .12);
}

.receive-page textarea.form-control {
    min-height: 42px;
    height: 42px;
    resize: vertical;
}

.receive-page .receive-section-title {
    margin: 4px 0 10px;
    color: #334155;
    font-size: 16px;
    font-weight: 800;
}

.receive-page .fill-remaining-table-row th {
    padding: 7px !important;
    border-bottom: 1px solid #fdba74 !important;
    background: #fff7ed !important;
    text-align: center !important;
}

.receive-page .fill-remaining-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    min-width: 0;
    min-height: 42px;
    padding: 8px 16px;
    border: 1px solid #ea580c !important;
    border-radius: 9px;
    background: #f97316 !important;
    color: #fff !important;
    font-size: 16px;
    font-weight: 900;
    box-shadow: 0 5px 12px rgba(234, 88, 12, .24);
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
}

.receive-page .fill-remaining-btn:hover,
.receive-page .fill-remaining-btn:focus {
    border-color: #c2410c !important;
    background: #ea580c !important;
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 7px 16px rgba(194, 65, 12, .28);
    outline: 0;
}

.receive-page .fill-remaining-btn.is-filled {
    border-color: #15803d !important;
    background: #16a34a !important;
    box-shadow: 0 5px 12px rgba(22, 163, 74, .22);
}

.receive-page .receive-table-card {
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    -webkit-overflow-scrolling: touch;
}

.receive-page .receive-table {
    width: 100%;
    min-width: 700px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

.receive-page .receive-table th {
    padding: 12px 10px;
    border-bottom: 1px solid #dbe3ec;
    background: #f1f5f9;
    color: #334155;
    font-size: 16px;
    font-weight: 800;
    white-space: nowrap;
}

.receive-page .receive-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #edf2f7;
    color: #334155;
    font-size: 16px;
    vertical-align: middle;
}

.receive-page .receive-table tbody tr:last-child td {
    border-bottom: 0;
}

.receive-page .product-name {
    width: 20%;
    min-width: 0;
    overflow-wrap: anywhere;
    word-break: break-word;
    font-weight: 800;
}

.receive-page .product-code {
    display: block;
    margin-top: 3px;
    color: #94a3b8;
    font-size: 16px;
    font-weight: 600;
}

.receive-page .qty-text {
    font-weight: 700;
    white-space: nowrap;
}

.receive-page .remaining-text {
    color: #ea580c;
    font-weight: 900;
    white-space: nowrap;
}

.receive-page .receive-qty {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    margin: 0;
    padding-right: 8px;
    padding-left: 8px;
    box-sizing: border-box;
    text-align: right;
    font-weight: 800;
}

.receive-page .receive-qty-cell {
    overflow: hidden;
}

.receive-page .receive-qty.is-auto-filled {
    border-color: #22c55e !important;
    background: #f0fdf4 !important;
    color: #166534;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, .12) !important;
}

.receive-page .receive-complete {
    color: #16a34a;
    font-weight: 800;
    white-space: nowrap;
}

.receive-page .received-action-cell {
    text-align: center !important;
}

.receive-page .received-item-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.receive-page .received-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 36px;
    padding: 6px 10px;
    border: 1px solid #93c5fd;
    border-radius: 8px;
    background: #eff6ff;
    color: #2563eb !important;
    font-size: 16px;
    font-weight: 800;
    line-height: 1.2;
    text-decoration: none !important;
    white-space: nowrap;
    cursor: pointer;
}

.receive-page .received-action-btn:hover,
.receive-page .received-action-btn:focus {
    border-color: #60a5fa;
    background: #dbeafe;
    color: #1d4ed8 !important;
    outline: 0;
    text-decoration: none !important;
}

/* Browser prompt/confirm အစား URL မပြသော custom dialog */
.receive-history-action-dialog {
    position: fixed;
    inset: 0;
    z-index: 12000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.receive-history-action-dialog.is-open {
    display: flex;
}

.receive-history-action-dialog .receive-history-dialog-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, .62);
}

.receive-history-action-dialog .receive-history-dialog-panel {
    position: relative;
    z-index: 1;
    width: 430px;
    max-width: 100%;
    overflow: hidden;
    border: 1px solid #dbe3ec;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 24px 60px rgba(15, 23, 42, .32);
}

.receive-history-action-dialog .receive-history-dialog-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.receive-history-action-dialog .receive-history-dialog-title {
    margin: 0;
    color: #1e293b;
    font-size: 18px;
    font-weight: 900;
}

.receive-history-action-dialog .receive-history-dialog-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    padding: 0;
    border: 1px solid #dbe3ec;
    border-radius: 8px;
    background: #fff;
    color: #64748b;
    font-size: 16px;
}

.receive-history-action-dialog .receive-history-dialog-body {
    padding: 16px;
}

.receive-history-action-dialog .receive-history-dialog-message {
    margin: 0;
    color: #475569;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.65;
}

.receive-history-action-dialog .receive-history-dialog-field {
    margin-top: 12px;
}

.receive-history-action-dialog .receive-history-dialog-field label {
    margin-bottom: 6px !important;
    font-size: 16px !important;
}

.receive-history-action-dialog .receive-history-dialog-input {
    width: 100%;
    height: 44px;
    padding: 8px 11px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #1e293b;
    font-size: 16px;
    font-weight: 800;
    box-sizing: border-box;
}

.receive-history-action-dialog .receive-history-dialog-input:focus {
    border-color: #3b82f6;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
}

.receive-history-action-dialog .receive-history-dialog-error {
    display: none;
    margin-top: 7px;
    color: #dc2626;
    font-size: 15px;
    font-weight: 700;
}

.receive-history-action-dialog .receive-history-dialog-error.is-visible {
    display: block;
}

.receive-history-action-dialog .receive-history-dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    padding: 12px 16px 15px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

.receive-history-action-dialog .receive-history-dialog-btn {
    min-width: 110px;
    min-height: 42px;
    padding: 8px 15px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #475569;
    font-size: 16px;
    font-weight: 800;
}

.receive-history-action-dialog .receive-history-dialog-confirm {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
}

.receive-history-action-dialog .receive-history-dialog-confirm.is-danger {
    border-color: #dc2626;
    background: #dc2626;
}

.receive-history-action-dialog .receive-history-dialog-btn:disabled {
    cursor: wait;
    opacity: .68;
}

body.receive-history-dialog-open {
    overflow: hidden !important;
}

@media (max-width: 575px) {
    .receive-history-action-dialog {
        padding: 10px;
    }

    .receive-history-action-dialog .receive-history-dialog-panel {
        width: 100%;
    }

    .receive-history-action-dialog .receive-history-dialog-footer {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .receive-history-action-dialog .receive-history-dialog-btn {
        width: 100%;
        min-width: 0;
    }
}

.receive-page .receive-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 16px;
}

.receive-page .btn-receive-save {
    min-width: 170px;
    min-height: 42px;
    border: 0;
    border-radius: 8px;
    background: #16a34a;
    color: #fff;
    font-weight: 800;
}

.receive-page .btn-receive-save:hover,
.receive-page .btn-receive-save:focus {
    background: #15803d;
    color: #fff;
}

.receive-page,
.receive-page input,
.receive-page select,
.receive-page textarea,
.receive-page button,
.receive-page a {
    font-family: "Pyidaungsu", "Noto Sans Myanmar", "Myanmar Text", Arial, sans-serif;
}

@media (max-width: 991px) {
    .receive-page .receive-form-row {
        grid-template-columns: 1fr 1fr;
    }

    .receive-page .receive-form-row .receive-note-field {
        grid-column: 1 / -1;
    }
}

@media (max-width: 767px) {
    .receive-page {
        padding: 0 8px 22px;
    }

    .receive-page .receive-page-header {
        padding: 14px;
    }

    .receive-page .receive-page-title {
        font-size: 17px;
    }

    .receive-page .receive-page-body {
        padding: 12px;
    }

    .receive-page .receive-summary {
        gap: 6px;
    }

    .receive-page .summary-card {
        padding: 10px 8px;
    }

    .receive-page .summary-label {
        font-size: 16px;
    }

    .receive-page .summary-value {
        font-size: 18px;
    }

    .receive-page .receive-form-row {
        grid-template-columns: 1fr;
    }

    .receive-page .receive-form-row .receive-note-field {
        grid-column: auto;
    }

    .receive-page .receive-table {
        min-width: 650px;
    }

    .receive-page .receive-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .receive-page .receive-actions .btn,
    .receive-page .btn-receive-save {
        width: 100%;
        min-width: 0;
    }
}

/* =========================================================
   KLSPOS Receive - APP / MOBILE MODE
   ========================================================= */

/*
 * App mode မှာ desktop header/sidebar မပြပါ။
 * Viewport က tablet width (ဥပမာ 928px) ဖြစ်နေရင်တောင်
 * ဒီ mobile view file ကို load လုပ်ထားတာဖြစ်လို့ အမြဲ hide ပါမယ်။
 */
html,
body {
    width: 100% !important;
    min-height: 100% !important;
    background: #f4f7fb !important;
}

.main-header,
.main-sidebar,
.main-footer,
.control-sidebar,
.breadcrumb,
.content-header {
    display: none !important;
}

.wrapper {
    width: 100% !important;
    min-height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #f4f7fb !important;
}

.content-wrapper,
.right-side {
    width: 100% !important;
    min-height: 100vh !important;
    margin-left: 0 !important;
    padding-top: 0 !important;
    background: #f4f7fb !important;
}

.content {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 8px !important;
    background: #f4f7fb !important;
}

.receive-page {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 0 14px !important;
}

.receive-page .receive-page-card {
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 12px !important;
    box-shadow: none !important;
}

.receive-page .receive-page-header {
    padding: 8px 10px !important;
}

.receive-page .receive-page-title {
    font-size: 17px !important;
}

.receive-page .receive-back-btn {
    min-height: 34px !important;
    padding: 5px 9px !important;
    font-size: 16px !important;
}

.receive-page .receive-page-body {
    padding: 8px !important;
}

.receive-page .receive-info-line {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 5px 8px !important;
    margin-bottom: 7px !important;
    padding: 7px 8px !important;
    font-size: 16px !important;
}

.receive-page .receive-summary {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 6px !important;
    margin-bottom: 7px !important;
}

.receive-page .summary-card {
    min-width: 0 !important;
    padding: 7px 8px !important;
    border-radius: 8px !important;
}

.receive-page .summary-label {
    margin-bottom: 0 !important;
    font-size: 16px !important;
    white-space: normal;
}

.receive-page .summary-value {
    font-size: 16px !important;
}

/*
 * Tablet-size app viewport မှာ 3 fields တစ်တန်းတည်းထားမယ်။
 * Phone width မှာအောက်က media query က 1 column ပြောင်းပေးမယ်။
 */
.receive-page .receive-form-row {
    grid-template-columns:
        minmax(175px, .9fr)
        minmax(190px, 1fr)
        minmax(230px, 1.25fr) !important;
    gap: 8px !important;
    margin-bottom: 7px !important;
}

.receive-page label {
    margin-bottom: 4px !important;
    font-size: 16px !important;
}

.receive-page .form-control {
    height: 39px !important;
    min-height: 39px !important;
    font-size: 16px !important;
}

.receive-page textarea.form-control {
    height: 39px !important;
    min-height: 39px !important;
}

.receive-page .select2-container {
    width: 100% !important;
}

.receive-page .select2-container .select2-choice,
.receive-page .select2-container--default .select2-selection--single {
    height: 39px !important;
    min-height: 39px !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    background: #fff !important;
}

.receive-page .select2-container .select2-choice,
.receive-page .select2-container .select2-choice > .select2-chosen,
.receive-page .select2-container--default
.select2-selection--single
.select2-selection__rendered {
    line-height: 37px !important;
}

.receive-page .receive-section-title {
    margin: 2px 0 7px !important;
    font-size: 16px !important;
}

.receive-page .fill-remaining-btn {
    min-height: 42px !important;
    padding: 8px 16px !important;
    font-size: 16px !important;
}

.receive-page .receive-table-card {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
}

.receive-page .receive-table {
    width: 100% !important;
    min-width: 700px !important;
}

.receive-page .receive-table th,
.receive-page .receive-table td {
    padding: 9px 8px !important;
    font-size: 16px !important;
}

.receive-page .product-name {
    width: 20% !important;
    min-width: 0 !important;
}

.receive-page .product-code {
    margin-top: 2px !important;
    font-size: 16px !important;
}

.receive-page .receive-qty {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    height: 36px !important;
    min-height: 36px !important;
    margin: 0 !important;
    padding-right: 8px !important;
    padding-left: 8px !important;
    box-sizing: border-box !important;
}

.receive-page .receive-actions {
    gap: 7px !important;
    padding-top: 11px !important;
}

.receive-page .receive-actions .btn,
.receive-page .btn-receive-save {
    min-height: 39px !important;
    font-size: 16px !important;
}


/* =========================================================
   Real phone width
   ========================================================= */
@media (max-width: 767px) {
    .content {
        padding: 6px !important;
    }

    .receive-page .receive-page-header {
        padding: 7px 8px !important;
    }

    .receive-page .receive-page-title {
        font-size: 16px !important;
    }

    .receive-page .receive-page-body {
        padding: 8px !important;
    }

    .receive-page .receive-info-line {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 5px 7px !important;
    }

    .receive-page .receive-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 4px !important;
    }

    .receive-page .summary-card {
        padding: 8px 6px !important;
    }

    .receive-page .summary-label {
        font-size: 16px !important;
    }

    .receive-page .summary-value {
        font-size: 18px !important;
    }

    .receive-page .receive-form-row {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 6px !important;
    }

    .receive-page .receive-form-row .receive-note-field {
        grid-column: 1 / -1 !important;
    }

    .receive-page .receive-table {
        min-width: 700px !important;
    }

    .receive-page .receive-actions {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
    }

    .receive-page .fill-remaining-btn {
        width: 100%;
        min-width: 0;
    }
}

@media (max-width: 420px) {
    .receive-page .receive-back-btn {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }

    .receive-page .receive-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 4px !important;
    }

    .receive-page .summary-card {
        display: block;
        padding: 7px 6px !important;
    }

    .receive-page .summary-label {
        margin-bottom: 2px !important;
        font-size: 16px !important;
    }

    .receive-page .summary-value {
        font-size: 18px !important;
    }

    .receive-page .receive-info-line {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    .receive-page .receive-info-line span:nth-child(3) {
        grid-column: 1 / -1;
    }
}


/* =========================================================
   Receive Table - Clear Column Borders
   ========================================================= */
.receive-page .receive-table th,
.receive-page .receive-table td {
    border-right: 1px solid #dbe3ec !important;
}

.receive-page .receive-table th:first-child,
.receive-page .receive-table td:first-child {
    border-left: 1px solid #dbe3ec !important;
}

.receive-page .receive-table thead th {
    border-top: 1px solid #dbe3ec !important;
}

.receive-page .receive-table tbody td {
    border-bottom: 1px solid #e5eaf0 !important;
}

.receive-page .receive-table tbody tr:last-child td {
    border-bottom: 1px solid #dbe3ec !important;
}

</style>

<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<div class="receive-page kls-mobile-ui">

    <div class="receive-page-card">

        <div class="receive-page-header">
            <h2 class="receive-page-title">
                <i class="fa fa-truck"></i>
                ပစ္စည်းလက်ခံရန်
            </h2>

            <a href="<?= html_escape($back_url); ?>" class="receive-back-btn">
                <i class="fa fa-arrow-left"></i>
                နောက်သို့
            </a>
        </div>

        <?= form_open(
            $receive_post_url,
            [
                'id' => 'partialReceiveForm',
                'autocomplete' => 'off'
            ]
        ); ?>

        <input type="hidden" name="app" value="1">
        <input
            type="hidden"
            name="app_lang"
            value="<?= html_escape($app_language); ?>"
        >

        <input type="hidden" name="purchase_id" value="<?= $purchase_id; ?>">

        <div class="receive-page-body">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <div class="receive-info-line">
                <span class="receive-info-item">
                    <strong class="receive-info-label">ဘောင်ချာ ID:</strong>
                    <span class="receive-info-value"><?= $purchase_id; ?></span>
                </span>

                <span class="receive-info-item">
                    <strong class="receive-info-label">ပေးသွင်းသူ:</strong>
                    <span class="receive-info-value"><?= html_escape($purchase->supplier_name ?? '-'); ?></span>
                </span>

                <span class="receive-info-item">
                    <strong class="receive-info-label">မူလဝယ်ယူသည့်နေရာ:</strong>
                    <span class="receive-info-value"><?= html_escape($purchase->store_name ?? '-'); ?></span>
                </span>
            </div>

            <div class="receive-summary">

                <div class="summary-card">
                    <span class="summary-label">စုစုပေါင်း</span>
                    <strong class="summary-value">
                        <?= $this->tec->formatDecimal($total_ordered); ?>
                    </strong>
                </div>

                <div class="summary-card received">
                    <span class="summary-label">လက်ခံပြီး</span>
                    <strong class="summary-value">
                        <?= $this->tec->formatDecimal($total_received); ?>
                    </strong>
                </div>

                <div class="summary-card remaining">
                    <span class="summary-label">လက်ခံရန်ကျန်</span>
                    <strong class="summary-value">
                        <?= $this->tec->formatDecimal($total_remaining); ?>
                    </strong>
                </div>

            </div>

            <div class="receive-form-row">

                <div class="form-group">
                    <label for="received_at">လက်ခံသည့်ရက်စွဲ</label>
                    <input
                        type="datetime-local"
                        name="received_at"
                        id="received_at"
                        class="form-control"
                        value="<?= html_escape(
                            isset($_POST['received_at'])
                                ? $_POST['received_at']
                                : date('Y-m-d\TH:i')
                        ); ?>"
                        required="required"
                    >
                </div>

                <div class="form-group">
                    <label for="receive_store_id">
                        ပစ္စည်းလက်ခံသည့်နေရာ
                    </label>

                    <?= form_dropdown(
                        'receive_store_id',
                        $store_options,
                        $default_store_id,
                        'class="form-control select2" id="receive_store_id" style="width:100%" required="required"'
                    ); ?>
                </div>

                <div class="form-group receive-note-field">
                    <label for="receive_note">မှတ်ချက်</label>
                    <textarea
                        name="receive_note"
                        id="receive_note"
                        class="form-control"
                        placeholder="လိုအပ်ပါက မှတ်ချက်ရေးပါ"
                    ><?= html_escape($_POST['receive_note'] ?? ''); ?></textarea>
                </div>

            </div>

            

            <div class="receive-table-card">
                <table class="table receive-table">
                    <colgroup>
                        <col style="width:20%;">
                        <col style="width:15%;">
                        <col style="width:15%;">
                        <col style="width:15%;">
                        <col style="width:15%;">
                    </colgroup>

                    <thead>
                        <?php if ($total_remaining > 0): ?>
                            <tr class="fill-remaining-table-row">
                                <th colspan="5">
                                    <button
                                        type="button"
                                        class="btn btn-default btn-sm fill-remaining-btn"
                                        id="fillAllRemaining"
                                        title="လက်ခံရန်ကျန်ရှိသမျှကို ဖြည့်ရန်"
                                    >
                                        <i class="fa fa-check-square-o"></i>
                                        <span class="fill-remaining-btn-label">ကျန်အားလုံးဖြည့်ရန်</span>
                                    </button>
                                </th>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th>ပစ္စည်း</th>
                            <th class="text-right">ယခုလက်ခံ</th>
                            <th class="text-right">ဝယ်ထား</th>
                            <th class="text-right">လက်ခံပြီး</th>
                            <th class="text-right">ကျန်</th>
                            
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $ordered   = (float) ($item->primary_qty ?? 0);
                        $received  = (float) ($item->received_primary_qty ?? 0);
                        $remaining = max(0, $ordered - $received);
                        $unit_name = trim((string) ($item->primary_unit_name ?? ''));
                        $product_id = (int) ($item->product_id ?? 0);
                        $item_id    = (int) ($item->id ?? 0);
                        ?>

                        <tr>
                            <td class="product-name">
                                <?= html_escape($item->product_name ?? '-'); ?>

                                <span class="product-code">
                                    <?= html_escape($item->product_code ?? ''); ?>
                                </span>
                            </td>
                            
                            <td class="text-right receive-qty-cell<?= $remaining <= 0 ? ' received-action-cell' : ''; ?>">
                                <?php if ($remaining > 0): ?>

                                    <input
                                        type="text"
                                        name="receive_qty[<?= (int) $item->id; ?>]"
                                        class="form-control receive-qty"
                                        value=""
                                        inputmode="decimal"
                                        autocomplete="off"
                                        data-max="<?= html_escape(
                                            number_format($remaining, 2, '.', '')
                                        ); ?>"
                                        placeholder="0"
                                    >

                                <?php else: ?>

                                    <div class="received-item-actions">
                                        <button
                                            type="button"
                                            class="received-action-btn received-history-btn js-receive-history"
                                            data-purchase-id="<?= (int) $purchase_id; ?>"
                                            data-product-id="<?= $product_id; ?>"
                                            data-item-id="<?= $item_id; ?>"
                                            title="လက်ခံမှတ်တမ်းကြည့်ရန်"
                                            aria-label="လက်ခံမှတ်တမ်းကြည့်ရန်"
                                        >
                                            <i class="fa fa-history"></i>
                                            <span>မှတ်တမ်း</span>
                                        </button>
                                    </div>

                                <?php endif; ?>
                            </td>

                            <td class="text-right qty-text">
                                <?= $this->tec->formatDecimal($ordered); ?>
                                <?= html_escape($unit_name); ?>
                            </td>

                            <td class="text-right qty-text">
                                <?= $this->tec->formatDecimal($received); ?>
                                <?= html_escape($unit_name); ?>
                            </td>

                            <td class="text-right remaining-text">
                                <?= $this->tec->formatDecimal($remaining); ?>
                                <?= html_escape($unit_name); ?>
                            </td>

                            
                        </tr>

                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="receive-actions">

                <a href="<?= html_escape($back_url); ?>" class="btn btn-default">
                    <i class="fa fa-times"></i>
                    မလုပ်တော့ပါ
                </a>

                <?php if ($total_remaining > 0): ?>
                    <button
                        type="submit"
                        class="btn btn-success btn-receive-save"
                        id="savePartialReceive"
                    >
                        <i class="fa fa-check"></i>
                        ပစ္စည်းလက်ခံမည်
                    </button>
                <?php endif; ?>

            </div>

        </div>

        <?= form_close(); ?>

    </div>

</div>

<div
    id="receiveHistoryActionDialog"
    class="receive-history-action-dialog"
    aria-hidden="true"
>
    <div
        class="receive-history-dialog-backdrop"
        data-receive-dialog-close="1"
    ></div>

    <div
        class="receive-history-dialog-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="receiveHistoryDialogTitle"
    >
        <div class="receive-history-dialog-header">
            <h4
                class="receive-history-dialog-title"
                id="receiveHistoryDialogTitle"
            >
                လက်ခံမှတ်တမ်း
            </h4>

            <button
                type="button"
                class="receive-history-dialog-close"
                data-receive-dialog-close="1"
                title="ပိတ်ရန်"
                aria-label="ပိတ်ရန်"
            >
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="receive-history-dialog-body">
            

            <div
                class="receive-history-dialog-field"
                id="receiveHistoryEditField"
            >
                <label for="receiveHistoryEditQuantity">
                    လက်ခံအရေအတွက်အသစ်
                </label>

                <input
                    type="text"
                    id="receiveHistoryEditQuantity"
                    class="receive-history-dialog-input"
                    inputmode="decimal"
                    autocomplete="off"
                >
            </div>

            <div
                class="receive-history-dialog-error"
                id="receiveHistoryDialogError"
            ></div>
        </div>

        <div class="receive-history-dialog-footer">
            <button
                type="button"
                class="receive-history-dialog-btn"
                data-receive-dialog-close="1"
            >
                မလုပ်တော့ပါ
            </button>

            <button
                type="button"
                class="receive-history-dialog-btn receive-history-dialog-confirm"
                id="confirmReceiveHistoryAction"
            >
                ပြင်မည်
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
(function ($) {

    function cleanQty(value) {
        value = String(value || '').replace(/[^0-9.]/g, '');

        var firstDot = value.indexOf('.');

        if (firstDot !== -1) {
            value =
                value.substring(0, firstDot + 1) +
                value.substring(firstDot + 1).replace(/\./g, '');
        }

        if (value.indexOf('.') !== -1) {
            var parts = value.split('.');
            parts[1] = (parts[1] || '').substring(0, 4);
            value = parts[0] + '.' + parts[1];
        }

        return value;
    }

    var receiveHistoryContext = null;
    var receiveHistoryChanged = false;
    var receiveHistoryDialogMode = '';
    var receiveHistoryActionButton = null;
    var receiveHistoryCsrfName =
        <?= json_encode($this->security->get_csrf_token_name()); ?>;
    var receiveHistoryCsrfHash =
        <?= json_encode($this->security->get_csrf_hash()); ?>;

    function receiveHistoryPostData(extra) {
        extra = extra || {};
        extra[receiveHistoryCsrfName] = receiveHistoryCsrfHash;

        return extra;
    }

    function loadReceiveHistory(showModal) {
        if (!receiveHistoryContext) {
            return null;
        }

        $('#receiveHistoryModal .history-action')
            .prop('disabled', true);

        return $.ajax({
            url: <?= json_encode(site_url('purchases/receive_history')); ?>,
            type: 'POST',
            data: receiveHistoryPostData({
                purchase_id: receiveHistoryContext.purchase_id,
                product_id: receiveHistoryContext.product_id,
                item_id: receiveHistoryContext.item_id
            }),
            dataType: 'html'
        }).done(function (html) {
            if (showModal) {
                $('#receiveHistoryModal').remove();

                $('<div ' +
                    'id="receiveHistoryModal" ' +
                    'class="modal fade" ' +
                    'tabindex="-1" ' +
                    'role="dialog"></div>')
                    .html(html)
                    .appendTo('body')
                    .modal('show');
            } else {
                $('#receiveHistoryModal').html(html);
            }
        }).fail(function () {
            showReceiveHistoryNotice(
                'လက်ခံမှတ်တမ်းကို ဖွင့်၍မရပါ။ ' +
                'ထပ်မံကြိုးစားပါ။'
            );
        });
    }

    function setReceiveHistoryDialogError(message) {
        var $error = $('#receiveHistoryDialogError');

        if (message) {
            $error
                .text(message)
                .addClass('is-visible');
        } else {
            $error
                .text('')
                .removeClass('is-visible');
        }
    }

    function openReceiveHistoryActionDialog(mode, $button, message) {
        var $dialog = $('#receiveHistoryActionDialog');
        var $title = $('#receiveHistoryDialogTitle');
        var $message = $('#receiveHistoryDialogMessage');
        var $field = $('#receiveHistoryEditField');
        var $quantity = $('#receiveHistoryEditQuantity');
        var $confirm = $('#confirmReceiveHistoryAction');
        var $cancel = $dialog.find(
            '.receive-history-dialog-footer ' +
            '[data-receive-dialog-close]'
        );

        receiveHistoryDialogMode = mode;
        receiveHistoryActionButton = $button || null;

        /*
         * Bootstrap modal က အပြင်ဘက် element တွေရဲ့ focus ကို
         * history modal ဆီ ပြန်ဆွဲယူသဖြင့် custom edit input မှာ
         * စာရိုက်မရနိုင်ပါ။ History modal ဖွင့်ထားချိန်တွင် dialog ကို
         * ၎င်း modal အတွင်းသို့ ရွှေ့ထားပါသည်။
         */
        if ($('#receiveHistoryModal').length) {
            $dialog.appendTo('#receiveHistoryModal');
        } else {
            $dialog.appendTo('body');
        }

        setReceiveHistoryDialogError('');
        $confirm
            .prop('disabled', false)
            .removeClass('is-danger');
        $cancel.show();

        if (mode === 'edit') {
            $title.text('လက်ခံအရေအတွက် ပြင်ရန်');
            $message.text('လက်ခံထားသည့် အရေအတွက်အသစ်ကို ထည့်ပါ။');
            $field.show();
            $quantity.val($button.data('quantity'));
            $confirm.text('ပြင်မည်');
        } else if (mode === 'delete') {
            $title.text('လက်ခံမှတ်တမ်း ဖျက်ရန်');
            $message.text(
                'ဤလက်ခံမှတ်တမ်းကို ဖျက်မည်မှာ သေချာပါသလား။'
            );
            $field.hide();
            $quantity.val('');
            $confirm
                .addClass('is-danger')
                .text('ဖျက်မည်');
        } else {
            $title.text('အသိပေးချက်');
            $message.text(message || 'လုပ်ဆောင်၍မရပါ။');
            $field.hide();
            $quantity.val('');
            $cancel.hide();
            $confirm.text('ပိတ်မည်');
        }

        $dialog
            .addClass('is-open')
            .attr('aria-hidden', 'false');
        $('body').addClass('receive-history-dialog-open');

        if (mode === 'edit') {
            window.setTimeout(function () {
                $quantity.trigger('focus').trigger('select');
            }, 80);
        } else {
            window.setTimeout(function () {
                $confirm.trigger('focus');
            }, 80);
        }
    }

    function closeReceiveHistoryActionDialog(forceClose) {
        var $confirm = $('#confirmReceiveHistoryAction');
        var $dialog = $('#receiveHistoryActionDialog');

        if (!forceClose && $confirm.prop('disabled')) {
            return;
        }

        $dialog
            .removeClass('is-open')
            .attr('aria-hidden', 'true');
        $('body').removeClass('receive-history-dialog-open');

        /* History modal content refresh/remove လုပ်သည့်အခါ မပျောက်စေရန် */
        $dialog.appendTo('body');

        receiveHistoryDialogMode = '';
        receiveHistoryActionButton = null;
        setReceiveHistoryDialogError('');
    }

    function showReceiveHistoryNotice(message) {
        openReceiveHistoryActionDialog(
            'notice',
            null,
            message
        );
    }

    $(document)
        .off('click.receivePageHistory', '.js-receive-history')
        .on(
            'click.receivePageHistory',
            '.js-receive-history',
            function () {
                var $button = $(this);

                receiveHistoryContext = {
                    purchase_id: $button.data('purchase-id'),
                    product_id: $button.data('product-id'),
                    item_id: $button.data('item-id')
                };

                $button.prop('disabled', true);

                var request = loadReceiveHistory(true);

                if (request) {
                    request.always(function () {
                        $button.prop('disabled', false);
                    });
                } else {
                    $button.prop('disabled', false);
                }
            }
        );

    $(document)
        .off(
            'click.receivePageHistoryDelete',
            '.js-delete-receive-history'
        )
        .on(
            'click.receivePageHistoryDelete',
            '.js-delete-receive-history',
            function () {
                openReceiveHistoryActionDialog(
                    'delete',
                    $(this)
                );
            }
        );

    $(document)
        .off(
            'click.receivePageHistoryEdit',
            '.js-edit-receive-history'
        )
        .on(
            'click.receivePageHistoryEdit',
            '.js-edit-receive-history',
            function () {
                openReceiveHistoryActionDialog(
                    'edit',
                    $(this)
                );
            }
        );

    $(document)
        .off(
            'click.receiveHistoryDialogClose',
            '[data-receive-dialog-close]'
        )
        .on(
            'click.receiveHistoryDialogClose',
            '[data-receive-dialog-close]',
            function () {
                closeReceiveHistoryActionDialog(false);
            }
        );

    $(document)
        .off(
            'click.receiveHistoryDialogConfirm',
            '#confirmReceiveHistoryAction'
        )
        .on(
            'click.receiveHistoryDialogConfirm',
            '#confirmReceiveHistoryAction',
            function () {
                var $confirm = $(this);

                if (receiveHistoryDialogMode === 'notice') {
                    closeReceiveHistoryActionDialog(true);
                    return;
                }

                if (!receiveHistoryActionButton) {
                    closeReceiveHistoryActionDialog(true);
                    return;
                }

                var $sourceButton = receiveHistoryActionButton;
                var url = '';
                var data = {
                    receipt_id: $sourceButton.data('id')
                };

                if (receiveHistoryDialogMode === 'edit') {
                    var quantity = $.trim(
                        $('#receiveHistoryEditQuantity').val()
                    );

                    if (
                        !quantity ||
                        isNaN(quantity) ||
                        Number(quantity) <= 0
                    ) {
                        setReceiveHistoryDialogError(
                            '0 ထက်ကြီးသော အရေအတွက်ထည့်ပါ။'
                        );
                        $('#receiveHistoryEditQuantity')
                            .trigger('focus')
                            .trigger('select');
                        return;
                    }

                    url = <?= json_encode(site_url('purchases/update_receive_history')); ?>;
                    data.quantity = quantity;
                } else {
                    url = <?= json_encode(site_url('purchases/delete_receive_history')); ?>;
                }

                setReceiveHistoryDialogError('');
                $confirm.prop('disabled', true);
                $sourceButton.prop('disabled', true);

                $.post(
                    url,
                    receiveHistoryPostData(data),
                    null,
                    'json'
                ).done(function (response) {
                    if (response && response.status) {
                        receiveHistoryChanged = true;
                        closeReceiveHistoryActionDialog(true);

                        /*
                         * လက်ခံမှတ်တမ်းပြင်/ဖျက်ပြီးသည်နှင့် summary၊
                         * လက်ခံပြီးနှင့် ကျန်အရေအတွက်များကို server မှ
                         * အမှန်ပြန်တွက်ထားသည့်တန်ဖိုးဖြင့် ချက်ချင်းပြရန်။
                         */
                        window.location.reload();
                    } else {
                        setReceiveHistoryDialogError(
                            (response && response.message) ||
                            (
                                receiveHistoryDialogMode === 'edit'
                                    ? 'ပြင်ဆင်၍မရပါ။'
                                    : 'ဖျက်၍မရပါ။'
                            )
                        );
                    }
                }).fail(function () {
                    setReceiveHistoryDialogError(
                        receiveHistoryDialogMode === 'edit'
                            ? 'ပြင်ဆင်၍မရပါ။ ထပ်မံကြိုးစားပါ။'
                            : 'ဖျက်၍မရပါ။ ထပ်မံကြိုးစားပါ။'
                    );
                }).always(function () {
                    $confirm.prop('disabled', false);
                    $sourceButton.prop('disabled', false);
                });
            }
        );

    $(document)
        .off('keydown.receiveHistoryDialog')
        .on('keydown.receiveHistoryDialog', function (event) {
            if (
                event.key === 'Escape' &&
                $('#receiveHistoryActionDialog').hasClass('is-open')
            ) {
                closeReceiveHistoryActionDialog(false);
            }
        });

    $(document)
        .off(
            'hidden.bs.modal.receivePageHistory',
            '#receiveHistoryModal'
        )
        .on(
            'hidden.bs.modal.receivePageHistory',
            '#receiveHistoryModal',
            function () {
                closeReceiveHistoryActionDialog(true);
                $(this).remove();

                if (receiveHistoryChanged) {
                    window.location.reload();
                }
            }
        );


    $(document)
        .off('input.partialReceiveQty', '.receive-qty')
        .on('input.partialReceiveQty', '.receive-qty', function () {

            var $input = $(this);
            var value  = cleanQty($input.val());
            var max    = parseFloat($input.attr('data-max') || '0');
            var numeric = parseFloat(value || '0');

            if (max >= 0 && numeric > max) {
                value = String(max);
            }

            $input
                .val(value)
                .removeClass('is-auto-filled');

            $('#fillAllRemaining')
                .removeClass('is-filled')
                .find('.fill-remaining-btn-label')
                .text('ကျန်အားလုံးဖြည့်ရန်');
        });


    $('#fillAllRemaining')
        .off('click.partialReceive')
        .on('click.partialReceive', function () {

            var $button = $(this);

            $('.receive-qty').each(function () {
                $(this)
                    .val($(this).attr('data-max') || '0')
                    .addClass('is-auto-filled');
            });

            $button
                .addClass('is-filled')
                .find('.fill-remaining-btn-label')
                .text('ကျန်အားလုံးဖြည့်ပြီး');
        });


    $('#partialReceiveForm')
        .off('submit.partialReceive')
        .on('submit.partialReceive', function () {

            var $form = $(this);
            var hasQty = false;
            var invalid = false;

            if (!$('#receive_store_id').val()) {
                showReceiveHistoryNotice(
                    'ပစ္စည်းလက်ခံသည့်နေရာ ရွေးပါ။'
                );
                return false;
            }

            $form.find('.receive-qty').each(function () {

                var qty = parseFloat($(this).val() || '0');
                var max = parseFloat($(this).attr('data-max') || '0');

                if (qty > 0) {
                    hasQty = true;
                }

                if (
                    qty < 0 ||
                    qty > max + 0.000001
                ) {
                    invalid = true;
                }
            });

            if (invalid) {
                showReceiveHistoryNotice(
                    'လက်ခံမည့်အရေအတွက်သည် ' +
                    'ကျန်ရှိသည့်အရေအတွက်ထက် မကျော်ရပါ။'
                );
                return false;
            }

            if (!hasQty) {
                showReceiveHistoryNotice(
                    'လက်ခံမည့် ပစ္စည်းအရေအတွက် တစ်ခုခု ထည့်ပါ။'
                );
                return false;
            }

            var $button = $('#savePartialReceive');

            if ($button.prop('disabled')) {
                return false;
            }

            $button
                .prop('disabled', true)
                .html(
                    '<i class="fa fa-spinner fa-spin"></i> ' +
                    'လက်ခံနေသည်...'
                );

            return true;
        });

})(jQuery);
</script>
