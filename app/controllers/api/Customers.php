<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Customers extends MY_Controller {
    
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

        $this->db->select('id, name, phone, email');

        if ($search) {
            $this->db->group_start()
                ->like('name', $search)
                ->or_like('phone', $search)
                ->group_end();
        }

        $customers = $this->db->get('customers')->result();

        $this->_json(true, 'Customer list', $customers);
    }

    // ======================================
    // CREATE CUSTOMER (FROM POS)
    // ======================================
    public function store()
    {
        log_message('error', 'STORE API HIT');

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['name'])) {
            $this->_json(false, 'Customer name required');
        }

        $data = [
            'name'    => $input['name'],
            'phone'   => $input['phone'] ?? '',
            'email'   => $input['email'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ];
 
        $this->db->insert('tec_customers', $data);

        $this->_json(true, 'Customer created', [
            'id' => $this->db->insert_id(),
            'name' => $data['name'],
        ]);
    } 
}
