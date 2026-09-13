<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>
<?php
/*
|--------------------------------------------------------------------------
| KLSPOS - View Received Products Modal
|--------------------------------------------------------------------------
| Shown ONLY for purchases where received = 1 (အကုန်လက်ခံပြီး).
| Loaded the same way as purchases/payments — via an ajax modal link.
|
| EXPECTED VARIABLES FROM CONTROLLER (adjust names to match your schema):
|
|   $purchase        object  -> id, date, supplier_name (or name), status
|   $received_items  array   -> list of objects, each with:
|                                   product_code
|                                   product_name
|                                   quantity        (received qty, numeric)
|                                   unit_name        (e.g. 'ဖာ', 'Box')
|                                   received_date    (optional, per-line date)
|
| Suggested controller method (adapt to your Purchases_model):
|
|   public function view_received($purchase_id = 0)
|   {
|       $purchase = $this->purchases_model->get_purchase($purchase_id);
|
|       // Only allow this view when fully received
|       if (!$purchase || (int) $purchase->received !== 1) {
|           show_404();
|       }
|
|       $data['purchase']       = $purchase;
|       $data['received_items'] = $this->purchases_model
|           ->get_received_items($purchase_id);
|
|       $this->load->view('purchases/view_received', $data);
|   }
*/


// echo '<pre>',print_r($items),'</pre>';


$purchase_id   = !empty($purchase) ? (int) $purchase->id : 0;
$purchase_date = !empty($purchase) ? $purchase->date : '';
$supplier_name = !empty($purchase)
    ? ($purchase->supplier_name ?? $purchase->name ?? '')
    : '';
$purchase_id = !empty($purchase) ? ($purchase->id ?? '') : '';
$store = !empty($purchase) ? ($purchase->store_name ?? $purchase->store ?? '') : '';
$total_received_qty = 0;

if (!empty($items)) {
    foreach ($items as $item) {
        $total_received_qty += (float) ($item->quantity ?? 0);
    }
}
?>

<style>
/* =========================================================
   KLSPOS ERP - View Received Products Modal
   (Same visual language as the View Payments modal)
========================================================= */

.received-view-modal {
    width: 920px;
    max-width: calc(100% - 30px);
    margin: 25px auto;
}

.received-view-modal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 20px 55px rgba(15, 23, 42, 0.25);
}

/* =========================================================
   Modal Header
========================================================= */

.received-view-modal .received-modal-header {
    position: relative;
    min-height: 64px;
    padding: 17px 118px 17px 22px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.received-view-modal .modal-title {
    margin: 0;
    color: #1e293b;
    font-size: 18px;
    font-weight: 700;
    line-height: 30px;
}

.received-view-modal .received-modal-actions {
    position: absolute;
    top: 15px;
    right: 16px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.received-view-modal .received-modal-action {
    width: 34px;
    height: 34px;
    padding: 0;
    border: 1px solid #dbe3ec;
    border-radius: 8px;
    background: #f8fafc;
    color: #64748b;
    font-size: 15px;
    line-height: 32px;
    text-align: center;
    opacity: 1;
    transition: all 0.18s ease;
}

.received-view-modal .received-modal-action:hover,
.received-view-modal .received-modal-action:focus {
    border-color: #94a3b8;
    background: #f1f5f9;
    color: #1e293b;
    outline: none;
}

.received-view-modal .received-modal-close:hover,
.received-view-modal .received-modal-close:focus {
    border-color: #fecaca;
    background: #fef2f2;
    color: #dc2626;
}

/* =========================================================
   Modal Body
========================================================= */

.received-view-modal .modal-body {
    padding: 20px 22px 24px;
    background: #f8fafc;
}

/* =========================================================
   Summary
========================================================= */

.received-view-modal .received-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 12px;
}

.received-view-modal .received-summary-item {
    padding: 14px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
}

.received-view-modal .received-summary-label {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.6;
}

.received-view-modal .received-summary-value {
    display: block;
    color: #1e293b;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.5;
    white-space: nowrap;
}

.received-view-modal .received-summary-item.is-status {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.received-view-modal .received-summary-item.is-status .received-summary-value {
    color: #15803d;
}

/* =========================================================
   Table
========================================================= */

.received-view-modal .received-table-card {
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    background: #ffffff;
}

.received-view-modal .received-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.received-view-modal .received-table {
    width: 100%;
    min-width: 620px;
    margin: 0;
    border: 0;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

.received-view-modal .received-table thead th {
    padding: 12px 11px;
    border-top: 0 !important;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #dbe3ec;
    border-left: 0 !important;
    background: #f1f5f9;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.7;
    vertical-align: middle;
    white-space: nowrap;
}

.received-view-modal .received-table thead th:last-child {
    border-right: 0;
}

.received-view-modal .received-table tbody td {
    padding: 12px 11px;
    border-top: 0 !important;
    border-right: 1px solid #edf2f7;
    border-bottom: 1px solid #edf2f7;
    border-left: 0 !important;
    background: #ffffff;
    color: #334155;
    font-size: 14px;
    line-height: 1.7;
    vertical-align: middle;
    overflow-wrap: anywhere;
}

.received-view-modal .received-table tbody td:last-child {
    border-right: 0;
}

.received-view-modal .received-table tbody tr:last-child td {
    border-bottom: 0;
}

.received-view-modal .received-table tbody tr:hover td {
    background: #f8fafc;
}

.received-view-modal .received-code-cell {
    width: 20%;
    color: #475569;
    font-weight: 600;
}

.received-view-modal .received-name-cell {
    width: 40%;
    color: #1e293b;
    font-weight: 600;
}

.received-view-modal .received-qty-cell .bought-qty-cell {
    width: 20%;
    color: #15803d;
    font-weight: 800;
    text-align: right;
    white-space: nowrap;
}

.received-view-modal .received-date-cell {
    width: 20%;
    color: #334155;
    text-align: right;
    white-space: nowrap;
}

.received-view-modal .received-table tfoot th {
    padding: 12px 11px;
    border-top: 1px solid #dbe3ec !important;
    background: #f8fafc;
    color: #1e293b;
    font-size: 14px;
    font-weight: 800;
}

/* =========================================================
   Empty State
========================================================= */

.received-view-modal .received-empty-cell {
    padding: 35px 20px !important;
    color: #64748b !important;
    text-align: center !important;
}

.received-view-modal .received-empty-icon {
    display: block;
    margin-bottom: 9px;
    color: #94a3b8;
    font-size: 30px;
}

/* =========================================================
   Tablet / Mobile Card Mode
========================================================= */

@media (max-width: 991px) {

    .received-view-modal {
        width: calc(100% - 30px);
        max-width: 760px;
        height: auto;
        max-height: calc(100vh - 30px);
        max-height: calc(100dvh - 30px);
        margin: 15px auto;
    }

    .received-view-modal .modal-content {
        display: flex;
        flex-direction: column;
        height: auto;
        min-height: 0;
        max-height: calc(100vh - 30px);
        max-height: calc(100dvh - 30px);
        border-radius: 13px;
    }

    .received-view-modal .received-modal-header {
        flex: 0 0 auto;
        min-height: 58px;
        padding: 13px 105px 13px 16px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        z-index: 20;
    }

    .received-view-modal .modal-title {
        font-size: 16px;
        line-height: 32px;
    }

    .received-view-modal .received-modal-actions {
        top: 12px;
        right: 12px;
    }

    .received-view-modal .received-modal-action {
        width: 34px;
        height: 34px;
        line-height: 32px;
    }

    .received-view-modal .modal-body {
        flex: 0 1 auto;
        min-height: 0;
        max-height: calc(100vh - 95px);
        max-height: calc(100dvh - 95px);
        padding: 14px;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .received-view-modal .received-summary-grid {
        grid-template-columns: 1fr;
        gap: 8px;
        margin-bottom: 10px;
    }

    .received-view-modal .received-summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
    }

    .received-view-modal .received-summary-label {
        margin-bottom: 0;
    }

    .received-view-modal .received-table-card {
        overflow: hidden !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
    }

    .received-view-modal .received-table-wrap {
        overflow-x: auto !important;
    }

    .received-view-modal .received-table {
        min-width: 560px !important;
    }

    .received-view-modal .received-table thead th,
    .received-view-modal .received-table tbody td {
        padding: 10px 8px !important;
        font-size: 13px !important;
    }
}

@media (max-width: 575px) {
    .received-view-modal .received-table {
        min-width: 520px !important;
    }

    .received-view-modal .received-table thead th,
    .received-view-modal .received-table tbody td {
        padding: 9px 6px !important;
        font-size: 12px !important;
    }
}

/* =========================================================
   Myanmar Font
========================================================= */

.received-view-modal,
.received-view-modal table,
.received-view-modal button,
.received-view-modal a {
    font-family:
        "Pyidaungsu",
        "Noto Sans Myanmar",
        "Myanmar Text",
        Arial,
        sans-serif;
}

/* =========================================================
   Print
========================================================= */

@media print {

    body * {
        visibility: hidden !important;
    }

    .received-view-modal,
    .received-view-modal * {
        visibility: visible !important;
    }

    .received-view-modal {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: none !important;
        margin: 0 !important;
    }

    .received-view-modal .modal-content {
        height: auto !important;
        max-height: none !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .received-view-modal .received-modal-actions {
        display: none !important;
    }

    .received-view-modal .modal-body {
        height: auto !important;
        max-height: none !important;
        padding: 15px !important;
        overflow: visible !important;
        background: #ffffff !important;
    }

    .modal-backdrop {
        display: none !important;
    }
}

/* =========================================================
   Purchase Item Actions
   ========================================================= */

.received-item-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: wrap;
}


/* Base button */

.received-action-btn {
    appearance: none;
    -webkit-appearance: none;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 32px;
    padding: 6px 10px;

    border: 1px solid transparent;
    border-radius: 6px;

    background: #fff;

    font-family: inherit;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.2;

    cursor: pointer;

    transition:
        background-color .15s ease,
        border-color .15s ease,
        color .15s ease,
        box-shadow .15s ease,
        transform .1s ease;
}


/* Icon */

.received-action-btn i {
    margin-right: 5px;
    font-size: 13px;
}


/* Click effect */

.received-action-btn:active {
    transform: translateY(1px);
}


/* Focus */

.received-action-btn:focus {
    outline: none;
}

.received-action-btn:focus-visible {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
}


/* =========================================================
   History button
   ========================================================= */

.received-history-btn {
    color: #2563eb;
    border-color: #bfdbfe;
    background: #eff6ff;
}

.received-history-btn:hover {
    color: #1d4ed8;
    border-color: #93c5fd;
    background: #dbeafe;
}


/* =========================================================
   Delete button
   ========================================================= */

.received-delete-btn {
    color: #dc2626;
    border-color: #fecaca;
    background: #fff5f5;
}

.received-delete-btn:hover {
    color: #b91c1c;
    border-color: #fca5a5;
    background: #fee2e2;
}


/* =========================================================
   Small screen
   ========================================================= */

@media (max-width: 768px) {

    .received-item-actions {
        gap: 4px;
    }

    .received-action-btn {
        min-height: 30px;
        padding: 5px 8px;
        font-size: 11px;
    }

    .received-action-btn i {
        margin-right: 0;
    }

    .received-action-btn span {
        display: none;
    }
}
/* =========================================================
   Action Column
========================================================= */

.received-view-modal .received-action-cell {
    width: 160px;
    text-align: center;
    white-space: nowrap;
}

.received-view-modal .received-action-cell {
    color: #334155;
}


/* =========================================================
   Purchase Item Actions
========================================================= */

.received-view-modal .received-item-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}


/* =========================================================
   Action Button
========================================================= */

.received-view-modal .received-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 32px;
    padding: 5px 9px;

    border: 1px solid transparent;
    border-radius: 7px;

    background: #fff;

    font-family: inherit;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.2;

    cursor: pointer;

    transition:
        background-color .15s ease,
        border-color .15s ease,
        color .15s ease,
        box-shadow .15s ease,
        transform .1s ease;
}

.received-view-modal .received-action-btn i {
    margin-right: 5px;
    font-size: 13px;
}

.received-view-modal .received-action-btn:active {
    transform: translateY(1px);
}

.received-view-modal .received-action-btn:focus {
    outline: none;
}


/* =========================================================
   History
========================================================= */

.received-view-modal .received-history-btn {
    color: #2563eb;
    border-color: #bfdbfe;
    background: #eff6ff;
}

.received-view-modal .received-history-btn:hover {
    color: #1d4ed8;
    border-color: #93c5fd;
    background: #dbeafe;
}


/* =========================================================
   Delete
========================================================= */

.received-view-modal .received-delete-btn {
    color: #dc2626;
    border-color: #fecaca;
    background: #fff5f5;
}

.received-view-modal .received-delete-btn:hover {
    color: #b91c1c;
    border-color: #fca5a5;
    background: #fee2e2;
}


/* =========================================================
   Quantity Unit
========================================================= */

.received-view-modal .qty-unit {
    margin-left: 3px;
    color: #64748b;
    font-size: 11px;
    font-weight: 500;
}


/* =========================================================
   Mobile
========================================================= */

@media (max-width: 768px) {

    .received-view-modal .received-action-cell {
        width: 90px;
    }

    .received-view-modal .received-item-actions {
        gap: 4px;
    }

    .received-view-modal .received-action-btn {
        width: 32px;
        min-width: 32px;
        height: 32px;
        padding: 0;
    }

    .received-view-modal .received-action-btn i {
        margin-right: 0;
    }

    .received-view-modal .received-action-btn span {
        display: none;
    }

}
</style>

<div class="modal-dialog modal-lg received-view-modal" role="document">

    <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header received-modal-header">

            <h4 class="modal-title" id="viewReceivedModalLabel">
                အကုန်လက်ခံပြီး - ပစ္စည်းစာရင်း
            </h4>

            <div class="received-modal-actions">

                <!-- Print -->
                <button
                    type="button"
                    class="received-modal-action"
                    onclick="window.print();"
                    title="<?= html_escape(lang('print')); ?>"
                    aria-label="<?= html_escape(lang('print')); ?>"
                >
                    <i class="fa fa-print"></i>
                </button>

                <!-- Close -->
                <button
                    type="button"
                    class="received-modal-action received-modal-close"
                    data-dismiss="modal"
                    title="<?= html_escape(lang('close')); ?>"
                    aria-label="<?= html_escape(lang('close')); ?>"
                >
                    <i class="fa fa-times"></i>
                </button>

            </div>

        </div>

        <!-- Modal Body -->
        <div class="modal-body">

            <!-- Summary -->
            <div class="received-summary-grid">

                <div class="received-summary-item">
                    <span class="received-summary-label">
                        ဘောက်ချာ ID
                    </span>
                    <span class="received-summary-value">
                        <?= html_escape(
                            $purchase_id
                                ? $purchase_id
                                : '-'
                        ); ?>
                    </span>
                </div>

                <div class="received-summary-item">
                    <span class="received-summary-label">
                        ပစ္စည်းလက်ခံသည့်နေရာ
                    </span>
                    <span class="received-summary-value">
                        <?= html_escape(
                            $store
                                ? $store
                                : '-'
                        ); ?>
                    </span>
                </div>

                <div class="received-summary-item">
                    <span class="received-summary-label">
                        <?= html_escape(lang('date')); ?>
                    </span>
                    <span class="received-summary-value">
                        <?= html_escape(
                            $purchase_date
                                ? $purchase_date
                                : '-'
                        ); ?>
                    </span>
                </div>

                <div class="received-summary-item">
                    <span class="received-summary-label">
                        <?= html_escape(lang('supplier')); ?>
                    </span>
                    <span class="received-summary-value">
                        <?= html_escape($supplier_name ?: '-'); ?>
                    </span>
                </div>

                <div class="received-summary-item is-status">
                    <span class="received-summary-label">
                        <?= html_escape(lang('status')); ?>
                    </span>
                    <span class="received-summary-value">
                        အကုန်လက်ခံပြီး
                    </span>
                </div>

            </div>

            <!-- Received Products -->
            <div class="received-table-card">

                <div class="received-table-wrap">

                    <table
                        class="received-table"
                        cellpadding="0"
                        cellspacing="0"
                        border="0"
                    >

                        <thead>
                            <tr>

                                <th class="received-code-cell">
                                    <?= html_escape(lang('product_code')); ?>
                                </th>

                                <th class="received-name-cell">
                                    <?= html_escape(lang('product_name')); ?>
                                </th>

                                <th class="bought-qty-cell text-right">
                                    ဝယ်ထား
                                </th>

                                <th class="received-qty-cell text-right">
                                    လက်ခံပြီး
                                </th>

                                <th class="received-action-cell">
                                    Action
                                </th>

                            </tr>
                        </thead>

                        <tbody>

                        <?php if (!empty($items)): ?>

                            <?php foreach ($items as $item): ?>

                                <?php
                                $item_id = (int) ($item->id ?? 0);

                                $product_id = (int) ($item->product_id ?? 0);

                                $ordered = (float) (
                                    $item->primary_qty ?? 0
                                );

                                $received = (float) (
                                    $item->received_primary_qty ?? 0
                                );

                                $unit_name = trim(
                                    (string) (
                                        $item->primary_unit_name ?? ''
                                    )
                                );
                                ?>

                                <tr>

                                    <!-- Product Code -->
                                    <td class="received-code-cell">
                                        <?= html_escape(
                                            $item->product_code ?? ''
                                        ); ?>
                                    </td>

                                    <!-- Product -->
                                    <td class="received-name-cell">
                                        <?= html_escape(
                                            $item->product_name ?? ''
                                        ); ?>
                                    </td>

                                    <!-- Ordered -->
                                    <td class="bought-qty-cell text-right">

                                        <?= $this->tec->formatDecimal(
                                            $ordered
                                        ); ?>

                                        <?php if ($unit_name): ?>
                                            <span class="qty-unit">
                                                <?= html_escape($unit_name); ?>
                                            </span>
                                        <?php endif; ?>

                                    </td>

                                    <!-- Received -->
                                    <td class="received-qty-cell text-right">

                                        <span class="received-qty-value">
                                            <?= $this->tec->formatDecimal(
                                                $received
                                            ); ?>
                                        </span>

                                        <?php if ($unit_name): ?>
                                            <span class="qty-unit">
                                                <?= html_escape($unit_name); ?>
                                            </span>
                                        <?php endif; ?>

                                    </td>

                                    <!-- Actions -->
                                    <td class="received-action-cell">

                                        <div class="received-item-actions">

                                            <!-- History -->
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

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="received-empty-cell"
                                >

                                    <i class="fa fa-inbox received-empty-icon"></i>

                                    <?= html_escape(
                                        lang('no_data_available')
                                    ); ?>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>


                        

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
(function ($) {
    'use strict';

    var csrfName = <?= json_encode($this->security->get_csrf_token_name()); ?>;
    var csrfHash = <?= json_encode($this->security->get_csrf_hash()); ?>;

    function postData(extra) {
        extra[csrfName] = csrfHash;
        return extra;
    }

    var historyContext = null;

    function loadHistory(showModal) {
        if (!historyContext) {
            return;
        }

        var data = postData(historyContext);
        $('#receiveHistoryModal .history-action').prop('disabled', true);
            $.ajax({
                url: <?= json_encode(site_url('purchases/receive_history')); ?>,
                type: 'POST',
                data: data,
                dataType: 'html'
            }).done(function (html) {
                if (showModal) {
                    $('#receiveHistoryModal').remove();
                    $('<div id="receiveHistoryModal" class="modal fade" tabindex="-1" role="dialog"></div>')
                        .html(html).appendTo('body').modal('show');
                } else {
                    $('#receiveHistoryModal').html(html);
                }
            }).fail(function () {
                alert('လက်ခံမှတ်တမ်းကို ဖွင့်၍မရပါ။ ထပ်မံကြိုးစားပါ။');
            });
    }

    function updateReceivedProductsModal(receivedQty) {
        if (!historyContext) {
            return;
        }
        var selector = '.js-receive-history[data-purchase-id="' +
            historyContext.purchase_id + '"][data-product-id="' +
            historyContext.product_id + '"]';
        var $row = $(selector).first().closest('tr');
        $row.find('.received-qty-value').text(Number(receivedQty).toFixed(2));

        refreshReceivedTotal();
    }

    function refreshReceivedTotal() {
        var total = 0;
        $('.received-view-modal .received-qty-value').each(function () {
            total += parseFloat($(this).text()) || 0;
        });
        $('.received-view-modal .received-total-qty').text(total.toFixed(2));
    }

    $(document).off('click.receiveHistory', '.js-receive-history')
        .on('click.receiveHistory', '.js-receive-history', function () {
            var $button = $(this);
            historyContext = {
                purchase_id: $button.data('purchase-id'),
                product_id: $button.data('product-id')
            };
            $button.prop('disabled', true);
            loadHistory(true);
            window.setTimeout(function () { $button.prop('disabled', false); }, 500);
        });

    $(document).off('click.receiveHistoryDelete', '.js-delete-receive-history')
        .on('click.receiveHistoryDelete', '.js-delete-receive-history', function () {
            var $button = $(this);
            if (!window.confirm('ဤလက်ခံမှတ်တမ်းကို ဖျက်မည်မှာ သေချာပါသလား။')) {
                return;
            }
            $button.prop('disabled', true);
            $.post(<?= json_encode(site_url('purchases/delete_receive_history')); ?>,
                postData({receipt_id: $button.data('id')}), null, 'json')
                .done(function (response) {
                    if (response && response.status) {
                        updateReceivedProductsModal(response.received_qty);
                        loadHistory(false);
                    } else {
                        alert((response && response.message) || 'ဖျက်၍မရပါ။');
                    }
                }).fail(function () { alert('ဖျက်၍မရပါ။ ထပ်မံကြိုးစားပါ။'); })
                .always(function () { $button.prop('disabled', false); });
        });

    $(document).off('click.receiveHistoryEdit', '.js-edit-receive-history')
        .on('click.receiveHistoryEdit', '.js-edit-receive-history', function () {
            var $button = $(this);
            var quantity = window.prompt('လက်ခံအရေအတွက် အသစ်ထည့်ပါ။', $button.data('quantity'));
            if (quantity === null) {
                return;
            }
            quantity = $.trim(quantity);
            if (!quantity || isNaN(quantity) || Number(quantity) <= 0) {
                alert('0 ထက်ကြီးသော အရေအတွက်ထည့်ပါ။');
                return;
            }
            $button.prop('disabled', true);
            $.post(<?= json_encode(site_url('purchases/update_receive_history')); ?>,
                postData({receipt_id: $button.data('id'), quantity: quantity}), null, 'json')
                .done(function (response) {
                    if (response && response.status) {
                        updateReceivedProductsModal(response.received_qty);
                        loadHistory(false);
                    } else {
                        alert((response && response.message) || 'ပြင်ဆင်၍မရပါ။');
                    }
                }).fail(function () { alert('ပြင်ဆင်၍မရပါ။ ထပ်မံကြိုးစားပါ။'); })
                .always(function () { $button.prop('disabled', false); });
        });

    $(document).on('hidden.bs.modal', '#receiveHistoryModal', function () {
        $(this).remove();
    });

    $(document).off('click.deletePurchaseItem', '.js-delete-purchase-item')
        .on('click.deletePurchaseItem', '.js-delete-purchase-item', function () {
            var $button = $(this);
            var productName = $button.data('product-name') || 'ဤပစ္စည်း';
            if (!window.confirm(productName + ' ကို ဖျက်မည်မှာ သေချာပါသလား။ လက်ခံမှတ်တမ်းနှင့် stock ကိုလည်း ပြန်လည်ပြင်ဆင်ပါမည်။')) {
                return;
            }
            $button.prop('disabled', true);
            $.post(<?= json_encode(site_url('purchases/delete_purchase_item')); ?>,
                postData({item_id: $button.data('item-id')}), null, 'json')
                .done(function (response) {
                    if (!response || !response.status) {
                        alert((response && response.message) || 'ပစ္စည်းဖျက်၍မရပါ။');
                        return;
                    }
                    var purchaseId = $button.data('purchase-id');
                    var $row = $button.closest('tr');
                    if (historyContext && String(historyContext.purchase_id) === String(purchaseId) &&
                        String(historyContext.product_id) === String($row.find('.js-receive-history').data('product-id'))) {
                        $('#receiveHistoryModal').modal('hide');
                    }
                    $row.remove();
                    refreshReceivedTotal();
                }).fail(function () {
                    alert('ပစ္စည်းဖျက်၍မရပါ။ ထပ်မံကြိုးစားပါ။');
                }).always(function () {
                    $button.prop('disabled', false);
                });
        });
})(jQuery);
</script>
