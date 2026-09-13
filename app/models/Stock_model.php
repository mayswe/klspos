<?php
 if (!defined('BASEPATH')) {
     exit('No direct script access allowed');
 }

class Stock_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Warehouse Stock Report
    public function getWarehouseStockReport()
    {
        $this->db->select('p.code AS product_code, p.name AS product_name, w.name AS warehouse, SUM(s.qty) AS quantity, p.unit, MAX(s.purchase_date) AS last_purchase_date');
        $this->db->from('product_batches s');
        $this->db->join('products p', 'p.id = s.product_id');
        $this->db->join('warehouses w', 'w.id = s.warehouse_id');
        $this->db->group_by(['s.product_id', 's.warehouse_id']);
        return $this->db->get()->result();
    }

    // Container Box Report
    public function getContainerBoxReport()
    {
        $this->db->select('cb.box_name as container_box, p.code AS product_code, p.name AS product_name, s.quantity, pu.name as unit, w.name AS warehouse, pc.date AS purchase_date');
        $this->db->from('purchase_items s');
        $this->db->join('purchases pc', 'pc.id = s.purchase_id');
        $this->db->join('products p', 'p.id = s.product_id');
        $this->db->join('warehouses w', 'w.id = pc.warehouse_id');
        $this->db->join('container_boxes cb', 'cb.id = pc.container_box');
        $this->db->join('product_units pu', 'pu.id = s.unit_id');
        
        
        $this->db->order_by('pc.container_box');
        return $this->db->get()->result();
    }

    // Product Summary Report
    public function getProductSummaryReport()
    {
        $this->db->select('p.code AS product_code, p.name AS product_name, SUM(s.qty) AS total_quantity, pu.name as unit');
        $this->db->from('product_batches s');
        $this->db->join('products p', 'p.id = s.product_id');
        $this->db->join('product_units pu', 'pu.id = s.unit_id');
        $this->db->group_by('s.product_id');
        return $this->db->get()->result();
    }
}
