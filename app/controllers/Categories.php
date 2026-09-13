<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('categories_model');
    }

    public function add()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->form_validation->set_rules('name', lang('category_name'), 'required');
        
        if ($this->form_validation->run() == true) {
            
            $name = trim($this->input->post('name'));

            // DUPLICATE CATEGORY NAME CHECK
            $existing_category = $this->db
                ->where('name', $name)
                ->get('categories')
                ->row();
    
            if ($existing_category) {
                $this->session->set_flashdata(
                    'error',
                    'Category name "' . html_escape($name) . '" already exists.'
                );
    
                redirect('categories/add');
            }
            
            $data = ['code' => $this->input->post('code'), 'name' => $this->input->post('name')];

            if (isset($_FILES['userfile']) && !empty($_FILES['userfile']['name'])) {
                $this->load->library('upload');

                $config['upload_path']   = 'uploads/';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size']      = '500';
                $config['max_width']     = '800';
                $config['max_height']    = '800';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('userfile')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect('categories/add');
                }

                $photo         = $this->upload->data('file_name');
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
                    $this->session->set_flashdata('error', $this->image_lib->display_errors());
                    redirect('categories/add');
                }
            }
        }

        if ($this->form_validation->run() == true && $this->categories_model->addCategory($data)) {
            $this->session->set_flashdata('message', lang('category_added'));
            redirect('categories');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('add_category');
            $bc                       = [['link' => site_url('categories'), 'page' => lang('categories')], ['link' => '#', 'page' => lang('add_category')]];
            $meta                     = ['page_title' => lang('add_category'), 'bc' => $bc];
            $this->page_construct('categories/add', $this->data, $meta);
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

        if ($this->categories_model->deleteCategory($id)) {
            $this->session->set_flashdata('message', lang('category_deleted'));
            redirect('categories');
        }
    }

    public function edit($id = null)
    {

        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('name', lang('category_name'), 'required');

        if ($this->form_validation->run() == true) {
            $data = ['code' => $this->input->post('code'), 'name' => $this->input->post('name')];

            if (isset($_FILES['userfile']) && !empty($_FILES['userfile']['name'])) {

                $this->load->library('upload');
            
                $config['upload_path']   = 'uploads/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size']      = '500';
                $config['max_width']     = '800';
                $config['max_height']    = '800';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
            
                $this->upload->initialize($config);
            
                if (!$this->upload->do_upload('userfile')) {
            
                    $error = $this->upload->display_errors();
            
                    $this->session->set_flashdata('error', $error);
            
                    redirect('categories/edit/'.$id);
                }
            
                $photo = $this->upload->data('file_name');
            
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
            
                    $this->session->set_flashdata('error', $this->image_lib->display_errors());
            
                    redirect('categories/edit/'.$id);
                }
            }
        }

        if ($this->form_validation->run() == true && $this->categories_model->updateCategory($id, $data)) {
            $this->session->set_flashdata('message', lang('category_updated'));
            redirect('categories');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['category']   = $this->site->getCategoryByID($id);
            $this->data['page_title'] = lang('edit_category');
            $bc                       = [['link' => site_url('categories'), 'page' => lang('categories')], ['link' => '#', 'page' => lang('edit_category')]];
            $meta                     = ['page_title' => lang('edit_category'), 'bc' => $bc];
            $this->page_construct('categories/edit', $this->data, $meta);
        }
    }

    public function get_categories()
    {
        $this->load->library('datatables');
        $this->datatables->select('id, image, code, name,');
        $this->datatables->from('categories');
        $this->datatables->add_column('Actions',
    "<div class='text-center'>
        <div class='btn-group'>
            <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
            </button>
            <ul class='dropdown-menu dropdown-menu-right'>
                <li>
                    <a href='javascript:void(0);' 
                       class='edit-category' 
                       data-id='$1' 
                       data-code=\"$3\" 
                       data-name=\"$4\" 
                       data-image=\"$2\">
                       <i class='fa fa-edit'></i> " . lang('edit') . "
                    </a>
                </li>
                <li>
                    <a href='" . site_url('categories/delete/$1') . "' class='tip text-danger' title='" . lang('delete') . "' onClick=\"return confirm('" . lang('alert_x_category') . "')\">
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

    public function import()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                redirect('pos');
            }

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');

                $config['upload_path']   = 'uploads/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = '500';
                $config['overwrite']     = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect('categories/import');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('uploads/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                array_shift($arrResult);

                $keys = ['code', 'name'];

                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                if (sizeof($final) > 1001) {
                    $this->session->set_flashdata('error', lang('more_than_allowed'));
                    redirect('categories/import');
                }

                foreach ($final as $csv_pr) {
                    if ($this->site->getCategoryByCode($csv_pr['code'])) {
                        $this->session->set_flashdata('error', lang('check_category') . ' (' . $csv_pr['code'] . '). ' . lang('category_already_exist'));
                        redirect('categories/import');
                    }
                    $data[] = ['code' => $csv_pr['code'], 'name' => $csv_pr['name']];
                }
            }
        }

        if ($this->form_validation->run() == true && $this->categories_model->add_categories($data)) {
            $this->session->set_flashdata('message', lang('categories_added'));
            redirect('categories');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('import_categories');
            $bc                       = [['link' => site_url('products'), 'page' => lang('products')], ['link' => site_url('categories'), 'page' => lang('categories')], ['link' => '#', 'page' => lang('import_categories')]];
            $meta                     = ['page_title' => lang('import_categories'), 'bc' => $bc];
            $this->page_construct('categories/import', $this->data, $meta);
        }
    }

    public function index()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['page_title'] = lang('categories');
        $bc                       = [['link' => '#', 'page' => lang('categories')]];
        $meta                     = ['page_title' => lang('categories'), 'bc' => $bc];
        $this->page_construct('categories/index', $this->data, $meta);
    }
}