<?php
 if (!defined('BASEPATH')) {
     exit('No direct script access allowed');
 }

class Reports_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllCustomers()
    {
        $q = $this->db->get('customers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getAllSuppliers()
    {
        $q = $this->db->get('suppliers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllStaff()
    {
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDailySales($year, $month)
    {
        if ($this->db->dbdriver == 'sqlite3') {
            $this->db->select("strftime('%d', date) AS date, COALESCE(sum(product_tax), 0) as product_tax, COALESCE(sum(order_tax), 0) as order_tax, COALESCE(sum(total), 0) as total, COALESCE(sum(grand_total), 0) as grand_total, COALESCE(sum(total_tax), 0) as total_tax, COALESCE(sum(rounding), 0) as rounding, COALESCE(sum(total_discount), 0) as discount, COALESCE(sum(paid), 0) as paid", false)->group_by("strftime('%d', date)");
        } else {
            $this->db->select("DATE_FORMAT(date,  '%d') AS date, COALESCE(sum(product_tax), 0) as product_tax, COALESCE(sum(order_tax), 0) as order_tax, COALESCE(sum(total), 0) as total, COALESCE(sum(grand_total), 0) as grand_total, COALESCE(sum(total_tax), 0) as total_tax, COALESCE(sum(rounding), 0) as rounding, COALESCE(sum(total_discount), 0) as discount, COALESCE(sum(paid), 0) as paid", false)->group_by("DATE_FORMAT(date, '%d')");
        }
        $this->db->like('date', "{$year}-{$month}", 'after');
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getMonthlySales($year)
    {
        if ($this->db->dbdriver == 'sqlite3') {
            $this->db->select("strftime('%m', date) AS date, COALESCE(sum(product_tax), 0) as product_tax, COALESCE(sum(order_tax), 0) as order_tax, COALESCE(sum(total), 0) as total, COALESCE(sum(grand_total), 0) as grand_total, COALESCE(sum(total_tax), 0) as tax, COALESCE(sum(total_discount), 0) as discount, COALESCE(sum(paid), 0) as paid", false)
            ->group_by("strftime('%m', date)")
            ->order_by("strftime('%m', date) ASC");
        } else {
            $this->db->select("DATE_FORMAT( date,  '%m' ) AS date, COALESCE(sum(product_tax), 0) as product_tax, COALESCE(sum(order_tax), 0) as order_tax, COALESCE(sum(total), 0) as total, COALESCE(sum(grand_total), 0) as grand_total, COALESCE(sum(total_tax), 0) as tax, COALESCE(sum(total_discount), 0) as discount, COALESCE(sum(paid), 0) as paid", false)
            ->group_by("DATE_FORMAT(date, '%m')")
            ->order_by("DATE_FORMAT(date, '%m') ASC");
        }

        $this->db->like('date', "{$year}", 'after');
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTotalCustomerSales($customer_id, $user = null, $start_date = null, $end_date = null, $status = null)
    {
        $this->db->select('COUNT(id) as number, sum(grand_total) as amount, sum(paid) as paid');
        if ($start_date && $end_date) {
            $this->db->where('date >=', $start_date);
            $this->db->where('date <=', $end_date);
        }
        if ($user) {
            $this->db->where('created_by', $user);
        }
        if ($status) {
            $this->db->where('status', $status);
        }
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get_where('sales', ['customer_id' => $customer_id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    public function getTotalSupplierPurchase($supplier_id, $user = null, $start_date = null, $end_date = null)
    {
        $this->db->select('COUNT(id) as number, sum(total) as amount, sum(paid) as paid');
        if ($start_date && $end_date) {
            $this->db->where('date >=', $start_date);
            $this->db->where('date <=', $end_date);
        }
        if ($user) {
            $this->db->where('created_by', $user);
        }
       
        $q = $this->db->get_where('purchases', ['supplier_id' => $supplier_id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalExpenses($start, $end)
    {
        $this->db->select('count(id) as total, sum(COALESCE(amount_base, 0)) as total_amount', false)
            ->where("date >= '{$start}' and date <= '{$end}'", null, false);
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPurchases($start, $end)
    {
        $this->db->select('count(id) as total, sum(COALESCE(total, 0)) as total_amount', false)
            ->where("date >= '{$start}' and date <= '{$end}'", null, false);
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalSales($start, $end)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where("date >= '{$start}' and date <= '{$end}'", null, false);
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalSalesforCustomer($customer_id, $user = null, $start_date = null, $end_date = null)
    {
        if ($start_date && $end_date) {
            $this->db->where('date >=', $start_date);
            $this->db->where('date <=', $end_date);
        }
        if ($user) {
            $this->db->where('created_by', $user);
        }
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get_where('sales', ['customer_id' => $customer_id]);
        return $q->num_rows();
    }

    public function getTotalSalesValueforCustomer($customer_id, $user = null, $start_date = null, $end_date = null)
    {
        $this->db->select('sum(grand_total) as total');
        if ($start_date && $end_date) {
            $this->db->where('date >=', $start_date);
            $this->db->where('date <=', $end_date);
        }
        if ($user) {
            $this->db->where('created_by', $user);
        }
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get_where('sales', ['customer_id' => $customer_id]);
        if ($q->num_rows() > 0) {
            $s = $q->row();
            return $s->total;
        }
        return false;
    }

    public function topProducts()
    {
        $m = date('Y-m');
        $this->db->select($this->db->dbprefix('products') . '.code as product_code, ' . $this->db->dbprefix('products') . '.name as product_name, sum(' . $this->db->dbprefix('sale_items') . '.quantity) as quantity')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('sales', 'sales.id=sale_items.sale_id', 'left')
        ->order_by('sum(' . $this->db->dbprefix('sale_items') . '.quantity)', 'desc')
        ->group_by('sale_items.product_id')
        ->limit(30)
        ->like($this->db->dbprefix('sales') . '.date', $m, 'both');
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function topProducts1()
    {
        $m = date('Y-m', strtotime('first day of last month'));
        $this->db->select($this->db->dbprefix('products') . '.code as product_code, ' . $this->db->dbprefix('products') . '.name as product_name, sum(' . $this->db->dbprefix('sale_items') . '.quantity) as quantity')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('sales', 'sales.id=sale_items.sale_id', 'left')
        ->order_by('sum(' . $this->db->dbprefix('sale_items') . '.quantity)', 'desc')
        ->group_by('sale_items.product_id')
        ->limit(30)
        ->like($this->db->dbprefix('sales') . '.date', $m, 'both');
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function topProducts12()
    {
        $this->db->select($this->db->dbprefix('products') . '.code as product_code, ' . $this->db->dbprefix('products') . '.name as product_name, sum(' . $this->db->dbprefix('sale_items') . '.quantity) as quantity')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('sales', 'sales.id=sale_items.sale_id', 'left')
        ->order_by('sum(' . $this->db->dbprefix('sale_items') . '.quantity)', 'desc')
        ->group_by('sale_items.product_id')
        ->limit(30);
        if ($this->db->dbdriver == 'sqlite3') {
            // ->where("date >= datetime('now','-6 month')", NULL, FALSE)
            $this->db->where("{$this->db->dbprefix('sales')}.date >= datetime(date('now','start of month','+1 month','-1 day'), '-12 month')", null, false);
        } else {
            $this->db->where($this->db->dbprefix('sales') . '.date >= last_day(now()) + interval 1 day - interval 12 month', null, false);
        }

        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function topProducts3()
    {
        $this->db->select($this->db->dbprefix('products') . '.code as product_code, ' . $this->db->dbprefix('products') . '.name as product_name, sum(' . $this->db->dbprefix('sale_items') . '.quantity) as quantity')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('sales', 'sales.id=sale_items.sale_id', 'left')
        ->order_by('sum(' . $this->db->dbprefix('sale_items') . '.quantity)', 'desc')
        ->group_by('sale_items.product_id')
        ->limit(30);
        if ($this->db->dbdriver == 'sqlite3') {
            // ->where("date >= datetime('now','-6 month')", NULL, FALSE)
            $this->db->where("{$this->db->dbprefix('sales')}.date >= datetime(date('now','start of month','+1 month','-1 day'), '-3 month')", null, false);
        } else {
            $this->db->where($this->db->dbprefix('sales') . '.date >= last_day(now()) + interval 1 day - interval 3 month', null, false);
        }
        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProfitReport()
    {
        $this->db->select("s.id as sale_id, s.date as sale_date, s.id, s.total as sale_total, 
                        COALESCE(SUM(c.total_cost), 0) as cogs_total, 
                        (s.total - COALESCE(SUM(c.total_cost), 0)) as profit");
        $this->db->from('sales s');
        $this->db->join('cogs_logs c', 'c.sale_id = s.id', 'left');
        $this->db->group_by('s.id');
        $this->db->order_by('s.date', 'DESC');
        return $this->db->get()->result();
    }
    
    public function get_currency_rate($code)
{
    $q = $this->db->get_where('currencies', ['code' => $code], 1);
    return $q->num_rows() ? $q->row()->rate : 1;
}

public function get_total_sales($start, $end, $warehouse_id, $container_box)
{
    $this->db->select('tec_sales.id, tec_sales.total'); // select specific fields
    $this->db->from('tec_sales');
    $this->db->join('tec_cogs_logs', 'tec_cogs_logs.sale_id = tec_sales.id');
    $this->db->join('tec_product_batches', 'tec_product_batches.id = tec_cogs_logs.batch_id');

    if ($start && $end) {
        $this->db->where('tec_sales.date >=', $start);
        $this->db->where('tec_sales.date <=', $end);
    }
    if ($warehouse_id) {
        $this->db->where('tec_sales.warehouse_id', $warehouse_id);
    }
    if ($container_box) {
        $this->db->where('tec_product_batches.container_box', $container_box);
    }

    $this->db->group_by('tec_sales.id'); // prevent duplicate sales rows

    $q = $this->db->get();

    $total = 0;
    foreach ($q->result() as $row) {
        $total += $row->total;
    }

    return $total;
}

public function get_total_discounts($start, $end, $warehouse_id, $container_box)
{
    $this->db->select('tec_sales.id, tec_sales.total_discount');
    $this->db->from('sales');
    $this->db->join('cogs_logs', 'tec_cogs_logs.sale_id = tec_sales.id');
    $this->db->join('product_batches', 'tec_product_batches.id = tec_cogs_logs.batch_id');

    if ($start && $end) {
        $this->db->where('tec_sales.date >=', $start);
        $this->db->where('tec_sales.date <=', $end);
    }
    if ($warehouse_id) {
        $this->db->where('tec_sales.warehouse_id', $warehouse_id);
    }
    if ($container_box) {
        $this->db->where('tec_product_batches.container_box', $container_box);
    }

    $this->db->group_by('tec_sales.id'); // avoid duplicates

    $q = $this->db->get();

    $total_discount = 0;
    foreach ($q->result() as $row) {
        $total_discount += $row->total_discount;
    }

    return $total_discount;
}


public function get_total_cogs($start, $end, $warehouse_id, $container_box)
{
    $this->db->select_sum('tec_cogs_logs.total_cost');
    $this->db->from('cogs_logs');
    $this->db->join('product_batches', 'tec_product_batches.id = tec_cogs_logs.batch_id', 'left');

    if ($start && $end) {
        $this->db->where('tec_cogs_logs.date >=', $start);
        $this->db->where('tec_cogs_logs.date <=', $end);
    }
    if ($warehouse_id) {
        $this->db->where('tec_cogs_logs.warehouse_id', $warehouse_id);
    }
    if ($container_box) {
        $this->db->where('tec_product_batches.container_box', $container_box);
    }

    $q = $this->db->get();
    return $q->row()->total_cost ?? 0;
}


public function get_total_expenses($start, $end, $warehouse_id, $container_box)
{
    $this->db->select_sum('amount_base');
    $this->db->from('expenses');

    if ($start && $end) {
        $this->db->where('date >=', $start);
        $this->db->where('date <=', $end);
    }
    if ($warehouse_id) {
        $this->db->where('warehouse_id', $warehouse_id);
    }
    if ($container_box) {
        $this->db->where('container_box', $container_box);
    }

    $q = $this->db->get();
    return $q->row()->amount_base ?? 0;
}

public function get_profit_loss($start_date = null, $end_date = null)
{
    // 🧾 Ensure dates are not empty
    $start_date = !empty($start_date) ? $start_date : date('Y-m-01 00:00:00');
    $end_date   = !empty($end_date)   ? $end_date   : date('Y-m-t 23:59:59');

    log_message('debug', "📅 get_profit_loss() START | Start Date: {$start_date}, End Date: {$end_date}");

    // 🛒 Sales
    $q1 = $this->db->select_sum('grand_total', 'sales_total')
                   ->where("date >=", $start_date)
                   ->where("date <=", $end_date)
                   ->get('sales');
    log_message('debug', '🟡 SALES QUERY: ' . $this->db->last_query());
    $sales = ($q1 && $q1->num_rows() > 0) ? (float) $q1->row()->sales_total : 0;
    log_message('debug', "🧮 Sales Total: {$sales}");

    // 💸 Discounts
    $q2 = $this->db->select_sum('total_discount', 'discount_total')
                   ->where("date >=", $start_date)
                   ->where("date <=", $end_date)
                   ->get('sales');
    log_message('debug', '🟡 DISCOUNTS QUERY: ' . $this->db->last_query());
    $discounts = ($q2 && $q2->num_rows() > 0) ? (float) $q2->row()->discount_total : 0;
    log_message('debug', "🧮 Discount Total: {$discounts}");

    // 🧾 COGS
    // 🧾 Get sale IDs first (date filter)
    $this->db->select('id');
    $this->db->where("date >=", $start_date);
    $this->db->where("date <=", $end_date);
    $sales_query = $this->db->get('sales');
    log_message('debug', '🟡 SALES ID QUERY: ' . $this->db->last_query());

    $sale_ids = [];
    if ($sales_query && $sales_query->num_rows() > 0) {
        foreach ($sales_query->result() as $s) {
            $sale_ids[] = $s->id;
        }
    }
    log_message('debug', '🧾 Sale IDs for COGS: ' . json_encode($sale_ids));

    $cogs = 0;
    if (!empty($sale_ids)) {
        // 🧮 Sum total_cost from tec_cogs_logs for these sale_ids
        $q3 = $this->db->select_sum('total_cost', 'cogs_total')
                    ->from('tec_cogs_logs')
                    ->where_in('sale_id', $sale_ids)
                    ->get();

        log_message('debug', '🟡 COGS QUERY (using sale_ids): ' . $this->db->last_query());
        $cogs = ($q3 && $q3->num_rows() > 0) ? (float) $q3->row()->cogs_total : 0;
    }
    log_message('debug', "🧮 COGS Total (from filtered sale_ids): {$cogs}");


    // 📊 Expenses
    $qExp = $this->db->select('et.name as category, SUM(e.amount) as total')
                     ->from('expenses e')
                     ->join('expensetype et', 'et.id = e.type_id', 'left')
                     ->where("e.date >=", $start_date)
                     ->where("e.date <=", $end_date)
                     ->group_by('et.name')
                     ->get();
    log_message('debug', '🟡 EXPENSES QUERY: ' . $this->db->last_query());
    $expenses = ($qExp && $qExp->num_rows() > 0) ? $qExp->result() : [];
    log_message('debug', '🧮 Expenses Result: ' . json_encode($expenses));

    // 🏢 Depreciation
    $q4 = $this->db->select('*')
                   ->where("purchase_date <=", $end_date)
                   ->get('depreciation_expenses');
    log_message('debug', '🟡 DEPRECIATION QUERY: ' . $this->db->last_query());
    
    $depreciation = 0;
    if ($q4 && $q4->num_rows() > 0) {
        foreach ($q4->result() as $asset) {
            $annualDep = $asset->purchase_cost / $asset->useful_life;
            $dailyDep  = $annualDep / 365;

            $asset_start = max(strtotime($asset->purchase_date), strtotime($start_date));
            $asset_end   = min(strtotime($end_date), strtotime($asset->purchase_date . " +{$asset->useful_life} years"));

            if ($asset_end >= $asset_start) {
                $daysInRange = ($asset_end - $asset_start) / (60*60*24) + 1;
                $depreciation += $dailyDep * $daysInRange;
            }

            log_message('debug', "🏗️ Asset: {$asset->id}, AnnualDep: {$annualDep}, DailyDep: {$dailyDep}, DaysInRange: {$daysInRange}, TotalDep: {$depreciation}");
        }
    }
    log_message('debug', "🧮 Total Depreciation: {$depreciation}");

    // 🧮 Final Calculations
    $net_sales    = $sales - $discounts;
    $gross_profit = $net_sales - $cogs;

    $operating_expenses = 0;
    foreach ($expenses as $exp) {
        if ($exp->category != 'Depreciation') {
            $operating_expenses += (float) $exp->total;
        }
    }

    $total_expenses = $operating_expenses + $depreciation;
    $net_profit     = $gross_profit - $total_expenses;

    log_message('debug', "📊 Net Sales: {$net_sales}");
    log_message('debug', "📊 Gross Profit: {$gross_profit}");
    log_message('debug', "📊 Operating Expenses: {$operating_expenses}");
    log_message('debug', "📊 Total Expenses: {$total_expenses}");
    log_message('debug', "💰 Net Profit: {$net_profit}");

    log_message('debug', "✅ get_profit_loss() END");

    return [
        'sales'              => $sales,
        'discounts'          => $discounts,
        'net_sales'          => $net_sales,
        'cogs'               => $cogs,
        'gross_profit'       => $gross_profit,
        'expenses'           => $expenses,
        'operating_expenses' => $operating_expenses,
        'depreciation'       => $depreciation,
        'total_expenses'     => $total_expenses,
        'net_profit'         => $net_profit,
        'start_date'         => $start_date,
        'end_date'           => $end_date,
    ];
}

public function getCustomerOrders($customer_id = null, $product_id = null)
{
    $this->db->select("
        s.id as sale_id,
        c.name as customer_name,
        p.name as product_name,
        si.net_unit_price as price,
        si.quantity as qty,
        si.subtotal as total,
        s.date
    ");

    $this->db->from("tec_sales s");
    $this->db->join("tec_sale_items si", "si.sale_id = s.id");
    $this->db->join("tec_products p", "p.id = si.product_id");
    $this->db->join("tec_customers c", "c.id = s.customer_id", "left");

    if ($customer_id) {
        $this->db->where("s.customer_id", $customer_id);
    }

    if ($product_id) {
        $this->db->where("si.product_id", $product_id);
    }

    $this->db->order_by("s.id", "DESC");

    return $this->db->get()->result();
}





}
