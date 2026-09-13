<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sales extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }
        if (!$this->session->userdata('store_id')) {
            $this->session->set_flashdata('warning', lang('please_select_store'));
            redirect('stores');
        }
        $this->load->library('form_validation');
        $this->load->model('sales_model');
         $this->load->model('reports_model');
         $this->load->model('pos_model');

        $this->digital_file_types = 'zip|pdf|doc|docx|xls|xlsx|jpg|png|gif';
    }

    public function add_payment($id = null, $cid = null)
    {
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Admin) {
                $date = $this->input->post('date');
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = [
                'date'        => $date,
                'sale_id'     => $id,
                'customer_id' => $cid,
                'reference'   => $this->input->post('reference'),
                'amount'      => $this->input->post('amount-paid'),
                'paid_by'     => $this->input->post('paid_by'),
                'cheque_no'   => $this->input->post('cheque_no'),
                'gc_no'       => $this->input->post('gift_card_no'),
                'cc_no'       => $this->input->post('pcc_no'),
                'cc_holder'   => $this->input->post('pcc_holder'),
                'cc_month'    => $this->input->post('pcc_month'),
                'cc_year'     => $this->input->post('pcc_year'),
                'cc_type'     => $this->input->post('pcc_type'),
                'note'        => $this->input->post('note'),
                'created_by'  => $this->session->userdata('user_id'),
                'store_id'    => $this->session->userdata('store_id'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = 2048;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            // $this->tec->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            $this->tec->dd();
        }

        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment)) {
            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $sale                = $this->sales_model->getSaleByID($id);
            $this->data['inv']   = $sale;

            $this->load->view($this->theme . 'sales/add_payment', $this->data);
        }
    }

    public function delete($id = null)
    {
        $is_ajax_delete =
            $this->input->is_ajax_request() ||
            (int) $this->input->post('ajax_delete') === 1;

        $send_delete_json = function (
            $status,
            $message,
            $http_status = 200,
            array $extra = []
        ) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $payload = array_merge([
                'status'  => $status,
                'message' => $message,
            ], $extra);

            $this->output->enable_profiler(false);
            $this->output
                ->set_status_header((int) $http_status)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ))
                ->_display();
            exit;
        };

        if (DEMO) {
            log_message(
                'warning',
                '[SALE DELETE BLOCKED] DEMO mode. User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_delete) {
                $send_delete_json(
                    'error',
                    lang('disabled_in_demo'),
                    403,
                    ['code' => 'demo_mode']
                );
            }

            $this->session->set_flashdata('error', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
            return;
        }

        if ($this->input->post('sale_id')) {
            $id = $this->input->post('sale_id');
        } elseif ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $id = (int) $id;

        if (!$this->Admin && !$this->Owner) {
            log_message(
                'warning',
                '[SALE DELETE BLOCKED] Access denied. Sale ID: ' . $id .
                ' | User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_delete) {
                $send_delete_json(
                    'error',
                    lang('access_denied'),
                    403,
                    ['code' => 'access_denied']
                );
            }

            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('sales');
            return;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $message =
                'လုံခြုံရေးအရ အရောင်းဘောင်ချာဖျက်ခြင်းကို ' .
                'အတည်ပြု popup မှတစ်ဆင့်သာ လုပ်နိုင်ပါသည်။';

            log_message(
                'warning',
                '[SALE DELETE BLOCKED] Non-POST request. Sale ID: ' . $id .
                ' | User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_delete) {
                $send_delete_json(
                    'error',
                    $message,
                    405,
                    ['code' => 'post_required']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        if ($id <= 0) {
            $message = 'Invalid Sale ID.';

            if ($is_ajax_delete) {
                $send_delete_json(
                    'error',
                    $message,
                    400,
                    ['code' => 'invalid_sale_id']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $reason = trim(strip_tags((string) $this->input->post(
            'delete_reason',
            true
        )));
        $reason = trim((string) preg_replace('/\s+/u', ' ', $reason));

        if (function_exists('mb_substr')) {
            $reason = mb_substr($reason, 0, 500, 'UTF-8');
        } else {
            $reason = substr($reason, 0, 500);
        }

        if ($reason === '') {
            $message = 'ဖျက်ရသည့်အကြောင်းပြချက်ကို ထည့်ပေးပါ။';

            log_message(
                'warning',
                '[SALE DELETE BLOCKED] Delete reason missing. Sale ID: ' .
                $id . ' | User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_delete) {
                $send_delete_json(
                    'error',
                    $message,
                    422,
                    ['code' => 'delete_reason_required']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $sale = $this->sales_model->getSaleByID($id);

        if (!$sale) {
            $message = 'အရောင်းဘောင်ချာကို ရှာမတွေ့ပါ။';

            if ($is_ajax_delete) {
                $send_delete_json(
                    'error',
                    $message,
                    404,
                    ['code' => 'sale_not_found']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $session_store_id = (int) $this->session->userdata('store_id');

        if (
            !$this->Owner &&
            isset($sale->store_id) &&
            (int) $sale->store_id !== $session_store_id
        ) {
            $message =
                'မိမိရွေးထားသောဆိုင်၏ အရောင်းဘောင်ချာကိုသာ ဖျက်နိုင်ပါသည်။';

            log_message(
                'warning',
                '[SALE DELETE BLOCKED] Store mismatch. Sale ID: ' . $id .
                ' | Sale Store ID: ' . (int) $sale->store_id .
                ' | Session Store ID: ' . $session_store_id .
                ' | User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_delete) {
                $send_delete_json(
                    'error',
                    $message,
                    403,
                    ['code' => 'store_mismatch']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $delete_result = $this->sales_model->deleteInvoiceERP($id, [
            'reason'      => $reason,
            'user_id'     => (int) $this->session->userdata('user_id'),
            'store_id'    => isset($sale->store_id)
                ? (int) $sale->store_id
                : $session_store_id,
            'ip_address'  => $this->input->ip_address(),
            'user_agent'  => $this->input->user_agent(),
            'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        ]);

        if (!empty($delete_result['success'])) {
            $message = (string) ($delete_result['message'] ??
                lang('invoice_deleted'));

            if ($is_ajax_delete) {
                $send_delete_json(
                    'success',
                    $message,
                    200,
                    [
                        'code'  => (string) ($delete_result['code'] ??
                            'sale_deleted'),
                        'stock' => $delete_result['stock'] ?? null,
                    ]
                );
            }

            $this->session->set_flashdata('message', $message);
            redirect('sales?delete_result=success');
            return;
        }

        $message = (string) ($delete_result['message'] ??
            'အရောင်းဘောင်ချာကို ဖျက်၍မရပါ။');
        $code = (string) ($delete_result['code'] ?? 'delete_failed');

        $http_status = in_array(
            $code,
            ['payments_exist', 'returns_exist', 'stock_trace_missing'],
            true
        ) ? 409 : 500;

        if ($is_ajax_delete) {
            $send_delete_json(
                'error',
                $message,
                $http_status,
                ['code' => $code]
            );
        }

        $this->session->set_flashdata('error', $message);
        redirect('sales?delete_result=failed');
        return;
    }

    public function delete_holded($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('sales/opened');
        }

        if ($this->sales_model->deleteOpenedSale($id)) {
            $this->session->set_flashdata('message', lang('opened_bill_deleted'));
            redirect('sales/opened');
        }
    }

    public function delete_payment($id = null)
    {
        $is_ajax_reverse =
            $this->input->is_ajax_request() ||
            (int) $this->input->post('ajax_delete') === 1;

        $send_reverse_json = function (
            $status,
            $message,
            $http_status = 200,
            array $extra = []
        ) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $payload = array_merge([
                'status'  => $status,
                'message' => $message,
            ], $extra);

            $this->output->enable_profiler(false);
            $this->output
                ->set_status_header((int) $http_status)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ))
                ->_display();
            exit;
        };

        if (DEMO) {
            $message = lang('disabled_in_demo');

            log_message(
                'warning',
                '[SALE PAYMENT REVERSE BLOCKED] DEMO mode. User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'error',
                    $message,
                    403,
                    ['code' => 'demo_mode']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        if ($this->input->post('payment_id')) {
            $id = $this->input->post('payment_id');
        } elseif ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $id = (int) $id;

        if (!$this->Admin && !$this->Owner) {
            $message = lang('access_denied');

            log_message(
                'warning',
                '[SALE PAYMENT REVERSE BLOCKED] Access denied. Payment ID: ' .
                $id . ' | User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'error',
                    $message,
                    403,
                    ['code' => 'access_denied']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $message =
                'လုံခြုံရေးအရ ငွေလက်ခံမှု Reverse လုပ်ခြင်းကို ' .
                'အတည်ပြု popup မှတစ်ဆင့်သာ လုပ်နိုင်ပါသည်။';

            log_message(
                'warning',
                '[SALE PAYMENT REVERSE BLOCKED] Non-POST request. ' .
                'Payment ID: ' . $id . ' | User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'error',
                    $message,
                    405,
                    ['code' => 'post_required']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        if ($id <= 0) {
            $message = 'Invalid Sales Payment ID.';

            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'error',
                    $message,
                    400,
                    ['code' => 'invalid_payment_id']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $reason = trim(strip_tags((string) $this->input->post(
            'delete_reason',
            true
        )));
        $reason = trim((string) preg_replace('/\s+/u', ' ', $reason));

        if (function_exists('mb_substr')) {
            $reason = mb_substr($reason, 0, 500, 'UTF-8');
        } else {
            $reason = substr($reason, 0, 500);
        }

        if ($reason === '') {
            $message = 'Reverse လုပ်ရသည့်အကြောင်းပြချက်ကို ထည့်ပေးပါ။';

            log_message(
                'warning',
                '[SALE PAYMENT REVERSE BLOCKED] Reason missing. Payment ID: ' .
                $id . ' | User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'error',
                    $message,
                    422,
                    ['code' => 'reverse_reason_required']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $payment = $this->sales_model->getPaymentByID($id);

        if (!$payment || empty($payment->sale_id)) {
            $message = 'Sales payment ကို ရှာမတွေ့ပါ။';

            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'error',
                    $message,
                    404,
                    ['code' => 'payment_not_found']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $sale = $this->sales_model->getSaleByID((int) $payment->sale_id);

        if (!$sale) {
            $message =
                'Payment နှင့်ချိတ်ဆက်ထားသော အရောင်းဘောင်ချာကို ရှာမတွေ့ပါ။';

            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'error',
                    $message,
                    409,
                    ['code' => 'sale_not_found']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $session_store_id = (int) $this->session->userdata('store_id');

        if (
            !$this->Owner &&
            isset($sale->store_id) &&
            (int) $sale->store_id !== $session_store_id
        ) {
            $message =
                'မိမိရွေးထားသောဆိုင်၏ ငွေလက်ခံမှုကိုသာ Reverse ' .
                'လုပ်နိုင်ပါသည်။';

            log_message(
                'warning',
                '[SALE PAYMENT REVERSE BLOCKED] Store mismatch. ' .
                'Payment ID: ' . $id .
                ' | Sale Store ID: ' . (int) $sale->store_id .
                ' | Session Store ID: ' . $session_store_id .
                ' | User ID: ' .
                (int) $this->session->userdata('user_id')
            );

            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'error',
                    $message,
                    403,
                    ['code' => 'store_mismatch']
                );
            }

            $this->session->set_flashdata('error', $message);
            redirect('sales');
            return;
        }

        $reverse_result = $this->sales_model->reversePaymentERP($id, [
            'reason'      => $reason,
            'user_id'     => (int) $this->session->userdata('user_id'),
            'store_id'    => isset($sale->store_id)
                ? (int) $sale->store_id
                : $session_store_id,
            'ip_address'  => $this->input->ip_address(),
            'user_agent'  => $this->input->user_agent(),
            'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        ]);

        $message = (string) ($reverse_result['message'] ??
            'ငွေလက်ခံမှုကို Reverse လုပ်၍မရပါ။');
        $code = (string) ($reverse_result['code'] ??
            'payment_reverse_failed');

        if (!empty($reverse_result['success'])) {
            if ($is_ajax_reverse) {
                $send_reverse_json(
                    'success',
                    $message,
                    200,
                    ['code' => $code]
                );
            }

            $this->session->set_flashdata('message', $message);
            redirect('sales');
            return;
        }

        $http_status = in_array(
            $code,
            ['gateway_refund_required', 'sale_not_found'],
            true
        ) ? 409 : 500;

        if ($is_ajax_reverse) {
            $send_reverse_json(
                'error',
                $message,
                $http_status,
                ['code' => $code]
            );
        }

        $this->session->set_flashdata('error', $message);
        redirect('sales');
        return;
    }

    public function edit_payment($id = null, $sid = null)
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $payment = [
                'sale_id'    => $sid,
                'reference'  => $this->input->post('reference'),
                'amount'     => $this->input->post('amount-paid'),
                'paid_by'    => $this->input->post('paid_by'),
                'cheque_no'  => $this->input->post('cheque_no'),
                'gc_no'      => $this->input->post('gift_card_no'),
                'cc_no'      => $this->input->post('pcc_no'),
                'cc_holder'  => $this->input->post('pcc_holder'),
                'cc_month'   => $this->input->post('pcc_month'),
                'cc_year'    => $this->input->post('pcc_year'),
                'cc_type'    => $this->input->post('pcc_type'),
                'note'       => $this->input->post('note'),
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->Admin) {
                $payment['date'] = $this->input->post('date');
            }

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = 'files/';
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = 2048;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->tec->print_arrays($payment);
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            $this->tec->dd();
        }

        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            redirect('sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $payment             = $this->sales_model->getPaymentByID($id);
            
            $this->data['payment'] = $payment;
            $this->load->view($this->theme . 'sales/edit_payment', $this->data);
        }
    }

    public function get_opened_list()
    {
        $this->load->library('datatables');
        if ($this->db->dbdriver == 'sqlite3') {
            $this->datatables->select("id, date, customer_name, hold_ref, (total_items || ' (' || total_quantity || ')') as items, grand_total", false);
        } else {
            $this->datatables->select("id, date, customer_name, hold_ref, CONCAT(total_items, ' (', total_quantity, ')') as items, grand_total", false);
        }
        $this->datatables->from('suspended_sales');
        if (!$this->Admin) {
            $user_id = $this->session->userdata('user_id');
            $this->datatables->where('created_by', $user_id);
        }
        $this->datatables->where('store_id', $this->session->userdata('store_id'));
        $this->datatables->add_column(
    'Actions',
    "<div class='text-center'>
        <div class='btn-group'>
            <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
            </button>
            <ul class='dropdown-menu dropdown-menu-right'>
                <li>
                    <a href='" . site_url('pos/?hold=$1') . "' class='tip' title='" . lang('click_to_add') . "'>
                        <i class='fa fa-th-large'></i> " . lang('click_to_add') . "
                    </a>
                </li>
                <li>
                    <a href='" . site_url('sales/delete_holded/$1') . "' class='tip text-danger' title='" . lang('delete') . "' onClick=\"return confirm('" . lang('alert_x_holded') . "')\">
                        <i class='fa-solid fa-trash'></i> " . lang('delete') . "
                    </a>
                </li>
            </ul>
        </div>
    </div>",
    'id'
)->unset_column('id');

        echo $this->datatables->generate();
    }


public function get_sales()
{
    $this->load->library('datatables');

    $this->sales_model->ensureSalesDeliveryTables();

    $sales_table = $this->db->dbprefix('sales');
    $sale_items_table = $this->db->dbprefix('sale_items');
    /*
     * Custom Datatables.php က SELECT expression များကို column အလိုက်
     * parse လုပ်သောကြောင့် CASE/LEAST အရှည်ကြီးကို outer SELECT ထဲမထည့်ပါ။
     * Status ကို derived table ထဲမှာ အရင်တွက်ထားပြီး အပြင်ဘက်မှာ
     * ရိုးရိုး delivery_status column ကိုသာ ရွေးထားသည်။
     */
    $delivery_summary = "(
        SELECT
            delivery_totals.sale_id,
            delivery_totals.ordered_qty,
            delivery_totals.delivered_qty,
            CASE
                WHEN delivery_totals.ordered_qty > 0
                     AND delivery_totals.delivered_qty >=
                         delivery_totals.ordered_qty
                    THEN 1
                WHEN delivery_totals.delivered_qty > 0
                    THEN 2
                ELSE 0
            END AS delivery_status
        FROM (
            SELECT
                si.sale_id,
                SUM(
                    CASE
                        WHEN COALESCE(si.preorder_qty, 0) > 0
                            THEN si.preorder_qty
                        ELSE COALESCE(si.quantity, 0)
                    END
                ) AS ordered_qty,
                SUM(
                    CASE
                        WHEN COALESCE(si.fulfilled_qty, 0) > 0
                            THEN LEAST(
                                si.fulfilled_qty,
                                CASE
                                    WHEN COALESCE(si.preorder_qty, 0) > 0
                                        THEN si.preorder_qty
                                    ELSE COALESCE(si.quantity, 0)
                                END
                            )
                        WHEN COALESCE(si.preorder_qty, 0) > 0
                             AND COALESCE(si.quantity, 0) >= si.preorder_qty
                            THEN si.preorder_qty
                        ELSE 0
                    END
                ) AS delivered_qty
            FROM `{$sale_items_table}` si
            GROUP BY si.sale_id
        ) delivery_totals
    ) sale_delivery_summary";

    $this->datatables
        ->select("
            id,
            DATE_FORMAT(date, '%Y-%m-%d %H:%i') AS date,
            customer_name,
            total,
            total_tax,
            total_discount,
            grand_total,
            paid,
            status,
            sale_delivery_summary.delivery_status
        ", false)
        ->from('sales')
        ->join(
            $delivery_summary,
            "sale_delivery_summary.sale_id = `{$sales_table}`.`id`",
            'left'
        );

    // Store restriction
    $store_id = $this->session->userdata('store_id');

    if ($store_id) {
        $this->datatables->where('store_id', $store_id);
    }

    // User restriction
    if (!$this->Admin && !$this->session->userdata('view_right')) {
        $this->datatables->where(
            'created_by',
            $this->session->userdata('user_id')
        );
    }

    /*
     * Custom toolbar filters
     */

    // Customer
    $customer = trim((string) $this->input->post('customer_filter'));

    if ($customer !== '') {
        $this->datatables->where('customer_name', $customer);
    }

    // Payment status
    $status = trim((string) $this->input->post('status_filter'));

    if ($status !== '') {
        if ($status === 'partial|due') {
            $this->datatables->where_in(
                'status',
                array('partial', 'due')
            );
        } else {
            $this->datatables->where('status', $status);
        }
    }

    // Product delivery status: 0=pending, 2=partial, 1=delivered
    $delivery_filter = $this->input->post('preorder_filter');

    if ($delivery_filter !== null && $delivery_filter !== '') {
        $delivery_filter = (int) $delivery_filter;

        if ($delivery_filter === 1) {
            $this->datatables
                ->where('sale_delivery_summary.ordered_qty >', 0)
                ->where(
                    'sale_delivery_summary.delivered_qty >= sale_delivery_summary.ordered_qty'
                );
        } elseif ($delivery_filter === 2) {
            $this->datatables
                ->where('sale_delivery_summary.delivered_qty >', 0)
                ->where(
                    'sale_delivery_summary.delivered_qty < sale_delivery_summary.ordered_qty'
                );
        } else {
            $this->datatables
                ->where('sale_delivery_summary.ordered_qty >', 0)
                ->where(
                    '(sale_delivery_summary.delivered_qty <= 0 ' .
                    'OR sale_delivery_summary.delivered_qty IS NULL)'
                );
        }
    }

    // Actions
    $this->datatables->add_column(
        'Actions',
        "
        <div class='text-center'>
            <div class='btn-group'>
                <button type='button'
                        class='btn btn-primary dropdown-toggle'
                        data-toggle='dropdown'>
                    <i class='fa fa-cog'></i> " . lang('actions') . "
                </button>

                <ul class='dropdown-menu dropdown-menu-right'>

                    <li>
                        <a href='" . site_url('pos/view/$1/1') . "'
                           data-toggle='ajax-modal'>
                            <i class='fa fa-list'></i>
                            " . lang('view_invoice') . "
                        </a>
                    </li>

                    <li>
                        <a href='" . site_url('pos/?edit=$1') . "'>
                            <i class='fa fa-edit'></i>
                            " . lang('edit') . "
                        </a>
                    </li>

                    <li>
                        <a href='" . site_url('sales/payments/$1') . "'
                           data-toggle='ajax'>
                            <i class='fa fa-money'></i>
                            " . lang('view_payments') . "
                        </a>
                    </li>

                    <li>
                        <a href='" . site_url('sales/add_payment/$1') . "'
                           data-toggle='ajax'>
                            <i class='fa fa-briefcase'></i>
                            " . lang('add_payment') . "
                        </a>
                    </li>

                    <li>
                        <a href='" . site_url('sales/delete/$1') . "'
                           class='text-danger erp-delete-sale'>
                            <i class='fa fa-trash'></i>
                            " . lang('delete') . "
                        </a>
                    </li>

                </ul>
            </div>
        </div>
        ",
        'id'
    );

    echo $this->datatables->generate();
}


public function bulk_due_payment()
{
    $sale_ids = $this->input->post('sale_ids');
    $amount   = (float) $this->input->post('amount');

    if (!$sale_ids || $amount <= 0) {
        show_error('Invalid request');
    }

    $this->db->trans_start();

    $sales = $this->db
        ->where_in('id', $sale_ids)
        ->where('status !=', 'paid')
        ->order_by('date', 'asc')
        ->get('sales')->result();

    foreach ($sales as $sale) {

        if ($amount <= 0) break;

        $due = $sale->grand_total - $sale->paid;

        if ($amount >= $due) {
            $pay = $due;
            $status = 'paid';
        } else {
            $pay = $amount;
            $status = 'partial';
        }

        // Update sale
        $this->db->update('sales', [
            'paid' => $sale->paid + $pay,
            'status' => $status
        ], ['id' => $sale->id]);

        // Payment log
        $this->db->insert('payments', [
            'sale_id' => $sale->id,
            'amount' => $pay,
            'date' => date('Y-m-d H:i:s')
        ]);

        $amount -= $pay;
    }

    $this->db->trans_complete();

    echo json_encode(['status' => 'success']);
}


    private function sendSalesDeliveryJson(array $payload, $http_status = 200)
    {
        $payload['csrf_hash'] = $this->security->get_csrf_hash();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->output->enable_profiler(false);
        $this->output
            ->set_status_header((int) $http_status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ))
            ->_display();
        exit;
    }

    private function canAccessSalesDelivery($sale, $admin_only = false)
    {
        if (!$sale) {
            return false;
        }

        if ($this->Owner) {
            return true;
        }

        if (
            isset($sale->store_id) &&
            (int) $sale->store_id !==
                (int) $this->session->userdata('store_id')
        ) {
            return false;
        }

        if ($admin_only) {
            return (bool) $this->Admin;
        }

        return $this->Admin ||
            (bool) $this->session->userdata('view_right') ||
            (int) ($sale->created_by ?? 0) ===
                (int) $this->session->userdata('user_id');
    }

    private function salesDeliveryContext($sale, $reason)
    {
        return [
            'reason' => (string) $reason,
            'user_id' => (int) $this->session->userdata('user_id'),
            'store_id' => (int) ($sale->store_id ??
                $this->session->userdata('store_id')),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        ];
    }

    public function delivery_form()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'POST request လိုအပ်ပါသည်။',
            ], 405);
        }

        $sale_id = (int) $this->input->post('sale_id');
        $sale = $this->sales_model->getSaleByID($sale_id);

        if (!$sale) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'အရောင်းဘောင်ချာကို ရှာမတွေ့ပါ။',
            ], 404);
        }

        if (!$this->canAccessSalesDelivery($sale, false)) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => lang('access_denied'),
            ], 403);
        }

        try {
            if (!$this->sales_model->ensureSalesDeliveryTables()) {
                throw new RuntimeException(
                    'Delivery log table ဖန်တီး၍မရပါ။'
                );
            }

            $delivery = $this->sales_model->getSaleDeliveryStatus($sale_id);
            $store = $this->db
                ->select('id, name')
                ->where('id', (int) $sale->store_id)
                ->get('stores', 1)
                ->row();
            $items = [];

            foreach ($delivery['items'] as $item) {
                $items[] = [
                    'id' => (int) $item->id,
                    'product_id' => (int) ($item->product_id ?? 0),
                    'product_name' => (string) ($item->product_name ?? '-'),
                    'product_code' => (string) ($item->product_code ?? ''),
                    'unit_name' => (string) ($item->unit_name ?? ''),
                    'ordered' => round((float) $item->ordered_qty, 4),
                    'delivered' => round((float) $item->delivered_qty, 4),
                    'remaining' => round((float) $item->remaining_qty, 4),
                    'history_count' => (int) $this->db
                        ->where('sale_id', $sale_id)
                        ->where('sale_item_id', (int) $item->id)
                        ->count_all_results('sale_delivery_logs'),
                ];
            }

            $this->sendSalesDeliveryJson([
                'success' => true,
                'sale' => [
                    'id' => $sale_id,
                    'reference_no' => (string) ($sale->reference_no ?? ''),
                    'customer_name' => (string) ($sale->customer_name ?? '-'),
                    'store_name' => (string) ($store->name ?? '-'),
                ],
                'summary' => [
                    'status' => (int) $delivery['status'],
                    'ordered' => round((float) $delivery['ordered'], 4),
                    'delivered' => round((float) $delivery['delivered'], 4),
                    'remaining' => round((float) $delivery['remaining'], 4),
                ],
                'items' => $items,
                'default_delivered_at' => date('Y-m-d\TH:i'),
            ]);
        } catch (Throwable $e) {
            log_message(
                'error',
                '[SALE DELIVERY FORM FAILED] Sale ID: ' . $sale_id .
                ' | User ID: ' .
                (int) $this->session->userdata('user_id') .
                ' | Error: ' . $e->getMessage()
            );

            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' =>
                    'ပို့ဆောင်မှုအချက်အလက် ရယူ၍မရပါ။ ' .
                    '(' . $e->getMessage() . ')',
            ], 500);
        }
    }

    public function deliver_products()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'POST request လိုအပ်ပါသည်။',
            ], 405);
        }

        $sale_id = (int) $this->input->post('sale_id');
        $sale = $this->sales_model->getSaleByID($sale_id);

        if (!$sale) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'အရောင်းဘောင်ချာကို ရှာမတွေ့ပါ။',
            ], 404);
        }

        if (!$this->canAccessSalesDelivery($sale, false)) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => lang('access_denied'),
            ], 403);
        }

        $delivered_at_input = trim((string) $this->input->post(
            'delivered_at',
            true
        ));
        $timestamp = strtotime($delivered_at_input);

        if ($delivered_at_input === '' || $timestamp === false) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'ပို့ဆောင်သည့်ရက်စွဲ မမှန်ပါ။',
            ], 422);
        }

        $note = trim(strip_tags((string) $this->input->post(
            'delivery_note',
            true
        )));
        $note = function_exists('mb_substr')
            ? mb_substr($note, 0, 500, 'UTF-8')
            : substr($note, 0, 500);
        $posted_qty = $this->input->post('delivery_qty');
        $delivery_map = [];

        if (is_array($posted_qty)) {
            foreach ($posted_qty as $item_id => $quantity) {
                $item_id = (int) $item_id;
                $quantity = trim((string) $quantity);

                if ($item_id <= 0 || $quantity === '') {
                    continue;
                }

                if (!is_numeric($quantity) || (float) $quantity < 0) {
                    $this->sendSalesDeliveryJson([
                        'success' => false,
                        'message' => 'ပို့မည့်အရေအတွက်တွင် 0 နှင့်အထက် ဂဏန်းသာထည့်ပါ။',
                    ], 422);
                }

                if ((float) $quantity > 0) {
                    $delivery_map[$item_id] = round((float) $quantity, 4);
                }
            }
        }

        if (empty($delivery_map)) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'ပို့မည့် ပစ္စည်းအရေအတွက် တစ်ခုခုထည့်ပါ။',
            ], 422);
        }

        $result = $this->sales_model->recordSaleDelivery(
            $sale_id,
            $delivery_map,
            date('Y-m-d H:i:s', $timestamp),
            $note,
            $this->salesDeliveryContext(
                $sale,
                'Product delivery saved from Sales popup'
            )
        );

        $this->sendSalesDeliveryJson(
            $result,
            !empty($result['success']) ? 200 : 409
        );
    }

    public function delivery_history()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'POST request လိုအပ်ပါသည်။',
            ], 405);
        }

        $sale_id = (int) $this->input->post('sale_id');
        $sale_item_id = (int) $this->input->post('sale_item_id');
        $sale = $this->sales_model->getSaleByID($sale_id);

        if (!$sale || !$this->canAccessSalesDelivery($sale, false)) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => lang('access_denied'),
            ], 403);
        }

        $item_exists = (int) $this->db
            ->where('id', $sale_item_id)
            ->where('sale_id', $sale_id)
            ->count_all_results('sale_items');

        if (!$item_exists) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'Sale item ကို ရှာမတွေ့ပါ။',
            ], 404);
        }

        $history = $this->sales_model->getSaleDeliveryHistory(
            $sale_id,
            $sale_item_id
        );
        $rows = [];

        foreach ($history as $row) {
            $rows[] = [
                'id' => (int) $row->id,
                'quantity' => round((float) $row->quantity, 4),
                'delivered_at' => (string) $row->delivered_at,
                'note' => (string) ($row->note ?? ''),
                'created_by_name' => (string) ($row->created_by_name ?? '-'),
                'stock_deducted' => (int) $row->stock_deducted,
            ];
        }

        $this->sendSalesDeliveryJson([
            'success' => true,
            'history' => $rows,
            'can_manage' => (bool) ($this->Admin || $this->Owner),
        ]);
    }

    public function update_delivery_history()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'POST request လိုအပ်ပါသည်။',
            ], 405);
        }

        $delivery_id = (int) $this->input->post('delivery_id');
        $quantity = (float) $this->input->post('quantity');
        $delivery = $this->sales_model->getSaleDeliveryByID($delivery_id);
        $sale = $delivery
            ? $this->sales_model->getSaleByID((int) $delivery->sale_id)
            : false;

        if (!$sale || !$this->canAccessSalesDelivery($sale, true)) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => lang('access_denied'),
            ], 403);
        }

        if ($quantity <= 0) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => '0 ထက်ကြီးသော အရေအတွက်ထည့်ပါ။',
            ], 422);
        }

        $result = $this->sales_model->updateSaleDelivery(
            $delivery_id,
            round($quantity, 4),
            $this->salesDeliveryContext(
                $sale,
                'Delivery history quantity edited from popup'
            )
        );

        $this->sendSalesDeliveryJson(
            $result,
            !empty($result['success']) ? 200 : 409
        );
    }

    public function delete_delivery_history()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'POST request လိုအပ်ပါသည်။',
            ], 405);
        }

        $delivery_id = (int) $this->input->post('delivery_id');
        $delivery = $this->sales_model->getSaleDeliveryByID($delivery_id);
        $sale = $delivery
            ? $this->sales_model->getSaleByID((int) $delivery->sale_id)
            : false;

        if (!$sale || !$this->canAccessSalesDelivery($sale, true)) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => lang('access_denied'),
            ], 403);
        }

        $result = $this->sales_model->deleteSaleDelivery(
            $delivery_id,
            $this->salesDeliveryContext(
                $sale,
                'Delivery history deleted from popup'
            )
        );

        $this->sendSalesDeliveryJson(
            $result,
            !empty($result['success']) ? 200 : 409
        );
    }

    /** Backward-compatible route: old clients deliver every remaining item. */
    public function deliver_preorder()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'POST request လိုအပ်ပါသည်။',
            ], 405);
        }

        $sale_id = (int) $this->input->post('sale_id');
        $sale = $this->sales_model->getSaleByID($sale_id);

        if (!$sale || !$this->canAccessSalesDelivery($sale, false)) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => lang('access_denied'),
            ], 403);
        }

        $delivery = $this->sales_model->getSaleDeliveryStatus($sale_id);
        $delivery_map = [];

        foreach ($delivery['items'] as $item) {
            if ((float) $item->remaining_qty > 0) {
                $delivery_map[(int) $item->id] =
                    (float) $item->remaining_qty;
            }
        }

        if (empty($delivery_map)) {
            $this->sendSalesDeliveryJson([
                'success' => false,
                'message' => 'ပစ္စည်းအားလုံး ပို့ပြီးဖြစ်ပါသည်။',
            ], 409);
        }

        $result = $this->sales_model->recordSaleDelivery(
            $sale_id,
            $delivery_map,
            date('Y-m-d H:i:s'),
            '',
            $this->salesDeliveryContext(
                $sale,
                'Legacy mark-all delivery request'
            )
        );

        $this->sendSalesDeliveryJson(
            $result,
            !empty($result['success']) ? 200 : 409
        );
    }





    public function index()
    {
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('sales');
        $this->data['customers']  = $this->reports_model->getAllCustomers();
        $bc                       = [['link' => '#', 'page' => lang('sales')]];
        $meta                     = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->page_construct('sales/sales', $this->data, $meta);
    }
    
    public function get_sales_summary()
{
    $this->db
        ->select(
            "
            COUNT(id) AS sales_records,
            COALESCE(SUM(grand_total), 0) AS grand_total,
            COALESCE(SUM(paid), 0) AS paid_total,
            COALESCE(
                SUM(
                    GREATEST(
                        COALESCE(grand_total, 0) -
                        COALESCE(paid, 0),
                        0
                    )
                ),
                0
            ) AS due_total,
            COALESCE(
                SUM(
                    CASE
                        WHEN status IN ('partial', 'due')
                             OR COALESCE(grand_total, 0) >
                                COALESCE(paid, 0)
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS outstanding_sales
            ",
            false
        )
        ->from('sales');

    /*
     * Keep the same access restrictions used by get_sales().
     */
    $store_id = $this->session->userdata('store_id');

    if ($store_id !== false && $store_id !== null && $store_id !== '') {
        $this->db->where('store_id', $store_id);
    }

    if (
        !$this->Admin &&
        !$this->session->userdata('view_right')
    ) {
        $this->db->where(
            'created_by',
            $this->session->userdata('user_id')
        );
    }

    $summary = $this->db->get()->row_array();

    $response = [
        'success' => true,
        'summary' => [
            'sales_records' => (int) (
                $summary['sales_records'] ?? 0
            ),
            'grand_total' => (float) (
                $summary['grand_total'] ?? 0
            ),
            'paid_total' => (float) (
                $summary['paid_total'] ?? 0
            ),
            'due_total' => (float) (
                $summary['due_total'] ?? 0
            ),
            'outstanding_sales' => (int) (
                $summary['outstanding_sales'] ?? 0
            ),
        ],
    ];

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

    public function opened()
    {
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('opened_bills');
        $bc                       = [['link' => '#', 'page' => lang('opened_bills')]];
        $meta                     = ['page_title' => lang('opened_bills'), 'bc' => $bc];
        $this->page_construct('sales/opened', $this->data, $meta);
    }

    public function payment_note($id = null)
    {
        $payment                  = $this->sales_model->getPaymentByID($id);
        $inv                      = $this->sales_model->getSaleByID($payment->sale_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = $this->lang->line('payment_note');

        $this->load->view($this->theme . 'sales/payment_note', $this->data);
    }

    /* -------------------------------------------------------------------------------- */

    public function payments($id = null)
    {
        $this->data['payments'] = $this->sales_model->getSalePayments($id);
        $this->load->view($this->theme . 'sales/payments', $this->data);
    }

    public function status()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('sales');
        }
        $this->form_validation->set_rules('sale_id', lang('sale_id'), 'required');
        $this->form_validation->set_rules('status', lang('status'), 'required');

        if ($this->form_validation->run() == true) {
            $this->sales_model->updateStatus($this->input->post('sale_id', true), $this->input->post('status', true));
            $this->session->set_flashdata('message', lang('status_updated'));
            redirect('sales');
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect('sales');
        }
    }
}
