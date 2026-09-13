<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>
<?php
/*
|--------------------------------------------------------------------------
| Sales Payment History - Purchase Style
|--------------------------------------------------------------------------
| Controller က $inv မပို့သေးသော version များအတွက် URL sale id ဖြင့်
| sale ကို fallback ရယူထားသည်။ Controller ကိုပြင်စရာမလိုဘဲ summary နှင့်
| Add Payment button အလုပ်လုပ်စေမည်။
*/
$sale_id = !empty($inv->id)
    ? (int) $inv->id
    : (int) $this->uri->segment(3);

if (empty($inv) && $sale_id > 0) {
    $inv = $this->db
        ->get_where('sales', ['id' => $sale_id])
        ->row();
}

$total_amount = !empty($inv)
    ? (float) ($inv->grand_total ?? ($inv->total ?? 0))
    : 0;

$paid_amount = !empty($inv)
    ? (float) ($inv->paid ?? 0)
    : 0;

$balance_amount = max(0, $total_amount - $paid_amount);
$customer_id = !empty($inv) ? (int) ($inv->customer_id ?? 0) : 0;

$payment_language = strtolower(trim((string) (
    $this->input->get('app_lang', true) ?:
    $this->input->post('app_lang', true) ?:
    ($Settings->selected_language ?? '')
)));

$is_myanmar_payment_language = in_array(
    $payment_language,
    ['myanmar', 'burmese', 'mm', 'my'],
    true
);
?>

<style>
/* =========================================================
   Sales Payment History - Purchase Style
   ========================================================= */
.sales-payment-view-modal {
    width: 980px;
    max-width: calc(100vw - 24px);
    margin: 24px auto;
    color: #334155;
}

.sales-payment-view-modal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 15px;
    background: #ffffff;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
}

.sales-payment-view-modal .sales-payment-modal-header {
    display: flex;
    min-height: 68px;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.sales-payment-view-modal .modal-title {
    margin: 0;
    color: #1e293b;
    font-size: 20px;
    font-weight: 900;
    line-height: 1.4;
}

.sales-payment-modal-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.sales-payment-modal-action {
    display: inline-flex;
    width: 42px;
    height: 42px;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid #dbe5ef;
    border-radius: 10px;
    background: #f8fafc;
    color: #52647a;
    font-size: 17px;
}

.sales-payment-modal-action:hover,
.sales-payment-modal-action:focus {
    border-color: #94a3b8;
    background: #f1f5f9;
    color: #0f172a;
}

.sales-payment-view-modal .modal-body {
    max-height: calc(100vh - 135px);
    overflow-y: auto;
    padding: 20px;
    background: #f8fafc;
}

.sales-payment-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}

.sales-payment-summary-item {
    min-width: 0;
    padding: 16px;
    border: 1px solid #dbe5ef;
    border-radius: 12px;
    background: #ffffff;
}

.sales-payment-summary-item.is-paid {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.sales-payment-summary-item.is-balance {
    border-color: #fecaca;
    background: #fff7f7;
}

.sales-payment-summary-label {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 13px;
    font-weight: 800;
}

.sales-payment-summary-value {
    display: block;
    overflow: hidden;
    color: #1e293b;
    font-size: 21px;
    font-weight: 900;
    line-height: 1.25;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sales-payment-summary-item.is-paid .sales-payment-summary-value {
    color: #15803d;
}

.sales-payment-summary-item.is-balance .sales-payment-summary-value {
    color: #dc2626;
}

.sales-payment-add-wrap {
    margin-bottom: 14px;
}

.sales-payment-add-button {
    display: flex;
    width: 100%;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 11px 16px;
    border: 1px solid #15803d;
    border-radius: 10px;
    background: #16a34a;
    color: #ffffff !important;
    font-size: 15px;
    font-weight: 900;
    text-decoration: none !important;
}

.sales-payment-add-button:hover,
.sales-payment-add-button:focus {
    background: #15803d;
}

.sales-payment-table-card {
    overflow: hidden;
    border: 1px solid #dbe5ef;
    border-radius: 12px;
    background: #ffffff;
}

.sales-payment-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.sales-payment-table {
    width: 100% !important;
    min-width: 690px;
    margin: 0 !important;
    table-layout: fixed;
    border-collapse: collapse;
}

.sales-payment-table thead th {
    padding: 12px 9px !important;
    border-right: 1px solid #dbe5ef !important;
    border-bottom: 1px solid #dbe5ef !important;
    background: #f1f5f9 !important;
    color: #334155;
    font-size: 13px;
    font-weight: 900;
    line-height: 1.35;
    text-align: center;
    vertical-align: middle !important;
    white-space: normal;
}

.sales-payment-table tbody td {
    padding: 11px 9px !important;
    border-right: 1px solid #e8eef5 !important;
    border-bottom: 1px solid #e8eef5 !important;
    background: #ffffff;
    color: #334155;
    vertical-align: middle !important;
}

.sales-payment-table thead th:last-child,
.sales-payment-table tbody td:last-child {
    border-right: 0 !important;
}

.sales-payment-table tbody tr:last-child td {
    border-bottom: 0 !important;
}

.sales-payment-col-date { width: 30%; }
.sales-payment-col-amount { width: 20%; }
.sales-payment-col-voucher { width: 18%; }
.sales-payment-col-edit,
.sales-payment-col-delete { width: 16%; }

.sales-payment-date-cell {
    font-size: 13px;
    font-weight: 700;
    line-height: 1.45;
}

.sales-payment-amount-cell {
    font-size: 15px;
    font-weight: 900;
    text-align: right;
    white-space: nowrap;
}

.sales-payment-voucher,
.sales-payment-row-button {
    display: inline-flex;
    min-height: 36px;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 900;
    line-height: 1;
    text-decoration: none !important;
    white-space: nowrap;
}

.sales-payment-voucher.has-file {
    border: 1px solid #99f6e4;
    background: #f0fdfa;
    color: #0f766e;
}

.sales-payment-voucher.no-file {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #94a3b8;
}

.sales-payment-edit-button {
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #2563eb;
}

.sales-payment-delete-button {
    border: 1px solid #fecaca;
    background: #fff1f2;
    color: #dc2626;
}

.sales-payment-empty-cell {
    padding: 32px 15px !important;
    color: #94a3b8 !important;
    font-size: 15px;
    font-weight: 800;
    text-align: center;
}

.sales-payment-empty-cell i {
    display: block;
    margin-bottom: 8px;
    font-size: 28px;
}

/* Voucher viewer and delete confirmation stay inside the app. */
.sales-payment-overlay {
    position: fixed;
    inset: 0;
    z-index: 13000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.sales-payment-overlay.is-open {
    display: flex;
}

.sales-payment-overlay-backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    background: rgba(15, 23, 42, .82);
}

.sales-payment-overlay-panel {
    position: relative;
    z-index: 1;
    width: 900px;
    max-width: 100%;
    max-height: calc(100vh - 24px);
    overflow: hidden;
    border-radius: 14px;
    background: #ffffff;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .45);
}

.sales-payment-overlay-header {
    display: flex;
    min-height: 58px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px 10px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.sales-payment-overlay-title {
    margin: 0;
    color: #1e293b;
    font-size: 17px;
    font-weight: 900;
}

.sales-payment-overlay-close {
    display: inline-flex;
    min-width: 84px;
    min-height: 40px;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 11px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fff1f2;
    color: #dc2626;
    font-weight: 900;
}

.sales-payment-voucher-body {
    display: flex;
    min-height: 220px;
    max-height: calc(100vh - 90px);
    align-items: center;
    justify-content: center;
    overflow: auto;
    padding: 10px;
    background: #111827;
}

.sales-payment-voucher-image,
.sales-payment-voucher-frame {
    display: none;
    max-width: 100%;
    border: 0;
    background: #ffffff;
}

.sales-payment-voucher-image {
    width: auto;
    height: auto;
    max-height: calc(100vh - 115px);
    object-fit: contain;
}

.sales-payment-voucher-frame {
    width: 100%;
    height: calc(100vh - 115px);
}

.sales-payment-voucher-error {
    display: none;
    padding: 18px;
    color: #ffffff;
    font-size: 16px;
    font-weight: 800;
    text-align: center;
}

.sales-payment-confirm-panel {
    width: 430px;
}

.sales-payment-confirm-body {
    padding: 22px 18px;
    color: #334155;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.7;
}

.sales-payment-confirm-reason-label {
    display: block;
    margin: 14px 0 6px;
    color: #1e293b;
    font-size: 13px;
    font-weight: 900;
}

.sales-payment-confirm-reason {
    display: block;
    width: 100%;
    min-height: 82px;
    resize: vertical;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    color: #1e293b;
    font-size: 14px;
    line-height: 1.55;
}

.sales-payment-confirm-reason:focus {
    border-color: #dc2626;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, .12);
}

.sales-payment-confirm-error {
    display: none;
    margin-top: 8px;
    padding: 8px 10px;
    border: 1px solid #fecaca;
    border-radius: 7px;
    background: #fff1f2;
    color: #b91c1c;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.5;
}

.sales-payment-confirm-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    padding: 12px 16px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

.sales-payment-confirm-actions button {
    min-height: 40px;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 900;
}

.sales-payment-confirm-cancel {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
}

.sales-payment-confirm-delete {
    border: 1px solid #dc2626;
    background: #dc2626;
    color: #ffffff;
}

@media (max-width: 767px) {
    .sales-payment-view-modal {
        width: calc(100vw - 12px);
        max-width: calc(100vw - 12px);
        margin: 6px auto;
    }

    .sales-payment-view-modal .sales-payment-modal-header {
        min-height: 56px;
        padding: 9px 11px;
    }

    .sales-payment-view-modal .modal-title {
        font-size: 16px;
    }

    .sales-payment-modal-action {
        width: 38px;
        height: 38px;
    }

    .sales-payment-view-modal .modal-body {
        max-height: calc(100vh - 70px);
        padding: 10px;
    }

    .sales-payment-summary-grid {
        gap: 6px;
        margin-bottom: 9px;
    }

    .sales-payment-summary-item {
        padding: 10px 8px;
        border-radius: 8px;
    }

    .sales-payment-summary-label {
        min-height: 34px;
        margin-bottom: 3px;
        font-size: 10px;
        line-height: 1.5;
    }

    .sales-payment-summary-value {
        font-size: 13px;
    }

    .sales-payment-add-button {
        min-height: 42px;
        padding: 9px 12px;
        font-size: 13px;
    }

    .sales-payment-table {
        min-width: 620px;
    }

    .sales-payment-table thead th,
    .sales-payment-table tbody td {
        padding: 9px 6px !important;
        font-size: 11px;
    }

    .sales-payment-row-button,
    .sales-payment-voucher {
        min-height: 34px;
        padding: 6px 8px;
        font-size: 10px;
    }

    .sales-payment-overlay {
        padding: 7px;
    }
}

.sales-payment-view-modal,
.sales-payment-view-modal *,
.sales-payment-overlay,
.sales-payment-overlay * {
    box-sizing: border-box;
    font-family: "Pyidaungsu", "Noto Sans Myanmar", "Myanmar Text", Arial, sans-serif;
}

/* Myanmar font rule က Font Awesome glyph ကို မလွှမ်းစေရန် */
.sales-payment-view-modal .fa,
.sales-payment-view-modal .fas,
.sales-payment-view-modal .far,
.sales-payment-view-modal .fab,
.sales-payment-view-modal .fa-solid,
.sales-payment-view-modal .fa-regular,
.sales-payment-view-modal .fa-brands,
.sales-payment-overlay .fa,
.sales-payment-overlay .fas,
.sales-payment-overlay .far,
.sales-payment-overlay .fab,
.sales-payment-overlay .fa-solid,
.sales-payment-overlay .fa-regular,
.sales-payment-overlay .fa-brands {
    font-family: var(--fa-style-family, "Font Awesome 6 Free"), FontAwesome !important;
    font-style: normal !important;
    font-weight: var(--fa-style, 900) !important;
    speak: never;
}

@media print {
    body * {
        visibility: hidden !important;
    }

    .sales-payment-view-modal,
    .sales-payment-view-modal * {
        visibility: visible !important;
    }

    .sales-payment-view-modal {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    .sales-payment-modal-actions,
    .sales-payment-add-wrap,
    .sales-payment-col-edit,
    .sales-payment-col-delete,
    .sales-payment-edit-cell,
    .sales-payment-delete-cell {
        display: none !important;
    }

    .sales-payment-view-modal .modal-body {
        max-height: none !important;
        overflow: visible !important;
        background: #ffffff !important;
    }
}
</style>

<div class="modal-dialog modal-lg sales-payment-view-modal" role="document">
    <div class="modal-content">
        <div class="modal-header sales-payment-modal-header">
            <h4 class="modal-title" id="myModalLabel">
                <?= html_escape(lang('view_payments')); ?>
            </h4>

            <div class="sales-payment-modal-actions">
                <button
                    type="button"
                    class="sales-payment-modal-action"
                    onclick="window.print();"
                    title="<?= html_escape(lang('print')); ?>"
                    aria-label="<?= html_escape(lang('print')); ?>"
                >
                    <i class="fa fa-print"></i>
                </button>

                <button
                    type="button"
                    class="sales-payment-modal-action"
                    data-dismiss="modal"
                    title="<?= html_escape(lang('close')); ?>"
                    aria-label="<?= html_escape(lang('close')); ?>"
                >
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>

        <div class="modal-body">
            <div class="sales-payment-summary-grid">
                <div class="sales-payment-summary-item">
                    <span class="sales-payment-summary-label">
                        <?= html_escape(lang('grand_total')); ?>
                    </span>
                    <span class="sales-payment-summary-value">
                        <?= $this->tec->formatMoney($total_amount); ?>
                    </span>
                </div>

                <div class="sales-payment-summary-item is-paid">
                    <span class="sales-payment-summary-label">
                        <?= $is_myanmar_payment_language
                            ? 'လက်ခံပြီး'
                            : html_escape(lang('paid')); ?>
                    </span>
                    <span class="sales-payment-summary-value">
                        <?= $this->tec->formatMoney($paid_amount); ?>
                    </span>
                </div>

                <div class="sales-payment-summary-item is-balance">
                    <span class="sales-payment-summary-label">
                        <?= $is_myanmar_payment_language
                            ? 'လက်ခံရန်ကျန်ငွေ'
                            : html_escape(lang('pay_balance')); ?>
                    </span>
                    <span class="sales-payment-summary-value">
                        <?= $this->tec->formatMoney($balance_amount); ?>
                    </span>
                </div>
            </div>

            <?php if ($sale_id > 0 && $customer_id > 0 && $balance_amount > 0): ?>
                <div class="sales-payment-add-wrap">
                    <a
                        href="<?= site_url(
                            'sales/add_payment/' . $sale_id . '/' . $customer_id
                        ); ?>"
                        class="sales-payment-add-button"
                        data-toggle="ajax"
                        title="<?= html_escape(lang('add_payment')); ?>"
                    >
                        <i class="fa fa-plus-circle"></i>
                        <span>
                            <?= $is_myanmar_payment_language
                                ? 'ငွေပေးချေမှုထည့်ရန်'
                                : html_escape(lang('add_payment')); ?>
                        </span>
                    </a>
                </div>
            <?php endif; ?>

            <div class="sales-payment-table-card">
                <div class="sales-payment-table-wrap">
                    <table
                        id="CompTable"
                        cellpadding="0"
                        cellspacing="0"
                        border="0"
                        class="table sales-payment-table"
                    >
                        <thead>
                            <tr>
                                <th class="sales-payment-col-date">
                                    <?= html_escape(lang('date')); ?>
                                </th>
                                <th class="sales-payment-col-amount">
                                    <?= html_escape(lang('amount')); ?>
                                </th>
                                <th class="sales-payment-col-voucher">
                                    <?= $is_myanmar_payment_language
                                        ? 'ဘောင်ချာပုံ'
                                        : 'Voucher'; ?>
                                </th>
                                <th class="sales-payment-col-edit">
                                    <?= html_escape(lang('edit')); ?>
                                </th>
                                <th class="sales-payment-col-delete">
                                    <?= html_escape(lang('delete')); ?>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($payments)): ?>
                                <?php foreach ($payments as $payment): ?>
                                    <tr class="row<?= (int) $payment->id; ?>">
                                        <td class="sales-payment-date-cell">
                                            <?= html_escape(
                                                $this->tec->hrld($payment->date)
                                            ); ?>
                                        </td>

                                        <td class="sales-payment-amount-cell">
                                            <?= $this->tec->formatMoney(
                                                $payment->amount ?? 0
                                            ); ?>
                                        </td>

                                        <td class="sales-payment-voucher-cell text-center">
                                            <?php if (!empty($payment->attachment)): ?>
                                                <a
                                                    href="<?= html_escape(base_url(
                                                        'files/' . rawurlencode(
                                                            $payment->attachment
                                                        )
                                                    )); ?>"
                                                    class="sales-payment-voucher has-file"
                                                    data-sales-voucher="1"
                                                    title="<?= $is_myanmar_payment_language ? 'ဘောင်ချာပုံကြည့်ရန်' : 'View voucher'; ?>"
                                                >
                                                    <i class="fa fa-paperclip"></i>
                                                    <span>
                                                        <?= $is_myanmar_payment_language ? 'ကြည့်ရန်' : 'View'; ?>
                                                    </span>
                                                </a>
                                            <?php else: ?>
                                                <span class="sales-payment-voucher no-file">
                                                    <i class="fa fa-paperclip"></i>
                                                    <span>
                                                        <?= $is_myanmar_payment_language ? 'မရှိပါ' : 'No file'; ?>
                                                    </span>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="sales-payment-edit-cell text-center">
                                            <a
                                                href="<?= site_url(
                                                    'sales/edit_payment/' .
                                                    (int) $payment->id
                                                ); ?>"
                                                class="sales-payment-row-button sales-payment-edit-button tip"
                                                data-toggle="ajax"
                                                title="<?= html_escape(lang('edit')); ?>"
                                            >
                                                <i class="fa fa-edit"></i>
                                                <span><?= html_escape(lang('edit')); ?></span>
                                            </a>
                                        </td>

                                        <td class="sales-payment-delete-cell text-center">
                                            <?php
                                            /*
                                             * Sales payment ကိုသာ ဖျက်ရန် Purchase route နှင့်
                                             * မရောစေရန် Sales delete URL ကို သီးသန့်သတ်မှတ်ထားသည်။
                                             */
                                            $sales_payment_delete_url = site_url(
                                                'sales/delete_payment/' .
                                                (int) $payment->id
                                            );
                                            ?>
                                            <a
                                                href="<?= html_escape($sales_payment_delete_url); ?>"
                                                class="sales-payment-row-button sales-payment-delete-button"
                                                data-sales-payment-delete="1"
                                                data-sales-payment-delete-url="<?= html_escape($sales_payment_delete_url); ?>"
                                                data-sales-payment-id="<?= (int) $payment->id; ?>"
                                                data-sales-id="<?= (int) $sale_id; ?>"
                                                title="<?= html_escape(lang('delete')); ?>"
                                            >
                                                <i class="fa fa-trash"></i>
                                                <span><?= html_escape(lang('delete')); ?></span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="sales-payment-empty-cell">
                                        <i class="fa fa-inbox"></i>
                                        <?= html_escape(lang('no_data_available')); ?>
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

<!-- In-app voucher viewer -->
<div
    id="salesPaymentVoucherViewer"
    class="sales-payment-overlay"
    aria-hidden="true"
>
    <button
        type="button"
        class="sales-payment-overlay-backdrop"
        data-sales-voucher-close="1"
        aria-label="<?= html_escape(lang('close')); ?>"
    ></button>

    <div
        class="sales-payment-overlay-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="salesPaymentVoucherTitle"
    >
        <div class="sales-payment-overlay-header">
            <h4 class="sales-payment-overlay-title" id="salesPaymentVoucherTitle">
                <?= $is_myanmar_payment_language ? 'ဘောင်ချာပုံ' : 'Voucher'; ?>
            </h4>

            <button
                type="button"
                class="sales-payment-overlay-close"
                data-sales-voucher-close="1"
            >
                <i class="fa fa-times"></i>
                <span>
                    <?= $is_myanmar_payment_language
                        ? 'ပိတ်ရန်'
                        : html_escape(lang('close')); ?>
                </span>
            </button>
        </div>

        <div class="sales-payment-voucher-body">
            <img
                id="salesPaymentVoucherImage"
                class="sales-payment-voucher-image"
                src=""
                alt="<?= $is_myanmar_payment_language ? 'ဘောင်ချာပုံ' : 'Voucher'; ?>"
            >

            <iframe
                id="salesPaymentVoucherFrame"
                class="sales-payment-voucher-frame"
                src="about:blank"
                title="<?= $is_myanmar_payment_language ? 'ဘောင်ချာဖိုင်' : 'Voucher file'; ?>"
            ></iframe>

            <div
                id="salesPaymentVoucherError"
                class="sales-payment-voucher-error"
            >
                <?= $is_myanmar_payment_language
                    ? 'ဘောင်ချာပုံကို ဖွင့်၍မရပါ။'
                    : 'The voucher could not be opened.'; ?>
            </div>
        </div>
    </div>
</div>

<!-- URL-free delete confirmation -->
<div
    id="salesPaymentDeleteConfirm"
    class="sales-payment-overlay"
    aria-hidden="true"
>
    <button
        type="button"
        class="sales-payment-overlay-backdrop"
        data-sales-payment-delete-close="1"
        aria-label="<?= html_escape(lang('close')); ?>"
    ></button>

    <div
        class="sales-payment-overlay-panel sales-payment-confirm-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="salesPaymentDeleteTitle"
    >
        <div class="sales-payment-overlay-header">
            <h4 class="sales-payment-overlay-title" id="salesPaymentDeleteTitle">
                <i class="fa fa-exclamation-triangle text-danger"></i>
                <?= $is_myanmar_payment_language
                    ? 'ငွေပေးချေမှု ဖျက်ရန်အတည်ပြုခြင်း'
                    : 'Confirm Payment Delete'; ?>
            </h4>
        </div>

        <div class="sales-payment-confirm-body">
            <?= $is_myanmar_payment_language
                ? 'ဤငွေလက်ခံမှုကိုပြန်ဖျက်မည်မှာ သေချာပါသလား။'
                : 'Reverse this payment under the audit workflow?'; ?>

            <label
                for="salesPaymentDeleteReason"
                class="sales-payment-confirm-reason-label"
            >
                <?= $is_myanmar_payment_language
                    ? 'ပြန်ဖျက်ရသည့်အကြောင်းပြချက် *'
                    : 'Reversal reason *'; ?>
            </label>

            <textarea
                id="salesPaymentDeleteReason"
                class="sales-payment-confirm-reason"
                maxlength="500"
                required
                placeholder="<?= $is_myanmar_payment_language
                    ? 'ဥပမာ - ငွေလက်ခံမှတ်တမ်း မှားယွင်းထည့်သွင်းမိခြင်း'
                    : 'Example: Payment was entered by mistake'; ?>"
            ></textarea>

            <div
                id="salesPaymentDeleteError"
                class="sales-payment-confirm-error"
                role="alert"
            ></div>
        </div>

        <div class="sales-payment-confirm-actions">
            <button
                type="button"
                class="sales-payment-confirm-cancel"
                data-sales-payment-delete-close="1"
            >
                <i class="fa fa-times"></i>
                <?= $is_myanmar_payment_language ? 'မဖျက်တော့ပါ' : 'Cancel'; ?>
            </button>

            <button
                type="button"
                class="sales-payment-confirm-delete"
                id="confirmSalesPaymentDelete"
            >
                <i class="fa fa-trash"></i>
                <?= $is_myanmar_payment_language ? 'ဖျက်မည်' : 'Reverse'; ?>
            </button>
        </div>
    </div>
</div>

<script type="text/javascript" charset="UTF-8">
$(document).ready(function () {
    var pendingPaymentDeleteUrl = '';

    function closeSalesVoucherViewer() {
        $('#salesPaymentVoucherViewer')
            .removeClass('is-open')
            .attr('aria-hidden', 'true');

        $('#salesPaymentVoucherImage').hide().attr('src', '');
        $('#salesPaymentVoucherFrame').hide().attr('src', 'about:blank');
        $('#salesPaymentVoucherError').hide();
    }

    $(document)
        .off('click.salesPaymentVoucher', '[data-sales-voucher]')
        .on('click.salesPaymentVoucher', '[data-sales-voucher]', function (event) {
            var href = $(this).attr('href') || '';
            var $image = $('#salesPaymentVoucherImage');
            var $frame = $('#salesPaymentVoucherFrame');
            var $error = $('#salesPaymentVoucherError');

            if (!href) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            $image.hide().attr('src', '');
            $frame.hide().attr('src', 'about:blank');
            $error.hide();

            if (/\.pdf(?:[?#]|$)/i.test(href)) {
                $frame.attr('src', href).show();
            } else {
                $image
                    .off('error.salesPaymentVoucher')
                    .on('error.salesPaymentVoucher', function () {
                        $(this).hide();
                        $error.show();
                    })
                    .attr('src', href)
                    .show();
            }

            $('#salesPaymentVoucherViewer')
                .addClass('is-open')
                .attr('aria-hidden', 'false');

            return false;
        });

    $(document)
        .off('click.salesPaymentVoucherClose', '[data-sales-voucher-close]')
        .on(
            'click.salesPaymentVoucherClose',
            '[data-sales-voucher-close]',
            closeSalesVoucherViewer
        );

    $(document)
        .off('click.salesPaymentDelete', '[data-sales-payment-delete]')
        .on(
            'click.salesPaymentDelete',
            '[data-sales-payment-delete]',
            function (event) {
                var salesDeleteUrl = $(this).attr(
                    'data-sales-payment-delete-url'
                ) || '';

                event.preventDefault();
                event.stopImmediatePropagation();

                /* Purchase payment URL ကို မတော်တဆမသုံးနိုင်ရန် Sales route စစ်ပါ။ */
                if (
                    !salesDeleteUrl ||
                    salesDeleteUrl.indexOf('/sales/delete_payment/') === -1
                ) {
                    pendingPaymentDeleteUrl = '';
                    return false;
                }

                pendingPaymentDeleteUrl = salesDeleteUrl;

                $('#salesPaymentDeleteReason').val('');
                $('#salesPaymentDeleteError').hide().text('');
                $('#confirmSalesPaymentDelete')
                    .prop('disabled', false)
                    .html(
                        '<i class="fa fa-trash"></i> ' +
                        '<?= $is_myanmar_payment_language
                            ? 'Reverse လုပ်မည်'
                            : 'Reverse'; ?>'
                    );

                $('#salesPaymentDeleteConfirm')
                    .addClass('is-open')
                    .attr('aria-hidden', 'false');

                window.setTimeout(function () {
                    $('#salesPaymentDeleteReason').trigger('focus');
                }, 0);

                return false;
            }
        );

    function closeSalesPaymentDeleteConfirm() {
        pendingPaymentDeleteUrl = '';

        $('#salesPaymentDeleteReason').val('');
        $('#salesPaymentDeleteError').hide().text('');
        $('#confirmSalesPaymentDelete')
            .prop('disabled', false)
            .html(
                '<i class="fa fa-trash"></i> ' +
                '<?= $is_myanmar_payment_language
                    ? 'Reverse လုပ်မည်'
                    : 'Reverse'; ?>'
            );

        $('#salesPaymentDeleteConfirm')
            .removeClass('is-open')
            .attr('aria-hidden', 'true');
    }

    $(document)
        .off(
            'click.salesPaymentDeleteClose',
            '[data-sales-payment-delete-close]'
        )
        .on(
            'click.salesPaymentDeleteClose',
            '[data-sales-payment-delete-close]',
            closeSalesPaymentDeleteConfirm
        );

    $('#confirmSalesPaymentDelete')
        .off('click.salesPaymentDeleteConfirm')
        .on('click.salesPaymentDeleteConfirm', function () {
            var deleteUrl = pendingPaymentDeleteUrl;
            var reason = $.trim($('#salesPaymentDeleteReason').val() || '');
            var paymentIdMatch;
            var paymentId;
            var requestData;
            var $button = $(this);
            var $error = $('#salesPaymentDeleteError');

            if (!deleteUrl) {
                return;
            }

            if (!reason) {
                $error
                    .text(
                        '<?= $is_myanmar_payment_language
                            ? 'Reverse လုပ်ရသည့်အကြောင်းပြချက်ကို ထည့်ပေးပါ။'
                            : 'Please enter a reversal reason.'; ?>'
                    )
                    .show();
                $('#salesPaymentDeleteReason').trigger('focus');
                return;
            }

            paymentIdMatch = deleteUrl.match(/\/sales\/delete_payment\/(\d+)/);
            paymentId = paymentIdMatch ? paymentIdMatch[1] : '';

            if (!paymentId) {
                $error
                    .text(
                        '<?= $is_myanmar_payment_language
                            ? 'Sales payment ID မမှန်ပါ။'
                            : 'Invalid sales payment ID.'; ?>'
                    )
                    .show();
                return;
            }

            requestData = {
                payment_id: paymentId,
                delete_reason: reason,
                ajax_delete: 1,
                '<?= $this->security->get_csrf_token_name(); ?>':
                    '<?= $this->security->get_csrf_hash(); ?>'
            };

            $error.hide().text('');

            $button
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: deleteUrl,
                type: 'POST',
                dataType: 'json',
                data: requestData
            })
            .done(function (response) {
                if (response && response.status === 'success') {
                    window.location.reload();
                    return;
                }

                $error
                    .text(
                        response && response.message
                            ? response.message
                            : '<?= $is_myanmar_payment_language
                                ? 'ငွေလက်ခံမှုကို Reverse လုပ်၍မရပါ။'
                                : 'The payment could not be reversed.'; ?>'
                    )
                    .show();
            })
            .fail(function (xhr) {
                var response = xhr.responseJSON || {};

                $error
                    .text(
                        response.message ||
                        '<?= $is_myanmar_payment_language
                            ? 'ငွေလက်ခံမှုကို Reverse လုပ်၍မရပါ။'
                            : 'The payment could not be reversed.'; ?>'
                    )
                    .show();
            })
            .always(function () {
                $button
                    .prop('disabled', false)
                    .html(
                        '<i class="fa fa-trash"></i> ' +
                        '<?= $is_myanmar_payment_language
                            ? 'Reverse လုပ်မည်'
                            : 'Reverse'; ?>'
                    );
            });
        });

    $(document)
        .off('keydown.salesPaymentOverlays')
        .on('keydown.salesPaymentOverlays', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if ($('#salesPaymentVoucherViewer').hasClass('is-open')) {
                closeSalesVoucherViewer();
            }

            if ($('#salesPaymentDeleteConfirm').hasClass('is-open')) {
                closeSalesPaymentDeleteConfirm();
            }
        });
});
</script>
