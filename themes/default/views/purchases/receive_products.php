<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$purchase_id = (int) $purchase->id;
$total_ordered = 0;
$total_received = 0;
$total_remaining = 0;

foreach ($items as $row) {
    $ordered = (float) ($row->primary_qty ?? 0);
    $received = (float) ($row->received_primary_qty ?? 0);
    $remaining = max(0, $ordered - $received);

    $total_ordered += $ordered;
    $total_received += $received;
    $total_remaining += $remaining;
}
?>

<style>
.partial-receive-modal {
    width: 900px;
    max-width: calc(100% - 24px);
    margin: 18px auto;
}
.partial-receive-modal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 20px 55px rgba(15, 23, 42, .25);
}
.partial-receive-modal .modal-header {
    padding: 15px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #fff;
}
.partial-receive-modal .modal-title {
    margin: 0;
    color: #1e293b;
    font-size: 18px;
    font-weight: 800;
}
.partial-receive-modal .modal-body {
    padding: 16px 18px 8px;
    background: #f8fafc;
}
.partial-receive-modal .receive-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin-bottom: 12px;
}
.partial-receive-modal .receive-meta-card {
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #fff;
}
.partial-receive-modal .receive-meta-card.received {
    border-color: #bbf7d0;
    background: #f0fdf4;
}
.partial-receive-modal .receive-meta-card.remaining {
    border-color: #fed7aa;
    background: #fff7ed;
}
.partial-receive-modal .receive-meta-label {
    display: block;
    margin-bottom: 3px;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
}
.partial-receive-modal .receive-meta-value {
    color: #1e293b;
    font-size: 17px;
    font-weight: 900;
}
.partial-receive-modal .receive-info-line {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 12px;
    padding: 10px 12px;
    border-radius: 9px;
    background: #eef6ff;
    color: #334155;
    font-size: 12px;
}
.partial-receive-modal .receive-date-row {
    display: grid;
    grid-template-columns: minmax(180px, 260px) 1fr;
    gap: 10px;
    margin-bottom: 12px;
}
.partial-receive-modal label {
    display: block;
    margin-bottom: 5px;
    color: #475569;
    font-size: 12px;
    font-weight: 800;
}
.partial-receive-modal .form-control {
    height: 39px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    box-shadow: none;
}
.partial-receive-modal textarea.form-control {
    height: 39px;
    min-height: 39px;
    resize: vertical;
}
.partial-receive-modal .receive-table-card {
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    -webkit-overflow-scrolling: touch;
}
.partial-receive-modal .receive-table {
    width: 100%;
    min-width: 720px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}
.partial-receive-modal .receive-table th {
    padding: 10px 9px;
    border-bottom: 1px solid #dbe3ec;
    background: #f1f5f9;
    color: #334155;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}
.partial-receive-modal .receive-table td {
    padding: 10px 9px;
    border-bottom: 1px solid #edf2f7;
    color: #334155;
    vertical-align: middle;
}
.partial-receive-modal .receive-table tr:last-child td {
    border-bottom: 0;
}
.partial-receive-modal .product-name {
    min-width: 180px;
    font-weight: 800;
}
.partial-receive-modal .product-code {
    display: block;
    margin-top: 2px;
    color: #94a3b8;
    font-size: 11px;
    font-weight: 600;
}
.partial-receive-modal .qty-text {
    white-space: nowrap;
    font-weight: 700;
}
.partial-receive-modal .remaining-text {
    color: #ea580c;
    font-weight: 900;
}
.partial-receive-modal .receive-qty {
    width: 115px;
    text-align: right;
    font-weight: 800;
}
.partial-receive-modal .receive-complete {
    color: #16a34a;
    font-weight: 800;
    white-space: nowrap;
}
.partial-receive-modal .fill-remaining-btn {
    margin-bottom: 9px;
    border-radius: 7px;
    font-weight: 700;
}
.partial-receive-modal .modal-footer {
    padding: 12px 18px 16px;
    border-top: 0;
    background: #fff;
}
.partial-receive-modal .btn-receive-save {
    min-width: 155px;
    min-height: 40px;
    border: 0;
    border-radius: 8px;
    background: #16a34a;
    color: #fff;
    font-weight: 800;
}
.partial-receive-modal .btn-receive-save:hover,
.partial-receive-modal .btn-receive-save:focus {
    background: #15803d;
    color: #fff;
}
.partial-receive-modal,
.partial-receive-modal input,
.partial-receive-modal textarea,
.partial-receive-modal button {
    font-family: "Pyidaungsu", "Noto Sans Myanmar", "Myanmar Text", Arial, sans-serif;
}
@media (max-width: 767px) {
    .partial-receive-modal {
        width: calc(100% - 14px);
        max-width: none;
        margin: 7px auto;
    }
    .partial-receive-modal .modal-body {
        padding: 11px 10px 5px;
    }
    .partial-receive-modal .receive-meta {
        gap: 5px;
    }
    .partial-receive-modal .receive-meta-card {
        padding: 8px 7px;
    }
    .partial-receive-modal .receive-meta-label {
        font-size: 10px;
    }
    .partial-receive-modal .receive-meta-value {
        font-size: 14px;
    }
    .partial-receive-modal .receive-date-row {
        grid-template-columns: 1fr;
    }
    .partial-receive-modal .receive-table {
        min-width: 650px;
    }
}
</style>

<div class="modal-dialog modal-lg partial-receive-modal" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-times"></i>
            </button>
            <h4 class="modal-title">ပစ္စည်းလက်ခံရန်</h4>
        </div>

        <?= form_open(
            'purchases/receive_products',
            [
                'id' => 'partialReceiveForm',
                'autocomplete' => 'off'
            ]
        ); ?>

        <input type="hidden" name="purchase_id" value="<?= $purchase_id; ?>">

        <div class="modal-body">

            <div class="receive-info-line">
                <span><strong>ဘောင်ချာ ID:</strong> <?= $purchase_id; ?></span>
                <span><strong>ပေးသွင်းသူ:</strong> <?= html_escape($purchase->supplier_name ?? '-'); ?></span>
                <span><strong>တည်နေရာ:</strong> <?= html_escape($purchase->store_name ?? '-'); ?></span>
            </div>

            <div class="receive-meta">
                <div class="receive-meta-card">
                    <span class="receive-meta-label">ဝယ်ထားသည့် စုစုပေါင်း</span>
                    <strong class="receive-meta-value"><?= $this->tec->formatDecimal($total_ordered); ?></strong>
                </div>
                <div class="receive-meta-card received">
                    <span class="receive-meta-label">လက်ခံပြီး</span>
                    <strong class="receive-meta-value"><?= $this->tec->formatDecimal($total_received); ?></strong>
                </div>
                <div class="receive-meta-card remaining">
                    <span class="receive-meta-label">လက်ခံရန်ကျန်</span>
                    <strong class="receive-meta-value"><?= $this->tec->formatDecimal($total_remaining); ?></strong>
                </div>
            </div>

            <div class="receive-date-row">
                <div>
                    <label for="received_at">လက်ခံသည့်ရက်စွဲ</label>
                    <input
                        type="datetime-local"
                        name="received_at"
                        id="received_at"
                        class="form-control"
                        value="<?= html_escape(date('Y-m-d\\TH:i')); ?>"
                        required="required"
                    >
                </div>
                <div>
                    <label for="receive_note">မှတ်ချက်</label>
                    <textarea
                        name="receive_note"
                        id="receive_note"
                        class="form-control"
                        placeholder="လိုအပ်ပါက မှတ်ချက်ရေးပါ"
                    ></textarea>
                </div>
            </div>

            <?php if ($total_remaining > 0): ?>
                <button
                    type="button"
                    class="btn btn-default btn-sm fill-remaining-btn"
                    id="fillAllRemaining"
                >
                    <i class="fa fa-check-square-o"></i>
                    ကျန်အားလုံးဖြည့်ရန်
                </button>
            <?php endif; ?>

            <div class="receive-table-card">
                <table class="table receive-table">
                    <thead>
                        <tr>
                            <th>ပစ္စည်း</th>
                            <th class="text-right">ဝယ်ထား</th>
                            <th class="text-right">လက်ခံပြီး</th>
                            <th class="text-right">ကျန်</th>
                            <th class="text-right">ယခုလက်ခံ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $ordered = (float) ($item->primary_qty ?? 0);
                        $received = (float) ($item->received_primary_qty ?? 0);
                        $remaining = max(0, $ordered - $received);
                        $unit_name = trim((string) ($item->primary_unit_name ?? ''));
                        ?>
                        <tr>
                            <td class="product-name">
                                <?= html_escape($item->product_name ?? '-'); ?>
                                <span class="product-code">
                                    <?= html_escape($item->product_code ?? ''); ?>
                                </span>
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
                            <td class="text-right">
                                <?php if ($remaining > 0): ?>
                                    <input
                                        type="text"
                                        name="receive_qty[<?= (int) $item->id; ?>]"
                                        class="form-control receive-qty"
                                        value=""
                                        inputmode="decimal"
                                        autocomplete="off"
                                        data-max="<?= html_escape(number_format($remaining, 4, '.', '')); ?>"
                                        placeholder="0"
                                    >
                                <?php else: ?>
                                    <span class="receive-complete">
                                        <i class="fa fa-check-circle"></i>
                                        ပြည့်စုံ
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="modal-footer">
            <?php if ($total_remaining > 0): ?>
                <button
                    type="submit"
                    class="btn btn-success btn-receive-save"
                    id="savePartialReceive"
                >
                    <i class="fa fa-check"></i>
                    ပစ္စည်းလက်ခံမည်
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    ပိတ်မည်
                </button>
            <?php endif; ?>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<script type="text/javascript">
(function ($) {
    function cleanQty(value) {
        value = String(value || '').replace(/[^0-9.]/g, '');

        var firstDot = value.indexOf('.');
        if (firstDot !== -1) {
            value = value.substring(0, firstDot + 1) +
                value.substring(firstDot + 1).replace(/\./g, '');
        }

        if (value.indexOf('.') !== -1) {
            var parts = value.split('.');
            parts[1] = (parts[1] || '').substring(0, 4);
            value = parts[0] + '.' + parts[1];
        }

        return value;
    }

    $(document)
        .off('input.partialReceiveQty', '.receive-qty')
        .on('input.partialReceiveQty', '.receive-qty', function () {
            var $input = $(this);
            var value = cleanQty($input.val());
            var max = parseFloat($input.attr('data-max') || '0');
            var numeric = parseFloat(value || '0');

            if (max >= 0 && numeric > max) {
                value = String(max);
            }

            $input.val(value);
        });

    $('#fillAllRemaining')
        .off('click.partialReceive')
        .on('click.partialReceive', function () {
            $('.receive-qty').each(function () {
                $(this).val($(this).attr('data-max') || '0');
            });
        });

    $('#partialReceiveForm')
        .off('submit.partialReceive')
        .on('submit.partialReceive', function () {
            var $form = $(this);
            var hasQty = false;
            var invalid = false;

            $form.find('.receive-qty').each(function () {
                var qty = parseFloat($(this).val() || '0');
                var max = parseFloat($(this).attr('data-max') || '0');

                if (qty > 0) {
                    hasQty = true;
                }

                if (qty < 0 || qty > max + 0.000001) {
                    invalid = true;
                }
            });

            if (invalid) {
                alert('လက်ခံမည့်အရေအတွက်သည် ကျန်ရှိသည့်အရေအတွက်ထက် မကျော်ရပါ။');
                return false;
            }

            if (!hasQty) {
                alert('လက်ခံမည့် ပစ္စည်းအရေအတွက် တစ်ခုခု ထည့်ပါ။');
                return false;
            }

            /*
             * AJAX မသုံးတော့ပါ။
             * Normal form POST ဖြင့် submit လုပ်မည်။
             * Save အောင်မြင်လျှင် controller redirect ကြောင့် popup ပိတ်သွားမည်။
             */
            var $button = $('#savePartialReceive');

            if ($button.prop('disabled')) {
                return false;
            }

            $button
                .prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> လက်ခံနေသည်...');

            return true;
        });
})(jQuery);
</script>