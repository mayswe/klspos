<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>
<?php
$sale_id = !empty($inv->id) ? (int) $inv->id : 0;
$customer_id = !empty($inv->customer_id) ? (int) $inv->customer_id : 0;
$total_amount = !empty($inv)
    ? (float) ($inv->grand_total ?? ($inv->total ?? 0))
    : 0;
$paid_amount = !empty($inv) ? (float) ($inv->paid ?? 0) : 0;
$balance_amount = max(0, $total_amount - $paid_amount);

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
   Sales Add Payment - Purchase Style
   ========================================================= */
.sales-add-payment-modal {
    width: 760px;
    max-width: calc(100vw - 24px);
    margin: 24px auto;
    color: #334155;
}

.sales-add-payment-modal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 15px;
    background: #ffffff;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
}

.sales-add-payment-header {
    display: flex;
    min-height: 66px;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.sales-add-payment-title {
    margin: 0;
    color: #1e293b;
    font-size: 20px;
    font-weight: 900;
    line-height: 1.4;
}

.sales-add-payment-close {
    display: inline-flex;
    width: 42px;
    height: 42px;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #fff1f2;
    color: #dc2626;
    font-size: 17px;
}

.sales-add-payment-modal .modal-body {
    max-height: calc(100vh - 145px);
    overflow-x: hidden !important;
    overflow-y: auto;
    padding: 18px;
    background: #f8fafc;
}

#salesAddPaymentForm,
.sales-add-payment-form-card,
.sales-add-payment-grid,
.sales-add-payment-field,
.sales-add-payment-modal .form-group,
.sales-add-payment-modal .input-group,
.sales-add-payment-modal .file-input,
.sales-add-payment-modal .file-caption-main {
    min-width: 0 !important;
    max-width: 100% !important;
}

#salesAddPaymentForm {
    width: 100%;
    overflow-x: hidden !important;
}

.sales-add-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}

.sales-add-summary-item {
    min-width: 0;
    padding: 14px;
    border: 1px solid #dbe5ef;
    border-radius: 11px;
    background: #ffffff;
}

.sales-add-summary-item.is-paid {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.sales-add-summary-item.is-balance {
    border-color: #fed7aa;
    background: #fff7ed;
}

.sales-add-summary-label {
    display: block;
    margin-bottom: 5px;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
}

.sales-add-summary-value {
    display: block;
    overflow: hidden;
    color: #1e293b;
    font-size: 18px;
    font-weight: 900;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sales-add-summary-item.is-paid .sales-add-summary-value {
    color: #15803d;
}

.sales-add-summary-item.is-balance .sales-add-summary-value {
    color: #ea580c;
}

.sales-add-payment-form-card {
    padding: 16px;
    border: 1px solid #dbe5ef;
    border-radius: 12px;
    background: #ffffff;
}

.sales-add-payment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.sales-add-payment-field {
    min-width: 0;
}

.sales-add-payment-field.is-full {
    grid-column: 1 / -1;
}

.sales-add-payment-label {
    display: block;
    margin-bottom: 7px;
    color: #334155;
    font-size: 14px;
    font-weight: 900;
}

.sales-add-payment-required {
    color: #dc2626;
}

.sales-add-payment-modal .form-control {
    width: 100%;
    height: 44px;
    padding: 9px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #ffffff;
    color: #1e293b;
    box-shadow: none !important;
    font-size: 15px;
}

.sales-add-payment-modal textarea.form-control {
    height: 82px;
    min-height: 82px;
    resize: vertical;
}

.sales-add-payment-modal .form-control:focus {
    border-color: #0f8a94;
    box-shadow: 0 0 0 3px rgba(15, 138, 148, .12) !important;
}

.sales-add-payment-amount-wrap {
    position: relative;
}

.sales-add-payment-amount-wrap .form-control {
    padding-right: 52px;
    font-size: 18px;
    font-weight: 900;
    text-align: right;
}

.sales-add-payment-currency {
    position: absolute;
    top: 50%;
    right: 12px;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    transform: translateY(-50%);
    pointer-events: none;
}

.sales-add-payment-file {
    min-height: 44px;
    height: auto !important;
    padding: 7px 9px !important;
}

.sales-add-payment-help {
    display: block;
    margin-top: 6px;
    color: #94a3b8;
    font-size: 11px;
    line-height: 1.5;
}

.sales-add-payment-error {
    display: none;
    margin-top: 7px;
    padding: 8px 10px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fff1f2;
    color: #dc2626;
    font-size: 12px;
    font-weight: 800;
}

.sales-add-payment-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 13px 18px;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
}

.sales-add-payment-footer .btn {
    display: inline-flex;
    min-height: 44px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 900;
}

.sales-add-payment-submit {
    min-width: 150px;
    border-color: #16a34a !important;
    background: #16a34a !important;
    color: #ffffff !important;
}

.sales-add-payment-submit:hover,
.sales-add-payment-submit:focus {
    background: #15803d !important;
}

@media (max-width: 767px) {
    .sales-add-payment-modal {
        width: calc(100vw - 12px);
        max-width: calc(100vw - 12px);
        margin: 6px auto;
    }

    .sales-add-payment-header {
        min-height: 56px;
        padding: 9px 11px;
    }

    .sales-add-payment-title {
        font-size: 16px;
    }

    .sales-add-payment-close {
        width: 38px;
        height: 38px;
    }

    .sales-add-payment-modal .modal-body {
        max-height: calc(100vh - 125px);
        padding: 10px;
    }

    .sales-add-summary-grid {
        gap: 6px;
        margin-bottom: 10px;
    }

    .sales-add-summary-item {
        padding: 10px 7px;
        border-radius: 8px;
    }

    .sales-add-summary-label {
        min-height: 34px;
        margin-bottom: 3px;
        font-size: 10px;
        line-height: 1.5;
    }

    .sales-add-summary-value {
        font-size: 13px;
    }

    .sales-add-payment-form-card {
        padding: 11px;
    }

    .sales-add-payment-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .sales-add-payment-field.is-full {
        grid-column: auto;
    }

    .sales-add-payment-label {
        margin-bottom: 5px;
        font-size: 12px;
    }

    .sales-add-payment-modal .form-control {
        height: 42px;
        font-size: 14px;
    }

    .sales-add-payment-modal textarea.form-control {
        height: 68px;
        min-height: 68px;
    }

    .sales-add-payment-footer {
        display: grid;
        grid-template-columns: 1fr 1.35fr;
        gap: 8px;
        padding: 10px;
    }

    .sales-add-payment-footer .btn,
    .sales-add-payment-submit {
        width: 100%;
        min-width: 0;
        min-height: 43px;
        padding: 8px 7px;
        font-size: 12px;
    }
}

.sales-add-payment-modal,
.sales-add-payment-modal * {
    box-sizing: border-box;
    font-family: "Pyidaungsu", "Noto Sans Myanmar", "Myanmar Text", Arial, sans-serif;
}

/* Myanmar font rule က Font Awesome glyph ကို မလွှမ်းစေရန် */
.sales-add-payment-modal .fa,
.sales-add-payment-modal .fas,
.sales-add-payment-modal .far,
.sales-add-payment-modal .fab,
.sales-add-payment-modal .fa-solid,
.sales-add-payment-modal .fa-regular,
.sales-add-payment-modal .fa-brands {
    font-family: var(--fa-style-family, "Font Awesome 6 Free"), FontAwesome !important;
    font-style: normal !important;
    font-weight: var(--fa-style, 900) !important;
    speak: never;
}
</style>

<div class="modal-dialog sales-add-payment-modal" role="document">
    <div class="modal-content">
        <?= form_open_multipart(
            'sales/add_payment/' . $sale_id . '/' . $customer_id,
            ['id' => 'salesAddPaymentForm']
        ); ?>

        <div class="modal-header sales-add-payment-header">
            <h4 class="sales-add-payment-title" id="myModalLabel">
                <?= $is_myanmar_payment_language
                    ? 'ငွေပေးချေမှုထည့်ရန်'
                    : html_escape(lang('add_payment')); ?>
            </h4>

            <button
                type="button"
                class="sales-add-payment-close"
                data-dismiss="modal"
                title="<?= html_escape(lang('close')); ?>"
                aria-label="<?= html_escape(lang('close')); ?>"
            >
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="sales-add-summary-grid">
                <div class="sales-add-summary-item">
                    <span class="sales-add-summary-label">
                        <?= html_escape(lang('grand_total')); ?>
                    </span>
                    <span class="sales-add-summary-value">
                        <?= $this->tec->formatMoney($total_amount); ?>
                    </span>
                </div>

                <div class="sales-add-summary-item is-paid">
                    <span class="sales-add-summary-label">
                        <?= $is_myanmar_payment_language
                            ? 'လက်ခံပြီး'
                            : html_escape(lang('paid')); ?>
                    </span>
                    <span class="sales-add-summary-value">
                        <?= $this->tec->formatMoney($paid_amount); ?>
                    </span>
                </div>

                <div class="sales-add-summary-item is-balance">
                    <span class="sales-add-summary-label">
                        <?= $is_myanmar_payment_language
                            ? 'လက်ခံရန်ကျန်ငွေ'
                            : html_escape(lang('pay_balance')); ?>
                    </span>
                    <span class="sales-add-summary-value">
                        <?= $this->tec->formatMoney($balance_amount); ?>
                    </span>
                </div>
            </div>

            <input type="hidden" name="sale_id" value="<?= $sale_id; ?>">

            <div class="sales-add-payment-form-card">
                <div class="sales-add-payment-grid">
                    <?php if ($Admin): ?>
                        <div class="sales-add-payment-field">
                            <label class="sales-add-payment-label" for="date">
                                <?= html_escape(lang('date')); ?>
                                <span class="sales-add-payment-required">*</span>
                            </label>

                            <input
                                type="text"
                                name="date"
                                id="date"
                                class="form-control datetimepicker"
                                value="<?= html_escape(
                                    isset($_POST['date'])
                                        ? $_POST['date']
                                        : date('Y-m-d H:i')
                                ); ?>"
                                required
                            >
                        </div>
                    <?php endif; ?>

                    <div class="sales-add-payment-field">
                        <label class="sales-add-payment-label" for="reference">
                            <?= html_escape(lang('reference')); ?>
                        </label>

                        <input
                            type="text"
                            name="reference"
                            id="reference"
                            class="form-control"
                            value="<?= html_escape(set_value('reference')); ?>"
                            placeholder="<?= html_escape(lang('reference')); ?>"
                        >
                    </div>

                    <div class="sales-add-payment-field">
                        <label class="sales-add-payment-label" for="amount">
                            <?= html_escape(lang('amount')); ?>
                            <span class="sales-add-payment-required">*</span>
                        </label>

                        <div class="sales-add-payment-amount-wrap">
                            <input
                                type="text"
                                name="amount-paid"
                                id="amount"
                                class="form-control amount"
                                inputmode="decimal"
                                autocomplete="off"
                                value="<?= html_escape(
                                    $this->tec->formatDecimal($balance_amount)
                                ); ?>"
                                data-max-amount="<?= html_escape(
                                    number_format($balance_amount, 4, '.', '')
                                ); ?>"
                                required
                            >
                            <span class="sales-add-payment-currency">MMK</span>
                        </div>

                        <div class="sales-add-payment-error" id="salesPaymentAmountError">
                            <?= $is_myanmar_payment_language
                                ? 'လက်ခံမည့်ပမာဏကို မှန်ကန်စွာထည့်ပါ။'
                                : 'Please enter a valid payment amount.'; ?>
                        </div>
                    </div>

                    <div class="sales-add-payment-field">
                        <label class="sales-add-payment-label" for="paid_by">
                            <?= html_escape(lang('paying_by')); ?>
                            <span class="sales-add-payment-required">*</span>
                        </label>

                        <select
                            name="paid_by"
                            id="paid_by"
                            class="form-control paid_by"
                            required
                        >
                            <option value="cash">Cash</option>
                            <option value="kpay">KBZPay</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="quick_pay">Quick Pay</option>
                            <option value="other"><?= html_escape(lang('other')); ?></option>
                        </select>
                    </div>

                    <div class="sales-add-payment-field is-full">
                        <label class="sales-add-payment-label" for="attachment">
                            <?= $is_myanmar_payment_language
                                ? 'ဘောင်ချာပုံတင်ရန်'
                                : html_escape(lang('attachment')); ?>
                        </label>

                        <input
                            type="file"
                            name="userfile"
                            id="attachment"
                            class="form-control sales-add-payment-file"
                            accept="image/*,.pdf"
                        >

                        <span class="sales-add-payment-help">
                            <?= $is_myanmar_payment_language
                                ? 'ဓာတ်ပုံ သို့မဟုတ် PDF ဖိုင်တင်နိုင်ပါသည်။'
                                : 'You can upload an image or PDF file.'; ?>
                        </span>
                    </div>

                    <div class="sales-add-payment-field is-full">
                        <label class="sales-add-payment-label" for="note">
                            <?= html_escape(lang('note')); ?>
                        </label>

                        <textarea
                            name="note"
                            id="note"
                            class="form-control"
                            placeholder="<?= html_escape(lang('note')); ?>"
                        ><?= html_escape(
                            isset($_POST['note']) ? $_POST['note'] : ''
                        ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer sales-add-payment-footer">
            <button
                type="button"
                class="btn btn-default"
                data-dismiss="modal"
            >
                <i class="fa fa-times"></i>
                <?= $is_myanmar_payment_language
                    ? 'မလုပ်တော့ပါ'
                    : html_escape(lang('close')); ?>
            </button>

            <button
                type="submit"
                name="create"
                value="1"
                class="btn btn-success sales-add-payment-submit"
                <?= $balance_amount <= 0 ? 'disabled' : ''; ?>
            >
                <i class="fa fa-check"></i>
                <?= $is_myanmar_payment_language
                    ? 'ငွေလက်ခံမည်'
                    : html_escape(lang('add_payment')); ?>
            </button>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<script
    src="<?= $assets; ?>plugins/bootstrap-datetimepicker/js/moment.min.js"
    type="text/javascript"
></script>
<script
    src="<?= $assets; ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"
    type="text/javascript"
></script>

<script type="text/javascript" charset="UTF-8">
$(document).ready(function () {
    var $form = $('#salesAddPaymentForm');
    var $amount = $('#amount');
    var $error = $('#salesPaymentAmountError');
    var maxAmount = parseFloat($amount.attr('data-max-amount')) || 0;

    /* Modal ဖွင့်ချိန်တွင် ဘယ်ဘက်အစမှ စပြီး X-scroll မဖန်တီးပါ။ */
    $('.sales-add-payment-modal .modal-body').scrollLeft(0);

    if ($.fn.datetimepicker) {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });
    }

    $amount.on('input', function () {
        var cleaned = String($(this).val() || '')
            .replace(/,/g, '')
            .replace(/[^0-9.]/g, '');

        var firstDot = cleaned.indexOf('.');

        if (firstDot !== -1) {
            cleaned =
                cleaned.substring(0, firstDot + 1) +
                cleaned.substring(firstDot + 1).replace(/\./g, '');
        }

        $(this).val(cleaned);
        $error.hide();
    });

    $form.on('submit', function (event) {
        var amount = parseFloat(
            String($amount.val() || '').replace(/,/g, '')
        );

        if (
            !isFinite(amount) ||
            amount <= 0 ||
            (maxAmount > 0 && amount > maxAmount)
        ) {
            event.preventDefault();
            $error.show();
            $amount.trigger('focus');
            return false;
        }

        var $submit = $form.find('.sales-add-payment-submit');

        if ($submit.prop('disabled')) {
            event.preventDefault();
            return false;
        }

        $submit
            .prop('disabled', true)
            .html(
                '<i class="fa fa-spinner fa-spin"></i> ' +
                <?= json_encode(
                    $is_myanmar_payment_language
                        ? 'သိမ်းနေသည်...'
                        : 'Saving...'
                ); ?>
            );

        return true;
    });
});
</script>
