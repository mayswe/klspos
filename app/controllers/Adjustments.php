<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Adjustments extends MY_Controller {

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('adjustment_model');
    }

    public function index()
    {

        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['adjustments'] = $this->adjustment_model->getAdjustments();
        $this->data['page_title'] = lang('categories');
        $bc                       = [['link' => '#', 'page' => lang('categories')]];
        $meta                     = ['page_title' => lang('categories'), 'bc' => $bc];
        $this->page_construct('products/adjustments', $this->data, $meta);
    }

    public function add()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->form_validation->set_rules('product_id', lang('product'), 'required');
        $this->form_validation->set_rules('warehouse_id', lang('warehouse'), 'required');
        $this->form_validation->set_rules('quantity', lang('quantity'), 'required|numeric');
        $this->form_validation->set_rules('adjustment_type', lang('adjustment_type'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'product_id'      => $this->input->post('product_id'),
                'warehouse_id'    => $this->input->post('warehouse_id'),
                'quantity'        => $this->input->post('quantity'),
                'adjustment_type' => $this->input->post('adjustment_type'),
                'note'            => $this->input->post('note'),
                'date'            => date('Y-m-d H:i:s'),
                'created_by'      => $this->session->userdata('user_id')
            ];

            if ($this->adjustment_model->addAdjustment($data)) {
                $this->adjust_stock_fifo($data['product_id'], $data['warehouse_id'], $data['quantity']);

                $this->session->set_flashdata('message', lang('product_adjustment_added'));
                redirect('products/');
            } else {
                $this->session->set_flashdata('error', lang('adjustment_save_failed'));
                redirect('products/adjustments_add');
            }
        } else {
            // load data for form
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['stores']     = $this->db->get('tec_stores')->result();
            $this->data['products']   = $this->db->get('tec_products')->result();
            $this->data['page_title'] = lang('add_product_adjustment');

            $bc   = [['link' => site_url('adjustments'), 'page' => lang('product_adjustments')],
                    ['link' => '#', 'page' => lang('add_product_adjustment')]];
            $meta = ['page_title' => lang('add_product_adjustment'), 'bc' => $bc];

            $this->page_construct('products/adjustments_add', $this->data, $meta);
        }
    }


    public function get_warehouses_by_store($store_id)
    {
        $warehouses = $this->db->where('store_id', $store_id)->get('tec_warehouses')->result();
        echo json_encode($warehouses);
    }

    private function adjust_stock_fifo($product_id, $warehouse_id, $quantity)
    {
        $batches = $this->db->order_by('date', 'asc')
                            ->where('product_id', $product_id)
                            ->where('warehouse_id', $warehouse_id)
                            ->where('quantity_balance >', 0)
                            ->get('tec_purchase_items')
                            ->result();

        foreach ($batches as $batch) {
            if ($quantity <= 0) break;

            $deduct_qty = min($quantity, $batch->quantity_balance);
            $new_balance = $batch->quantity_balance - $deduct_qty;

            $this->db->where('id', $batch->id)->update('tec_purchase_items', [
                'quantity_balance' => $new_balance
            ]);

            $quantity -= $deduct_qty;
        }
    }
}
