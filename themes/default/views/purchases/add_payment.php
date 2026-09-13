<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$total_amount = (float) $inv->total;
$total_paid   = (float) $inv->paid;
$due_amount   = $total_amount - $total_paid;

if ($due_amount < 0) {
    $due_amount = 0;
}
?>

<style>
/* =========================================================
   KLSPOS - Add Purchase Payment
   ========================================================= */

.add-payment-modal {
    width: 760px;
    max-width: calc(100% - 30px);
    margin: 25px auto;
}

.add-payment-modal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 20px 55px rgba(15, 23, 42, 0.22);
}

.add-payment-modal .modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.add-payment-modal .modal-title {
    color: #1e293b;
    font-size: 18px;
    font-weight: 700;
}

.add-payment-modal .close {
    margin-top: 1px;
    color: #64748b;
    opacity: 1;
}

.add-payment-modal .modal-body {
    padding: 18px 20px 8px;
    background: #ffffff;
}

.add-payment-modal .modal-footer {
    padding: 12px 20px 18px;
    border-top: 0;
    background: #ffffff;
}

.add-payment-modal .payment-enter-info {
    margin: 0 0 14px;
    color: #64748b;
    font-size: 16px;
}

/* =========================================================
   Summary - 3 Cards
   ========================================================= */

.add-payment-modal .payment-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin: 4px 0 18px;
}

.add-payment-modal .payment-summary-card {
    min-width: 0;
    padding: 13px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
}

.add-payment-modal .payment-summary-card.paid {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.add-payment-modal .payment-summary-card.due {
    border-color: #fecaca;
    background: #fef2f2;
}

.add-payment-modal .payment-summary-label {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 16px;
    font-weight: 600;
    white-space: nowrap;
}

.add-payment-modal .payment-summary-value {
    display: block;
    color: #1e293b;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.35;
    white-space: nowrap;
}

.add-payment-modal .payment-summary-card.paid .payment-summary-value {
    color: #16a34a;
}

.add-payment-modal .payment-summary-card.due .payment-summary-value {
    color: #dc2626;
}

/* =========================================================
   Main Payment Row
   No surrounding panel / well / border
   ========================================================= */

.add-payment-modal .payment-fields-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}

.add-payment-modal .payment-fields-row .form-group {
    min-width: 0;
    margin-bottom: 0;
}

.add-payment-modal .form-group {
    margin-bottom: 14px;
}

.add-payment-modal .form-group label,
.add-payment-modal .field-label {
    display: block;
    margin-bottom: 6px;
    color: #334155;
    font-size: 16px;
    font-weight: 700;
}

.add-payment-modal .form-control {
    height: 40px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    box-shadow: none;
    font-size: 16px;
}

.add-payment-modal .form-control:focus {
    border-color: #94a3b8;
    box-shadow: none;
}

.add-payment-modal input[readonly] {
    background: #f8fafc;
    color: #475569;
}

.add-payment-modal textarea.form-control {
    height: auto;
    min-height: 105px;
}

.add-payment-modal .select2-container {
    width: 100% !important;
}

.add-payment-modal .select2-container .select2-choice,
.add-payment-modal .select2-container--default .select2-selection--single {
    min-height: 40px;
    border-color: #cbd5e1;
    border-radius: 7px;
}

/* Hidden payment-type extra fields */
.add-payment-modal .gc,
.add-payment-modal .pcc,
.add-payment-modal .pcheque {
    margin-top: 10px;
}

/* Attachment */
.add-payment-modal .voucher-upload .form-control {
    height: auto;
    min-height: 40px;
    padding: 6px 10px;
}

/* Submit */
.add-payment-modal .btn-pay {
    min-width: 120px;
    min-height: 40px;
    padding: 8px 20px;
    border-color: #0f766e;
    border-radius: 7px;
    background: #0f766e;
    color: #ffffff;
    font-weight: 700;
}

.add-payment-modal .btn-pay:hover,
.add-payment-modal .btn-pay:focus {
    border-color: #0d5f59;
    background: #0d5f59;
    color: #ffffff;
}

/* Myanmar Font */
.add-payment-modal,
.add-payment-modal input,
.add-payment-modal select,
.add-payment-modal textarea,
.add-payment-modal button {
    font-family:
        "Pyidaungsu",
        "Noto Sans Myanmar",
        "Myanmar Text",
        Arial,
        sans-serif;
}

/* =========================================================
   Tablet / Mobile
   Keep both requested groups on one row
   ========================================================= */

@media (max-width: 767px) {
    .add-payment-modal {
        width: calc(100% - 20px);
        max-width: none;
        margin: 10px auto;
    }

    .add-payment-modal .modal-body {
        padding: 14px 12px 6px;
    }

    .add-payment-modal .modal-header,
    .add-payment-modal .modal-footer {
        padding-left: 12px;
        padding-right: 12px;
    }

    .add-payment-modal .payment-summary-grid,
    .add-payment-modal .payment-fields-row {
        gap: 6px;
    }

    .add-payment-modal .payment-summary-card {
        padding: 10px 8px;
    }

    .add-payment-modal .payment-summary-label,
    .add-payment-modal .form-group label,
    .add-payment-modal .field-label {
        font-size: 11px;
    }

    .add-payment-modal .payment-summary-value {
        font-size: 16px;
    }

    .add-payment-modal .payment-fields-row .form-control {
        padding-left: 7px;
        padding-right: 7px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .add-payment-modal .payment-summary-label {
        font-size: 10px;
    }

    .add-payment-modal .payment-summary-value {
        font-size: 16px;
    }

    .add-payment-modal .payment-fields-row {
        grid-template-columns: 1fr 1fr 1fr;
    }
}
</style>

<div class="modal-dialog add-payment-modal" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-times"></i>
            </button>

            <h4 class="modal-title" id="myModalLabel">
                <?= html_escape(lang('add_payment')); ?>
            </h4>
        </div>

        <?= form_open_multipart(
            "purchases/add_payment/" . (int) $inv->id . "/" . (int) $inv->supplier_id
        ); ?>

        <div class="modal-body">

            <p class="payment-enter-info">
                <?= html_escape(lang('enter_info')); ?>
            </p>

            <?php if ($Admin) { ?>
                <div class="form-group" style="max-width: 300px;">
                    <?= lang("date", "date"); ?>
                    <?= form_input(
                        'date',
                        (isset($_POST['date']) ? $_POST['date'] : date('Y-m-d H:i')),
                        'class="form-control datetimepicker" id="date" required="required"'
                    ); ?>
                </div>
            <?php } ?>

            <input
                type="hidden"
                value="<?= (int) $inv->id; ?>"
                name="sale_id"
            />

            <!-- =====================================================
                 စုစုပေါင်း | ပေးဆောင်ပြီး | ပေးရန်ကျန်ငွေ
                 ===================================================== -->
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

            <!-- =====================================================
                 အကြွေးစုစုပေါင်း | ပေးချေမည့်ပမာဏ | ပေးချေမည့်နည်းလမ်း
                 One row, no surrounding well / border
                 ===================================================== -->
            <div id="payments">

                <div class="payment-fields-row">

                    <div class="form-group">
                        <label for="total_due">
                            အကြွေးစုစုပေါင်း
                        </label>

                        <input
                            type="text"
                            id="total_due"
                            value="<?= html_escape(
                                $this->tec->formatDecimal($due_amount)
                            ); ?>"
                            class="form-control"
                            readonly="readonly"
                        />
                    </div>

                    <div class="form-group">
                        <label for="amount">
                            ပေးချေမည့်ပမာဏ
                        </label>

                        <input
                            name="amount-paid"
                            type="text"
                            id="amount"
                            value="<?= $due_amount > 0
                                ? html_escape($this->tec->formatDecimal($due_amount))
                                : '0'; ?>"
                            class="pa form-control kb-pad amount"
                            inputmode="decimal"
                            autocomplete="off"
                            pattern="[0-9]+([.][0-9]{1,2})?"
                            required="required"
                        />
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
                            <option value="cash"><?= html_escape(lang("cash")); ?></option>
                            <option value="other"><?= html_escape(lang("other")); ?></option>
                        </select>
                    </div>

                </div>

                <!-- Existing optional payment fields kept for compatibility -->
                <div class="form-group gc" style="display: none;">
                    <?= lang("gift_card_no", "gift_card_no"); ?>

                    <input
                        name="gift_card_no"
                        type="text"
                        id="gift_card_no"
                        class="pa form-control kb-pad"
                    />

                    <div id="gc_details"></div>
                </div>

                <div class="pcc" style="display:none;">

                    <div class="form-group">
                        <input
                            type="text"
                            id="swipe"
                            class="form-control swipe swipe_input"
                            placeholder="<?= html_escape(lang('focus_swipe_here')); ?>"
                        />
                    </div>

                    <div class="row">

                        <div class="col-sm-6">
                            <div class="form-group">
                                <input
                                    name="pcc_no"
                                    type="text"
                                    id="pcc_no"
                                    class="form-control"
                                    placeholder="<?= html_escape(lang('cc_no')); ?>"
                                />
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <input
                                    name="pcc_holder"
                                    type="text"
                                    id="pcc_holder"
                                    class="form-control"
                                    placeholder="<?= html_escape(lang('cc_holder')); ?>"
                                />
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <select
                                    name="pcc_type"
                                    id="pcc_type"
                                    class="form-control pcc_type select2"
                                    style="width:100%"
                                >
                                    <option value="Visa"><?= html_escape(lang("Visa")); ?></option>
                                    <option value="MasterCard"><?= html_escape(lang("MasterCard")); ?></option>
                                    <option value="Amex"><?= html_escape(lang("Amex")); ?></option>
                                    <option value="Discover"><?= html_escape(lang("Discover")); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <input
                                    name="pcc_month"
                                    type="text"
                                    id="pcc_month"
                                    class="form-control"
                                    placeholder="<?= html_escape(lang('month')); ?>"
                                />
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <input
                                    name="pcc_year"
                                    type="text"
                                    id="pcc_year"
                                    class="form-control"
                                    placeholder="<?= html_escape(lang('year')); ?>"
                                />
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <input
                                    name="pcc_ccv"
                                    type="text"
                                    id="pcc_cvv2"
                                    class="form-control"
                                    placeholder="<?= html_escape(lang('cvv2')); ?>"
                                />
                            </div>
                        </div>

                    </div>
                </div>

                <div class="pcheque" style="display:none;">
                    <div class="form-group">
                        <?= lang("cheque_no", "cheque_no"); ?>

                        <input
                            name="cheque_no"
                            type="text"
                            id="cheque_no"
                            class="form-control cheque_no"
                        />
                    </div>
                </div>

            </div>

            <!-- Voucher Image -->
            <div class="form-group voucher-upload">
                <label for="attachment">
                    ဘောင်ချာပုံတင်ရန်
                </label>

                <input
                    id="attachment"
                    type="file"
                    name="userfile"
                    class="form-control file"
                    accept="image/*"
                >
            </div>

            <!-- Note -->
            <div class="form-group">
                <?= lang("note", "note"); ?>

                <?= form_textarea(
                    'note',
                    (isset($_POST['note']) ? $_POST['note'] : ""),
                    'class="form-control redactor" id="note"'
                ); ?>
            </div>

        </div>

        <div class="modal-footer">
            <button
                type="submit"
                name="create"
                value="1"
                class="btn btn-primary btn-pay"
            >
                ပေးမည်
            </button>
        </div>

        <?= form_close(); ?>

    </div>
</div>

<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {

        /*
         * Payment amount:
         * - numbers only
         * - allow one decimal point
         * - maximum 2 decimal places
         * - pasted text is cleaned too
         */
        $(document)
            .off('input.paymentAmount', '#amount')
            .on('input.paymentAmount', '#amount', function () {
                var value = String($(this).val() || '');

                // Keep digits and decimal point only
                value = value.replace(/[^0-9.]/g, '');

                // Allow only one decimal point
                var firstDot = value.indexOf('.');
                if (firstDot !== -1) {
                    value =
                        value.substring(0, firstDot + 1) +
                        value.substring(firstDot + 1).replace(/\./g, '');
                }

                // Limit to 2 decimal places
                if (value.indexOf('.') !== -1) {
                    var parts = value.split('.');
                    parts[1] = (parts[1] || '').substring(0, 2);
                    value = parts[0] + '.' + parts[1];
                }

                $(this).val(value);
            });

        // Prevent common non-numeric number-input characters
        $(document)
            .off('keydown.paymentAmount', '#amount')
            .on('keydown.paymentAmount', '#amount', function (e) {
                if (
                    e.ctrlKey || e.metaKey ||
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

                if (e.key === '.' && $(this).val().indexOf('.') === -1) {
                    return;
                }

                e.preventDefault();
            });

        $('#gift_card_no').inputmask("9999 9999 9999 9999");
        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val();
            if (p_val == 'gift_card') {
                $('.gc').slideDown();
                $('.ngc').slideUp('fast');
                setTimeout(function(){ $('#gift_card_no').focus(); }, 10);
                $('#amount').attr('readonly', true);
            } else {
                $('.ngc').slideDown();
                $('.gc').slideUp('fast');
                $('#gc_details').html('');
                $('#amount').attr('readonly', false);
            }
            if (p_val == 'cash' || p_val == 'other') {
                $('.pcash').slideDown();
                $('.pcheque').slideUp('fast');
                $('.pcc').slideUp('fast');
                setTimeout(function(){ $('#amount').focus(); }, 10);
            } else if (p_val == 'CC' || p_val == 'stripe') {
                $('.pcc').slideDown();
                $('.pcheque').slideUp('fast');
                $('.pcash').slideUp('fast');
                setTimeout(function(){ $('#swipe').val('').focus(); }, 10);
            } else if (p_val == 'Cheque') {
                $('.pcheque').slideDown();
                $('.pcc').slideUp('fast');
                $('.pcash').slideUp('fast');
                setTimeout(function(){ $('#cheque_no').focus(); }, 10);
            } else {
                $('.pcheque').hide();
                $('.pcc').hide();
                $('.pcash').hide();
            }
        });

        $(document).on('change', '#gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: base_url + "pos/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            bootbox.alert('<?= lang('incorrect_gift_card'); ?>');
                        } else {
                            $('#gc_details').html('<?= lang('card_no'); ?>: ' + data.card_no + '<br><?= lang('value'); ?>: ' + data.value + '<?= lang('balance'); ?>: ' + data.balance);
                            var g_total = <?= $this->tec->formatDecimal((float) $inv->total - (float) $inv->paid); ?>;
                            $('#amount').val((g_total > data.balance) ? data.balance : g_total).change().focus();
                        }
                    }
                });
            }
            return false;
        });

        $('.swipe').keypress( function (e) {
            var TrackData = $(this).val() ? $(this).val() : '';
            if (TrackData != '') {
                if (e.keyCode == 13) {
                    e.preventDefault();
                    var p = new SwipeParserObj(TrackData);

                    if (p.hasTrack1)
                    {

                        var CardType = null;
                        var ccn1 = p.account.charAt(0);
                        if (ccn1 == 4)
                            CardType = 'Visa';
                        else if (ccn1 == 5)
                            CardType = 'MasterCard';
                        else if (ccn1 == 3)
                            CardType = 'Amex';
                        else if (ccn1 == 6)
                            CardType = 'Discover';
                        else
                            CardType = 'Visa';

                        $('#pcc_no').val(p.account).change();
                        $('#pcc_holder').val(p.account_name).change();
                        $('#pcc_month').val(p.exp_month).change();
                        $('#pcc_year').val(p.exp_year).change();
                        $('#pcc_cvv2').val('');
                        $('#pcc_type').select2('val', CardType);

                    } else {
                        $('#pcc_no').val('').change();
                        $('#pcc_holder').val('').change();
                        $('#pcc_month').val('').change();
                        $('#pcc_year').val('').change();
                        $('#pcc_cvv2').val('').change();
                        $('#pcc_type').val('').change();
                    }

                    $('#pcc_cvv2').focus();
                }
            }

        }).blur(function (e) {
            $(this).val('');
        }).focus( function (e) {
            $(this).val('');
        });

        $('#pcc_no').change(function (e) {
            var cn = $(this).val();
            var ccn1 = cn.charAt(0);
            if (ccn1 == 4)
                CardType = 'Visa';
            else if (ccn1 == 5)
                CardType = 'MasterCard';
            else if (ccn1 == 3)
                CardType = 'Amex';
            else if (ccn1 == 6)
                CardType = 'Discover';
            else
                CardType = 'Visa';

            $('#pcc_type').select2('val', CardType);
        });

    });
</script>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });
    });
</script>
