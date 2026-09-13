<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Currency_model extends CI_Model
{

    public function get_all() {
        return $this->db->get('tec_currencies')->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where('tec_currencies', ['id' => $id])->row();
    }

    public function insert($data) {
        return $this->db->insert('tec_currencies', $data);
    }

    public function update($id, $data) {
        return $this->db->where('id', $id)->update('tec_currencies', $data);
    }

    public function delete($id) {
        return $this->db->delete('tec_currencies', ['id' => $id]);
    }
}
?>
