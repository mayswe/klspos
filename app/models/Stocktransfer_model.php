<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Stocktransfer_model extends CI_Model
{


public function getTransferByID($id)
{
    return $this->db->select('st.*, fw.name as from_warehouse, tw.name as to_warehouse, u.username as created_by')
                    ->from('tec_stock_transfers st')
                    ->join('warehouses fw', 'fw.id = st.from_warehouse', 'left')
                    ->join('warehouses tw', 'tw.id = st.to_warehouse', 'left')
                    ->join('users u', 'u.id = st.created_by', 'left')
                    ->where('st.id', $id)
                    ->get()->row();
}

public function getTransferItems($transfer_id)
{
    return $this->db->select('ti.*, p.name as product_name, p.code as product_code')
                    ->from('stock_transfer_items ti')
                    ->join('products p', 'p.id = ti.product_id', 'left')
                    ->where('ti.transfer_id', $transfer_id)
                    ->get()->result();
}


public function addStockTransfer($data, $items)
{
    $this->db->trans_start();

    // Save master transfer
    $this->db->insert('stock_transfers', $data);
    $transfer_id = $this->db->insert_id();

    foreach ($items as $item) {
        $item['transfer_id'] = $transfer_id;
        $this->db->insert('stock_transfer_items', $item);

        // 🔹 Convert to base unit
        $base_qty = $this->site->convertToBaseQty($item['product_id'], $item['quantity'], $item['unit_id']);

        // 🔹 Deduct stock from source batches (FIFO)
        $batches_out = $this->site->deductFromBatches($item['product_id'], $data['from_location'], $base_qty);

        foreach ($batches_out as $batch) {
            // Movement OUT
            $this->db->insert('stock_movements', [
                'date'       => $data['date'],
                'product_id' => $item['product_id'],
                'location_id'=> $data['from_location'],
                'batch_id'   => $batch['batch_id'],
                'quantity'   => -$batch['qty'],  // negative = out
                'reference'  => 'TRANSFER OUT #' . $transfer_id,
                'created_by' => $data['created_by'],
            ]);

            // 🔹 Add to destination batches
            $to_batch_id = $this->site->addToBatch($item['product_id'], $data['to_location'], $batch['qty'], $batch['cost']);

            // Movement IN
            $this->db->insert('stock_movements', [
                'date'       => $data['date'],
                'product_id' => $item['product_id'],
                'location_id'=> $data['to_location'],
                'batch_id'   => $to_batch_id,
                'quantity'   => $batch['qty'],   // positive = in
                'reference'  => 'TRANSFER IN #' . $transfer_id,
                'created_by' => $data['created_by'],
            ]);
        }
    }

    $this->db->trans_complete();
    return $this->db->trans_status();
}

public function transfer_stock($from_store_id, $to_store_id, $items, $reference, $date) 
{
    $this->db->trans_start();

    log_message('debug', "=== START TRANSFER === From Store: {$from_store_id}, To Store: {$to_store_id}, Ref: {$reference}, Date: {$date}");

    foreach ($items as $item) {
        $product_id    = $item['product_id'];
        $primary_qty   = (float) $item['primary_qty'];
        $secondary_qty = (float) $item['secondary_qty'];

        // 🔹 Get conversion rule once
            $conversion = $this->db
                ->select('operation_value, operator')
                ->where('product_id', $product_id)
                ->where('operation_value >', 1) // secondary unit id (can be dynamic later)
                ->get('tec_product_unit_conversions')
                ->row();

            if ($conversion) {
                // Case 1: Only base entered
                if ($primary_qty > 0 && $secondary_qty == 0) {
                    if ($conversion->operator == '*') {
                        $secondary_qty = $primary_qty * $conversion->operation_value;
                    } elseif ($conversion->operator == '/') {
                        $secondary_qty = $primary_qty / $conversion->operation_value;
                    }
                }

                // Case 2: Only secondary entered
                if ($secondary_qty > 0 && $primary_qty == 0) {
                    if ($conversion->operator == '*') {
                        $primary_qty = $secondary_qty / $conversion->operation_value;
                    } elseif ($conversion->operator == '/') {
                        $primary_qty = $secondary_qty * $conversion->operation_value;
                    }
                }
            }

        log_message('debug', "Processing Product ID: {$product_id}, Primary Qty: {$primary_qty}, Secondary Qty: {$secondary_qty}");

        $remaining_primary   = $primary_qty;
        $remaining_secondary = $secondary_qty;

        

        // FIFO batches from source store
        $batches = $this->db->where('product_id', $product_id)
                            ->where('store_id', $from_store_id)
                            ->where("(qty_base > 0 OR qty_secondary > 0)")
                            ->order_by('date, id', 'asc')
                            ->get('tec_stock_batches')
                            ->result();

        log_message('debug', "Found " . count($batches) . " batches for product {$product_id} in store {$from_store_id}");

        foreach ($batches as $batch) {
            if ($remaining_primary <= 0 && $remaining_secondary <= 0) break;

            

            $deduct_primary   = min($batch->qty_base, $remaining_primary);
            $deduct_secondary = min($batch->qty_secondary, $remaining_secondary);

            if ($deduct_primary <= 0 && $deduct_secondary <= 0) continue;

            log_message('debug', "Batch {$batch->id} | Avail: P={$batch->qty_base}, S={$batch->qty_secondary} | Deduct: P={$deduct_primary}, S={$deduct_secondary}");

            // Update source batch
            $this->db->where('id', $batch->id)
                     ->set('qty_base', "qty_base - {$deduct_primary}", FALSE)
                     ->set('qty_secondary', "qty_secondary - {$deduct_secondary}", FALSE)
                     ->update('tec_stock_batches');
            
            // Create transfer header
            $this->db->insert('tec_transfers', [
                'reference'     => $reference,
                'from_store_id' => $from_store_id,
                'to_store_id'   => $to_store_id,
                'date'          => $date,
                'created_by'    => $this->session->userdata('user_id'),
            ]);
            $transfer_id = $this->db->insert_id();


            // Movement OUT
            $this->db->insert('tec_stock_movements', [
                'transfer_id'    => $transfer_id,
                'batch_id'       => $batch->id,
                'product_id'     => $product_id,
                'from_store_id'  => $from_store_id,
                'to_store_id'    => $to_store_id,
                'movement_type'  => 'transfer',
                'qty_base'    => -$deduct_primary,
                'qty_secondary'  => -$deduct_secondary,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
            
             // 🔹 Correct unit conversion (base / secondary)
            $unit_convert = ($deduct_secondary > 0) ? $deduct_secondary / $deduct_primary : 0;
            
            // Create new batch in destination (FIFO cost kept)
            $this->db->insert('tec_stock_batches', [
                'product_id'    => $product_id,
                'store_id'      => $to_store_id,
                'batch_no'      => 'Transfer',
                'qty_base'      => $deduct_primary,
                'qty_secondary' => $deduct_secondary,
                'primary_unit_id'   => $batch->primary_unit_id,
                'secondary_unit_id' => $batch->secondary_unit_id,
                'cost_per_base' => $batch->cost_per_base, // ✅ Keep cost for FIFO
                'delivery'      => $batch->delivery ? (float)$batch->delivery : 0,
                'unit_convert'      => $unit_convert,
                'date'          => $date,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ]);
            $new_batch_id = $this->db->insert_id();

            // Movement IN
            $this->db->insert('tec_stock_movements', [
                 'transfer_id'    => $transfer_id,
                'batch_id'       => $new_batch_id,
                'product_id'     => $product_id,
                'from_store_id'  => $from_store_id,
                'to_store_id'    => $to_store_id,
                'movement_type'  => 'transfer',
                'qty_base'    => $deduct_primary,
                'qty_secondary'  => $deduct_secondary,
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            // Reduce remaining
            $remaining_primary   -= $deduct_primary;
            $remaining_secondary -= $deduct_secondary;
        }

        if ($remaining_primary > 0 || $remaining_secondary > 0) {
            log_message('error', "Not enough stock for product ID {$product_id}. Remaining P={$remaining_primary}, S={$remaining_secondary}");
            $this->db->trans_rollback();
            throw new Exception("Not enough stock for product ID {$product_id}");
        }
    }

    $this->db->trans_complete();
    $status = $this->db->trans_status();

    log_message('debug', "=== END TRANSFER === Status: " . ($status ? 'SUCCESS' : 'FAILED'));

    return $status;
}


}
?>
