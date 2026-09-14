<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shippings extends MY_Controller
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
        $this->load->model('shippings_model');
        $this->allowed_types = 'gif|jpg|png|pdf|doc|docx|xls|xlsx|zip';
    }

    public function add()
    {
        if (!$this->session->userdata('store_id')) {
            $this->session->set_flashdata('warning', lang('please_select_store'));
            redirect('stores');
        }
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->form_validation->set_rules('date', lang('date'), 'required');

        if ($this->form_validation->run() == true) {
            $total      = 0;
            $quantity   = 'quantity';
            $product_id = 'product_id';
            $unit_cost  = 'cost';
            $i          = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id   = $_POST['product_id'][$r];
                $item_qty  = $_POST['quantity'][$r];
                $item_cost = $_POST['cost'][$r];
                if ($item_id && $item_qty && $unit_cost) {
                    if (!$this->shippings_model->getProductByID($item_id)) {
                        $this->session->set_flashdata('error', $this->lang->line('product_not_found') . ' ( ' . $item_id . ' ).');
                        redirect('shippings/add');
                    }

                    $products[] = [
                        'product_id' => $item_id,
                        'cost'       => $item_cost,
                        'quantity'   => $item_qty,
                        'subtotal'   => ($item_cost * $item_qty),
                    ];

                    $total += ($item_cost * $item_qty);
                }
            }

            if (!isset($products) || empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $data = [
                'date'        => $this->input->post('date'),
                'reference'   => $this->input->post('reference'),
                'supplier_id' => $this->input->post('supplier'),
                'note'        => $this->input->post('note', true),
                'received'    => $this->input->post('received'),
                'total'       => $total,
                'created_by'  => $this->session->userdata('user_id'),
                'store_id'    => $this->session->userdata('store_id'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = 'uploads/';
                $config['allowed_types'] = $this->allowed_types;
                $config['max_size']      = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->upload->set_flashdata('error', $error);
                    redirect('shippings/add');
                }

                $data['attachment'] = $this->upload->file_name;
            }
            // $this->tec->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->shippings_model->addShipping($data, $products)) {
            $this->session->set_userdata('remove_spo', 1);
            $this->session->set_flashdata('message', lang('shipping_added'));
            redirect($this->mobilePageUrl('shippings'));
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['suppliers']  = $this->site->getAllSuppliers();
            $this->data['page_title'] = lang('add_shipping');
            $bc                       = [['link' => site_url('shippings'), 'page' => lang('shippings')], ['link' => '#', 'page' => lang('add_shipping')]];
            $meta                     = ['page_title' => lang('add_shipping'), 'bc' => $bc];
            $this->page_construct('shippings/add', $this->data, $meta);
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

        if ($this->shippings_model->deleteShipping($id)) {
            $this->session->set_flashdata('message', lang('shipping_deleted'));
            redirect($this->mobilePageUrl('shippings'));
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

        $this->form_validation->set_rules('date', lang('date'), 'required');

        if ($this->form_validation->run() == true) {
            $total      = 0;
            $quantity   = 'quantity';
            $product_id = 'product_id';
            $unit_cost  = 'cost';
            $i          = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id   = $_POST['product_id'][$r];
                $item_qty  = $_POST['quantity'][$r];
                $item_cost = $_POST['cost'][$r];
                if ($item_id && $item_qty && $unit_cost) {
                    if (!$this->site->getProductByID($item_id)) {
                        $this->session->set_flashdata('error', $this->lang->line('product_not_found') . ' ( ' . $item_id . ' ).');
                        redirect('shippings/edit/' . $id);
                    }

                    $products[] = [
                        'product_id' => $item_id,
                        'cost'       => $item_cost,
                        'quantity'   => $item_qty,
                        'subtotal'   => ($item_cost * $item_qty),
                    ];

                    $total += ($item_cost * $item_qty);
                }
            }

            if (!isset($products) || empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $data = [
                'date'        => $this->input->post('date'),
                'reference'   => $this->input->post('reference'),
                'note'        => $this->input->post('note', true),
                'supplier_id' => $this->input->post('supplier'),
                'received'    => $this->input->post('received'),
                'total'       => $total,
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = 'uploads/';
                $config['allowed_types'] = $this->allowed_types;
                $config['max_size']      = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->upload->set_flashdata('error', $error);
                    redirect('shippings/add');
                }

                $data['attachment'] = $this->upload->file_name;
            }
            // $this->tec->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->shippings_model->updateShipping($id, $data, $products)) {
            $this->session->set_userdata('remove_spo', 1);
            $this->session->set_flashdata('message', lang('shipping_updated'));
            redirect($this->mobilePageUrl('shippings'));
        } else {
            $this->data['shipping'] = $this->shippings_model->getShippingByID($id);
            $inv_items              = $this->shippings_model->getAllShippingItems($id);
            $c                      = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row       = $this->site->getProductByID($item->product_id);
                $row->qty  = $item->quantity;
                $row->cost = $item->cost;
                $ri        = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri]   = ['id' => $ri, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row];
                $c++;
            }

            $this->data['items']      = json_encode($pr);
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['suppliers']  = $this->site->getAllSuppliers();
            $this->data['page_title'] = lang('edit_shipping');
            $bc                       = [['link' => site_url('shippings'), 'page' => lang('shippings')], ['link' => '#', 'page' => lang('edit_shipping')]];
            $meta                     = ['page_title' => lang('edit_shipping'), 'bc' => $bc];
            $this->page_construct('shippings/edit', $this->data, $meta);
        }
    }

    public function get_shippings()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->load->library('datatables');
        $this->datatables->select('id, date, reference, total, note, attachment');
        $this->datatables->from('shippings');
        if (!$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->where('store_id', $this->session->userdata('store_id'));
        $this->datatables->add_column('Actions', "<div class='text-center'><div class='btn-group'><a href='" . site_url('shippings/view/$1') . "' title='" . lang('view_shipping') . "' class='tip btn btn-primary btn-xs' data-toggle='ajax-modal'><i class='fa-solid fa-eye'></i></a> <a href='" . site_url('shippings/edit/$1') . "' title='" . lang('edit_shipping') . "' class='tip btn btn-warning btn-xs'><i class='fa fa-edit'></i></a> <a href='" . site_url('shippings/delete/$1') . "' onClick=\"return confirm('" . lang('alert_x_shipping') . "')\" title='" . lang('delete_shipping') . "' class='tip btn btn-danger btn-xs'><i class='fa-solid fa-trash'></i></a></div></div>", 'id');

        $this->datatables->unset_column('id');
        echo $this->datatables->generate();
    }

    public function index()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['page_title'] = lang('shippings');
        $bc                       = [['link' => '#', 'page' => lang('shippings')]];
        $meta                     = ['page_title' => lang('shippings'), 'bc' => $bc];
        $this->page_construct('shippings/index', $this->data, $meta);
    }

    public function suggestions($id = null)
    {
        if ($id) {
            $row      = $this->site->getProductByID($id);
            $row->qty = 1;
            $pr       = ['id' => str_replace('.', '', microtime(true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row];
            echo json_encode($pr);
            die();
        }
        $term = $this->tec->parse_scale_barcode($this->input->get('term', true));
        if (is_array($term)) {
            $bqty   = $term['weight'] ?? null;
            $bprice = $term['price']  ?? null;
            $term   = $term['item_code'];
            $rows   = $this->shippings_model->getProductNames($term, null, true);
        }
        if (!$rows) {
            $bqty   = null;
            $bprice = null;
            $term   = $this->input->get('term', true);
            $rows   = $this->shippings_model->getProductNames($term);
        }
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = $bqty ?: ($bprice ? $bprice / $row->price : 1);
                $pr[]     = ['id' => str_replace('.', '', microtime(true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row];
            }
            echo json_encode($pr);
        } else {
            echo json_encode([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function view($id = null)
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->data['shipping']   = $this->shippings_model->getShippingByID($id);
        $this->data['items']      = $this->shippings_model->getAllShippingItems($id);
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['page_title'] = lang('view_shipping');
        $this->load->view($this->theme . 'shippings/view', $this->data);
    }

}
