<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Openingstock_model extends CI_Model
{
    protected $table = 'stock_batches';

    /**
     * Get a single opening stock record by ID
     */
    public function getOpeningStockByID($id)
    {
        $this->db->select('sb.*, p.name as product_name, p.code as product_code, w.name as store_name');
        $this->db->from('stock_batches sb');
        $this->db->join('products p', 'p.id = sb.product_id', 'left');
        $this->db->join('stores w', 'w.id = sb.store_id', 'left');
        $this->db->where('sb.id', $id);
        $query = $this->db->get();
        return $query->row(); // returns single row
    }

    /**
     * Update opening stock
     */
    public function updateOpeningStock($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data); // returns true/false
    }

    public function getOpeningStockRowsByID($id)
{
    $this->db->select('sb.id, sb.product_id, sb.store_id, sb.batch_no, sb.qty_base, sb.qty_secondary, sb.cost_per_base');
    $this->db->from('tec_stock_batches sb');
    $this->db->join('tec_stock_movements sm', 'sm.batch_id = sb.id', 'left');
    $this->db->where('sm.movement_type', 'opening');
    $this->db->where('sm.id', $id); // Or use the batch_id of the opening stock you want to edit
    $query = $this->db->get();
    return $query->result();
}

}
