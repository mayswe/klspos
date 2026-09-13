<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts extends MY_Controller {
    
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
    public function index()
    {

        $search = $this->input->get('search');

        $this->db->select('*');

        $accounts = $this->db->get('accounts')->result();

        $this->_json(true, 'Accounts list', $accounts);
    }

    
}
