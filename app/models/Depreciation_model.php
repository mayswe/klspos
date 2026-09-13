<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Depreciation_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addDepreciation($data)
    {
        return $this->db->insert('tec_depreciation_expenses', $data);
    }

    public function getAllDepreciation()
    {
        return $this->db->get('tec_depreciation_expenses')->result();
    }

    public function getDepreciationById($id)
    {
        return $this->db->get_where('tec_depreciation_expenses', ['id' => $id])->row();
    }

    public function updateDepreciation($id, $data)
    {
        return $this->db->update('tec_depreciation_expenses', $data, ['id' => $id]);
    }

    public function deleteDepreciation($id)
    {
        return $this->db->delete('tec_depreciation_expenses', ['id' => $id]);
    }
}
