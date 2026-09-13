<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addSupplier($data = [])
    {
        if ($this->db->insert('suppliers', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function deleteSupplier($id)
    {
        if ($this->db->delete('suppliers', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteAdvance($id)
    {
        if ($this->db->delete('supplier_advances', ['id' => $id])) {
            return true;
        }
        return false;
    }


    public function getSupplierByID($id)
    {
        $q = $this->db->get_where('suppliers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateSupplier($id, $data = [])
    {
        if ($this->db->update('suppliers', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function update_advance($id, $data)
    {
        return $this->db->update('supplier_advances', $data, ['id' => $id]);
    }

    public function get_advance_by_id($id)
    {
        return $this->db->get_where('supplier_advances', ['id' => $id])->row();
    }


    public function get_all_advances()
    {
        $this->db->select('supplier_advances.*, suppliers.name as supplier_name')
            ->from('supplier_advances')
            ->join('suppliers', 'suppliers.id = supplier_advances.supplier_id')
            ->order_by('supplier_advances.id', 'desc');
        $q = $this->db->get();
        return $q->result();
    }

    public function add_advance($data)
    {
        return $this->db->insert('supplier_advances', $data);
    }

    public function add_opening($data)
    {
        return $this->db->insert('supplier_opening_dues', $data);
    }

    public function get_all_suppliers()
    {
        $q = $this->db->get('suppliers');
        return $q->result();
    }

    public function get_supplier_advance_balance($supplier_id)
    {
        // Total advance given
        $this->db->select_sum('amount');
        $this->db->where('supplier_id', $supplier_id);
        $advance_query = $this->db->get('supplier_advances');
        $total_advance = $advance_query->row()->amount ?? 0;

        // Total advance used (if you store it in purchases table)
        $this->db->select_sum('advance_deducted');
        $this->db->where('supplier_id', $supplier_id);
        $used_query = $this->db->get('purchases');
        $total_used = $used_query->row()->advance_deducted ?? 0;

        // Current balance
        return $total_advance - $total_used;
    }

    public function get_all_opening()
    {
        $this->db->select('o.*, s.name as supplier_name');
        $this->db->from('tec_supplier_opening_dues o');
        $this->db->join('tec_suppliers s', 's.id=o.supplier_id', 'left');
        $this->db->order_by('o.date', 'DESC');
        $query = $this->db->get();
        $data = [];
        foreach ($query->result() as $row) {
            $row->Actions = '
                <button class="btn btn-sm btn-primary edit-opening-due" 
                    data-id="' . $row->id . '" 
                    data-supplier_id="' . $row->supplier_id . '" 
                    data-date="' . $row->date . '" 
                    data-voucher_no="' . $row->voucher_no . '" 
                    data-amount="' . $row->amount . '" 
                    data-note="' . $row->note . '">
                    <i class="fa fa-edit"></i>
                </button>
                <a href="' . site_url('supplier_opening_dues/delete/' . $row->id) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">
                    <i class="fa fa-trash"></i>
                </a>';
            $data[] = $row;
        }
        return $data;
    }

    public function update_opening_due($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('tec_supplier_opening_dues', $data);
    }

    public function get_opening_due_by_id($id)
    {
        return $this->db->get_where('tec_supplier_opening_dues', ['id' => $id])->row();
    }

    public function delete_opening_due($id)
    {
        return $this->db->delete('tec_supplier_opening_dues', ['id' => $id]);
    }

    public function getSupplierOpeningDueByID($id)
    {
        $this->db->select('
        supplier_opening_dues.*, 
        suppliers.name as supplier_name
    ');
        $this->db->from('supplier_opening_dues');
        $this->db->join('suppliers', 'suppliers.id = supplier_opening_dues.supplier_id', 'left');
        $this->db->where('supplier_opening_dues.id', $id);
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDuePayments($purchase_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sppayments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

     

    public function getSupplierDuePayments($id)
    {
        $q = $this->db->get_where('sppayments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addPayment($data = [])
    {
        // 🪵 Debug log the incoming payment data
        log_message('debug', '💰 addPayment() called with data: ' . json_encode($data));

        if ($this->db->insert('sppayments', $data)) {
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

        $purchase = $this->getSupplierOpeningDueByID($id);
        log_message('debug', '📦 Purchase data: ' . json_encode($purchase));

        $payments = $this->getDuePayments($id);
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

        if ($this->db->update('supplier_opening_dues', ['paid_amount' => $paid, 'status' => $status], ['id' => $id])) {
            log_message('debug', "✅ Purchases table updated. ID={$id}, Paid={$paid}, Status={$status}");
            return true;
        }

        log_message('error', "❌ Failed to update purchases table. ID={$id}");
        return false;
    }



    public function deletePayment($id)
{
    // Get the payment record first
    $payment = $this->db->get_where('sppayments', ['id' => $id])->row();

    if (!$payment) {
        log_message('error', "❌ Payment not found for deletePayment ID={$id}");
        return false;
    }

    // Delete the payment
    if ($this->db->delete('sppayments', ['id' => $id])) {
        log_message('debug', "🗑️ Payment deleted. ID={$id}, PurchaseID={$payment->purchase_id}");

        // Recalculate totals
        $this->syncPurchasePayments($payment->purchase_id);
        return true;
    }

    log_message('error', "❌ Failed to delete payment ID={$id}");
    return false;
}

public function updatePayment($id, $data = [])
    {
        $payment = $this->getSupplierDuePayments($id);
        if ($payment->paid_by == 'gift_card') {
            $gc = $this->site->getGiftCard($payment->gc_no);
            $this->db->update('gift_cards', ['balance' => ($gc->balance + $payment->amount)], ['card_no' => $payment->gc_no]);
        }
        if ($this->db->update('sppayments', $data, ['id' => $id])) {
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCard($data['gc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['gc_no']]);
            }
            $this->syncPurchasePayments($data['purchase_id']);
            return true;
        }
        return false;
    }

}
