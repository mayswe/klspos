<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $public_methods = ['shared_profit_loss_pdf'];
        $current_method = strtolower($this->router->fetch_method());

        if (!in_array($current_method, $public_methods, true)) {
            if (!$this->loggedIn) {
                redirect('login');
            }

            if (!$this->Admin && !$this->Owner) {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('pos');
            }
        }

        $this->load->model('reports_model');
        $this->load->model('stock_model');
        $this->load->model('suppliers_model');
        $this->load->model('pos_model');
        
    }
    
    // 📊 Weekly Profit & Loss
    public function weekly_profit_loss() {
        $dates = $this->get_week_range();
        $data = $this->Report_model->get_profit_loss($dates['start'], $dates['end']);
        echo json_encode($data);
    }

    // 💰 Weekly Sales
    public function weekly_sales() {
        $dates = $this->get_week_range();
        $data = $this->Report_model->get_sales($dates['start'], $dates['end']);
        echo json_encode($data);
    }

    // 📦 Weekly Purchase
    public function weekly_purchase() {
        $dates = $this->get_week_range();
        $data = $this->Report_model->get_purchase($dates['start'], $dates['end']);
        echo json_encode($data);
    }

    // 💸 Weekly Expense
    public function weekly_expense() {
        $dates = $this->get_week_range();
        $data = $this->Report_model->get_expense($dates['start'], $dates['end']);
        echo json_encode($data);
    }

    // 📦 Stock Balance
    public function stock_balance() {
        $data = $this->Report_model->get_stock_balance();
        echo json_encode($data);
    }

    // 📅 MON → SAT
    private function get_week_range() {
        $monday = date('Y-m-d', strtotime('monday this week'));
        $saturday = date('Y-m-d', strtotime('saturday this week'));
        return ['start' => $monday, 'end' => $saturday];
    }

    public function alerts()
    {
        $data['error']            = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('stock_alert');
        $bc                       = [['link' => '#', 'page' => lang('stock_alert')]];
        $meta                     = ['page_title' => lang('stock_alert'), 'bc' => $bc];
        $this->page_construct('reports/alerts', $this->data, $meta);
    }

    public function daily_sales($year = null, $month = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->lang->load('calendar');
        $config = [
            'show_next_prev' => true,
            'next_prev_url'  => site_url('reports/daily_sales'),
            'month_type'     => 'long',
            'day_type'       => 'long'
        ];
        $config['template'] = '

        {table_open}<table border="0" cellpadding="0" cellspacing="0" class="table table-bordered table-calendar" style="min-width:522px;">{/table_open}

        {heading_row_start}<tr class="active">{/heading_row_start}

        {heading_previous_cell}<th><div class="text-center"><a href="{previous_url}">&lt;&lt;</div></a></th>{/heading_previous_cell}
        {heading_title_cell}<th colspan="{colspan}"><div class="text-center">{heading}</div></th>{/heading_title_cell}
        {heading_next_cell}<th><div class="text-center"><a href="{next_url}">&gt;&gt;</a></div></th>{/heading_next_cell}

        {heading_row_end}</tr>{/heading_row_end}

        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_equal"><div class="cl_wday">{week_day}</div></td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}

        {cal_row_start}<tr>{/cal_row_start}
        {cal_cell_start}<td>{/cal_cell_start}

        {cal_cell_content}{day}<br>{content}{/cal_cell_content}
        {cal_cell_content_today}<div class="highlight">{day}</div>{content}{/cal_cell_content_today}

        {cal_cell_no_content}{day}{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="highlight">{day}</div>{/cal_cell_no_content_today}

        {cal_cell_blank}&nbsp;{/cal_cell_blank}

        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}

        {table_close}</table>{/table_close}
        ';

        $this->load->library('calendar', $config);

        $sales = $this->reports_model->getDailySales($year, $month);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $sale->date              = intval($sale->date);
                $daily_sale[$sale->date] = "<table class='table table-condensed table-striped' style='margin-bottom:0;'><tr><td>" . lang('total') .
                "</td><td style='text-align:right;'>{$this->tec->formatMoney($sale->total)}</td></tr><tr><td><span style='font-weight:normal;'>" . lang('product_tax') . '<br>' . lang('order_tax') . '</span><br>' . lang('tax') .
                "</td><td style='text-align:right;'><span style='font-weight:normal;'>{$this->tec->formatMoney($sale->product_tax)}<br>{$this->tec->formatMoney($sale->order_tax)}</span><br>{$this->tec->formatMoney($sale->total_tax)}</td></tr><tr><td class='violet'>" . lang('discount') .
                "</td><td style='text-align:right;'>{$this->tec->formatMoney($sale->discount)}</td></tr><tr><td class='violet'>" . lang('grand_total') .
                "</td><td style='text-align:right;' class='violet'>{$this->tec->formatMoney($sale->grand_total)}</td></tr><tr><td class='green'>" . lang('paid') .
                "</td><td style='text-align:right;' class='green'>{$this->tec->formatMoney($sale->paid)}</td></tr><tr><td class='orange'>" . lang('balance') .
                "</td><td style='text-align:right;' class='orange'>{$this->tec->formatMoney(($sale->grand_total + $sale->rounding) - $sale->paid)}</td></tr></table>";
            }
        } else {
            $daily_sale = [];
        }

        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);

        $start                         = $year . '-' . $month . '-01 00:00:00';
        $end                           = $year . '-' . $month . '-' . days_in_month($month, $year) . ' 23:59:59';
        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales']     = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses']  = $this->reports_model->getTotalExpenses($start, $end);

        $this->data['page_title'] = $this->lang->line('daily_sales');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('daily_sales')]];
        $meta                     = ['page_title' => lang('daily_sales'), 'bc' => $bc];
        $this->page_construct('reports/daily', $this->data, $meta);
    }

    public function get_alerts()
{
    $store_id = $this->session->userdata('store_id');
    if (!$store_id) {
        return;
    }

    $this->load->library('datatables');

    $this->datatables->select("
        products.id as id,
        products.image as image,
        products.code as code,
        products.name as pname,
        products.type as type,
        categories.name as cname,
        COALESCE(sb_tot.total_qty, 0) as quantity,
        products.alert_quantity,
        products.tax,
        products.tax_method,
        products.cost,
        products.price
    ", false)
    ->from('products')
    ->join('categories', 'categories.id = products.category_id', 'left')
    ->join("(SELECT product_id, SUM(qty_base) as total_qty 
             FROM tec_stock_batches 
             WHERE store_id = {$store_id} 
             GROUP BY product_id) sb_tot", 'products.id = sb_tot.product_id', 'left')
    ->where('tec_products.alert_quantity >', 0)
    ->where("COALESCE(sb_tot.total_qty, 0) < tec_products.alert_quantity", null, false); // raw SQL works here

    $this->datatables->add_column('Actions', "<div class='text-center'><a href='#' class='btn btn-xs btn-primary ap tip' data-id='$1' title='" . lang('add_to_purcahse_order') . "'><i class='fa fa-plus'></i></a></div>", 'id');

    echo $this->datatables->generate();
}



    public function get_payments()
    {
        $user       = $this->input->get('user') ? $this->input->get('user') : null;
        $ref        = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : null;
        $sale_id    = $this->input->get('sale_no') ? $this->input->get('sale_no') : null;
        $customer   = $this->input->get('customer') ? $this->input->get('customer') : null;
        $paid_by    = $this->input->get('paid_by') ? $this->input->get('paid_by') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date   = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $this->load->library('datatables');
        $this->datatables
        ->select("{$this->db->dbprefix('payments')}.id as id, {$this->db->dbprefix('payments')}.date, {$this->db->dbprefix('payments')}.reference as ref, {$this->db->dbprefix('sales')}.id as sale_no, paid_by, amount")
        ->from('payments')
        ->join('sales', 'payments.sale_id=sales.id', 'left')
        ->group_by('payments.id');

        if ($this->session->userdata('store_id')) {
            $this->datatables->where('payments.store_id', $this->session->userdata('store_id'));
        }
        if ($user) {
            $this->datatables->where('payments.created_by', $user);
        }
        if ($ref) {
            $this->datatables->where('payments.reference', $ref);
        }
        if ($paid_by) {
            $this->datatables->where('payments.paid_by', $paid_by);
        }
        if ($sale_id) {
            $this->datatables->where('sales.id', $sale_id);
        }
        if ($customer) {
            $this->datatables->where('sales.customer_id', $customer);
        }
        if ($start_date) {
            $this->datatables->where("{$this->db->dbprefix('payments')}.date  >=", $start_date)
                ->where("{$this->db->dbprefix('payments')}.date <=", $end_date);
        }

        echo $this->datatables->generate();
    }

    public function get_products()
    {
        $product    = $this->input->get('product') ? $this->input->get('product') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date   = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        //COALESCE(sum(".$this->db->dbprefix('sale_items').".quantity)*".$this->db->dbprefix('products').".cost, 0) as cost,
        $this->load->library('datatables');
        $this->datatables
        ->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.name, ' . $this->db->dbprefix('products') . '.code, 
        COALESCE(sum(' . $this->db->dbprefix('sale_items') . '.quantity), 0) as sold, 
        (CASE WHEN psq.quantity IS NULL THEN 0 ELSE psq.quantity END) as quantity, 
        ROUND(COALESCE(((sum(' . $this->db->dbprefix('sale_items') . '.subtotal)*' . $this->db->dbprefix('products') . '.tax)/100), 0), 2) as tax, COALESCE(sum(' . $this->db->dbprefix('sale_items') . '.quantity)*' . $this->db->dbprefix('sale_items') . '.cost, 0) as cost, COALESCE(sum(' . $this->db->dbprefix('sale_items') . '.subtotal), 0) as income, ROUND((COALESCE(sum(' . $this->db->dbprefix('sale_items') . '.subtotal), 0)) - COALESCE(sum(' . $this->db->dbprefix('sale_items') . '.quantity)*' . $this->db->dbprefix('sale_items') . '.cost, 0) -COALESCE(((sum(' . $this->db->dbprefix('sale_items') . '.subtotal)*' . $this->db->dbprefix('products') . '.tax)/100), 0), 2)
            as profit', false)
        ->from('sale_items')
        ->join('products', 'sale_items.product_id=products.id', 'left')
        ->join("( SELECT * from {$this->db->dbprefix('product_store_qty')} WHERE store_id = {$this->session->userdata('store_id')}) psq", 
        'products.id=psq.product_id', 'left')
        ->join('sales', 'sale_items.sale_id=sales.id', 'left');
        if ($this->session->userdata('store_id')) {
            $this->datatables->where('sales.store_id', $this->session->userdata('store_id'));
        }
        $this->datatables->group_by('products.id');

        if ($product) {
            $this->datatables->where('products.id', $product);
        }
        if ($start_date) {
            $this->datatables->where('date >=', $start_date);
        }
        if ($end_date) {
            $this->datatables->where('date <=', $end_date);
        }
        echo $this->datatables->generate();
    }
    
    
   public function get_product_trace()
{
    $product_id    = $this->input->get('product') ? $this->input->get('product') : null;
    $start_date    = $this->input->get('start_date') ? $this->input->get('start_date') : null;
    $end_date      = $this->input->get('end_date') ? $this->input->get('end_date') : null;
    $movement_type = $this->input->get('movement_type') ?? null; // use GET to be consistent

    $movement_filter_sm   = "";
    $movement_filter_cogs = "";

    if ($movement_type) {
        // Apply filter to stock_movements
        $movement_filter_sm = " AND sm.movement_type = " . $this->db->escape($movement_type);

        // Apply filter to COGS logs only if movement_type = 'sale'
        if ($movement_type === 'sale') {
            $movement_filter_cogs = " AND 'sale' = " . $this->db->escape($movement_type);
        } else {
            // if movement_type != 'sale', exclude COGS completely
            $movement_filter_cogs = " AND 1=0";
        }
    }

    $sql = "
        (
            SELECT 
                sm.id,
                sm.movement_type,
                sm.qty_base,
                sm.qty_secondary,
                sm.qty_primary,
                sm.created_at as date,
                sfrom.name as from_store,
                sto.name as to_store,
                NULL as sale_id,
                NULL as sale_ref,
                NULL as cogs_cost,
                NULL as cogs_total_cost
            FROM tec_stock_movements sm
            LEFT JOIN tec_stores sfrom ON sfrom.id = sm.from_store_id
            LEFT JOIN tec_stores sto ON sto.id = sm.to_store_id
            WHERE sm.qty_base > 0 and sm.product_id = {$this->db->escape_str($product_id)} {$movement_filter_sm}
        )
        UNION ALL
        (
            SELECT
                cogs.id,
                'sale' as movement_type,
                cogs.quantity as qty_base,
                NULL as qty_secondary,
                NULL as qty_primary,
                cogs.created_at as date,
                NULL as from_store,
                NULL as to_store,
                cogs.sale_id,
                cogs.sale_item_id as sale_ref,
                cogs.cost_price as cogs_cost,
                cogs.total_cost as cogs_total_cost
            FROM tec_cogs_logs cogs
            WHERE cogs.product_id = {$this->db->escape_str($product_id)} {$movement_filter_cogs}
        )
        ORDER BY date DESC
    ";
    
    $query = $this->db->query($sql);
    $data  = $query->result_array();

    $output = [
        "draw" => intval($this->input->get("draw")),
        "recordsTotal" => count($data),
        "recordsFiltered" => count($data),
        "data" => $data
    ];

    echo json_encode($output);
}


    public function get_register_logs()
{
    $user       = $this->input->get('user') ?: null;
    $start_date = $this->input->get('start_date') ?: null;
    $end_date   = $this->input->get('end_date') ?: null;

    $this->load->library('datatables');

    if ($this->db->dbdriver == 'sqlite3') {
        $this->datatables->select("
            {$this->db->dbprefix('registers')}.id as id,
            {$this->db->dbprefix('registers')}.date,
            {$this->db->dbprefix('registers')}.closed_at,
            ({$this->db->dbprefix('users')}.first_name || ' ' || {$this->db->dbprefix('users')}.last_name || '<br>' || {$this->db->dbprefix('users')}.email) as user,
            {$this->db->dbprefix('registers')}.cash_in_hand,
            (total_cc_slips || ' (' || total_cc_slips_submitted || ')') as cc_slips,
            (total_cheques || ' (' || total_cheques_submitted || ')') as total_cheques,
            (total_cash || ' (' || total_cash_submitted || ')') as total_cash,
            total_cash_submitted,
            {$this->db->dbprefix('registers')}.note
        ", false);
    } else {
        $this->datatables->select("
            {$this->db->dbprefix('registers')}.id as id,
            {$this->db->dbprefix('registers')}.date,
            {$this->db->dbprefix('registers')}.closed_at,
            CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name, '<br>', {$this->db->dbprefix('users')}.email) as user,
            {$this->db->dbprefix('registers')}.cash_in_hand,
            CONCAT(total_cc_slips, ' (', total_cc_slips_submitted, ')') as cc_slips,
            CONCAT(total_cheques, ' (', total_cheques_submitted, ')') as total_cheques,
            CONCAT(total_cash, ' (', total_cash_submitted, ')') as total_cash,
            total_cash_submitted,
            {$this->db->dbprefix('registers')}.note
        ", false);
    }

    $this->datatables->from('registers')
        ->join('users', 'users.id = registers.user_id', 'left');

    if ($user) {
        $this->datatables->where('registers.user_id', $user);
    }

    if ($start_date && $end_date) {
        $this->datatables->where('DATE(registers.date) >=', $start_date);
        $this->datatables->where('DATE(registers.date) <=', $end_date);
    }

    if ($this->session->userdata('store_id')) {
        $this->datatables->where('registers.store_id', $this->session->userdata('store_id'));
    }

    // ❌ Removed order_by() – not supported in Datatables library
    // Sorting will be handled by DataTables JS on the frontend

    echo $this->datatables->generate();
}


    public function get_sales()
{
    $customer   = $this->input->get('customer') ? $this->input->get('customer') : null;
    $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
    $end_date   = $this->input->get('end_date') ? $this->input->get('end_date') : null;
    $user       = $this->input->get('user') ? $this->input->get('user') : null;
    $status       = $this->input->get('status') ? $this->input->get('status') : null;

    $this->load->library('datatables');
    $this->datatables
        ->select('id, date, customer_name, total, total_tax, total_discount, grand_total, paid, (grand_total - paid) as balance, status')
        ->from('sales');

    if ($this->session->userdata('store_id')) {
        $this->datatables->where('store_id', $this->session->userdata('store_id'));
    }
    if ($customer) {
        $this->datatables->where('customer_id', $customer);
    }
    if ($user) {
        $this->datatables->where('created_by', $user);
    }
    if ($status) {
        $this->datatables->where('status', $status);
    }
    if ($start_date) {
        $this->datatables->where('date >=', $start_date);
    }
    if ($end_date) {
        $this->datatables->where('date <=', $end_date);
    }

    // Add action link with the ID
    
    
    $this->datatables->add_column('actions',
    "<div class='text-center'>
        <div class='btn-group'>
            <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
            </button>
            <ul class='dropdown-menu dropdown-menu-right'>
                <li><a href='" . site_url('pos/view/$1/1') . "' class='tip' title='" . lang('view_invoice') . "' data-toggle='ajax-modal'><i class='fa fa-list'></i> " . lang('view_invoice') . "</a></li>
                
                <li><a href='" . site_url('sales/payments/$1') . "' class='tip' title='" . lang('view_payments') . "' data-toggle='ajax'><i class='fa-solid fa-money-bill'></i> " . lang('view_payments') . "</a></li>
                <li><a href='" . site_url('sales/add_payment/$1') . "' class='tip' title='" . lang('add_payment') . "' data-toggle='ajax'><i class='fa fa-briefcase'></i> " . lang('add_payment') . "</a></li>
               
                
            </ul>
        </div>
    </div>",
    'id');

    // Optional: if you don't want to show raw ID column
    $this->datatables->unset_column('id');

    echo $this->datatables->generate();
}

    
    public function get_purchase()
{
    $supplier    = $this->input->get('supplier') ? $this->input->get('supplier') : null;
    $start_date    = $this->input->get('start_date') ? $this->input->get('start_date') : null;
    $end_date    = $this->input->get('end_date') ? $this->input->get('end_date') : null;
    $status    = $this->input->get('status') ? $this->input->get('status') : null;

    $this->load->library('datatables');

    $this->datatables
        ->select('purchases.id, purchases.date, suppliers.name, purchases.total, (tec_purchases.total - tec_purchases.paid) as balance, purchases.status, purchases.paid, purchases.received')
        ->from('purchases')
        ->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left');

    if ($supplier) {
        $this->datatables->where('tec_purchases.supplier_id', $supplier);
    }
    if ($user) {
        $this->datatables->where('tec_purchases.created_by', $user);
    }
    if ($status) {
        $this->datatables->where('tec_purchases.status', $status);
    }
    if ($start_date) {
        $this->datatables->where('tec_purchases.date >=', $start_date);
    }
    if ($end_date) {
        $this->datatables->where('tec_purchases.date <=', $end_date);
    }
    
    $this->datatables->add_column(
        'Actions',
        "<div class='text-center'>
            <div class='btn-group'>
                <button class='btn btn-primary dropdown-toggle' data-toggle='dropdown'>
                    <i class='fa fa-cog'></i> " . lang('actions') . "
                </button>
                <ul class='dropdown-menu dropdown-menu-right'>
                    <li>
                        <a href='" . site_url('purchases/view/$1') . "' data-toggle='ajax-modal'>
                            <i class='fa fa-eye'></i> " . lang('view_purchase') . "
                        </a>
                    </li>
                    <li>
                        <a href='" . site_url('purchases/payments/$1') . "' data-toggle='ajax'>
                            <i class='fa fa-money'></i> " . lang('view_payments') . "
                        </a>
                    </li>
                    <li>
                        <a href='" . site_url('purchases/add_payment/$1') . "' data-toggle='ajax'>
                            <i class='fa fa-briefcase'></i> " . lang('add_payment') . "
                        </a>
                    </li>
                    <li>
                        <a href='#' class='receive-link' data-id='$1'>
                            <i class='fa fa-check'></i> " . lang('receive') . "
                        </a>
                    </li>
                    <li>
                        <a href='" . site_url('purchases/delete/$1') . "' class='text-danger'
                           onclick=\"return confirm('" . lang('alert_x_purchase') . "')\">
                            <i class='fa fa-trash'></i> " . lang('delete') . "
                        </a>
                    </li>
                </ul>
            </div>
        </div>",
        'id'
    );

    echo $this->datatables->generate();
}


    public function index()
    {
        if ($this->input->post('customer')) {
            $start_date                = $this->input->post('start_date') ? $this->input->post('start_date') : null;
            $end_date                  = $this->input->post('end_date') ? $this->input->post('end_date') : null;
            $user                      = $this->input->post('user') ? $this->input->post('user') : null;
            $status                      = $this->input->post('status') ? $this->input->post('status') : null;
            $this->data['total_sales'] = $this->reports_model->getTotalCustomerSales($this->input->post('customer'), $user, $start_date, $end_date,$status);
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['customers']  = $this->reports_model->getAllCustomers();
        $this->data['users']      = $this->reports_model->getAllStaff();
        $this->data['page_title'] = $this->lang->line('sales_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('sales_report')]];
        $meta                     = ['page_title' => lang('sales_report'), 'bc' => $bc];
        $this->page_construct('reports/sales', $this->data, $meta);
    }
    
    public function sales()
    {
        if ($this->input->post('customer')) {
            $start_date                = $this->input->post('start_date') ? $this->input->post('start_date') : null;
            $end_date                  = $this->input->post('end_date') ? $this->input->post('end_date') : null;
            $user                      = $this->input->post('user') ? $this->input->post('user') : null;
            $status                      = $this->input->post('status') ? $this->input->post('status') : null;
            $this->data['total_sales'] = $this->reports_model->getTotalCustomerSales($this->input->post('customer'), $user, $start_date, $end_date,$status);
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['customers']  = $this->reports_model->getAllCustomers();
        $this->data['users']      = $this->reports_model->getAllStaff();
        $this->data['page_title'] = $this->lang->line('sales_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('sales_report')]];
        $meta                     = ['page_title' => lang('sales_report'), 'bc' => $bc];
        $this->page_construct('reports/sales', $this->data, $meta);
    }
    
    public function purchases()
    {
        if ($this->input->post('customer')) {
            $start_date                = $this->input->post('start_date') ? $this->input->post('start_date') : null;
            $end_date                  = $this->input->post('end_date') ? $this->input->post('end_date') : null;
            $user                      = $this->input->post('user') ? $this->input->post('user') : null;
            $status                      = $this->input->post('status') ? $this->input->post('status') : null;
            $this->data['total_sales'] = $this->reports_model->getTotalCustomerSales($this->input->post('customer'), $user, $start_date, $end_date,$status);
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['suppliers']  = $this->reports_model->getAllSuppliers();
        $this->data['users']      = $this->reports_model->getAllStaff();
        $this->data['page_title'] = $this->lang->line('purchase_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('purchase_report')]];
        $meta                     = ['page_title' => lang('purchase_report'), 'bc' => $bc];
        $this->page_construct('reports/purchases', $this->data, $meta);
    }
    
    public function purchasesupplier()
    {
        if ($this->input->post('supplier')) {
            $start_date                = $this->input->post('start_date') ? $this->input->post('start_date') : null;
            $end_date                  = $this->input->post('end_date') ? $this->input->post('end_date') : null;
            $user                      = $this->input->post('user') ? $this->input->post('user') : null;
            $this->data['total_sales'] = $this->reports_model->getTotalSupplierPurchase($this->input->post('supplier'), $user, $start_date, $end_date);
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['suppliers']  = $this->reports_model->getAllSuppliers();
        $this->data['users']      = $this->reports_model->getAllStaff();
        $this->data['page_title'] = $this->lang->line('purchase_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('purchase_report')]];
        $meta                     = ['page_title' => lang('purchase_report'), 'bc' => $bc];
        $this->page_construct('reports/purchasesupplier', $this->data, $meta);
    }

    public function monthly_sales($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        $this->load->language('calendar');
        $this->lang->load('calendar');
        $this->data['error']           = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $start                         = $year . '-01-01 00:00:00';
        $end                           = $year . '-12-31 23:59:59';
        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales']     = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses']  = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['year']            = $year;
        $this->data['sales']           = $this->reports_model->getMonthlySales($year);
        $this->data['page_title']      = $this->lang->line('monthly_sales');
        $bc                            = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('monthly_sales')]];
        $meta                          = ['page_title' => lang('monthly_sales'), 'bc' => $bc];
        $this->page_construct('reports/monthly', $this->data, $meta);
    }

    public function payments()
    {
        if ($this->input->post('customer')) {
            $start_date                = $this->input->post('start_date') ? $this->input->post('start_date') : null;
            $end_date                  = $this->input->post('end_date') ? $this->input->post('end_date') : null;
            $user                      = $this->input->post('user') ? $this->input->post('user') : null;
            $this->data['total_sales'] = $this->reports_model->getTotalCustomerSales($this->input->post('customer'), $user, $start_date, $end_date);
        }
        $this->data['error']     = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users']     = $this->reports_model->getAllStaff();
        $this->data['customers'] = $this->reports_model->getAllCustomers();
        $bc                      = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('payments_report')]];
        $meta                    = ['page_title' => lang('payments_report'), 'bc' => $bc];
        $this->page_construct('reports/payments', $this->data, $meta);
    }

    public function products()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['products']   = $this->reports_model->getAllProducts();
        $this->data['page_title'] = $this->lang->line('products_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('products_report')]];
        $meta                     = ['page_title' => lang('products_report'), 'bc' => $bc];
        $this->page_construct('reports/products', $this->data, $meta);
    }

    

    public function expenses()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories']   = $this->site->getAllExpenseCategories();
        $this->data['page_title'] = $this->lang->line('expense_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('expense_report')]];
        $meta                     = ['page_title' => lang('expense_report'), 'bc' => $bc];
        $this->page_construct('reports/expenses', $this->data, $meta);
    }

    public function profit($income, $cost, $tax)
    {
        return floatval($income) . ' - ' . floatval($cost) . ' - ' . floatval($tax);
    }

    public function registers()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getAllStaff();
        $bc                  = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('registers_report')]];
        $meta                = ['page_title' => lang('registers_report'), 'bc' => $bc];
        $this->page_construct('reports/registers', $this->data, $meta);
    }

    public function top_products()
    {
        $this->data['topProducts']   = $this->reports_model->topProducts();
        $this->data['topProducts1']  = $this->reports_model->topProducts1();
        $this->data['topProducts3']  = $this->reports_model->topProducts3();
        $this->data['topProducts12'] = $this->reports_model->topProducts12();
        $this->data['page_title']    = $this->lang->line('top_products');
        $bc                          = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('top_products')]];
        $meta                        = ['page_title' => lang('top_products'), 'bc' => $bc];
        $this->page_construct('reports/top', $this->data, $meta);
    }

    public function investment() {
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['products'] = $this->reports_model->getAllProducts();
        $this->data['page_title'] = $this->lang->line("investment_report");
        $this->data['page_title'] = $this->lang->line("investment_report");
        $bc = array(array('link' => '#', 'page' => lang('reports')), array('link' => '#', 'page' => lang('investment_report')));
        $meta = array('page_title' => lang('investment_report'), 'bc' => $bc);
        $this->page_construct('reports/investment', $this->data, $meta);
    }
    
    public function get_investment() {
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        //COALESCE(sum(".$this->db->dbprefix('sale_items').".quantity)*".$this->db->dbprefix('products').".cost, 0) as cost,
        $this->load->library('datatables');
        $this->datatables
        ->select("tec_products.id, tec_categories.name as cname, tec_products.name as pname, tec_products.cost, tec_product_store_qty.quantity,
            IF(tec_products.unit = 0, (tec_products.cost*tec_product_store_qty.quantity), ((tec_products.cost*tec_products.unit)*tec_product_store_qty.quantity)) as investment", FALSE)
        ->from('tec_products')
        ->join('tec_product_store_qty', 'tec_products.id = tec_product_store_qty.product_id', 'left')
        ->join('tec_categories', 'tec_products.category_id = tec_categories.id', 'left');
        if ($this->session->userdata('store_id')) {
            $this->datatables->where('tec_product_store_qty.store_id', $this->session->userdata('store_id'));
        }
        $this->datatables->group_by('products.id');

        if($product) { $this->datatables->where('products.id', $product); }
        if($start_date) { $this->datatables->where('date >=', $start_date); }
        if($end_date) { $this->datatables->where('date <=', $end_date); }
        echo $this->datatables->generate();
    }

    public function customers()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['customers']   = $this->reports_model->getAllCustomers();
        $this->data['page_title'] = $this->lang->line('customers_report');
        $this->data['page_title'] = $this->lang->line('customers_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('customers_report')]];
        $meta                     = ['page_title' => lang('customers_report'), 'bc' => $bc];
        $this->page_construct('reports/customers', $this->data, $meta);
    }

    public function get_customers()
    {
        $start_date = $this->input->get('start_date');
        $end_date   = $this->input->get('end_date');

        $this->load->library('datatables');

        $this->datatables
            ->select(
                'customers.id as id, 
                customers.name, 
                customers.phone, 
                customers.email, 
                COUNT(tec_sales.id) as total_orders, 
                COALESCE(SUM(tec_sales.grand_total), 0) as total_spent, 
                MAX(tec_sales.date) as last_purchase'
            )
            ->from('customers')
            ->join('tec_sales', 'tec_sales.customer_id = customers.id', 'left')
            ->group_by('customers.id');

        // Filter by store if applicable
        if ($this->session->userdata('store_id')) {
            $this->datatables->where('sales.store_id', $this->session->userdata('store_id'));
        }

        // Optional date filtering
        if ($start_date) {
            $this->datatables->where('sales.date >=', $start_date);
        }
        if ($end_date) {
            $this->datatables->where('sales.date <=', $end_date);
        }

        echo $this->datatables->generate();
    }

    
    public function get_expenses()
    {
        $category    = $this->input->get('category') ? $this->input->get('category') : null;
        $start_date  = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date    = $this->input->get('end_date') ? $this->input->get('end_date') : null;
    
        $this->load->library('datatables');
    
        $this->datatables
            ->select(
                'expenses.id as id, ' .
                'expenses.date as date, ' .
                'expenses.reference as reference, ' .
                'expensetype.name as category, ' .
                'expenses.amount as amount, ' .
                'expenses.quantity as quantity, ' .
                'expenses.total as total'
            )
            ->from('expenses')
            ->join('expensetype', 'expensetype.id = expenses.type_id', 'left');
    
        if ($this->session->userdata('store_id')) {
            $this->datatables->where('expenses.store_id', $this->session->userdata('store_id'));
        }
       
        if ($category) {
            $this->datatables->where('expenses.type_id', $category);
        }
        if ($start_date) {
            $this->datatables->where('expenses.date >=', $start_date);
        }
        if ($end_date) {
            $this->datatables->where('expenses.date <=', $end_date);
        }
        
        echo $this->datatables->generate();
    }


public function purchase()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['products']   = $this->reports_model->getAllProducts();
        $this->data['page_title'] = $this->lang->line('purchase_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('purchase_report')]];
        $meta                     = ['page_title' => lang('purchase_report'), 'bc' => $bc];
        $this->page_construct('reports/purchase', $this->data, $meta);
    }

public function get_purchase_report()
{
    $this->load->library('datatables');
    $this->datatables
        ->select("p.date as purchase_date, pr.name as product_name, pi.net_unit_cost as unit_price, pi.quantity as quantity_purchased, (pi.net_unit_cost * pi.quantity) as total_price")
        ->from("purchase_items pi")
        ->join("purchases p", "p.id = pi.purchase_id", "left")
        ->join("products pr", "pr.id = pi.product_id", "left");
    echo $this->datatables->generate();
}

public function warehouse_stock()
    {
        
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['products']   = $this->reports_model->getAllProducts();
        $this->data['warehouses'] = $this->site->getAllWarehouses(); // optional dropdown
        $this->data['page_title'] = $this->lang->line('warehouse_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('warehouse_report')]];
        $meta                     = ['page_title' => lang('warehouse_report'), 'bc' => $bc];
        $this->page_construct('reports/warehouse_stock', $this->data, $meta);
    }



public function get_warehouse_stock()
{
    $warehouse = $this->input->get('warehouse') ?? null;

    $this->load->library('datatables');

    $this->datatables
        ->select(
            'MIN(tec_product_batches.id) as id, ' .
            'tec_products.code as product_code, ' .
            'tec_products.name as product_name, ' .
            'tec_warehouses.name as warehouse, ' .
            'SUM(tec_product_batches.qty) as quantity, ' .
            'tec_product_units.name as unit, ' .
            'MAX(tec_product_batches.purchase_date) as last_purchase_date'
        )
        ->from('product_batches')
        ->join('products', 'products.id = product_batches.product_id', 'left')
        ->join('warehouses', 'warehouses.id = product_batches.warehouse_id', 'left')
        ->join('product_units', 'product_units.id = product_batches.unit_id', 'left');

    if ($warehouse) {
        $this->datatables->where('product_batches.warehouse_id', $warehouse);
    }else{
        $this->datatables->where('tec_product_batches.warehouse_id IS NOT NULL', null, false);
    }

    $this->datatables->group_by(['product_batches.product_id', 'product_batches.warehouse_id']);

    echo $this->datatables->generate();
}


    public function container_box()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['products']   = $this->reports_model->getAllProducts();
        $this->data['container_boxes']   = $this->site->getAllContainerBoxes();
        $this->data['page_title'] = $this->lang->line('container_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('container_report')]];
        $meta                     = ['page_title' => lang('container_report'), 'bc' => $bc];
        $this->page_construct('reports/container_box', $this->data, $meta);
    }

public function get_container_box()
{
    $container_box = $this->input->get('container_box') ?? null;

    $this->load->library('datatables');

    $this->datatables
        ->select(
            'MIN(tec_purchase_items.id) as id, ' .
            'tec_container_boxes.box_name as container_box, ' .
            'tec_products.code AS product_code, ' .
            'tec_products.name AS product_name, ' .
            'SUM(tec_purchase_items.quantity) as quantity, ' .
            'tec_product_units.name as unit, ' .
            'MAX(tec_purchases.date) AS last_purchase_date'
        )
        ->from('purchase_items')
        ->join('products', 'products.id = purchase_items.product_id', 'left')
        ->join('product_units', 'product_units.id = purchase_items.unit_id', 'left')
        ->join('purchases', 'purchases.id = purchase_items.purchase_id', 'left')
        ->join('container_boxes', 'container_boxes.id = purchases.container_box', 'left');

    if ($container_box) {
        $this->datatables->where('tec_purchases.container_box', $container_box);
    }else{
        $this->datatables->where('tec_purchases.container_box IS NOT NULL', null, false);
    }

    $this->datatables->group_by([
        'purchase_items.product_id',
        'tec_purchases.container_box'
    ]);

    echo $this->datatables->generate();
}



    public function product_summary()
    {
        $this->data['report'] = $this->stock_model->getProductSummaryReport();
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['products']   = $this->reports_model->getAllProducts();
        $this->data['page_title'] = $this->lang->line('product_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('product_report')]];
        $meta                     = ['page_title' => lang('product_report'), 'bc' => $bc];
        $this->page_construct('reports/product_summary', $this->data, $meta);
    }

public function get_product_summary()
{
    $this->load->library('datatables');

    $product_id = $this->input->get('product') ?? $this->input->post('product');

    $this->datatables
        ->select(
            'MIN(tec_product_batches.id) as id, ' .
            'tec_products.code AS product_code, ' .
            'tec_products.name AS product_name, ' .
            'SUM(tec_product_batches.qty) AS total_quantity, ' .
            'tec_product_units.name as unit'
        )
        ->from('product_batches')
        ->join('products', 'products.id = product_batches.product_id', 'left')
        ->join('product_units', 'product_units.id = product_batches.unit_id', 'left')
        ->group_by('product_batches.product_id');

    // ✅ Apply product filter if present
    if (!empty($product_id) && is_numeric($product_id)) {
        $this->datatables->where('product_batches.product_id', $product_id);
    }

    echo $this->datatables->generate();
}

public function supplieradvances()
    {
        
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['suppliers'] = $this->suppliers_model->get_all_suppliers();
        $this->data['page_title'] = $this->lang->line('supplier_advances');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('supplier_advances')]];
        $meta                     = ['page_title' => lang('supplier_advances'), 'bc' => $bc];
        $this->page_construct('reports/supplieradvances', $this->data, $meta);
    }



public function get_supplieradvances()
{
    $suppliers = $this->input->get('suppliers') ?? null;

    $this->load->library('datatables');

    $this->datatables
        ->select('
            suppliers.id,
            suppliers.name as supplier_name,
            IFNULL(sa.total_advance, 0) as total_advance,
            IFNULL(p.total_deducted, 0) as deducted_amount,
            (IFNULL(sa.total_advance, 0) - IFNULL(p.total_deducted, 0)) as remaining_balance
        ')
        ->from('suppliers')
        // Subquery for total advances
        ->join('(SELECT supplier_id, SUM(amount) as total_advance FROM tec_supplier_advances GROUP BY supplier_id) sa', 'sa.supplier_id = suppliers.id', 'left')
        // Subquery for total deductions
        ->join('(SELECT supplier_id, SUM(advance_deducted) as total_deducted FROM tec_purchases GROUP BY supplier_id) p', 'p.supplier_id = suppliers.id', 'left');

    if ($suppliers) {
        $this->datatables->where('suppliers.id', $suppliers);
    }

    echo $this->datatables->generate();
}

public function profitloss()
    {
        
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['suppliers'] = $this->suppliers_model->get_all_suppliers();
        $this->data['page_title'] = $this->lang->line('profit_loss');
        $this->data['container_boxes']   = $this->site->getAllContainerBoxes();
        $this->data['warehouses'] = $this->site->getAllWarehouses(); // optional dropdown
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('profitloss')]];
        $meta                     = ['page_title' => lang('profit_loss'), 'bc' => $bc];
        $this->page_construct('reports/profitloss', $this->data, $meta);
    }


public function get_profit_loss()
{
    $start_date     = $this->input->post('start_date');
    $end_date       = $this->input->post('end_date');
    $warehouse_id   = $this->input->post('warehouse');
    $container_box  = $this->input->post('container_box');
    $currency       = $this->input->post('currency');

    $currency_rate = 1;
    if ($currency === 'MYR') {
        $currency_rate = $this->reports_model->get_currency_rate('MYR');
    }

    $data = [
        'total_sales'   => round($this->reports_model->get_total_sales($start_date, $end_date, $warehouse_id, $container_box) / $currency_rate, 2),
        'discounts'     => round($this->reports_model->get_total_discounts($start_date, $end_date, $warehouse_id, $container_box) / $currency_rate, 2),
        'cogs'          => round($this->reports_model->get_total_cogs($start_date, $end_date, $warehouse_id, $container_box) / $currency_rate, 2),
        'expenses'      => round($this->reports_model->get_total_expenses($start_date, $end_date, $warehouse_id, $container_box) / $currency_rate, 2),
    ];

    $data['net_sales']    = $data['total_sales'] - $data['discounts'];
    $data['gross_profit'] = $data['net_sales'] - $data['cogs'];
    $data['net_profit']   = $data['gross_profit'] - $data['expenses'];

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

public function profit_report()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['profit_reports'] = $this->reports_model->getProfitReport();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['container_boxes']   = $this->site->getAllContainerBoxes();
        $this->data['page_title'] = $this->lang->line('profit_report');
        $this->data['page_title'] = $this->lang->line('profit_report');
        $bc                       = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('profit_report')]];
        $meta                     = ['page_title' => lang('profit_report'), 'bc' => $bc];
        $this->page_construct('reports/profit_report', $this->data, $meta);
    }



public function get_profit_ajax()
{
    $start_date = $this->input->get('start_date') ?: date('Y-m-01 00:00:00');
    $end_date   = $this->input->get('end_date')   ?: date('Y-m-t 23:59:59');

    $this->load->library('datatables');

    $this->datatables
        ->select('
            tec_sales.id as sale_id,
            DATE(tec_sales.date) as sale_date,
            tec_sales.grand_total as sale_total,
            IFNULL(c.cogs_total, 0) as cogs_total,
            (tec_sales.grand_total - IFNULL(c.cogs_total, 0)) as profit
        ')
        ->from('tec_sales')
        ->join('(SELECT sale_id, SUM(total_cost) as cogs_total 
                FROM tec_cogs_logs 
                GROUP BY sale_id) c', 'c.sale_id = tec_sales.id', 'left')
        ->where('tec_sales.date >=', $start_date)
        ->where('tec_sales.date <=', $end_date)
        ->group_by('tec_sales.id');

    echo $this->datatables->generate();
}



public function dailysales()
{
    // Get POST values or default to today
    $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : date('Y-m-d 00:00:00');
    $end_date   = $this->input->post('end_date') ? $this->input->post('end_date') : date('Y-m-d 23:59:59');
    $user       = $this->input->post('user') ? $this->input->post('user') : null;

    if ($this->input->post('customer')) {
        $this->data['total_sales'] = $this->reports_model->getTotalCustomerSales($this->input->post('customer'), $user, $start_date, $end_date);
    }

    // Prepare display date
    if ($start_date == $end_date) {
        $this->data['display_date'] = date('Y-m-d', strtotime($start_date));
    } else {
        $this->data['display_date'] = date('Y-m-d', strtotime($start_date)) . " to " . date('Y-m-d', strtotime($end_date));
    }

    $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['customers']  = $this->reports_model->getAllCustomers();
    $this->data['products']  = $this->reports_model->getAllProducts();
    $this->data['users']      = $this->reports_model->getAllStaff();
    $this->data['page_title'] = $this->lang->line('product_summary');

    $bc   = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('product_summary')]];
    $meta = ['page_title' => lang('product_summary'), 'bc' => $bc];
    $this->page_construct('reports/dailysales', $this->data, $meta);
}

public function dailyspurchases()
{
    // Get POST values or default to today
    $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : date('Y-m-d 00:00:00');
    $end_date   = $this->input->post('end_date') ? $this->input->post('end_date') : date('Y-m-d 23:59:59');
    $user       = $this->input->post('user') ? $this->input->post('user') : null;

    if ($this->input->post('customer')) {
        $this->data['total_sales'] = $this->reports_model->getTotalCustomerSales($this->input->post('customer'), $user, $start_date, $end_date);
    }

    // Prepare display date
    if ($start_date == $end_date) {
        $this->data['display_date'] = date('Y-m-d', strtotime($start_date));
    } else {
        $this->data['display_date'] = date('Y-m-d', strtotime($start_date)) . " to " . date('Y-m-d', strtotime($end_date));
    }

    $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['customers']  = $this->reports_model->getAllCustomers();
    $this->data['products']  = $this->reports_model->getAllProducts();
    $this->data['users']      = $this->reports_model->getAllStaff();
    $this->data['page_title'] = $this->lang->line('reports_dailyspurchases');

    $bc   = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('reports_dailyspurchases')]];
    $meta = ['page_title' => lang('reports_dailyspurchases'), 'bc' => $bc];
    $this->page_construct('reports/dailyspurchases', $this->data, $meta);
}


public function stocks()
{

    $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['customers']  = $this->reports_model->getAllCustomers();
    $this->data['products']  = $this->reports_model->getAllProducts();
    
    $this->data['users']      = $this->reports_model->getAllStaff();
    $this->data['page_title'] = $this->lang->line('stock_report');

    $bc   = [['link' => '#', 'page' => lang('reports')], ['link' => '#', 'page' => lang('stock_report')]];
    $meta = ['page_title' => lang('stock_report'), 'bc' => $bc];
    $this->page_construct('reports/stocks', $this->data, $meta);
}

public function get_product_sales($v = null)
{
    $product_id = $this->input->get('product') ?? $this->input->post('product');
    $start_date = $this->input->get_post('start_date');
    $end_date   = $this->input->get_post('end_date');

    $this->db->select('
        p.id as product_id,
        p.code as product_code,
        p.name as product_name,
        SUM(si.quantity) as total_qty,
        SUM(si.subtotal) as total_amount,
        MAX(s.date) as last_sale_date,
        (
            SELECT IFNULL(SUM(sb.qty_base), 0)
            FROM tec_stock_batches sb
            WHERE sb.product_id = p.id AND sb.store_id = 1
        ) as store1_balance1,   
        (
            SELECT IFNULL(SUM(sb.qty_secondary), 0)
            FROM tec_stock_batches sb
            WHERE sb.product_id = p.id AND sb.store_id = 1
        ) as store1_balance2, 
        (
            SELECT IFNULL(SUM(sb.qty_base), 0)
            FROM tec_stock_batches sb
            WHERE sb.product_id = p.id AND sb.store_id = 2
        ) as store2_balance1,    
        (
            SELECT IFNULL(SUM(sb.qty_secondary), 0)
            FROM tec_stock_batches sb
            WHERE sb.product_id = p.id AND sb.store_id = 2
        ) as store2_balance2,
    ');
    $this->db->from('tec_sale_items si');
    $this->db->join('tec_sales s', 's.id = si.sale_id', 'left');
    $this->db->join('tec_products p', 'p.id = si.product_id', 'left');
    $this->db->where('s.status !=', 'returned');

    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('s.date >=', $start_date);
        $this->db->where('s.date <=', $end_date);
    }
    
    if (!empty($product_id)) {
        $this->db->where('si.product_id =', $product_id);
    }

    $this->db->group_by('si.product_id');

    $q = $this->db->get();

    if ($q && $q->num_rows() > 0) {
        $data = [];
        $i = 1;
        foreach ($q->result() as $row) {
            $data[] = [
                "sr_no"         => $i++,
                "product_code"  => $row->product_code,
                "product_name"  => "<a target=_blank href='" . site_url('stocktransfers/trace/' . $row->product_id) . "' class='tip' title='" . lang('trace') . "'>" . $row->product_name . "</a>",
                "total_qty"     => $row->total_qty,
                "total_amount"  => $row->total_amount,
                "store1_balance1"=> $row->store1_balance1, 
                "store2_balance1"=> $row->store2_balance1, 
                "store1_balance2"=> $row->store1_balance2, 
                "store2_balance2"=> $row->store2_balance2, 
                "date"          => $row->last_sale_date,
            ];
        }
        echo json_encode(["data" => $data]);
    } else {
        echo json_encode(["data" => []]);
    }
}

public function get_product_purchase($v = null)
{
    $product_id = $this->input->get('product') ?? $this->input->post('product');
    $start_date = $this->input->get_post('start_date');
    $end_date   = $this->input->get_post('end_date');

    $this->db->select('
        p.id as product_id,
        p.code as product_code,
        p.name as product_name,

        SUM(IFNULL(si.primary_qty, 0)) as total_primary_qty,
        u.name as unit_name,
        SUM(si.subtotal) as total_amount,

        MAX(s.date) as last_purchase_date
    ');

    $this->db->from('tec_purchase_items si');

    $this->db->join('tec_purchases s', 's.id = si.purchase_id', 'left');

    $this->db->join('tec_products p', 'p.id = si.product_id', 'left');
    
    $this->db->join('tec_product_units u', 'u.id = si.primary_unit', 'left');

    $this->db->where('s.status !=', 'returned');

    if (!empty($start_date) && !empty($end_date)) {
        $this->db->where('DATE(s.date) >=', $start_date);
        $this->db->where('DATE(s.date) <=', $end_date);
    }

    if (!empty($product_id)) {
        $this->db->where('si.product_id', $product_id);
    }

    $this->db->group_by('si.product_id');

    $q = $this->db->get();

    if ($q && $q->num_rows() > 0) {

        $data = [];
        $i = 1;

        foreach ($q->result() as $row) {
            
            $data[] = [
                "sr_no"               => $i++,

                "product_code"        => $row->product_code,

                "product_name"        => "<a target='_blank' href='" .
                    site_url('products/view/' . $row->product_id) .
                    "' class='tip' title='" . lang('trace') . "'>" .
                    $row->product_name .
                    "</a>",

                "primary_qty" => $row->total_primary_qty . ' ' . $row->unit_name,

                "total_amount"        => $row->total_amount,

                "date"                => $row->last_purchase_date,
            ];
        }

        echo json_encode(["data" => $data]);

    } else {

        echo json_encode(["data" => []]);
    }
}

public function get_product_stock($v = null)
{
    
    $product_id = $this->input->get('product') ?? $this->input->post('product');

    $this->db->select('
        p.id as product_id,
        p.code as product_code,
        p.name as product_name,
    
        SUM(CASE WHEN sb.store_id = 1 THEN sb.qty_base ELSE 0 END) as store1_balance1,
        SUM(CASE WHEN sb.store_id = 1 THEN sb.qty_secondary ELSE 0 END) as store1_balance2,
    
        SUM(CASE WHEN sb.store_id = 2 THEN sb.qty_base ELSE 0 END) as store2_balance1,
        SUM(CASE WHEN sb.store_id = 2 THEN sb.qty_secondary ELSE 0 END) as store2_balance2
    ');
    
    $this->db->from('tec_products p');
    $this->db->join('tec_stock_batches sb', 'sb.product_id = p.id', 'left');
    $this->db->group_by('p.id');
    
    if (!empty($product_id)) {
        $this->db->where('sb.product_id =', $product_id);
    }

    $q = $this->db->get();

    if ($q && $q->num_rows() > 0) {
        $data = [];
        $i = 1;
        foreach ($q->result() as $row) {
            $data[] = [
                "sr_no"         => $i++,
                "product_code"  => $row->product_code,
                "product_name"  => "<a target=_blank href='" . site_url('stocktransfers/trace/' . $row->product_id) . "' class='tip' title='" . lang('trace') . "'>" . $row->product_name . "</a>",
                "store1_balance1" => (float)$row->store1_balance1,
                "store1_balance2" => (float)$row->store1_balance2,
                "store2_balance1" => (float)$row->store2_balance1,
                "store2_balance2" => (float)$row->store2_balance2,
            ];
        }
        echo json_encode(["data" => $data]);
    } else {
        echo json_encode(["data" => []]);
    }
}

public function profit_loss()
{
    // ✅ Define local variables first
    $start_date = $this->input->post('start_date') ?? date('Y-m-01 00:00:00');
    $end_date   = $this->input->post('end_date') ?? date('Y-m-t 23:59:59');

    // ✅ Pass them to model
    $data = $this->reports_model->get_profit_loss($start_date, $end_date);

    // ✅ Store in $this->data for the view
    $this->data['start_date'] = $start_date;
    $this->data['end_date']   = $end_date;

    $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['customers']  = $this->reports_model->getAllCustomers();
    $this->data['users']      = $this->reports_model->getAllStaff();
    $this->data['profit_loss_data'] = $data; // optional to pass profit/loss result to view

    $bc   = [['link' => '#', 'page' => lang('profit_loss_report')], ['link' => '#', 'page' => lang('profit_loss_report')]];
    $meta = ['page_title' => lang('profit_loss_report'), 'bc' => $bc];
    $this->page_construct('reports/profit_loss', $this->data, $meta);
}

public function get_profit_losss()
{
    $start_date = $this->input->get_post('start_date');
    $end_date   = $this->input->get_post('end_date');
    if (empty($start_date)) $start_date = date('Y-m-01 00:00:00');
    if (empty($end_date))   $end_date   = date('Y-m-t 23:59:59');
    

    $result = $this->reports_model->get_profit_loss($start_date, $end_date);

    echo json_encode($result);
}


/**
 * Generates the Profit & Loss PDF entirely on the server.
 * Mobile and desktop therefore receive exactly the same A4 layout.
 */
public function generate_profit_loss_pdf()
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return $this->profit_loss_pdf_json(false, 'Invalid request method.', 405);
    }

    $user_id = (int) $this->session->userdata('user_id');

    if ($user_id <= 0) {
        return $this->profit_loss_pdf_json(false, 'User session was not found.', 401);
    }

    if (!$this->profit_loss_load_mpdf()) {
        return $this->profit_loss_pdf_json(
            false,
            'mPDF library မတွေ့ပါ။ Project root တွင် composer require mpdf/mpdf ကို run လုပ်ပါ။',
            500
        );
    }

    $start_input = trim((string) $this->input->post('start_date', true));
    $end_input   = trim((string) $this->input->post('end_date', true));

    $start_date = $start_input !== ''
        ? $start_input
        : date('Y-m-01 00:00:00');

    $end_date = $end_input !== ''
        ? $end_input
        : date('Y-m-t 23:59:59');

    $start_timestamp = strtotime($start_date);
    $end_timestamp   = strtotime($end_date);

    if (!$start_timestamp || !$end_timestamp) {
        return $this->profit_loss_pdf_json(false, 'Invalid report date.', 400);
    }

    if ($start_timestamp > $end_timestamp) {
        return $this->profit_loss_pdf_json(
            false,
            'Start Date သည် End Date ထက် မကျော်ရပါ။',
            400
        );
    }

    $report_result = $this->reports_model->get_profit_loss(
        $start_date,
        $end_date
    );
    $report = is_object($report_result)
        ? (array) $report_result
        : (array) $report_result;

    $sales = $this->profit_loss_number(
        isset($report['sales'])
            ? $report['sales']
            : ($report['total_sales'] ?? 0)
    );
    $discounts = $this->profit_loss_number($report['discounts'] ?? 0);
    $net_sales = array_key_exists('net_sales', $report)
        ? $this->profit_loss_number($report['net_sales'])
        : ($sales - $discounts);

    $cogs = $this->profit_loss_number($report['cogs'] ?? 0);
    $gross_profit = array_key_exists('gross_profit', $report)
        ? $this->profit_loss_number($report['gross_profit'])
        : ($net_sales - $cogs);

    $operating_expenses = $this->profit_loss_number(
        isset($report['operating_expenses'])
            ? $report['operating_expenses']
            : ($report['expenses'] ?? 0)
    );
    $depreciation = $this->profit_loss_number(
        $report['depreciation'] ?? 0
    );
    $total_expenses = array_key_exists('total_expenses', $report)
        ? $this->profit_loss_number($report['total_expenses'])
        : ($operating_expenses + $depreciation);

    $net_profit = array_key_exists('net_profit', $report)
        ? $this->profit_loss_number($report['net_profit'])
        : ($gross_profit - $total_expenses);

    $pdf_data = [
        'title'              => $this->profit_loss_pdf_label(
            'profit_loss_report',
            'အမြတ်/အရှုံး အစီရင်ခံစာ'
        ),
        'start_date'         => date('Y-m-d H:i', $start_timestamp),
        'end_date'           => date('Y-m-d H:i', $end_timestamp),
        'particulars'        => $this->profit_loss_pdf_label(
            'particulars',
            'အချက်အလက်'
        ),
        'amount_mmk'         => $this->profit_loss_pdf_label(
            'amount_mmk',
            'ပမာဏ (ကျပ်)'
        ),
        'sales_revenue'      => $this->profit_loss_pdf_label(
            'sales_revenue',
            'အရောင်းဝင်ငွေ'
        ),
        'discounts_returns'  => $this->profit_loss_pdf_label(
            'discounts_returns',
            'လျှော့ချခြင်း / ပြန်အမ်းခြင်း'
        ),
        'net_sales_label'    => $this->profit_loss_pdf_label(
            'net_sales',
            'အရောင်းသန့်'
        ),
        'cogs_label'         => $this->profit_loss_pdf_label(
            'cogs',
            'ပစ္စည်းဝယ်ဈေး'
        ),
        'gross_profit_label' => $this->profit_loss_pdf_label(
            'gross_profit',
            'အကြမ်းအမြတ်'
        ),
        'operating_expenses_label' => $this->profit_loss_pdf_label(
            'operating_expenses',
            'လုပ်ငန်းလည်ပတ်စရိတ်'
        ),
        'depreciation_label' => $this->profit_loss_pdf_label(
            'depreciation',
            'တန်ဖိုးကျဆင်းမှု'
        ),
        'total_expenses_label' => $this->profit_loss_pdf_label(
            'total_expenses',
            'စုစုပေါင်းစရိတ်'
        ),
        'net_profit_label'   => $this->profit_loss_pdf_label(
            'net_profit',
            'အသားတင်အမြတ်'
        ),
        'sales'              => $sales,
        'discounts'          => $discounts,
        'net_sales'          => $net_sales,
        'cogs'               => $cogs,
        'gross_profit'       => $gross_profit,
        'operating_expenses' => $operating_expenses,
        'depreciation'       => $depreciation,
        'total_expenses'     => $total_expenses,
        'net_profit'         => $net_profit,
    ];

    $directory = $this->profit_loss_pdf_user_directory($user_id);

    if (!$this->profit_loss_ensure_directory($directory)) {
        return $this->profit_loss_pdf_json(
            false,
            'PDF storage folder cannot be created. app/cache permission ကို စစ်ပါ။',
            500
        );
    }

    $temp_directory = rtrim(APPPATH, '/\\')
        . DIRECTORY_SEPARATOR . 'cache'
        . DIRECTORY_SEPARATOR . 'mpdf'
        . DIRECTORY_SEPARATOR;

    if (!$this->profit_loss_ensure_directory($temp_directory)) {
        return $this->profit_loss_pdf_json(
            false,
            'mPDF temporary folder cannot be created. app/cache permission ကို စစ်ပါ။',
            500
        );
    }

    try {
        $token = bin2hex(random_bytes(8));
    } catch (Exception $exception) {
        $token = substr(
            sha1(uniqid((string) mt_rand(), true)),
            0,
            16
        );
    }

    $file_start = date('Y-m-d', $start_timestamp);
    $file_end   = date('Y-m-d', $end_timestamp);
    $filename = sprintf(
        'profit-loss_%s_to_%s_%s.pdf',
        $file_start,
        $file_end,
        $token
    );
    $destination = $directory . $filename;

    $font_config = $this->profit_loss_mpdf_font_config();

    $mpdf_config = [
        'mode'             => 'utf-8',
        'format'           => 'A4',
        'orientation'      => 'P',
        'margin_left'      => 10,
        'margin_right'     => 10,
        'margin_top'       => 11,
        'margin_bottom'    => 11,
        'margin_header'    => 0,
        'margin_footer'    => 0,
        'tempDir'          => $temp_directory,
        'autoScriptToLang' => true,
        'autoLangToFont'   => true,
    ];

    if (!empty($font_config)) {
        $mpdf_config = array_merge($mpdf_config, $font_config);
    }

    try {
        $mpdf = new \Mpdf\Mpdf($mpdf_config);
        $mpdf->SetTitle($pdf_data['title']);
        $mpdf->SetAuthor('KLSPOS');
        $mpdf->SetCreator('KLSPOS');
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->shrink_tables_to_fit = 1;

        $html = $this->load->view(
            $this->theme . 'reports/profit_loss_pdf_template',
            $pdf_data,
            true
        );

        $mpdf->WriteHTML($html);
        $mpdf->Output(
            $destination,
            \Mpdf\Output\Destination::FILE
        );
    } catch (Throwable $exception) {
        log_message(
            'error',
            'Profit/Loss mPDF error: ' . $exception->getMessage()
        );

        return $this->profit_loss_pdf_json(
            false,
            'PDF ထုတ်ရာတွင် Error ဖြစ်နေပါသည်။ Log ကို စစ်ပါ။',
            500
        );
    }

    if (!is_file($destination) || filesize($destination) < 100) {
        return $this->profit_loss_pdf_json(
            false,
            'Generated PDF file is invalid.',
            500
        );
    }

    @chmod($destination, 0640);
    $this->profit_loss_cleanup_old_pdfs($directory, 30);

    $expires = time() + 86400;
    $share_signature = $this->profit_loss_share_signature(
        $user_id,
        $filename,
        $expires
    );

    $open_url = site_url('reports/profit_loss_pdf')
        . '?file=' . rawurlencode($filename);
    $download_url = site_url('reports/profit_loss_pdf')
        . '?download=1&file=' . rawurlencode($filename);
    $share_url = site_url('reports/shared_profit_loss_pdf')
        . '?uid=' . $user_id
        . '&expires=' . $expires
        . '&file=' . rawurlencode($filename)
        . '&sig=' . rawurlencode($share_signature);

    return $this->profit_loss_pdf_json(
        true,
        'PDF ကို Server မှ မှန်ကန်စွာ ထုတ်ပြီးပါပြီ။',
        200,
        [
            'file_name'    => $filename,
            'open_url'     => $open_url,
            'download_url' => $download_url,
            'share_url'    => $share_url,
            'expires_at'   => date('Y-m-d H:i:s', $expires),
        ]
    );
}

private function profit_loss_load_mpdf()
{
    if (class_exists('\\Mpdf\\Mpdf')) {
        return true;
    }

    $autoload_files = [
        // Recommended isolated mPDF installation path.
        APPPATH . 'third_party/mpdf/vendor/autoload.php',

        // Other common Composer locations.
        FCPATH . 'vendor/autoload.php',
        APPPATH . 'vendor/autoload.php',
        dirname(APPPATH) . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'autoload.php',
    ];

    foreach ($autoload_files as $autoload_file) {
        if (is_file($autoload_file)) {
            require_once $autoload_file;

            if (class_exists('\\Mpdf\\Mpdf')) {
                return true;
            }
        }
    }

    return false;
}

private function profit_loss_mpdf_font_config()
{
    $candidate_fonts = [
        FCPATH . 'assets/fonts/Pyidaungsu.ttf',
        FCPATH . 'assets/fonts/Pyidaungsu-Regular.ttf',
        FCPATH . 'assets/fonts/NotoSansMyanmar-Regular.ttf',
        FCPATH . 'assets/fonts/NotoSansMyanmar.ttf',
        APPPATH . 'assets/fonts/Pyidaungsu.ttf',
        APPPATH . 'assets/fonts/NotoSansMyanmar-Regular.ttf',
        '/usr/share/fonts/truetype/noto/NotoSansMyanmar-Regular.ttf',
        '/usr/share/fonts/opentype/noto/NotoSansMyanmar-Regular.ttf',
        '/usr/local/share/fonts/NotoSansMyanmar-Regular.ttf',
    ];

    $asset_matches = array_merge(
        glob(FCPATH . 'assets/fonts/*Myanmar*.ttf') ?: [],
        glob(FCPATH . 'assets/fonts/*Pyidaungsu*.ttf') ?: []
    );

    $candidate_fonts = array_merge(
        $candidate_fonts,
        $asset_matches
    );

    $font_path = null;

    foreach ($candidate_fonts as $candidate_font) {
        if (is_file($candidate_font) && is_readable($candidate_font)) {
            $font_path = $candidate_font;
            break;
        }
    }

    if (!$font_path) {
        return [];
    }

    $default_config = (
        new \Mpdf\Config\ConfigVariables()
    )->getDefaults();
    $default_font_config = (
        new \Mpdf\Config\FontVariables()
    )->getDefaults();

    return [
        'fontDir' => array_merge(
            $default_config['fontDir'],
            [dirname($font_path)]
        ),
        'fontdata' => $default_font_config['fontdata'] + [
            'klsmyanmar' => [
                'R' => basename($font_path),
                'B' => basename($font_path),
            ],
        ],
        'default_font' => 'klsmyanmar',
    ];
}

private function profit_loss_pdf_label($key, $fallback)
{
    $line = lang($key);

    return (
        $line !== false &&
        $line !== null &&
        $line !== '' &&
        $line !== $key
    ) ? $line : $fallback;
}

private function profit_loss_number($value)
{
    if ($value === null || $value === '') {
        return 0.0;
    }

    return (float) str_replace(',', '', (string) $value);
}

/**
 * Receives a PDF generated by the Profit & Loss page and stores it privately.
 * The PDF is served through controller endpoints instead of a public uploads URL.
 */
public function save_profit_loss_pdf()
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return $this->profit_loss_pdf_json(false, 'Invalid request method.', 405);
    }

    if (!isset($_FILES['pdf_file'])) {
        return $this->profit_loss_pdf_json(false, 'PDF file was not received.', 400);
    }

    $upload = $_FILES['pdf_file'];

    if (!isset($upload['error']) || $upload['error'] !== UPLOAD_ERR_OK) {
        return $this->profit_loss_pdf_json(false, $this->profit_loss_upload_error($upload['error'] ?? null), 400);
    }

    if (empty($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
        return $this->profit_loss_pdf_json(false, 'Invalid PDF upload.', 400);
    }

    $max_size = 15 * 1024 * 1024;
    $file_size = isset($upload['size']) ? (int) $upload['size'] : 0;

    if ($file_size < 100 || $file_size > $max_size) {
        return $this->profit_loss_pdf_json(false, 'PDF file size must be between 100 bytes and 15 MB.', 400);
    }

    $handle = @fopen($upload['tmp_name'], 'rb');
    $signature = $handle ? fread($handle, 5) : false;

    if ($handle) {
        fclose($handle);
    }

    if ($signature !== '%PDF-') {
        return $this->profit_loss_pdf_json(false, 'Uploaded file is not a valid PDF.', 400);
    }

    $user_id = (int) $this->session->userdata('user_id');

    if ($user_id <= 0) {
        return $this->profit_loss_pdf_json(false, 'User session was not found.', 401);
    }

    $start_date = $this->profit_loss_safe_date(
        $this->input->post('start_date'),
        date('Y-m-01')
    );
    $end_date = $this->profit_loss_safe_date(
        $this->input->post('end_date'),
        date('Y-m-t')
    );

    $directory = $this->profit_loss_pdf_user_directory($user_id);

    if (!$this->profit_loss_ensure_directory($directory)) {
        return $this->profit_loss_pdf_json(
            false,
            'PDF storage folder cannot be created. Please check app/cache write permission.',
            500
        );
    }

    try {
        $token = bin2hex(random_bytes(8));
    } catch (Exception $exception) {
        $token = substr(sha1(uniqid((string) mt_rand(), true)), 0, 16);
    }

    $filename = sprintf(
        'profit-loss_%s_to_%s_%s.pdf',
        $start_date,
        $end_date,
        $token
    );
    $destination = $directory . $filename;

    if (!@move_uploaded_file($upload['tmp_name'], $destination)) {
        return $this->profit_loss_pdf_json(false, 'PDF could not be saved on the server.', 500);
    }

    @chmod($destination, 0640);
    $this->profit_loss_cleanup_old_pdfs($directory, 30);

    $expires = time() + 86400;
    $share_signature = $this->profit_loss_share_signature(
        $user_id,
        $filename,
        $expires
    );

    $open_url = site_url('reports/profit_loss_pdf')
        . '?file=' . rawurlencode($filename);
    $download_url = site_url('reports/profit_loss_pdf')
        . '?download=1&file=' . rawurlencode($filename);
    $share_url = site_url('reports/shared_profit_loss_pdf')
        . '?uid=' . $user_id
        . '&expires=' . $expires
        . '&file=' . rawurlencode($filename)
        . '&sig=' . rawurlencode($share_signature);

    return $this->profit_loss_pdf_json(true, 'PDF ကို Server တွင် သိမ်းပြီးပါပြီ။', 200, [
        'file_name'    => $filename,
        'open_url'     => $open_url,
        'download_url' => $download_url,
        'share_url'    => $share_url,
        'expires_at'   => date('Y-m-d H:i:s', $expires),
    ]);
}

/**
 * Opens or downloads the logged-in user's saved PDF.
 */
public function profit_loss_pdf()
{
    $user_id = (int) $this->session->userdata('user_id');
    $filename = basename((string) $this->input->get('file', true));

    if ($user_id <= 0 || !$this->profit_loss_valid_filename($filename)) {
        show_404();
        return;
    }

    $path = $this->profit_loss_pdf_user_directory($user_id) . $filename;

    if (!is_file($path) || !is_readable($path)) {
        show_404();
        return;
    }

    $download = $this->input->get('download') === '1';
    $this->profit_loss_stream_pdf($path, $filename, $download);
}

/**
 * Public, signed, expiring PDF link used by the mobile Share button.
 * The link expires after 24 hours.
 */
public function shared_profit_loss_pdf()
{
    $user_id = (int) $this->input->get('uid');
    $expires = (int) $this->input->get('expires');
    $filename = basename((string) $this->input->get('file', true));
    $signature = (string) $this->input->get('sig', true);

    if (
        $user_id <= 0 ||
        $expires < time() ||
        $expires > (time() + 172800) ||
        !$this->profit_loss_valid_filename($filename)
    ) {
        show_404();
        return;
    }

    $expected = $this->profit_loss_share_signature(
        $user_id,
        $filename,
        $expires
    );

    if (
        $signature === '' ||
        !function_exists('hash_equals') ||
        !hash_equals($expected, $signature)
    ) {
        show_404();
        return;
    }

    $path = $this->profit_loss_pdf_user_directory($user_id) . $filename;

    if (!is_file($path) || !is_readable($path)) {
        show_404();
        return;
    }

    $this->profit_loss_stream_pdf($path, $filename, false);
}

private function profit_loss_pdf_base_directory()
{
    return rtrim(APPPATH, '/\\')
        . DIRECTORY_SEPARATOR . 'cache'
        . DIRECTORY_SEPARATOR . 'profit_loss_reports'
        . DIRECTORY_SEPARATOR;
}

private function profit_loss_pdf_user_directory($user_id)
{
    return $this->profit_loss_pdf_base_directory()
        . (int) $user_id . DIRECTORY_SEPARATOR;
}

private function profit_loss_ensure_directory($directory)
{
    if (is_dir($directory)) {
        return is_writable($directory);
    }

    if (!@mkdir($directory, 0750, true) && !is_dir($directory)) {
        return false;
    }

    $index_file = rtrim($this->profit_loss_pdf_base_directory(), '/\\')
        . DIRECTORY_SEPARATOR . 'index.html';

    if (!is_file($index_file)) {
        @file_put_contents($index_file, '');
    }

    return is_writable($directory);
}

private function profit_loss_safe_date($value, $fallback)
{
    $timestamp = strtotime((string) $value);

    return $timestamp ? date('Y-m-d', $timestamp) : $fallback;
}

private function profit_loss_valid_filename($filename)
{
    return (bool) preg_match(
        '/^profit-loss_\d{4}-\d{2}-\d{2}_to_\d{4}-\d{2}-\d{2}_[a-f0-9]{16}\.pdf$/',
        $filename
    );
}

private function profit_loss_share_signature($user_id, $filename, $expires)
{
    $key = (string) $this->config->item('encryption_key');

    if ($key === '') {
        $key = hash('sha256', APPPATH . FCPATH . __FILE__);
    }

    $payload = (int) $user_id . '|' . $filename . '|' . (int) $expires;

    return hash_hmac('sha256', $payload, $key);
}

private function profit_loss_cleanup_old_pdfs($directory, $days)
{
    $cutoff = time() - ((int) $days * 86400);
    $files = glob(rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . '*.pdf');

    if (!$files) {
        return;
    }

    foreach ($files as $file) {
        if (is_file($file) && filemtime($file) < $cutoff) {
            @unlink($file);
        }
    }
}

private function profit_loss_upload_error($error_code)
{
    $messages = [
        UPLOAD_ERR_INI_SIZE   => 'PDF exceeds the server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'PDF exceeds the allowed upload size.',
        UPLOAD_ERR_PARTIAL    => 'PDF upload was incomplete.',
        UPLOAD_ERR_NO_FILE    => 'No PDF file was selected.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server temporary folder is missing.',
        UPLOAD_ERR_CANT_WRITE => 'Server could not write the PDF file.',
        UPLOAD_ERR_EXTENSION  => 'PDF upload was stopped by a server extension.',
    ];

    return isset($messages[$error_code])
        ? $messages[$error_code]
        : 'PDF upload failed.';
}

private function profit_loss_pdf_json($success, $message, $status_code = 200, array $extra = [])
{
    $payload = array_merge([
        'success'   => (bool) $success,
        'message'   => $message,
        'csrf_hash' => $this->security->get_csrf_hash(),
    ], $extra);

    $this->output
        ->set_status_header((int) $status_code)
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));

    return;
}

private function profit_loss_stream_pdf($path, $filename, $download = false)
{
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    $disposition = $download ? 'attachment' : 'inline';
    $safe_name = str_replace(['"', "\r", "\n"], '', $filename);

    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . $disposition . '; filename="' . $safe_name . '"');
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    readfile($path);
    exit;
}


public function dueprintall($customer_id = null, $noprint = null)
{
    if (!$customer_id) {
        $this->session->set_flashdata('error', lang('no_customer_selected'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    // Check store access
    if (!$this->session->userdata('store_id')) {
        $this->session->set_flashdata('warning', lang('please_select_store'));
        redirect('stores');
    }

    // ✅ Get all due or partial invoices for this customer
    $this->db->where('customer_id', $customer_id);
    $this->db->where_in('status', ['partial', 'due']);
    $this->db->where('store_id', $this->session->userdata('store_id'));
    $this->db->order_by('id', 'ASC');
    $sales = $this->db->get('sales')->result();

    if (!$sales) {
        $this->session->set_flashdata('error', lang('no_due_invoice_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['message'] = $this->session->flashdata('message');
    $this->load->helper('text');

    $all_invoices = [];
    foreach ($sales as $inv) {
        // Check access
        if ($this->session->userdata('store_id') != $inv->store_id) {
            continue;
        }

        $invoice_data = [];
        $invoice_data['inv']        = $inv;
        $invoice_data['rows']       = $this->pos_model->getAllSaleItems($inv->id);
        $invoice_data['customer']   = $this->pos_model->getCustomerByID($inv->customer_id);
        $invoice_data['store']      = $this->site->getStoreByID($inv->store_id);
        $invoice_data['payments']   = $this->pos_model->getAllSalePayments($inv->id);
        $invoice_data['created_by'] = $this->site->getUser($inv->created_by);
        

        $all_invoices[] = $invoice_data;
    }

    $this->data['all_invoices'] = $all_invoices;
    $this->data['printer'] = $this->site->getPrinterByID($this->Settings->printer);
    $this->data['page_title'] = lang('due_invoices');
    $this->data['modal']      = $noprint ? true : false;

    $this->load->view($this->theme . 'reports/dueprintall', $this->data);
    
}

public function dueprint($customer_id = null, $noprint = null)
{
    if (!$customer_id) {
        $this->session->set_flashdata('error', lang('no_customer_selected'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    // Check store access
    if (!$this->session->userdata('store_id')) {
        $this->session->set_flashdata('warning', lang('please_select_store'));
        redirect('stores');
    }

    // ✅ Get all due or partial invoices for this customer
    $this->db->where('customer_id', $customer_id);
    $this->db->where_in('status', ['partial', 'due']);
    $this->db->where('store_id', $this->session->userdata('store_id'));
    $this->db->order_by('id', 'ASC');
    $sales = $this->db->get('sales')->result();

    if (!$sales) {
        $this->session->set_flashdata('error', lang('no_due_invoice_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['message'] = $this->session->flashdata('message');
    $this->load->helper('text');

    $all_invoices = [];
    foreach ($sales as $inv) {
        // Check access
        if ($this->session->userdata('store_id') != $inv->store_id) {
            continue;
        }

        $invoice_data = [];
        $invoice_data['inv']        = $inv;
        $invoice_data['rows']       = $this->pos_model->getAllSaleItems($inv->id);
        $invoice_data['customer']   = $this->pos_model->getCustomerByID($inv->customer_id);
        $invoice_data['store']      = $this->site->getStoreByID($inv->store_id);
        $invoice_data['payments']   = $this->pos_model->getAllSalePayments($inv->id);
        $invoice_data['created_by'] = $this->site->getUser($inv->created_by);
        

        $all_invoices[] = $invoice_data;
    }

    $this->data['all_invoices'] = $all_invoices;
    $this->data['printer'] = $this->site->getPrinterByID($this->Settings->printer);
    $this->data['page_title'] = lang('due_invoices');
    $this->data['modal']      = $noprint ? true : false;

    $this->load->view($this->theme . 'reports/dueprint', $this->data);
    
}

public function supplierdueprintall($supplier_id = null, $noprint = null)
{
    if (!$supplier_id) {
        $this->session->set_flashdata('error', lang('no_supplier_selected'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    

    // ✅ Get all due or partial purchases for this supplier
    $this->db->where('supplier_id', $supplier_id);
    $this->db->where_in('status', ['partial', 'due']); 
    $this->db->order_by('id', 'ASC');
    $purchases = $this->db->get('purchases')->result();

    if (!$purchases) {
        $this->session->set_flashdata('error', lang('no_due_purchase_found'));
        redirect($_SERVER["HTTP_REFERER"]);
    }

    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['message'] = $this->session->flashdata('message');
    $this->load->helper('text');

    $all_invoices = [];
    foreach ($purchases as $inv) {
        // Check store access
        

        $invoice_data = [];
        $invoice_data['inv']        = $inv;
        $invoice_data['rows']       = $this->pos_model->getAllPurchaseItems($inv->id);
        $invoice_data['supplier']   = $this->site->getSupplierByID($inv->supplier_id);
        $invoice_data['store']      = $this->site->getStoreByID($inv->store_id);
        $invoice_data['payments']   = $this->pos_model->getAllPurchasePayments($inv->id);
        $invoice_data['created_by'] = $this->site->getUser($inv->created_by);

        $all_invoices[] = $invoice_data;
    }

    $this->data['all_invoices'] = $all_invoices;
    $this->data['printer'] = $this->site->getPrinterByID($this->Settings->printer);
    $this->data['page_title'] = lang('supplier_due_invoices');
    $this->data['modal']      = $noprint ? true : false;

    $this->load->view($this->theme . 'reports/supplier_dueprintall', $this->data);
}

public function customer_order_report()
{

    $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    $this->data['customers']  = $this->reports_model->getAllCustomers();
    $this->data['products']   = $this->reports_model->getAllProducts();
    $this->data['profit_loss_data'] = $data; // optional to pass profit/loss result to view

    $bc   = [['link' => '#', 'page' => lang('customer_order_report')], ['link' => '#', 'page' => lang('customer_order_report')]];
    $meta = ['page_title' => lang('customer_order_report'), 'bc' => $bc];
    $this->page_construct('reports/customer_order_report', $this->data, $meta);
}



public function get_customer_orders()
{
    $customer = $this->input->get('customer');
    $product  = $this->input->get('product');

    // ❌ First page (no customer and no product)
    // ➜ Return empty data  
    if (!$customer && !$product) {
        echo json_encode(["data" => []]);
        return;
    }

    // ✔ After search — return results
    $rows = $this->reports_model->getCustomerOrders($customer, $product);
    echo json_encode(["data" => $rows]);
}



}
