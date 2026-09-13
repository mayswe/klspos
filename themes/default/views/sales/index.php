<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>
<script>
var table; // ✅ GLOBAL
</script>
<script type="text/javascript">
$(document).ready(function() {

    // Render payment status
    function status(data, type, row) {
        var paid = '<?= lang('paid'); ?>';
        var partial = '<?= lang('partial'); ?>';
        var due = '<?= lang('due'); ?>';
        if (data == 'paid') {
            return '<div class="text-center"><span class="sale_status label label-success">'+paid+'</span></div>';
        } else if (data == 'partial') {
            return '<div class="text-center"><span class="sale_status label label-primary">'+partial+'</span></div>';
        } else if (data == 'due') {
            return '<div class="text-center"><span class="sale_status label label-danger">'+due+'</span></div>';
        } else {
            return '<div class="text-center"><span class="sale_status label label-default">'+data+'</span></div>';
        }
    }

    // Render preorder column with Deliver button
    function is_preorder(data, type, row) {
        if (data == 1) {
            return `<div class="text-center">
                        <span class="label label-warning"><?= lang("preorder"); ?></span>
                        <button class="btn btn-xs btn-success deliver_preorder" data-id="${row.id}" style="margin-left:5px;">
                            <?= lang("deliver"); ?>
                        </button>
                    </div>`;
        } else {
            return '<div class="text-center"><span class="label label-success"><?= lang("delivered"); ?></span></div>';
        }
    }

    table = $('#SLData').DataTable({
        'ajax' : { 
            url: '<?=site_url('sales/get_sales');?>', 
            type: 'POST', 
            data: function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
                // Add filter values
                d.status_filter = $('select.select_filter[data-column="9"]').val();
                d.customer_filter = $('select.select_filter[data-column="3"]').val();
            }
        },
        "buttons": [
            { extend: 'copyHtml5', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'excelHtml5', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'csvHtml5', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', footer: true, exportOptions: 
            { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'colvis', text: 'Columns' }
        ],
        "columns": [
            {
                data: 'id',
                render: function(id) {
                    return `<input type="checkbox" class="sale-check" value="${id}">`;
                }
            },
            { "data": "id" },
            { "data": "date", "render": hrld },
            { "data": "customer_name" },
            { "data": "total", "render": currencyFormat },
            { "data": "total_tax", "render": currencyFormat },
            { "data": "total_discount", "render": currencyFormat },
            { "data": "grand_total", "render": currencyFormat },
            { "data": "paid", "render": currencyFormat },
            { "data": "status", "render": status },
            { "data": "is_preorder", "render": is_preorder },
            { "data": "Actions", "searchable": false, "orderable": false }
        ],
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            nRow.id = aData.id;
            return nRow;
        },
        "footerCallback": function (tfoot, data, start, end, display) {
            var api = this.api();
            
            $(api.column(4).footer()).html(cf(api.column(4).data().reduce((a,b)=>pf(a)+pf(b),0)));
            $(api.column(5).footer()).html(cf(api.column(5).data().reduce((a,b)=>pf(a)+pf(b),0)));
            $(api.column(6).footer()).html(cf(api.column(6).data().reduce((a,b)=>pf(a)+pf(b),0)));
            $(api.column(7).footer()).html(cf(api.column(7).data().reduce((a,b)=>pf(a)+pf(b),0)));
            $(api.column(8).footer()).html(cf(api.column(8).data().reduce((a,b)=>pf(a)+pf(b),0)));
        }
    });

    // Global search
    $('#search_table').on('keyup change', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if ((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === '')) {
            table.search(this.value).draw();
        }
    });
    
    // Column filters (AND logic)
        $('#SLData tfoot').on('keyup change', 'input.text_filter, select.select_filter', function (e) {
            var colIndex = $(this).closest('th').index();
            var value = this.value;
            var column = table.column(colIndex);

            // Only trigger search when pressing Enter for text fields
            if ($(this).is('input') && e.type === 'keyup' && e.keyCode !== 13) return;

            column.search(value).draw();
        });

        $('#SLData tfoot').on('change', 'select.select_filter', function() {
            table.ajax.reload();
        });

    
    $('#SLData').on('click', '.deliver_preorder', function() {
        var sale_id = $(this).attr('data-id'); // safer than .data('id') sometimes
        if (!sale_id) {
            alert('Sale ID not found!');
            return;
        }
        $.ajax({
            url: '<?= site_url('sales/deliver_preorder') ?>',
            type: 'POST',
            data: {
                sale_id: sale_id,
                "<?=$this->security->get_csrf_token_name()?>": "<?=$this->security->get_csrf_hash()?>"
            },
            success: function(response) {
                table.ajax.reload(null, false);
            },
            error: function(xhr, status, error) {
                alert('Error delivering preorder: ' + xhr.responseText);
            }
        });
    });

    
     $('#SLData').on('draw.dt', function () {
        $('li[data-edit-visible]').each(function () {
            const is_preorder = $(this).attr('data-edit-visible');
            if (is_preorder == 1) {
                $(this).hide(); // hide Edit button
            }
        });
    });
    
    // Select all
    $('#check_all').on('change', function () {
        $('.sale-check').prop('checked', this.checked);
    });


    $('#SLData').on('draw.dt', function () {
        $('#check_all').prop('checked', false);
    });
    
    $('#bulk_pay_btn').on('click', function () {

        let amount = parseFloat($('#bulk_pay_amount').val());
        if (!amount || amount <= 0) {
            alert('Enter valid amount');
            return;
        }
    
        let sale_ids = [];
        $('.sale-check:checked').each(function () {
            let row = $(this).closest('tr');
            let status = row.find('.sale_status').text().toLowerCase();
        
            if (status !== 'paid') {
                sale_ids.push($(this).val());
            }
        });

    
        if (sale_ids.length === 0) {
            alert('Select at least one sale');
            return;
        }
    
        if (!confirm('Apply payment to selected sales?')) return;
    
        $.ajax({
            url: '<?= site_url('sales/bulk_due_payment') ?>',
            type: 'POST',
            data: {
                sale_ids: sale_ids,
                amount: amount,
                "<?= $this->security->get_csrf_token_name() ?>":
                "<?= $this->security->get_csrf_hash() ?>"
            },
            success: function (res) {
                $('#bulk_pay_amount').val('');
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseText);
            }
        });
    });

    

    // Initialize Select2 for all select filters
    $('.select2.select_filter').select2({ width: '100%' });

    // Handle footer filter changes
    $('#SLData tfoot').on('keyup change', 'input.text_filter, select.select_filter', function (e) {
        var $this = $(this);
        var colIndex = $this.data('column');

        // For text inputs, trigger only on Enter key
        if ($this.is('input') && e.type === 'keyup' && e.keyCode !== 13) return;
        if (colIndex === undefined) {
            colIndex = $this.closest('th').index(); // fallback
        }

        var value = $this.val();
        table.column(colIndex).search(value).draw();
    });

    // Initialize datepickers in footer
    $('.datepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        showClear: true,
        showClose: true,
        useCurrent: false,
        widgetPositioning: { horizontal: 'auto', vertical: 'bottom' },
        widgetParent: $('.dataTable tfoot')
    });
    
    // Whenever a checkbox changes, recalc the total due
    $('#SLData').on('change', '.sale-check', function() {
        let total_due = 0;
    
        $('.sale-check:checked').each(function() {
            // Get the row data
            let row_data = table.row($(this).closest('tr')).data();
            if (row_data) {
                // due = grand_total - paid
                let due = parseFloat(row_data.grand_total) - parseFloat(row_data.paid);
                total_due += due;
            }
        });
    
        
        // Update the display with comma
        $('#selected_due_total').text(
            total_due.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        );
        

    });
    
    // Reset total when "Select All" is clicked
    $('#check_all').on('change', function () {
        $('.sale-check').prop('checked', this.checked).trigger('change');
    });

    
});



</script>



<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <div class="row" style="margin-bottom:10px;">
    <div class="col-md-3">
        <strong>Total Due for Selected:</strong> 
        <span id="selected_due_total">0.00</span>
    </div>
</div>

                    <div class="row">
    <div class="col-md-3">
        <input type="number" id="bulk_pay_amount" class="form-control"
               placeholder="Pay Amount">
    </div>

    <div class="col-md-2">
        <button id="bulk_pay_btn" class="btn btn-success">
            <i class="fa fa-money"></i> Pay Selected
        </button>
    </div>
</div>

                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="SLData" class="table table-striped table-bordered table-condensed table-hover">
    <thead>
        <tr class="active">
            <th style="width:30px;text-align:center;">
                <input type="checkbox" id="check_all">
            </th>
            <th style="max-width:30px;"><?= lang("id"); ?></th>
            <th ><?= lang("date"); ?></th>
            <th class="col-xs-2"><?= lang("customer"); ?></th>
            <th class="col-xs-1"><?= lang("total"); ?></th>
            <th class="col-xs-1"><?= lang("tax"); ?></th>
            <th class="col-xs-1"><?= lang("discount"); ?></th>
            <th class="col-xs-1"><?= lang("grand_total"); ?></th>
            <th class="col-xs-1"><?= lang("paid"); ?></th>
            <th class="col-xs-1"><?= lang("status"); ?></th>
            <th class="col-xs-1"><?= lang("is_preorder"); ?></th> <!-- Added -->
            <th style="min-width:115px; max-width:115px; text-align:center;"><?= lang("actions"); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td> <!-- Updated colspan -->
        </tr>
    </tbody>
    <!-- Footer filters HTML -->
<tfoot>
    <tr class="active">
        <th></th>
        <th><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
        <th></th>
        <th>
            <select class="select2 select_filter" data-column="3">
                <option value=""><?= lang("all"); ?></option>
                <?php foreach($customers as $cs): ?>
                    <option value="<?= $cs->name; ?>"><?= $cs->name; ?></option>
                <?php endforeach; ?>
            </select>
        </th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th>
            <select class="select2 select_filter" data-column="9"> 
                <option value=""><?= lang("all"); ?></option> <option value="paid"><?= lang("paid"); ?></option> 
                <option value="partial"><?= lang("partial"); ?></option> 
                <option value="due"><?= lang("due"); ?></option> 
                <option value="partial|due">Partial & Due</option> 
            </select>
        </th>
        <th>
            <select class="select2 select_filter" data-column="10">
                <option value=""><?= lang("all"); ?></option>
                <option value="1"><?= lang("preorder"); ?></option>
            </select>
        </th>
        <th></th>
    </tr>
    <tr>
        <td colspan="12"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
    </tr>
</tfoot>

</table>

                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
</section>
<?php if ($Admin) { ?>
<div class="modal fade" id="stModal" tabindex="-1" role="dialog" aria-labelledby="stModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fa fa-times"></i></span></button>
                <h4 class="modal-title" id="stModalLabel"><?= lang('update_status'); ?> <span id="status-id"></span></h4>
            </div>
            <?= form_open('sales/status'); ?>
            <div class="modal-body">
                <input type="hidden" value="" id="sale_id" name="sale_id" />
                <div class="form-group form-group-lg">
                    <?= lang('status', 'status'); ?>
                    <?php $opts = array('paid' => lang('paid'), 'partial' => lang('partial'), 'due' => lang('due'))  ?>
                    <?= form_dropdown('status', $opts, set_value('status'), 'class="form-control select2 tip" id="status" required="required" style="width:100%;"'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?= lang('update'); ?></button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.sale_status', function() {
            var sale_id = $(this).closest('tr').attr('id');
            var curr_status = $(this).text();
            var status = curr_status.toLowerCase();
            $('#status-id').text('( <?= lang('sale_id'); ?> '+sale_id+' )');
            $('#sale_id').val(sale_id);
            $('#status').val(status);
            $('#status').select2('val', status);
            $('#stModal').modal()
        });
    });
</script>
<?php } ?>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datetimepicker({format: 'YYYY-MM-DD', showClear: true, showClose: true, useCurrent: false, widgetPositioning: {horizontal: 'auto', vertical: 'bottom'}, widgetParent: $('.dataTable tfoot')});
    });
</script>

