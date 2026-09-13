<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Adjustment_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addAdjustment($data)
    {
        $this->db->insert('tec_product_adjustments', $data);
        return $this->db->insert_id();
    }

    public function getAdjustments()
    {
        $this->db->select('a.*, p.name as product_name, w.name as warehouse_name, u.first_name');
        $this->db->from('tec_product_adjustments a');
        $this->db->join('tec_products p', 'p.id = a.product_id', 'left');
        $this->db->join('tec_stores w', 'w.id = a.store_id', 'left');
        $this->db->join('tec_users u', 'u.id = a.created_by', 'left');
        $this->db->order_by('a.date', 'DESC');
        return $this->db->get()->result();
    }
}
