<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends MY_Controller {
    
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

        $this->db->select('id, image, code, name');

        if ($search) {
            $this->db->group_start()
                ->like('name', $search)
                ->group_end();
        }

        $categories = $this->db->get('categories')->result();

        $this->_json(true, 'Categories list', $categories);
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
