<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>
<?php
$payment_id = !empty($payment->id) ? (int) $payment->id : 0;
$sale_id = !empty($payment->sale_id) ? (int) $payment->sale_id : 0;

$sale = $sale_id > 0
    ? $this->db->get_where('sales', ['id' => $sale_id])->row()
    : null;

$total_amount = !empty($sale)
    ? (float) ($sale->grand_total ?? ($sale->total ?? 0))
    : 0;
$paid_amount = !empty($sale) ? (float) ($sale->paid ?? 0) : 0;
$balance_amount = max(0, $total_amount - $paid_amount);
$current_payment_amount = !empty($payment)
    ? (float) ($payment->amount ?? 0)
    : 0;

/*
 * Edit လုပ်ချိန်မှာ မူလ payment ပမာဏကို paid ထဲကပြန်နုတ်တွက်ရမည်။
 * ဒါကြောင့် လက်ရှိကျန်ငွေ + မူလ payment ပမာဏအထိ ပြင်နိုင်သည်။
 */
$editable_amount_limit = max(
    $current_payment_amount,
    $balance_amount + $current_payment_amount
);

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
   Sales Edit Payment - Same style as Add Payment
   ========================================================= */
.sales-edit-payment-modal {
    width: 760px;
    max-width: calc(100vw - 24px);
    margin: 24px auto;
    color: #334155;
}

.sales-edit-payment-modal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 15px;
    background: #ffffff;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
}

.sales-edit-payment-header {
    display: flex;
    min-height: 66px;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.sales-edit-payment-title {
    margin: 0;
    color: #1e293b;
    font-size: 20px;
    font-weight: 900;
    line-height: 1.4;
}

.sales-edit-payment-close {
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

.sales-edit-payment-modal .modal-body {
    max-height: calc(100vh - 145px);
    overflow-x: hidden !important;
    overflow-y: auto;
    padding: 18px;
    background: #f8fafc;
}

#salesEditPaymentForm,
.sales-edit-payment-form-card,
.sales-edit-payment-grid,
.sales-edit-payment-field,
.sales-edit-payment-modal .form-group,
.sales-edit-payment-modal .input-group,
.sales-edit-payment-modal .file-input,
.sales-edit-payment-modal .file-caption-main,
.sales-edit-current-voucher {
    min-width: 0 !important;
    max-width: 100% !important;
}

#salesEditPaymentForm {
    width: 100%;
    overflow-x: hidden !important;
}

.sales-edit-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}

.sales-edit-summary-item {
    min-width: 0;
    padding: 14px;
    border: 1px solid #dbe5ef;
    border-radius: 11px;
    background: #ffffff;
}

.sales-edit-summary-item.is-paid {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.sales-edit-summary-item.is-balance {
    border-color: #fed7aa;
    background: #fff7ed;
}

.sales-edit-summary-label {
    display: block;
    margin-bottom: 5px;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
}

.sales-edit-summary-value {
    display: block;
    overflow: hidden;
    color: #1e293b;
    font-size: 18px;
    font-weight: 900;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sales-edit-summary-item.is-paid .sales-edit-summary-value {
    color: #15803d;
}

.sales-edit-summary-item.is-balance .sales-edit-summary-value {
    color: #ea580c;
}

.sales-edit-payment-form-card {
    padding: 16px;
    border: 1px solid #dbe5ef;
    border-radius: 12px;
    background: #ffffff;
}

.sales-edit-payment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.sales-edit-payment-field {
    min-width: 0;
}

.sales-edit-payment-field.is-full {
    grid-column: 1 / -1;
}

.sales-edit-payment-label {
    display: block;
    margin-bottom: 7px;
    color: #334155;
    font-size: 14px;
    font-weight: 900;
}

.sales-edit-payment-required {
    color: #dc2626;
}

.sales-edit-payment-modal .form-control {
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

.sales-edit-payment-modal textarea.form-control {
    height: 82px;
    min-height: 82px;
    resize: vertical;
}

.sales-edit-payment-modal .form-control:focus {
    border-color: #0f8a94;
    box-shadow: 0 0 0 3px rgba(15, 138, 148, .12) !important;
}

.sales-edit-payment-amount-wrap {
    position: relative;
}

.sales-edit-payment-amount-wrap .form-control {
    padding-right: 52px;
    font-size: 18px;
    font-weight: 900;
    text-align: right;
}

.sales-edit-payment-currency {
    position: absolute;
    top: 50%;
    right: 12px;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    transform: translateY(-50%);
    pointer-events: none;
}

.sales-edit-payment-file {
    min-height: 44px;
    height: auto !important;
    padding: 7px 9px !important;
}

.sales-edit-payment-help {
    display: block;
    margin-top: 6px;
    color: #94a3b8;
    font-size: 11px;
    line-height: 1.5;
}

.sales-edit-current-voucher {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 9px;
    padding: 9px 11px;
    border: 1px solid #99f6e4;
    border-radius: 9px;
    background: #f0fdfa;
}

.sales-edit-current-voucher-label {
    min-width: 0;
    overflow: hidden;
    color: #0f766e;
    font-size: 12px;
    font-weight: 800;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sales-edit-current-voucher-button {
    display: inline-flex;
    min-height: 34px;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 6px 10px;
    border: 1px solid #5eead4;
    border-radius: 7px;
    background: #ffffff;
    color: #0f766e !important;
    font-size: 11px;
    font-weight: 900;
    text-decoration: none !important;
    white-space: nowrap;
}

.sales-edit-payment-error {
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

.sales-edit-payment-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 13px 18px;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
}

.sales-edit-payment-footer .btn {
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

.sales-edit-payment-submit {
    min-width: 170px;
    border-color: #2563eb !important;
    background: #2563eb !important;
    color: #ffffff !important;
}

.sales-edit-payment-submit:hover,
.sales-edit-payment-submit:focus {
    background: #1d4ed8 !important;
}

/* Existing voucher stays inside the mobile app. */
.sales-edit-voucher-viewer {
    position: fixed;
    inset: 0;
    z-index: 13000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.sales-edit-voucher-viewer.is-open {
    display: flex;
}

.sales-edit-voucher-backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    background: rgba(15, 23, 42, .82);
}

.sales-edit-voucher-panel {
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

.sales-edit-voucher-header {
    display: flex;
    min-height: 58px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px 10px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.sales-edit-voucher-title {
    margin: 0;
    color: #1e293b;
    font-size: 17px;
    font-weight: 900;
}

.sales-edit-voucher-close {
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

.sales-edit-voucher-body {
    display: flex;
    min-height: 220px;
    max-height: calc(100vh - 90px);
    align-items: center;
    justify-content: center;
    overflow: auto;
    padding: 10px;
    background: #111827;
}

.sales-edit-voucher-image,
.sales-edit-voucher-frame {
    display: none;
    max-width: 100%;
    border: 0;
    background: #ffffff;
}

.sales-edit-voucher-image {
    width: auto;
    height: auto;
    max-height: calc(100vh - 115px);
    object-fit: contain;
}

.sales-edit-voucher-frame {
    width: 100%;
    height: calc(100vh - 115px);
}

.sales-edit-voucher-error {
    display: none;
    padding: 18px;
    color: #ffffff;
    font-size: 16px;
    font-weight: 800;
    text-align: center;
}

@media (max-width: 767px) {
    .sales-edit-payment-modal {
        width: calc(100vw - 12px);
        max-width: calc(100vw - 12px);
        margin: 6px auto;
    }

    .sales-edit-payment-header {
        min-height: 56px;
        padding: 9px 11px;
    }

    .sales-edit-payment-title {
        font-size: 16px;
    }

    .sales-edit-payment-close {
        width: 38px;
        height: 38px;
    }

    .sales-edit-payment-modal .modal-body {
        max-height: calc(100vh - 125px);
        padding: 10px;
    }

    .sales-edit-summary-grid {
        gap: 6px;
        margin-bottom: 10px;
    }

    .sales-edit-summary-item {
        padding: 10px 7px;
        border-radius: 8px;
    }

    .sales-edit-summary-label {
        min-height: 34px;
        margin-bottom: 3px;
        font-size: 10px;
        line-height: 1.5;
    }

    .sales-edit-summary-value {
        font-size: 13px;
    }

    .sales-edit-payment-form-card {
        padding: 11px;
    }

    .sales-edit-payment-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .sales-edit-payment-field.is-full {
        grid-column: auto;
    }

    .sales-edit-payment-label {
        margin-bottom: 5px;
        font-size: 12px;
    }

    .sales-edit-payment-modal .form-control {
        height: 42px;
        font-size: 14px;
    }

    .sales-edit-payment-modal textarea.form-control {
        height: 68px;
        min-height: 68px;
    }

    .sales-edit-current-voucher {
        align-items: flex-start;
    }

    .sales-edit-payment-footer {
        display: grid;
        grid-template-columns: 1fr 1.35fr;
        gap: 8px;
        padding: 10px;
    }

    .sales-edit-payment-footer .btn,
    .sales-edit-payment-submit {
        width: 100%;
        min-width: 0;
        min-height: 43px;
        padding: 8px 7px;
        font-size: 12px;
    }

    .sales-edit-voucher-viewer {
        padding: 7px;
    }
}

.sales-edit-payment-modal,
.sales-edit-payment-modal *,
.sales-edit-voucher-viewer,
.sales-edit-voucher-viewer * {
    box-sizing: border-box;
    font-family: "Pyidaungsu", "Noto Sans Myanmar", "Myanmar Text", Arial, sans-serif;
}

/* Myanmar font rule က Font Awesome glyph ကို မလွှမ်းစေရန် */
.sales-edit-payment-modal .fa,
.sales-edit-payment-modal .fas,
.sales-edit-payment-modal .far,
.sales-edit-payment-modal .fab,
.sales-edit-payment-modal .fa-solid,
.sales-edit-payment-modal .fa-regular,
.sales-edit-payment-modal .fa-brands,
.sales-edit-voucher-viewer .fa,
.sales-edit-voucher-viewer .fas,
.sales-edit-voucher-viewer .far,
.sales-edit-voucher-viewer .fab,
.sales-edit-voucher-viewer .fa-solid,
.sales-edit-voucher-viewer .fa-regular,
.sales-edit-voucher-viewer .fa-brands {
    font-family: var(--fa-style-family, "Font Awesome 6 Free"), FontAwesome !important;
    font-style: normal !important;
    font-weight: var(--fa-style, 900) !important;
    speak: never;
}
</style>

<div class="modal-dialog sales-edit-payment-modal" role="document">
    <div class="modal-content">
        <?= form_open_multipart(
            'sales/edit_payment/' . $payment_id . '/' . $sale_id,
            ['id' => 'salesEditPaymentForm']
        ); ?>

        <div class="modal-header sales-edit-payment-header">
            <h4 class="sales-edit-payment-title" id="myModalLabel">
                <?= $is_myanmar_payment_language
                    ? 'ငွေပေးချေမှု ပြင်ရန်'
                    : html_escape(lang('edit_payment')); ?>
            </h4>

            <button
                type="button"
                class="sales-edit-payment-close"
                data-dismiss="modal"
                title="<?= html_escape(lang('close')); ?>"
                aria-label="<?= html_escape(lang('close')); ?>"
            >
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="sales-edit-summary-grid">
                <div class="sales-edit-summary-item">
                    <span class="sales-edit-summary-label">
                        <?= html_escape(lang('grand_total')); ?>
                    </span>
                    <span class="sales-edit-summary-value">
                        <?= $this->tec->formatMoney($total_amount); ?>
                    </span>
                </div>

                <div class="sales-edit-summary-item is-paid">
                    <span class="sales-edit-summary-label">
                        <?= $is_myanmar_payment_language
                            ? 'လက်ခံပြီး'
                            : html_escape(lang('paid')); ?>
                    </span>
                    <span class="sales-edit-summary-value">
                        <?= $this->tec->formatMoney($paid_amount); ?>
                    </span>
                </div>

                <div class="sales-edit-summary-item is-balance">
                    <span class="sales-edit-summary-label">
                        <?= $is_myanmar_payment_language
                            ? 'လက်ခံရန်ကျန်ငွေ'
                            : html_escape(lang('pay_balance')); ?>
                    </span>
                    <span class="sales-edit-summary-value">
                        <?= $this->tec->formatMoney($balance_amount); ?>
                    </span>
                </div>
            </div>

            <input type="hidden" name="sale_id" value="<?= $sale_id; ?>">

            <div class="sales-edit-payment-form-card">
                <div class="sales-edit-payment-grid">
                    <?php if ($Admin): ?>
                        <div class="sales-edit-payment-field">
                            <label class="sales-edit-payment-label" for="date">
                                <?= html_escape(lang('date')); ?>
                                <span class="sales-edit-payment-required">*</span>
                            </label>

                            <input
                                type="text"
                                name="date"
                                id="date"
                                class="form-control datetimepicker"
                                value="<?= html_escape(
                                    isset($_POST['date'])
                                        ? $_POST['date']
                                        : ($payment->date ?? '')
                                ); ?>"
                                required
                            >
                        </div>
                    <?php endif; ?>

                    <div class="sales-edit-payment-field">
                        <label class="sales-edit-payment-label" for="reference">
                            <?= html_escape(lang('reference')); ?>
                        </label>

                        <input
                            type="text"
                            name="reference"
                            id="reference"
                            class="form-control"
                            value="<?= html_escape(
                                isset($_POST['reference'])
                                    ? $_POST['reference']
                                    : ($payment->reference ?? '')
                            ); ?>"
                        >
                    </div>

                    <div class="sales-edit-payment-field">
                        <label class="sales-edit-payment-label" for="amount">
                            <?= html_escape(lang('amount')); ?>
                            <span class="sales-edit-payment-required">*</span>
                        </label>

                        <div class="sales-edit-payment-amount-wrap">
                            <input
                                type="text"
                                name="amount-paid"
                                id="amount"
                                class="form-control amount"
                                inputmode="decimal"
                                autocomplete="off"
                                value="<?= html_escape(
                                    isset($_POST['amount-paid'])
                                        ? $_POST['amount-paid']
                                        : $this->tec->formatDecimal(
                                            $current_payment_amount
                                        )
                                ); ?>"
                                data-max-amount="<?= html_escape(
                                    number_format(
                                        $editable_amount_limit,
                                        4,
                                        '.',
                                        ''
                                    )
                                ); ?>"
                                required
                            >
                            <span class="sales-edit-payment-currency">MMK</span>
                        </div>

                        <div class="sales-edit-payment-error" id="salesEditPaymentAmountError">
                            <?= $is_myanmar_payment_language
                                ? 'ပမာဏကို မှန်ကန်စွာထည့်ပါ။ ကျန်ငွေထက်မကျော်ရပါ။'
                                : 'Please enter a valid amount within the available balance.'; ?>
                        </div>
                    </div>

                    <div class="sales-edit-payment-field">
                        <label class="sales-edit-payment-label" for="paid_by">
                            <?= html_escape(lang('paying_by')); ?>
                            <span class="sales-edit-payment-required">*</span>
                        </label>

                        <select
                            name="paid_by"
                            id="paid_by"
                            class="form-control paid_by"
                            required
                        >
                            <option
                                value="cash"
                                <?= ($payment->paid_by ?? '') === 'cash' ? 'selected' : ''; ?>
                            >Cash</option>
                            <option
                                value="kpay"
                                <?= ($payment->paid_by ?? '') === 'kpay' ? 'selected' : ''; ?>
                            >KBZPay</option>
                            <option
                                value="mobile_banking"
                                <?= ($payment->paid_by ?? '') === 'mobile_banking' ? 'selected' : ''; ?>
                            >Mobile Banking</option>
                            <option
                                value="quick_pay"
                                <?= ($payment->paid_by ?? '') === 'quick_pay' ? 'selected' : ''; ?>
                            >Quick Pay</option>
                            <option
                                value="other"
                                <?= ($payment->paid_by ?? '') === 'other' ? 'selected' : ''; ?>
                            ><?= html_escape(lang('other')); ?></option>
                        </select>
                    </div>

                    <div class="sales-edit-payment-field is-full">
                        <label class="sales-edit-payment-label" for="attachment">
                            <?= $is_myanmar_payment_language
                                ? 'ဘောင်ချာပုံ'
                                : html_escape(lang('attachment')); ?>
                        </label>

                        <?php if (!empty($payment->attachment)): ?>
                            <div class="sales-edit-current-voucher">
                                <span class="sales-edit-current-voucher-label">
                                    <i class="fa fa-paperclip"></i>
                                    <?= html_escape($payment->attachment); ?>
                                </span>

                                <a
                                    href="<?= html_escape(base_url(
                                        'files/' . rawurlencode(
                                            $payment->attachment
                                        )
                                    )); ?>"
                                    class="sales-edit-current-voucher-button"
                                    data-sales-edit-voucher="1"
                                >
                                    <i class="fa fa-eye"></i>
                                    <span>
                                        <?= $is_myanmar_payment_language
                                            ? 'ကြည့်ရန်'
                                            : 'View'; ?>
                                    </span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <input
                            type="file"
                            name="userfile"
                            id="attachment"
                            class="form-control sales-edit-payment-file"
                            accept="image/*,.pdf"
                        >

                        <span class="sales-edit-payment-help">
                            <?= $is_myanmar_payment_language
                                ? 'ဖိုင်အသစ်ရွေးမှသာ မူလဘောင်ချာပုံကို အစားထိုးပါမည်။'
                                : 'Choose a new file only when replacing the existing voucher.'; ?>
                        </span>
                    </div>

                    <div class="sales-edit-payment-field is-full">
                        <label class="sales-edit-payment-label" for="note">
                            <?= html_escape(lang('note')); ?>
                        </label>

                        <textarea
                            name="note"
                            id="note"
                            class="form-control"
                        ><?= html_escape(
                            isset($_POST['note'])
                                ? $_POST['note']
                                : ($payment->note ?? '')
                        ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer sales-edit-payment-footer">
            <button
                type="button"
                class="btn btn-default"
                data-dismiss="modal"
            >
                <i class="fa fa-times"></i>
                <?= $is_myanmar_payment_language
                    ? 'မပြင်တော့ပါ'
                    : html_escape(lang('close')); ?>
            </button>

            <button
                type="submit"
                name="update"
                value="1"
                class="btn btn-primary sales-edit-payment-submit"
            >
                <i class="fa fa-save"></i>
                <?= $is_myanmar_payment_language
                    ? 'ပြင်ဆင်ချက်သိမ်းမည်'
                    : html_escape(lang('update')); ?>
            </button>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<?php if (!empty($payment->attachment)): ?>
    <div
        id="salesEditPaymentVoucherViewer"
        class="sales-edit-voucher-viewer"
        aria-hidden="true"
    >
        <button
            type="button"
            class="sales-edit-voucher-backdrop"
            data-sales-edit-voucher-close="1"
            aria-label="<?= html_escape(lang('close')); ?>"
        ></button>

        <div
            class="sales-edit-voucher-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="salesEditVoucherTitle"
        >
            <div class="sales-edit-voucher-header">
                <h4 class="sales-edit-voucher-title" id="salesEditVoucherTitle">
                    <?= $is_myanmar_payment_language ? 'ဘောင်ချာပုံ' : 'Voucher'; ?>
                </h4>

                <button
                    type="button"
                    class="sales-edit-voucher-close"
                    data-sales-edit-voucher-close="1"
                >
                    <i class="fa fa-times"></i>
                    <span>
                        <?= $is_myanmar_payment_language
                            ? 'ပိတ်ရန်'
                            : html_escape(lang('close')); ?>
                    </span>
                </button>
            </div>

            <div class="sales-edit-voucher-body">
                <img
                    id="salesEditVoucherImage"
                    class="sales-edit-voucher-image"
                    src=""
                    alt="<?= $is_myanmar_payment_language ? 'ဘောင်ချာပုံ' : 'Voucher'; ?>"
                >

                <iframe
                    id="salesEditVoucherFrame"
                    class="sales-edit-voucher-frame"
                    src="about:blank"
                    title="<?= $is_myanmar_payment_language ? 'ဘောင်ချာဖိုင်' : 'Voucher file'; ?>"
                ></iframe>

                <div
                    id="salesEditVoucherError"
                    class="sales-edit-voucher-error"
                >
                    <?= $is_myanmar_payment_language
                        ? 'ဘောင်ချာပုံကို ဖွင့်၍မရပါ။'
                        : 'The voucher could not be opened.'; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

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
    var $form = $('#salesEditPaymentForm');
    var $amount = $('#amount');
    var $error = $('#salesEditPaymentAmountError');
    var maxAmount = parseFloat($amount.attr('data-max-amount')) || 0;

    /* Modal ဖွင့်ချိန်တွင် ဘယ်ဘက်အစမှ စပြီး X-scroll မဖန်တီးပါ။ */
    $('.sales-edit-payment-modal .modal-body').scrollLeft(0);

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

        var $submit = $form.find('.sales-edit-payment-submit');

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

    function closeSalesEditVoucher() {
        $('#salesEditPaymentVoucherViewer')
            .removeClass('is-open')
            .attr('aria-hidden', 'true');

        $('#salesEditVoucherImage').hide().attr('src', '');
        $('#salesEditVoucherFrame').hide().attr('src', 'about:blank');
        $('#salesEditVoucherError').hide();
    }

    $(document)
        .off('click.salesEditVoucher', '[data-sales-edit-voucher]')
        .on('click.salesEditVoucher', '[data-sales-edit-voucher]', function (event) {
            var href = $(this).attr('href') || '';
            var $image = $('#salesEditVoucherImage');
            var $frame = $('#salesEditVoucherFrame');
            var $error = $('#salesEditVoucherError');

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
                    .off('error.salesEditVoucher')
                    .on('error.salesEditVoucher', function () {
                        $(this).hide();
                        $error.show();
                    })
                    .attr('src', href)
                    .show();
            }

            $('#salesEditPaymentVoucherViewer')
                .addClass('is-open')
                .attr('aria-hidden', 'false');

            return false;
        });

    $(document)
        .off('click.salesEditVoucherClose', '[data-sales-edit-voucher-close]')
        .on(
            'click.salesEditVoucherClose',
            '[data-sales-edit-voucher-close]',
            closeSalesEditVoucher
        );

    $(document)
        .off('keydown.salesEditVoucher')
        .on('keydown.salesEditVoucher', function (event) {
            if (
                event.key === 'Escape' &&
                $('#salesEditPaymentVoucherViewer').hasClass('is-open')
            ) {
                closeSalesEditVoucher();
            }
        });
});
</script>
