<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<style>
.product-detail-page {
    background: #f4f7fb;
    min-height: 100vh;
    padding: 20px 0 40px;
}

.product-detail-wrap {
    max-width: 1280px;
    margin: 0 auto;
}

.erp-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.erp-title {
    margin: 0;
    font-size: 23px;
    font-weight: 600;
    color: #1f2937;
}

.erp-subtitle {
    color: #6b7280;
    margin-top: 5px;
}

.erp-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
}

.erp-card-header {
    padding: 14px 18px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    font-size: 16px;
    font-weight: 600;
    color: #374151;
}

.erp-card-body {
    padding: 20px;
}

.product-top {
    display: flex;
    gap: 25px;
    align-items: flex-start;
}

.product-image-box {
    width: 190px;
    min-width: 190px;
    height: 190px;
    border: 1px solid #e5e7eb;
    background: #fff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-image-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.no-image {
    color: #9ca3af;
    text-align: center;
}

.product-main-info {
    flex: 1;
}

.product-name {
    margin: 0 0 5px;
    font-size: 25px;
    font-weight: 600;
    color: #111827;
}

.product-code {
    color: #6b7280;
    margin-bottom: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.info-item {
    border: 1px solid #e5e7eb;
    background: #fafafa;
    border-radius: 6px;
    padding: 12px 14px;
}

.info-label {
    color: #6b7280;
    font-size: 16px;
    margin-bottom: 5px;
}

.info-value {
    color: #111827;
    font-weight: 600;
}

.stock-value {
    color: #059669;
    font-size: 18px;
}

.erp-table {
    width: 100%;
    margin: 0;
}

.erp-table th {
    background: #f8fafc;
    color: #4b5563;
    font-weight: 600;
    white-space: nowrap;
}

.erp-table > thead > tr > th,
.erp-table > tbody > tr > td {
    vertical-align: middle;
    padding: 11px 13px;
}

.base-badge {
    display: inline-block;
    padding: 3px 8px;
    background: #e8f3ff;
    color: #1677c8;
    border-radius: 4px;
    font-size: 16px;
    margin-left: 5px;
}

.price-text {
    font-weight: 600;
    color: #059669;
}

.conversion-text {
    color: #374151;
}

.audit-box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.audit-item {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 14px;
}

.empty-data {
    color: #9ca3af;
    padding: 20px;
    text-align: center;
}

@media (max-width: 767px) {

    .product-detail-page {
        padding: 12px 0 30px;
    }

    .erp-header {
        align-items: flex-start;
    }

    .erp-title {
        font-size: 20px;
    }

    .product-top {
        display: block;
    }

    .product-image-box {
        width: 150px;
        min-width: 150px;
        height: 150px;
        margin: 0 auto 20px;
    }

    .product-name,
    .product-code {
        text-align: center;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .audit-box {
        grid-template-columns: 1fr;
    }

    .table-responsive {
        border: 0;
    }
}
/* Unit / Packaging Table Border */
.erp-table {
    border-collapse: collapse !important;
    border: 1px solid #dfe4ea !important;
}

.erp-table thead th {
    border: 1px solid #dfe4ea !important;
    background: #f6f8fa !important;
}

.erp-table tbody td {
    border: 1px solid #e1e5ea !important;
    padding: 13px 14px !important;
    vertical-align: middle !important;
}

.erp-table tbody tr:hover {
    background: #fafcff;
}

/* Unit column */
.erp-table tbody td:first-child {
    font-weight: 600;
}

/* Selling Price */
.erp-table tbody td:last-child {
    font-weight: 600;
}

/* Mobile */
@media (max-width: 767px) {
    .erp-table tbody td,
    .erp-table thead th {
        padding: 10px 8px !important;
    }
}
.stock-value {
    color: #079447 !important;
    font-weight: 700;
    font-size: 16px;
}

.stock-plus {
    color: #9ca3af;
    margin: 0 4px;
}

.stock-base-total {
    margin-top: 5px;
    color: #8a94a3;
    font-size: 12px;
    font-weight: 500;
}

/* =========================================================
   RESPONSIVE LAYOUT — same pattern as Unit Conversion page
========================================================= */
.product-detail-page,
.product-detail-page * {
    box-sizing: border-box;
}

.product-detail-page {
    padding: 18px 0 40px;
}

.product-detail-page .container-fluid {
    width: 100%;
    padding-left: 15px;
    padding-right: 15px;
}

.product-detail-wrap {
    width: 100%;
    max-width: 1280px;
    min-width: 0;
}

.erp-card,
.erp-card-body,
.product-top,
.product-main-info,
.info-grid,
.info-item {
    min-width: 0;
    max-width: 100%;
}

.erp-card {
    box-shadow: none;
}

.erp-card-header {
    padding: 14px 18px;
    background: #f8fafc;
    color: #374151;
    font-weight: 800;
}

.erp-card-header i {
    margin-right: 6px;
    color: #0f766e;
}

.info-item {
    overflow: hidden;
    overflow-wrap: anywhere;
}

.info-value,
.stock-value,
.stock-base-total {
    overflow-wrap: anywhere;
    word-break: break-word;
}

.table-responsive {
    width: 100%;
    max-width: 100%;
    margin-bottom: 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border: 0;
}

.table-responsive > .erp-table {
    margin-bottom: 0;
}

/* Desktop below 1200px */
@media (max-width: 1199px) {
    .product-detail-page .container-fluid {
        padding-left: 12px;
        padding-right: 12px;
    }

    .erp-card-body {
        padding: 18px;
    }

    .product-top {
        gap: 18px;
    }

    .product-image-box {
        width: 170px;
        min-width: 170px;
        height: 170px;
    }
}

/* Tablet / iPad — no fixed child width may push outside content area. */
@media (max-width: 991px) {
    .product-detail-page .container-fluid {
        padding-left: 10px;
        padding-right: 10px;
    }

    .erp-card-body {
        padding: 14px;
    }

    .product-top {
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 14px;
        width: 100%;
    }

    .product-image-box {
        width: 150px;
        min-width: 0;
        height: 150px;
    }

    .product-name {
        font-size: 21px;
        overflow-wrap: anywhere;
    }

    .product-code {
        margin-bottom: 14px;
        overflow-wrap: anywhere;
    }

    .info-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .info-item {
        padding: 10px;
    }

    .erp-table {
        width: 100%;
    }

    /* The 7-column history table stays readable and scrolls inside its card. */
    .purchase-history-card .erp-table {
        min-width: 820px;
    }
}

/* Phone */
@media (max-width: 767px) {
    .product-detail-page {
        padding: 0 0 24px;
    }

    .product-detail-page .container-fluid {
        padding-left: 8px;
        padding-right: 8px;
    }

    .erp-card {
        margin-bottom: 10px;
        border-radius: 8px;
    }

    .erp-card-header {
        padding: 11px 10px;
        font-size: 15px;
    }

    .erp-card-body {
        padding: 10px;
    }

    .product-top {
        display: block;
    }

    .product-image-box {
        width: 130px;
        height: 130px;
        margin: 0 auto 12px;
    }

    .product-name {
        margin-bottom: 4px;
        font-size: 19px;
        text-align: center;
    }

    .product-code {
        margin-bottom: 12px;
        text-align: center;
    }

    .info-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 7px !important;
    }

    .info-item {
        padding: 9px 10px;
        border-radius: 9px;
    }

    .info-label,
    .info-value {
        font-size: 15px;
    }

    .stock-value {
        font-size: 15px;
    }

    .stock-base-total {
        font-size: 12px;
    }

    /* 3-column unit table: keep useful column widths and scroll only the table. */
    .unit-packaging-card .erp-table {
        min-width: 620px;
    }

    .purchase-history-card .erp-table {
        min-width: 800px;
    }

    .erp-table > thead > tr > th,
    .erp-table > tbody > tr > td {
        padding: 10px 8px !important;
        white-space: nowrap;
    }

    .conversion-text {
        white-space: normal !important;
        min-width: 230px;
    }
}

@media (max-width: 480px) {
    .product-detail-page .container-fluid {
        padding-left: 5px;
        padding-right: 5px;
    }

    .erp-card-body {
        padding: 8px;
    }

    .product-image-box {
        width: 115px;
        height: 115px;
    }
}
</style>


<div class="product-detail-page">

<div class="container-fluid">
<div class="product-detail-wrap">


    


    <!-- PRODUCT INFORMATION -->
    <div class="erp-card">

        

        <div class="erp-card-body">

            <div class="product-top">


                <!-- IMAGE -->
                <div class="product-image-box">

                    <?php if (!empty($product->image)) { ?>

                        <img
                            src="<?= base_url('uploads/' . $product->image); ?>"
                            alt="<?= html_escape($product->name); ?>"
                        >

                    <?php } else { ?>

                        <div class="no-image">
                            <i class="fa fa-image fa-3x"></i>
                            <br>
                            No Image
                        </div>

                    <?php } ?>

                </div>


                <!-- BASIC INFO -->
                <div class="product-main-info">

                    <h3 class="product-name">
                        <?= html_escape($product->name); ?>
                    </h3>

                    <div class="product-code">

                        <i class="fa fa-barcode"></i>

                        <?= html_escape($product->code); ?>

                    </div>


                    <div class="info-grid">


                        <!-- CATEGORY -->
                        <div class="info-item">

                            <div class="info-label">
                                <?= lang('category') ?: 'Category'; ?>
                            </div>

                            <div class="info-value">
                                <?= !empty($category)
                                    ? html_escape($category->name)
                                    : '-'; ?>
                            </div>

                        </div>


                        <!-- BASE UNIT -->
                        <div class="info-item">

                            <div class="info-label">
                                အခြေခံယူနစ်
                            </div>

                            <div class="info-value">

                                <?php if (!empty($unit)) { ?>

                                    <?= html_escape($unit->name); ?>

                                    <?php if (!empty($unit->code)) { ?>
                                        (<?= html_escape($unit->code); ?>)
                                    <?php } ?>

                                <?php } else { ?>
                                    -
                                <?php } ?>

                            </div>

                        </div>


                        <!-- CURRENT STOCK -->
                        <div class="info-item">

                            <div class="info-label">
                                လက်ရှိလက်ကျန်
                            </div>

                            <div class="info-value stock-value">

    <?php
    $stock_qty = (float) ($current_stock ?? 0);
    $remaining = $stock_qty;

    /*
     * Conversion တွေကို အကြီးဆုံး Unit ကနေ
     * အငယ်ဆုံး Unit အလိုက်စီမယ်
     *
     * Example:
     * Box     = 24
     * ကဒ်     = 4
     * ပုလင်း  = 1
     */
    $stock_units = [];

    if (!empty($unit_conversions)) {

        foreach ($unit_conversions as $conversion) {

            $convert_value =
                (float) ($conversion->operation_value ?? 0);

            // Base unit (1) ကို နောက်ဆုံးမှ သီးသန့်ပြမယ်
            if ($convert_value > 1) {

                $stock_units[] = [
                    'name'  => $conversion->unit_name,
                    'value' => $convert_value
                ];

            }
        }

    }

    // အကြီးဆုံး conversion ကို အရင်
    usort($stock_units, function ($a, $b) {
        return $b['value'] <=> $a['value'];
    });


    $stock_display = [];

    foreach ($stock_units as $stock_unit) {

        if ($remaining >= $stock_unit['value']) {

            $unit_qty = floor(
                $remaining / $stock_unit['value']
            );

            if ($unit_qty > 0) {

                $stock_display[] =
                    $this->tec->formatNumber($unit_qty)
                    . ' '
                    . html_escape($stock_unit['name']);

                $remaining -=
                    $unit_qty * $stock_unit['value'];
            }
        }
    }


    // ကျန်တဲ့ Base Unit
    if ($remaining > 0 || empty($stock_display)) {

        $stock_display[] =
            $this->tec->formatNumber($remaining)
            . ' '
            . (
                !empty($unit)
                ? html_escape($unit->name)
                : ''
            );
    }

    echo implode(
        ' <span class="stock-plus">+</span> ',
        $stock_display
    );
    ?>

    <div class="stock-base-total">
        <?= $this->tec->formatNumber($stock_qty); ?>
        <?= !empty($unit)
            ? html_escape($unit->name)
            : ''; ?>
        <?= lang('total') ? '(' . lang('total') . ')' : ''; ?>
    </div>

</div>

                        </div>


                        <!-- COST -->
                        <div class="info-item">

                            <div class="info-label">
                                <?= lang('cost') ?: 'Cost'; ?>
                            </div>

                            <div class="info-value">
                                <?= $this->tec->formatMoney(
                                    $product->cost ?? 0
                                ); ?>
                            </div>

                        </div>


                        <!-- BASE SELLING PRICE -->
                        <div class="info-item">

                            <div class="info-label">
                                <?= lang('price') ?: 'Selling Price'; ?>
                            </div>

                            <div class="info-value price-text">
                                <?= $this->tec->formatMoney(
                                    $product->price ?? 0
                                ); ?>
                            </div>

                        </div>


                        <!-- ALERT QTY -->
                        <div class="info-item">

                            <div class="info-label">
                                <?= lang('alert_quantity') ?: 'Minimum / Alert Quantity'; ?>
                                
                            </div>

                            <div class="info-value">
                                <?= $this->tec->formatNumber(
                                    $product->alert_quantity ?? 0
                                ); ?>
                            </div>

                        </div>
                        
                        <!-- CREATED BY -->
                <div class="info-item">

                    <div class="info-label">
                         <?= lang('datacreated_by') ?: 'Created By'; ?> 
                    </div>

                    <div class="info-value">

                        <?php

                        if (!empty($creator)) {

                            $creator_name = trim(
                                ($creator->first_name ?? '')
                                . ' '
                                . ($creator->last_name ?? '')
                            );

                            echo $creator_name
                                ? html_escape($creator_name)
                                : '-';

                        } else {

                            echo '-';

                        }

                        ?>

                    </div>

                </div>


                <!-- CREATED DATE -->
                <div class="info-item">

                    <div class="info-label">
                        ထည့်သွင်းသည့်အချိန်
                    </div>

                    <div class="info-value">

                        <?php if (!empty($product->created_at)) { ?>

                            <?= date(
                                'd-m-Y h:i A',
                                strtotime($product->created_at)
                            ); ?>

                        <?php } else { ?>

                            -

                        <?php } ?>

                    </div>

                </div>


                    </div>

                </div>

            </div>

        </div>
    </div>



    <!-- UNIT / PACKAGING -->
    <div class="erp-card unit-packaging-card">

        <div class="erp-card-header">
            <i class="fa fa-cubes"></i>
            Unit / ထုပ်ပိုးပုံ
        </div>

        <div class="table-responsive">

            <table class="table table-bordered erp-table">

                <thead>
                    <tr>
                        <th style="width:25%">
                            <?= lang('unit') ?: 'Minimum / Alert Quantity'; ?>
                        </th>

                        <th>
                            <?= lang('packaging') ?: 'Minimum / Alert Quantity'; ?>
                        </th>

                        <th style="width:25%" class="text-right">
                            <?= lang('selling_price') ?: 'Minimum / Alert Quantity'; ?> 
                        </th>
                    </tr>
                </thead>

                <tbody>

                <?php

                // Unit price ကို unit_id နဲ့ ရှာလွယ်အောင်
                $price_map = [];

                if (!empty($unit_prices)) {

                    foreach ($unit_prices as $up) {
                        $price_map[$up->unit_id] = $up->price;
                    }

                }


                $previous = null;

                if (!empty($unit_conversions)) {

                    foreach ($unit_conversions as $conversion) {

                        $value = (float) $conversion->operation_value;
                ?>

                    <tr>

                        <!-- UNIT -->
                        <td>

                            <strong>
                                <?= html_escape($conversion->unit_name); ?>
                            </strong>

                            <?php if (!empty($conversion->unit_code)) { ?>

                                <span class="text-muted">
                                    (<?= html_escape(
                                        $conversion->unit_code
                                    ); ?>)
                                </span>

                            <?php } ?>


                            <?php if ($value == 1) { ?>

                                <span class="base-badge">
                                    Base
                                </span>

                            <?php } ?>

                        </td>


                        <!-- CONVERSION -->
                        <td class="conversion-text">

                            <?php if ($value == 1) { ?>

                                အခြေခံယူနစ်

                            <?php } else { ?>

                                <strong>
                                    1
                                    <?= html_escape(
                                        $conversion->unit_name
                                    ); ?>
                                </strong>

                                =

                                <?php

                                if (
                                    $previous &&
                                    (float)$previous->operation_value > 1
                                ) {

                                    $previous_value =
                                        (float)$previous->operation_value;

                                    $ratio =
                                        $value / $previous_value;

                                    if (
                                        abs(
                                            $ratio - round($ratio)
                                        ) < 0.00001
                                    ) {

                                        echo
                                            $this->tec->formatNumber($ratio)
                                            . ' '
                                            . html_escape(
                                                $previous->unit_name
                                            )
                                            . ' = ';
                                    }
                                }

                                ?>

                                <strong>

                                    <?= $this->tec->formatNumber(
                                        $value
                                    ); ?>

                                    <?= !empty($unit)
                                        ? html_escape($unit->name)
                                        : ''; ?>

                                </strong>

                            <?php } ?>

                        </td>


                        <!-- PRICE -->
                        <td class="text-right price-text">

                            <?php

                            if (isset($price_map[$conversion->unit_id])) {

                                echo $this->tec->formatMoney(
                                    $price_map[$conversion->unit_id]
                                );

                            } elseif ($value == 1) {

                                echo $this->tec->formatMoney(
                                    $product->price ?? 0
                                );

                            } else {

                                echo '-';

                            }

                            ?>

                        </td>

                    </tr>

                <?php

                        $previous = $conversion;
                    }

                } else {

                ?>

                    <tr>
                        <td colspan="3" class="empty-data">
                            Unit Conversion မရှိသေးပါ။
                        </td>
                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>



    



    <!-- PURCHASE BATCH -->
    <!-- PURCHASE BATCH HISTORY -->
    <div class="erp-card purchase-history-card">
    <div class="erp-card-header">
    <i class="fa fa-shopping-cart"></i>
    <?= lang('purchase_batch_history'); ?>
</div>

<div class="table-responsive">

    <table class="table table-bordered table-striped erp-table">

        <thead>
            <tr>
                <th>#</th>

                <th>
                    <?= lang('purchase_date'); ?>
                </th>

                

                <th class="text-right">
                    <?= lang('purchase_qty'); ?>
                </th>

                <th class="text-right">
                    <?= lang('unit_convert'); ?>
                </th>

                <th class="text-right">
                    <?= lang('current_balance'); ?>
                </th>

                <th class="text-right">
                    <?= lang('cost_per_base'); ?>
                </th>
            </tr>
        </thead>

        <tbody>

        <?php if (!empty($batches)) { ?>

            <?php
            $no = 1;
            $total_balance = 0;
            ?>

            <?php foreach ($batches as $batch) { ?>
                <?php
                $balance = (float) ($batch->qty_base ?? 0);
                $total_balance += $balance;
                ?>

                <tr>

                    <!-- NO -->
                    <td>
                        <?= $no++; ?>
                    </td>


                    <!-- PURCHASE DATE -->
                    <td>
                        <?php
                        if (!empty($batch->date)) {
                            echo date(
                                'd-m-Y',
                                strtotime($batch->date)
                            );
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>


                    <!-- BATCH NO -->
                    


                    <!-- PURCHASE QTY -->
                    <td class="text-right">

                        <strong>
                            <?= rtrim(
                                rtrim(
                                    number_format(
                                        (float) ($batch->qty_primary ?? 0),
                                        2,
                                        '.',
                                        ','
                                    ),
                                    '0'
                                ),
                                '.'
                            ); ?>
                        </strong>

                        <?php if (!empty($batch->primary_unit_name)) { ?>

                            <?= html_escape(
                                $batch->primary_unit_name
                            ); ?>

                        <?php } ?>

                    </td>


                    <!-- UNIT CONVERT -->
                    <td class="text-right">

                        <?php if ((float) ($batch->unit_convert ?? 0) > 0) { ?>

                            1

                            <?= !empty($batch->primary_unit_name)
                                ? html_escape($batch->primary_unit_name)
                                : ''; ?>

                            =

                            <strong>
                                <?= $this->tec->formatNumber(
                                    $batch->unit_convert
                                ); ?>
                            </strong>

                            <?= !empty($unit)
                                ? html_escape($unit->name)
                                : ''; ?>

                        <?php } else { ?>

                            -

                        <?php } ?>

                    </td>


                    <!-- CURRENT BALANCE -->
                    <td class="text-right">

                        <?php if ($balance > 0) { ?>

                            <span style="
                                color:#16803c;
                                font-weight:700;
                            ">

                                <?= $this->tec->formatNumber(
                                    $balance
                                ); ?>

                                <?= !empty($unit)
                                    ? html_escape($unit->name)
                                    : ''; ?>

                            </span>

                        <?php } else { ?>

                            <span style="
                                color:#dc3545;
                                font-weight:600;
                            ">

                                0

                                <?= !empty($unit)
                                    ? html_escape($unit->name)
                                    : ''; ?>

                            </span>

                        <?php } ?>

                    </td>


                    <!-- COST / BASE -->
                    <td class="text-right">

                        <?= $this->tec->formatMoney(
                            $batch->cost_per_base ?? 0
                        ); ?>

                    </td>

                </tr>

            <?php } ?>


            <!-- TOTAL CURRENT STOCK -->
            <tr style="background:#f3f8f5;">

                <td colspan="4"
                    class="text-right"
                    style="font-weight:700;">

                    <?= lang('total_current_stock'); ?>

                </td>

                <td class="text-right"
                    style="
                        color:#16803c;
                        font-weight:700;
                        font-size:16px;
                    ">

                    <?= $this->tec->formatNumber(
                        $total_balance
                    ); ?>

                    <?= !empty($unit)
                        ? html_escape($unit->name)
                        : ''; ?>

                </td>

                <td></td>

            </tr>


        <?php } else { ?>

            <tr>

                <td colspan="6"
                    class="empty-data">

                    <i class="fa fa-info-circle"></i>

                    <?= lang('no_purchase_batch_history'); ?>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

</div>


</div>
</div>
</div>