<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->verify_token();
    }
    
    // ======================================
    // SALES DASHBOARD SUMMARY
    // ======================================
    
    
    public function dashboard_summary()
    {
    
        // Get date range (GET params)
        $start_date = $this->input->get('start_date');
        $end_date   = $this->input->get('end_date');
    
        $this->db->select('COUNT(id) as total_count, SUM(total) as total_amount');
        $this->db->from('sales');
        $this->db->where('store_id', 1);
    
        // Apply date range filter only if both dates exist
        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('DATE(date) >=', $start_date);
            $this->db->where('DATE(date) <=', $end_date);
        }
    
        $summary = $this->db->get()->row();
    
        $total_count  = $summary ? (int) $summary->total_count : 0;
        $total_amount = $summary ? (float) $summary->total_amount : 0;
    
        return $this->_json(true, 'Sales dashboard summary', [
            'total_count'  => $total_count,
            'total_amount' => $total_amount
        ]);
    }

    
    // ======================================
    // MONTHLY SALES CHART (LAST 12 MONTHS)
    // ======================================
    public function monthly_summary()
    {
    
        $this->db->select("
            DATE_FORMAT(date, '%Y-%m') as month,
            SUM(total) as total_amount
        ");
        $this->db->from('sales');
        $this->db->where('store_id', 1);
        $this->db->where('date >=', date('Y-m-01', strtotime('-11 months')));
        $this->db->group_by("DATE_FORMAT(date, '%Y-%m')");
        $this->db->order_by('month', 'ASC');
    
        $rows = $this->db->get()->result();
    
        $labels = [];
        $data   = [];
    
        foreach ($rows as $row) {
            $labels[] = $row->month;                  // 2025-01
            $data[]   = (float) $row->total_amount;
        }
    
        $this->_json(true, 'Monthly sales summary', [
            'labels' => $labels,
            'data'   => $data
        ]);
    }
    
    // ======================================
    // SALES BY CUSTOMER SUMMARY
    // ======================================
    public function by_customer_summary()
    {
    
        // Get date range
        $start_date = $this->input->get('start_date');
        $end_date   = $this->input->get('end_date');
    
        $this->db->select('
            customers.id,
            customers.name as customer_name,
            COUNT(tec_sales.id) as sale_count,
            SUM(tec_sales.total) as total_amount
        ');
        $this->db->from('sales');
        $this->db->join('customers', 'customers.id = sales.customer_id', 'left');
        $this->db->where('sales.store_id', 1);
    
        // Date range filter
        if (!empty($start_date)) {
            $this->db->where('sales.date >=', $start_date . ' 00:00:00');
        }
    
        if (!empty($end_date)) {
            $this->db->where('sales.date <=', $end_date . ' 23:59:59');
        }
    
        $this->db->group_by('customers.id');
        $this->db->order_by('total_amount', 'DESC');
    
        $customers = $this->db->get()->result();
    
        return $this->_json(true, 'Sales by customer summary', $customers);
    }


    public function today_summary()
    {
    
        $this->db->select('
            COUNT(id) as total_count,
            SUM(total) as total_amount
        ');
        $this->db->from('sales');
        $this->db->where('store_id', 1);
        $this->db->where('DATE(date)', date('Y-m-d'));
    
        $summary = $this->db->get()->row();
    
        $this->_json(true, 'Today sales summary', [
            'total_count'  => (int) ($summary->total_count ?? 0),
            'total_amount' => (float) ($summary->total_amount ?? 0)
        ]);
    }

    // ======================================
    // SALES BY PRODUCT SUMMARY
    // ======================================
    public function by_product_summary()
    {
    
        $this->db->select('
            p.id as product_id,
            p.name as product_name,
            p.code as product_code,
            SUM(si.quantity) as total_qty,
            SUM(si.subtotal) as total_amount
        ');
        $this->db->from('sale_items si');
        $this->db->join('sales s', 's.id = si.sale_id', 'left');
        $this->db->join('products p', 'p.id = si.product_id', 'left');
        $this->db->where('s.store_id', 1);
        $this->db->where('DATE(date)', date('Y-m-d'));
        $this->db->group_by('p.id');
        $this->db->order_by('total_amount', 'DESC');
        $this->db->limit(100); // Top 10 selling products
    
        $products = $this->db->get()->result();
    
        $this->_json(true, 'Sales by product summary', $products);
    }
    
    public function all_product_summary()
    {
    
        // Get date range (GET params)
        $start_date = $this->input->get('start_date');
        $end_date   = $this->input->get('end_date');
    
        // Default to today if not provided
        if (!$start_date) {
            $start_date = date('Y-m-d');
        }
        if (!$end_date) {
            $end_date = date('Y-m-d');
        }
    
        $this->db->select('
            p.id as product_id,
            p.name as product_name,
            p.code as product_code,
            SUM(si.quantity) as total_qty,
            SUM(si.subtotal) as total_amount
        ');
        $this->db->from('sale_items si');
        $this->db->join('sales s', 's.id = si.sale_id', 'left');
        $this->db->join('products p', 'p.id = si.product_id', 'left');
    
        $this->db->where('s.store_id', 1);
    
        // 🔥 Date range filter
        $this->db->where('DATE(s.date) >=', $start_date);
        $this->db->where('DATE(s.date) <=', $end_date);
    
        $this->db->group_by('p.id');
        $this->db->order_by('total_amount', 'DESC');
        $this->db->limit(100);
    
        $products = $this->db->get()->result();
    
        $this->_json(true, 'Sales by product summary', $products);
    }

    /**
     * Financial cards shown in the mobile dashboard.
     *
     * Receivable is unpaid sales, and payable is unpaid purchases. Sales,
     * purchases and expenses follow the selected date range; outstanding
     * balances remain all-time until the invoice is fully paid.
     */
    public function daily_financials()
    {
        $store_id = (int) ($this->current_user->store_id ?? 1);
        $start_date = $this->input->get('start_date') ?: date('Y-m-d');
        $end_date   = $this->input->get('end_date') ?: $start_date;

        if ($start_date > $end_date) {
            [$start_date, $end_date] = [$end_date, $start_date];
        }

        // Sales payment status is calculated from grand_total in Sales_model.
        // Fall back to total for legacy rows where grand_total is empty or zero.
        $sale_total_sql = 'COALESCE(NULLIF(grand_total, 0), total, 0)';
        $sale_outstanding_sql = "CASE
            WHEN {$sale_total_sql} > COALESCE(paid, 0)
            THEN {$sale_total_sql} - COALESCE(paid, 0)
            ELSE 0
        END";

        $purchase_total_sql = 'COALESCE(total, 0)';
        $purchase_outstanding_sql = "CASE
            WHEN {$purchase_total_sql} > COALESCE(paid, 0)
            THEN {$purchase_total_sql} - COALESCE(paid, 0)
            ELSE 0
        END";

        $this->db->select("
            COUNT(id) AS total_count,
            COALESCE(SUM({$sale_total_sql}), 0) AS total_amount,
            COALESCE(SUM({$sale_outstanding_sql}), 0) AS outstanding_amount
        ", false);
        $this->db->from('sales');
        $this->db->where('store_id', $store_id);
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->where('sale_type', 0);
        $this->db->where('DATE(date) >=', $start_date);
        $this->db->where('DATE(date) <=', $end_date);
        $sales = $this->db->get()->row();

        $this->db->select("
            COUNT(id) AS total_count,
            COALESCE(SUM({$purchase_total_sql}), 0) AS total_amount,
            COALESCE(SUM({$purchase_outstanding_sql}), 0) AS outstanding_amount
        ", false);
        $this->db->from('purchases');
        $this->db->where('store_id', $store_id);
        $this->db->where('DATE(date) >=', $start_date);
        $this->db->where('DATE(date) <=', $end_date);
        $purchases = $this->db->get()->row();

        $this->db->select('
            COUNT(id) AS total_count,
            COALESCE(SUM(total), 0) AS total_amount
        ', false);
        $this->db->from('tec_expenses');
        $this->db->where('store_id', $store_id);
        $this->db->where('date >=', $start_date . ' 00:00:00');
        $this->db->where('date <=', $end_date . ' 23:59:59');
        $expenses = $this->db->get()->row();

        // Outstanding balances are not limited to the selected dashboard
        // dates. A due/partial invoice remains payable/receivable until it
        // is fully paid, even when it was created on an earlier date.
        $this->db->select("
            COALESCE(SUM(CASE
                WHEN {$sale_total_sql} > COALESCE(paid, 0) THEN 1
                ELSE 0
            END), 0) AS total_count,
            COALESCE(SUM({$sale_outstanding_sql}), 0) AS outstanding_amount
        ", false);
        $this->db->from('sales');
        $this->db->where('store_id', $store_id);
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->where('sale_type', 0);
        $sales_outstanding = $this->db->get()->row();

        $this->db->select("
            COALESCE(SUM(CASE
                WHEN {$purchase_total_sql} > COALESCE(paid, 0) THEN 1
                ELSE 0
            END), 0) AS total_count,
            COALESCE(SUM({$purchase_outstanding_sql}), 0) AS outstanding_amount
        ", false);
        $this->db->from('purchases');
        $this->db->where('store_id', $store_id);
        $purchases_outstanding = $this->db->get()->row();

        $sales_amount = (float) ($sales->total_amount ?? 0);
        $purchase_amount = (float) ($purchases->total_amount ?? 0);
        $expense_amount = (float) ($expenses->total_amount ?? 0);

        return $this->_json(true, 'Daily financial summary', [
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'sales' => [
                'total_count'  => (int) ($sales->total_count ?? 0),
                'total_amount' => $sales_amount,
            ],
            'purchases' => [
                'total_count'  => (int) ($purchases->total_count ?? 0),
                'total_amount' => $purchase_amount,
            ],
            'expenses' => [
                'total_count'  => (int) ($expenses->total_count ?? 0),
                'total_amount' => $expense_amount,
            ],
            'payable' => [
                'total_count'  => (int) ($purchases_outstanding->total_count ?? 0),
                'total_amount' => (float) ($purchases_outstanding->outstanding_amount ?? 0),
            ],
            'receivable' => [
                'total_count'  => (int) ($sales_outstanding->total_count ?? 0),
                'total_amount' => (float) ($sales_outstanding->outstanding_amount ?? 0),
            ],
            'profit' => [
                'total_amount' => $sales_amount - $purchase_amount - $expense_amount,
            ],
        ]);
    }

    /**
     * Return all unpaid sales and purchases, independent of the dashboard
     * date filter. A due/partial invoice remains outstanding until paid.
     */
    public function outstanding_summary()
    {
        $store_id = (int) ($this->current_user->store_id ?? 1);

        $sale_total_sql = 'COALESCE(NULLIF(grand_total, 0), total, 0)';
        $sale_outstanding_sql = "CASE
            WHEN {$sale_total_sql} > COALESCE(paid, 0)
            THEN {$sale_total_sql} - COALESCE(paid, 0)
            ELSE 0
        END";

        $purchase_total_sql = 'COALESCE(total, 0)';
        $purchase_outstanding_sql = "CASE
            WHEN {$purchase_total_sql} > COALESCE(paid, 0)
            THEN {$purchase_total_sql} - COALESCE(paid, 0)
            ELSE 0
        END";

        $this->db->select("
            COALESCE(SUM(CASE
                WHEN {$purchase_total_sql} > COALESCE(paid, 0) THEN 1
                ELSE 0
            END), 0) AS total_count,
            COALESCE(SUM({$purchase_outstanding_sql}), 0) AS total_amount
        ", false);
        $this->db->from('purchases');
        $this->db->where('store_id', $store_id);
        $purchases = $this->db->get()->row();

        $this->db->select("
            COALESCE(SUM(CASE
                WHEN {$sale_total_sql} > COALESCE(paid, 0) THEN 1
                ELSE 0
            END), 0) AS total_count,
            COALESCE(SUM({$sale_outstanding_sql}), 0) AS total_amount
        ", false);
        $this->db->from('sales');
        $this->db->where('store_id', $store_id);
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->where('sale_type', 0);
        $sales = $this->db->get()->row();

        return $this->_json(true, 'Outstanding summary', [
            'payable' => [
                'total_count' => (int) ($purchases->total_count ?? 0),
                'total_amount' => (float) ($purchases->total_amount ?? 0),
            ],
            'receivable' => [
                'total_count' => (int) ($sales->total_count ?? 0),
                'total_amount' => (float) ($sales->total_amount ?? 0),
            ],
        ]);
    }

}
