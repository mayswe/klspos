<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Warehouse_model extends CI_Model
{

    public function get_all() {
        return $this->db->get('tec_warehouses')->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where('tec_warehouses', ['id' => $id])->row();
    }

    public function insert($data) {
        return $this->db->insert('tec_warehouses', $data);
    }

    public function update($id, $data) {
        return $this->db->where('id', $id)->update('tec_warehouses', $data);
    }

    public function delete($id) {
        return $this->db->delete('tec_warehouses', ['id' => $id]);
    }
}
?>
