<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function addSale($data, $items, $payment = [], $did = null)
    {
        log_message('debug', '========== START addSale() ==========');

        $this->db->trans_begin();

        $is_preorder_sale = !empty($data['is_preorder']) && (int) $data['is_preorder'] === 1;

        log_message(
            'debug',
            '[SALE TYPE] is_preorder = ' . ($is_preorder_sale ? 'YES' : 'NO')
        );

        // Insert sale record
        if ($this->db->insert('sales', $data)) {

            $sale_id = $this->db->insert_id();
            log_message('debug', "[SALE CREATED] sale_id={$sale_id}");

            foreach ($items as $index => $item) {

                // primary_qty/secondary_qty are request-only aliases used by
                // the existing FIFO stock logic. Persist the exact pair in the
                // dedicated sale_items columns before removing those aliases.
                $explicit_primary_qty = array_key_exists('primary_qty', $item)
                    ? (float) $item['primary_qty']
                    : null;
                $explicit_secondary_qty = array_key_exists('secondary_qty', $item)
                    ? (float) $item['secondary_qty']
                    : null;
                $sale_item = $item;
                $sale_item['qty_base'] = max(
                    0,
                    (float) ($sale_item['qty_base'] ?? $explicit_primary_qty ?? 0)
                );
                $sale_item['qty_secondary'] = max(
                    0,
                    (float) ($sale_item['qty_secondary'] ?? $explicit_secondary_qty ?? 0)
                );
                unset($sale_item['primary_qty'], $sale_item['secondary_qty']);

                log_message(
                    'debug',
                    "[ITEM {$index}] BEFORE qty={$item['quantity']}, product_id={$item['product_id']}, unit_id={$item['unit_id']}"
                );

                $sale_item['sale_id'] = $sale_id;

                // Preorder handling (NO STOCK DEDUCTION)
                if ($is_preorder_sale) {

                    $original_qty = $sale_item['quantity'];

                    $sale_item['preorder_qty'] = (float) $sale_item['quantity'];
                    $sale_item['fulfilled_qty'] = 0;
                    $sale_item['quantity'] = 0;
                    $item['preorder_qty'] = $sale_item['preorder_qty'];

                    log_message(
                        'debug',
                        "[PREORDER ITEM] original_qty={$original_qty} → quantity=0, preorder_qty={$item['preorder_qty']}"
                    );

                } else {

                    $sale_item['preorder_qty'] = 0;
                    $sale_item['fulfilled_qty'] = 0;
                }

                // Insert sale item
                if ($this->db->insert('sale_items', $sale_item)) {

                    $sale_item_id = $this->db->insert_id();
                    log_message(
                        'debug',
                        "[SALE ITEM INSERTED] sale_item_id={$sale_item_id}, product_id={$item['product_id']}"
                    );

                    // Skip stock deduction for preorder
                    if ($is_preorder_sale) {
                        log_message('debug', "[SKIP STOCK] preorder sale → no deduct_stock()");
                        continue;
                    }

                    // Deduct stock only for normal sales
                    if ($item['product_id'] > 0) {

                        if ($product = $this->site->getProductByID($item['product_id'])) {

                            log_message(
                                'debug',
                                "[PRODUCT] id={$product->id}, type={$product->type}"
                            );

                            if ($product->type === 'standard') {

                                log_message(
                                    'debug',
                                    "[CALL deduct_stock] sale_id={$sale_id}, sale_item_id={$sale_item_id}, product_id={$product->id}, unit_id={$sale_item['unit_id']}, qty={$sale_item['quantity']}"
                                );

                                $has_explicit_dual_qty =
                                    !empty($product->secondary_unit_id) &&
                                    ($explicit_primary_qty !== null || $explicit_secondary_qty !== null);

                                if ($has_explicit_dual_qty) {
                                    $stock_result = $this->deduct_stock_dual(
                                        $product->id,
                                        max(0, $explicit_primary_qty ?? 0),
                                        max(0, $explicit_secondary_qty ?? 0),
                                        $sale_id,
                                        $sale_item_id,
                                        $data['store_id'] ?? null
                                    );
                                } else {
                                    $stock_result = $this->deduct_stock(
                                        $product->id,
                                        $sale_item['unit_id'],
                                        $sale_item['quantity'],
                                        $sale_id,
                                        $sale_item_id,
                                        $data['store_id'] ?? null
                                    );
                                }

                                if ($stock_result === false) {
                                    log_message('error', "[STOCK DEDUCTION FAILED] product_id={$product->id}");
                                    $this->db->trans_rollback();
                                    return false;
                                }
                            }
                        }
                    }

                } else {
                    log_message(
                        'error',
                        "[SALE ITEM FAILED] product_id={$item['product_id']}"
                    );
                }
            }

            // Remove suspended sale if exists
            if ($did) {
                log_message('debug', "[SUSPENDED SALE REMOVE] suspend_id={$did}");
                $this->db->delete('suspended_sales', ['id' => $did]);
                $this->db->delete('suspended_items', ['suspend_id' => $did]);
            }

            // Handle payments
            $msg = [];

            if (!$is_preorder_sale && !empty($payment)) {

                log_message(
                    'debug',
                    "[PAYMENT] method={$payment['paid_by']}, amount={$payment['amount']}"
                );

                if ($payment['paid_by'] === 'stripe') {

                    $result = $this->stripe($payment['amount'], [
                        'number' => $payment['cc_no'],
                        'exp_month' => $payment['cc_month'],
                        'exp_year' => $payment['cc_year'],
                        'cvc' => $payment['cc_cvv2'],
                        'type' => $payment['cc_type'],
                    ]);

                    if (!isset($result['error']) && !empty($result['transaction_id'])) {

                        log_message(
                            'debug',
                            "[STRIPE SUCCESS] txn_id={$result['transaction_id']}"
                        );

                        $payment['transaction_id'] = $result['transaction_id'];
                        $payment['date'] = $result['created_at'];
                        $payment['amount'] = $result['amount'];
                        $payment['sale_id'] = $sale_id;

                        unset($payment['cc_cvv2']);

                        $this->db->insert('payments', $payment);

                    } else {

                        log_message(
                            'error',
                            "[STRIPE FAILED] {$result['code']} : {$result['message']}"
                        );

                        $this->db->update(
                            'sales',
                            ['paid' => 0, 'status' => 'due'],
                            ['id' => $sale_id]
                        );

                        $msg[] = lang('payment_failed');
                    }

                } else {

                    if ($payment['paid_by'] === 'gift_card') {
                        log_message('debug', "[GIFT CARD] card_no={$payment['gc_no']}");
                        $gc = $this->getGiftCardByNO($payment['gc_no']);
                        $this->db->update(
                            'gift_cards',
                            ['balance' => ($gc->balance - $payment['amount'])],
                            ['card_no' => $payment['gc_no']]
                        );
                    }

                    unset($payment['cc_cvv2']);
                    $payment['sale_id'] = $sale_id;
                    $payment['attachment'] = $payment['attachment'];
                    $this->db->insert('payments', $payment);
                }
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                log_message('error', "[TRANSACTION FAILED] sale_id={$sale_id}");
                return false;
            }

            $this->db->trans_commit();

            log_message('debug', "========== END addSale() sale_id={$sale_id} ==========");

            return [
                'sale_id' => $sale_id,
                'message' => $msg,
            ];
        }

        $this->db->trans_rollback();
        log_message('error', 'SALE INSERT FAILED');
        return false;
    }




    public function getSaleItem($sale_id, $product_id, $unit_id = null)
    {
        $this->db->from('sale_items');
        $this->db->where('sale_id', $sale_id);
        $this->db->where('product_id', $product_id);

        if ($unit_id) {
            $this->db->where('unit_id', $unit_id);
        }

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return null;
    }

    // Helper method to get product unit conversion
    public function getProductUnitConversion($product_id, $unit_id)
    {
        return $this->db->where('product_id', $product_id)
            ->where('unit_id', $unit_id)
            ->get('tec_product_unit_conversions')
            ->row();
    }

    /**
     * Deduct an explicitly supplied dual-unit pair.
     *
     * This does not calculate one quantity from the other. A sale can therefore
     * contain, for example, 10 base units and 20 secondary units on one line.
     */
    public function deduct_stock_dual($product_id, $primary_qty, $secondary_qty, $sale_id = null, $sale_item_id = null, $store_id = null)
    {
        $primary_qty = max(0, (float) $primary_qty);
        $secondary_qty = max(0, (float) $secondary_qty);

        if ($primary_qty <= 0 && $secondary_qty <= 0) {
            return true;
        }

        if (!$store_id) {
            $store_id = $this->session->userdata('store_id');
        }

        $available = $this->db
            ->select('COALESCE(SUM(qty_base), 0) AS available_base, COALESCE(SUM(qty_secondary), 0) AS available_secondary', false)
            ->where('product_id', $product_id)
            ->where('store_id', $store_id)
            ->get('stock_batches')
            ->row();

        if (
            !$available ||
            (float) $available->available_base + 0.000001 < $primary_qty ||
            (float) $available->available_secondary + 0.000001 < $secondary_qty
        ) {
            log_message(
                'error',
                "[DUAL STOCK LOW] product_id={$product_id}, requested_base={$primary_qty}, requested_secondary={$secondary_qty}"
            );
            return false;
        }

        $remaining_base = $primary_qty;
        $remaining_secondary = $secondary_qty;

        $batches = $this->db
            ->order_by('id', 'ASC')
            ->where('product_id', $product_id)
            ->where('store_id', $store_id)
            ->where('(qty_base > 0 OR qty_secondary > 0)', null, false)
            ->get('stock_batches')
            ->result();

        foreach ($batches as $batch) {
            if ($remaining_base <= 0.000001 && $remaining_secondary <= 0.000001) {
                break;
            }

            $take_base = min((float) $batch->qty_base, $remaining_base);
            $take_secondary = min((float) $batch->qty_secondary, $remaining_secondary);

            if ($take_base <= 0 && $take_secondary <= 0) {
                continue;
            }

            $this->db
                ->set('qty_base', 'qty_base - ' . $take_base, false)
                ->set('qty_secondary', 'qty_secondary - ' . $take_secondary, false)
                ->where('id', $batch->id)
                ->update('stock_batches');

            if ($sale_id && $sale_item_id) {
                $this->db->insert('cogs_logs', [
                    'sale_id' => $sale_id,
                    'sale_item_id' => $sale_item_id,
                    'product_id' => $product_id,
                    'batch_id' => $batch->id,
                    'qty_base' => $take_base,
                    'qty_secondary' => $take_secondary,
                    'cost_price' => $batch->cost_per_base,
                    'total_cost' => $take_base * $batch->cost_per_base,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $remaining_base -= $take_base;
            $remaining_secondary -= $take_secondary;
        }

        return $remaining_base <= 0.000001 && $remaining_secondary <= 0.000001;
    }


    public function closeRegister($rid, $user_id, $data)
    {
        if (!$rid) {
            $rid = $this->session->userdata('register_id');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }

        // --- Handle suspended bills transfer/deletion ---
        if (isset($data['transfer_opened_bills'])) {
            if ($data['transfer_opened_bills'] == -1) {
                $this->db->delete('suspended_sales', ['created_by' => $user_id]);
            } elseif ($data['transfer_opened_bills'] != 0) {
                $this->db->update(
                    'suspended_sales',
                    ['created_by' => $data['transfer_opened_bills']],
                    ['created_by' => $user_id]
                );
            }
        }

        // ✅ Remove user restriction so any authorized user can close
        $this->db->where('id', $rid);
        if ($this->db->update('registers', $data)) {
            return true;
        }

        return false;
    }


    public function fetch_products($category_id, $limit, $start)
    {
        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        $this->db->order_by('code', 'asc');
        $query = $this->db->get('products');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSaleItems($sale_id)
    {
        $this->db->select("sale_items.*,
        products.code as product_code,
        products.name as product_name,
        products.tax_method as tax_method,
        product_units.name as unit_name", false)
            ->from('sale_items')
            ->join('products', 'products.id = sale_items.product_id', 'left')
            ->join('product_units', 'product_units.id = sale_items.unit_id', 'left')
            ->where('sale_items.sale_id', $sale_id)
            ->order_by('sale_items.id');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $this->db->select("purchase_items.*,
        products.code as product_code,
        products.name as product_name,
        products.tax_method as tax_method,
        product_units.name as unit_name", false)
            ->from('purchase_items')
            ->join('products', 'products.id = purchase_items.product_id', 'left')
            ->join('product_units', 'product_units.id = purchase_items.unit_id', 'left')
            ->where('purchase_items.purchase_id', $purchase_id)
            ->order_by('purchase_items.id');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            $data = [];
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }



    public function getAllSalePayments($sale_id)
    {
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPurchasePayments($purchase_id)
    {
        $q = $this->db->get_where('ppayments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }



    public function getComboItemsByPID($product_id)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCustomerByID($id)
    {
        $q = $this->db->get_where('customers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGiftCardByNO($no)
    {
        $q = $this->db->get_where('gift_cards', ['card_no' => $no], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getOpenRegisterToday()
    {
        $today = date('Y-m-d'); // today only
        $this->db->select('r.id, r.date, r.user_id, r.cash_in_hand, 
        CONCAT(u.first_name, " ", u.last_name, " - ", u.email) as user', false)
            ->from('registers r')
            ->join('users u', 'u.id=r.user_id', 'left')
            ->where('r.status', 'open')
            ->where('DATE(r.date)', $today);

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row(); // only one open register per day
        }
        return false;
    }


    public function getProductByCode($code)
    {
        $jpsq = "( SELECT product_id, quantity, price from {$this->db->dbprefix('product_store_qty')} WHERE store_id = {$this->session->userdata('store_id')} ) AS PSQ";
        $this->db->select("{$this->db->dbprefix('products')}.*, COALESCE(PSQ.quantity, 0) as quantity, COALESCE(PSQ.price, {$this->db->dbprefix('products')}.price) as store_price", false)
            ->join($jpsq, 'PSQ.product_id=products.id', 'left');
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 10, $strict = false)
    {
        $store_id = $this->session->userdata('store_id');

        // Remove zero width characters
        $term = preg_replace('/\x{200C}|\x{200D}/u', '', $term);

        log_message('info', "getProductNames called with cleaned term: {$term}");

        $this->db->select("{$this->db->dbprefix('products')}.*, COALESCE(psq.quantity, 0) as quantity, COALESCE(psq.price, 0) as store_price")
            ->join("( SELECT * from {$this->db->dbprefix('product_store_qty')} WHERE store_id = {$store_id}) psq", 'products.id=psq.product_id', 'left');

        if ($strict) {
            $this->db->where('code', $term);
        } else {
            if ($this->db->dbdriver == 'sqlite3') {
                $this->db->where("(name LIKE '%{$term}%' OR code LIKE '%{$term}%' OR  (name || ' (' || code || ')') LIKE '%{$term}%')");
            } else {
                $this->db->where("(name LIKE '%{$term}%' OR code LIKE '%{$term}%' OR  concat(name, ' (', code, ')') LIKE '%{$term}%')");
            }
        }

        $this->db->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');

        log_message('info', 'Executed SQL: ' . $this->db->last_query());

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            log_message('info', 'Products found: ' . count($data));
            return $data;
        }

        log_message('info', 'No products found for term: ' . $term);
        return false;
    }


    public function getRegisterCashRefunds($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('return_sales', 'return_sales.id=payments.return_id', 'left')
            ->where('type', 'returned')->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'cash');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterCashSales($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'cash');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterCCSales($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'CC');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterChSales($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)
            ->group_start()->where("{$this->db->dbprefix('payments')}.paid_by", 'Cheque')->or_where("{$this->db->dbprefix('payments')}.paid_by", 'cheque')->group_end();
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterExpenses($date = null, $user_id = null)
    {
        $today = date('Y-m-d');

        $this->db->select('COALESCE(SUM(amount), 0) AS total', false)
            ->where('DATE(date)', $today);

        if ($this->session->userdata('store_id')) {
            $this->db->where('store_id', $this->session->userdata('store_id'));
        }

        $q = $this->db->get('tec_expenses');
        return $q->row();
    }


    public function getRegisterGCSales($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'gift_card');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterOtherSales($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'other');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterRefunds($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('return_sales', 'return_sales.id=payments.return_id', 'left')
            ->where('type', 'returned')->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterSales($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterStripeSales($date = null, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'stripe');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleByID($sale_id)
    {
        $q = $this->db->get_where('sales', ['id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSuspendedSaleByID($id)
    {
        $q = $this->db->get_where('suspended_sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSuspendedSaleItems($id)
    {
        $q = $this->db->get_where('suspended_items', ['suspend_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSuspendedSales($user_id = null)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->order_by('date', 'desc');
        $q = $this->db->get_where('suspended_sales', ['created_by' => $user_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTodayCashRefunds()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('return_sales', 'return_sales.id=payments.return_id', 'left')
            ->where('type', 'returned')->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'cash');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }



    public function getTodayCCSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'CC');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayChSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)
            ->group_start()->where("{$this->db->dbprefix('payments')}.paid_by", 'Cheque')->or_where("{$this->db->dbprefix('payments')}.paid_by", 'cheque')->group_end();

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayExpenses()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false)
            ->where('date >', $date);

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayGCSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'gift_card');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayOtherSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'other');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayRefunds()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('return_sales', 'return_sales.id=payments.return_id', 'left')
            ->where('type', 'returned')->where('payments.date >', $date);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodaySales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayStripeSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.date >', $date)->where("{$this->db->dbprefix('payments')}.paid_by", 'stripe');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function openRegister($data)
    {
        if ($this->db->insert('registers', $data)) {
            return true;
        }
        return false;
    }

    public function products_count($category_id)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        return $this->db->count_all_results('products');
    }

    public function registerData($user_id = null)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('registers', ['user_id' => $user_id, 'status' => 'open'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function stripe($amount = 0, $card_info = [], $desc = '')
    {
        $this->load->model('stripe_payments');
        // $card_info = array( "number" => "4242424242424242", "exp_month" => 1, "exp_year" => 2016, "cvc" => "314" );
        // $amount = $amount ? $amount*100 : 3000;
        $amount = $amount * 100;
        if ($amount && !empty($card_info)) {
            $token_info = $this->stripe_payments->create_card_token($card_info);
            if (!isset($token_info['error'])) {
                $token = $token_info->id;
                $data = $this->stripe_payments->insert($token, $desc, $amount, $this->Settings->currency_prefix);
                if (!isset($data['error'])) {
                    $result = [
                        'transaction_id' => $data->id,
                        'created_at' => date('Y-m-d H:i:s', $data->created),
                        'amount' => ($data->amount / 100),
                        'currency' => strtoupper($data->currency),
                    ];
                    return $result;
                }
                return $data;
            }
            return $token_info;
        }
        return false;
    }

    public function suspendSale($data, $items, $did = null)
    {
        if ($did) {
            if ($this->db->update('suspended_sales', $data, ['id' => $did]) && $this->db->delete('suspended_items', ['suspend_id' => $did])) {
                foreach ($items as $item) {
                    unset($item['cost']);
                    $item['suspend_id'] = $did;
                    $this->db->insert('suspended_items', $item);
                }
                return true;
            }
        } else {
            if ($this->db->insert('suspended_sales', $data)) {
                $suspend_id = $this->db->insert_id();
                foreach ($items as $item) {
                    unset($item['cost']);
                    $item['suspend_id'] = $suspend_id;
                    $this->db->insert('suspended_items', $item);
                }
                return $suspend_id;
            }
        }
        return false;
    }

    public function updateSale($id, $data, $items, $payment = [], $did = null)
    {
        log_message('debug', "========== START updateSale() sale_id={$id} ==========");

        // 🔹 Get old sale and items
        $osale = $this->getSaleByID($id);
        $oitems = $this->getAllSaleItems($id) ?: [];

        if (!$osale) {
            log_message('error', "[updateSale] INVALID SALE ID {$id}");
            return false;
        }

        // Keep stock restoration, replacement items, payments and the sale
        // header atomic. This is especially important for native JSON edits,
        // which can contain several unit lines for the same product.
        $this->db->trans_begin();

        $is_preorder_sale = !empty($data['is_preorder']) && (int) $data['is_preorder'] === 1;

        log_message(
            'debug',
            "[SALE TYPE] is_preorder = " . ($is_preorder_sale ? 'YES' : 'NO')
        );

        // 1️⃣ Restore stock for old items (reverse FIFO)
        if (!$is_preorder_sale) {

            log_message('debug', "[RESTORE STOCK] Start reverse FIFO");

            foreach ($oitems as $oitem) {

                log_message(
                    'debug',
                    "[OLD ITEM] sale_item_id={$oitem->id}, product_id={$oitem->product_id}, unit_id={$oitem->unit_id}, qty={$oitem->quantity}"
                );

                if ($oitem->product_id > 0) {

                    $product = $this->site->getProductByID(
                        $oitem->product_id,
                        $osale->store_id
                    );

                    if ($product && $product->type == 'standard') {

                        log_message(
                            'debug',
                            "[CALL restore_stock] product_id={$oitem->product_id}, qty={$oitem->quantity}"
                        );

                        $restored = $this->restore_stock(
                            $oitem->product_id,
                            $oitem->unit_id,
                            $oitem->quantity,
                            $id,
                            $oitem->id
                        );

                        if ($restored === false) {
                            $this->db->trans_rollback();
                            return false;
                        }

                    } elseif ($product && $product->type == 'combo') {

                        log_message(
                            'debug',
                            "[COMBO RESTORE] product_id={$product->id}"
                        );

                        $combo_items = $this->getComboItemsByPID($product->id);

                        foreach ($combo_items as $combo_item) {

                            $cpr = $this->site->getProductByID(
                                $combo_item->id,
                                $osale->store_id
                            );

                            if ($cpr && $cpr->type == 'standard') {

                                $qty = $combo_item->qty * $oitem->quantity;

                                log_message(
                                    'debug',
                                    "[COMBO restore_stock] child_product_id={$cpr->id}, qty={$qty}"
                                );

                                $this->restore_stock(
                                    $cpr->id,
                                    $cpr->unit_id,
                                    $qty,
                                    $id,
                                    $oitem->id
                                );
                            }
                        }
                    }
                }
            }
        } else {
            log_message('debug', "[SKIP RESTORE] preorder sale");
        }

        // 2️⃣ Update sale status based on payment
        $paid = $this->getTotalPaid($id);

        if ($data['grand_total'] <= $paid) {
            $data['status'] = 'paid';
        } elseif ($paid > 0) {
            $data['status'] = 'partial';
        } else {
            $data['status'] = 'due';
        }

        log_message(
            'debug',
            "[SALE STATUS] paid={$paid}, grand_total={$data['grand_total']}, status={$data['status']}"
        );

        // 3️⃣ Update sale record
        if ($this->db->update('sales', $data, ['id' => $id])) {

            log_message('debug', "[SALE UPDATED] sale_id={$id}");

            // 4️⃣ Remove old items + COGS
            $this->db->delete('sale_items', ['sale_id' => $id]);
            $this->db->delete('cogs_logs', ['sale_id' => $id]);

            log_message('debug', "[OLD ITEMS REMOVED] sale_items & cogs_logs");

            foreach ($items as $index => $item) {

                log_message(
                    'debug',
                    "[NEW ITEM {$index}] product_id={$item['product_id']}, unit_id={$item['unit_id']}, qty={$item['quantity']}"
                );

                $explicit_primary_qty = array_key_exists('primary_qty', $item)
                    ? (float) $item['primary_qty']
                    : null;
                $explicit_secondary_qty = array_key_exists('secondary_qty', $item)
                    ? (float) $item['secondary_qty']
                    : null;
                $sale_item = $item;
                $sale_item['qty_base'] = max(
                    0,
                    (float) ($sale_item['qty_base'] ?? $explicit_primary_qty ?? 0)
                );
                $sale_item['qty_secondary'] = max(
                    0,
                    (float) ($sale_item['qty_secondary'] ?? $explicit_secondary_qty ?? 0)
                );
                unset($sale_item['primary_qty'], $sale_item['secondary_qty']);
                $sale_item['sale_id'] = $id;

                if (!$this->db->insert('sale_items', $sale_item)) {
                    $this->db->trans_rollback();
                    return false;
                }
                $sale_item_id = $this->db->insert_id();

                if ($is_preorder_sale) {
                    log_message('debug', "[SKIP DEDUCT] preorder update");
                    continue;
                }

                if ($item['product_id'] > 0) {

                    $product = $this->site->getProductByID(
                        $item['product_id'],
                        $osale->store_id
                    );

                    if ($product && $product->type == 'standard') {

                        log_message(
                            'debug',
                            "[CALL deduct_stock] product_id={$item['product_id']}, qty={$item['quantity']}"
                        );

                        $has_explicit_dual_qty =
                            !empty($product->secondary_unit_id) &&
                            ($explicit_primary_qty !== null || $explicit_secondary_qty !== null);

                        if ($has_explicit_dual_qty) {
                            $stock_result = $this->deduct_stock_dual(
                                $item['product_id'],
                                max(0, $explicit_primary_qty ?? 0),
                                max(0, $explicit_secondary_qty ?? 0),
                                $id,
                                $sale_item_id,
                                $osale->store_id
                            );
                        } else {
                            $stock_result = $this->deduct_stock(
                                $item['product_id'],
                                $item['unit_id'],
                                $item['quantity'],
                                $id,
                                $sale_item_id,
                                $osale->store_id
                            );
                        }

                        if ($stock_result === false) {
                            $this->db->trans_rollback();
                            return false;
                        }

                    } elseif ($product && $product->type == 'combo') {

                        log_message(
                            'debug',
                            "[COMBO DEDUCT] product_id={$product->id}"
                        );

                        $combo_items = $this->getComboItemsByPID($product->id);

                        foreach ($combo_items as $combo_item) {

                            $cpr = $this->site->getProductByID(
                                $combo_item->id,
                                $osale->store_id
                            );

                            if ($cpr && $cpr->type == 'standard') {

                                $qty = $combo_item->qty * $item['quantity'];

                                log_message(
                                    'debug',
                                    "[COMBO deduct_stock] child_product_id={$cpr->id}, qty={$qty}"
                                );

                                $this->deduct_stock(
                                    $cpr->id,
                                    $cpr->unit_id,
                                    $qty,
                                    $id,
                                    $sale_item_id
                                );
                            }
                        }
                    }
                }
            }

            // 5️⃣ Remove suspended sale
            if ($did) {
                log_message('debug', "[REMOVE SUSPENDED] suspend_id={$did}");
                $this->db->delete('suspended_sales', ['id' => $did]);
                $this->db->delete('suspended_items', ['suspend_id' => $did]);
            }

            // 6️⃣ Remove old payments
            $this->db->delete('payments', ['sale_id' => $id]);
            log_message('debug', "[OLD PAYMENTS REMOVED]");

            // 7️⃣ Insert new payment
            if (!empty($payment)) {

                log_message(
                    'debug',
                    "[PAYMENT] method={$payment['paid_by']}, amount={$payment['amount']}"
                );

                if ($payment['paid_by'] == 'stripe') {

                    $result = $this->stripe($payment['amount'], [
                        'number' => $payment['cc_no'],
                        'exp_month' => $payment['cc_month'],
                        'exp_year' => $payment['cc_year'],
                        'cvc' => $payment['cc_cvv2'],
                        'type' => $payment['cc_type']
                    ]);

                    if (!isset($result['error']) && !empty($result['transaction_id'])) {

                        log_message(
                            'debug',
                            "[STRIPE SUCCESS] txn_id={$result['transaction_id']}"
                        );

                        $payment['transaction_id'] = $result['transaction_id'];
                        $payment['date'] = $result['created_at'];
                        $payment['amount'] = $result['amount'];
                        unset($payment['cc_cvv2']);
                        $payment['sale_id'] = $id;
                        $this->db->insert('payments', $payment);

                    } else {

                        log_message(
                            'error',
                            "[STRIPE FAILED] {$result['code']} : {$result['message']}"
                        );

                        $this->db->update(
                            'sales',
                            ['paid' => 0, 'status' => 'due'],
                            ['id' => $id]
                        );
                    }

                } else {

                    if ($payment['paid_by'] == 'gift_card') {
                        log_message('debug', "[GIFT CARD] card_no={$payment['gc_no']}");
                        $gc = $this->getGiftCardByNO($payment['gc_no']);
                        $this->db->update(
                            'gift_cards',
                            ['balance' => ($gc->balance - $payment['amount'])],
                            ['card_no' => $payment['gc_no']]
                        );
                    }

                    unset($payment['cc_cvv2']);
                    $payment['sale_id'] = $id;
                    $this->db->insert('payments', $payment);
                }
            }

            // Always recalc payment status
            $this->site->updateSalePaymentStatus($id);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }

            $this->db->trans_commit();

            log_message('debug', "========== END updateSale() sale_id={$id} ==========");

            return true;
        }

        $this->db->trans_rollback();
        log_message('error', "[updateSale] UPDATE FAILED sale_id={$id}");
        return false;
    }



    public function getTotalPaid($sale_id)
    {
        $this->db->select_sum('amount');
        $this->db->where('sale_id', $sale_id);
        $q = $this->db->get('payments');
        return $q->row()->amount ?? 0;
    }


    /**
     * Restore every FIFO batch consumed by a sale.
     *
     * IMPORTANT: This method does not open or commit a transaction. The
     * caller must own the transaction so stock restoration, sale deletion and
     * audit logging are committed (or rolled back) as one ERP operation.
     *
     * COGS rows contain the exact base/secondary quantities removed from each
     * batch. Replaying those rows is safer than recalculating with a product's
     * current unit-conversion settings.
     */
    public function restoreSaleStockFromCogs(
        $sale_id,
        $store_id = null,
        array $cogs_ids = []
    )
    {
        $sale_id = (int) $sale_id;
        $store_id = $store_id !== null ? (int) $store_id : null;

        $result = [
            'success'            => false,
            'code'               => 'stock_restore_failed',
            'message'            => 'Sale stock could not be restored.',
            'cogs_count'         => 0,
            'restored_base'      => 0.0,
            'restored_secondary' => 0.0,
        ];

        if ($sale_id <= 0) {
            $result['code'] = 'invalid_sale_id';
            $result['message'] = 'Invalid Sale ID.';
            return $result;
        }

        /* Service-only/preorder sales can legitimately have no COGS table use. */
        if (!$this->db->table_exists('cogs_logs')) {
            $result['success'] = true;
            $result['code'] = 'no_cogs_table';
            $result['message'] = 'No stock trace was required for this sale.';
            return $result;
        }

        $this->db->where('sale_id', $sale_id);

        if (!empty($cogs_ids)) {
            $cogs_ids = array_values(array_unique(array_filter(
                array_map('intval', $cogs_ids),
                function ($id) {
                    return $id > 0;
                }
            )));

            if (empty($cogs_ids)) {
                $result['code'] = 'invalid_cogs_ids';
                $result['message'] = 'Invalid COGS IDs for stock restoration.';
                return $result;
            }

            $this->db->where_in('id', $cogs_ids);
        }

        $cogs_logs = $this->db
            ->order_by('id', 'ASC')
            ->get('cogs_logs')
            ->result();

        if (!empty($cogs_ids) && count($cogs_logs) !== count($cogs_ids)) {
            $result['code'] = 'cogs_mapping_incomplete';
            $result['message'] =
                'Some mapped COGS records are missing. Stock was not restored.';
            return $result;
        }

        $result['cogs_count'] = count($cogs_logs);

        foreach ($cogs_logs as $cogs) {
            $batch_id = (int) ($cogs->batch_id ?? 0);
            $product_id = (int) ($cogs->product_id ?? 0);
            $restore_base = max(0, (float) ($cogs->qty_base ?? 0));
            $restore_secondary = max(
                0,
                (float) ($cogs->qty_secondary ?? 0)
            );

            if ($batch_id <= 0 || $product_id <= 0) {
                $result['code'] = 'invalid_cogs_record';
                $result['message'] =
                    'Invalid COGS record found while restoring sale stock.';

                log_message(
                    'error',
                    '[SALE DELETE STOCK] Invalid COGS row. Sale ID: ' .
                    $sale_id . ' | COGS ID: ' . (int) ($cogs->id ?? 0)
                );

                return $result;
            }

            if ($restore_base <= 0 && $restore_secondary <= 0) {
                continue;
            }

            /* Lock the exact FIFO batch until the outer transaction ends. */
            $batch_table = $this->db->dbprefix('stock_batches');
            $batch = $this->db->query(
                "SELECT * FROM `{$batch_table}` WHERE `id` = ? FOR UPDATE",
                [$batch_id]
            )->row();

            if (!$batch) {
                $result['code'] = 'stock_batch_missing';
                $result['message'] =
                    'The original stock batch is missing. Sale deletion was stopped.';

                log_message(
                    'error',
                    '[SALE DELETE STOCK] Batch missing. Sale ID: ' .
                    $sale_id . ' | Batch ID: ' . $batch_id .
                    ' | Product ID: ' . $product_id
                );

                return $result;
            }

            if ((int) $batch->product_id !== $product_id) {
                $result['code'] = 'stock_batch_product_mismatch';
                $result['message'] =
                    'Stock batch product mismatch. Sale deletion was stopped.';

                log_message(
                    'error',
                    '[SALE DELETE STOCK] Product mismatch. Sale ID: ' .
                    $sale_id . ' | Batch ID: ' . $batch_id .
                    ' | COGS Product ID: ' . $product_id .
                    ' | Batch Product ID: ' . (int) $batch->product_id
                );

                return $result;
            }

            if (
                $store_id !== null &&
                isset($batch->store_id) &&
                (int) $batch->store_id !== $store_id
            ) {
                $result['code'] = 'stock_batch_store_mismatch';
                $result['message'] =
                    'Stock batch store mismatch. Sale deletion was stopped.';

                log_message(
                    'error',
                    '[SALE DELETE STOCK] Store mismatch. Sale ID: ' .
                    $sale_id . ' | Batch ID: ' . $batch_id .
                    ' | Sale Store ID: ' . $store_id .
                    ' | Batch Store ID: ' . (int) $batch->store_id
                );

                return $result;
            }

            $updated = $this->db
                ->set(
                    'qty_base',
                    'qty_base + ' . $restore_base,
                    false
                )
                ->set(
                    'qty_secondary',
                    'qty_secondary + ' . $restore_secondary,
                    false
                )
                ->where('id', $batch_id)
                ->update('stock_batches');

            if (!$updated) {
                $result['code'] = 'stock_batch_update_failed';
                $result['message'] =
                    'Stock batch could not be restored. Sale deletion was stopped.';

                log_message(
                    'error',
                    '[SALE DELETE STOCK] Batch update failed. Sale ID: ' .
                    $sale_id . ' | Batch ID: ' . $batch_id
                );

                return $result;
            }

            $result['restored_base'] += $restore_base;
            $result['restored_secondary'] += $restore_secondary;

            log_message(
                'info',
                '[SALE DELETE STOCK] Restored FIFO batch. Sale ID: ' .
                $sale_id . ' | COGS ID: ' . (int) ($cogs->id ?? 0) .
                ' | Batch ID: ' . $batch_id .
                ' | Product ID: ' . $product_id .
                ' | Base Qty: ' . $restore_base .
                ' | Secondary Qty: ' . $restore_secondary
            );
        }

        $result['success'] = true;
        $result['code'] = 'stock_restored';
        $result['message'] = 'Sale stock restored successfully.';

        return $result;
    }


    public function restore_stock($product_id, $unit_id, $quantity, $sale_id = null, $sale_item_id = null)
    {
        log_message('debug', "========== START restore_stock() ==========");

        if ($quantity <= 0) {
            log_message('debug', "[SKIP] quantity <= 0");
            return;
        }

        // ✅ Get correct store ID from sale
        $store_id = $this->db
            ->select('store_id')
            ->where('id', $sale_id)
            ->get('tec_sales')
            ->row('store_id') ?? 1;

        log_message(
            'debug',
            "[INPUT] product_id={$product_id}, unit_id={$unit_id}, qty={$quantity}, sale_id={$sale_id}, sale_item_id={$sale_item_id}, store_id={$store_id}"
        );

        // ✅ Get product and units
        $product = $this->db
            ->select('id, base_unit_id as primary_unit_id, secondary_unit_id')
            ->where('id', $product_id)
            ->get('tec_products')
            ->row();

        if (!$product) {
            log_message('error', "[ERROR] Product not found product_id={$product_id}");
            return false;
        }

        log_message(
            'debug',
            "[PRODUCT] primary_unit_id={$product->primary_unit_id}, secondary_unit_id={$product->secondary_unit_id}"
        );

        // ✅ Get unit conversion
        $conv = $this->getProductUnitConversion($product_id, $product->secondary_unit_id);

        if ($conv) {
            log_message(
                'debug',
                "[CONVERSION] operation_value={$conv->operation_value}"
            );
        } else {
            log_message('debug', "[CONVERSION] NOT FOUND");
        }

        // ✅ Get FIFO batches
        $batches = $this->db
            ->where('product_id', $product_id)
            ->where('store_id', $store_id)
            ->order_by('id', 'ASC')
            ->get('tec_stock_batches')
            ->result();

        log_message(
            'debug',
            "[BATCHES] found=" . count($batches)
        );

        $this->db
            ->select('COALESCE(SUM(qty_base), 0) AS total_base, COALESCE(SUM(qty_secondary), 0) AS total_secondary', false)
            ->where('sale_id', $sale_id)
            ->where('product_id', $product_id);

        if ($sale_item_id) {
            $this->db->where('sale_item_id', $sale_item_id);
        }

        $cogs_totals = $this->db->get('tec_cogs_logs')->row();
        $remaining_base = (float) ($cogs_totals->total_base ?? 0);
        $remaining_secondary = (float) ($cogs_totals->total_secondary ?? 0);
        $restored_total = 0;

        foreach ($batches as $batch) {

            log_message(
                'debug',
                "[BATCH] batch_id={$batch->id}, qty_base={$batch->qty_base}, qty_secondary={$batch->qty_secondary}"
            );

            // ✅ Find deducted qty from COGS
            $this->db
                ->select('qty_base, qty_secondary')
                ->where('batch_id', $batch->id)
                ->where('sale_id', $sale_id)
                ->where('product_id', $product_id);

            if ($sale_item_id) {
                $this->db->where('sale_item_id', $sale_item_id);
            }

            $cogs = $this->db->get('tec_cogs_logs')->row();

            if (!$cogs) {
                log_message(
                    'debug',
                    "[COGS] none for batch_id={$batch->id}"
                );
                continue;
            }

            log_message(
                'debug',
                "[COGS] batch_id={$batch->id}, qty_base={$cogs->qty_base}, qty_secondary={$cogs->qty_secondary}"
            );

            // COGS already contains the exact base/secondary pair that was
            // deducted. Restore that pair instead of recalculating from the
            // product's current conversion rule.
            $restore_base = max(0, (float) $cogs->qty_base);
            $restore_secondary = max(0, (float) $cogs->qty_secondary);
            $restore_qty = max($restore_base, $restore_secondary);

            if ($restore_base <= 0 && $restore_secondary <= 0) {
                continue;
            }

            log_message(
                'debug',
                "[CALC] restore_qty={$restore_qty}, restore_base={$restore_base}, restore_secondary={$restore_secondary}"
            );

            // ✅ Restore stock
            $this->db
                ->set('qty_base', 'qty_base + ' . $restore_base, false)
                ->set('qty_secondary', 'qty_secondary + ' . $restore_secondary, false)
                ->where('id', $batch->id)
                ->update('tec_stock_batches');

            log_message(
                'debug',
                "[RESTORED] batch_id={$batch->id}"
            );

            $remaining_base -= $restore_base;
            $remaining_secondary -= $restore_secondary;
            $restored_total += $restore_qty;

            log_message(
                'debug',
                "[PROGRESS] remaining_base={$remaining_base}, remaining_secondary={$remaining_secondary}, restored_total={$restored_total}"
            );

            if ($remaining_base <= 0.000001 && $remaining_secondary <= 0.000001) {
                break;
            }
        }

        log_message(
            'debug',
            "========== END restore_stock() product_id={$product_id}, restored_total={$restored_total} =========="
        );

        return true;
    }





    public function getCurrencyRate($code)
    {
        $this->db->where('currency_code', $code);
        $this->db->where('status', 1);
        $q = $this->db->get('tec_currencies');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }


    public function getProductUnitPrices($product_id)
    {
        $this->db->select('unit_id, price');
        $this->db->from('tec_product_unit_prices');
        $this->db->where('product_id', $product_id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function getProductUnitConversions($product_id)
    {
        $this->db->where('product_id', $product_id);
        $q = $this->db->get('tec_product_unit_conversions');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function getProductBatches($product_id, $store_id)
    {
        return $this->db->select('id, expiry_date, qty_base, qty_secondary, cost_per_base as cost')
            ->from('stock_batches')
            ->where('product_id', $product_id)
            ->where('store_id', $store_id)
            ->where('qty_base >', 0)
            ->order_by('expiry_date', 'ASC') // FIFO
            ->get()
            ->result();
    }


    public function getProductAvailableQty($product_id, $store_id = null)
    {
        $this->db->select('SUM(qty_base) as total_qty');
        $this->db->from('tec_stock_batches');
        $this->db->where('product_id', $product_id);

        if ($store_id) {
            $this->db->where('store_id', $store_id);
        }

        $batch = $this->db->get()->row();
        return $batch ? (float) $batch->total_qty : 0;
    }

    public function getProductDelivery($product_id, $store_id = null)
    {
        $this->db->select('delivery');
        $this->db->from('tec_stock_batches');
        $this->db->where('product_id', $product_id);
        $this->db->where('qty_base >', 0); // ✅ Fixed line

        if ($store_id) {
            $this->db->where('store_id', $store_id);
        }

        $this->db->order_by('id', 'asc'); // optional: get the latest batch
        $this->db->limit(1); // only one row

        $delivery = $this->db->get()->row();
        return $delivery ? (float) $delivery->delivery : 0;
    }

    public function getProductCost($product_id, $store_id = null)
    {
        // 1️⃣ Try stock batches first
        $this->db->select('cost_per_base')
            ->from('tec_stock_batches')
            ->where('product_id', $product_id)
            ->where('qty_base >', 0);

        if ($store_id) {
            $this->db->where('store_id', $store_id);
        }

        $this->db->order_by('id', 'DESC')->limit(1);
        $row = $this->db->get()->row();

        if ($row) {
            return (float) $row->cost_per_base;
        }

        // 2️⃣ Fallback → latest batch (qty may be 0)
        $this->db->select('cost_per_base')
            ->from('tec_stock_batches')
            ->where('product_id', $product_id);

        if ($store_id) {
            $this->db->where('store_id', $store_id);
        }

        $this->db->order_by('id', 'DESC')->limit(1);
        $row = $this->db->get()->row();

        return $row ? (float) $row->cost_per_base : 0;
    }




    public function getProductAvailableSecondQty($product_id, $store_id = null)
    {
        $this->db->select('SUM(qty_secondary) as total_secondaryqty');
        $this->db->from('tec_stock_batches');
        $this->db->where('product_id', $product_id);

        if ($store_id) {
            $this->db->where('store_id', $store_id);
        }

        $batch = $this->db->get()->row();
        return $batch ? (float) $batch->total_secondaryqty : 0; // ✅ correct field
    }



    /**
     * Convert a quantity entered in a selectable unit into base-stock quantity.
     *
     * Normal products may have two units without being dual-quantity products.
     * Their stock is tracked in qty_base, so the conversion table is the source
     * of truth when the secondary unit is selected.
     */
    private function getBaseQuantityFactor($product_id, $unit_id, $base_unit_id)
    {
        $unit_id = (int) $unit_id;
        $base_unit_id = (int) $base_unit_id;

        if (!$unit_id || !$base_unit_id || $unit_id === $base_unit_id) {
            return 1.0;
        }

        $conversion = $this->getProductUnitConversion($product_id, $unit_id);
        if (!$conversion || !is_numeric($conversion->operation_value)) {
            log_message(
                'error',
                "[UNIT CONVERSION MISSING] product_id={$product_id}, unit_id={$unit_id}"
            );
            return null;
        }

        $value = (float) $conversion->operation_value;
        if ($value <= 0) {
            log_message(
                'error',
                "[UNIT CONVERSION INVALID] product_id={$product_id}, unit_id={$unit_id}, value={$value}"
            );
            return null;
        }

        $operator = strtolower(trim((string) ($conversion->operator ?? '*')));
        return $operator === '/' ? 1 / $value : $value;
    }

    /**
     * Deduct a normal product from base stock using FIFO.
     *
     * This path is intentionally separate from deduct_stock_dual(). A product
     * with two selectable units but is_dual_unit = 0 still has one stock balance;
     * selecting its secondary unit automatically converts the entered quantity
     * into base units before deduction.
     */
    public function deduct_stock($product_id, $unit_id, $qty, $sale_id = null, $sale_item_id = null, $store_id = null)
    {
        $qty = (float) $qty;

        log_message(
            'debug',
            "[NORMAL STOCK] product_id={$product_id}, unit_id={$unit_id}, qty={$qty}, sale_id={$sale_id}, sale_item_id={$sale_item_id}"
        );

        if (!$store_id) {
            $store_id = $this->session->userdata('store_id');
        }

        if ($qty <= 0) {
            return true;
        }

        $product = $this->db->get_where('tec_products', ['id' => $product_id])->row();
        if (!$product) {
            log_message('error', "[NORMAL STOCK] Product not found product_id={$product_id}");
            return false;
        }

        $unit_id = (int) $unit_id ?: (int) $product->base_unit_id;
        $base_factor = $this->getBaseQuantityFactor(
            $product_id,
            $unit_id,
            $product->base_unit_id
        );

        if ($base_factor === null) {
            return false;
        }

        $remaining_base = $qty * $base_factor;
        $epsilon = 0.000001;

        $batches = $this->db
            ->order_by('id', 'ASC')
            ->where('product_id', $product_id)
            ->where('store_id', $store_id)
            ->where('qty_base >', 0)
            ->get('tec_stock_batches')
            ->result();

        foreach ($batches as $batch) {
            if ($remaining_base <= $epsilon) {
                break;
            }

            $available_base = max(0, (float) $batch->qty_base);
            $take_base = min($available_base, $remaining_base);
            if ($take_base <= $epsilon) {
                continue;
            }

            $updated = $this->db
                ->set('qty_base', 'qty_base - ' . $take_base, false)
                ->where('id', $batch->id)
                ->update('tec_stock_batches');

            if (!$updated) {
                log_message(
                    'error',
                    "[NORMAL STOCK] Failed to update batch_id={$batch->id}, product_id={$product_id}"
                );
                return false;
            }

            if ($sale_id && $sale_item_id) {
                $this->db->insert('tec_cogs_logs', [
                    'sale_id' => $sale_id,
                    'sale_item_id' => $sale_item_id,
                    'product_id' => $product_id,
                    'batch_id' => $batch->id,
                    'qty_base' => $take_base,
                    'qty_secondary' => 0,
                    'cost_price' => $batch->cost_per_base,
                    'total_cost' => $take_base * $batch->cost_per_base,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $remaining_base -= $take_base;
        }

        if ($remaining_base > $epsilon) {
            log_message(
                'error',
                "[NORMAL STOCK LOW] product_id={$product_id}, requested_base=" . ($qty * $base_factor) . ", remaining_base={$remaining_base}"
            );
            return false;
        }

        return true;
    }




    public function get_unit_ratio($product_id, $unit_id)
    {
        $row = $this->db->get_where('tec_product_unit_conversions', [
            'product_id' => $product_id,
            'unit_id' => $unit_id
        ])->row();

        if (!$row)
            return 1;  // fallback

        if ($row->operator == '*' || $row->operator == 'x') {
            return $row->operation_value;
        }

        // You can extend here if you add / or + later
        return 1;
    }

    public function get_other_unit_ratio($product_id, $current_unit_id)
    {
        // Find the conversion row that is NOT the current unit_id
        $other = $this->db
            ->where('product_id', $product_id)
            ->where('unit_id !=', $current_unit_id)
            ->get('tec_product_unit_conversions')
            ->row();

        if ($other) {
            return $other->operation_value;
        }

        return 0; // fallback
    }



    public function getRegisterByDate($store_id, $date)
    {
        $this->db->from('registers');
        $this->db->where('store_id', $store_id);
        $this->db->where('DATE(date)', $date);
        $this->db->where('status', 'open'); // only open registers

        $query = $this->db->get();
        return $query->row(); // return single row if exists
    }

    public function getYesterdayClosedRegister($store_id)
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $this->db->from('registers');
        $this->db->where('store_id', $store_id);
        $this->db->where('DATE(date)', $yesterday);
        $this->db->where('status', 'close'); // only closed registers
        $this->db->order_by('date', 'DESC');
        $this->db->limit(1);

        $query = $this->db->get();
        return $query->row();
    }



    public function getTodayCashSales($store_id = null)
    {
        $today = date('Y-m-d 00:00:00');
        $now = date('Y-m-d 23:59:59');

        $this->db->select('SUM(p.amount) as paid')
            ->from('tec_payments p')
            ->join('tec_sales s', 's.id = p.sale_id', 'left')
            ->where('p.paid_by', 'cash')
            ->where('p.date >=', $today)
            ->where('p.date <=', $now);

        if ($store_id) {
            $this->db->where('s.store_id', $store_id);
        }

        $q = $this->db->get();
        if ($q && $q->num_rows() > 0) {
            return $q->row();
        }
        return (object) ['paid' => 0];
    }

    public function getSecondCost($product_id, $store_id = null)
    {
        // Get base cost from stock batch
        $this->db->select('cost_per_base');
        $this->db->from('tec_stock_batches');
        $this->db->where('product_id', $product_id);
        $this->db->where('qty_base >', 0);

        if ($store_id) {
            $this->db->where('store_id', $store_id);
        }

        $this->db->order_by('id', 'asc');
        $this->db->limit(1);

        $batch = $this->db->get()->row();
        $cost_per_base = $batch ? (float) $batch->cost_per_base : 0;

        // ✅ Get conversion rate from product table (for example: carton -> bottle)
        $this->db->select('unit_id, operation_value, operator');
        // assuming unit_quantity = how many base units in 1 secondary unit
        $this->db->from('tec_product_unit_conversions');
        $this->db->where('product_id', $product_id);
        $this->db->where('operation_value >', 1);
        $product = $this->db->get()->row();

        $secondary_cost = 0;
        if ($product && $product->operation_value > 0) {
            $secondary_cost = $cost_per_base / $product->operation_value;
        }


        return $secondary_cost ? (float) $secondary_cost : 0;
    }

    public function get_available_stock($product_id, $store_id)
    {
        $this->db->select_sum('qty_base');
        $this->db->from('tec_stock_batches');
        $this->db->where('product_id', $product_id);
        $this->db->where('store_id', $store_id);

        $row = $this->db->get()->row();
        return $row ? (float) $row->qty_base : 0;
    }

    public function get_available_stock_by_unit($product_id, $unit_id, $store_id)
    {
        $sql = "
        SELECT SUM(
            CASE
                WHEN primary_unit_id = ? THEN qty_base
                WHEN secondary_unit_id = ? THEN qty_secondary
                ELSE 0
            END
        ) AS available_qty
        FROM tec_stock_batches
        WHERE product_id = ?
          AND store_id = ?
    ";

        $row = $this->db->query(
            $sql,
            [$unit_id, $unit_id, $product_id, $store_id]
        )->row();

        return $row ? (float) $row->available_qty : 0;
    }


}
