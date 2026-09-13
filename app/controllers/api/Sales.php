<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sales extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->verify_token();
    }

    public function index()
    {
        $user = $this->current_user;
        $store_id = (int) ($user->store_id ?? 1);

        $search = $this->input->get('search');
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        // Keep this query compatible with older installations of the ERP
        // database.  The sales table already stores customer_name, so the
        // customers join is not needed for the native list and can cause the
        // endpoint to fail when a tenant has an older customers schema.
        $this->db->select('
            sales.id,
            sales.date,
            sales.customer_name,
            sales.total,
            sales.grand_total,
            sales.paid,
            sales.status
        ');
        $this->db->from('sales');

        $this->db->where('sales.store_id', $store_id);

        // Date logic
        if (!empty($start_date)) {
            $this->db->where('sales.date >=', $start_date . ' 00:00:00');
        }

        if (!empty($end_date)) {
            $this->db->where('sales.date <=', $end_date . ' 23:59:59');
        }

        // Search
        if (!empty($search)) {
            $this->db->group_start()
                ->like('sales.id', $search)
                ->or_like('sales.customer_name', $search)
                ->group_end();
        }

        $this->db->order_by('sales.id', 'DESC');

        $services = $this->db->get()->result();

        return $this->_json(true, 'Sales list', $services);
    }

    // ======================================
    // CREATE NEW SALE (API CHECKOUT)
    // ======================================
    public function add()
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            $user = null;

            // BYPASS SERVER HEADER STRIPPING
            if (isset($input['token']) && !empty($input['token'])) {
                $record = $this->db
                    ->where('token', $input['token'])
                    ->where('expires_at >=', date('Y-m-d H:i:s'))
                    ->get('api_tokens')
                    ->row();

                if ($record) {
                    $user = $this->db->where('id', $record->user_id)->get('users')->row();
                }
            }

            // Fallback to normal header check
            if (!$user && method_exists($this, 'api_user')) {
                $user = $this->api_user();
            }

            if (!$user) {
                echo json_encode(['status' => false, 'message' => 'Invalid or expired token. Please log in again.']);
                return;
            }

            // --- PROCEED WITH CHECKOUT ---
            $customer_id = !empty($input['customer_id']) ? $input['customer_id'] : 1;
            $items = isset($input['items']) ? $input['items'] : [];
            $store_id = isset($user->store_id) ? $user->store_id : 1;

            if (empty($items)) {
                echo json_encode(['status' => false, 'message' => 'No items in cart']);
                return;
            }

            $grand_total = 0;
            $total_items = 0;
            $total_quantity = 0;
            $products = [];

            foreach ($items as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $subtotal = $price * $quantity;

                $grand_total += $subtotal;
                $total_items++;
                $total_quantity += $quantity;

                $product_details = $this->db->get_where('products', ['id' => $product_id])->row();

                // FIXED: Removed the (object) cast. CodeIgniter strictly expects an Array here!
                $products[] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'net_unit_price' => $price,
                    'discount' => '0',
                    'comment' => '',
                    'item_discount' => 0,
                    'tax' => '0%',
                    'item_tax' => 0,
                    'subtotal' => $subtotal,
                    'real_unit_price' => $price,
                    'cost' => $product_details ? $product_details->cost : 0,
                    'product_code' => $product_details ? $product_details->code : '',
                    'product_name' => $product_details ? $product_details->name : 'API Item',
                    'ordered' => 1,
                    'preorder_qty' => 0,
                    'fulfilled_qty' => 0
                ];
            }

            $date = date('Y-m-d H:i:s');
            $customer = $this->db->get_where('customers', ['id' => $customer_id])->row();

            $data = [
                'date' => $date,
                'customer_id' => $customer_id,
                'customer_name' => $customer ? $customer->name : 'Walk-in Customer',
                'total' => $grand_total,
                'product_discount' => 0,
                'order_discount_id' => null,
                'order_discount' => 0,
                'total_discount' => 0,
                'product_tax' => 0,
                'order_tax_id' => null,
                'order_tax' => 0,
                'total_tax' => 0,
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'total_quantity' => $total_quantity,
                'paid' => $grand_total,
                'created_by' => $this->current_user->id,
                'store_id' => $store_id,
                'status' => 'paid',
                'rounding' => 0
            ];

            $payment = [
                'date' => $date,
                'amount' => $grand_total,
                'customer_id' => $customer_id,
                'paid_by' => 'cash',
                'created_by' => $this->current_user->id,
                'store_id' => $store_id,
                'pos_paid' => $grand_total,
                'pos_balance' => 0,
            ];

            $this->load->model('pos_model');

            if ($this->pos_model->addSale($data, $products, $payment)) {
                echo json_encode(['status' => true, 'message' => 'Sale successfully added!']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Database refused to save the sale. Missing required fields.']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['status' => false, 'message' => 'PHP FATAL ERROR: ' . $e->getMessage() . ' on line ' . $e->getLine()]);
        }
    }




    public function search()
    {
        $user = $this->current_user;
        $store_id = (int) ($user->store_id ?? 1);

        $search = $this->input->get('search');

        $this->db->select('
            sales.id, sales.date, sales.customer_name, sales.total, sales.grand_total,
            sales.paid, sales.status
        ');
        $this->db->from('sales');
        $this->db->where('sales.store_id', $store_id);

        if ($search) {
            $this->db->group_start()
                ->like('sales.id', $search)
                ->or_like('sales.customer_name', $search)
                ->group_end();
        }

        $services = $this->db->get()->result();

        echo json_encode(['status' => true, 'message' => 'Today sales list', 'data' => $services]);
    }


    public function view($id = null)
    {
        $user = $this->current_user;
        $store_id = (int) ($user->store_id ?? 1);

        if (!$id) {
            echo json_encode(['status' => false, 'message' => 'Sale ID is required']);
            return;
        }

        // Read the stored snapshot directly.  This avoids making the native
        // detail endpoint depend on the optional customer-table join.
        $this->db->select('sales.*');
        $this->db->from('sales');
        $this->db->where('sales.id', $id);
        $this->db->where('sales.store_id', $store_id);
        $sale = $this->db->get()->row();

        if (!$sale) {
            echo json_encode(['status' => false, 'message' => 'Sale not found']);
            return;
        }

        // The website receipt displays the user who created the sale and the
        // customer's phone. Keep these lookups separate from the sale query so
        // this endpoint remains compatible with older tenant schemas where a
        // customer join can fail because of a missing optional column.
        if (!empty($sale->created_by)) {
            $created_by = $this->db
                ->select('first_name, last_name')
                ->where('id', (int) $sale->created_by)
                ->get('users')
                ->row();

            if ($created_by) {
                $sale->biller_name = trim(
                    (string) ($created_by->first_name ?? '') . ' ' .
                    (string) ($created_by->last_name ?? '')
                );
            }
        }

        if (!empty($sale->customer_id)) {
            $customer = $this->db
                ->select('phone')
                ->where('id', (int) $sale->customer_id)
                ->get('customers')
                ->row();

            if ($customer) {
                $sale->customer_phone = $customer->phone ?? '';
            }
        }

        $this->db->select('sale_items.*, products.name as product_name, products.code as product_code,
            products.base_unit_id, products.secondary_unit_id, products.is_dual_unit,
            selected_units.name as unit_name');
        $this->db->from('sale_items');
        $this->db->join('products', 'products.id = sale_items.product_id', 'left');
        $this->db->join('product_units selected_units', 'selected_units.id = sale_items.unit_id', 'left');
        $this->db->where('sale_items.sale_id', $id);
        $items = $this->db->get()->result();

        $payments = $this->db->where('sale_id', $id)->get('payments')->result();

        $data = [
            'sale' => $sale,
            'items' => $items,
            'payments' => $payments
        ];

        echo json_encode(['status' => true, 'message' => 'Sale detail', 'data' => $data]);
    }

    /**
     * Update an existing sale from the native POS.
     *
     * The website normally posts a large HTML form to Pos::index(). The
     * mobile POS sends JSON instead, so this endpoint translates that JSON to
     * the same sale/item/payment structures used by Pos_model::updateSale().
     */
    public function update($id = null)
    {
        $user = $this->current_user;
        $store_id = (int) ($user->store_id ?? 1);
        $sale_id = (int) $id;

        if ($sale_id <= 0) {
            $this->_json(false, 'Sale ID is required');
            return;
        }

        $sale = $this->db
            ->where('id', $sale_id)
            ->where('store_id', $store_id)
            ->get('sales')
            ->row();

        if (!$sale) {
            $this->_json(false, 'Sale not found');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = [];
        }

        $request_items = isset($input['items']) && is_array($input['items'])
            ? $input['items']
            : [];

        if (empty($request_items)) {
            $this->_json(false, 'No sale items');
            return;
        }

        $items = [];
        $subtotal = 0.0;
        $total_quantity = 0.0;
        $is_preorder = (int) ($sale->is_preorder ?? 0);

        foreach ($request_items as $request_item) {
            if (!is_array($request_item)) {
                $this->_json(false, 'Invalid sale item');
                return;
            }

            $product_id = (int) ($request_item['product_id'] ?? 0);
            $quantity = (float) ($request_item['qty'] ?? $request_item['quantity'] ?? 0);
            $price = (float) ($request_item['price'] ?? $request_item['unit_price'] ?? 0);
            $unit_id = (int) ($request_item['unit_id'] ?? 0);

            if ($product_id <= 0 || $quantity <= 0 || $price < 0) {
                $this->_json(false, 'Invalid sale quantity or price');
                return;
            }

            $product = $this->site->getProductByID($product_id, $store_id);
            if (!$product) {
                $product = $this->db->where('id', $product_id)->get('products')->row();
            }

            if (!$product) {
                $this->_json(false, 'Product not found');
                return;
            }

            $base_unit_id = (int) ($product->base_unit_id ?? 0);
            $secondary_unit_id = (int) ($product->secondary_unit_id ?? 0);
            if ($unit_id <= 0) {
                $unit_id = $base_unit_id;
            }

            $valid_unit = $unit_id <= 0 || $unit_id === $base_unit_id || $unit_id === $secondary_unit_id;
            if (!$valid_unit && $unit_id > 0) {
                $valid_unit = $this->db
                    ->where('product_id', $product_id)
                    ->where('unit_id', $unit_id)
                    ->count_all_results('product_unit_conversions') > 0;
            }

            if (!$valid_unit) {
                $this->_json(false, 'Invalid product unit');
                return;
            }

            $is_dual_product = (int) ($product->is_dual_unit ?? 0) === 1;
            $has_explicit_dual_qty = $is_dual_product &&
                (array_key_exists('primary_qty', $request_item) || array_key_exists('secondary_qty', $request_item));
            $primary_qty = $has_explicit_dual_qty ? (float) ($request_item['primary_qty'] ?? 0) : 0;
            $secondary_qty = $has_explicit_dual_qty ? (float) ($request_item['secondary_qty'] ?? 0) : 0;

            if ($has_explicit_dual_qty && ($primary_qty <= 0 || $secondary_qty <= 0)) {
                $this->_json(false, 'Both dual-unit quantities are required');
                return;
            }

            $line_subtotal = $quantity * $price;
            $item_data = [
                'product_id' => $product_id,
                'product_code' => $product->code ?? '',
                'product_name' => $product->name ?? 'Product',
                'quantity' => $is_preorder ? 0 : $quantity,
                'qty_base' => $is_dual_product ? max(0, $primary_qty) : 0,
                'qty_secondary' => $is_dual_product ? max(0, $secondary_qty) : 0,
                'preorder_qty' => $is_preorder ? $quantity : 0,
                'fulfilled_qty' => 0,
                'unit_id' => $unit_id,
                'unit_price' => $price,
                'net_unit_price' => $price,
                'real_unit_price' => $price,
                'discount' => '0',
                'comment' => '',
                'item_discount' => 0,
                'tax' => '0%',
                'item_tax' => 0,
                'subtotal' => $line_subtotal,
                'cost' => $product->cost ?? 0,
            ];

            // These two values are request-only values. Pos_model removes them
            // before inserting sale_items and uses them for dual stock logic.
            if ($has_explicit_dual_qty) {
                $item_data['primary_qty'] = $primary_qty;
                $item_data['secondary_qty'] = $secondary_qty;
            }

            $items[] = $item_data;

            $subtotal += $line_subtotal;
            $total_quantity += $quantity;
        }

        $discount_value = max(0, (float) ($input['discount_value'] ?? 0));
        $discount_type = strtolower((string) ($input['discount_type'] ?? 'percent')) === 'fixed'
            ? 'fixed'
            : 'percent';
        $order_discount = $discount_type === 'percent'
            ? min($subtotal, $subtotal * ($discount_value / 100))
            : min($subtotal, $discount_value);

        $taxable_subtotal = max($subtotal - $order_discount, 0);
        $tax_value = max(0, (float) ($input['tax_value'] ?? 0));
        $tax_type = strtolower((string) ($input['tax_type'] ?? 'percent')) === 'fixed'
            ? 'fixed'
            : 'percent';
        $order_tax = $tax_type === 'percent'
            ? $taxable_subtotal * ($tax_value / 100)
            : $tax_value;
        $grand_total = max($taxable_subtotal + $order_tax, 0);

        $customer_id = (int) ($input['customer_id'] ?? $sale->customer_id ?? 1);
        $customer = $this->db->where('id', $customer_id)->get('customers')->row();
        $customer_name = $customer
            ? $customer->name
            : ($sale->customer_name ?? 'Walk-in Customer');

        $requested_paid = array_key_exists('paid', $input)
            ? max(0, (float) $input['paid'])
            : max(0, (float) ($sale->paid ?? 0));
        $paid = min($requested_paid, $grand_total);
        $status = $paid >= $grand_total
            ? 'paid'
            : ($paid > 0 ? 'partial' : 'due');

        $discount_id = $order_discount > 0
            ? ($discount_type === 'percent' ? $discount_value . '%' : (string) $order_discount)
            : null;
        $tax_id = $order_tax > 0
            ? ($tax_type === 'percent' ? $tax_value . '%' : (string) $order_tax)
            : null;

        $sale_data = [
            'date' => $sale->date,
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'total' => $subtotal,
            'product_discount' => 0,
            'order_discount_id' => $discount_id,
            'order_discount' => $order_discount,
            'total_discount' => $order_discount,
            'product_tax' => 0,
            'order_tax_id' => $tax_id,
            'order_tax' => $order_tax,
            'total_tax' => $order_tax,
            'grand_total' => $grand_total,
            'total_items' => count($items),
            'total_quantity' => $total_quantity,
            'rounding' => 0,
            'paid' => $paid,
            'status' => $status,
            'note' => array_key_exists('note', $input) ? (string) $input['note'] : ($sale->note ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user->id,
            'is_preorder' => $is_preorder,
        ];

        $old_payment = $this->db
            ->where('sale_id', $sale_id)
            ->order_by('id', 'ASC')
            ->get('payments')
            ->row();
        $payment_method = trim((string) ($input['payment_method'] ?? ($old_payment->paid_by ?? 'cash')));

        $payment = [];
        if ($paid > 0) {
            $received = array_key_exists('cash_received', $input)
                ? max(0, (float) $input['cash_received'])
                : $paid;
            $pos_paid = array_key_exists('pos_paid', $input)
                ? max(0, (float) $input['pos_paid'])
                : $received;
            $pos_balance = array_key_exists('pos_balance', $input)
                ? max(0, (float) $input['pos_balance'])
                : max($grand_total - $paid, 0);

            $payment = [
                'date' => date('Y-m-d H:i:s'),
                'amount' => $paid,
                'customer_id' => $customer_id,
                'paid_by' => $payment_method ?: 'cash',
                'created_by' => $user->id,
                'store_id' => $store_id,
                'note' => (string) ($input['payment_note'] ?? ''),
                'pos_paid' => $pos_paid,
                'pos_balance' => $pos_balance,
            ];
        }

        $this->load->model('pos_model');
        $updated = $this->pos_model->updateSale($sale_id, $sale_data, $items, $payment);

        if (!$updated) {
            $this->_json(false, 'Unable to update sale');
            return;
        }

        $this->_json(true, 'Sale updated successfully', [
            'sale_id' => $sale_id,
            'total' => $grand_total,
            'paid' => $paid,
            'status' => $status,
        ]);
    }

    public function delete($id = null)
    {
        $user = $this->current_user;
        $store_id = (int) ($user->store_id ?? 1);

        if (!$id) {
            echo json_encode(['status' => false, 'message' => 'Sale ID is required']);
            return;
        }

        // This database version does not have the newer deleted_at/deleted_by
        // columns. Reuse the website's ERP deletion workflow instead of
        // attempting a schema-dependent soft delete.
        $sale = $this->db->where('id', (int) $id)
            ->where('store_id', $store_id)
            ->get('sales')
            ->row();

        if (!$sale) {
            echo json_encode(['status' => false, 'message' => 'Sale not found or already deleted']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $reason = is_array($input) && !empty($input['reason'])
            ? trim((string) $input['reason'])
            : 'Deleted from native sales list';

        $this->load->model('sales_model');
        $result = $this->sales_model->deleteInvoiceERP((int) $id, [
            'reason' => $reason,
            'user_id' => (int) $user->id,
            'store_id' => $store_id,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => (string) $this->input->user_agent(),
            'action' => 'delete_sale_from_native_sales_list',
        ]);

        $this->_json(
            !empty($result['success']),
            $result['message'] ?? ($result['success'] ? 'Sale deleted' : 'Unable to delete sale'),
            $result
        );
    }

    public function dashboard_summary()
    {
        $user = $this->api_user();
        $store_id = $user->store_id ?? 1;

        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        $this->db->select('COUNT(id) as total_count, SUM(total) as total_amount');
        $this->db->from('sales');
        $this->db->where('store_id', $store_id);
        $this->db->where('deleted_at', null);
        $this->db->where('sales.sale_type', 0);

        if (!empty($start_date) && !empty($end_date)) {
            $this->db->where('DATE(date) >=', $start_date);
            $this->db->where('DATE(date) <=', $end_date);
        }

        $summary = $this->db->get()->row();

        $total_count = $summary ? (int) $summary->total_count : 0;
        $total_amount = $summary ? (float) $summary->total_amount : 0;

        echo json_encode([
            'status' => true,
            'message' => 'Sales dashboard summary',
            'data' => ['total_count' => $total_count, 'total_amount' => $total_amount]
        ]);
    }

    public function monthly_summary()
    {
        $user = $this->api_user();
        $store_id = $user->store_id ?? 1;

        $this->db->select("DATE_FORMAT(date, '%Y-%m') as month, SUM(total) as total_amount");
        $this->db->from('sales');
        $this->db->where('store_id', $store_id);
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->where('sales.sale_type', 0);
        $this->db->where('date >=', date('Y-m-01', strtotime('-11 months')));
        $this->db->group_by("DATE_FORMAT(date, '%Y-%m')");
        $this->db->order_by('month', 'ASC');

        $rows = $this->db->get()->result();

        $labels = [];
        $data = [];

        foreach ($rows as $row) {
            $labels[] = $row->month;
            $data[] = (float) $row->total_amount;
        }

        echo json_encode(['status' => true, 'message' => 'Monthly sales summary', 'data' => ['labels' => $labels, 'data' => $data]]);
    }

    public function by_customer_summary()
    {
        $user = $this->api_user();
        $store_id = $user->store_id ?? 1;

        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        $this->db->select('customers.id, customers.name as customer_name, COUNT(tec_sales.id) as sale_count, SUM(tec_sales.total) as total_amount');
        $this->db->from('sales');
        $this->db->join('customers', 'customers.id = sales.customer_id', 'left');
        $this->db->where('sales.store_id', $store_id);
        $this->db->where('sales.deleted_at', null);
        $this->db->where('sales.sale_type', 0);

        if (!empty($start_date)) {
            $this->db->where('sales.date >=', $start_date . ' 00:00:00');
        }

        if (!empty($end_date)) {
            $this->db->where('sales.date <=', $end_date . ' 23:59:59');
        }

        $this->db->group_by('customers.id');
        $this->db->order_by('total_amount', 'DESC');

        $customers = $this->db->get()->result();

        echo json_encode(['status' => true, 'message' => 'Sales by customer summary', 'data' => $customers]);
    }

    public function today_summary()
    {
        $user = $this->api_user();
        $store_id = $user->store_id ?? 1;

        $this->db->select('COUNT(id) as total_count, SUM(total) as total_amount');
        $this->db->from('sales');
        $this->db->where('store_id', $store_id);
        $this->db->where('DATE(date)', date('Y-m-d'));
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->where('sales.sale_type', 0);

        $summary = $this->db->get()->row();

        echo json_encode([
            'status' => true,
            'message' => 'Today sales summary',
            'data' => ['total_count' => (int) ($summary->total_count ?? 0), 'total_amount' => (float) ($summary->total_amount ?? 0)]
        ]);
    }

    public function by_product_summary()
    {
        $user = $this->api_user();
        $store_id = $user->store_id ?? 1;

        $this->db->select('p.id as product_id, p.name as product_name, p.code as product_code, SUM(si.quantity) as total_qty, SUM(si.subtotal) as total_amount');
        $this->db->from('sale_items si');
        $this->db->join('sales s', 's.id = si.sale_id', 'left');
        $this->db->join('products p', 'p.id = si.product_id', 'left');
        $this->db->where('s.store_id', $store_id);
        $this->db->where('s.deleted_at IS NULL', null, false);
        $this->db->where('sales.sale_type', 0);
        $this->db->where('DATE(date)', date('Y-m-d'));
        $this->db->group_by('p.id');
        $this->db->order_by('total_amount', 'DESC');
        $this->db->limit(100);

        $products = $this->db->get()->result();

        echo json_encode(['status' => true, 'message' => 'Sales by product summary', 'data' => $products]);
    }

    public function all_product_summary()
    {
        $user = $this->api_user();
        $store_id = $user->store_id ?? 1;

        $start_date = $this->input->get('start_date') ?: date('Y-m-d');
        $end_date = $this->input->get('end_date') ?: date('Y-m-d');

        $this->db->select('p.id as product_id, p.name as product_name, p.code as product_code, SUM(si.quantity) as total_qty, SUM(si.subtotal) as total_amount');
        $this->db->from('sale_items si');
        $this->db->join('sales s', 's.id = si.sale_id', 'left');
        $this->db->join('products p', 'p.id = si.product_id', 'left');
        $this->db->where('s.store_id', $store_id);
        $this->db->where('s.deleted_at IS NULL', null, false);
        $this->db->where('s.sale_type', 0);
        $this->db->where('DATE(s.date) >=', $start_date);
        $this->db->where('DATE(s.date) <=', $end_date);
        $this->db->group_by('p.id');
        $this->db->order_by('total_amount', 'DESC');
        $this->db->limit(100);

        $products = $this->db->get()->result();

        echo json_encode(['status' => true, 'message' => 'Sales by product summary', 'data' => $products]);
    }

    public function store()
    {
        log_message('error', 'STORE API HIT');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = [];
        }

        if (empty($input['items'])) {
            $this->_json(false, 'No sale items');
            return;
        }

        $this->load->model('pos_model');
        $this->db->trans_begin();

        // -----------------------------------
        // BASIC SALE DATA
        // -----------------------------------

        $date = date('Y-m-d H:i:s');

        $reference_no = 'CW-' . date('YmdHis');

        $total = 0;
        $total_quantity = 0;

        foreach ($input['items'] as $item) {
            $quantity = (float) ($item['qty'] ?? $item['quantity'] ?? 0);
            $total += ((float) ($item['price'] ?? $item['unit_price'] ?? 0) * $quantity);
            $total_quantity += $quantity;
        }

        $grand_total = $total;

        $discount_value = max(0, (float) ($input['discount_value'] ?? 0));
        $discount_type = strtolower((string) ($input['discount_type'] ?? 'percent')) === 'fixed'
            ? 'fixed'
            : 'percent';
        $order_discount = $discount_type === 'percent'
            ? min($grand_total, $grand_total * ($discount_value / 100))
            : min($grand_total, $discount_value);
        $taxable_total = max($grand_total - $order_discount, 0);
        $tax_value = max(0, (float) ($input['tax_value'] ?? 0));
        $tax_type = strtolower((string) ($input['tax_type'] ?? 'percent')) === 'fixed'
            ? 'fixed'
            : 'percent';
        $order_tax = $tax_type === 'percent'
            ? $taxable_total * ($tax_value / 100)
            : $tax_value;
        $grand_total = max($taxable_total + $order_tax, 0);

        $paid = isset($input['paid']) ? max(0, (float) $input['paid']) : 0;
        $paid = min($paid, $grand_total);
        $payment_status = $paid >= $grand_total
            ? 'paid'
            : ($paid > 0 ? 'partial' : 'due');

        // -----------------------------------
        // CUSTOMER
        // -----------------------------------

        $customer_id = $input['customer_id'] ?? 1;

        $customer = $this->db
            ->where('id', $customer_id)
            ->get('tec_customers')
            ->row();

        $customer_name = $customer ? $customer->name : 'Walk-in Customer';

        // -----------------------------------
        // SALE INSERT
        // -----------------------------------

        $saleData = [
            'date' => $date,
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'total' => $grand_total + $order_discount - $order_tax,
            'product_discount' => 0,
            'order_discount_id' => $order_discount > 0
                ? ($discount_type === 'percent' ? $discount_value . '%' : (string) $order_discount)
                : null,
            'order_discount' => $order_discount,
            'total_discount' => $order_discount,
            'product_tax' => 0,
            'order_tax_id' => $order_tax > 0
                ? ($tax_type === 'percent' ? $tax_value . '%' : (string) $order_tax)
                : null,
            'order_tax' => $order_tax,
            'total_tax' => $order_tax,
            'grand_total' => $grand_total,
            'total_items' => count($input['items']),
            'total_quantity' => $total_quantity,
            'rounding' => 0,
            'paid' => $paid,
            'status' => $payment_status,
            'created_by' => $this->current_user->id,
            'store_id' => $this->current_user->store_id ?? 1,
            'note' => '(Mobile Sales)',
        ];

        if (!$this->db->insert('tec_sales', $saleData)) {
            $this->db->trans_rollback();
            $this->_json(false, 'Unable to create sale');
            return;
        }

        $sale_id = $this->db->insert_id();

        // -----------------------------------
        // SALE ITEMS
        // -----------------------------------

        foreach ($input['items'] as $item) {

            $product = $this->db
                ->where('id', $item['product_id'])
                ->get('tec_products')
                ->row();

            if (!$product) {
                $this->db->trans_rollback();
                $this->_json(false, 'Product not found');
                return;
            }

            $qty = (float) ($item['qty'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $unit_id = !empty($item['unit_id'])
                ? (int) $item['unit_id']
                : (int) $product->base_unit_id;

            $is_dual_product = (int) $product->is_dual_unit === 1;

            $has_explicit_dual_qty =
                $is_dual_product &&
                (array_key_exists('primary_qty', $item) || array_key_exists('secondary_qty', $item));
            $primary_qty = $has_explicit_dual_qty && is_numeric($item['primary_qty'] ?? null)
                ? max(0, (float) $item['primary_qty'])
                : 0;
            $secondary_qty = $has_explicit_dual_qty && is_numeric($item['secondary_qty'] ?? null)
                ? max(0, (float) $item['secondary_qty'])
                : 0;

            $valid_unit = empty($product->base_unit_id) ||
                $unit_id === (int) $product->base_unit_id ||
                (!empty($product->secondary_unit_id) &&
                    $unit_id === (int) $product->secondary_unit_id);

            // Products can have more than two selectable units. Those extra
            // units are stored in the product conversion table, so accept a
            // conversion unit here before passing it to unit-aware stock
            // deduction.
            if (!$valid_unit && $unit_id > 0) {
                $valid_unit = $this->db
                    ->where('product_id', $product->id)
                    ->where('unit_id', $unit_id)
                    ->count_all_results('tec_product_unit_conversions') > 0;
            }

            if (
                $qty <= 0 || $price < 0 || !$valid_unit ||
                ($has_explicit_dual_qty && ($primary_qty <= 0 || $secondary_qty <= 0))
            ) {
                $this->db->trans_rollback();
                $this->_json(false, 'Invalid sale quantity');
                return;
            }

            $subtotal = $qty * $price;

            $itemData = [
                'sale_id' => $sale_id,
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'quantity' => $qty,
                // Preserve the exact two quantities entered in Mobile POS.
                // The same values continue to be used below for FIFO stock.
                'qty_base' => $is_dual_product ? $primary_qty : 0,
                'qty_secondary' => $is_dual_product ? $secondary_qty : 0,
                'unit_id' => $unit_id,
                'unit_price' => $price,
                'net_unit_price' => $price,
                'real_unit_price' => $price,
                'discount' => '0',
                'comment' => '',
                'item_discount' => 0,
                'tax' => '0%',
                'item_tax' => 0,
                'subtotal' => $subtotal,
                'cost' => $product->cost ?? 0,
                'preorder_qty' => 0,
                'fulfilled_qty' => 0,
            ];

            if (!$this->db->insert('tec_sale_items', $itemData)) {
                $this->db->trans_rollback();
                $this->_json(false, 'Unable to create sale item');
                return;
            }

            $sale_item_id = $this->db->insert_id();

            // -----------------------------------
            // FIFO STOCK DEDUCTION
            // -----------------------------------

            if ($has_explicit_dual_qty && $product->type === 'standard') {
                $stock_ok = $this->pos_model->deduct_stock_dual(
                    $product->id,
                    $primary_qty,
                    $secondary_qty,
                    $sale_id,
                    $sale_item_id,
                    $this->current_user->store_id ?? 1
                );

                if ($stock_ok === false) {
                    $this->db->trans_rollback();
                    $this->_json(false, 'Insufficient stock for sale');
                    return;
                }
            }

            if (!$has_explicit_dual_qty && $product->type === 'standard') {
                $stock_ok = $this->pos_model->deduct_stock(
                    $product->id,
                    $unit_id,
                    $qty,
                    $sale_id,
                    $sale_item_id,
                    $this->current_user->store_id ?? 1
                );

                if ($stock_ok === false) {
                    $this->db->trans_rollback();
                    $this->_json(false, 'Insufficient stock for sale');
                    return;
                }
            }
        }

        // -----------------------------------
        // PAYMENT INSERT
        // -----------------------------------

        if ($paid > 0) {

            $cash_received = array_key_exists('cash_received', $input)
                ? max(0, (float) $input['cash_received'])
                : $paid;
            $pos_paid = array_key_exists('pos_paid', $input)
                ? max(0, (float) $input['pos_paid'])
                : $cash_received;
            $pos_balance = array_key_exists('pos_balance', $input)
                ? max(0, (float) $input['pos_balance'])
                : max($grand_total - $paid, 0);

            $paymentData = [
                'date' => $date,
                'sale_id' => $sale_id,
                'amount' => $paid,
                'paid_by' => $input['payment_method'] ?? 'cash',
                'created_by' => $this->current_user->id,
                'store_id' => $this->current_user->store_id ?? 1,
                'note' => $input['payment_note'] ?? '',
                'pos_paid' => $pos_paid,
                'pos_balance' => $pos_balance,
            ];

            // Attachment upload
            if (!empty($_FILES['attachment']['name'])) {

                $this->load->library('upload');

                $config['upload_path'] = 'files/';
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = 2048;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('attachment')) {

                    $error = $this->upload->display_errors();

                    $this->db->trans_rollback();
                    $this->_json(false, $error);

                    return;
                }

                $photo = $this->upload->file_name;

                $paymentData['attachment'] = $photo;
            }

            $this->db->insert('tec_payments', $paymentData);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->_json(false, 'Unable to complete sale');
            return;
        }

        $this->db->trans_commit();

        // -----------------------------------
        // RESPONSE
        // -----------------------------------

        $this->_json(true, 'Sale created successfully', [
            'sale_id' => $sale_id,
            'reference_no' => $reference_no,
            'total' => $grand_total,
            'paid' => $paid,
            'status' => $payment_status
        ]);
    }
}
