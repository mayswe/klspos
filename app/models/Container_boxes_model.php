<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Container_boxes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addContainerBox($data = [])
    {
        if ($this->db->insert('container_boxes', $data)) {
            return true;
        }
        return false;
    }

    public function deleteContainerBox($id)
    {
        if ($this->db->delete('container_boxes', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getContainerBoxByID($id)
    {
        $q = $this->db->get_where('container_boxes', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateContainerBox($id, $data = [])
    {
        if ($this->db->update('container_boxes', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function generateNextBoxName()
    {
        $yearMonth = date('Ym');
        $this->db->select('box_name');
        $this->db->from('container_boxes');
        $this->db->like('box_name', $yearMonth, 'after');
        $this->db->order_by('box_name', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $lastBoxName = $query->row()->box_name;
            $lastChar = substr($lastBoxName, -1);
            if ($lastChar == 'Z') {
                return false;
            }
            $nextChar = chr(ord($lastChar) + 1);
        } else {
            $nextChar = 'A';
        }

        return $yearMonth . $nextChar;
    }

}
