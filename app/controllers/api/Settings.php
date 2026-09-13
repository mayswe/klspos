<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        // 🔥 Require login for ALL endpoints
        $this->verify_token();
    }

    // ======================================
    // GET CUSTOMERS (SEARCH FOR POS)
    // ======================================
    public function stores()
    {

        $search = $this->input->get('search');

        $this->db->select('*');

        $stores = $this->db->get('stores')->result();

        $this->_json(true, 'Stores list', $stores);
    }

    
}
