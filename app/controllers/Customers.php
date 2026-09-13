<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('customers_model');
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

        if ($this->form_validation->run() == true && $cid = $this->customers_model->addCustomer($data)) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'success', 'msg' => $this->lang->line('customer_added'), 'id' => $cid, 'val' => $data['name']]);
                die();
            }
            $this->session->set_flashdata('message', $this->lang->line('customer_added'));
            redirect('customers');
        } else {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'failed', 'msg' => validation_errors()]);
                die();
            }

            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_customer');
            $bc                       = [['link' => site_url('customers'), 'page' => lang('customers')], ['link' => '#', 'page' => lang('add_customer')]];
            $meta                     = ['page_title' => lang('add_customer'), 'bc' => $bc];
            $this->page_construct('customers/add', $this->data, $meta);
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

        if ($this->customers_model->deleteCustomer($id)) {
            $this->session->set_flashdata('message', lang('customer_deleted'));
            redirect('customers');
        }
    }

    public function edit($id = null)
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', $this->lang->line('access_denied'));
            redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'valid_email');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'      => $this->input->post('name'),
                'email'     => $this->input->post('email'),
                'phone'     => $this->input->post('phone'),
                'cf1'       => $this->input->post('cf1'),
                'cf2'       => $this->input->post('cf2'),
                'group_id'  => $this->input->post('group'),
            ];
        }

        if ($this->form_validation->run() == true && $this->customers_model->updateCustomer($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('customer_updated'));
            redirect('customers');
        } else {
            $this->data['customer']   = $this->customers_model->getCustomerByID($id);
            $this->data['customergroup']   = $this->customers_model->getAllCustomergroup();
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_customer');
            $bc                       = [['link' => site_url('customers'), 'page' => lang('customers')], ['link' => '#', 'page' => lang('edit_customer')]];
            $meta                     = ['page_title' => lang('edit_customer'), 'bc' => $bc];
            $this->page_construct('customers/edit', $this->data, $meta);
        }
    }

    public function get_customers()
    {
        $this->load->library('datatables');

        $this->datatables
            ->select('
                customers.id as id,
                customers.name,
                customers.phone,
                customers.email,
                customers.cf1,
                customers.cf2,
                customergroup.name as group_name
            ')
            ->from('customers')
            ->join('customergroup', 'customergroup.id = customers.group_id', 'left')

            ->add_column('Actions', "
                <div class='text-center'>
                    <div class='btn-group'>
                        <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown'>
                            <i class='fa fa-cog'></i> ".$this->lang->line('actions')." <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-right'>
                            <li>
                                <a class='tip' title='".$this->lang->line('edit')."' href='".site_url('customers/edit/$1')."'>
                                    <i class='fa fa-edit'></i> ".$this->lang->line('edit')."
                                </a>
                            </li>
                            <li>
                                <a class='tip text-danger' title='".$this->lang->line('delete')."' 
                                href='".site_url('customers/delete/$1')."' 
                                onclick=\"return confirm('".$this->lang->line('alert_x_customer')."')\">
                                    <i class='fa fa-trash'></i> ".$this->lang->line('delete')."
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
        $this->data['page_title'] = lang('customers');
        $bc                       = [['link' => '#', 'page' => lang('customers')]];
        $meta                     = ['page_title' => lang('customers'), 'bc' => $bc];
        $this->page_construct('customers/index', $this->data, $meta);
    }

    public function customergroup()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['customergroup'] = $this->site->getAllCustomergroup();
        
        $this->data['group'] = $this->customers_model->getCustomergroupById($id);
        $this->data['customers'] = $this->customers_model->getAllCustomers();

        $this->data['page_title'] = lang('customer_groups');
        $bc                       = [['link' => '#', 'page' => lang('customer_groups')]];
        $meta                     = ['page_title' => lang('customer_groups'), 'bc' => $bc];
        $this->page_construct('customers/customergroup', $this->data, $meta);
    }

     public function get_customergroup()
    {
        $this->load->library('datatables');
        $this->datatables->select('id, image, code, name,');
        $this->datatables->from('customergroup');
        $this->datatables->add_column('Actions',
            "<div class='text-center'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-right'>
                        <li>
                            <a href='javascript:void(0);' 
                            class='edit-customergroup' 
                            data-id='$1' 
                            data-code=\"$3\" 
                            data-name=\"$4\" 
                            data-image=\"$2\">
                            <i class='fa fa-edit'></i> " . lang('edit') . "
                            </a>
                        </li>
                        <li>
                            <a href='" . site_url('customers/customergroupdelete/$1') . "' class='tip text-danger' title='" . lang('delete') . "' onClick=\"return confirm('" . lang('alert_x_category') . "')\">
                                <i class='fa-solid fa-trash'></i> " . lang('delete') . "
                            </a>
                        </li>
                    </ul>
                </div>
            </div>",
        'id, image, code, name');


                $this->datatables->unset_column('id');
                echo $this->datatables->generate();
    }

    public function customergroupadd()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->form_validation->set_rules('name', lang('customergroup_name'), 'required');

        if ($this->form_validation->run() == true) {
            $data = ['code' => $this->input->post('code'), 'name' => $this->input->post('name')];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'uploads/';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size']      = '500';
                $config['max_width']     = '800';
                $config['max_height']    = '800';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->upload->set_flashdata('error', $error);
                    redirect('customers/add');
                }

                $photo         = $this->upload->file_name;
                $data['image'] = $photo;

                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = 'uploads/' . $photo;
                $config['new_image']      = 'uploads/thumbs/' . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = 50;
                $config['height']         = 50;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    $this->upload->set_flashdata('error', $this->image_lib->display_errors());
                    redirect('customers/customergroupadd');
                }
            }
        }

        if ($this->form_validation->run() == true && $this->customers_model->addCustomergroup($data)) {
            $this->session->set_flashdata('message', lang('customergroup_added'));
            redirect('customers/customergroup');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('add_customergroup');
            $bc                       = [['link' => site_url('customers'), 'page' => lang('customers')], ['link' => '#', 'page' => lang('add_category')]];
            $meta                     = ['page_title' => lang('add_customergroup'), 'bc' => $bc];
            $this->page_construct('customers/customergroup', $this->data, $meta);
        }
    }

    public function customergroupdelete($id = null)
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

        if ($this->customers_model->deleteCustomergroup($id)) {
            $this->session->set_flashdata('message', lang('customergroup_deleted'));
            redirect('customers/customergroup');
        }
    }

    public function customergroupupdate($id = null)
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
    
        $id = $this->input->post('id');
    
        $this->form_validation->set_rules('name', lang('name'), 'required');
    
        if ($this->form_validation->run() == true) {
    
            $data = [
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
            ];
    
            // 1️⃣ Update customer group table first
            if ($this->customers_model->updateCustomergroup($id, $data)) {
    
                // 2️⃣ Get selected customers from form
                $customers = $this->input->post('customers');
    
                // 3️⃣ Remove this group from all customers first
                $this->db->where('group_id', $id);
                $this->db->update('tec_customers', ['group_id' => NULL]);
    
                // 4️⃣ Assign selected customers to this group
                if (!empty($customers)) {
                    $this->db->where_in('id', $customers);
                    $this->db->update('tec_customers', ['group_id' => $id]);
                }
    
                $this->session->set_flashdata('message', lang('customergroup_updated'));
                redirect('customers/customergroup');
            }
        }
    
        // If validation fails
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['page_title'] = lang('edit_customergroup');
    
        $bc = [
            ['link' => site_url('customers'), 'page' => lang('customers')],
            ['link' => '#', 'page' => lang('edit_customergroup')]
        ];
    
        $meta = ['page_title' => lang('edit_customergroup'), 'bc' => $bc];
    
        $this->page_construct('customers/customergroup', $this->data, $meta);
    }





}
