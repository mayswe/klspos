<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
/*
 * Controller က $inv မပို့ထားသေးလျှင် payment->purchase_id ဖြင့်
 * purchase ကို ပြန်ယူပေးထားသည်။
 */
if (empty($inv) && !empty($payment->purchase_id)) {
    $inv = $this->purchases_model->getPurchaseByID((int) $payment->purchase_id);
}

if (empty($payment) || empty($inv)) {
    echo '<div class="alert alert-danger">Payment or Purchase data not found.</div>';
    return;
}

$total_amount = (float) $inv->total;

/*
 * Current paid total ကို payment records မှ ပြန်တွက်ထားသည်။
 * Purchase header paid value stale ဖြစ်နေပါကလည်း UI မှန်စေရန်။
 */
$payment_rows = $this->purchases_model->getPurchasePayments((int) $payment->purchase_id);
$total_paid = 0;

if (!empty($payment_rows)) {
    foreach ($payment_rows as $row) {
        $total_paid += (float) $row->amount;
    }
} else {
    $total_paid = (float) $inv->paid;
}

$due_amount = $total_amount - $total_paid;
if ($due_amount < 0) {
    $due_amount = 0;
}

$current_payment_amount = (float) $payment->amount;

/*
 * Edit လုပ်ရာတွင် လက်ရှိ payment ကို replace လုပ်တာဖြစ်သောကြောင့်
 * maximum editable amount = current due + old payment amount.
 */
$editable_limit = $due_amount + $current_payment_amount;
if ($editable_limit > $total_amount) {
    $editable_limit = $total_amount;
}

$current_paid_by = !empty($payment->paid_by) ? $payment->paid_by : 'cash';

$payment_methods = [
    'cash'  => lang('cash'),
    'other' => lang('other'),
];

if (!isset($payment_methods[$current_paid_by])) {
    $translated = lang($current_paid_by);
    $payment_methods[$current_paid_by] =
        ($translated && $translated !== $current_paid_by)
            ? $translated
            : $current_paid_by;
}

$payment_date = !empty($payment->date)
    ? date('Y-m-d H:i', strtotime($payment->date))
    : date('Y-m-d H:i');
?>

<style>
/* =========================================================
   KLSPOS - Edit Purchase Payment
   ========================================================= */

.edit-payment-modal {
    width: 760px;
    max-width: calc(100% - 30px);
    margin: 25px auto;
}

.edit-payment-modal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 20px 55px rgba(15, 23, 42, 0.22);
}

.edit-payment-modal .modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.edit-payment-modal .modal-title {
    margin: 0;
    color: #1e293b;
    font-size: 18px;
    font-weight: 700;
}

.edit-payment-modal .close {
    margin-top: 1px;
    color: #64748b;
    opacity: 1;
}

.edit-payment-modal .modal-body {
    padding: 18px 20px 8px;
    background: #ffffff;
}

.edit-payment-modal .modal-footer {
    padding: 12px 20px 18px;
    border-top: 0;
    background: #ffffff;
}

.edit-payment-modal .payment-enter-info {
    margin: 0 0 14px;
    color: #64748b;
    font-size: 13px;
}

/* Summary */
.edit-payment-modal .payment-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin: 4px 0 18px;
}

.edit-payment-modal .payment-summary-card {
    min-width: 0;
    padding: 13px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
}

.edit-payment-modal .payment-summary-card.paid {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.edit-payment-modal .payment-summary-card.due {
    border-color: #fecaca;
    background: #fef2f2;
}

.edit-payment-modal .payment-summary-label {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.edit-payment-modal .payment-summary-value {
    display: block;
    color: #1e293b;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.35;
    white-space: nowrap;
}

.edit-payment-modal .payment-summary-card.paid .payment-summary-value {
    color: #16a34a;
}

.edit-payment-modal .payment-summary-card.due .payment-summary-value {
    color: #dc2626;
}

/* Main row */
.edit-payment-modal .payment-fields-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}

.edit-payment-modal .payment-fields-row .form-group {
    min-width: 0;
    margin-bottom: 0;
}

.edit-payment-modal .form-group {
    margin-bottom: 14px;
}

.edit-payment-modal .form-group label {
    display: block;
    margin-bottom: 6px;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
}

.edit-payment-modal .form-control {
    height: 40px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    box-shadow: none;
    font-size: 14px;
}

.edit-payment-modal .form-control:focus {
    border-color: #94a3b8;
    box-shadow: none;
}

.edit-payment-modal input[readonly] {
    background: #f8fafc;
    color: #475569;
}

.edit-payment-modal textarea.form-control {
    height: auto;
    min-height: 105px;
}

.edit-payment-modal .select2-container {
    width: 100% !important;
}

.edit-payment-modal .select2-container .select2-choice,
.edit-payment-modal .select2-container--default .select2-selection--single {
    min-height: 40px;
    border-color: #cbd5e1;
    border-radius: 7px;
}

.edit-payment-modal .voucher-upload .form-control {
    height: auto;
    min-height: 40px;
    padding: 6px 10px;
}

.edit-payment-modal .current-voucher {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 8px;
    padding: 9px 11px;
    border: 1px solid #dbeafe;
    border-radius: 7px;
    background: #eff6ff;
    color: #334155;
    font-size: 12px;
}

.edit-payment-modal .current-voucher a {
    color: #2563eb;
    font-weight: 700;
    white-space: nowrap;
}

.edit-payment-modal .payment-limit-help {
    display: block;
    margin-top: 5px;
    color: #64748b;
    font-size: 11px;
}

.edit-payment-modal .btn-update-payment {
    min-width: 125px;
    min-height: 40px;
    padding: 8px 20px;
    border-color: #2563eb;
    border-radius: 7px;
    background: #2563eb;
    color: #ffffff;
    font-weight: 700;
}

.edit-payment-modal .btn-update-payment:hover,
.edit-payment-modal .btn-update-payment:focus {
    border-color: #1d4ed8;
    background: #1d4ed8;
    color: #ffffff;
}

.edit-payment-modal,
.edit-payment-modal input,
.edit-payment-modal select,
.edit-payment-modal textarea,
.edit-payment-modal button {
    font-family:
        "Pyidaungsu",
        "Noto Sans Myanmar",
        "Myanmar Text",
        Arial,
        sans-serif;
}

/* =========================================================
   Payment Voucher Attachment
   ========================================================= */

.payment-view-modal .payment-voucher-cell {
    text-align: center !important;
    white-space: nowrap;
}

.payment-view-modal .payment-voucher {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-width: 82px;
    height: 32px;
    padding: 5px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    text-decoration: none !important;
    transition: all 0.18s ease;
}

.payment-view-modal .payment-voucher i {
    font-size: 13px;
}

.payment-view-modal .payment-voucher:hover,
.payment-view-modal .payment-voucher:focus {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #2563eb;
    text-decoration: none !important;
    outline: none;
}

.payment-view-modal .payment-voucher.has-file {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #2563eb;
}

.payment-view-modal .payment-voucher.has-file:hover {
    border-color: #60a5fa;
    background: #dbeafe;
    color: #1d4ed8;
}

@media (max-width: 767px) {
    .edit-payment-modal {
        width: calc(100% - 20px);
        max-width: none;
        margin: 10px auto;
    }

    .edit-payment-modal .modal-body {
        padding: 14px 12px 6px;
    }

    .edit-payment-modal .modal-header,
    .edit-payment-modal .modal-footer {
        padding-left: 12px;
        padding-right: 12px;
    }

    .edit-payment-modal .payment-summary-grid,
    .edit-payment-modal .payment-fields-row {
        gap: 6px;
    }

    .edit-payment-modal .payment-summary-card {
        padding: 10px 8px;
    }

    .edit-payment-modal .payment-summary-label,
    .edit-payment-modal .form-group label {
        font-size: 11px;
    }

    .edit-payment-modal .payment-summary-value {
        font-size: 15px;
    }

    .edit-payment-modal .payment-fields-row .form-control {
        padding-left: 7px;
        padding-right: 7px;
        font-size: 12px;
    }
    
    .payment-view-modal .payment-voucher {
        min-width: 34px;
        width: 34px;
        height: 32px;
        padding: 0;
    }

    .payment-view-modal .payment-voucher span {
        display: none;
    }
}

@media (max-width: 480px) {
    .edit-payment-modal .payment-summary-label {
        font-size: 10px;
    }

    .edit-payment-modal .payment-summary-value {
        font-size: 13px;
    }

    .edit-payment-modal .payment-fields-row {
        grid-template-columns: 1fr 1fr 1fr;
    }
}
</style>

<div class="modal-dialog edit-payment-modal" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-hidden="true"
            >
                <i class="fa fa-times"></i>
            </button>

            <h4 class="modal-title">
                ငွေပေးချေမှု ပြင်ရန်
            </h4>
        </div>

        <?= form_open_multipart(
            'purchases/edit_payment/' . (int) $payment->id
        ); ?>

        <div class="modal-body">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <p class="payment-enter-info">
                ပြင်ဆင်လိုသည့် ငွေပေးချေမှုအချက်အလက်များကို ပြောင်းလဲနိုင်ပါသည်။
            </p>

            <!-- Date -->
            <div class="form-group" style="max-width:300px;">
                <label for="date">
                    ရက်စွဲ
                </label>

                <input
                    type="text"
                    name="date"
                    id="date"
                    value="<?= html_escape(
                        isset($_POST['date']) ? $_POST['date'] : $payment_date
                    ); ?>"
                    class="form-control datetimepicker"
                    required="required"
                >
            </div>

            <!-- Current payment reference kept unchanged -->
            <input
                type="hidden"
                name="reference"
                value="<?= html_escape($payment->reference ?? ''); ?>"
            >

            <!-- Summary -->
            <div class="payment-summary-grid">

                <div class="payment-summary-card">
                    <span class="payment-summary-label">
                        စုစုပေါင်း
                    </span>

                    <strong class="payment-summary-value">
                        <?= $this->tec->formatMoney($total_amount); ?>
                    </strong>
                </div>

                <div class="payment-summary-card paid">
                    <span class="payment-summary-label">
                        ပေးဆောင်ပြီး
                    </span>

                    <strong class="payment-summary-value">
                        <?= $this->tec->formatMoney($total_paid); ?>
                    </strong>
                </div>

                <div class="payment-summary-card due">
                    <span class="payment-summary-label">
                        ပေးရန်ကျန်ငွေ
                    </span>

                    <strong class="payment-summary-value">
                        <?= $this->tec->formatMoney($due_amount); ?>
                    </strong>
                </div>

            </div>

            <!-- One row -->
            <div class="payment-fields-row">

                <div class="form-group">
                    <label for="total_due">
                        အကြွေးစုစုပေါင်း
                    </label>

                    <input
                        type="text"
                        id="total_due"
                        value="<?= html_escape(
                            $this->tec->formatDecimal($editable_limit)
                        ); ?>"
                        class="form-control"
                        readonly="readonly"
                    >
                </div>

                <div class="form-group">
                    <label for="amount">
                        ပေးချေမည့်ပမာဏ
                    </label>

                    <input
                        name="amount-paid"
                        type="text"
                        id="amount"
                        value="<?= html_escape(
                            isset($_POST['amount-paid'])
                                ? $_POST['amount-paid']
                                : $this->tec->formatDecimal($current_payment_amount)
                        ); ?>"
                        class="form-control kb-pad amount"
                        inputmode="decimal"
                        autocomplete="off"
                        pattern="[0-9]+([.][0-9]{1,2})?"
                        data-max="<?= html_escape(
                            number_format($editable_limit, 2, '.', '')
                        ); ?>"
                        required="required"
                    >

                    <span class="payment-limit-help">
                        အများဆုံး <?= $this->tec->formatMoney($editable_limit); ?> အထိ ပြင်နိုင်သည်
                    </span>
                </div>

                <div class="form-group">
                    <label for="paid_by">
                        ပေးချေမည့် နည်းလမ်း
                    </label>

                    <select
                        name="paid_by"
                        id="paid_by"
                        class="form-control paid_by select2"
                        style="width:100%"
                        required="required"
                    >
                        <?php foreach ($payment_methods as $method_key => $method_label): ?>
                            <option
                                value="<?= html_escape($method_key); ?>"
                                <?= $current_paid_by === $method_key
                                    ? 'selected="selected"'
                                    : ''; ?>
                            >
                                <?= html_escape(
                                    $method_label ?: $method_key
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <!-- Existing optional values -->
            <input
                type="hidden"
                name="cheque_no"
                value="<?= html_escape($payment->cheque_no ?? ''); ?>"
            >

            <input
                type="hidden"
                name="gift_card_no"
                value="<?= html_escape($payment->gc_no ?? ''); ?>"
            >

            <!-- Voucher -->
            <div class="form-group voucher-upload">

                <label for="attachment">
                    ဘောင်ချာပုံတင်ရန်
                </label>

                <?php if (!empty($payment->attachment)): ?>
                    <div class="current-voucher">
                        <span>
                            <i class="fa fa-paperclip"></i>
                            လက်ရှိဖိုင်:
                            <?= html_escape(basename($payment->attachment)); ?>
                        </span>

                        <a
                            href="<?= base_url(
                                'files/' . rawurlencode($payment->attachment)
                            ); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            ကြည့်ရန်
                        </a>
                    </div>
                <?php endif; ?>

                <input
                    id="attachment"
                    type="file"
                    name="userfile"
                    class="form-control file"
                    accept="image/*"
                >

                <?php if (!empty($payment->attachment)): ?>
                    <span class="payment-limit-help">
                        ဖိုင်အသစ်မရွေးပါက လက်ရှိဘောင်ချာပုံကို မပြောင်းပါ။
                    </span>
                <?php endif; ?>

            </div>

            <!-- Note -->
            <div class="form-group">
                <label for="note">
                    <?= html_escape(lang('note')); ?>
                </label>

                <?= form_textarea(
                    'note',
                    (
                        isset($_POST['note'])
                            ? $_POST['note']
                            : ($payment->note ?? '')
                    ),
                    'class="form-control redactor" id="note"'
                ); ?>
            </div>

        </div>

        <div class="modal-footer">
            <button
                type="submit"
                name="update_payment"
                value="1"
                class="btn btn-primary btn-update-payment"
            >
                <i class="fa fa-edit"></i>
                ပြင်မည်
            </button>
        </div>

        <?= form_close(); ?>

    </div>
</div>

<script type="text/javascript" charset="UTF-8">
$(document).ready(function () {

    /*
     * Amount input:
     * digits only + one dot + max 2 decimal places
     */
    $(document)
        .off('input.editPaymentAmount', '#amount')
        .on('input.editPaymentAmount', '#amount', function () {

            var value = String($(this).val() || '');

            value = value.replace(/[^0-9.]/g, '');

            var firstDot = value.indexOf('.');

            if (firstDot !== -1) {
                value =
                    value.substring(0, firstDot + 1) +
                    value.substring(firstDot + 1).replace(/\./g, '');
            }

            if (value.indexOf('.') !== -1) {
                var parts = value.split('.');
                parts[1] = (parts[1] || '').substring(0, 2);
                value = parts[0] + '.' + parts[1];
            }

            var numericValue = parseFloat(value || '0');
            var maxValue = parseFloat($(this).attr('data-max') || '0');

            if (
                maxValue > 0 &&
                numericValue > maxValue
            ) {
                value = maxValue.toFixed(2);
            }

            $(this).val(value);
        });

    $(document)
        .off('keydown.editPaymentAmount', '#amount')
        .on('keydown.editPaymentAmount', '#amount', function (e) {

            if (
                e.ctrlKey ||
                e.metaKey ||
                e.key === 'Backspace' ||
                e.key === 'Delete' ||
                e.key === 'Tab' ||
                e.key === 'ArrowLeft' ||
                e.key === 'ArrowRight' ||
                e.key === 'Home' ||
                e.key === 'End'
            ) {
                return;
            }

            if (/^[0-9]$/.test(e.key)) {
                return;
            }

            if (
                e.key === '.' &&
                $(this).val().indexOf('.') === -1
            ) {
                return;
            }

            e.preventDefault();
        });

});
</script>

<script
    src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js"
    type="text/javascript"
></script>

<script
    src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"
    type="text/javascript"
></script>

<script type="text/javascript">
$(function () {
    if ($.fn.datetimepicker) {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });
    }
});
</script>