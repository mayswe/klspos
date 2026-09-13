<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->load->library('form_validation');
        $this->load->model('suppliers_model');
    }

    public function add()
    {
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'valid_email');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'email'     => $this->input->post('email'),
                'phone'     => $this->input->post('phone'),
                'cf1'       => $this->input->post('cf1'),
                'cf2'       => $this->input->post('cf2'),
            ];
        }

        if ($this->form_validation->run() == true && $cid = $this->suppliers_model->addSupplier($data)) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'success', 'msg' => $this->lang->line('supplier_added'), 'id' => $cid, 'val' => $data['name']]);
                die();
            }
            $this->session->set_flashdata('message', $this->lang->line('supplier_added'));
            redirect('suppliers');
        } else {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'failed', 'msg' => validation_errors()]);
                die();
            }

            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_supplier');
            $bc                       = [['link' => site_url('suppliers'), 'page' => lang('suppliers')], ['link' => '#', 'page' => lang('add_supplier')]];
            $meta                     = ['page_title' => lang('add_supplier'), 'bc' => $bc];
            $this->page_construct('suppliers/add', $this->data, $meta);
        }
    }

    public function delete($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
            redirect('pos');
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }

        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        if ($this->suppliers_model->deleteSupplier($id)) {
            $this->session->set_flashdata('message', lang('supplier_deleted'));
            redirect('suppliers');
        }
    }

    public function edit($id = null)
    {
       if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', $this->lang->line('access_denied'));
            redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'valid_email');

        if ($this->form_validation->run() == true) {
            $data = ['name' => $this->input->post('name'),
                'email'     => $this->input->post('email'),
                'phone'     => $this->input->post('phone'),
                'cf1'       => $this->input->post('cf1'),
                'cf2'       => $this->input->post('cf2'),
            ];
        }

        if ($this->form_validation->run() == true && $this->suppliers_model->updateSupplier($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('supplier_updated'));
            redirect('suppliers');
        } else {
            $this->data['supplier']   = $this->suppliers_model->getSupplierByID($id);
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_supplier');
            $bc                       = [['link' => site_url('suppliers'), 'page' => lang('suppliers')], ['link' => '#', 'page' => lang('edit_supplier')]];
            $meta                     = ['page_title' => lang('edit_supplier'), 'bc' => $bc];
            $this->page_construct('suppliers/edit', $this->data, $meta);
        }
    }

    public function get_suppliers()
    {
        $this->load->library('datatables');
        $this->datatables
        ->select('id, name, phone, email, cf1, cf2')
        ->from('suppliers')
        ->add_column('Actions', "
            <div class='text-center'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        <i class='fa fa-cog'></i> " . $this->lang->line('actions') . " <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-right'>
                        <li>
                            <a class='tip' title='" . $this->lang->line('edit') . "' href='" . site_url('suppliers/edit/$1') . "'>
                                <i class='fa fa-edit'></i> " . $this->lang->line('edit') . "
                            </a>
                        </li>
                        <li>
                            <a class='tip text-danger' title='" . $this->lang->line('delete') . "' href='" . site_url('suppliers/delete/$1') . "' onclick=\"return confirm('" . $this->lang->line('alert_x_supplier') . "')\">
                                <i class='fa-solid fa-trash'></i> " . $this->lang->line('delete') . "
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            ", 'id')
            ->unset_column('id');

        echo $this->datatables->generate();
    }

    public function index()
    {
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('suppliers');
        $bc                       = [['link' => '#', 'page' => lang('suppliers')]];
        $meta                     = ['page_title' => lang('suppliers'), 'bc' => $bc];
        $this->page_construct('suppliers/index', $this->data, $meta);
    }

    
    public function advances($id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->data['suppliers'] = $this->suppliers_model->get_all_suppliers();
        $this->data['advances'] = $this->suppliers_model->get_all_advances();
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('supplier_advances');
        $bc                       = [['link' => site_url('suppliers'), 'page' => lang('suppliers')], ['link' => '#', 'page' => lang('supplier_advances')]];
        $meta                     = ['page_title' => lang('expenses'), 'bc' => $bc];
        $this->page_construct('suppliers/advances', $this->data, $meta);
    }

    public function add_advance()
    {
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array(
                'supplier_id' => $this->input->post('supplier_id'),
                'date'        => $this->input->post('date'),
                'amount'      => $this->input->post('amount'),
                'note'        => $this->input->post('note'),
                'created_by'  => $this->session->userdata('user_id'),
                'created_at'  => date('Y-m-d H:i:s')
            );

            if ($this->suppliers_model->add_advance($data)) {
                $this->session->set_flashdata('message', 'Advance Payment Added Successfully');
                redirect('suppliers/advances');
            }
        } else {
            $this->data['suppliers'] = $this->suppliers_model->get_all_suppliers();
            $this->page_construct('suppliers/add_advance', $this->data);
        }
    }


    public function edit_advance($id = null)
{
    if (!$id) {
        $this->session->set_flashdata('error', 'Invalid Advance Payment ID');
        redirect('suppliers/advances');
    }

    $this->form_validation->set_rules('supplier_id', 'Supplier', 'required');
    $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');

    if ($this->form_validation->run() == true) {
        $data = array(
            'supplier_id' => $this->input->post('supplier_id'),
            'date'        => $this->input->post('date'),
            'amount'      => $this->input->post('amount'),
            'note'        => $this->input->post('note'),
        );

        if ($this->suppliers_model->update_advance($id, $data)) {
            $this->session->set_flashdata('message', 'Advance Payment updated successfully.');
            redirect('suppliers/advances');
        } else {
            $this->session->set_flashdata('error', 'Failed to update advance payment.');
            redirect('suppliers/advances');
        }

    } else {
        $this->data['advance']   = $this->suppliers_model->get_advance_by_id($id);
        $this->data['suppliers'] = $this->suppliers_model->get_all_suppliers();
        $this->page_construct('suppliers/edit_advance', $this->data);
    }
}

public function edit_opening($id = null)
{
    if (!$id) {
        $this->session->set_flashdata('error', 'Invalid Opening Due ID');
        redirect('suppliers/opening');
    }

    // Validation rules
    $this->form_validation->set_rules('supplier_id', 'Supplier', 'required');
    $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');
    $this->form_validation->set_rules('paid_amount', 'Paid Amount', 'numeric');

    if ($this->form_validation->run() == true) {
        $data = array(
            'supplier_id'  => $this->input->post('supplier_id'),
            'date'         => $this->input->post('date'),
            'voucher_no'   => $this->input->post('voucher_no'),
            'amount'       => $this->input->post('amount'),
            'paid_amount'  => $this->input->post('paid_amount'),
            'note'         => $this->input->post('note'),
        );

        if ($this->suppliers_model->update_opening_due($id, $data)) {
            $this->session->set_flashdata('message', 'Opening Due updated successfully.');
            redirect('suppliers/opening');
        } else {
            $this->session->set_flashdata('error', 'Failed to update Opening Due.');
            redirect('suppliers/opening');
        }

    } else {
        // Load the existing opening due data to populate the edit form
        $this->data['opening_due'] = $this->suppliers_model->get_opening_due_by_id($id);
        $this->data['suppliers']   = $this->suppliers_model->get_all_suppliers();
        $this->page_construct('suppliers/edit_opening', $this->data);
    }
}

public function delete_opening($id = null)
{
    if (!$id) {
        $this->session->set_flashdata('error', 'Invalid Opening Due ID');
        redirect('suppliers/opening');
    }

    if ($this->suppliers_model->delete_opening_due($id)) {
        $this->session->set_flashdata('message', 'Opening Due deleted successfully.');
    } else {
        $this->session->set_flashdata('error', 'Failed to delete Opening Due.');
    }

    redirect('suppliers/opening');
}

    public function get_supplieradvances()
{
    $this->load->library('datatables');
    $this->datatables
        ->select('supplier_advances.id, supplier_advances.supplier_id, suppliers.name, supplier_advances.date, supplier_advances.amount, supplier_advances.note,  supplier_advances.created_at')
        ->from('supplier_advances')
        ->join('suppliers', 'suppliers.id = supplier_advances.supplier_id')
        ->add_column('Actions', "
            <div class='text-center'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown'>
                        <i class='fa fa-cog'></i> " . $this->lang->line('actions') . " <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-right'>
                        <li>
                            <a href='javascript:void(0);' 
                                class='edit-SupplierAdvances' 
                                data-id='$1' 
                                data-supplier_id=\"$2\" 
                                data-date=\"$3\" 
                                data-amount=\"$4\" 
                                data-note=\"$5\">
                                <i class='fa fa-edit'></i> " . lang('edit') . "
                            </a>
                        </li>
                        <li>
                            <a class='tip text-danger' title='" . $this->lang->line('delete') . "' href='" . site_url('suppliers/delete_advance/$1') . "' onclick=\"return confirm('" . $this->lang->line('alert_x_supplier') . "')\">
                                <i class='fa fa-trash'></i> " . $this->lang->line('delete') . "
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        ", 'id,supplier_id,date,amount,note');

    echo $this->datatables->generate();
}



    

    public function delete_advance($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
            redirect('pos');
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }

        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        if ($this->suppliers_model->deleteAdvance($id)) {
            $this->session->set_flashdata('message', lang('supplier_deleted'));
            redirect('suppliers/advances');
        }
    }



    public function opening($id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->data['suppliers'] = $this->suppliers_model->get_all_suppliers();
        $this->data['opening'] = $this->suppliers_model->get_all_opening();
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('supplier_opening');
        $bc                       = [['link' => site_url('suppliers'), 'page' => lang('suppliers')], ['link' => '#', 'page' => lang('supplier_advances')]];
        $meta                     = ['page_title' => lang('expenses'), 'bc' => $bc];
        $this->page_construct('suppliers/opening', $this->data, $meta);
    }

public function add_opening()
    {
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');

        if ($this->form_validation->run() == true) {
            $data = array(
                'supplier_id' => $this->input->post('supplier_id'),
                'voucher_no'      => $this->input->post('voucher_no'),
                'date'        => $this->input->post('date'),
                'amount'      => $this->input->post('amount'),
                'paid_amount'      => $this->input->post('paid_amount'),
                'note'        => $this->input->post('note'),
                'created_by'  => $this->session->userdata('user_id'),
                'created_at'  => date('Y-m-d H:i:s')
            );

            if ($this->suppliers_model->add_opening($data)) {
                $this->session->set_flashdata('message', 'Opening Due Added Successfully');
                redirect('suppliers/opening');
            }
        } else {
            $this->data['suppliers'] = $this->suppliers_model->get_all_suppliers();
            $this->page_construct('suppliers/add_opening', $this->data);
        }
    }

    public function get_all_opening_dues()
{
    $this->load->library('datatables');
    $this->datatables
        ->select('
            tec_supplier_opening_dues.id,
            tec_supplier_opening_dues.supplier_id,
            tec_supplier_opening_dues.voucher_no,
            tec_suppliers.name,
            tec_supplier_opening_dues.date,
            tec_supplier_opening_dues.amount,
            tec_supplier_opening_dues.paid_amount,
            (tec_supplier_opening_dues.amount - tec_supplier_opening_dues.paid_amount) AS due_amount,
            tec_supplier_opening_dues.note,
            tec_supplier_opening_dues.created_at
        ', FALSE) // FALSE prevents CI from escaping
        ->from('tec_supplier_opening_dues')
        ->join('tec_suppliers', 'suppliers.id = supplier_opening_dues.supplier_id', 'left')
        ->add_column('Actions', "
            <div class='text-center'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown'>
                        <i class='fa fa-cog'></i> ".$this->lang->line('actions')." <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-right'>
                        <li>
                            <a href='javascript:void(0);' 
                               class='edit-OpeningDue' 
                               data-id='$1' 
                               data-supplier_id=\"$2\" 
                               data-date=\"$3\" 
                               data-voucher_no=\"$4\"
                               data-amount=\"$5\" 
                               data-paid_amount=\"$6\"
                               data-note=\"$7\">
                               <i class='fa fa-edit'></i> ".lang('edit')."
                            </a>
                        </li>
                        <li><a href='" . site_url('suppliers/payments/$1') . "' class='tip' title='" . lang('view_payments') . "' data-toggle='ajax'><i class='fa-solid fa-money-bill'></i> " . lang('view_payments') . "</a></li>
                        <li><a href='" . site_url('suppliers/add_payment/$1') . "' class='tip' title='" . lang('add_payment') . "' data-toggle='ajax'><i class='fa fa-briefcase'></i> " . lang('add_payment') . "</a></li>
                        <li>
                            <a class='tip text-danger' title='".$this->lang->line('delete')."' href='".site_url('suppliers/delete_opening/$1')."' onclick=\"return confirm('".$this->lang->line('alert_x_supplier')."')\">
                                <i class='fa fa-trash'></i> ".$this->lang->line('delete')."
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        ", 'id,supplier_id,date,voucher_no,amount,paid_amount,note');

    echo $this->datatables->generate();
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
                'purchase_id'     => $id,
                'supplier_id' => $cid,
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

        if ($this->form_validation->run() == true && $this->suppliers_model->addPayment($payment)) {
            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $purchase                = $this->suppliers_model->getSupplierOpeningDueByID($id);
            $this->data['inv']   = $purchase;

            $this->load->view($this->theme . 'suppliers/add_payment', $this->data);
        }
    }

public function payments($id = null)
    {
        $this->data['payments'] = $this->suppliers_model->getDuePayments($id);
        $this->load->view($this->theme . 'suppliers/payments', $this->data);
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

        if ($this->suppliers_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect('suppliers/opening');
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
                'reference'  => $this->input->post('reference'),
                'amount'     => $this->input->post('amount-paid'),
                'paid_by'    => $this->input->post('paid_by'),
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

        if ($this->form_validation->run() == true && $this->suppliers_model->updatePayment($id, $payment)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            redirect('suppliers/opening');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $payment             = $this->suppliers_model->getSupplierDuePayments($id);
            if ($payment->paid_by != 'cash') {
                $this->session->set_flashdata('error', lang('only_cash_can_be_edited'));
                $this->tec->dd();
            }
            $this->data['payment'] = $payment;
            $this->load->view($this->theme . 'suppliers/edit_payment', $this->data);
        }
    }

    

}
