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
    padding: 18px 20px;
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
    padding: 18px 20px 20px;
    background: #f8fafc;
}

.receive-page .receive-info-line {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 22px;
    margin-bottom: 14px;
    padding: 12px 14px;
    border: 1px solid #dbeafe;
    border-radius: 10px;
    background: #eff6ff;
    color: #334155;
    font-size: 13px;
}

.receive-page .receive-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.receive-page .summary-card {
    padding: 14px 16px;
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
    margin-bottom: 5px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
}

.receive-page .summary-value {
    color: #1e293b;
    font-size: 20px;
    font-weight: 900;
}

.receive-page .receive-form-row {
    display: grid;
    grid-template-columns: minmax(210px, .8fr) minmax(220px, 1fr) minmax(260px, 1.4fr);
    gap: 12px;
    margin-bottom: 16px;
}

.receive-page .form-group {
    margin-bottom: 0;
}

.receive-page label {
    display: block;
    margin-bottom: 6px;
    color: #334155;
    font-size: 13px;
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

.receive-page .receive-toolbar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 9px;
}

.receive-page .fill-remaining-btn {
    border-radius: 7px;
    font-weight: 700;
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
    min-width: 760px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.receive-page .receive-table th {
    padding: 12px 10px;
    border-bottom: 1px solid #dbe3ec;
    background: #f1f5f9;
    color: #334155;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
}

.receive-page .receive-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #edf2f7;
    color: #334155;
    vertical-align: middle;
}

.receive-page .receive-table tbody tr:last-child td {
    border-bottom: 0;
}

.receive-page .product-name {
    min-width: 210px;
    font-weight: 800;
}

.receive-page .product-code {
    display: block;
    margin-top: 3px;
    color: #94a3b8;
    font-size: 11px;
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
    width: 125px;
    margin-left: auto;
    text-align: right;
    font-weight: 800;
}

.receive-page .receive-complete {
    color: #16a34a;
    font-weight: 800;
    white-space: nowrap;
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
        font-size: 10px;
    }

    .receive-page .summary-value {
        font-size: 15px;
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
</style>

<div class="receive-page">

    <div class="receive-page-card">

        <div class="receive-page-header">
            <h2 class="receive-page-title">
                <i class="fa fa-truck"></i>
                ပစ္စည်းလက်ခံရန်
            </h2>

            <a href="<?= site_url('purchases'); ?>" class="receive-back-btn">
                <i class="fa fa-arrow-left"></i>
                နောက်သို့
            </a>
        </div>

        <?= form_open(
            'purchases/receive_products',
            [
                'id' => 'partialReceiveForm',
                'autocomplete' => 'off'
            ]
        ); ?>

        <input type="hidden" name="purchase_id" value="<?= $purchase_id; ?>">

        <div class="receive-page-body">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <div class="receive-info-line">
                <span>
                    <strong>ဘောင်ချာ ID:</strong>
                    <?= $purchase_id; ?>
                </span>

                <span>
                    <strong>ပေးသွင်းသူ:</strong>
                    <?= html_escape($purchase->supplier_name ?? '-'); ?>
                </span>

                <span>
                    <strong>မူလဝယ်ယူသည့်နေရာ:</strong>
                    <?= html_escape($purchase->store_name ?? '-'); ?>
                </span>
            </div>

            <div class="receive-summary">

                <div class="summary-card">
                    <span class="summary-label">ဝယ်ထားသည့် စုစုပေါင်း</span>
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

            <h3 class="receive-section-title">
                ကျန်အရေအတွက် ပြည့်ရန်
            </h3>

            <?php if ($total_remaining > 0): ?>
                <div class="receive-toolbar">
                    <button
                        type="button"
                        class="btn btn-default btn-sm fill-remaining-btn"
                        id="fillAllRemaining"
                    >
                        <i class="fa fa-check-square-o"></i>
                        ကျန်အားလုံးဖြည့်ရန်
                    </button>
                </div>
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
                        $ordered   = (float) ($item->primary_qty ?? 0);
                        $received  = (float) ($item->received_primary_qty ?? 0);
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
                                        data-max="<?= html_escape(
                                            number_format($remaining, 4, '.', '')
                                        ); ?>"
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

            <div class="receive-actions">

                <a href="<?= site_url('purchases'); ?>" class="btn btn-default">
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

            $input.val(value);
        });


    $('#fillAllRemaining')
        .off('click.partialReceive')
        .on('click.partialReceive', function () {

            $('.receive-qty').each(function () {
                $(this).val(
                    $(this).attr('data-max') || '0'
                );
            });
        });


    $('#partialReceiveForm')
        .off('submit.partialReceive')
        .on('submit.partialReceive', function () {

            var $form = $(this);
            var hasQty = false;
            var invalid = false;

            if (!$('#receive_store_id').val()) {
                alert('ပစ္စည်းလက်ခံသည့်နေရာ ရွေးပါ။');
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
                alert(
                    'လက်ခံမည့်အရေအတွက်သည် ' +
                    'ကျန်ရှိသည့်အရေအတွက်ထက် မကျော်ရပါ။'
                );
                return false;
            }

            if (!hasQty) {
                alert(
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
