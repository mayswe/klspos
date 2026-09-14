<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public $loggedIn;
    
    public function __construct()
    {
        parent::__construct();
        
        // Load session library if not autoloaded
        $this->load->library('session');

        // Get logged_in status from the session
        $this->loggedIn = (bool)$this->session->userdata('logged_in');

        
        if ($this->db->dbdriver == 'mysqli') {
            $this->db->query('SET SESSION sql_mode = ""');
        }
        $this->Settings = $this->site->getSettings();

        $is_app_mode = $this->input->get('app') == 1 || $this->input->post('app') == 1 || $this->input->get('mobile') == 1 || $this->input->post('mobile') == 1;
        $app_language = $this->input->get('app_lang', true) ?: $this->input->post('app_lang', true);
        $language_map = [
            'en'      => 'english',
            'english' => 'english',
            'mm'      => 'myanmar',
            'my'      => 'myanmar',
            'myanmar' => 'myanmar',
        ];

        if ($is_app_mode && $app_language && isset($language_map[strtolower($app_language)])) {
            $selected_language = $language_map[strtolower($app_language)];
            $this->session->set_userdata('app_language', $selected_language);
        } elseif ($is_app_mode && $this->session->userdata('app_language')) {
            $selected_language = $this->session->userdata('app_language');
        } elseif ($spos_language = $this->input->cookie('spos_language', true)) {
            $selected_language = $spos_language;
        } else {
            $selected_language = $this->Settings->language;
        }

        $this->Settings->selected_language = $selected_language;
        $this->config->set_item('language', $selected_language);
        $this->lang->load('app', $selected_language);
        $this->Settings->pin_code = $this->Settings->pin_code ? md5($this->Settings->pin_code) : null;
        $this->theme              = $this->Settings->theme . '/views/';
        $this->data['assets']     = base_url() . 'themes/default/assets/';
        $this->data['Settings']   = $this->Settings;
        $this->data['loggedIn']   = $this->loggedIn;
        $this->data['is_app_mode'] = $is_app_mode;
        $this->data['store']      = $this->site->getStoreByID($this->session->userdata('store_id'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->Admin              = $this->tec->in_group('admin') ? true : null;
        $this->data['Admin']      = $this->Admin;
        $this->Owner              = $this->tec->in_group('owner') ? true : null;
        $this->data['Owner']      = $this->Owner;
        $this->Supervisor              = $this->tec->in_group('supervisor') ? true : null;
        $this->data['Supervisor']      = $this->Supervisor;
        $this->Sales              = $this->tec->in_group('sales') ? true : null;
        $this->data['Sales']      = $this->Sales;
        $this->m                  = strtolower($this->router->fetch_class());
        $this->v                  = strtolower($this->router->fetch_method());
        $this->data['m']          = $this->m;
        $this->data['v']          = $this->v;
    }

    protected function mobilePageUrl($path)
    {
        $url = site_url($path);
        if ($this->input->get('app') == 1 || $this->input->post('app') == 1
            || $this->input->get('mobile') == 1 || $this->input->post('mobile') == 1) {
            $url .= '?' . http_build_query(['app'=>1, 'app_lang'=>
                $this->input->post('app_lang',true) ?: $this->input->get('app_lang',true) ?: $this->Settings->selected_language]);
        }
        return $url;
    }

    public function page_construct($page, $data = [], $meta = [])
    {
        if (empty($meta)) {
            $meta['page_title'] = $data['page_title'];
        }
        $meta['message']         = $data['message'] ?? $this->session->flashdata('message');
        $meta['error']           = $data['error']   ?? $this->session->flashdata('error');
        $meta['warning']         = $data['warning'] ?? $this->session->flashdata('warning');
        $meta['ip_address']      = $this->input->ip_address();
        $meta['Admin']           = $data['Admin'];
        $meta['Owner']           = $data['Owner'];
        $meta['Supervisor']      = $data['Supervisor'];
        $meta['Sales']           = $data['Sales'];
        $meta['loggedIn']        = $data['loggedIn'];
        $meta['is_app_mode']     = $data['is_app_mode'] ?? ($this->input->get('app') == 1 || $this->input->post('app') == 1 || $this->input->get('mobile') == 1 || $this->input->post('mobile') == 1);
        $meta['Settings']        = $data['Settings'];
        $meta['assets']          = $data['assets'];
        $meta['store']           = $data['store'];
        $meta['suspended_sales'] = $this->site->getUserSuspenedSales();
        $meta['qty_alert_num']   = $this->site->getQtyAlerts();
        $meta['preorder_alert_num']   = $this->site->getPreorderAlerts();
        $meta['due_alert_num']   = $this->site->getDueAlerts();
        $this->session->unset_userdata('error');
        $this->session->unset_userdata('message');
        $this->session->unset_userdata('warning');
        $this->load->view($this->theme . 'header', $meta);
        $mobile_shared_pages = [
            'purchases/expenses', 'purchases/edit_expense', 'purchases/add_expensetype', 'purchases/edit_expensetype',
            'customers/add', 'customers/edit', 'customers/customergroupadd',
            'suppliers/add', 'suppliers/edit', 'categories/add', 'categories/edit',
            'stocktransfers/add', 'stocktransfers/edit', 'openingstock/edit', 'products/adjustments_add',
            'warehouses/index', 'warehouses/add', 'warehouses/edit',
            'currencies/index', 'currencies/add', 'currencies/edit',
            'container_boxes/index', 'container_boxes/add', 'container_boxes/edit',
            'shippings/index', 'shippings/add', 'shippings/edit',
            'gift_cards/index', 'gift_cards/add', 'gift_cards/edit', 'settings/index',
            'customers/customergroup', 'categories/import', 'depreciation/create',
            'products/add_unit', 'products/edit_unit', 'products/import', 'products/selling_prices', 'products/unit_conversions',
            'suppliers/advances', 'suppliers/add_advance', 'suppliers/edit_advance',
            'suppliers/opening', 'suppliers/add_opening', 'suppliers/edit_opening',
            'settings/add_store', 'settings/edit_store', 'settings/printers', 'settings/add_printer', 'settings/edit_printer',
            'reports/container_box', 'reports/customer_order_report', 'reports/customers',
            'reports/daily', 'reports/dailysales', 'reports/dailyspurchases', 'reports/investment',
            'reports/monthly', 'reports/payments', 'reports/product_summary', 'reports/products',
            'reports/profit_report', 'reports/profitloss', 'reports/purchasesupplier', 'reports/registers',
            'reports/stocks', 'reports/supplieradvances', 'reports/top', 'reports/warehouse_stock', 'sales/opened'
        ];
        $use_mobile_shared = !empty($meta['is_app_mode']) && in_array($page, $mobile_shared_pages, true);
        if ($use_mobile_shared) {
            $this->load->view($this->theme . 'shared/mobile_page_start', array_merge($data, ['mobile_page'=>$page]));
        }
        $this->load->view($this->theme . $page, $data);
        if ($use_mobile_shared) {
            $this->load->view($this->theme . 'shared/mobile_page_end');
        }
        $this->load->view($this->theme . 'footer');
    }
    
    protected function response($status, $message, $data = [])
    {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }
    
    protected function _json($status, $message, $data = [])
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ]);
        exit;
    }
    
    protected function api_user()
    {
        $headers = getallheaders();
    
        if (empty($headers['Authorization'])) {
            $this->_json(false, 'Authorization header missing');
        }
    
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    
        $user = $this->db->where('api_token', $token)->get('users')->row();
    
        if (!$user) {
            $this->_json(false, 'Invalid or expired token');
        }
    
        return $user;
    }
    
    protected $current_user = null;

    protected function verify_token()
    {
        $headers = $this->input->request_headers();
    
        if (!isset($headers['Authorization'])) {
            $this->_json(false, 'Unauthorized - No token', null, 401);
            exit;
        }
    
        // Format: Bearer TOKEN
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
    
        if (!$token) {
            $this->_json(false, 'Invalid token format', null, 401);
            exit;
        }
    
        // 👉 Decode token (depends how you generated it)
        $decoded = $this->decode_token($token);
    
        if (!$decoded) {
            $this->_json(false, 'Invalid or expired token', null, 401);
            exit;
        }
    
        // 👉 Get user from DB
        $user = $this->db->where('id', $decoded->user_id)->get('users')->row();
    
        if (!$user || !$user->active) {
            $this->_json(false, 'User not found or inactive', null, 401);
            exit;
        }
    
        $this->current_user = $user;
    }
    
    protected function generate_token($user_id)
    {
        $token = bin2hex(random_bytes(32));
    
        $this->db->insert('api_tokens', [
            'user_id'    => $user_id,
            'token'      => $token,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    
        return $token;
    }

    protected function decode_token($token)
    {
        $row = $this->db
            ->where('token', $token)
            ->get('api_tokens')
            ->row();
    
        if (!$row) return false;
    
        return (object)[
            'user_id' => $row->user_id
        ];
    }
}
