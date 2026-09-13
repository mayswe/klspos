<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customers_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addCustomer($data = [])
    {
        if ($this->db->insert('customers', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function addCustomergroup($data = [])
    {
        if ($this->db->insert('customergroup', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    

    public function deleteCustomer($id)
    {
        if ($this->db->delete('customers', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteCustomergroup($id)
    {
        if ($this->db->delete('customergroup', ['id' => $id])) {
            return true;
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

    public function updateCustomer($id, $data = [])
    {
        if ($this->db->update('customers', $data, ['id' => $id])) {
            return true;
        }
        return 
        false;
    }

    public function updateCustomergroup($id, $data = null)
    {
        if ($this->db->update('customergroup', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllCustomergroup()
    {
        $this->db->order_by('code');
        $q = $this->db->get('customergroup');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getAllCustomers()
    {
        $this->db->order_by('id');
        $q = $this->db->get('customers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getCustomersByGroup($group_id)
    {
        $this->db->where('group_id', $group_id);
        return $this->db->get('tec_customers')->result();
    }

    public function getCustomergroupById($id)
    {
        return $this->db->get_where('tec_customers', ['group_id' => $id])->row();
    }

    
}
