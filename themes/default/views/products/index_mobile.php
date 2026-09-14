<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$app_language =
    $this->input->get('app_lang', true) ?:
    (
        $this->input->post('app_lang', true) ?:
        $Settings->selected_language
    );

$app_query = $is_app_mode
    ? '?app=1&app_lang=' . rawurlencode($app_language)
    : '';

$active_product_language = strtolower(trim((string) $app_language));
$is_myanmar_language = in_array(
    $active_product_language,
    array('myanmar', 'burmese', 'mm', 'my'),
    true
);

$unit_conversion_label = $is_myanmar_language
    ? 'ယူနစ်ဆက်စပ်မှု'
    : 'Unit Conversion';

$manage_unit_conversion_label = $is_myanmar_language
    ? 'ယူနစ်ဆက်စပ်မှု သတ်မှတ်ရန်'
    : 'Manage Unit Conversion';

$base_quantity_label = $is_myanmar_language
    ? 'အရေအတွက်'
    : 'Base Unit Quantity';

$base_unit_label = $is_myanmar_language
    ? 'ယူနစ်'
    : 'Base Unit';

$unit_type_label = $is_myanmar_language
    ? 'Dual'
    : 'Dual';

$dual_unit_label = $is_myanmar_language
    ? 'Yes'
    : 'Yes';

$normal_unit_label = $is_myanmar_language
    ? 'No'
    : 'No';

$independent_count_label = $is_myanmar_language
    ? 'သီးခြားရေတွက်'
    : 'independent count';

$purchase_price_label = $is_myanmar_language
    ? 'ဝယ်ဈေး'
    : 'Purchase Price';

$selling_price_label = $is_myanmar_language
    ? 'ရောင်းဈေး'
    : 'Selling Price';

$set_selling_price_label = $is_myanmar_language
    ? 'ရောင်းဈေးထည့်ရန်'
    : 'Set Selling Price';

$delete_confirm_message = $is_myanmar_language
    ? 'ဤကုန်ပစ္စည်းကို ဖျက်ရန် သေချာပါသလား?'
    : 'Are you sure you want to delete this product?';

$actions_label = lang('actions');
if (
    empty($actions_label) ||
    $actions_label === 'actions'
) {
    $actions_label = $is_myanmar_language
        ? 'လုပ်ဆောင်မှုများ'
        : 'Actions';
}
?>

<link rel="stylesheet" href="<?= $assets ?>css/products-mobile.css?v=1">
<link rel="stylesheet" href="<?= $assets ?>css/mobile-ui.css?v=3">
<link rel="stylesheet" href="<?= $assets ?>css/mobile-actions.css?v=1">

<section class="content kls-mobile-ui">
    <div class="erp-page">
        <header class="kls-mobile-heading">
            <div>
                <span class="kls-mobile-eyebrow"><?= $is_myanmar_language ? 'ကုန်ပစ္စည်းစီမံခန့်ခွဲမှု' : 'Inventory'; ?></span>
                <h1><?= html_escape(lang('products')); ?></h1>
                <p><?= $is_myanmar_language ? 'ကုန်ပစ္စည်း၊ လက်ကျန်နှင့် ဈေးနှုန်းများ' : 'Products, stock levels and prices'; ?></p>
            </div>
        </header>
        <div class="erp-card">



            <div class="erp-body">

                <?php if ($Admin && $qty_alert_num && $this->session->userdata('store_id')) { ?>
                    <div class="erp-alert">
                        <i class="fa fa-bullhorn"></i>
                        <div>
                            <a href="<?= site_url('reports/alerts') . $app_query; ?>">
                                ပစ္စည်းအမျိုးအစား <strong><?= $qty_alert_num; ?></strong> ကျော် အသစ်ထပ်မံ ဝယ်ယူထည့်သွင်းရန် လိုအပ်နေပါသည်။
                            </a>
                        </div>
                    </div>
                <?php } ?>


<div class="product-search-help">
    <i class="fa fa-search"></i>
    <div>
        <strong><?= $is_myanmar_language ? 'ကုန်ပစ္စည်းရှာဖွေရန်' : 'Find a product'; ?></strong>
        <span>
            <?= $is_myanmar_language ? 'ကုဒ်၊ အမည်၊ အရေအတွက် သို့မဟုတ် ယူနစ်ဖြင့် ရှာဖွေနိုင်ပါသည်။' : 'Filter by code, name, quantity or unit.'; ?>
        </span>
    </div>
</div>


                <div class="erp-column-filters" id="product-column-filters">
                    <div class="erp-column-filter">
                        <i class="fa fa-barcode"></i>
                        <input type="text" class="erp-and-filter-input" aria-label="<?= html_escape(lang('code')); ?>" data-column="2" placeholder="<?= html_escape(lang('code')); ?>">
                    </div>
                    <div class="erp-column-filter">
                        <i class="fa fa-cube"></i>
                        <input type="text" class="erp-and-filter-input" aria-label="<?= html_escape(lang('name')); ?>" data-column="3" placeholder="<?= html_escape(lang('name')); ?>">
                    </div>
                    <div class="erp-column-filter">
                        <i class="fa fa-sort-numeric-asc"></i>
                        <input type="text" class="erp-and-filter-input" aria-label="<?= html_escape(lang('search')); ?>" data-column="5" placeholder="<?= html_escape($base_quantity_label); ?>">
                    </div>
                    <div class="erp-column-filter">
                        <i class="fa fa-balance-scale"></i>
                        <input type="text" class="erp-and-filter-input" aria-label="<?= html_escape(lang('search')); ?>" data-column="6" placeholder="<?= html_escape($base_unit_label); ?>">
                    </div>
                </div>

                <div class="erp-table-wrap">
                    <div class="table-responsive" tabindex="0" role="region" aria-label="<?= html_escape(lang('products')); ?>">
                        <table id="prTables" class="table table-striped table-bordered table-hover kls-action-table">
                            <thead>
                                <tr>
                                    <th><?= lang("id"); ?></th>
                                    <th><?= lang("image"); ?></th>
                                    <th><?= lang("code"); ?></th>
                                    <th><?= lang("name"); ?></th>
                                    <th><?= lang("category"); ?></th>
                                    <th><?= html_escape($base_quantity_label); ?></th>
                                    <th><?= html_escape($base_unit_label); ?></th>
                                    <th><?= html_escape($unit_type_label); ?></th>
                                    <th><?= html_escape($purchase_price_label); ?></th>
                                    <th><?= html_escape($selling_price_label); ?></th>
                                    <th><?= html_escape($actions_label); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="erp-action-legend" aria-label="Action icon meanings">
                    <div class="erp-action-legend-title">
                        <i class="fa fa-info-circle"></i>
                        <span>လုပ်ဆောင်ချက်ပုံများ၏ အဓိပ္ပာယ်</span>
                    </div>
                    <div class="erp-action-legend-list">

                        <div class="erp-action-legend-item erp-action-legend-edit">
                            <span class="erp-action-legend-icon"><i class="fa fa-pencil"></i></span>
                            <span>ပြင်ဆင်ရန်</span>
                        </div>
                        <div class="erp-action-legend-item erp-action-legend-view">
                            <span class="erp-action-legend-icon"><i class="fa fa-eye"></i></span>
                            <span>အသေးစိတ်ကြည့်ရန်</span>
                        </div>
                        <div class="erp-action-legend-item erp-action-legend-unit">
                            <span class="erp-action-legend-icon"><i class="fa fa-balance-scale"></i></span>
                            <span>ယူနစ်ဆက်စပ်မှု သတ်မှတ်ရန်</span>
                        </div>



                        <div class="erp-action-legend-item erp-action-legend-delete">
                            <span class="erp-action-legend-icon"><i class="fa fa-trash"></i></span>
                            <span>ဖျက်ရန်</span>
                        </div>
                    </div>
                </div>

                <!-- Image Modal -->
                <div class="modal fade" id="picModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">
                                    <i class="fa fa-times"></i>
                                </button>
                                <button type="button" class="close mr10" onclick="window.print();">
                                    <i class="fa fa-print"></i>
                                </button>
                                <h4 class="modal-title" id="myModalLabel">Product Image</h4>
                            </div>
                            <div class="modal-body text-center">
                                <img class="img-responsive" id="product_image" src="" alt="" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
$(document).ready(function() {

    function image(n) {
        if (n !== null && n !== '') {
            return '<div class="erp-product-img">' +
                   '<a href="<?= base_url(); ?>uploads/' + n + '" class="open-image">' +
                   '<img src="<?= base_url(); ?>uploads/thumbs/' + n + '" class="img-responsive"></a></div>';
        }

        return '<div class="erp-product-img"><i class="fa fa-picture-o erp-no-img"></i></div>';
    }

    function numericValue(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        if (typeof value === 'number') {
            return value;
        }

        var cleaned = String(value)
            .replace(/<[^>]*>/g, '')
            .replace(/,/g, '')
            .replace(/[^0-9.\-]/g, '');

        return parseFloat(cleaned) || 0;
    }

    function quantityFormat(value, type) {
        var num = numericValue(value);

        if (type !== 'display') {
            return num;
        }

        var output = num % 1 === 0
            ? num.toFixed(0)
            : num.toFixed(2);

        return '<span class="text-primary" style="font-weight:800;">' +
            output +
        '</span>';
    }

    function currencyFormat(value, type) {
        var num = numericValue(value);

        if (type !== 'display') {
            return num;
        }

        return '<span style="font-weight:800;">' +
            num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) +
        '</span>';
    }

    function productPurchasePrice(row) {
        if (!row) {
            return 0;
        }

        var fields = [
            'cost',
            'purchase_price',
            'purchase_cost',
            'base_cost',
            'unit_cost',
            'pcost',
            'product_cost'
        ];

        for (var i = 0; i < fields.length; i++) {
            if (
                row[fields[i]] !== undefined &&
                row[fields[i]] !== null &&
                row[fields[i]] !== ''
            ) {
                return row[fields[i]];
            }
        }

        return 0;
    }

    function productSellingPrice(row) {
        if (!row) {
            return 0;
        }

        var fields = [
            'selling_price',
            'sale_price',
            'price',
            'base_price',
            'unit_price',
            'product_price'
        ];

        for (var i = 0; i < fields.length; i++) {
            if (
                row[fields[i]] !== undefined &&
                row[fields[i]] !== null &&
                row[fields[i]] !== ''
            ) {
                return row[fields[i]];
            }
        }

        return 0;
    }

    function actionTypeAndIcon($link) {
        var href = String($link.attr('href') || '').toLowerCase();
        var title = String($link.attr('title') || '').toLowerCase();
        var text = String($link.text() || '').toLowerCase();
        var iconClass = String($link.find('i').first().attr('class') || '').toLowerCase();
        var combined = href + ' ' + title + ' ' + text + ' ' + iconClass;

        if (
            combined.indexOf('selling_prices') !== -1 ||
            combined.indexOf('selling-price') !== -1 ||
            combined.indexOf('set selling price') !== -1 ||
            combined.indexOf('ရောင်းဈေးထည့်') !== -1 ||
            combined.indexOf('ရောင်းစျေးထည့်') !== -1
        ) {
            return { type: 'price', icon: 'fa fa-tag' };
        }

        if (
            combined.indexOf('delete') !== -1 ||
            combined.indexOf('remove') !== -1 ||
            combined.indexOf('trash') !== -1 ||
            combined.indexOf('ဖျက်') !== -1
        ) {
            return { type: 'delete', icon: 'fa fa-trash' };
        }

        if (
            combined.indexOf('edit') !== -1 ||
            combined.indexOf('pencil') !== -1 ||
            combined.indexOf('ပြင်') !== -1
        ) {
            return { type: 'edit', icon: 'fa fa-pencil' };
        }

        if (
            combined.indexOf('barcode') !== -1 ||
            combined.indexOf('print') !== -1 ||
            combined.indexOf('ဘားကုဒ်') !== -1 ||
            combined.indexOf('ပုံနှိပ်') !== -1
        ) {
            return { type: 'print', icon: 'fa fa-barcode' };
        }

        if (
            combined.indexOf('copy') !== -1 ||
            combined.indexOf('duplicate') !== -1 ||
            combined.indexOf('ကူး') !== -1
        ) {
            return { type: 'copy', icon: 'fa fa-copy' };
        }

        if (
            combined.indexOf('unit') !== -1 ||
            combined.indexOf('conversion') !== -1 ||
            combined.indexOf('balance-scale') !== -1 ||
            combined.indexOf('ယူနစ်') !== -1
        ) {
            return { type: 'unit', icon: 'fa fa-balance-scale' };
        }

        if (
            combined.indexOf('stock') !== -1 ||
            combined.indexOf('quantity') !== -1 ||
            combined.indexOf('inventory') !== -1 ||
            combined.indexOf('လက်ကျန်') !== -1
        ) {
            return { type: 'stock', icon: 'fa fa-cubes' };
        }

        if (
            combined.indexOf('view') !== -1 ||
            combined.indexOf('detail') !== -1 ||
            combined.indexOf('eye') !== -1 ||
            combined.indexOf('ကြည့်') !== -1
        ) {
            return { type: 'view', icon: 'fa fa-eye' };
        }

        return {
            type: 'view',
            icon: iconClass ? iconClass : 'fa fa-link'
        };
    }

    function isProductPictureAction($link) {
        var href = String($link.attr('href') || '').toLowerCase();
        var title = String($link.attr('title') || '').toLowerCase();
        var textValue = String($link.text() || '').toLowerCase();
        var classes = String($link.attr('class') || '').toLowerCase();
        var iconClass = String($link.find('i').first().attr('class') || '').toLowerCase();
        var combined = href + ' ' + title + ' ' + textValue + ' ' + classes + ' ' + iconClass;

        var isEditOrDelete =
            combined.indexOf('edit') !== -1 ||
            combined.indexOf('delete') !== -1 ||
            combined.indexOf('ပြင်') !== -1 ||
            combined.indexOf('ဖျက်') !== -1;

        if (isEditOrDelete) {
            return false;
        }

        return (
            classes.indexOf('open-image') !== -1 ||
            href.indexOf('/uploads/') !== -1 ||
            combined.indexOf('view_image') !== -1 ||
            combined.indexOf('product_image') !== -1 ||
            combined.indexOf('picture') !== -1 ||
            combined.indexOf('photo') !== -1 ||
            combined.indexOf('ပုံကြည့်') !== -1 ||
            combined.indexOf('ပုံကြည့်') !== -1 ||
            (
                combined.indexOf('image') !== -1 &&
                combined.indexOf('view') !== -1
            )
        );
    }

    function styleActionLink($original) {
        var $link = $original.clone();
        if (isAppMode && $link.attr('href')) {
            var actionUrl = new URL($link.attr('href'), window.location.href);
            if (actionUrl.origin === window.location.origin) {
                actionUrl.searchParams.set('app', '1');
                actionUrl.searchParams.set('app_lang', <?= json_encode($app_language); ?>);
                $link.attr('href', actionUrl.href);
            }
        }
        var info = actionTypeAndIcon($link);
        var $labelSource = $link.clone();

        $labelSource.find('i, .fa, .glyphicon, .caret').remove();

        var label = $.trim($labelSource.text());
        if (!label) {
            label = $.trim($link.attr('title') || '<?= html_escape($actions_label); ?>');
        }

        var originalClasses = $.trim($link.attr('class') || '');

        $link
            .attr(
                'class',
                originalClasses +
                ' erp-action-btn erp-action-' +
                info.type
            )
            .removeAttr('style')
            .attr('aria-label', label)
            .empty()
            .append(
                $('<span>', { 'class': 'erp-action-icon' }).append(
                    $('<i>', { 'class': info.icon })
                )
            )
            .append(
                $('<span>', {
                    'class': 'erp-action-label',
                    text: label
                })
            );

        return $('<div>').append($link).html();
    }

    function buildProductActions(data, type, row) {
        if (type !== 'display') {
            return row && row.pid ? row.pid : '';
        }

        var productId = row && row.pid ? row.pid : '';
        var $source = $('<div>').html(data || '');
        var buttons = [];
        var deleteButtons = [];
        var hasUnitConversion = false;

        $source.find('a').each(function () {
            var $link = $(this);
            var href = String($link.attr('href') || '');

            if (isProductPictureAction($link)) {
                return;
            }

            var actionInfo = actionTypeAndIcon($link);

            /* This product-list page must not show the selling-price action. */
            if (actionInfo.type === 'price') {
                return;
            }

            if (href.indexOf('unit_conversions') !== -1) {
                hasUnitConversion = true;
            }

            /* Keep delete separately so it is always rendered last. */
            if (actionInfo.type === 'delete') {
                deleteButtons.push(styleActionLink($link));
                return;
            }

            buttons.push(styleActionLink($link));
        });

        if (productId && !hasUnitConversion) {
            var unitUrl =
                '<?= site_url('products/unit_conversions/'); ?>' +
                encodeURIComponent(productId) +
                '<?= $is_app_mode
                    ? '?app=1&app_lang=' . rawurlencode($app_language)
                    : ''; ?>';

            var $unitLink = $('<a>', {
                href: unitUrl,
                'class': 'tip',
                title: '<?= html_escape($manage_unit_conversion_label); ?>'
            }).append(
                $('<i>', { 'class': 'fa fa-balance-scale' })
            ).append(
                document.createTextNode(' <?= html_escape($unit_conversion_label); ?>')
            );

            buttons.push(styleActionLink($unitLink));
        }

        /* ဖျက်ရန် button ကို Action များ၏ နောက်ဆုံးနေရာတွင် ထည့်ရန် */
        buttons = buttons.concat(deleteButtons);

        if (buttons.length) {
            return '<div class="erp-actions">' + buttons.join('') + '</div>';
        }

        return '<div class="erp-actions"></div>';
    }

    function buildUnitType(data, type, row) {
        var rawDualValue = row && row.is_dual_unit !== undefined
            ? String(row.is_dual_unit).toLowerCase()
            : '0';

        var isDual =
            rawDualValue === '1' ||
            rawDualValue === 'true' ||
            rawDualValue === 'yes';

        var dualLabel =
            <?= json_encode(
                $dual_unit_label,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        var normalLabel =
            <?= json_encode(
                $normal_unit_label,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        var independentLabel =
            <?= json_encode(
                $independent_count_label,
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            ); ?>;

        var secondaryUnit = row && row.secondary_unit_name
            ? String(row.secondary_unit_name)
            : '';

        if (type !== 'display') {
            return isDual
                ? dualLabel + (secondaryUnit ? ' - ' + secondaryUnit : '')
                : normalLabel;
        }

        if (!isDual) {
            return '<div class="erp-unit-type-wrap">' +
                '<span class="erp-unit-type-badge erp-unit-type-normal">' +
                    '<i class="fa fa-cube"></i>' +
                    '<span>' + $('<div>').text(normalLabel).html() + '</span>' +
                '</span>' +
            '</div>';
        }



        return '<div class="erp-unit-type-wrap">' +
            '<span class="erp-unit-type-badge erp-unit-type-dual">' +
                '<i class="fa fa-balance-scale"></i>' +
                '<span>' + $('<div>').text(dualLabel).html() + '</span>' +
            '</span>'
             +
        '</div>';
    }

    var isAppMode = <?= $is_app_mode ? 'true' : 'false'; ?>;

    var table = $('#prTables').DataTable({
    'ajax': {
        url: '<?= site_url('products/get_products/'.$store->id); ?>',
        type: 'POST',
        data: function(d) {
            d.<?=$this->security->get_csrf_token_name();?> =
                "<?=$this->security->get_csrf_hash()?>";
        }
    },

    "columns": [
        {
            "data": "pid",
            "visible": false,
            "type": "num"
        },
        {
            "data": "image",
            "searchable": false,
            "orderable": false,
            "render": image,
            "className": "text-center"
        },
        {
            "data": "code"
        },
        {
            "data": "pname"
        },
        {
            "data": "cname"
        },
        {
            "data": "base_quantity",
            "render": quantityFormat,
            "className": "qty-cell"
        },
        {
            "data": "base_unit_name",
            "defaultContent": "-",
            "className": "unit-cell"
        },
        {
            "data": "is_dual_unit",
            "defaultContent": "0",
            "render": buildUnitType,
            "searchable": false,
            "orderable": false,
            "className": "unit-type-cell"
        },
        {
            "data": null,
            "render": function (data, type, row) {
                return currencyFormat(productPurchasePrice(row), type);
            },
            "searchable": false,
            "className": "price-cell purchase-price-cell"
        },
        {
            "data": null,
            "render": function (data, type, row) {
                return currencyFormat(productSellingPrice(row), type);
            },
            "searchable": false,
            "className": "price-cell selling-price-cell"
        },
        {
            "data": "Actions",
            "render": buildProductActions,
            "searchable": false,
            "orderable": false,
            "className": "action-cell"
        }
    ],

    "buttons": [
        {
            extend: 'copyHtml5',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'excelHtml5',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'csvHtml5',
            exportOptions: { columns: ':visible' }
        },
        {
            extend: 'pdfHtml5',
            exportOptions: { columns: ':visible' },
            orientation: 'landscape',
            pageSize: 'A4'
        },
        {
            extend: 'colvis',
            text: 'Columns'
        }
    ],

    /* နောက်ဆုံးထည့်ထားသည့် Product ကို အပေါ်ဆုံးပြရန် */
    "order": [[0, 'desc']],

    /* Browser မှ သိမ်းထားသော အရင် sorting ကို မသုံးရန် */
    "stateSave": false,

    "pageLength": 10,
    "orderCellsTop": true,
    /* Native .table-responsive handles horizontal scrolling more cleanly. */
    "scrollX": false,
    "autoWidth": false,

    "initComplete": function() {
        var api = this.api();
        var filterTimer = null;

        function applyAndFilters() {
            var changed = false;

            $('#product-column-filters .erp-and-filter-input[data-column]').each(function() {
                var columnIndex = parseInt($(this).attr('data-column'), 10);
                var value = $.trim($(this).val());
                var column = api.column(columnIndex);

                if (column.search() !== value) {
                    column.search(value);
                    changed = true;
                }
            });

            if (changed) {
                api.draw();
            }
        }

        $('#product-column-filters .erp-and-filter-input[data-column]').each(function() {
            var $input = $(this);

            $input
                .on('click', function(e) {
                    e.stopPropagation();
                })
                .on('input search keyup change clear', function() {
                    clearTimeout(filterTimer);
                    filterTimer = setTimeout(applyAndFilters, 180);
                });
        });
    }
});

    if (isAppMode) {
        /*
         * Mobile: hide only image and category.
         * Quantity, base unit, purchase price, selling price and actions
         * all remain visible.
         */
        table.columns([1, 4]).visible(false, false);

        setTimeout(function() {
            table.columns.adjust().draw(false);
        }, 300);
    }

    $('#prTables').on('click', '.open-image', function(e) {
        e.preventDefault();

        var a_href = $(this).attr('href');
        var rowData = table.row($(this).closest('tr')).data();
        var code = rowData && rowData.code ? rowData.code : '';

        $('#myModalLabel').text(code);
        $('#product_image').attr('src', a_href);
        $('#picModal').modal();
    });

    /* Delete ကို တန်းမဖျက်ဘဲ အတည်ပြုချက် အရင်မေးရန် */
    $('#prTables').on('click', '.erp-action-delete', function(e) {
        var confirmed = window.confirm(
            <?= json_encode($delete_confirm_message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        );

        if (!confirmed) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }

        return true;
    });

});
</script>
