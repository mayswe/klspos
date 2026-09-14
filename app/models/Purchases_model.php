<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Purchases_model extends CI_Model
{
    private $last_receive_batch_id;
    public function __construct()
    {
        parent::__construct();
    }

    public function addExpense($data = [])
    {
        if ($this->db->insert('expenses', $data)) {
            return true;
        }
        return false;
    }
    
   public function addPayment($data = [])
{
    // 🪵 Debug log the incoming payment data
    log_message('debug', '💰 addPayment() called with data: ' . json_encode($data));

    if ($this->db->insert('ppayments', $data)) {
        log_message('debug', '✅ Payment inserted into ppayments. ID: ' . $this->db->insert_id());

        if ($data['paid_by'] == 'gift_card') {
            $gc = $this->site->getGiftCard($data['gc_no']);
            log_message('debug', '🎁 Gift card found: ' . json_encode($gc));

            $new_balance = $gc->balance - $data['amount'];
            $this->db->update('gift_cards', ['balance' => $new_balance], ['card_no' => $data['gc_no']]);
            log_message('debug', "🎯 Gift card balance updated: Old={$gc->balance}, New={$new_balance}");
        }

        // Sync purchase payments
        log_message('debug', '🔁 Calling syncPurchasePayments with ID: ' . $data['purchase_id']);
        $this->syncPurchasePayments($data['purchase_id']);

        return true;
    }

    log_message('error', '❌ Failed to insert payment into ppayments. Data: ' . json_encode($data));
    return false;
}

public function syncPurchasePayments($id)
{
    log_message('debug', "🧾 syncPurchasePayments() called for Purchase ID: {$id}");

    $purchase = $this->getPurchaseByID($id);
    log_message('debug', '📦 Purchase data: ' . json_encode($purchase));

    $payments = $this->getPurchasePayments($id);
    log_message('debug', '💵 Payments found: ' . json_encode($payments));

    $paid = 0;
    if ($payments) {
        foreach ($payments as $payment) {
            $paid += $payment->amount;
        }
    }
    log_message('debug', "💰 Total paid calculated: {$paid}");

    $status = $paid <= 0 ? 'due' : ($purchase->total <= $paid ? 'paid' : 'partial');
    log_message('debug', "📊 Status determined: {$status}");

    if ($this->db->update('purchases', ['paid' => $paid, 'status' => $status], ['id' => $id])) {
        log_message('debug', "✅ Purchases table updated. ID={$id}, Paid={$paid}, Status={$status}");
        return true;
    }

    log_message('error', "❌ Failed to update purchases table. ID={$id}");
    return false;
}

    
   

public function addPurchase($data, $items, $payment_attachment = null)
{
    $this->db->trans_begin();

    log_message('debug', '========== addPurchase START ==========');
    log_message('debug', 'Purchase Data => ' . print_r($data, true));
    log_message('debug', 'Purchase Items => ' . print_r($items, true));

    // ---------------------------------------------------------
    // Normalize numeric fields
    // ---------------------------------------------------------
    $data['delivery'] = (
        isset($data['delivery']) &&
        $data['delivery'] !== '' &&
        is_numeric($data['delivery'])
    )
        ? (float) $data['delivery']
        : 0;

    $data['total'] = (
        isset($data['total']) &&
        is_numeric($data['total'])
    )
        ? (float) $data['total']
        : 0;

    $data['paid'] = (
        isset($data['paid']) &&
        is_numeric($data['paid'])
    )
        ? (float) $data['paid']
        : 0;

    $intended_received = !empty($data['received']); 
    $data['received']  = 0;

    // ---------------------------------------------------------
    // Insert purchase
    // ---------------------------------------------------------
    if (!$this->db->insert('purchases', $data)) {

        $error = $this->db->error();

        log_message(
            'error',
            'PURCHASE INSERT FAILED => ' . print_r($error, true)
        );

        $this->db->trans_rollback();

        return false;
    }

    $purchase_id = $this->db->insert_id();

    log_message(
        'debug',
        'Purchase inserted. ID => ' . $purchase_id
    );

    // ---------------------------------------------------------
    // Total Qty
    // ---------------------------------------------------------
    $total_qty = 0;

    foreach ($items as $item) {

        $qty = (
            isset($item['primary_qty']) &&
            is_numeric($item['primary_qty'])
        )
            ? (float) $item['primary_qty']
            : 0;

        $total_qty += $qty;
    }
    
    
    log_message(
        'debug',
        'Total Primary Qty => ' . $total_qty
    );

    $receive_map = [];
    
    // ---------------------------------------------------------
    // Purchase Items
    // ---------------------------------------------------------
    foreach ($items as $key => $item) {

        log_message(
            'debug',
            'Processing item #' . $key . ' => '
            . print_r($item, true)
        );

        $primary_qty = (
            isset($item['primary_qty']) &&
            is_numeric($item['primary_qty'])
        )
            ? (float) $item['primary_qty']
            : 0;

        $secondary_qty = (
            isset($item['secondary_qty']) &&
            $item['secondary_qty'] !== '' &&
            is_numeric($item['secondary_qty'])
        )
            ? (float) $item['secondary_qty']
            : 0;

        $net_unit_cost = (
            isset($item['net_unit_cost']) &&
            is_numeric($item['net_unit_cost'])
        )
            ? (float) $item['net_unit_cost']
            : 0;

        $subtotal = (
            isset($item['subtotal']) &&
            is_numeric($item['subtotal'])
        )
            ? (float) $item['subtotal']
            : 0;

        // -----------------------------------------------------
        // Delivery
        // -----------------------------------------------------
        if (
            isset($item['delivery']) &&
            $item['delivery'] !== '' &&
            is_numeric($item['delivery']) &&
            (float) $item['delivery'] > 0
        ) {

            $delivery = (float) $item['delivery'];

        } elseif (
            $data['delivery'] > 0 &&
            $total_qty > 0
        ) {

            $delivery = round(
                ($data['delivery'] / $total_qty)
                * $primary_qty,
                2
            );

        } else {

            $delivery = 0;
        }

        $purchase_item_data = [

            'purchase_id' =>
                $purchase_id,

            'product_id' =>
                (int) $item['product_id'],

            'primary_qty' =>
                $primary_qty,

            'primary_unit' =>
                !empty($item['primary_unit'])
                    ? (int) $item['primary_unit']
                    : null,

            'secondary_qty' =>
                $secondary_qty,

            'secondary_unit' =>
                !empty($item['secondary_unit'])
                    ? (int) $item['secondary_unit']
                    : null,

            'net_unit_cost' =>
                $net_unit_cost,

            'subtotal' =>
                $subtotal,

            'delivery' =>
                $delivery
        ];

        log_message(
            'debug',
            'Purchase Item Insert Data => '
            . print_r($purchase_item_data, true)
        );

        // -----------------------------------------------------
        // Insert purchase item
        // -----------------------------------------------------
        if (
            !$this->db->insert(
                'purchase_items',
                $purchase_item_data
            )
        ) {

            $error = $this->db->error();

            log_message(
                'error',
                'PURCHASE ITEM INSERT FAILED => '
                . print_r($error, true)
            );

            $this->db->trans_rollback();

            return false;
        }

        log_message(
            'debug',
            'Purchase item inserted successfully.'
        );
        
        $purchase_item_id = $this->db->insert_id();
        
        // -----------------------------------------------------
        // Queue automatic receiving
        // -----------------------------------------------------
        /*
         * IMPORTANT:
         * Stock ကို ဒီ item loop ထဲမှာ မထည့်ရပါ။
         * အောက်ဘက် transaction commit ပြီးနောက် receivePurchaseItems()
         * က setStoreQuantity() ကို တစ်ကြိမ်တည်း ခေါ်ပြီး stock batch,
         * stock movement နှင့် receive history တို့ကို အတူသိမ်းမည်။
         *
         * ယခင် code က ဒီနေရာမှာ setStoreQuantity() ခေါ်ပြီးနောက်
         * receivePurchaseItems() ထဲမှာ ထပ်ခေါ်သဖြင့် stock ၂ ဆဝင်ခဲ့သည်။
         */
        if (
            empty($data['opening']) &&
            $intended_received
        ) {
            $receive_map[$purchase_item_id] = $primary_qty;

            log_message(
                'debug',
                'Queued item for automatic receiving => '
                . print_r([
                    'purchase_id'      => $purchase_id,
                    'purchase_item_id' => $purchase_item_id,
                    'product_id'       => $item['product_id'],
                    'primary_qty'      => $primary_qty,
                ], true)
            );
        }
    }

    // ---------------------------------------------------------
    // Payment
    // ---------------------------------------------------------
    if (
        isset($data['paid']) &&
        $data['paid'] > 0
    ) {

        $first_payment = [

            'purchase_id' =>
                $purchase_id,

            'date' =>
                date('Y-m-d H:i:s'),

            'amount' =>
                (float) $data['paid'],

            'paid_by' =>
                !empty($data['paid_by'])
                    ? $data['paid_by']
                    : 'cash',

            'created_by' =>
                (int) $this->session
                    ->userdata('user_id')
        ];

        // The voucher selected on the purchase form belongs to the
        // first payment too.  Without this value, the payment history
        // popup displays "No file" even though the purchase has a file.
        if (!empty($payment_attachment)) {
            $first_payment['attachment'] = $payment_attachment;
        }

        log_message(
            'debug',
            'Payment Insert Data => '
            . print_r($first_payment, true)
        );

        if (
            !$this->db->insert(
                'ppayments',
                $first_payment
            )
        ) {

            $error = $this->db->error();

            log_message(
                'error',
                'PAYMENT INSERT FAILED => '
                . print_r($error, true)
            );

            $this->db->trans_rollback();

            return false;
        }

        log_message(
            'debug',
            'Payment inserted successfully.'
        );

        $this->syncPurchasePayments(
            $purchase_id
        );

        log_message(
            'debug',
            'syncPurchasePayments completed.'
        );
    }

    // ---------------------------------------------------------
    // Final transaction
    // ---------------------------------------------------------
    if ($this->db->trans_status() === false) {

        $error = $this->db->error();

        log_message(
            'error',
            'TRANSACTION FAILED => '
            . print_r($error, true)
        );

        $this->db->trans_rollback();

        return false;
    }

    $this->db->trans_commit();

    log_message(
        'debug',
        '========== addPurchase SUCCESS ID '
        . $purchase_id
        . ' =========='
    );
    
    // ---------------------------------------------------------
    // Now receive items, in a separate transaction, AFTER commit
    // ---------------------------------------------------------
    if (!empty($receive_map)) {
        $result = $this->receivePurchaseItems(
            $purchase_id,
            $receive_map,
            date('Y-m-d H:i:s'),
            $data['note'],
            $data['created_by']
            // no $data['store_id'] — the function doesn't take it;
            
        );

        if (empty($result) || empty($result['status'])) {
            log_message('error', 'PARTIAL RECEIVE FAILED => ' . ($result['message'] ?? 'unknown'));
            // purchase + items are already committed at this point —
            // decide here whether that's acceptable, or whether you
            // need to explicitly reverse the purchase (e.g. delete it)
            // since receivePurchaseItems already rolled back its own txn.
        }
        return $purchase_id;
    }

    log_message('debug', '========== addPurchase SUCCESS ID ' . $purchase_id . ' ==========');
    return $purchase_id;

    
}


    // Get base unit for product
    private function getBaseUnitId($product_id)
    {
        $q = $this->db->select('base_unit_id')
            ->where('id', $product_id)
            ->get('products', 1);
        return $q->num_rows() ? $q->row()->base_unit_id : null;
    }

    // Get unit conversion value (e.g., 1 carton = 12 pcs)
    private function getUnitConversion($product_id, $unit_id)
    {
        $q = $this->db->select('operation_value')
            ->where('product_id', $product_id)
            ->where('unit_id', $unit_id)
            ->get('product_unit_conversions', 1);
        return $q->num_rows() ? $q->row()->operation_value : null;
    }

    public function deleteExpense($id)
    {
        if ($this->db->delete('expenses', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePurchase($id)
    {
        return $this->deletePurchaseSafely($id)['status'];
    }

    public function deletePurchaseSafely($id)
    {
        $id = (int)$id;
        $this->db->trans_begin();
        try {
            $purchase = $this->db->query('SELECT * FROM '.$this->db->dbprefix('purchases').' WHERE id = ? FOR UPDATE', [$id])->row();
            if (!$purchase) { throw new RuntimeException('Purchase not found.'); }
            if ((float)$purchase->paid != 0 || $this->db->where('purchase_id',$id)->count_all_results('ppayments')) {
                throw new RuntimeException('Reverse purchase payments before deleting this purchase.');
            }
            if ($this->db->where('purchase_id',$id)->count_all_results('purchase_receipts')) {
                throw new RuntimeException('Reverse receive history before deleting this purchase.');
            }
            $items = $this->db->query('SELECT * FROM '.$this->db->dbprefix('purchase_items').' WHERE purchase_id = ? FOR UPDATE', [$id])->result();
            foreach ($items as $item) {
                if ((float)$item->received_primary_qty != 0 || (float)$item->received_secondary_qty != 0) {
                    throw new RuntimeException('Received quantities remain. Reconcile receive history first.');
                }
            }
            if ((int)$purchase->received !== 0) {
                throw new RuntimeException('Purchase is still marked received. Reconcile receive history first.');
            }
            $batches = $this->db->query('SELECT * FROM '.$this->db->dbprefix('stock_batches').' WHERE purchase_id = ? FOR UPDATE', [$id])->result();
            foreach ($batches as $batch) {
                if ((float)$batch->qty_base != 0 || (float)$batch->qty_primary != 0 || (float)$batch->qty_secondary != 0) {
                    throw new RuntimeException('Purchase stock remains. Reverse receive history first.');
                }
                if ($this->db->where('batch_id',$batch->id)->count_all_results('cogs_logs')) {
                    throw new RuntimeException('Purchase stock has sale history and cannot be deleted.');
                }
            }
            // Preserve zero-balance batches and signed movements as the reversal audit.
            // Legacy product_batches cannot be safely reconciled by this path.
            if ($this->db->table_exists('product_batches') && $this->db->where('purchase_id',$id)->count_all_results('product_batches')) {
                throw new RuntimeException('Legacy stock records require reconciliation before deletion.');
            }
            $this->db->where('purchase_id',$id)->delete('purchase_items');
            $this->db->where('id',$id)->delete('purchases');
            if ($this->db->trans_status() === false) { throw new RuntimeException('Purchase deletion failed.'); }
            $this->db->trans_commit();
            return ['status'=>true, 'message'=>'Purchase deleted.'];
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            return ['status'=>false, 'message'=>$error->getMessage()];
        }
    }

    public function getExpenseByID($id)
    {
        $q = $this->db->get_where('expenses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 10, $strict = false)
    {
        // Remove zero width characters (U+200C ZERO WIDTH NON-JOINER and U+200D ZERO WIDTH JOINER)
        $term = preg_replace('/\x{200C}|\x{200D}/u', '', $term);

        if ($strict) {
            $this->db->where('code', $term);
        } else {
            $this->db->where('type !=', 'combo');

            if ($this->db->dbdriver == 'sqlite3') {
                $like_term = '%' . $term . '%';
                $this->db->where("(name LIKE '{$like_term}' OR code LIKE '{$like_term}' OR (name || ' (' || code || ')') LIKE '{$like_term}')");
            } else {
                // Safer with query bindings
                $like_term = '%' . $this->db->escape_like_str($term) . '%';
                $this->db->where("(name LIKE '{$like_term}' ESCAPE '!' OR code LIKE '{$like_term}' ESCAPE '!' OR CONCAT(name, ' (', code, ')') LIKE '{$like_term}' ESCAPE '!')");
            }
        }

        $this->db->limit($limit);
        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {
            $data = [];  // Ensure $data is initialized
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    

    public function getPurchaseByID($id)
{
    $this->db->select('
        purchases.*, 
        suppliers.name as supplier_name, 
        stores.name as store_name
    ');
    $this->db->from('purchases');
    $this->db->join('suppliers', 'suppliers.id = purchases.supplier_id', 'left');
    $this->db->join('stores', 'stores.id = purchases.store_id', 'left');
    $this->db->where('purchases.id', $id);
    $q = $this->db->get();

    if ($q->num_rows() > 0) {
        return $q->row();
    }
    return false;
}


   


    public function getStoreQuantity($product_id, $store_id)
    {
        $q = $this->db->get_where('product_store_qty', ['product_id' => $product_id, 'store_id' => $store_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row()->quantity;
        }
        return 0;
    }





    public function checkStoreProductExists($product_id, $store_id)
    {
        $q = $this->db->get_where('product_store_qty', ['product_id' => $product_id, 'store_id' => $store_id], 1);
        return $q->num_rows() > 0;
    }


    public function updateExpense($id, $data = [])
    {
        if ($this->db->update('expenses', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public $purchase_edit_error = '';

    public function updatePurchase($id, $data = null, $items = [])
    {
        $this->purchase_edit_error = '';
        $this->db->trans_begin();
        try {
            $purchase = $this->db->query('SELECT * FROM '.$this->db->dbprefix('purchases').' WHERE id = ? FOR UPDATE', [(int)$id])->row();
            if (!$purchase || !is_array($data) || !$items) { throw new RuntimeException('Purchase and at least one valid item are required.'); }
            if ((int)$purchase->received || (float)$purchase->paid != 0
                || $this->db->where('purchase_id',$id)->count_all_results('purchase_receipts')
                || $this->db->where('purchase_id',$id)->count_all_results('ppayments')) {
                throw new RuntimeException('Reverse receive history and payments before editing purchase items.');
            }
            foreach ($this->db->where('purchase_id',$id)->get('purchase_items')->result() as $old) {
                if ((float)$old->received_primary_qty != 0 || (float)$old->received_secondary_qty != 0) {
                    throw new RuntimeException('Received quantities must be reconciled before editing.');
                }
            }
            foreach ($this->db->where('purchase_id',$id)->get('stock_batches')->result() as $batch) {
                if ((float)$batch->qty_base != 0 || (float)$batch->qty_primary != 0 || (float)$batch->qty_secondary != 0) {
                    throw new RuntimeException('Remaining stock must be reconciled before editing.');
                }
            }
            if ($this->db->table_exists('product_batches') && $this->db->where('purchase_id',$id)->count_all_results('product_batches')) {
                throw new RuntimeException('Legacy stock records require reconciliation before editing.');
            }
            if (!empty($data['received']) || !empty($data['paid']) || !empty($data['advance_deducted'])) {
                throw new RuntimeException('Use Receive and Payment actions after saving the purchase.');
            }
            $number = function ($value, $positive = false) {
                if (!is_numeric($value) || !is_finite((float)$value) || ($positive ? (float)$value <= 0 : (float)$value < 0)) {
                    throw new RuntimeException('Quantities and costs must be valid non-negative numbers; primary quantity must be positive.');
                }
                return (float)$value;
            };
            $delivery = round($number($data['delivery'] ?? $purchase->delivery),2);
            $rows = []; $total = 0; $quantity = 0;
            foreach ($items as $item) {
                $product = $this->getProductByID((int)($item['product_id'] ?? 0));
                if (!$product) { throw new RuntimeException('Purchase product not found.'); }
                $primary = $number($item['primary_qty'] ?? null,true);
                $secondary = $number($item['secondary_qty'] ?? 0);
                $cost = $number($item['net_unit_cost'] ?? null);
                $unit = (int)($item['primary_unit'] ?? 0);
                if ($unit !== (int)$product->base_unit_id && !$this->db->get_where('product_unit_conversions',['product_id'=>$product->id,'unit_id'=>$unit])->row()) {
                    throw new RuntimeException('Primary unit does not belong to this product.');
                }
                $secondaryUnit = (int)($item['secondary_unit'] ?? 0);
                if ($secondary > 0 && (empty($product->is_dual_unit) || !$secondaryUnit || $secondaryUnit !== (int)$product->secondary_unit_id)) {
                    throw new RuntimeException('Secondary unit does not belong to this product.');
                }
                $subtotal = round($primary*$cost,2);
                $rows[] = ['purchase_id'=>(int)$id,'product_id'=>(int)$product->id,
                    'primary_qty'=>$primary,'primary_unit'=>$unit,'secondary_qty'=>$secondary,
                    'secondary_unit'=>$secondaryUnit ?: null,'net_unit_cost'=>$cost,'subtotal'=>$subtotal,
                    'received_primary_qty'=>0,'received_secondary_qty'=>0];
                $quantity += $primary; $total += $subtotal;
            }
            // Allocate header delivery once. The final line receives rounding residue.
            $remaining = $delivery;
            foreach ($rows as $index => &$row) {
                $row['delivery'] = $index === count($rows)-1 ? $remaining : min($remaining,round($delivery*$row['primary_qty']/$quantity,2));
                $remaining = round($remaining-$row['delivery'],2);
            }
            unset($row);
            $header = array_intersect_key($data,array_flip(['date','reference','note','supplier_id','store_id','container_box','exchange_rate','attachment']));
            $header = array_merge($header,['total'=>round($total+$delivery,2),'delivery'=>$delivery,'paid'=>0,'advance_deducted'=>0,'status'=>'due','received'=>0]);
            $this->db->where('id',$id)->update('purchases',$header);
            $this->db->where('purchase_id',$id)->delete('purchase_items');
            foreach ($rows as $row) { $this->db->insert('purchase_items',$row); }
            if ($this->db->trans_status() === false) { throw new RuntimeException('Purchase edit failed. No changes saved.'); }
            $this->db->trans_commit();
            return true;
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            $this->purchase_edit_error = $error->getMessage();
            return false;
        }
    }

    public function addCategory($data)
    {
        if ($this->db->insert('expensetype', $data)) {
            return true;
        }
        return false;
    }

    public function updateCategory($id, $data = null)
    {
        if ($this->db->update('expensetype', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteCategory($id)
    {
        if ($this->db->delete('expensetype', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function get_product_units($product_id)
    {
        $units = $this->Site->get_units_by_product($product_id);
        echo json_encode($units);
    }
    
    public function getPurchasePayments($purchase_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('ppayments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    
    public function getPaymentByID($id)
    {
        $this->db->select('
            ppayments.*,
        ')
        ->from('ppayments')
        ->join(
            'purchases',
            'purchases.id = ppayments.purchase_id',
            'left'
        )
        ->where('ppayments.id', $id)
        ->limit(1);
    
        $q = $this->db->get();
    
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    
        return false;
    }
    
    public function updatePayment($id, $data = [])
{
    $this->db->trans_start();

    $payment = $this->getPaymentByID($id);

    if ($payment->paid_by == 'gift_card') {
        $gc = $this->site->getGiftCard($payment->gc_no);
        $this->db->update('gift_cards',
            ['balance' => ($gc->balance + $payment->amount)],
            ['card_no' => $payment->gc_no]
        );
    }

    $this->db->update('ppayments', $data, ['id' => $id]);

    if ($data['paid_by'] == 'gift_card') {
        $gc = $this->site->getGiftCard($data['gc_no']);
        $this->db->update('gift_cards',
            ['balance' => ($gc->balance - $data['amount'])],
            ['card_no' => $data['gc_no']]
        );
    }

    $this->syncPurchasePayments($data['purchase_id']);

    $this->db->trans_complete();

    if ($this->db->trans_status() === false) {
        log_message('error', 'Payment update transaction failed. ID: ' . $id);
        return false;
    }

    log_message('info', 'Payment updated successfully. ID: ' . $id);
    return true;
}

    
    
    public function deletePayment($id)
    {
        $payment = $this->getPaymentByID($id);
        if ($payment->paid_by == 'gift_card') {
            $gc = $this->site->getGiftCard($payment->gc_no);
            $this->db->update('gift_cards', ['balance' => ($gc->balance + $payment->amount)], ['card_no' => $payment->gc_no]);
        }
        if ($this->db->delete('ppayments', ['id' => $id])) {
            $this->syncPurchasePayments($payment->purchase_id);
            return true;
        }
        return false;
    }
    
    public function getNextExpenseTypeCode()
{
    $prefix = 'ET';

    $this->db
        ->select('code')
        ->like('code', $prefix, 'after')
        ->order_by('id', 'DESC')
        ->limit(1);

    $last_record = $this->db
        ->get('tec_expensetype')
        ->row();

    $next_number = 1;

    if (
        $last_record &&
        !empty($last_record->code)
    ) {
        $number = (int) preg_replace(
            '/[^0-9]/',
            '',
            $last_record->code
        );

        $next_number = $number + 1;
    }

    /*
     * Code တူပြီးသားရှိနေလျှင်
     * နောက်နံပါတ်သို့ ဆက်တိုးမည်။
     */
    do {
        $code = $prefix . str_pad(
            $next_number,
            4,
            '0',
            STR_PAD_LEFT
        );

        $exists = $this->db
            ->where('code', $code)
            ->count_all_results('tec_expensetype');

        $next_number++;
    } while ($exists > 0);

    return $code;
}

public function addExpenseType($data)
{
    return $this->db->insert(
        'tec_expensetype',
        $data
    );
}

public function getExpenseTypeById($id)
{
    return $this->db
        ->where('id', (int) $id)
        ->get('expensetype')
        ->row();
}

public function deleteExpenseType($id)
{
    return $this->db
        ->where('id', (int) $id)
        ->delete('expensetype');
}

public function isExpenseTypeInUse($id)
{
    return $this->db
        ->where('type_id', (int) $id)
        ->count_all_results('expenses') > 0;
}

public function get_purchase_summary()
{
    $this->output->set_content_type('application/json');

    $supplier_filter = trim(
        (string) $this->input->post('supplier_filter', true)
    );

    $status_filter = trim(
        (string) $this->input->post('status_filter', true)
    );

    $received_filter = $this->input->post(
        'received_filter',
        true
    );

    $search_value = trim(
        (string) $this->input->post('search_value', true)
    );

    /*
     * The Purchase list currently uses:
     * purchases.total
     * purchases.paid
     * purchases.total - purchases.paid AS balance
     */
    $this->db
        ->select(
            "
            COUNT(purchases.id) AS records,
            COALESCE(SUM(purchases.total), 0) AS total,
            COALESCE(SUM(purchases.paid), 0) AS paid,
            COALESCE(
                SUM(purchases.total - purchases.paid),
                0
            ) AS balance
            ",
            false
        )
        ->from('purchases')
        ->join(
            'suppliers',
            'suppliers.id = purchases.supplier_id',
            'left'
        );

    /*
     * Keep the same store restriction as the Purchase list.
     */
    $store_id = (int) $this->session->userdata('store_id');

    if ($store_id > 0) {
        $this->db->where(
            'purchases.store_id',
            $store_id
        );
    }

    /*
     * Keep the same user restriction as get_purchases().
     */
    if (
        !$this->Admin &&
        !$this->session->userdata('view_right')
    ) {
        $this->db->where(
            'purchases.created_by',
            (int) $this->session->userdata('user_id')
        );
    }

    /*
     * Supplier filter must be exact.
     * Example: Zaw Win must not match Zaw Win Maung.
     */
    if ($supplier_filter !== '') {
        $this->db->where(
            'suppliers.name',
            $supplier_filter
        );
    }

    /*
     * Payment status filter.
     */
    if (
        $status_filter === 'notpaid' ||
        $status_filter === 'partial|due'
    ) {
        $this->db->group_start();
        $this->db->where(
            'purchases.status',
            'partial'
        );
        $this->db->or_where(
            'purchases.status',
            'due'
        );
        $this->db->group_end();
    } elseif ($status_filter !== '') {
        $this->db->where(
            'purchases.status',
            $status_filter
        );
    }

    /*
     * Important: received value can be "0",
     * so do not check it with empty().
     */
    if (
        $received_filter !== null &&
        $received_filter !== ''
    ) {
        $this->db->where(
            'purchases.received',
            (int) $received_filter
        );
    }

    /*
     * Match the global Purchase DataTable search.
     */
    if ($search_value !== '') {
        $this->db->group_start();

        $this->db->like(
            'purchases.id',
            $search_value
        );

        $this->db->or_like(
            'purchases.date',
            $search_value
        );

        $this->db->or_like(
            'suppliers.name',
            $search_value
        );

        $this->db->or_like(
            'purchases.total',
            $search_value
        );

        $this->db->or_like(
            'purchases.paid',
            $search_value
        );

        $this->db->or_like(
            'purchases.status',
            $search_value
        );

        $this->db->or_like(
            'purchases.received',
            $search_value
        );

        $this->db->group_end();
    }

    $summary = $this->db->get()->row_array();

    if (!$summary) {
        $summary = [
            'records' => 0,
            'total'   => 0,
            'paid'    => 0,
            'balance' => 0,
        ];
    }

    /*
     * Return numbers, not formatted strings.
     * The view formats them with KLSPOS cf().
     */
    $response = [
        'success' => true,
        'summary' => [
            'records' => (int) $summary['records'],
            'total'   => (float) $summary['total'],
            'paid'    => (float) $summary['paid'],
            'balance' => (float) $summary['balance'],
        ],
    ];

    return $this->output->set_output(
        json_encode($response)
    );
}

public function getPurchaseReceiveItems($purchase_id)
{
    $purchase_id = (int) $purchase_id;

    $q = $this->db
        ->select(
            'pi.*, '
            . 'p.code AS product_code, '
            . 'p.name AS product_name, '
            . 'u.name AS primary_unit_name, '
            . 'su.name AS secondary_unit_name',
            false
        )
        ->from('purchase_items pi')
        ->join('products p', 'p.id = pi.product_id', 'left')
        ->join('product_units u', 'u.id = pi.primary_unit', 'left')
        ->join('product_units su', 'su.id = pi.secondary_unit', 'left')
        ->where('pi.purchase_id', $purchase_id)
        ->order_by('pi.id', 'ASC')
        ->get();

    return $q->num_rows() > 0 ? $q->result() : [];
}

public function getPurchaseReceiptHistory($purchase_id)
{
    $purchase_id = (int) $purchase_id;

    if (!$this->db->table_exists('purchase_receipts')) {
        return [];
    }

    return $this->db
        ->select(
            'r.*, '
            . 'p.code AS product_code, '
            . 'p.name AS product_name, '
            . 'u.name AS primary_unit_name, '
            . 'su.name AS secondary_unit_name',
            false
        )
        ->from('purchase_receipts r')
        ->join('products p', 'p.id = r.product_id', 'left')
        ->join('product_units u', 'u.id = r.primary_unit_id', 'left')
        ->join('product_units su', 'su.id = r.secondary_unit_id', 'left')
        ->where('r.purchase_id', $purchase_id)
        ->order_by('r.received_at', 'DESC')
        ->order_by('r.id', 'DESC')
        ->get()
        ->result();
}

public function receivePurchaseItems(
    $purchase_id,
    array $receive_map,
    $received_at = null,
    $note = null,
    $user_id = null
) {
    $purchase_id = (int) $purchase_id;
    $user_id     = (int) $user_id;

    if ($purchase_id <= 0) {
        return [
            'status'  => false,
            'message' => 'Invalid purchase ID.',
        ];
    }

    if (empty($receive_map)) {
        return [
            'status'  => false,
            'message' => 'လက်ခံမည့် ပစ္စည်းအရေအတွက် ထည့်ပါ။',
        ];
    }


    // ---------------------------------------------------------
    // Required DB schema check
    // ---------------------------------------------------------
    $item_fields = $this->db->list_fields('purchase_items');

    if (
        !in_array('received_primary_qty', $item_fields, true) ||
        !in_array('received_secondary_qty', $item_fields, true)
    ) {
        return [
            'status'  => false,
            'message' =>
                'tec_purchase_items တွင် received_primary_qty နှင့် ' .
                'received_secondary_qty fields မရှိသေးပါ။',
        ];
    }

    if (!$this->db->table_exists('purchase_receipts')) {
        return [
            'status'  => false,
            'message' => 'tec_purchase_receipts table မရှိသေးပါ။',
        ];
    }


    // ---------------------------------------------------------
    // Receive date
    // ---------------------------------------------------------
    $received_at = trim((string) $received_at);

    $receive_time = strtotime($received_at);

    if ($receive_time === false) {
        $receive_time = time();
    }

    $received_at = date('Y-m-d H:i:s', $receive_time);
    $note        = trim((string) $note);


    // ---------------------------------------------------------
    // Tables with configured CI prefix
    // ---------------------------------------------------------
    $purchase_table = $this->db->dbprefix('purchases');
    $item_table     = $this->db->dbprefix('purchase_items');


    // ---------------------------------------------------------
    // Transaction
    // ---------------------------------------------------------
    $this->db->trans_begin();


    // Lock purchase header
    $purchase = $this->db
        ->query(
            "SELECT * FROM `{$purchase_table}` " .
            "WHERE id = ? LIMIT 1 FOR UPDATE",
            [$purchase_id]
        )
        ->row();

    if (!$purchase) {

        $this->db->trans_rollback();

        return [
            'status'  => false,
            'message' => 'Purchase not found.',
        ];
    }


    if ((int) $purchase->received === 1) {

        $this->db->trans_rollback();

        return [
            'status'  => false,
            'message' =>
                'ဒီဘောင်ချာမှ ပစ္စည်းအားလုံး လက်ခံပြီးဖြစ်ပါသည်။',
        ];
    }


    $received_any  = false;
    $received_rows = [];


    // =========================================================
    // RECEIVE EACH ITEM
    // =========================================================
    foreach ($receive_map as $purchase_item_id => $receive_qty) {

        $purchase_item_id = (int) $purchase_item_id;

        if (
            $purchase_item_id <= 0 ||
            !is_numeric($receive_qty)
        ) {
            continue;
        }

        $receive_qty = (float) $receive_qty;

        if ($receive_qty <= 0) {
            continue;
        }


        // -----------------------------------------------------
        // Lock purchase item row
        // -----------------------------------------------------
        $item = $this->db
            ->query(
                "SELECT * FROM `{$item_table}` " .
                "WHERE id = ? AND purchase_id = ? " .
                "LIMIT 1 FOR UPDATE",
                [$purchase_item_id, $purchase_id]
            )
            ->row();

        if (!$item) {

            $this->db->trans_rollback();

            return [
                'status'  => false,
                'message' =>
                    'Purchase item not found. ID: ' .
                    $purchase_item_id,
            ];
        }


        // -----------------------------------------------------
        // Remaining primary quantity
        // -----------------------------------------------------
        $ordered_primary = isset($item->primary_qty)
            ? (float) $item->primary_qty
            : 0;

        $already_received = isset($item->received_primary_qty)
            ? (float) $item->received_primary_qty
            : 0;

        $remaining = $ordered_primary - $already_received;

        if ($remaining < 0) {
            $remaining = 0;
        }


        if ($receive_qty > ($remaining + 0.000001)) {

            $this->db->trans_rollback();

            return [
                'status'  => false,
                'message' =>
                    'လက်ခံမည့်အရေအတွက်သည် ကျန်ရှိသည့်အရေအတွက် ' .
                    number_format($remaining, 2, '.', '') .
                    ' ထက် မကျော်ရပါ။',
            ];
        }


        // -----------------------------------------------------
        // Secondary quantity proportion
        // -----------------------------------------------------
        $ordered_secondary = isset($item->secondary_qty)
            ? (float) $item->secondary_qty
            : 0;

        $receive_secondary = 0;

        if (
            $ordered_primary > 0 &&
            $ordered_secondary > 0
        ) {
            $receive_secondary =
                ($ordered_secondary / $ordered_primary)
                * $receive_qty;
        }


        // -----------------------------------------------------
        // Delivery cost proportion
        // -----------------------------------------------------
        $item_delivery = isset($item->delivery)
            ? (float) $item->delivery
            : 0;

        $receive_delivery = 0;

        if (
            $ordered_primary > 0 &&
            $item_delivery > 0
        ) {
            $receive_delivery =
                ($item_delivery / $ordered_primary)
                * $receive_qty;
        }


        // -----------------------------------------------------
        // Add ONLY newly received qty to stock
        // -----------------------------------------------------
        $stock_saved = $this->setStoreQuantity(
            $purchase_id,
            (int) $item->product_id,
            (int) $purchase->store_id,
            $receive_qty,
            $receive_secondary,
            isset($item->net_unit_cost)
                ? (float) $item->net_unit_cost
                : 0,
            !empty($item->secondary_unit)
                ? (int) $item->secondary_unit
                : null,
            !empty($item->primary_unit)
                ? (int) $item->primary_unit
                : null,
            $receive_delivery
        );


        if (!$stock_saved) {

            $this->db->trans_rollback();

            return [
                'status'  => false,
                'message' =>
                    'Stock update failed for product ID ' .
                    (int) $item->product_id . '.',
            ];
        }


        // -----------------------------------------------------
        // Update received qty in purchase item
        // -----------------------------------------------------
        $new_received_primary =
            $already_received + $receive_qty;

        if ($new_received_primary > $ordered_primary) {
            $new_received_primary = $ordered_primary;
        }


        $already_received_secondary =
            isset($item->received_secondary_qty)
                ? (float) $item->received_secondary_qty
                : 0;

        $new_received_secondary =
            $already_received_secondary
            + $receive_secondary;

        if (
            $ordered_secondary > 0 &&
            $new_received_secondary > $ordered_secondary
        ) {
            $new_received_secondary = $ordered_secondary;
        }


        $updated_item = $this->db->update(
            'purchase_items',
            [
                'received_primary_qty' =>
                    $new_received_primary,

                'received_secondary_qty' =>
                    $new_received_secondary,
            ],
            [
                'id' => $purchase_item_id,
            ]
        );


        if (!$updated_item) {

            $this->db->trans_rollback();

            return [
                'status'  => false,
                'message' =>
                    'Purchase item received quantity update failed.',
            ];
        }


        // -----------------------------------------------------
        // Receive history
        // -----------------------------------------------------
        $receipt_data = [

            'purchase_id' =>
                $purchase_id,

            'purchase_item_id' =>
                $purchase_item_id,

            'product_id' =>
                (int) $item->product_id,

            'store_id' =>
                (int) $purchase->store_id,

            'received_primary_qty' =>
                $receive_qty,

            'received_secondary_qty' =>
                $receive_secondary,

            'primary_unit_id' =>
                !empty($item->primary_unit)
                    ? (int) $item->primary_unit
                    : null,

            'secondary_unit_id' =>
                !empty($item->secondary_unit)
                    ? (int) $item->secondary_unit
                    : null,

            'allocated_delivery' =>
                $receive_delivery,

            'received_at' =>
                $received_at,

            'received_by' =>
                $user_id > 0
                    ? $user_id
                    : null,

            'note' =>
                $note !== ''
                    ? $note
                    : null,
        ];

        if ($this->db->field_exists('stock_batch_id', 'purchase_receipts')) {
            $receipt_data['stock_batch_id'] = $this->last_receive_batch_id;
        }


        if (
            !$this->db->insert(
                'purchase_receipts',
                $receipt_data
            )
        ) {

            $db_error = $this->db->error();

            $this->db->trans_rollback();

            log_message(
                'error',
                'Purchase receipt history insert failed => ' .
                print_r($db_error, true)
            );

            return [
                'status'  => false,
                'message' =>
                    'Purchase receipt history save failed.',
            ];
        }


        $receipt_id = $this->db->insert_id();
        if ($this->db->field_exists('purchase_receipt_id', 'stock_movements')) {
            $this->db->where('batch_id', $this->last_receive_batch_id)
                ->where('movement_type', 'purchase')
                ->update('stock_movements', ['purchase_receipt_id' => $receipt_id]);
        }
        $received_any = true;

        $received_rows[] = [
            'purchase_item_id' =>
                $purchase_item_id,

            'product_id' =>
                (int) $item->product_id,

            'received_qty' =>
                $receive_qty,

            'remaining_qty' =>
                max(
                    0,
                    $ordered_primary
                    - $new_received_primary
                ),
        ];
    }


    if (!$received_any) {

        $this->db->trans_rollback();

        return [
            'status'  => false,
            'message' =>
                'လက်ခံမည့် ပစ္စည်းအရေအတွက် တစ်ခုခု ထည့်ပါ။',
        ];
    }


    // =========================================================
    // Recalculate purchase receive status
    // =========================================================
    $summary = $this->db
        ->select(
            'COALESCE(SUM(primary_qty), 0) AS ordered_qty, ' .
            'COALESCE(SUM(received_primary_qty), 0) AS received_qty',
            false
        )
        ->where(
            'purchase_id',
            $purchase_id
        )
        ->get('purchase_items')
        ->row();


    $ordered_total = $summary
        ? (float) $summary->ordered_qty
        : 0;

    $received_total = $summary
        ? (float) $summary->received_qty
        : 0;


    if ($received_total <= 0) {

        $received_status = 0;

    } elseif (
        $received_total >=
        ($ordered_total - 0.000001)
    ) {

        $received_status = 1;

    } else {

        $received_status = 2;
    }


    $updated_purchase = $this->db->update(
        'purchases',
        [
            'received' => $received_status,
        ],
        [
            'id' => $purchase_id,
        ]
    );


    if (!$updated_purchase) {

        $this->db->trans_rollback();

        return [
            'status'  => false,
            'message' =>
                'Purchase received status update failed.',
        ];
    }


    // =========================================================
    // Final transaction check
    // =========================================================
    if ($this->db->trans_status() === false) {

        $db_error = $this->db->error();

        $this->db->trans_rollback();

        log_message(
            'error',
            'Partial receive transaction failed => ' .
            print_r($db_error, true)
        );

        return [
            'status'  => false,
            'message' =>
                'Database transaction failed.',
        ];
    }


    $this->db->trans_commit();


    log_message(
        'info',
        'Partial purchase receive success. Purchase ID: ' .
        $purchase_id .
        ' | Status: ' .
        $received_status .
        ' | User ID: ' .
        $user_id .
        ' | Rows: ' .
        json_encode($received_rows)
    );


    return [
        'status'          => true,
        'received_status' => $received_status,
        'ordered_qty'     => $ordered_total,
        'received_qty'    => $received_total,
        'rows'            => $received_rows,
    ];
}

public function setStoreQuantity(
    $purchase_id,
    $product_id,
    $store_id,
    $base_qty = 0,
    $secondary_qty = 0,
    $unit_cost = 0,
    $secondary_unit_id = null,
    $primary_unit_id = null,
    $delivery_cost = 0
) {
    $now = date('Y-m-d H:i:s');

    // =========================================================
    // Normalize values
    // =========================================================
    $purchase_id = (int) $purchase_id;
    $product_id  = (int) $product_id;
    $store_id    = (int) $store_id;

    /*
     * IMPORTANT:
     *
     * $base_qty ဆိုတဲ့ parameter name က အဟောင်းဖြစ်လို့
     * မပြောင်းဘဲထားထားပါတယ်။
     *
     * တကယ်တမ်း controller ကနေ ဝင်လာတာက
     * Selected Purchase Unit Qty ဖြစ်ပါတယ်။
     *
     * Example:
     * 2 ဖာ ဝယ်ရင် ဒီနေရာမှာ 2 ဝင်လာမယ်။
     */
    $primary_qty = is_numeric($base_qty)
        ? (float) $base_qty
        : 0;

    $secondary_qty = is_numeric($secondary_qty)
        ? (float) $secondary_qty
        : 0;

    $unit_cost = is_numeric($unit_cost)
        ? (float) $unit_cost
        : 0;

    $delivery_cost = is_numeric($delivery_cost)
        ? (float) $delivery_cost
        : 0;

    $primary_unit_id = !empty($primary_unit_id)
        ? (int) $primary_unit_id
        : null;

    $secondary_unit_id = !empty($secondary_unit_id)
        ? (int) $secondary_unit_id
        : null;


    if ($product_id <= 0 || $store_id <= 0 || $primary_qty <= 0) {

        log_message(
            'error',
            'setStoreQuantity invalid data => ' .
            print_r([
                'purchase_id'    => $purchase_id,
                'product_id'     => $product_id,
                'store_id'       => $store_id,
                'primary_qty'    => $primary_qty,
                'primary_unit'   => $primary_unit_id
            ], true)
        );

        return false;
    }


    // =========================================================
    // Get Product Base Unit
    // =========================================================
    $product = $this->db
        ->select('id, base_unit_id')
        ->where('id', $product_id)
        ->get('tec_products')
        ->row();


    if (!$product) {

        log_message(
            'error',
            'setStoreQuantity: Product not found => ' . $product_id
        );

        return false;
    }


    $product_base_unit_id = !empty($product->base_unit_id)
        ? (int) $product->base_unit_id
        : null;


    // =========================================================
    // Convert Selected Purchase Qty -> Base Qty
    // =========================================================
    $qty_base = $primary_qty;

    /*
     * unit_convert means:
     *
     * 1 selected purchase unit = X base units
     *
     * Example:
     * 1 ဖာ = 1000 လုံး
     *
     * unit_convert = 1000
     */
    $unit_convert = 1;


    // Selected unit is NOT product base unit
    if (
        $primary_unit_id &&
        $product_base_unit_id &&
        $primary_unit_id != $product_base_unit_id
    ) {

        // IMPORTANT:
        // Filter with BOTH product_id AND selected unit_id
        $conversion = $this->db
            ->select('unit_id, operation_value, operator')
            ->where('product_id', $product_id)
            ->where('unit_id', $primary_unit_id)
            ->get('tec_product_unit_conversions')
            ->row();


        if ($conversion) {

            $value = is_numeric($conversion->operation_value)
                ? (float) $conversion->operation_value
                : 1;

            $operator = !empty($conversion->operator)
                ? trim($conversion->operator)
                : '*';


            if ($value <= 0) {
                $value = 1;
            }


            if ($operator === '/') {

                /*
                 * Example:
                 * selected qty / operation value
                 */
                $qty_base = $primary_qty / $value;

                /*
                 * Base units per selected unit
                 */
                $unit_convert = 1 / $value;

            } else {

                /*
                 * Normal:
                 *
                 * 2 ဖာ × 1000 = 2000 လုံး
                 */
                $qty_base = $primary_qty * $value;

                $unit_convert = $value;
            }

        } else {

            /*
             * Conversion not found.
             * Do NOT use another random conversion.
             */
            log_message(
                'error',
                'Unit conversion not found => ' .
                print_r([
                    'product_id'      => $product_id,
                    'primary_unit_id' => $primary_unit_id,
                    'base_unit_id'    => $product_base_unit_id
                ], true)
            );

            /*
             * Fallback 1:1
             */
            $qty_base = $primary_qty;
            $unit_convert = 1;
        }
    }


    // =========================================================
    // Landed Cost Per Base Unit
    // =========================================================
    /*
     * Purchase item cost + this item's transportation cost
     * are both included in stock cost.
     *
     * Example:
     * 1 ဖာ = 24 ခု
     * Qty = 1 ဖာ
     * Cost = 20,000
     * Delivery = 10,000
     * Base Qty = 24
     *
     * cost_per_base = (20,000 + 10,000) / 24
     *               = 1,250
     */
    $item_cost_total = $primary_qty * $unit_cost;
    $landed_cost_total = $item_cost_total + $delivery_cost;

    if ($qty_base > 0) {
        $cost_per_base = $landed_cost_total / $qty_base;
    } else {
        $cost_per_base = $unit_cost;
    }


    // =========================================================
    // Secondary Unit
    // =========================================================
    /*
     * Easy Purchase View က secondary unit မသုံးဘူးဆိုရင်
     * qty_secondary = 0
     * secondary_unit_id = null
     *
     * Primary Qty ကို Secondary Qty အဖြစ် auto convert
     * မလုပ်တော့ပါဘူး။
     */
    if (!$secondary_unit_id) {

        $secondary_qty = 0;
        $secondary_unit_id = null;
    }


    // =========================================================
    // Debug
    // =========================================================
    log_message(
        'debug',
        'SET STORE QUANTITY => ' .
        print_r([
            'purchase_id'         => $purchase_id,
            'product_id'          => $product_id,
            'store_id'            => $store_id,

            'primary_qty'         => $primary_qty,
            'primary_unit_id'     => $primary_unit_id,

            'product_base_unit'   => $product_base_unit_id,

            'unit_convert'        => $unit_convert,
            'qty_base'            => $qty_base,

            'secondary_qty'       => $secondary_qty,
            'secondary_unit_id'   => $secondary_unit_id,

            'selected_unit_cost'  => $unit_cost,
            'item_cost_total'     => $item_cost_total,
            'landed_cost_total'   => $landed_cost_total,
            'cost_per_base'       => $cost_per_base,

            'delivery_cost'       => $delivery_cost
        ], true)
    );


    // =========================================================
    // Stock Batch
    // =========================================================
    $batch_data = [

        'product_id' =>
            $product_id,

        'store_id' =>
            $store_id,

        'batch_no' =>
            $purchase_id,

        /*
         * Actual quantity in PRODUCT BASE UNIT
         *
         * Example:
         * 2 ဖာ = 2000 လုံး
         */
        'qty_base' =>
            $qty_base,

        /*
         * Original purchase qty
         *
         * Example:
         * 2 ဖာ
         */
        'qty_primary' =>
            $primary_qty,

        'qty_secondary' =>
            $secondary_qty,

        'primary_unit_id' =>
            $primary_unit_id,

        'secondary_unit_id' =>
            $secondary_unit_id,

        /*
         * Cost per actual base unit
         */
        'cost_per_base' =>
            $cost_per_base,

        'delivery' =>
            $delivery_cost,

        'created_at' =>
            $now,

        'purchase_id' =>
            $purchase_id,

        /*
         * 1 purchase unit = X base units
         */
        'unit_convert' =>
            $unit_convert,
    ];


    if (
        !$this->db->insert(
            'tec_stock_batches',
            $batch_data
        )
    ) {

        log_message(
            'error',
            'Stock batch insert failed => ' .
            print_r($this->db->error(), true)
        );

        return false;
    }


    $batch_id = (int) $this->db->insert_id();
    $this->last_receive_batch_id = $batch_id;
    
    // =========================================================
// Update current product base cost
// =========================================================
$this->db
    ->where('id', $product_id)
    ->update(
        'tec_products',
        [
            'cost' => $cost_per_base
        ]
    );

log_message(
    'debug',
    'Product base cost updated => ' .
    print_r([
        'product_id'    => $product_id,
        'cost_per_base' => $cost_per_base
    ], true)
);


    // =========================================================
    // Stock Movement
    // =========================================================
    $movement_data = [

        'product_id' =>
            $product_id,

        'from_store_id' =>
            null,

        'to_store_id' =>
            $store_id,

        'batch_id' =>
            $batch_id,

        'movement_type' =>
            'purchase',

        /*
         * Actual base stock movement
         */
        'qty_base' =>
            $qty_base,

        /*
         * Original purchase qty
         */
        'qty_primary' =>
            $primary_qty,

        'qty_secondary' =>
            $secondary_qty,

        'created_at' =>
            $now,

        'purchase_id' =>
            $purchase_id,
    ];


    if (
        !$this->db->insert(
            'tec_stock_movements',
            $movement_data
        )
    ) {

        log_message(
            'error',
            'Stock movement insert failed => ' .
            print_r($this->db->error(), true)
        );

        return false;
    }


    log_message(
        'debug',
        'setStoreQuantity SUCCESS => ' .
        print_r([
            'purchase_id' => $purchase_id,
            'batch_id'    => $batch_id,
            'product_id'  => $product_id,
            'qty_primary' => $primary_qty,
            'qty_base'    => $qty_base
        ], true)
    );


    return true;
}

/*
|--------------------------------------------------------------------------
| Purchases_model.php
| Replace getAllPurchaseItems() with this version
|--------------------------------------------------------------------------
|
| Fixes wrong Unit Conversion shown in Purchase View.
|
| OLD BUG:
|   uc.unit_id = u.id
|
| That can match a conversion row belonging to ANOTHER product that uses
| the same unit.
|
| FIX:
|   Match conversion with BOTH product_id AND selected primary_unit.
|--------------------------------------------------------------------------
*/
public function getAllPurchaseItems($purchase_id)
{
    $purchase_id = (int) $purchase_id;

    $this->db->select("
        purchase_items.*,

        products.code AS product_code,
        products.name AS product_name,
        products.base_unit_id AS base_unit_id,

        pu.name AS primary_unit_name,
        bu.name AS base_unit_name,

        uc.operator AS unit_convert_operator,
        uc.operation_value AS unit_convert_raw,

        CASE
            WHEN tec_purchase_items.primary_unit = tec_products.base_unit_id
                THEN 1

            WHEN uc.operator = '/'
                 AND uc.operation_value > 0
                THEN (1 / uc.operation_value)

            WHEN uc.operation_value > 0
                THEN uc.operation_value

            ELSE 1
        END AS unit_convert
    ", false);

    $this->db->from('purchase_items');

    $this->db->join(
        'products',
        'products.id = purchase_items.product_id',
        'left'
    );

    $this->db->join(
        'product_units pu',
        'pu.id = purchase_items.primary_unit',
        'left'
    );

    /*
     * IMPORTANT:
     * Conversion belongs to BOTH this product and this selected unit.
     */
    $this->db->join(
        'product_unit_conversions uc',
        'uc.product_id = purchase_items.product_id
         AND uc.unit_id = purchase_items.primary_unit',
        'left'
    );

    $this->db->join(
        'product_units bu',
        'bu.id = products.base_unit_id',
        'left'
    );

    $this->db->where(
        'purchase_items.purchase_id',
        $purchase_id
    );

    $this->db->order_by(
        'purchase_items.id',
        'ASC'
    );

    $q = $this->db->get();

    if (!$q) {
        log_message(
            'error',
            'getAllPurchaseItems DB ERROR => ' .
            print_r($this->db->error(), true)
        );

        return false;
    }

    return $q->num_rows() > 0
        ? $q->result()
        : false;
}

    public function getPurchaseItemByID($id)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return false;
        }

        $q = $this->db
            ->where('id', $id)
            ->get('purchase_items', 1);

        return $q->num_rows() ? $q->row() : false;
    }

    public function getPurchaseReceiptByID($id)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return false;
        }

        $q = $this->db
            ->where('id', $id)
            ->get('purchase_receipts', 1);

        return $q->num_rows() ? $q->row() : false;
    }

    public function getPurchaseReceiveHistory($purchase_id, $product_id)
    {
        if (!$this->db->table_exists('purchase_receipts')) {
            return [];
        }

        return $this->db
            ->select('purchase_receipts.*, received_primary_qty AS quantity', false)
            ->where('purchase_id', (int) $purchase_id)
            ->where('product_id', (int) $product_id)
            ->order_by('received_at', 'DESC')
            ->order_by('id', 'DESC')
            ->get('purchase_receipts')
            ->result();
    }

    public function deletePurchaseReceipt($receipt_id)
    {
        return $this->correctPurchaseReceipt($receipt_id, 0, true);
    }

    public function updatePurchaseReceipt($receipt_id, $new_qty)
    {
        if (!is_numeric($new_qty) || !is_finite((float) $new_qty) || (float) $new_qty <= 0) {
            return ['status' => false, 'message' => 'Invalid receipt quantity.'];
        }
        return $this->correctPurchaseReceipt($receipt_id, (float) $new_qty, false);
    }

    private function correctPurchaseReceipt($receipt_id, $new_qty, $delete)
    {
        $receipt_id = (int) $receipt_id;
        $receipt = $this->getPurchaseReceiptByID($receipt_id);
        if (!$receipt) {
            return ['status' => false, 'message' => 'Receive history not found.'];
        }
        if (!$this->db->field_exists('stock_batch_id', 'purchase_receipts')
            || !$this->db->field_exists('purchase_receipt_id', 'stock_movements')) {
            return ['status' => false, 'message' => 'Receipt stock-link schema upgrade is required.'];
        }
        $epsilon = 0.00011;
        $this->db->trans_begin();
        try {
            // Same lock order as receivePurchaseItems: purchase, then item/batch.
            $header = $this->db->query('SELECT * FROM '.$this->db->dbprefix('purchases').' WHERE id = ? FOR UPDATE',
                [(int) $receipt->purchase_id])->row();
            $receipt = $this->db->query('SELECT * FROM '.$this->db->dbprefix('purchase_receipts').' WHERE id = ? FOR UPDATE',
                [$receipt_id])->row();
            if (!$header || !$receipt || (int)$receipt->purchase_id !== (int)$header->id) {
                throw new RuntimeException('Purchase or receipt no longer exists.');
            }
            $item = $this->db->query('SELECT * FROM '.$this->db->dbprefix('purchase_items').' WHERE id = ? AND purchase_id = ? FOR UPDATE',
                [(int)$receipt->purchase_item_id, (int)$receipt->purchase_id])->row();
            if (!$item || (int)$item->product_id !== (int)$receipt->product_id) {
                throw new RuntimeException('The original purchase item could not be identified.');
            }
            $old_qty = (float)$receipt->received_primary_qty;
            $difference = $new_qty - $old_qty;
            $total = (float)$item->received_primary_qty + $difference;
            if ($old_qty <= 0 || $total < -$epsilon || $total > (float)$item->primary_qty + $epsilon) {
                throw new RuntimeException('Receipt quantity exceeds the ordered quantity.');
            }
            if (!$delete && abs($difference) < 0.000001) {
                $this->db->trans_rollback();
                return ['status'=>true, 'message'=>'Receipt quantity is unchanged.', 'received_qty'=>$total];
            }
            $batchTable = $this->db->dbprefix('stock_batches');
            if (!empty($receipt->stock_batch_id)) {
                $batches = $this->db->query('SELECT * FROM '.$batchTable.' WHERE id = ? FOR UPDATE',
                    [(int)$receipt->stock_batch_id])->result();
            } else {
                // Legacy records are accepted only when there is one exact candidate.
                // Do not guess by row order when several receipts have the same quantity.
                $batches = $this->db->query('SELECT * FROM '.$batchTable.' WHERE purchase_id = ? AND product_id = ? AND store_id = ? AND ABS(qty_primary - ?) < 0.00011 AND COALESCE(primary_unit_id,0) = ? AND COALESCE(secondary_unit_id,0) = ? FOR UPDATE',
                    [(int)$receipt->purchase_id,(int)$receipt->product_id,(int)$receipt->store_id,$old_qty,
                     (int)$receipt->primary_unit_id,(int)$receipt->secondary_unit_id])->result();
            }
            if (count($batches) !== 1) {
                throw new RuntimeException('This legacy receipt cannot be matched to a unique stock batch. No changes were saved.');
            }
            $batch = $batches[0];
            if ((int)$batch->purchase_id !== (int)$receipt->purchase_id
                || (int)$batch->product_id !== (int)$receipt->product_id
                || (int)$batch->store_id !== (int)$receipt->store_id
                || (int)$batch->primary_unit_id !== (int)$receipt->primary_unit_id
                || (int)$batch->secondary_unit_id !== (int)$receipt->secondary_unit_id) {
                throw new RuntimeException('Receipt and stock batch do not match.');
            }
            if ($this->db->where('stock_batch_id',(int)$batch->id)->where('id !=',$receipt_id)
                ->count_all_results('purchase_receipts') > 0) {
                throw new RuntimeException('This stock batch is linked to another receipt. No changes were saved.');
            }
            if ($this->db->where('batch_id',(int)$batch->id)->count_all_results('cogs_logs') > 0) {
                throw new RuntimeException('Stock from this receipt has been used in a sale. Reverse the dependent transaction first.');
            }
            $movements = $this->db->query('SELECT * FROM '.$this->db->dbprefix('stock_movements').' WHERE batch_id = ? FOR UPDATE',
                [(int)$batch->id])->result();
            $original = [];
            foreach ($movements as $movement) {
                if ($movement->movement_type === 'purchase') { $original[] = $movement; }
                elseif ($movement->movement_type !== 'adjustment' || (int)$movement->purchase_receipt_id !== $receipt_id) {
                    throw new RuntimeException('This stock batch has transfers or other stock changes. No changes were saved.');
                }
            }
            if (count($original) !== 1 || (float)$original[0]->qty_primary <= 0) {
                throw new RuntimeException('Original receipt stock movement is unavailable.');
            }
            if (!empty($original[0]->purchase_receipt_id) && (int)$original[0]->purchase_receipt_id !== $receipt_id) {
                throw new RuntimeException('Original stock movement belongs to another receipt.');
            }
            $factor = (float)$original[0]->qty_base / (float)$original[0]->qty_primary;
            $secondary_ratio = (float)$receipt->received_secondary_qty / $old_qty;
            if ($factor <= 0 || abs((float)$batch->qty_base - $old_qty*$factor) > $epsilon
                || abs((float)$batch->qty_secondary - (float)$receipt->received_secondary_qty) > $epsilon
                || abs((float)$batch->qty_primary - $old_qty) > $epsilon) {
                throw new RuntimeException('Stock balance has changed since receipt. No changes were saved.');
            }
            // Keep the original unit conversion and landed unit cost, even if current unit settings changed.
            $new_base = round($new_qty*$factor,4);
            $new_secondary = round($new_qty*$secondary_ratio,2);
            $new_delivery = round((float)$receipt->allocated_delivery*$new_qty/$old_qty,4);
            $secondary_total = (float)$item->received_secondary_qty
                + $new_secondary - (float)$receipt->received_secondary_qty;
            if ($secondary_total < -$epsilon || $secondary_total > (float)$item->secondary_qty + $epsilon) {
                throw new RuntimeException('Secondary receipt quantity exceeds the ordered quantity.');
            }
            $this->db->where('id',(int)$batch->id)->update('stock_batches', [
                'qty_base'=>$new_base, 'qty_primary'=>$new_qty, 'qty_secondary'=>$new_secondary,
                'delivery'=>$new_delivery, 'updated_at'=>date('Y-m-d H:i:s')]);
            $this->db->insert('stock_movements', [
                'batch_id'=>(int)$batch->id, 'product_id'=>(int)$receipt->product_id,
                'purchase_id'=>(int)$receipt->purchase_id, 'purchase_receipt_id'=>$receipt_id,
                'movement_type'=>'adjustment', 'from_store_id'=>$difference<0?(int)$receipt->store_id:null,
                'to_store_id'=>$difference>0?(int)$receipt->store_id:null,
                'qty_base'=>$new_base-(float)$batch->qty_base, 'qty_primary'=>$difference,
                'qty_secondary'=>$new_secondary-(float)$batch->qty_secondary, 'created_at'=>date('Y-m-d H:i:s')]);
            $this->db->where('id',(int)$item->id)->update('purchase_items', [
                'received_primary_qty'=>max(0,$total), 'received_secondary_qty'=>max(0,$secondary_total)]);
            if ($delete) { $this->db->where('id',$receipt_id)->delete('purchase_receipts'); }
            else {
                $this->db->where('id',$receipt_id)->update('purchase_receipts', [
                    'stock_batch_id'=>(int)$batch->id, 'received_primary_qty'=>$new_qty,
                    'received_secondary_qty'=>$new_secondary, 'allocated_delivery'=>$new_delivery]);
            }
            $this->syncPurchaseReceivedStatus((int)$receipt->purchase_id);
            $latest = $this->db->where('product_id',(int)$receipt->product_id)->where('qty_base >',0)
                ->order_by('id','DESC')->get('stock_batches',1)->row();
            $this->db->where('id',(int)$receipt->product_id)->update('products',['cost'=>$latest?(float)$latest->cost_per_base:0]);
            if ($this->db->trans_status() === false) { throw new RuntimeException('Receipt correction transaction failed.'); }
            $this->db->trans_commit();
            return ['status'=>true, 'message'=>$delete?'Receipt deleted and stock reversed.':'Receipt and stock updated.', 'received_qty'=>max(0,$total)];
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            return ['status'=>false, 'message'=>$error->getMessage()];
        }
    }
    private function syncPurchaseReceivedStatus($purchase_id)
    {
        $summary = $this->db->select('COALESCE(SUM(primary_qty), 0) AS ordered_qty, COALESCE(SUM(received_primary_qty), 0) AS received_qty', false)
            ->where('purchase_id', (int) $purchase_id)->get('purchase_items')->row();
        $ordered = (float) $summary->ordered_qty;
        $received = (float) $summary->received_qty;
        $status = $received <= 0 ? 0 : ($received >= ($ordered - 0.000001) ? 1 : 2);
        $this->db->where('id', (int) $purchase_id)->update('purchases', ['received' => $status]);
    }

    public function deletePurchaseItem($item_id)
    {
        // A line changes invoice totals and delivery allocation. Use the complete
        // purchase edit flow instead of deleting one row and leaving stale totals.
        return ['status'=>false, 'message'=>'Remove items through Purchase Edit so invoice totals and delivery costs are recalculated. Reverse receipts and payments first.'];
    }

}
