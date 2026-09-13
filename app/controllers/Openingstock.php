<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Openingstock extends MY_Controller {

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('Openingstock_model');
    }

    public function index()
{
    if (!$this->loggedIn) {
        redirect('login');
    }

    $this->data['page_title'] = lang('opening_stock');
    $bc = [['link' => '#', 'page' => lang('opening_stock')]];
    $meta = ['page_title' => lang('opening_stock'), 'bc' => $bc];
    $this->page_construct('openingstock/index', $this->data, $meta);
}


public function add()
{
    $this->form_validation->set_rules('product_id[]', lang('product'), 'required');
    $this->form_validation->set_rules('store_id[]', lang('location'), 'required');
    $this->form_validation->set_rules('primary_qty[]', lang('quantity'), 'required|numeric');

    if ($this->form_validation->run() == true) {
        $product_ids     = $this->input->post('product_id');
        $store_ids       = $this->input->post('store_id');
        $container_ids   = $this->input->post('container_id');
        $batch_nos       = $this->input->post('batch_no');
        $expiry_dates    = $this->input->post('expiry_date');
        $primary_qtys    = $this->input->post('primary_qty');
        $primary_units   = $this->input->post('primary_unit_id');
        $qty_secondaries = $this->input->post('qty_secondary');
        $costs           = $this->input->post('cost_per_base');

        $now     = date('Y-m-d H:i:s');
        $success = true;

        // Product cost and dual-unit definitions.
        $product_costs = $this->db
            ->select(
                'id, COALESCE(cost, 0) AS cost, base_unit_id, ' .
                'secondary_unit_id, is_dual_unit',
                false
            )
            ->where_in('id', $product_ids)
            ->get('tec_products')
            ->result();

        $cost_map = [];
        foreach ($product_costs as $p) {
            $cost_map[$p->id] = [
                'cost'              => (float) $p->cost,
                'base_unit_id'      => (int) $p->base_unit_id,
                'secondary_unit_id' => (int) $p->secondary_unit_id,
                'is_dual_unit'      => (int) $p->is_dual_unit === 1,
            ];
        }

        // Use the latest batch cost when product cost is empty.
        $zero_cost_ids = [];
        foreach ($cost_map as $pid => $data) {
            if ($data['cost'] == 0) $zero_cost_ids[] = $pid;
        }

        if (!empty($zero_cost_ids)) {
            $batch_costs = $this->db
                ->select('product_id, cost_per_base')
                ->where_in('product_id', $zero_cost_ids)
                ->order_by('id', 'DESC')
                ->group_by('product_id')
                ->get('tec_stock_batches')
                ->result();

            foreach ($batch_costs as $b) {
                if (isset($cost_map[$b->product_id]) && $cost_map[$b->product_id]['cost'] == 0) {
                    $cost_map[$b->product_id]['cost'] = $b->cost_per_base;
                }
            }
        }

        $this->db->trans_begin();

        foreach ($product_ids as $i => $product_id) {
            $product_id = (int) $product_id;
            $store_id = isset($store_ids[$i]) ? (int) $store_ids[$i] : 0;
            $primary_qty = isset($primary_qtys[$i])
                ? (float) $primary_qtys[$i]
                : 0;
            $primary_unit_id = isset($primary_units[$i])
                ? (int) $primary_units[$i]
                : 0;

            if (
                $product_id <= 0 ||
                $store_id <= 0 ||
                $primary_qty <= 0 ||
                empty($cost_map[$product_id])
            ) {
                $success = false;
                break;
            }

            $product_info = $cost_map[$product_id];
            $base_unit_id = (int) $product_info['base_unit_id'];

            if ($primary_unit_id <= 0) {
                $primary_unit_id = $base_unit_id;
            }

            $unit_convert = 1;

            if ($primary_unit_id !== $base_unit_id) {
                $conversion = $this->db
                    ->select('operation_value, operator')
                    ->where('product_id', $product_id)
                    ->where('unit_id', $primary_unit_id)
                    ->get('tec_product_unit_conversions')
                    ->row();

                if (!$conversion) {
                    $success = false;
                    break;
                }

                $operation_value = is_numeric($conversion->operation_value)
                    ? (float) $conversion->operation_value
                    : 1;

                if ($operation_value <= 0) {
                    $operation_value = 1;
                }

                $unit_convert = trim((string) $conversion->operator) === '/'
                    ? (1 / $operation_value)
                    : $operation_value;
            }

            $base_qty = $primary_qty * $unit_convert;

            $secondary_qty =
                !empty($product_info['is_dual_unit']) &&
                isset($qty_secondaries[$i])
                    ? max(0, (float) $qty_secondaries[$i])
                    : 0;

            $secondary_unit_id =
                !empty($product_info['is_dual_unit'])
                    ? (int) $product_info['secondary_unit_id']
                    : null;

            if (
                !empty($product_info['is_dual_unit']) &&
                (!$secondary_unit_id || $secondary_unit_id === $base_unit_id)
            ) {
                $success = false;
                break;
            }

            if (
                !isset($costs[$i]) ||
                $costs[$i] === '' ||
                $costs[$i] === null
            ) {
                $cost = $product_info['cost'] ?? 0;
            } else {
                $cost = (float) $costs[$i];
            }

            $batch_data = [
                'product_id'       => $product_id,
                'store_id'         => $store_id,
                'container_id'     => isset($container_ids[$i]) && $container_ids[$i]
                    ? (int) $container_ids[$i]
                    : null,
                'batch_no'         => isset($batch_nos[$i]) && $batch_nos[$i] !== ''
                    ? $batch_nos[$i]
                    : 'Opening',
                'expiry_date'      => isset($expiry_dates[$i]) && $expiry_dates[$i] !== ''
                    ? $expiry_dates[$i]
                    : null,
                'qty_base'         => $base_qty,
                'qty_primary'      => $primary_qty,
                'qty_secondary'    => $secondary_qty,
                'cost_per_base'    => $cost,
                'primary_unit_id'  => $primary_unit_id,
                'secondary_unit_id'=> $secondary_unit_id,
                'unit_convert'     => $unit_convert,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            if (!$this->db->insert('tec_stock_batches', $batch_data)) {
                $success = false;
                break;
            }

            $batch_id = $this->db->insert_id();

            if ($batch_id) {
                $movement_data = [
                    'batch_id'         => $batch_id,
                    'product_id'       => $product_id,
                    'from_store_id'    => null,
                    'to_store_id'      => $store_id,
                    'from_container_id'=> null,
                    'to_container_id'  => isset($container_ids[$i]) && $container_ids[$i]
                        ? (int) $container_ids[$i]
                        : null,
                    'movement_type'    => 'opening',
                    'qty_base'         => $base_qty,
                    'qty_secondary'    => $secondary_qty,
                    'created_at'       => $now
                ];

                if (!$this->db->insert('tec_stock_movements', $movement_data)) {
                    $success = false;
                    break;
                }
            } else {
                $success = false;
                break;
            }
        }

        if ($success && $this->db->trans_status() !== false) {
            $this->db->trans_commit();
            $this->session->set_flashdata('message', lang('opening_stock_added'));
            redirect('openingstock');
        } else {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', lang('something_went_wrong'));
            redirect('openingstock/add');
        }

    } else {
        // Load form
        $this->data['error']       = validation_errors() ?: $this->session->flashdata('error');
        $this->data['stores']      = $this->site->getAllStores();

        $products = $this->db
            ->select(
                'p.id, p.code, p.name, p.base_unit_id, p.secondary_unit_id, ' .
                'p.is_dual_unit, bu.name AS base_unit_name, ' .
                'su.name AS secondary_unit_name',
                false
            )
            ->from('tec_products p')
            ->join('tec_product_units bu', 'bu.id = p.base_unit_id', 'left')
            ->join('tec_product_units su', 'su.id = p.secondary_unit_id', 'left')
            ->order_by('p.name', 'ASC')
            ->get()
            ->result();

        $product_unit_data = [];

        foreach ($products as $product) {
            $product_unit_data[(int) $product->id] = [
                'base_unit_id'        => (int) $product->base_unit_id,
                'base_unit_name'      => (string) $product->base_unit_name,
                'secondary_unit_id'   => (int) $product->secondary_unit_id,
                'secondary_unit_name' => (string) $product->secondary_unit_name,
                'is_dual_unit'        => (int) $product->is_dual_unit === 1,
                'units'               => [],
            ];

            if ((int) $product->base_unit_id > 0) {
                $product_unit_data[(int) $product->id]['units'][] = [
                    'id'         => (int) $product->base_unit_id,
                    'name'       => (string) $product->base_unit_name,
                    'multiplier' => 1,
                ];
            }
        }

        $conversion_rows = [];

        if (!empty($product_unit_data)) {
            $conversion_rows = $this->db
                ->select(
                    'c.product_id, c.unit_id, c.operator, c.operation_value, ' .
                    'u.name AS unit_name',
                    false
                )
                ->from('tec_product_unit_conversions c')
                ->join('tec_product_units u', 'u.id = c.unit_id', 'left')
                ->where_in('c.product_id', array_keys($product_unit_data))
                ->order_by('c.operation_value', 'DESC')
                ->get()
                ->result();
        }

        foreach ($conversion_rows as $row) {
            $pid = (int) $row->product_id;
            $uid = (int) $row->unit_id;

            if (
                !isset($product_unit_data[$pid]) ||
                $uid === (int) $product_unit_data[$pid]['base_unit_id']
            ) {
                continue;
            }

            $value = is_numeric($row->operation_value)
                ? (float) $row->operation_value
                : 1;

            $multiplier = trim((string) $row->operator) === '/' && $value > 0
                ? (1 / $value)
                : $value;

            $product_unit_data[$pid]['units'][] = [
                'id'         => $uid,
                'name'       => (string) $row->unit_name,
                'multiplier' => $multiplier > 0 ? $multiplier : 1,
            ];
        }

        $this->data['products'] = $products;
        $this->data['product_unit_data'] = $product_unit_data;
        $this->data['containers']  = $this->site->getAllContainerBoxes();
        $this->data['page_title']  = lang('add_opening_stock');
        $bc = [['link' => site_url('openingstock'), 'page' => lang('opening_stock')], ['link' => '#', 'page' => lang('add_opening_stock')]];
        $meta = ['page_title' => lang('add_opening_stock'), 'bc' => $bc];
        $this->page_construct('openingstock/add', $this->data, $meta);
    }
}





public function get_opening_stock()
{
    $this->load->library('datatables');

    $this->datatables->select("
        tec_stock_movements.id AS id,
        tec_products.code AS product_code,
        tec_products.name AS product_name,
        tec_stores.name AS store_name,
        tec_stock_movements.movement_type AS movement_type,
        tec_stock_movements.qty_base AS opening_qty_base,
        tec_stock_movements.qty_secondary AS opening_qty_secondary,
        tec_stock_movements.created_at AS created_at
    ");

    // Use stock_movements as the base table (alias sm)
    $this->datatables->from('tec_stock_movements');

    // Join batches, products and stores (left joins to avoid dropping movements without batch/product/store)
    $this->datatables->join('tec_products',      'tec_products.id  = tec_stock_movements.product_id', 'left');
    $this->datatables->join('tec_stores',        'tec_stores.id  = tec_stock_movements.to_store_id', 'left');

    // only opening movements
    $this->datatables->where('tec_stock_movements.movement_type', 'opening');

    // actions column: $1 will be replaced with sm.id
    $this->datatables->add_column(
        'Actions',
        "<div class='text-center'>
            <div class='btn-group'>
                <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                    <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
                </button>
                <ul class='dropdown-menu dropdown-menu-right'>
                    <li>
                        <a class='tip' title='" . $this->lang->line('edit') . "' href='" . site_url('openingstock/edit/$1') . "'>
                            <i class='fa fa-edit'></i> " . $this->lang->line('edit') . "
                        </a>
                    </li>
                    <li>
                        <a href='" . site_url('openingstock/delete/$1') . "' 
                           class='tip text-danger' 
                           title='" . lang('delete') . "' 
                           onClick=\"return confirm('" . lang('alert_x_opening') . "')\">
                            <i class='fa fa-trash'></i> " . lang('delete') . "
                        </a>
                    </li>
                </ul>
            </div>
        </div>",
        'id'
    );

    // don't show the id column in the table
    $this->datatables->unset_column('id');

    echo $this->datatables->generate();
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

    // form validation rules
    $this->form_validation->set_rules('product_id', lang('product'), 'required');
    $this->form_validation->set_rules('store_id', lang('location'), 'required');
    $this->form_validation->set_rules('qty_base', lang('quantity_base'), 'required|numeric');
    $this->form_validation->set_rules('cost_per_base', lang('cost_per_base'), 'required|numeric');

    if ($this->form_validation->run() == true) {
        $data = [
            'product_id'     => $this->input->post('product_id'),
            'store_id'   => $this->input->post('store_id'),
            'batch_no'       => $this->input->post('batch_no'),
            'qty_base'       => $this->input->post('qty_base'),
            'qty_secondary'  => $this->input->post('qty_secondary'),
            'cost_per_base'  => $this->input->post('cost_per_base'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];
    }

    if ($this->form_validation->run() == true && $this->Openingstock_model->updateOpeningStock($id, $data)) {
        $this->session->set_flashdata('message', lang('opening_stock_updated'));
        redirect('openingstock');
    } else {
        // load data for view
        $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['stock'] = $this->Openingstock_model->getOpeningStockByID($id);
        
        $this->data['products']      = $this->site->getAllProducts();
        $this->data['stores']  = $this->site->getAllStores();
        $this->data['page_title']    = lang('edit_opening_stock');
        $bc                          = [['link' => site_url('openingstock'), 'page' => lang('opening_stock')], ['link' => '#', 'page' => lang('edit_opening_stock')]];
        $meta                        = ['page_title' => lang('edit_opening_stock'), 'bc' => $bc];
        $this->page_construct('openingstock/edit', $this->data, $meta);
    }
}


public function view($id = null)
{
    if (!$id) {
        $this->session->set_flashdata('error', lang('invalid_transfer_id'));
        redirect('openingstock');
    }

    $this->data['transfer'] = $this->Stock_model->getTransferByID($id);
    if (!$this->data['transfer']) {
        $this->session->set_flashdata('error', lang('transfer_not_found'));
        redirect('openingstock');
    }

    $this->data['items'] = $this->Stock_model->getTransferItems($id);

    $this->data['page_title'] = lang('view_transfer');
    $bc = [
        ['link' => site_url('openingstock'), 'page' => lang('stock_transfers')],
        ['link' => '#', 'page' => lang('view_transfer')]
    ];
    $meta = ['page_title' => lang('view_transfer'), 'bc' => $bc];
    $this->page_construct('openingstock/view', $this->data, $meta);
}

public function save()
{
    $this->load->model('Stock_model');
    $current_user_id = $this->session->userdata('user_id');

    $entry_date = $this->input->post('entry_date');
    $location_type = $this->input->post('location_type');
    $location_id = $this->input->post('location_id');

    $product_ids = $this->input->post('product_id');
    $first_unit_qtys = $this->input->post('first_unit_qty');
    $first_unit_names = $this->input->post('first_unit_name');
    $second_unit_qtys = $this->input->post('second_unit_qty');
    $second_unit_names = $this->input->post('second_unit_name');

    $this->db->trans_start();

    foreach ($product_ids as $key => $product_id) {
        $first_qty = floatval($first_unit_qtys[$key]);
        $first_name = $first_unit_names[$key];
        $second_qty = floatval($second_unit_qtys[$key]);
        $second_name = $second_unit_names[$key];

        // Update stock (implement increase_stock logic in your model)
        $this->Stock_model->increase_stock([
            'product_id' => $product_id,
            'location_type' => $location_type,
            'location_id' => $location_id,
            'first_unit_qty' => $first_qty,
            'first_unit_name' => $first_name,
            'second_unit_qty' => $second_qty,
            'second_unit_name' => $second_name,
        ]);

        // Insert stock movement
        $this->db->insert('stock_movements', [
            'product_id' => $product_id,
            'from_location_type' => null,
            'from_location_id' => null,
            'to_location_type' => $location_type,
            'to_location_id' => $location_id,
            'qty_first_unit' => $first_qty,
            'qty_second_unit' => $second_qty,
            'movement_type' => 'opening',
            'reference_id' => null,
            'created_at' => $entry_date,
            'created_by' => $current_user_id,
        ]);
    }

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
        $this->session->set_flashdata('error', 'Failed to save opening stock.');
    } else {
        $this->session->set_flashdata('success', 'Opening stock saved successfully.');
    }

    redirect('openingstock');
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

    if ($id) {
        // Load model if not already loaded
        $this->load->model('openingstock_model');

        // First, delete related stock movements of type 'opening'
        $this->db->where('id', $id);
        $movement = $this->db->get('tec_stock_movements')->row();

        if ($movement) {
            $this->db->where('id', $movement->id);
            $this->db->delete('tec_stock_movements');

            // Then delete the batch itself
            $this->db->where('id', $movement->batch_id);
            $this->db->delete('tec_stock_batches');

            $this->session->set_flashdata('message', lang('opening_stock_deleted'));
        } else {
            $this->session->set_flashdata('error', lang('opening_stock_not_found'));
        }
    }

    redirect('openingstock'); // redirect back to the opening stock index
}


}
