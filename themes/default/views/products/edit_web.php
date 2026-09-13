<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>
<?php
$unit_options = '';
foreach ($units as $unit) {
    $unit_options .= '<option value="' . $unit->id . '">' . $unit->name . '</option>';
}
?>
<script>
    var unit_options = '<?= $unit_options ?>';
</script>

<section class="content">
    <div class="row">
        <?php if ($error) {
            echo '<div class="alert alert-danger">' . $error . '</div>';
        } ?>

        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4><?= $page_title; ?></h4>
                </div>
                <div class="box-body">
                    <div class="col-lg-12">
                        <?= form_open_multipart("products/edit/" . $product->id, ['class' => 'validation']); ?>

                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group form-group-lg">
                                    <?= lang('name', 'name'); ?>
                                    <?= form_input('name', $product->name, 'class="form-control tip" id="name"  required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang('code', 'code'); ?> <?= lang('can_use_barcode'); ?>
                                    <?= form_input('code', $product->code, 'class="form-control tip" id="code"  required="required"'); ?>
                                </div>
                                
                                <div class="form-group form-group-lg">
                                    <?= lang('basic_unit', 'basic_unit'); ?>
                                    <?php
                                    $unt[''] = lang("select") . " " . lang("unit");
                                    foreach ($units as $unit) {
                                        $unt[$unit->id] = $unit->name;
                                    }
                                    ?>
                                    <?= form_dropdown('base_unit_id', $unt, $product->base_unit_id, 'class="form-control select2 tip" id="unit"  required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang('product_tax', 'product_tax'); ?>
                                    <?= form_input('product_tax', $product->tax, 'class="form-control tip" id="product_tax"  required="required"'); ?>
                                </div>
                                <div class="form-group form-group-lg">
                                    <?= lang('alert_quantity', 'alert_quantity'); ?>
                                    <?= form_input('alert_quantity', set_value('alert_quantity', $product->alert_quantity), 'class="form-control tip" id="alert_quantity"  required="required"'); ?>
                                </div>

                                <div class="form-group form-group-lg">
                                    <?= lang('type', 'type'); ?>
                                    <?php $opts = array('standard' => lang('standard'), 'combo' => lang('combo'), 'service' => lang('service')); ?>
                                    <?= form_dropdown('type', $opts, set_value('type', $product->type), 'class="form-control tip select2" id="type"  required="required" style="width:100%;"'); ?>
                                </div>

                                <div class="form-group form-group-lg">
                                    <label>
                                        <input type="checkbox" name="has_unit_conversion" value="1" 
                                            <?= $product->has_unit_conversion ? 'checked' : '' ?>>
                                        <?= lang('has_unit_conversion'); ?>
                                    </label>
                                </div>

                                <div class="form-group form-group-lg">
                                    <label>
                                        <input type="checkbox" name="is_dual_unit" value="1" 
                                            <?= $product->is_dual_unit ? 'checked' : '' ?>>
                                        <?= lang('is_dual_unit'); ?>
                                    </label>
                                </div>

                                <div class="form-group form-group-lg">
                                    <?= lang('secondary_unit_id', 'secondary_unit_id'); ?>
                                    <?php
                                    $unit_opts[''] = lang("select") . " " . lang("unit");
                                    foreach ($units as $unit) {
                                        $unit_opts[$unit->id] = $unit->name;
                                    }
                                    ?>
                                    <?= form_dropdown('secondary_unit_id', $unit_opts, $product->secondary_unit_id, 'class="form-control select2 tip" id="secondary_unit_id"'); ?>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <div class="form-group all">
                                    <?= lang("barcode_symbology", "barcode_symbology") ?>
                                    <?php
                                    $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca' => 'UPC-A', 'upce' => 'UPC-E');
                                    echo form_dropdown('barcode_symbology', $bs, set_value('barcode_symbology', $product->barcode_symbology), 'class="form-control select2" id="barcode_symbology" required="required" style="width:100%;"');
                                    ?>
                                </div>

                                <div class="form-group form-group-lg">
                                    <?= lang('category', 'category'); ?>
                                    <?php
                                    $cat[''] = lang("select") . " " . lang("category");
                                    foreach ($categories as $category) {
                                        $cat[$category->id] = $category->name;
                                    }
                                    ?>
                                    <?= form_dropdown('category', $cat, $product->category_id, 'class="form-control select2 tip" id="category"  required="required"'); ?>
                                </div>





                                


                                <div class="form-group form-group-lg">
                                    <?= lang('tax_method', 'tax_method'); ?>
                                    <?php $tm = array(0 => lang('inclusive'), 1 => lang('exclusive')); ?>
                                    <?= form_dropdown('tax_method', $tm, set_value('tax_method', $product->tax_method), 'class="form-control tip select2" id="tax_method"  required="required" style="width:100%;"'); ?>
                                </div>


                                <div class="form-group">
                                    <?= lang('image', 'image'); ?>
                                    <input type="file" name="userfile" id="image">
                                </div>
                                <div id="ct" style="display:none;">
                                    <div class="form-group form-group-lg">
                                        <?= lang("add_product", "add_item"); ?>
                                        <?php echo form_input('add_item', '', 'class="form-control ttip" id="add_item" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_items_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                                    </div>
                                    <div class="control-group table-group">
                                        <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>

                                        <div class="controls table-controls">
                                            <table id="prTable"
                                                class="table items table-striped table-bordered table-condensed table-hover">
                                                <thead>
                                                    <tr>
                                                        <th class="col-xs-9"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                                        <th class="col-xs-2"><?= lang("quantity"); ?></th>
                                                        <th class=" col-xs-1 text-center"><i class="fa fa-trash-o trash-opacity-50"></i></th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                                <tfoot></tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="form-group form-group-lg">
                            <label>Unit Conversions</label>
                            <table class="table table-bordered" id="unit-conversion-table">
                                <thead>
                                    <tr>
                                        <th>Unit</th>
                                        <th>Operator</th>
                                        <th>Value</th>
                                        <th><button type="button" class="btn btn-xs btn-primary add-row">+</button></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if (!empty($product_conversions)) {
                                        foreach ($product_conversions as $conversion) { ?>
                                            <tr>
                                                <td>
                                                    <select name="conversion_unit_id[]" class="form-control">
                                                        <?php foreach ($units as $unit) { ?>
                                                            <option value="<?= $unit->id ?>" <?= $conversion->unit_id == $unit->id ? 'selected' : '' ?>>
                                                                <?= $unit->name ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>

                                                </td>
                                                <td>
                                                    <select name="operator[]" class="form-control">
                                                        <option value="*" <?= $conversion->operator == '*' ? 'selected' : '' ?>>×</option>
                                                        <option value="/" <?= $conversion->operator == '/' ? 'selected' : '' ?>>÷</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.0001" name="operation_value[]" class="form-control" value="<?= $conversion->operation_value ?>" required>

                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-danger remove-row">×</button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group form-group-lg">
                            <label>Unit Prices</label>
                            <table class="table table-bordered" id="unit-price-table">
                                <thead>
                                    <tr>
                                        <th>Unit</th>
                                        <th>Price (<?= $Settings->symbol ?>)</th>
                                        <th><button type="button" class="btn btn-xs btn-primary add-price-row">+</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($product_unit_prices)) {
                                        foreach ($product_unit_prices as $price_row) { ?>
                                            <tr>
                                                <td>
                                                    <select name="unit_price_unit_id[]" class="form-control">
                                                        <?php foreach ($units as $unit) { ?>
                                                            <option value="<?= $unit->id ?>" <?= $price_row->unit_id == $unit->id ? 'selected' : '' ?>>
                                                                <?= $unit->name ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.0001" name="unit_price_value[]" class="form-control" value="<?= $price_row->price ?>" required>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-danger remove-row">×</button>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group form-group-lg">
                            <?= lang('details', 'details'); ?>
                            <?= form_textarea('details', $product->details, 'class="form-control tip redactor" id="details"'); ?>
                        </div>
                        <div class="form-group form-group-lg">
                            <?= form_submit('update', lang('update'), 'class="btn btn-primary"'); ?>
                        </div>
                        <?= form_close(); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript" charset="utf-8">
    var price = 0;
    cost = 0;
    items = {};
    $(document).ready(function() {
        $('#type').change(function(e) {
            var type = $(this).val();
            if (type == 'combo') {
                $('.st').slideUp();
                $('#ct').slideDown();
                //$('#cost').attr('readonly', true);
            } else if (type == 'service') {
                $('.st').slideUp();
                $('#ct').slideUp();
                //$('#cost').attr('readonly', false);
            } else {
                $('#ct').slideUp();
                $('.st').slideDown();
                //$('#cost').attr('readonly', false);
            }
        });

        $("#add_item").autocomplete({
            source: '<?= site_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 200,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_product_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_product_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        $('#add_item').bind('keypress', function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(this).autocomplete("search");
            }
        });

        $(document).on('click', '.del', function() {
            var id = $(this).attr('id');
            delete items[id];
            $(this).closest('#row_' + id).remove();
        });


        $(document).on('change', '.rqty', function() {
            var item_id = $(this).attr('data-item');
            items[item_id].row.qty = (parseFloat($(this).val())).toFixed(2);
            add_product_item(null, 1);
        });

        $(document).on('change', '.rprice', function() {
            var item_id = $(this).attr('data-item');
            items[item_id].row.price = (parseFloat($(this).val())).toFixed(2);
            add_product_item(null, 1);
        });

        function add_product_item(item, noitem) {
            if (item == null && noitem == null) {
                return false;
            }
            if (noitem != 1) {
                item_id = item.row.id;
                if (items[item_id]) {
                    items[item_id].row.qty = (parseFloat(items[item_id].row.qty) + 1).toFixed(2);
                } else {
                    items[item_id] = item;
                }
            }
            price = 0;
            cost = 0;

            $("#prTable tbody").empty();
            $.each(items, function() {
                var item = this.row;
                var row_no = item.id;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + item.id + '"></tr>');
                tr_html = '<td><input name="combo_item_code[]" type="hidden" value="' + item.code + '"><span id="name_' + row_no + '">' + item.name + ' (' + item.code + ')</span></td>';
                tr_html += '<td><input class="form-control text-center rqty" name="combo_item_quantity[]" type="text" value="' + formatDecimal(item.qty) + '" data-id="' + row_no + '" data-item="' + item.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                //tr_html += '<td><input class="form-control text-center rprice" name="combo_item_price[]" type="text" value="' + formatDecimal(item.price) + '" data-id="' + row_no + '" data-item="' + item.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#prTable");
                //price += formatDecimal(item.price*item.qty);
                cost += formatDecimal(item.cost * item.qty);
            });
            $('#cost').val(cost);
            return true;
        }
        var type = $('#type').val();
        if (type == 'combo') {
            $('.st').slideUp();
            $('#ct').slideDown();
            //$('#cost').attr('readonly', true);
        } else if (type == 'service') {
            $('.st').slideUp();
            $('#ct').slideUp();
            //$('#cost').attr('readonly', false);
        } else {
            $('#ct').slideUp();
            $('.st').slideDown();
            //$('#cost').attr('readonly', false);
        }
        <?php
        if ($this->input->post('type') == 'combo') {
            $c = sizeof($_POST['combo_item_code']);
            $items = array();
            for ($r = 0; $r <= $c; $r++) {
                if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r])) {
                    $items[] = array('id' => $_POST['combo_item_id'][$r], 'row' => array('id' => $_POST['combo_item_id'][$r], 'name' => $_POST['combo_item_name'][$r], 'code' => $_POST['combo_item_code'][$r], 'qty' => $_POST['combo_item_quantity'][$r], 'cost' => $_POST['combo_item_cost'][$r]));
                }
            }
            echo '
            var ci = ' . json_encode($items) . ';
            $.each(ci, function() { add_product_item(this); });
            ';
        } elseif (!empty($items)) {
            echo '
            var ci = ' . json_encode($items) . ';
            $.each(ci, function() { add_product_item(this); });
            ';
        }
        ?>
    });
</script>
<script>
    $(document).on('click', '.add-row', function() {
        var row = '<tr>' +
            '<td><select name="conversion_unit_id[]" class="form-control">' + unit_options + '</select></td>' +
            '<td><select name="operator[]" class="form-control"><option value="*">×</option><option value="/">÷</option></select></td>' +
            // Default operation_value to 1 so it's never empty by default
            '<td><input type="number" step="0.0001" name="operation_value[]" class="form-control" value="1"></td>' +
            '<td><button type="button" class="btn btn-xs btn-danger remove-row">×</button></td>' +
            '</tr>';
        $('#unit-conversion-table tbody').append(row);
    });


    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });

    $(document).on('click', '.add-price-row', function() {
        var row = '<tr>' +
            '<td><select name="unit_price_unit_id[]" class="form-control">' + unit_options + '</select></td>' +
            '<td><input type="number" step="0.0001" name="unit_price_value[]" class="form-control" required></td>' +
            '<td><button type="button" class="btn btn-xs btn-danger remove-row">×</button></td>' +
            '</tr>';
        $('#unit-price-table tbody').append(row);
    });

    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });
</script>