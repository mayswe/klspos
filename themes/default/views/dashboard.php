<?php (defined('BASEPATH')) or exit('No direct script access allowed'); ?>

<script src="<?= $assets ?>plugins/highchart/highcharts.js"></script>

<?php
if ($chartData) {
    foreach ($chartData as $month_sale) {
        $months[]   = date('M-Y', strtotime($month_sale->month));
        $sales[]    = $month_sale->total;
        $tax[]      = $month_sale->tax;
        $discount[] = $month_sale->discount;
    }
} else {
    $months[]   = '';
    $sales[]    = '';
    $tax[]      = '';
    $discount[] = '';
}
?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();

            $.ajax({
                url: '<?= site_url('welcome/get_dashboard_data'); ?>',
                type: 'GET',
                dataType: 'json',
                data: {
                    start_date: start_date,
                    end_date: end_date
                },
                success: function(response) {
                    // Update info-box values
                    $('.today_sales_value').text(response.total_sales);
                    $('.today_purchases_value').text(response.total_purchases);
                    $('.today_expenses_value').text(response.total_expenses);
                    $('.today_profit_loss').text(response.profit_loss);

                    // Update charts (destroy and recreate)
                    if (response.chartData) {
                        $('#chart').highcharts(response.chartData.salesChart);
                        $('#chart2').highcharts(response.chartData.topProductsChart);
                        $('#topCustomersChart').highcharts(response.chartData.customersChart);
                        $('#todayExpensesChart').highcharts(response.chartData.expensesChart);
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                }
            });
        });


        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function(color) {
            return {
                radialGradient: {
                    cx: 0.5,
                    cy: 0.3,
                    r: 0.7
                },
                stops: [
                    [0, color],
                    [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]
                ]
            };
        });
        <?php if ($chartData) { ?>
            $('#chart').highcharts({
                chart: {
                    type: 'column'
                },
                credits: {
                    enabled: false
                },
                exporting: {
                    enabled: false
                },
                title: {
                    text: '<?= lang('monthly_sales_summary'); ?>'
                },
                xAxis: {
                    categories: [<?php foreach ($months as $month) {
                                        echo "'" . $month . "', ";
                                    } ?>],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: '<?= lang('amount'); ?>'
                    }
                },
                tooltip: {
                    shared: true,
                    useHTML: true,
                    headerFormat: '<span style="font-size:13px;"><b>{point.key}</b></span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:4px">{series.name}: </td>' +
                        '<td style="padding:4px"><b>{point.y}</b></td></tr>',
                    footerFormat: '</table>',
                    backgroundColor: '#F0F0F0',
                    borderColor: '#333',
                    borderRadius: 5,
                    shadow: true
                },
                plotOptions: {
                    column: {
                        borderRadius: 5,
                        pointPadding: 0.1,
                        borderWidth: 0
                    }
                },
                colors: ['#ff7f0e', '#2ca02c', '#1f77b4'],
                series: [{
                        name: '<?= $this->lang->line('tax'); ?>',
                        data: [<?= implode(', ', $tax); ?>]
                    },
                    {
                        name: '<?= $this->lang->line('discount'); ?>',
                        data: [<?= implode(', ', $discount); ?>]
                    },
                    {
                        name: '<?= $this->lang->line('sales'); ?>',
                        data: [<?= implode(', ', $sales); ?>]
                    }
                ]
            });

        <?php } ?>
        <?php if ($topProducts) { ?>
            $('#chart2').highcharts({
                chart: {
                    type: 'pie'
                },
                title: {
                    text: '<?= lang('top_products'); ?>'
                },
                credits: {
                    enabled: false
                },
                exporting: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '<b>{point.y}</b> units sold ({point.percentage:.1f}%)'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                fontSize: '13px'
                            }
                        },
                        showInLegend: true
                    }
                },
                colors: ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b'],
                series: [{
                    name: '<?= $this->lang->line('total_sold') ?>',
                    colorByPoint: true,
                    data: [
                        <?php
                        foreach ($topProducts as $tp) {
                            echo "['" . $tp->product_name . ' (' . $tp->product_code . ")', " . $tp->quantity . '],';
                        } ?>
                    ]
                }]
            });
        <?php } ?>
        <?php if ($topCustomers) { ?>
            $('#topCustomersChart').highcharts({
                chart: {
                    type: 'bar'
                },
                title: {
                    text: 'Top Customers'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: [<?php foreach ($topCustomers as $c) {
                                        echo "'" . $c->customer_name . "', ";
                                    } ?>]
                },
                yAxis: {
                    title: {
                        text: 'Total Purchase (<?= $Settings->currency; ?>)'
                    }
                },
                colors: ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b'],
                series: [{
                    name: 'Total Sales',
                    data: [<?php foreach ($topCustomers as $c) {
                                echo $c->total_amount . ', ';
                            } ?>]
                }]
            });
        <?php } ?>
        <?php if ($topExpense) { ?>
            $('#todayExpensesChart').highcharts({
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Today\'s Expenses by Type'
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '<b>{point.y:.2f} <?= $Settings->currency; ?></b> ({point.percentage:.1f}%)'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.percentage:.1f} %',
                            style: {
                                fontSize: '13px'
                            }
                        },
                        showInLegend: true
                    }
                },
                colors: ['#e74c3c', '#f39c12', '#3498db', '#2ecc71', '#9b59b6'],
                series: [{
                    name: '<?= lang("expenses"); ?>',
                    colorByPoint: true,
                    data: [
                        <?php foreach ($topExpense as $e) {
                            echo "['" . $e->type_name . "', " . $e->total . "],";
                        } ?>
                    ]
                }]
            });
        <?php } ?>
    });
</script>
<section class="content">
    <div class="row">
        <div class="col-md-12 text-center">
            <form id="filterForm" class="form-inline">
                <div class="form-group form-group-lg">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control input-sm">
                </div>
                <div class="form-group form-group-lg">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control input-sm">
                </div>
                <button type="submit" class="btn btn-primary btn-sm ml10">Filter</button>
            </form>
            <hr>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><?= lang('sales_value'); ?></span>
                            <span class="info-box-number today_sales_value"><?= $this->tec->formatMoney($total_sales->total_amount) ?></span>
                            <div class="progress">
                                <div style="width: 100%" class="progress-bar"></div>
                            </div>
                            <span class="progress-description">
                                <?= $total_sales->total . ' ' . lang('sales'); ?> |
                                <?= $this->tec->formatMoney($total_sales->paid) . ' ' . lang('received') ?> |
                                <?= $this->tec->formatMoney($total_sales->tax) . ' ' . lang('tax') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-yellow">
                        <span class="info-box-icon"><i class="fa fa-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><?= lang('purchases_value'); ?></span>
                            <span class="info-box-number"><?= $this->tec->formatMoney($total_purchases->total_amount) ?></span>
                            <div class="progress">
                                <div style="width: 0%" class="progress-bar"></div>
                            </div>
                            <span class="progress-description">
                                <?= $total_purchases->total ?> <?= lang('purchases'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-red">
                        <span class="info-box-icon"><i class="fa-solid fa-square-up-right"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><?= lang('expenses_value'); ?></span>
                            <span class="info-box-number"><?= $this->tec->formatMoney($total_expenses->total_amount) ?></span>
                            <div class="progress">
                                <div style="width: 0%" class="progress-bar"></div>
                            </div>
                            <span class="progress-description">
                                <?= $total_expenses->total ?> <?= lang('expenses'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text"><?= lang('profit_loss'); ?></span>
                            <span class="info-box-number"><?= $this->tec->formatMoney($total_sales->total_amount - $total_purchases->total_amount - $total_expenses->total_amount) ?></span>
                            <div class="progress">
                                <div style="width: 100%" class="progress-bar"></div>
                            </div>
                            <span class="progress-description">
                                <?= $total_sales->total_amount . ' - ' . ($total_purchases->total_amount ? $total_purchases->total_amount : 0) . ' - ' . ($total_expenses->total_amount ? $total_expenses->total_amount : 0); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title"><?= lang('sales_chart'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div id="chart" style="height:300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title"><?= lang('top_products') . ' (' . date('F Y') . ')'; ?></h3>
                        </div>
                        <div class="box-body">
                            <div id="chart2" style="height:300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title"><?= lang('customers_chart'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div id="topCustomersChart" style="height:300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Today's Expenses by Type</h3>
                        </div>
                        <div class="box-body">
                            <div id="todayExpensesChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>


            </div>



        </div>
    </div>
</section>