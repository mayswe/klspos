<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$v = "?v=1";

if ($this->input->post('product')){
    $v .= "&product=".$this->input->post('product');
}
if ($this->input->post('start_date')){
    $v .= "&start_date=".$this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=".$this->input->post('end_date');
}

?>

<script type="text/javascript">
    $(document).ready(function() {

$(".mouse").on('click', function(event) {

    // Make sure this.hash has a value before overriding default behavior
    if (this.hash !== "") {
      // Prevent default anchor click behavior
      event.preventDefault();

      // Store hash
      var hash = this.hash;

      // Using jQuery's animate() method to add smooth page scroll
      // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 800, function(){
   
        // Add hash (#) to URL when done scrolling (default click behavior)
        window.location.hash = hash;
      });
    } // End if
  });
        var table = $('#PrRData').DataTable({
            'ajax' : { url: '<?=site_url('reports/get_investment/'. $v);?>', type: 'POST', "data": function ( d ) {
                d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
            }},
            "buttons": [],
            "columns": [
            { "data": "id" },
            { "data": "cname" },
            { "data": "pname" },
            { "data": "cost", "searchable": false, "render": currencyFormat },
            { "data": "quantity"},
            { "data": "investment", "searchable": false, "render": currencyFormat }
            ],
             "footerCallback": function (  tfoot, data, start, end, display ) {
                var api = this.api(), data;
                $(api.column(5).footer()).html( cf(api.column(5).data().reduce( function (a, b) { return pf(a) + pf(b); }, 0)) );
               
            }

        });

        $('#search_table').on( 'keyup change', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
                table.search( this.value ).draw();
            }
        });

        table.columns().every(function () {
            var self = this;
            $( 'input.datepicker', this.footer() ).on('dp.change', function (e) {
                self.search( this.value ).draw();
            });
            $( 'input:not(.datepicker)', this.footer() ).on('keyup change', function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
                    self.search( this.value ).draw();
                }
            });
            $( 'select', this.footer() ).on('change', function (e) {
                self.search( this.value ).draw();
            });
        });

    });
</script>

<script type="text/javascript">
    $(document).ready(function(){
        $('#form').hide();
        $('.toggle_form').click(function(){
            $("#form").slideToggle();
            return false;
        });
    });
</script>

<section class="content">

    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                
                <div class="box-body">
                  

                    <div class="row">
                        <div class="col-xs-12">
                        <div class="text-right">
			<a href="#section-2" class="mouse" aria-hidden="true">
    <span class="mouse__wheel"></span>
  </a>
  </div>
                            <div class="table-responsive">
                                <table id="PrRData" class="table table-striped table-bordered table-hover" style="margin-bottom:5px;">
                                    <thead>
                                        <tr class="active">
                                            <th style="max-width:30px;"><?= lang("id"); ?></th>
                                            <th class="col-xs-2"><?= lang("categories"); ?></th>
                                            <th><?= lang("name"); ?></th>
                                            <th class="col-xs-1"><?= lang("cost"); ?></th>
                                            <th class="col-xs-1"><?= lang("quantity"); ?></th>
                                            <th class="col-xs-1"><?= lang("investment"); ?></th>
                                        </tr>
                                        
                                    </thead>
                                   
                                    <tbody>
                                    
                                        <tr>
                                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="active">
                                            <th style="max-width:30px;"><input type="text" class="text_filter" placeholder="[<?= lang('id'); ?>]"></th>
                                            <th class="col-sm-2"><input type="text" class="text_filter" placeholder="[<?= lang('code'); ?>]"></th>
                                            <th><input type="text" class="text_filter" placeholder="[<?= lang('name'); ?>]"></th>
                                            
                                            <th class="col-xs-1"><?= lang("cost"); ?></th>
                                            <th class="col-xs-1"><?= lang("quantity"); ?></th>
                                            <th class="col-xs-1"><?= lang("investment"); ?></th>
                                        </tr>
                                        <tr>
                                            <td colspan="7" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
	<div id="section-2" class="text-center"><div class="scrollup" href="#"><i class="fa fa-angle-double-up" aria-hidden="true"></i></div></div>
	</div>
</section>

<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/moment.min.js" type="text/javascript"></script>
<script src="<?= $assets ?>plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });
        $('.datepicker').datetimepicker({format: 'YYYY-MM-DD', showClear: true, showClose: true, useCurrent: false, widgetPositioning: {horizontal: 'auto', vertical: 'bottom'}, widgetParent: $('.dataTable tfoot')});
    });
    $(window).scroll(function () {
if ($(this).scrollTop() > 400) {
$('.scrollup').fadeIn();
} else {
$('.scrollup').fadeOut();
}
});

</script>
<script>
    // scrolltop
$('.scrollup').click(function (){
$("html,body").animate({
scrollTop: 0
}, 1000);
return false;
});
</script>