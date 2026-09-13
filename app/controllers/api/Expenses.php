<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Expenses extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->verify_token();
    }

    // ======================================
    // GET EXPENSE LIST
    // ======================================
       public function index()
    {
        $store_id =  1;
    
        $start_date = $this->input->get('start_date');
        $end_date   = $this->input->get('end_date');
    
        $this->db->select('*');
        $this->db->from('tec_expenses');
        
        // Store filter
        $this->db->where('store_id', $store_id);
    
    
        // Date range filter
        if (!empty($start_date)) {
            $this->db->where('date >=', $start_date . ' 00:00:00');
        }
    
        if (!empty($end_date)) {
            $this->db->where('date <=', $end_date . ' 23:59:59');
        }
    
        // Default to today if no range provided
        if (empty($start_date) && empty($end_date)) {
            $today = date('Y-m-d');
            $this->db->where('date >=', $today . ' 00:00:00');
            $this->db->where('date <=', $today . ' 23:59:59');
        }
    
        $this->db->order_by('id', 'DESC');
    
        $expenses = $this->db->get()->result();
    
        $this->_json(true, 'Expenses list', $expenses);
    }

    // ======================================
    // CREATE EXPENSE
    // ======================================
    public function store()
    {
        log_message('debug', 'Expenses API HIT');

        $store_id =  1;
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['amount'])) {
            $this->_json(false, 'Amount is required');
            return;
        }

        $quantity = $input['quantity'] ?? 1;
        $amount   = $input['amount'];
        $total    = $amount * $quantity;

        $currency      = $input['currency'] ?? 'MMK';
        $exchange_rate = $input['exchange_rate'] ?? 1;
        $amount_base   = $amount * $exchange_rate;

        $expenseData = [
            'type_id'        => $input['type_id'] ?? null,
            'container_box' => $input['container_box'] ?? null,
            'date'          => date('Y-m-d H:i:s'),
            'reference'     => $input['reference'] ?? 'EX-' . date('YmdHis'),
            'amount'        => $amount,
            'quantity'      => $quantity,
            'total'         => $total,
            'currency'      => $currency,
            'exchange_rate' => $exchange_rate,
            'amount_base'   => $amount_base,
            'note'          => $input['note'] ?? null,
            'created_by'    => $user->id,
            'store_id'      => $user->store_id ?? 1,
            'attachment'    => $input['attachment'] ?? null
        ];

        $this->db->insert('tec_expenses', $expenseData);
        $expense_id = $this->db->insert_id();

        $this->_json(true, 'Expense created successfully', [
            'expense_id' => $expense_id,
            'reference'  => $expenseData['reference'],
            'total'      => $total
        ]);
    }

    // ======================================
    // DELETE EXPENSE (HARD DELETE)
    // ======================================
    public function delete($id = null)
    {
        $store_id =  1;

        if (!$id) {
            $this->_json(false, 'Expense ID is required');
            return;
        }

        $expense = $this->db->where('id', $id)
                            ->get('tec_expenses')
                            ->row();

        if (!$expense) {
            $this->_json(false, 'Expense not found');
            return;
        }

        $this->db->where('id', $id)->delete('tec_expenses');

        $this->_json(true, 'Expense deleted');
    }
    
    // ======================================
    // GET EXPENSE TYPES LIST
    // ======================================
    public function expensetype()
    {
        $store_id =  1;

        $this->db->select('id, code, name');
        $this->db->from('tec_expensetype');
        $this->db->order_by('name', 'ASC');

        $types = $this->db->get()->result();

        $this->_json(true, 'Expense types list', $types);
    }
    
    // ======================================
    // EXPENSE DASHBOARD SUMMARY
    // ======================================
    public function dashboard_summary()
    {
        $store_id =  1;
    
        // Get date range
        $start_date = $this->input->get('start_date');
        $end_date   = $this->input->get('end_date');
    
        $this->db->select('
            COUNT(id) as total_count,
            SUM(total) as total_amount,
            SUM(amount_base) as total_amount_base
        ');
        $this->db->from('tec_expenses');
        $this->db->where('store_id', $store_id);
    
        // Date filtering (better for index performance)
        if (!empty($start_date)) {
            $this->db->where('date >=', $start_date . ' 00:00:00');
        }
    
        if (!empty($end_date)) {
            $this->db->where('date <=', $end_date . ' 23:59:59');
        }
    
        $summary = $this->db->get()->row();
    
        $total_count       = $summary ? (int) $summary->total_count : 0;
        $total_amount      = $summary ? (float) $summary->total_amount : 0;
        $total_amount_base = $summary ? (float) $summary->total_amount_base : 0;
    
        return $this->_json(true, 'Expense dashboard summary', [
            'total_count'       => $total_count,
            'total_amount'      => $total_amount,
            'total_amount_base' => $total_amount_base,
        ]);
    }

    
    public function dashboard_today()
    {
        $store_id =  1;
    
        $today = date('Y-m-d');
    
        $this->db->select('
            COUNT(id) as total_count,
            SUM(total) as total_amount
        ');
        $this->db->from('tec_expenses');
        $this->db->where('store_id', $store_id);
        $this->db->where('DATE(date)', $today);
    
        $summary = $this->db->get()->row();
    
        $this->_json(true, 'Today expense summary', [
            'total_count'  => (int) ($summary->total_count ?? 0),
            'total_amount' => (float) ($summary->total_amount ?? 0),
        ]);
    }
    
    public function purchase_today()
    {
        $store_id =  1;
    
        $today = date('Y-m-d');
    
        $this->db->select('
            COUNT(id) as total_count,
            SUM(amount) as total_amount
        ');
        $this->db->from('tec_ppayments');
        $this->db->where('store_id', $store_id);
        $this->db->where('DATE(date)', $today);
    
        $summary = $this->db->get()->row();
    
        $this->_json(true, 'Today Purchase summary', [
            'total_count'  => (int) ($summary->total_count ?? 0),
            'total_amount' => (float) ($summary->total_amount ?? 0),
        ]);
    }

    // ======================================
    // MONTHLY EXPENSE CHART (LAST 12 MONTHS)
    // ======================================
    public function monthly_summary()
    {
        $store_id =  1;
    
        $this->db->select("
            DATE_FORMAT(date, '%Y-%m') as month,
            SUM(total) as total_amount
        ");
        $this->db->from('tec_expenses');
        $this->db->where('store_id', $store_id);
        $this->db->where('date >=', date('Y-m-01', strtotime('-11 months')));
        $this->db->group_by("DATE_FORMAT(date, '%Y-%m')");
        $this->db->order_by('month', 'ASC');
    
        $rows = $this->db->get()->result();
    
        // Format for chart
        $labels = [];
        $data   = [];
    
        foreach ($rows as $row) {
            $labels[] = $row->month;              // 2025-01
            $data[]   = (float) $row->total_amount;
        }
    
        $this->_json(true, 'Monthly expense summary', [
            'labels' => $labels,
            'data'   => $data
        ]);
    }

    // ======================================
    // EXPENSE BY TYPE SUMMARY
    // ======================================
    public function by_type_summary()
    {
        $store_id =  1;
    
        // Get date range
        $start_date = $this->input->get('start_date');
        $end_date   = $this->input->get('end_date');
    
        $this->db->select('
            et.id,
            et.name as type_name,
            COUNT(e.id) as expense_count,
            SUM(e.total) as total_amount
        ');
        $this->db->from('tec_expenses e');
        $this->db->join('tec_expensetype et', 'et.id = e.type_id', 'left');
        $this->db->where('e.store_id', $store_id);
    
    
        // Date range filter
        if (!empty($start_date)) {
            $this->db->where('e.date >=', $start_date . ' 00:00:00');
        }
    
        if (!empty($end_date)) {
            $this->db->where('e.date <=', $end_date . ' 23:59:59');
        }
    
        $this->db->group_by('et.id');
        $this->db->order_by('total_amount', 'DESC');
    
        $types = $this->db->get()->result();
    
        // Clean null values
        foreach ($types as &$type) {
            $type->expense_count = (int) $type->expense_count;
            $type->total_amount  = (float) ($type->total_amount ?? 0);
        }
    
        return $this->_json(true, 'Expense by type summary', $types);
    }



}
