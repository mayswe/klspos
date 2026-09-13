<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>
<script>
var table; // ✅ GLOBAL
</script>
<script type="text/javascript">
    $(document).ready(function() {

        if (get('remove_spo')) {
            if (get('spoitems')) {
                remove('spoitems');
            }
            remove('remove_spo');
        }
        <?php
        if ($this->session->userdata('remove_spo')) {
            ?>
            if (get('spoitems')) {
                remove('spoitems');
            }
            <?php
            $this->tec->unset_data('remove_spo');
        }
        ?>
        function attach(x) {
            if (x !== null) {
                return '<a href="<?=base_url();?>uploads/'+x+'" target="_blank" class="btn btn-primary btn-block btn-xs"><i class="fa fa-chain"></i></a>';
            }
            return '';
        }
        
        function status(data) {
            var paid = '<?= lang('paid'); ?>';
            var partial = '<?= lang('partial'); ?>';
            var due = '<?= lang('due'); ?>';
            if(data=='paid') return `<div class="text-center"><span class="sale_status label label-success">${paid}</span></div>`;
            if(data=='partial') return `<div class="text-center"><span class="sale_status label label-primary">${partial}</span></div>`;
            if(data=='due') return `<div class="text-center"><span class="sale_status label label-danger">${due}</span></div>`;
            return `<div class="text-center"><span class="sale_status label label-default">${data}</span></div>`;
        }
        
        function received(x){
            var yes = '<?= lang('Yes'); ?>';
            var no  = '<?= lang('No'); ?>';
            return `<div class="text-center"><span class="sale_status label label-${x=='1'?'success':'danger'}">${x=='1'?yes:no}</span></div>`;
        }

        

        table = $('#purData').DataTable({
            ajax: {
                url: '<?=site_url('purchases/get_purchases');?>',
                type: 'POST',
                data: function(d) {
                    d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash() ?>";
                    d.status_filter = $('select[name="status_filter"]').val(); // the dropdown for status
                    d.supplier_filter = $('#supplierFilter').val();
                    d.received_filter = $('#receivedFilter').val();
                }
            },
            buttons: [],
            columns: [
                { 
                    data: "id",
                    orderable: false,
                    render: data => `<input type="checkbox" class="purchase_check" value="${data}">`
                },
                { data: "id" },
                { 
                    data: "date",
                    render: function(data, type) {
                        if(!data) return '';
                        return data.substr(0, 10);
                    }
                },
                { data: "name" },
                { data: "total", render: currencyFormat },
                { data: "paid", render: currencyFormat },
                { data: "balance", orderable: false, searchable: false, render: currencyFormat },
                { data: "status", render: status },
                { data: "received", render: received },
                { data: "Actions", orderable: false, searchable: false }
            ],
            footerCallback: function () {
                var api = this.api();
                $(api.column(4).footer()).html(cf(api.column(4).data().reduce((a,b)=>pf(a)+pf(b),0)));
                $(api.column(5).footer()).html(cf(api.column(5).data().reduce((a,b)=>pf(a)+pf(b),0)));
                $(api.column(6).footer()).html(cf(api.column(6).data().reduce((a,b)=>pf(a)+pf(b),0)));
            }
        });


        $('#search_table').on( 'keyup change', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search( this.value ).draw();
            }
        });

        // Column filters (AND logic)
        $('#purData tfoot').on('keyup change', 'input.text_filter, select.select_filter', function (e) {
            var colIndex = $(this).closest('th').index();
            var value = this.value;
            var column = table.column(colIndex);

            // Only trigger search when pressing Enter for text fields
            if ($(this).is('input') && e.type === 'keyup' && e.keyCode !== 13) return;

            column.search(value).draw();
        });


        table.columns().every(function () {
            var self = this;
            $('input.datepicker', this.footer()).on('dp.change', function (e) {
                self.search(this.value).draw();
            });

            $( 'input:not(.datepicker)', this.footer() ).on('keyup change', function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                    self.search( this.value ).draw();
                }
            });
            $( 'select', this.footer() ).on( 'change', function (e) {
                self.search( this.value ).draw();
            });
        });
        
        $('#supplierFilter').on('change', function () {
            table.column(3).search(this.value).draw();
        });


    });
    $(document).on('click', '.receive-link', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    if (!id) return;

    if (!confirm('Receive stock for this purchase?')) return;

    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

    var postData = {};
    postData['purchase_id'] = id;
    postData[csrfName] = csrfHash;

    $.ajax({
        url: '<?= site_url("purchases/receive_products"); ?>',
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function (res) {
            if (res.status === 'success') {
                alert('Stock received successfully');
                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                } else {
                    location.reload();
                }
            } else {
                alert(res.message || 'Receive failed');
            }
        },
        error: function () {
            alert('Request failed');
        }
    });
});


</script>
<script>
$(document).ready(function () {

    // Check all
    $('#checkAll').on('change', function () {
        $('.purchase_check').prop('checked', this.checked);
    });

    // Bulk Pay
    $('#bulkPayBtn').on('click', function () {

        var amount = parseFloat($('#bulk_pay_amount').val());
        if (!amount || amount <= 0) {
            alert('Enter valid payment amount');
            return;
        }

        var ids = [];
        $('.purchase_check:checked').each(function () {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            alert('Select at least one purchase');
            return;
        }

        if (!confirm('Pay selected purchases?')) return;

        $.ajax({
            url: '<?= site_url("purchases/bulk_pay_due"); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                amount: amount,
                purchase_ids: ids,
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            success: function (res) {
                if (res.status === 'success') {
                    alert('Payment completed');
                    $('#bulk_pay_amount').val('');
                    table.ajax.reload(null, false); // ✅ now works
                } else {
                    alert(res.message || 'Payment failed');
                }
            },
            error: function () {
                alert('Server error');
            }
        });
    });
    
    
    // Whenever a checkbox changes, recalc the total due
    $('#purData').on('change', '.purchase_check', function() {
        let total_due = 0;
    
        $('.purchase_check:checked').each(function() {
            // Get the row data
            let row_data = table.row($(this).closest('tr')).data();
            if (row_data) {
                // due = grand_total - paid
                let due = parseFloat(row_data.total || 0) - parseFloat(row_data.paid || 0);
                total_due += due;
            }
        });
    
        // Update the display with comma
        $('#selected_due_total').text(
            total_due.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        );
    });
    
    // Reset total when "Select All" is clicked
    $('#checkAll').on('change', function () {
        $('.purchase_check').prop('checked', this.checked).trigger('change');
    });



});
</script>

<style type="text/css">
    .table td:nth-child(3) { text-align: right; }
</style>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h4><?= $page_title; ?></h4>
                    <div class="pull-right">
                        <a href="<?= site_url('purchases/add'); ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> <?= lang('add_purchase'); ?>
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom:10px;">
    <div class="col-md-3">
        <strong>Total Due for Selected:</strong> 
        <span id="selected_due_total">0.00</span>
    </div>
</div>

                    <div class="row" style="margin-bottom:10px;">
                        
                        <div class="form-group form-group-lg col-sm-3">
                            <input type="number" id="bulk_pay_amount" class="form-control" placeholder="Enter payment amount">
                        </div>
                        <div class="col-sm-2">
                            <button id="bulkPayBtn" class="btn btn-success btn-block">
                                <i class="fa fa-money"></i> Pay Selected
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                         <table id="purData" class="table table-striped table-bordered table-condensed table-hover" style="margin-bottom:5px;">
                            <thead>
                                <tr class="active">
                                    <th>
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th><?= lang("id"); ?></th>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('name'); ?></th>
                                    <th><?= lang('total'); ?></th>
                                    <th><?= lang('paid'); ?></th>
                                    <th><?= lang('due'); ?></th>
                                    <th><?= lang('status'); ?></th>
                                    <th><?= lang('Receive'); ?></th>
                                    <th style="width:75px;"><?= lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="active">
                                    <th></th>
                                    <th style="max-width:30px;"><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
                                     <th><span class="datepickercon"><input type="text" class="text_filter datepicker" placeholder="[<?= lang('date'); ?>]"></span></th>
                                    <th class="col-xs-2">
                                        <select class="select2 select_filter" data-column="3">
                                            <option value=""><?= lang("all"); ?></option>
                                            <?php foreach($suppliers as $cs): ?>
                                                <option value="<?= $cs->name; ?>"><?= $cs->name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </th>
                                    <th><?= lang('total'); ?></th>
                                    <th><?= lang('paid'); ?></th>
                                    <th><?= lang('due'); ?></th>
                                    <th>
                                        <select class="select2 select_filter" data-column="7">
                                            <option value=""><?= lang("all"); ?></option>
                                            <option value="paid"><?= lang("paid"); ?></option>
                                            <option value="partial"><?= lang("partial"); ?></option>
                                            <option value="due"><?= lang("due"); ?></option>
                                            <option value="notpaid">Partial & Due</option>
                                        </select>
                                    </th>
                                    
                                    <th>
                                        <select class="select2 select_filter" data-column="8">
                                            <option value=""><?= lang("all"); ?></option>
                                            <option value="1"><?= lang("Yes"); ?></option>
                                            <option value="0"><?= lang("No"); ?></option>
                                        </select>
                                    </th>

                                    <th style="width:75px;"><?= lang('actions'); ?></th>
                                </tr>
                                <tr>
                                    <td colspan="10" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datetimepicker({
            format: 'YYYY-MM-DD',
            showClear: true,
            showClose: true,
            useCurrent: false,
            widgetPositioning: { horizontal: 'auto', vertical: 'bottom' }
        });
    });
</script>
