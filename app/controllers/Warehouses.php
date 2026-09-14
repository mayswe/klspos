<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Warehouses extends MY_Controller{
    
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('Warehouse_model');
    }
    
    public function index()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['warehouses'] = $this->Warehouse_model->get_all();
        $this->data['page_title'] = lang('warehouses');
        $bc                       = [['link' => '#', 'page' => lang('warehouses')]];
        $meta                     = ['page_title' => lang('warehouses'), 'bc' => $bc];
        $this->page_construct('warehouses/index', $this->data, $meta);
    }

    public function add()
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->form_validation->set_rules('code', lang('code'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'), 
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'), 
                'email' => $this->input->post('email'),
                'status' => 1,
            ];

            
        }

        if ($this->form_validation->run() == true && $this->Warehouse_model->insert($data)) {
            $this->session->set_flashdata('message', lang('warehouse_added'));
            redirect($this->mobilePageUrl('warehouses'));
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('add_warehouses');
            $bc                       = [['link' => site_url('warehouses'), 'page' => lang('warehouses')], ['link' => '#', 'page' => lang('add_warehouses')]];
            $meta                     = ['page_title' => lang('add_warehouses'), 'bc' => $bc];
            $this->page_construct('warehouses/add', $this->data, $meta);
        }
    }

    public function edit($id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('code', lang('code'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
            'code' => $this->input->post('code'),
            'name' => $this->input->post('name'),
            'address' => $this->input->post('address'),
            'phone' => $this->input->post('phone'),
            'email' => $this->input->post('email'),
            'status' => $this->input->post('status'),
            
            
        ];
        
        }

        if ($this->form_validation->run() == true && $this->Warehouse_model->update($id, $data)) {
            $this->session->set_flashdata('message', lang('warehouse_updated'));
            redirect($this->mobilePageUrl('warehouses'));
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouse']   = $this->Warehouse_model->get_by_id($id);
            $this->data['page_title'] = lang('edit_warehouses');
            $bc                       = [['link' => site_url('warehouses'), 'page' => lang('warehouses')], ['link' => '#', 'page' => lang('edit_warehouses')]];
            $meta                     = ['page_title' => lang('edit_warehouses'), 'bc' => $bc];
            $this->page_construct('warehouses/edit', $this->data, $meta);
        }
    }

    
    public function delete($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
        }
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->Warehouse_model->delete($id)) {
            $this->session->set_flashdata('message', lang('warehouse_deleted'));
            redirect($this->mobilePageUrl('warehouses'));
        }
    }

    public function get_warehouses()
{
    $this->load->library('datatables');
    $this->datatables->select('id, code, name, address, phone, email, status');
    $this->datatables->from('warehouses');
    $this->datatables->add_column('Actions',
    "<div class='text-center'>
        <div class='btn-group'>
            <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
            </button>
            <ul class='dropdown-menu dropdown-menu-right'>
                <li>
                    <a href='javascript:void(0);' 
                        class='edit-warehouses' 
                        data-id='$1' 
                        data-code=\"$2\" 
                        data-name=\"$3\" 
                        data-address=\"$4\" 
                        data-phone=\"$5\" 
                        data-email=\"$6\" 
                        data-status=\"$7\">
                        <i class='fa fa-edit'></i> " . lang('edit') . "
                    </a>

                </li>
                <li>
                    <a href='" . site_url('warehouses/delete/$1') . "' 
                       class='tip text-danger' 
                       title='" . lang('delete') . "' 
                       onClick=\"return confirm('" . lang('alert_x_warehouse') . "')\">
                       <i class='fa fa-trash'></i> " . lang('delete') . "
                    </a>
                </li>
            </ul>
        </div>
    </div>",
'id, code, name, address, phone, email, status');
    $this->datatables->unset_column('id');
    echo $this->datatables->generate();
}

}