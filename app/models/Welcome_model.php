<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
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

    public function getChartData($user_id = null)
    {
        if (!$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        if ($this->db->dbdriver == 'sqlite3') {
            $this->db->select("strftime('%Y-%m', date) as month, SUM(total) as total, SUM(total_tax) as tax, SUM(total_discount) as discount")
                ->where("date >= datetime('now','-6 month')", null, false)
                // ->order_by("strftime('%Y-%m', date)", 'asc')
                ->group_by("strftime('%Y-%m', date)");
        } else {
            $this->db->select("date_format(date, '%Y-%m') as month, SUM(total) as total, SUM(total_tax) as tax, SUM(total_discount) as discount")
                ->where('date >= date_sub( now() , INTERVAL 6 MONTH)', null, false)
                // ->order_by("date_format(date, '%Y-%m')", 'asc')
                ->group_by("date_format(date, '%Y-%m')");
        }
        if ($user_id) {
            $this->db->where('created_by', $user_id);
        }
        if ($store_id = $this->session->userdata('store_id')) {
            $this->db->where('store_id', $store_id);
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

    public function getUserGroups()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('users_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function syncStoreQty()
    {
        $products = $this->getAllProducts();
        foreach ($products as $product) {
            $this->db->insert('product_store_qty', ['product_id' => $product->id, 'store_id' => 1, 'quantity' => $product->quantity]);
        }
        $this->db->update('settings', ['version' => '4.0.6'], ['setting_id' => 1]);
    }

    public function topProducts($user_id = null)
    {
        $m = date('Y-m');
        if (!$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select($this->db->dbprefix('products') . '.code as product_code, ' . $this->db->dbprefix('products') . '.name as product_name, sum(' . $this->db->dbprefix('sale_items') . '.quantity) as quantity')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('sales', 'sales.id=sale_items.sale_id', 'left')
            ->order_by('sum(' . $this->db->dbprefix('sale_items') . '.quantity)', 'desc')
            ->group_by('sale_items.product_id')
            ->limit(10)
            ->like("{$this->db->dbprefix('sales')}.date", $m, 'both');
        if ($user_id) {
            $this->db->where('created_by', $user_id);
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

    public function userGroups()
    {
        $ugs = $this->getUserGroups();
        if ($ugs) {
            foreach ($ugs as $ug) {
                $this->db->update('users', ['group_id' => $ug->group_id], ['id' => $ug->user_id]);
            }
            return true;
        }
        return false;
    }

    public function topCustomers()
    {
        $this->db->select("customer_name, SUM(grand_total) as total_amount")
            ->from("sales")
            ->group_by("customer_id")
            ->order_by("total_amount", "DESC")
            ->limit(5);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function topExpense()
    {
        $today = date('Y-m-d');
        $this->db->select('et.name as type_name, SUM(e.amount) as total')
            ->from('tec_expenses e')
            ->join('tec_expensetype et', 'et.id = e.type_id', 'left')
            ->where('e.date LIKE', "$today%")
            ->group_by('e.type_id')
            ->order_by('total', 'DESC');
        $q = $this->db->get();

        $data = []; // important
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function get_total_sales($start, $end)
    {
        $this->db->select_sum('grand_total');
        $this->db->from('sales');
        if ($start) $this->db->where('date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('date <=', $end . ' 23:59:59');
        $q = $this->db->get();
        return $q->row()->grand_total ?? 0;
    }

    // Create similar methods for get_total_purchases(), get_total_expenses()
    // And chart data methods get_sales_chart(), get_top_products_chart() etc.

    public function get_total_purchases($start, $end)
    {
        $this->db->select_sum('total');
        $this->db->from('purchases');
        if ($start) $this->db->where('date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('date <=', $end . ' 23:59:59');
        $q = $this->db->get();
        return $q->row()->total ?? 0;
    }

    public function get_total_expenses($start, $end)
    {
        $this->db->select_sum('amount');
        $this->db->from('expenses');
        if ($start) $this->db->where('date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('date <=', $end . ' 23:59:59');
        $q = $this->db->get();
        return $q->row()->amount ?? 0;
    }

    public function get_sales_chart($start, $end)
    {
        $this->db->select("DATE_FORMAT(date, '%b-%Y') as month, 
            SUM(grand_total) as total, 
            SUM(total_tax) as tax, 
            SUM(total_discount) as discount");
        $this->db->from('sales');
        if ($start) $this->db->where('date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('date <=', $end . ' 23:59:59');
        $this->db->group_by("DATE_FORMAT(date, '%Y-%m')");
        $this->db->order_by("date", 'ASC');
        $q = $this->db->get();

        $months = $sales = $tax = $discount = [];
        foreach ($q->result() as $row) {
            $months[]   = $row->month;
            $sales[]    = (float)$row->total;
            $tax[]      = (float)$row->tax;
            $discount[] = (float)$row->discount;
        }

        return [
            'chart' => [
                'type' => 'column'
            ],
            'credits' => ['enabled' => false],
            'title' => ['text' => 'Monthly Sales Summary'],
            'xAxis' => [
                'categories' => $months,
                'crosshair'  => true
            ],
            'yAxis' => [
                'min' => 0,
                'title' => ['text' => 'Amount']
            ],
            'series' => [
                ['name' => 'Tax',      'data' => $tax],
                ['name' => 'Discount', 'data' => $discount],
                ['name' => 'Sales',    'data' => $sales]
            ]
        ];
    }


    public function get_top_products_chart($start, $end)
    {
        $this->db->select('products.name as product_name, products.code as product_code, SUM(tec_sale_items.quantity) as quantity');
        $this->db->from('sale_items');
        $this->db->join('products', 'products.id = sale_items.product_id', 'left');
        $this->db->join('sales', 'sales.id = sale_items.sale_id', 'left');
        if ($start) $this->db->where('sales.date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('sales.date <=', $end . ' 23:59:59');
        $this->db->group_by('sale_items.product_id');
        $this->db->order_by('quantity', 'DESC');
        $this->db->limit(5);
        $q = $this->db->get();

        $data = [];
        foreach ($q->result() as $row) {
            $data[] = [$row->product_name . ' (' . $row->product_code . ')', (int)$row->quantity];
        }

        return [
            'chart' => ['type' => 'pie'],
            'title' => ['text' => 'Top Products'],
            'tooltip' => ['pointFormat' => '<b>{point.y}</b> units sold ({point.percentage:.1f}%)'],
            'series' => [[
                'name' => 'Units Sold',
                'colorByPoint' => true,
                'data' => $data
            ]]
        ];
    }

    public function get_customers_chart($start, $end)
    {
        $this->db->select('customers.name as customer_name, SUM(tec_sales.grand_total) as total_amount');
        $this->db->from('sales');
        $this->db->join('customers', 'customers.id = sales.customer_id', 'left');
        if ($start) $this->db->where('sales.date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('sales.date <=', $end . ' 23:59:59');
        $this->db->group_by('sales.customer_id');
        $this->db->order_by('total_amount', 'DESC');
        $this->db->limit(5);
        $q = $this->db->get();

        $names = [];
        $amounts = [];
        foreach ($q->result() as $row) {
            $names[] = $row->customer_name;
            $amounts[] = (float)$row->total_amount;
        }

        return [
            'chart' => ['type' => 'bar'],
            'title' => ['text' => 'Top Customers'],
            'xAxis' => ['categories' => $names],
            'yAxis' => ['title' => ['text' => 'Total Purchase (MMK)']],
            'series' => [[
                'name' => 'Total Sales',
                'data' => $amounts
            ]]
        ];
    }

    public function get_expenses_chart($start, $end)
    {
        $this->db->select('tec_expensetype.name as type_name, SUM(tec_expenses.amount) as total');
        $this->db->from('expenses');
        $this->db->join('expensetype', 'tec_expensetype.id = expenses.type_id', 'left');
        if ($start) $this->db->where('expenses.date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('expenses.date <=', $end . ' 23:59:59');
        $this->db->group_by('tec_expensetype.id');
        $q = $this->db->get();

        $data = [];
        foreach ($q->result() as $row) {
            $data[] = [$row->type_name, (float)$row->total];
        }

        return [
            'chart' => ['type' => 'column'],
            'title' => ['text' => "Expenses by Type"],
            'series' => [[
                'name' => 'Expenses',
                'colorByPoint' => true,
                'data' => $data
            ]]
        ];
    }

    public function get_dashboard_data($start, $end)
    {
        // Total Sales
        $this->db->select_sum('grand_total');
        $this->db->from('sales');
        if ($start) $this->db->where('date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('date <=', $end . ' 23:59:59');
        $sales_total = $this->db->get()->row()->grand_total ?? 0;

        // Total Purchases
        $this->db->select_sum('total');
        $this->db->from('purchases');
        if ($start) $this->db->where('date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('date <=', $end . ' 23:59:59');
        $purchases_total = $this->db->get()->row()->total ?? 0;

        // Total Expenses
        $this->db->select_sum('amount');
        $this->db->from('expenses');
        if ($start) $this->db->where('date >=', $start . ' 00:00:00');
        if ($end)   $this->db->where('date <=', $end . ' 23:59:59');
        $expenses_total = $this->db->get()->row()->amount ?? 0;

        $profit_loss = $sales_total - $purchases_total - $expenses_total;

        // Sales Chart
        $sales_chart = $this->get_sales_chart($start, $end);

        // Top Products Chart
        $top_products_chart = $this->get_top_products_chart($start, $end);

        // Top Customers Chart
        $customers_chart = $this->get_customers_chart($start, $end);

        // Expenses Chart
        $expenses_chart = $this->get_expenses_chart($start, $end);

        // Return all data in one array
        return [
            'total_sales'        => (float)$sales_total,
            'total_purchases'    => (float)$purchases_total,
            'total_expenses'     => (float)$expenses_total,
            'profit_loss'        => (float)$profit_loss,
            'chartData' => [
                'salesChart'       => $sales_chart,
                'topProductsChart' => $top_products_chart,
                'customersChart'   => $customers_chart,
                'expensesChart'    => $expenses_chart,
            ]
        ];
    }
}
