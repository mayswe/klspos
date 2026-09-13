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
        if (DEMO) {
            $this->session->set_flashdata('error', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('sales');
        }

        if ($this->sales_model->deleteInvoice($id)) {
            $this->session->set_flashdata('message', lang('invoice_deleted'));
            redirect('sales');
        }
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
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->sales_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect('sales');
        }
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
            is_preorder
        ")
        ->from('sales');

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

    // Preorder
    $preorder = $this->input->post('preorder_filter');

    if ($preorder !== null && $preorder !== '') {
        $this->datatables->where(
            'is_preorder',
            (int) $preorder
        );
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
                           class='text-danger'
                           onclick=\"return confirm('" .
                               lang('alert_x_sale') .
                           "')\">
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


public function deliver_preorder()
{
    $sale_id = $this->input->post('sale_id');
    log_message('debug', "🧾 deliver_preorder() called. Sale ID: {$sale_id}");

    if (!$sale_id) {
        echo json_encode(['error' => 'No sale ID provided']);
        return;
    }

    $sale = $this->db->get_where('sales', ['id' => $sale_id])->row();
    if (!$sale) {
        echo json_encode(['error' => 'Sale not found']);
        return;
    }

    if ($sale->is_preorder != 1) {
        echo json_encode(['error' => 'Sale already delivered']);
        return;
    }

    $items = $this->db->get_where('sale_items', ['sale_id' => $sale_id])->result();
    if (!$items) {
        echo json_encode(['error' => 'No items in sale']);
        return;
    }

    log_message('debug', "➡️ Starting preorder delivery using deduct_stock()");

    $this->db->trans_start();

    foreach ($items as $item) {

        log_message('debug', "Processing Item: sale_item_id={$item->id}, product_id={$item->product_id}, unit={$item->unit_id}, preorder_qty={$item->preorder_qty}");
        
        // 🔍 Check stock before deducting
        $available = $this->pos_model->get_available_stock_by_unit($item->product_id, $item->unit_id, $sale->store_id);
        
        if ($available < $item->preorder_qty) {
            $this->db->trans_rollback();
            echo json_encode([
                'error' => "Not enough stock for product ID {$item->product_id}. 
        Available: {$available}, Required: {$item->preorder_qty}"
            ]);
            return;
        }
        

        // 🔥 CALL THE SAME STOCK DEDUCTION FUNCTION
        $done = $this->pos_model->deduct_stock(
            $item->product_id,
            $item->unit_id,
            $item->preorder_qty,
            $sale_id,
            $item->id,
            $sale->store_id
        );

        if (!$done) {
            $this->db->trans_rollback();
            echo json_encode(['error' => "Stock deduction failed for product {$item->product_id}"]);
            return;
        }

        // update sale_item qty = preorder qty
        $this->db->where('id', $item->id)
                 ->update('sale_items', ['quantity' => $item->preorder_qty]);

        log_message('debug', "Updated sale_item {$item->id} quantity = preorder_qty");
    }

    // mark preorder delivered
    $this->db->where('id', $sale_id)->update('sales', ['is_preorder' => 0]);

    $this->db->trans_complete();

    if (!$this->db->trans_status()) {
        echo json_encode(['error' => 'Database transaction failed']);
        return;
    }

    echo json_encode(['success' => 'Preorder delivered & stock deducted']);
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
