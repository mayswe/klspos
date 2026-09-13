<?php
 if (!defined('BASEPATH')) {
     exit('No direct script access allowed');
 }

class Sales_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPayment($data = [])
    {
        if ($this->db->insert('payments', $data)) {
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCard($data['gc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['gc_no']]);
            }
            $this->syncSalePayments($data['sale_id']);
            return true;
        }
        return false;
    }

    public function deleteInvoice($id)
    {
        $this->db->trans_start(); // Start transaction
    
        $osale  = $this->getSaleByID($id);
        $oitems = $this->getAllSaleItems($id);
    
        foreach ($oitems as $oitem) {
            // Get product info (for base/secondary unit ids)
            $product = $this->site->getProductByID($oitem->product_id, $osale->store_id);
    
            // Get all COGS logs for this sale item
            $cogs_logs = $this->db->get_where('cogs_logs', [
                'sale_id'      => $id,
                'sale_item_id' => $oitem->id
            ])->result();
    
            foreach ($cogs_logs as $log) {
                // COGS contains the exact pair deducted for this sale line.
                $this->db
                    ->set('qty_base', 'qty_base + ' . (float) $log->qty_base, false)
                    ->set('qty_secondary', 'qty_secondary + ' . (float) $log->qty_secondary, false)
                    ->where('id', $log->batch_id)
                    ->update('stock_batches');
            }
        }
    
        // Cleanup related records (after restoring stock)
        $this->db->delete('cogs_logs', ['sale_id' => $id]);
        $this->db->delete('sale_items', ['sale_id' => $id]);
        $this->db->delete('payments', ['sale_id' => $id]);
        $this->db->delete('sales', ['id' => $id]);
    
        $this->db->trans_complete(); // Commit or rollback
    
        return $this->db->trans_status(); // true if success, false if rolled back
    }


    public function deleteOpenedSale($id)
    {
        if ($this->db->delete('suspended_items', ['suspend_id' => $id]) && $this->db->delete('suspended_sales', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $payment = $this->getPaymentByID($id);
        if ($payment->paid_by == 'gift_card') {
            $gc = $this->site->getGiftCard($payment->gc_no);
            $this->db->update('gift_cards', ['balance' => ($gc->balance + $payment->amount)], ['card_no' => $payment->gc_no]);
        }
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->syncSalePayments($payment->sale_id);
            return true;
        }
        return false;
    }

    public function getAllSaleItems($sale_id)
    {
        $j = "(SELECT id, code, name, tax_method from {$this->db->dbprefix('products')}) P";
        $this->db->select("sale_items.*,
            (CASE WHEN {$this->db->dbprefix('sale_items')}.product_code IS NULL THEN {$this->db->dbprefix('products')}.code ELSE {$this->db->dbprefix('sale_items')}.product_code END) as product_code,
            (CASE WHEN {$this->db->dbprefix('sale_items')}.product_name IS NULL THEN {$this->db->dbprefix('products')}.name ELSE {$this->db->dbprefix('sale_items')}.product_name END) as product_name,
            {$this->db->dbprefix('products')}.tax_method as tax_method", false)
        ->join('products', 'products.id=sale_items.product_id', 'left outer')
        ->order_by('sale_items.id');
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
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

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

   

    public function getSalePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function syncSalePayments($id)
    {
        $sale     = $this->getSaleByID($id);
        $payments = $this->getSalePayments($id);
        $paid     = 0;
        if ($payments) {
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
        }
        $status = $paid <= 0 ? 'due' : ($sale->grand_total <= $paid ? 'paid' : 'partial');
        if ($this->db->update('sales', ['paid' => $paid, 'status' => $status], ['id' => $id])) {
            return true;
        }

        return false;
    }

    public function updatePayment($id, $data = [])
    {
        $payment = $this->getPaymentByID($id);
        if ($payment->paid_by == 'gift_card') {
            $gc = $this->site->getGiftCard($payment->gc_no);
            $this->db->update('gift_cards', ['balance' => ($gc->balance + $payment->amount)], ['card_no' => $payment->gc_no]);
        }
        if ($this->db->update('payments', $data, ['id' => $id])) {
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCard($data['gc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['gc_no']]);
            }
            $this->syncSalePayments($data['sale_id']);
            return true;
        }
        return false;
    }

    public function updateStatus($id, $status)
    {
        if ($this->db->update('sales', ['status' => $status], ['id' => $id])) {
            return true;
        }
        return false;
    }




 public function getSaleByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

public function getProductStock($product_id)
{
    $row = $this->db->select('quantity')->get_where('products', ['id' => $product_id])->row();
    return $row ? (int)$row->quantity : 0;
}

public function markPreorderDelivered($sale_id)
{
    $this->db->where('id', $sale_id)->update('sales', ['is_preorder' => 0]);
}

public function reduceStock($product_id, $qty)
{
    $this->db->set('quantity', 'quantity-'.$qty, FALSE)
             ->where('id', $product_id)
             ->update('products');
}

public function get_unit_ratio($product_id, $unit_id) {
    $row = $this->db->get_where('tec_product_unit_conversions', [
        'product_id' => $product_id,
        'unit_id'    => $unit_id
    ])->row();

    if (!$row) return 1;  // fallback

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

}
