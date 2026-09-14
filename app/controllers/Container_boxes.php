<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Container_boxes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('container_boxes_model');
    }

    public function add()
    {
        $this->form_validation->set_rules('box_name', lang('box_name'), 'trim|is_unique[container_boxes.box_name]|required');
        
        if ($this->form_validation->run() == true) {
            $data = [
                'box_name' => $this->input->post('box_name'),
                'created_at'   => date('Y-m-d H:i:s'),
            ];
        } elseif ($this->input->post('add_container_box')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('container_boxes/add');
        }

        if ($this->form_validation->run() == true && $this->container_boxes_model->addContainerBox($data)) {
            $this->session->set_flashdata('message', lang('container_box_added'));
            redirect($this->mobilePageUrl('container_boxes'));
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('new_container_box');
            $bc                       = [['link' => site_url('container_boxes'), 'page' => lang('container_boxes')], ['link' => '#', 'page' => lang('new_container_box')]];
            $meta                     = ['page_title' => lang('new_container_box'), 'bc' => $bc];
            $this->page_construct('container_boxes/add', $this->data, $meta);
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

        if ($this->container_boxes_model->deleteContainerBox($id)) {
            $this->session->set_flashdata('success_message', lang('category_deleted'));
            redirect('container_boxes', 'refresh');
        }
    }

    public function edit($id = null)
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', $this->lang->line('access_denied'));
            redirect('pos');
        }
        $this->form_validation->set_rules('box_name', lang('box_name'), 'trim|required');
        $container_box = $this->container_boxes_model->getContainerBoxByID($id);
        if ($this->input->post('box_name') != $container_box->box_name) {
            $this->form_validation->set_rules('box_name', lang('box_name'), 'is_unique[container_boxes.box_name]');
        }
        

        if ($this->form_validation->run() == true) {
            $data = [
                'box_name' => $this->input->post('box_name'),
                'created_at' => date('Y-m-d H:i:s'),
            ];
        } elseif ($this->input->post('edit_container_box')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('container_boxes/edit');
        }

        if ($this->form_validation->run() == true && $this->container_boxes_model->updateContainerBox($id, $data)) {
            $this->session->set_flashdata('message', lang('container_box_updated'));
            redirect($this->mobilePageUrl('container_boxes'));
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['container_box']  = $container_box;
            $this->data['customers']  = $this->site->getAllCustomers();
            $this->data['page_title'] = lang('edit_container_box');
            $bc                       = [['link' => site_url('container_boxes'), 'page' => lang('container_boxes')], ['link' => '#', 'page' => lang('edit_container_box')]];
            $meta                     = ['page_title' => lang('edit_container_box'), 'bc' => $bc];
            $this->page_construct('container_boxes/edit', $this->data, $meta);
        }
    }

    public function get_container_boxes()
    {
        $this->load->library('datatables');
        if ($this->db->dbdriver == 'sqlite3') {
            $this->datatables->select($this->db->dbprefix('container_boxes') . '.id as id, box_name, created_at', false);
        } else {
            $this->datatables->select($this->db->dbprefix('container_boxes') . '.id as id, box_name, created_at', false);
        }
        $this->datatables->from('container_boxes');
        $this->datatables->add_column(
            'Actions',
            "<div class='text-center'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-right'>
                        
                        
                        <li>
                            <a href='" . site_url('container_boxes/delete/$1') . "' class='tip text-danger' title='" . lang('delete') . "' onClick=\"return confirm('" . lang('alert_x_container_box') . "')\">
                                <i class='fa-solid fa-trash'></i> " . lang('delete') . "
                            </a>
                        </li>
                    </ul>
                </div>
    </div>",
'id, box_name');
    $this->datatables->unset_column('id');


        echo $this->datatables->generate();
    }

    public function index()
    {
        $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('container_boxes');
        $bc                       = [['link' => '#', 'page' => lang('container_boxes')]];
        $meta                     = ['page_title' => lang('container_boxes'), 'bc' => $bc];
        $this->page_construct('container_boxes/index', $this->data, $meta);
    }

    public function sell_container_box()
    {
        $error  = null;
        $gcData = $this->input->get('gcdata');
        if (empty($gcData[0])) {
            $error = lang('value') . ' ' . lang('is_required');
        }
        if (empty($gcData[1])) {
            $error = lang('box_name') . ' ' . lang('is_required');
        }

        $data = ['box_name' => $gcData[0],
            'value'        => $gcData[1],
            'balance'      => $gcData[1],
            'expiry'       => $gcData[2] ? $gcData[2] : null,
            'created_by'   => $this->session->userdata('user_id'),
        ];

        if (!$error) {
            if ($this->container_boxes_model->addContainerBox($data)) {
                echo json_encode(['result' => 'success', 'message' => lang('container_box_added')]);
            }
        } else {
            echo json_encode(['result' => 'failed', 'message' => $error]);
        }
    }

    public function validate($no)
    {
        if ($gc = $this->site->getContainerBoxByNO($no)) {
            if ($gc->expiry) {
                if ($gc->expiry >= date('Y-m-d')) {
                    echo json_encode($gc);
                } else {
                    echo json_encode(false);
                }
            } else {
                echo json_encode($gc);
            }
        } else {
            echo json_encode(false);
        }
    }

    public function view($id = null)
    {
        $this->data['page_title'] = lang('container_box');
        $container_box                = $this->site->getContainerBoxByID($id);
        $this->data['container_box']  = $container_box;
        $this->data['customer']   = $this->site->getCustomerByID($container_box->customer_id);
        // $this->data['topups'] = $this->sales_model->getAllGCTopups($id);
        $this->load->view($this->theme . 'container_boxes/view', $this->data);
    }

    public function generate_box_name()
    {
        $nextBoxName = $this->container_boxes_model->generateNextBoxName();
        echo $nextBoxName;
    }
}
