<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Currencies extends MY_Controller{
    
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('Currency_model');
    }
    
    public function index()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['currencies'] = $this->Currency_model->get_all();
        $this->data['page_title'] = lang('exchangerate');
        $bc                       = [['link' => '#', 'page' => lang('exchangerate')]];
        $meta                     = ['page_title' => lang('exchangerate'), 'bc' => $bc];
        $this->page_construct('currencies/index', $this->data, $meta);
    }

    public function add()
    {
        

        $this->form_validation->set_rules('currency_name', lang('currency_name'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'currency_name' => $this->input->post('currency_name'),
                'currency_code' => $this->input->post('currency_code'), 
                'exchange_rate' => $this->input->post('exchange_rate'),
                'status' => 1,
                'date' => $this->input->post('date')
            ];

            
        }

        if ($this->form_validation->run() == true && $this->Currency_model->insert($data)) {
            $this->session->set_flashdata('message', lang('category_added'));
            redirect('currencies');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('add_exchangerate');
            $bc                       = [['link' => site_url('categories'), 'page' => lang('categories')], ['link' => '#', 'page' => lang('add_exchangerate')]];
            $meta                     = ['page_title' => lang('add_exchangerate'), 'bc' => $bc];
            $this->page_construct('currencies/add', $this->data, $meta);
        }
    }

    public function edit($id = null)
    {
        
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('currency_name', lang('currency_name'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
            'currency_name' => $this->input->post('currency_name'),
            'currency_code' => $this->input->post('currency_code'),
            'exchange_rate' => $this->input->post('exchange_rate'),
            'status' => $this->input->post('status'),
            'date' => $this->input->post('date')
            
        ];
        
        }

        if ($this->form_validation->run() == true && $this->Currency_model->update($id, $data)) {
            $this->session->set_flashdata('message', lang('category_updated'));
            redirect('currencies');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currency']   = $this->Currency_model->get_by_id($id);
            $this->data['page_title'] = lang('edit_exchangerate');
            $bc                       = [['link' => site_url('categories'), 'page' => lang('exchangerate')], ['link' => '#', 'page' => lang('edit_exchangerate')]];
            $meta                     = ['page_title' => lang('edit_exchangerate'), 'bc' => $bc];
            $this->page_construct('currencies/edit', $this->data, $meta);
        }
    }

    
    public function delete($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
        }
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->Currency_model->delete($id)) {
            $this->session->set_flashdata('message', lang('category_deleted'));
            redirect('currencies');
        }
    }

    public function get_exchangerate()
{
    $this->load->library('datatables');
    $this->datatables->select('id, currency_name, currency_code, exchange_rate, status, date');
    $this->datatables->from('currencies');
    $this->datatables->add_column('Actions',
        "<div class='text-center'>
            <div class='btn-group'>
                <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                    <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
                </button>
                <ul class='dropdown-menu dropdown-menu-right'>
                    <li>
                        <a href='javascript:void(0);' 
                            class='edit-currency' 
                            data-id='$1' 
                            data-date=\"$6\"
                            data-name=\"$2\" 
                            data-code=\"$3\" 
                            data-rate=\"$4\" 
                            data-status=\"$5\">
                            <i class='fa fa-edit'></i> " . lang('edit') . "
                        </a>
                    </li>
                    <li>
                        <a href='" . site_url('currencies/delete/$1') . "' 
                        class='tip text-danger' 
                        title='" . lang('delete') . "' 
                        onClick=\"return confirm('" . lang('alert_x_category') . "')\">
                        <i class='fa fa-trash'></i> " . lang('delete') . "
                        </a>
                    </li>
                </ul>
            </div>
        </div>",'id, currency_name, currency_code, exchange_rate, status, date');
    $this->datatables->unset_column('id');
    echo $this->datatables->generate();
}

}