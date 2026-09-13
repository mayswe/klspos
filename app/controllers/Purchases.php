<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchases extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }
        
        $this->load->library('form_validation');
        $this->load->model('purchases_model');
        $this->load->model('reports_model');
        $this->allowed_types = 'gif|jpg|png|pdf|doc|docx|xls|xlsx|zip';
    }

    
    public function add()
{
    $this->form_validation->set_rules(
        'date',
        lang('date'),
        'required'
    );

    $this->form_validation->set_rules(
        'store',
        'Location',
        'required'
    );

    if ($this->form_validation->run() == true) {

        $total = 0;
        $delivery_total = 0;
        $products = [];

        $date = $this->input->post('date', true);

        $product_ids = $this->input->post('product_id');

        $i = is_array($product_ids)
            ? count($product_ids)
            : 0;

        // =====================================================
        // PRODUCT LOOP
        // =====================================================
        for ($r = 0; $r < $i; $r++) {

            $item_id = isset($_POST['product_id'][$r])
                ? (int) $_POST['product_id'][$r]
                : 0;

            $unit_cost = isset($_POST['cost'][$r])
                && is_numeric($_POST['cost'][$r])
                ? (float) $_POST['cost'][$r]
                : 0;

            $primary_qty = isset($_POST['primary_qty'][$r])
                && is_numeric($_POST['primary_qty'][$r])
                ? (float) $_POST['primary_qty'][$r]
                : 0;

            $primary_unit = isset($_POST['primary_unit'][$r])
                ? (int) $_POST['primary_unit'][$r]
                : 0;

            $secondary_qty = isset($_POST['secondary_qty'][$r])
                && $_POST['secondary_qty'][$r] !== ''
                && is_numeric($_POST['secondary_qty'][$r])
                ? (float) $_POST['secondary_qty'][$r]
                : 0;

            $secondary_unit = isset($_POST['secondary_unit'][$r])
                ? (int) $_POST['secondary_unit'][$r]
                : 0;

            $transportation = isset($_POST['transportation'][$r])
                && $_POST['transportation'][$r] !== ''
                && is_numeric($_POST['transportation'][$r])
                ? (float) $_POST['transportation'][$r]
                : 0;


            // =================================================
            // BASIC VALIDATION
            // =================================================
            if ($item_id <= 0) {
                continue;
            }

            if ($primary_qty <= 0) {
                $this->session->set_flashdata(
                    'error',
                    'Purchase quantity must be greater than zero.'
                );

                redirect('purchases/add');
            }


            // =================================================
            // PRODUCT
            // =================================================
            $product = $this->purchases_model
                ->getProductByID($item_id);

            if (!$product) {

                $this->session->set_flashdata(
                    'error',
                    $this->lang->line('product_not_found')
                    . ' (' . $item_id . ')'
                );

                redirect('purchases/add');
            }

            $is_dual_unit =
                !empty($product->is_dual_unit);

            if (!$is_dual_unit) {
                $secondary_qty = 0;
                $secondary_unit = 0;
            } elseif (!empty($product->secondary_unit_id)) {
                // Independent count unit (လုံး/ခွေ) comes from Product Setup.
                $secondary_unit = (int) $product->secondary_unit_id;
            }


            // =================================================
            // PRIMARY UNIT CONVERSION
            // =================================================
            $unit_primary = $this->site
                ->getUnitOperatorByID(
                    $primary_unit,
                    $item_id
                );

            $converted_primary = $primary_qty;

            if (
                $unit_primary &&
                isset($unit_primary->operation_value) &&
                is_numeric($unit_primary->operation_value)
            ) {

                $operation_value =
                    (float) $unit_primary->operation_value;

                $operator = isset($unit_primary->operator)
                    ? trim($unit_primary->operator)
                    : '*';

                if ($operation_value > 0) {

                    if ($operator === '/') {

                        $converted_primary =
                            $primary_qty / $operation_value;

                    } else {

                        $converted_primary =
                            $primary_qty * $operation_value;
                    }
                }
            }


            // =================================================
            // SECONDARY UNIT
            // =================================================
            $converted_secondary = 0;

            if ($secondary_unit > 0) {

                $unit_secondary = $this->site
                    ->getUnitOperatorByID(
                        $secondary_unit,
                        $item_id
                    );

                // Converted secondary
                $converted_secondary =
                    $secondary_qty;

                if (
                    $unit_secondary &&
                    isset(
                        $unit_secondary->operation_value
                    ) &&
                    is_numeric(
                        $unit_secondary->operation_value
                    )
                ) {

                    $sec_value =
                        (float)
                        $unit_secondary->operation_value;

                    $sec_operator =
                        isset($unit_secondary->operator)
                        ? trim(
                            $unit_secondary->operator
                        )
                        : '*';

                    if ($sec_value > 0) {

                        if ($sec_operator === '/') {

                            $converted_secondary =
                                $secondary_qty
                                / $sec_value;

                        } else {

                            $converted_secondary =
                                $secondary_qty
                                * $sec_value;
                        }
                    }
                }
            }


            // =================================================
            // DELIVERY / TRANSPORTATION
            // =================================================
            /*
             * transportation field is assumed to be
             * total delivery cost for this item.
             *
             * Do NOT divide it by quantity here.
             */
            $delivery_cost = $transportation;


            // =================================================
            // SUBTOTAL
            // =================================================
            /*
             * Example:
             *
             * Qty      = 2 boxes
             * Cost     = 1000 / box
             * Subtotal = 2000
             */
            $subtotal =
                $primary_qty * $unit_cost;


            // =================================================
            // DEBUG
            // =================================================
            log_message(
                'debug',
                '-----------------------------------------'
            );

            log_message(
                'debug',
                "Product {$item_id}"
                . " | Primary Qty: {$primary_qty}"
                . " | Primary Unit: {$primary_unit}"
                . " | Converted Primary: {$converted_primary}"
            );

            log_message(
                'debug',
                "Product {$item_id}"
                . " | Secondary Qty: {$secondary_qty}"
                . " | Secondary Unit: {$secondary_unit}"
                . " | Converted Secondary: {$converted_secondary}"
            );

            log_message(
                'debug',
                "Product {$item_id}"
                . " | Unit Cost: {$unit_cost}"
                . " | Subtotal: {$subtotal}"
                . " | Delivery: {$delivery_cost}"
            );


            // =================================================
            // PREPARE PURCHASE ITEM
            // =================================================
            $product_data = [

                'product_id' => $item_id,

                'primary_qty' => $primary_qty,

                'primary_unit' => $primary_unit,

                'net_unit_cost' => $unit_cost,

                'subtotal' => $subtotal,

                'delivery' => $delivery_cost
            ];


            if ($secondary_unit > 0) {

                $product_data['secondary_qty'] =
                    $secondary_qty;

                $product_data['secondary_unit'] =
                    $secondary_unit;
            }


            $products[] = $product_data;

            $total += $subtotal;
            $delivery_total += $delivery_cost;
        }


        // =====================================================
        // NO PRODUCTS
        // =====================================================
        if (empty($products)) {

            $this->session->set_flashdata(
                'error',
                lang('order_items')
            );

            redirect('purchases/add');
        }
        
        if ($delivery_total <= 0) {
             $delivery_total = isset($_POST['delivery'])
                && $_POST['delivery'] !== ''
                && is_numeric($_POST['delivery'])
                ? (float) $_POST['delivery']
                : 0;
        }

        // =====================================================
        // GRAND TOTAL = ITEM TOTAL + DELIVERY
        // =====================================================
        $items_total = (float) $total;
        $grand_total = $items_total + $delivery_total;

        // =====================================================
        // PAID NOW
        // =====================================================
        $paid_input = $this->input->post('paid');
        $paid = is_numeric($paid_input)
            ? (float) $paid_input
            : 0;

        if ($paid < 0) {
            $paid = 0;
        }

        if ($paid > $grand_total) {
            $paid = $grand_total;
        }

        // =====================================================
        // PAYMENT STATUS
        // =====================================================
        if ($paid <= 0) {
            $status = 'due';
        } elseif ($paid >= $grand_total) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        // =====================================================
        // PURCHASE DATA
        // =====================================================
        $data = [
            'date'              => $date,
            'reference'         => $this->input->post('reference', true),
            'supplier_id'       => (int) $this->input->post('supplier'),
            'note'              => $this->input->post('note', true),
            'received'          => (int) $this->input->post('received'),
            
            'total'             => $grand_total,
            
            'created_by'        => $this->session->userdata('user_id'),
            'store_id'          => (int) $this->input->post('store'),
        
            'advance_deducted'  => 0,

            'delivery'          => $delivery_total,
            'opening'           => 0,

            'paid'              => $paid,
            'status'            => $status,
        ];


        // =====================================================
        // ATTACHMENT
        // =====================================================
        if (
            isset($_FILES['userfile']) &&
            isset($_FILES['userfile']['size']) &&
            $_FILES['userfile']['size'] > 0
        ) {

            $this->load->library('upload');

            $config = [];

            $config['upload_path'] =
                'files/';

            $config['allowed_types'] =
                $this->allowed_types;

            $config['max_size'] =
                '2000';

            $config['overwrite'] =
                false;

            $config['encrypt_name'] =
                true;

            $this->upload
                ->initialize($config);


            if (
                !$this->upload
                    ->do_upload('userfile')
            ) {

                $error =
                    $this->upload
                        ->display_errors();

                $this->session
                    ->set_flashdata(
                        'error',
                        $error
                    );

                redirect('purchases/add');
            }


            $upload_data =
                $this->upload->data();

            $data['attachment'] =
                $upload_data['file_name'];
        }


        // =====================================================
        // FINAL DEBUG
        // =====================================================
        log_message(
            'debug',
            '========================================='
        );

        log_message(
            'debug',
            'Purchase Data: '
            . print_r($data, true)
        );

        log_message(
            'debug',
            'Products Data: '
            . print_r($products, true)
        );


        // =====================================================
        // SAVE
        // =====================================================
        $purchase_id =
            $this->purchases_model
                ->addPurchase(
                    $data,
                    $products,
                    !empty($data['attachment'])
                        ? $data['attachment']
                        : null
                );


        // =====================================================
        // SAVE FAILED
        // =====================================================
        if (!$purchase_id) {

            log_message(
                'error',
                'Purchase save failed. '
                . 'addPurchase() returned FALSE.'
            );

            $this->session
                ->set_flashdata(
                    'error',
                    'Purchase could not be saved. '
                    . 'Please check system log.'
                );

            redirect('purchases/add');
        }


        // =====================================================
        // SUCCESS
        // =====================================================
        $this->session
            ->set_userdata(
                'remove_spo',
                1
            );

        $this->session
            ->set_flashdata(
                'message',
                lang('purchase_added')
            );

        redirect('purchases');
    }


    // =========================================================
    // LOAD VIEW
    // =========================================================
    $this->data['error'] =
        validation_errors()
        ? validation_errors()
        : $this->session
            ->flashdata('error');


    $this->data['suppliers'] =
        $this->site
            ->getAllSuppliers();


    $this->data['product_units'] =
        $this->site
            ->getProductUnits();


    $this->data['stores'] =
        $this->site
            ->getAllStores();


    $this->data['page_title'] =
        lang('add_purchase');


    $bc = [

        [
            'link' =>
                site_url('purchases'),

            'page' =>
                lang('purchases')
        ],

        [
            'link' => '#',

            'page' =>
                lang('add_purchase')
        ]
    ];


    $meta = [

        'page_title' =>
            lang('add_purchase'),

        'bc' =>
            $bc
    ];


    $this->page_construct(
        'purchases/add',
        $this->data,
        $meta
    );
}



    public function edit($id = null)
{
    if ($this->input->get('id')) {
        $id = $this->input->get('id');
    }

    $this->form_validation->set_rules('date', lang('date'), 'required');

    if ($this->form_validation->run() == true) {

        $exchange_rate = $this->input->post('exchange_rate');
        $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
        $total = 0;
        $products = [];

        // 1️⃣ Remove old purchase items + batches
        $this->db->delete('product_batches', ['purchase_id' => $id]);
        $this->db->delete('purchase_items', ['purchase_id' => $id]);

        // 2️⃣ Insert new purchase items and batches
        for ($r = 0; $r < $i; $r++) {
            $item_id         = $_POST['product_id'][$r];
            $primary_qty     = $_POST['primary_qty'][$r];
            $primary_unit_id = $_POST['primary_unit'][$r];
            $secondary_qty   = $_POST['secondary_qty'][$r];
            $secondary_unit_id = $_POST['secondary_unit'][$r];
            $item_cost       = $_POST['cost'][$r];
            $tran_cost       = $_POST['transportation'][$r];

            if ($item_id && $primary_qty && $item_cost) {
                $product = $this->purchases_model->getProductByID($item_id);
                if (!$product) {
                    $this->session->set_flashdata('error', $this->lang->line('product_not_found') . ' ( ' . $item_id . ' ).');
                    redirect('purchases/edit/' . $id);
                }

                // ✅ Convert primary qty to base qty
                $primary_unit   = $this->site->getUnitOperatorByID($primary_unit_id, $item_id);
                $converted_primary = $primary_qty * (($primary_unit && $primary_unit->operation_value) ? $primary_unit->operation_value : 1);

                // ✅ Convert secondary qty to base qty (if exists)
                $converted_secondary = 0;
                if (!empty($secondary_qty) && !empty($secondary_unit_id)) {
                    $secondary_unit = $this->site->getUnitOperatorByID($secondary_unit_id, $item_id);
                    $converted_secondary = $secondary_qty * (($secondary_unit && $secondary_unit->operation_value) ? $secondary_unit->operation_value : 1);
                }

                $final_qty = $converted_primary + $converted_secondary;

                if (!empty($tran_cost) && $final_qty > 0) {
                    $delivery_per_unit = $tran_cost / $final_qty;
                } else {
                    $delivery_per_unit = 0;
                }   

                // Save to purchase_items
                $products[] = [
                    'purchase_id'      => $id,
                    'product_id'       => $item_id,
                    'cost'             => $item_cost,
                    'delivery'         => $delivery_per_unit ?? 0,
                    'quantity'         => $final_qty,
                    'primary_qty'      => $primary_qty,
                    'primary_unit_id'  => $primary_unit_id,
                    'secondary_qty'    => $secondary_qty,
                    'secondary_unit_id'=> $secondary_unit_id,
                    'subtotal'         => ($item_cost * $primary_qty) + ($item_cost * $secondary_qty),
                ];

                // Save to batches
                $batch_data = [
                    'product_id'    => $item_id,
                    'qty'           => $final_qty,
                    'unit_id'       => $primary_unit_id, // keep primary unit as base
                    'cost_price'    => $item_cost,
                    'cost_myr'      => ($item_cost / $exchange_rate),
                    'purchase_date' => $this->input->post('date'),
                    'store_id'      => $this->input->post('store'),
                    'purchase_id'   => $id
                ];
                $this->db->insert('product_batches', $batch_data);

                $total += ($item_cost * $primary_qty) + ($item_cost * $secondary_qty);
            }
        }

        if (empty($products)) {
            $this->form_validation->set_rules('product', lang('order_items'), 'required');
        }

        // 3️⃣ Update purchase header
        $paid = $this->input->post('advance_deducted') ?? 0;
        $status = $paid <= 0 ? 'due' : ($total <= $paid ? 'paid' : 'partial');
        
        $data = [
            'date'             => $this->input->post('date'),
            'reference'        => $this->input->post('reference'),
            'note'             => $this->input->post('note', true),
            'supplier_id'      => $this->input->post('supplier'),
            'store_id'         => $this->input->post('store'),
            'container_box'    => $this->input->post('container_box'),
            'exchange_rate'    => $exchange_rate,
            'received'         => $this->input->post('received'),
            'total'            => $total,
            'advance_deducted' => $this->input->post('advance_deducted') ?? 0,
            'delivery'         => $this->input->post('delivery') ?? 0,
            'paid'             => $this->input->post('advance_deducted') ?? 0,
            'status'           => $status,
        ];

        // 4️⃣ Handle file upload
        if ($_FILES['userfile']['size'] > 0) {
            $this->load->library('upload');
            $config['upload_path']   = 'files/';
            $config['allowed_types'] = $this->allowed_types;
            $config['max_size']      = '2000';
            $config['overwrite']     = false;
            $config['encrypt_name']  = true;
            $this->upload->initialize($config);

            if (!$this->upload->do_upload()) {
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', $error);
                redirect('purchases/edit/' . $id);
            }

            $data['attachment'] = $this->upload->file_name;
        }
    }

    // 5️⃣ Save
    if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products)) {
        $this->session->set_userdata('remove_spo', 1);
        $this->session->set_flashdata('message', lang('purchase_updated'));
        redirect('purchases');
    } else {
        
        // Load purchase + items
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($id);
        $inv_items = $this->purchases_model->getAllPurchaseItems($id);
        $c = rand(100000, 9999999);
        $pr = [];

        foreach ($inv_items as $item) {
            $row = $this->site->getProductByID($item->product_id);
            
            if (!$row) {
                // Product deleted: fallback to purchase item info
                $row = new stdClass();
                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->base_unit_id = $item->unit_id;
                $row->is_dual_unit = ($item->secondary_unit) ? 1 : 0;
            }
        
            // Fill purchase item info
            $row->qty            = $item->quantity ?? 0;
            $row->cost           = $item->net_unit_cost ?? 0;
            $row->primary_qty    = $item->primary_qty ?? 0;
            $row->primary_unit   = $item->primary_unit ?? $row->base_unit_id;
            $row->secondary_qty  = $item->secondary_qty ?? 0;
            $row->secondary_unit = $item->secondary_unit ?? 0;
        
            $ri = $this->Settings->item_addition ? $row->id : $c;
            $pr[$ri] = [
                'id'      => $ri,
                'item_id' => $row->id,
                'label'   => $row->name . ' (' . $row->code . ')',
                'row'     => $row
            ];
            $c++;
        }
        
        $product_units = [];
        foreach ($inv_items as $item) {
            $product_units[$item->product_id] = $this->site->getUnitsByProductId($item->product_id);
        }
        
        $this->data['product_units']  = $product_units;
        $this->data['items']          = json_encode($pr);
        $this->data['error']          = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['suppliers']      = $this->site->getAllSuppliers();
        $this->data['exchange_rates'] = $this->site->getAllExchangeRates();
        $this->data['stores']         = $this->site->getAllStores();
        $this->data['container_boxes']= $this->site->getAllContainerBoxes();
        $this->data['page_title']     = lang('edit_purchase');
        $bc = [['link' => site_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('edit_purchase')]];
        $meta = ['page_title' => lang('edit_purchase'), 'bc' => $bc];
        $this->page_construct('purchases/edit', $this->data, $meta);

    }
}


    public function add_expense()
    {
        if (!$this->session->userdata('store_id')) {
            $this->session->set_flashdata('warning', lang('please_select_store'));
            redirect('stores');
        }

       if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->load->helper('security');

        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Admin) {
                $date = trim($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'       => $date,
                'type_id'       => $this->input->post('category'),
                'container_box' => $this->input->post('container_box'),
                'reference'  => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                'quantity'     => $this->input->post('quantity'),
                'amount'     => $this->input->post('amount'),
                'total'     => $this->input->post('total'),
                'currency'     => $this->input->post('currency'),
                'exchange_rate'     => $this->input->post('exchange_rate'),
                'amount_base'     => $this->input->post('amount_base'),
                'created_by' => $this->session->userdata('user_id'),
                'store_id'   => $this->session->userdata('store_id'),
                'note'       => $this->input->post('note', true),
                
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
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->tec->print_arrays($data);
        } elseif ($this->input->post('add_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addExpense($data)) {
            $this->session->set_flashdata('message', lang('expense_added'));
            redirect('purchases/expenses');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('add_expense');
            $this->data['categories'] = $this->site->getAllExpenseType();
            $this->data['exchange_rates']  = $this->site->getAllExchangeRates();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            
            $this->data['container_boxes']  = $this->site->getAllContainerBoxes();
            $bc                       = [['link' => site_url('purchases'), 'page' => lang('purchases')], ['link' => site_url('purchases/expenses'), 'page' => lang('expenses')], ['link' => '#', 'page' => lang('add_expense')]];
            $meta                     = ['page_title' => lang('add_expense'), 'bc' => $bc];
            $this->page_construct('purchases/add_expense', $this->data, $meta);
        }
    }

public function delete($id = null)
{
    /* Mobile AJAX delete request ကို header သို့မဟုတ် URL flag ဖြင့်သိမည်။ */
    $is_ajax_delete =
        $this->input->is_ajax_request() ||
        (int) $this->input->get('ajax_delete') === 1;

    /*
     * AJAX request အတွက် redirect/HTML မပြန်စေဘဲ JSON ကိုသာ ပြန်မည်။
     * Output buffer ထဲရှိ notice/whitespace များကိုလည်း ရှင်းပစ်ထားသည်။
     */
    $send_delete_json = function ($status, $message, $http_status = 200) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $this->output->enable_profiler(false);
        $this->output
            ->set_status_header($http_status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'status'  => $status,
                'message' => $message
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
        exit;
    };

    if (DEMO) {
        log_message(
            'error',
            'Attempted delete in DEMO mode.'
        );

        if ($is_ajax_delete) {
            $send_delete_json('error', lang('disabled_in_demo'), 403);
        }

        $this->session->set_flashdata(
            'error',
            lang('disabled_in_demo')
        );

        redirect($_SERVER['HTTP_REFERER'] ?? 'purchases');
        return;
    }

    if ($this->input->get('id')) {
        $id = (int) $this->input->get('id');
    }

    $id = (int) $id;

    if (!$id) {
        log_message(
            'error',
            'Purchase delete failed: Missing ID'
        );

        if ($is_ajax_delete) {
            $send_delete_json('error', 'Invalid Purchase ID', 400);
        }

        $this->session->set_flashdata(
            'error',
            'Invalid Purchase ID'
        );

        redirect('purchases');
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Purchase
    |--------------------------------------------------------------------------
    */
    $purchase = $this->db
        ->where('id', $id)
        ->get('purchases')
        ->row();

    if (!$purchase) {

        log_message(
            'error',
            'Purchase delete failed: Purchase not found. ID: ' . $id
        );

        if ($is_ajax_delete) {
            $send_delete_json('error', 'Purchase not found', 404);
        }

        $this->session->set_flashdata(
            'error',
            'Purchase not found'
        );

        redirect('purchases');
        return;
    }
    
    /*
    |--------------------------------------------------------------------------
    | Block Delete: Purchase Has Receive Records
    |--------------------------------------------------------------------------
    */
    $receipt_count = $this->db
        ->where('purchase_id', $id)
        ->count_all_results('tec_purchase_receipts');
    
    if ($receipt_count > 0) {
    
        log_message(
            'warning',
            'Purchase delete blocked because receive records exist. ' .
            'Purchase ID: ' . $id .
            ' | Receipt Count: ' . $receipt_count
        );
    
        if ($is_ajax_delete) {
            $send_delete_json(
                'error',
                'ဤအဝယ်ဘောင်ချာကို ငွေလက်ခံခြင်း (သို့မဟုတ်) ပစ္စည်းလက်ခံထားခြင်းကြောင့် ဖျက်၍မရပါ။',
                409
            );
        }

        $this->session->set_flashdata(
            'error','ဤအဝယ်ဘောင်ချာကို ငွေလက်ခံခြင်း (သို့မဟုတ်) ပစ္စည်းလက်ခံထားခြင်းကြောင့် ဖျက်၍မရပါ။'
        );

        redirect('purchases');
        return;
    }
    
    /*
    |--------------------------------------------------------------------------
    | Block Delete: Purchase Has Payment Records
    |--------------------------------------------------------------------------
    */
    $payment_count = $this->db
        ->where('purchase_id', $id)
        ->count_all_results('tec_ppayments');
    
    if ($payment_count > 0) {
    
        log_message(
            'warning',
            'Purchase delete blocked because payments exist. ' .
            'Purchase ID: ' . $id .
            ' | Payment Count: ' . $payment_count
        );
    
        if ($is_ajax_delete) {
            $send_delete_json(
                'error',
                'ဤအဝယ်ဘောင်ချာအတွက် ငွေပေးချေမှု မှတ်တမ်းရှိပြီးဖြစ်သောကြောင့် ဖျက်၍မရပါ။',
                409
            );
        }

        $this->session->set_flashdata(
            'error',
            'ဤအဝယ်ဘောင်ချာအတွက် ငွေပေးချေမှု မှတ်တမ်းရှိပြီးဖြစ်သောကြောင့် ' .
            'ဖျက်၍မရပါ။'
        );

        redirect('purchases');
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Check Purchase Stock Has Been Used
    |--------------------------------------------------------------------------
    |
    | stock_batches ထဲက qty_base သည် လက်ရှိကျန် stock ဖြစ်သည်။
    | Purchase Item ရဲ့ မူလ base quantity ထက် လျော့နေပါက
    | အဲဒီ Purchase stock ကို ရောင်း/ထုတ်သုံးပြီးသားဖြစ်သည်။
    |
    */

    $items = $this->purchases_model
        ->getAllPurchaseItems($id);

    foreach ($items as $item) {

        /*
         * ဒီ Purchase + Product အတွက်
         * လက်ရှိ Stock Batch balance
         */
        $batch = $this->db
            ->select(
                'COALESCE(SUM(qty_base), 0) AS remaining_qty',
                false
            )
            ->where('purchase_id', $id)
            ->where('product_id', $item->product_id)
            ->get('stock_batches')
            ->row();

        $remaining_qty = $batch
            ? (float) $batch->remaining_qty
            : 0;


        /*
         * Purchase Item ရဲ့ original base quantity
         *
         * KLSPOS multi-unit purchase မှာ primary_qty ကို
         * base quantity အဖြစ် သိမ်းထားပါက ဒီ field ကိုသုံးပါ။
         */
        $original_qty = isset($item->primary_qty)
            ? (float) $item->primary_qty
            : (float) $item->quantity;


        /*
         * Stock Received ဖြစ်ပြီး batch balance လျော့ထားတယ်ဆို
         * အသုံးပြုပြီးဖြစ်သည်။
         */
        if (
            (int) $purchase->received === 1 &&
            $remaining_qty < $original_qty
        ) {

            log_message(
                'warning',
                'Purchase delete blocked because stock has been used. ' .
                'Purchase ID: ' . $id .
                ' | Product ID: ' . $item->product_id .
                ' | Original Qty: ' . $original_qty .
                ' | Remaining Qty: ' . $remaining_qty
            );

            if ($is_ajax_delete) {
                $send_delete_json(
                    'error',
                    'ဤအဝယ်ဘောင်ချာမှ ပစ္စည်းအချို့ကို ရောင်းချခြင်း သို့မဟုတ် အသုံးပြုခြင်း ပြုလုပ်ပြီးဖြစ်သောကြောင့် ဖျက်၍မရပါ။',
                    409
                );
            }

            $this->session->set_flashdata(
                'error',
                'ဤအဝယ်ဘောင်ချာမှ ပစ္စည်းအချို့ကို ရောင်းချခြင်း သို့မဟုတ် ' .
                'အသုံးပြုခြင်း ပြုလုပ်ပြီးဖြစ်သောကြောင့် ဖျက်၍မရပါ။'
            );

            redirect('purchases');
            return;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Purchase
    |--------------------------------------------------------------------------
    */

    $this->db->trans_begin();

    try {

        foreach ($items as $item) {

            log_message(
                'debug',
                'Deleting stock for Product ID: ' .
                $item->product_id
            );


            /*
             * Delete Stock Movements
             */
            $this->db->where(
                'purchase_id',
                $id
            );

            $this->db->where(
                'product_id',
                $item->product_id
            );

            $this->db->delete(
                'stock_movements'
            );


            /*
             * Delete Stock Batches
             */
            $this->db->where(
                'purchase_id',
                $id
            );

            $this->db->where(
                'product_id',
                $item->product_id
            );

            $this->db->delete(
                'stock_batches'
            );
        }


        /*
         * Delete Purchase Items
         */
        $this->db
            ->where('purchase_id', $id)
            ->delete('purchase_items');


        /*
         * Delete Purchase
         */
        $this->db
            ->where('id', $id)
            ->delete('purchases');


        /*
         * Transaction Check
         */
        if ($this->db->trans_status() === false) {

            throw new Exception(
                'Database transaction failed.'
            );
        }


        $this->db->trans_commit();


        log_message(
            'info',
            'Purchase deleted successfully. ID: ' .
            $id .
            ' | Ref: ' .
            $purchase->reference_no .
            ' | User ID: ' .
            $this->session->userdata('user_id') .
            ' | IP: ' .
            $this->input->ip_address()
        );


        if ($is_ajax_delete) {
            $send_delete_json(
                'success',
                'အဝယ်ဘောင်ချာကို အောင်မြင်စွာ ဖျက်ပြီးပါပြီ။'
            );
        }

        /* AJAX မဟုတ်သော request အတွက် fallback */
        redirect('purchases?delete_result=success');
        return;

    } catch (Exception $e) {

        $this->db->trans_rollback();

        log_message(
            'error',
            'Purchase delete failed. ID: ' .
            $id .
            ' | Error: ' .
            $e->getMessage()
        );

        if ($is_ajax_delete) {
            $send_delete_json(
                'error',
                'အဝယ်ဘောင်ချာကို ဖျက်၍မရပါ။',
                500
            );
        }

        redirect('purchases?delete_result=failed');
        return;
    }
}


    public function delete_expense($id = null)
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

        $expense = $this->purchases_model->getExpenseByID($id);
        if ($this->purchases_model->deleteExpense($id)) {
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            $this->session->set_flashdata('message', lang('expense_deleted'));
            redirect('purchases/expenses');
        }
    }

    

    public function edit_expense($id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference', lang('reference'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Admin) {
                $date = trim($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'      => $date,
                'type_id'=> $this->input->post('category'),
                'container_box' => $this->input->post('container_box'),
                'reference' => $this->input->post('reference'),
                'quantity'    => $this->input->post('quantity'),
                'amount'    => $this->input->post('amount'),
                'total'    => $this->input->post('total'),
                'note'      => $this->input->post('note', true),
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
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->tec->print_arrays($data);
        } elseif ($this->input->post('edit_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateExpense($id, $data)) {
            $this->session->set_flashdata('message', lang('expense_updated'));
            redirect('purchases/expenses');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['expense']    = $this->purchases_model->getExpenseByID($id);
            $this->data['categories'] = $this->site->getAllExpenseType();
            $this->data['container_boxes']  = $this->site->getAllContainerBoxes();
            $this->data['page_title'] = lang('edit_expense');
            $bc                       = [['link' => site_url('purchases'), 'page' => lang('purchases')], ['link' => site_url('purchases/expenses'), 'page' => lang('expenses')], ['link' => '#', 'page' => lang('edit_expense')]];
            $meta                     = ['page_title' => lang('edit_expense'), 'bc' => $bc];
            $this->page_construct('purchases/edit_expense', $this->data, $meta);
        }
    }

    public function expense_note($id = null)
    {
        if (!$this->Admin) {
            if ($expense->created_by != $this->session->userdata('user_id')) {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect($_SERVER['HTTP_REFERER'] ?? 'pos');
            }
        }

        $expense                  = $this->purchases_model->getExpenseByID($id);
        $this->data['user']       = $this->site->getUser($expense->created_by);
        $this->data['expense']    = $expense;
        $this->data['page_title'] = $this->lang->line('expense_note');
        $this->load->view($this->theme . 'purchases/expense_note', $this->data);
    }

    /* ----------------------------------------------------------------- */

    public function expenses($id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
    
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('expenses');
        $bc                       = [['link' => site_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('expenses')]];
        $meta                     = ['page_title' => lang('expenses'), 'bc' => $bc];
        $this->page_construct('purchases/expenses', $this->data, $meta);
    }


    public function get_expenses($user_id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        
        $this->load->library('datatables');

        if ($this->db->dbdriver == 'sqlite3') {
            $this->datatables->select(
                $this->db->dbprefix('expenses') . '.id as id, date, ' .
                $this->db->dbprefix('expensetype') . ".name as cname, 
                reference, amount, note, quantity,total,
                (" . $this->db->dbprefix('users') . ".first_name || ' ' || " . $this->db->dbprefix('users') . ".last_name) as user, 
                attachment", false);
        } else {
            $this->datatables->select(
                $this->db->dbprefix('expenses') . '.id as id, date, ' .
                $this->db->dbprefix('expensetype') . ".name as cname, 
                reference, amount, note, quantity,total,
                CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as user, 
                attachment", false);
        }

        $this->datatables->from('expenses')
            ->join('expensetype', 'expensetype.id=expenses.type_id', 'left')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->group_by('expenses.id');

        if (!$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }

        $this->datatables->where('expenses.store_id', $this->session->userdata('store_id'));

        $this->datatables->add_column(
        'Actions',
        "<div class='text-center'>
            <div class='btn-group'>
                <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                    <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
                </button>
                <ul class='dropdown-menu dropdown-menu-right'>
                    <li>
                        <a href='" . site_url('purchases/expense_note/$1') . "' class='tip' title='" . lang('expense_note') . "' data-toggle='ajax-modal'>
                            <i class='fa-solid fa-eye'></i> " . lang('expense_note') . "
                        </a>
                    </li>
                    <li>
                        <a href='" . site_url('purchases/edit_expense/$1') . "' class='tip' title='" . lang('edit') . "'>
                            <i class='fa fa-edit'></i> " . lang('edit') . "
                        </a>
                    </li>
                    <li>
                        <a href='" . site_url('purchases/delete_expense/$1') . "' class='tip text-danger' title='" . lang('delete') . "' onClick=\"return confirm('" . lang('alert_x_expense') . "')\">
                            <i class='fa-solid fa-trash'></i> " . lang('delete') . "
                        </a>
                    </li>
                </ul>
            </div>
        </div>",
    'id');

        $this->datatables->unset_column('id');
        echo $this->datatables->generate();
    }


public function get_purchases()
{
    $this->load->library('datatables');

    $supplier_filter = trim((string) $this->input->post('supplier_filter', true));
    $status_filter   = trim((string) $this->input->post('status_filter', true));
    $received_filter = trim((string) $this->input->post('received_filter', true));

    /* =========================================================
     * Purchase Summary - all filtered records
     * ========================================================= */
    if ((int) $this->input->post('summary_request') === 1) {
    
        $global_search = trim(
            (string) $this->input->post('global_search', true)
        );
    
        /*
         * IMPORTANT:
         * Table alias သုံးထားတဲ့အတွက် tec_ prefix ရှိ/မရှိ
         * ဘယ် database မှာမဆို အလုပ်လုပ်ပါမယ်။
         */
        $this->db
            ->select('COUNT(p.id) AS records', false)
            ->select('COALESCE(SUM(p.total), 0) AS total', false)
            ->select('COALESCE(SUM(p.paid), 0) AS paid', false)
            ->select(
                'COALESCE(SUM(p.total - p.paid), 0) AS due',
                false
            )
            ->from('purchases AS p')
            ->join(
                'suppliers AS s',
                's.id = p.supplier_id',
                'left'
            );
    
        /*
         * Supplier Filter
         */
        if ($supplier_filter !== '') {
            $this->db->where(
                's.name',
                $supplier_filter
            );
        }
    
        /*
         * Payment Status Filter
         */
        if ($status_filter === 'notpaid') {
    
            $this->db->group_start()
                ->where('p.status', 'partial')
                ->or_where('p.status', 'due')
                ->group_end();
    
        } elseif ($status_filter !== '') {
    
            $this->db->where(
                'p.status',
                $status_filter
            );
        }
    
        /*
         * Received Filter
         */
        if ($received_filter !== '') {
            $this->db->where(
                'p.received',
                $received_filter
            );
        }
    
        /*
         * Search Filter
         */
        if ($global_search !== '') {
    
            $this->db->group_start();
    
            $this->db->like(
                'p.id',
                $global_search
            );
    
            $this->db->or_like(
                'p.date',
                $global_search
            );
    
            $this->db->or_like(
                's.name',
                $global_search
            );
    
            $this->db->or_like(
                'p.total',
                $global_search
            );
    
            $this->db->or_like(
                'p.paid',
                $global_search
            );
    
            $this->db->or_like(
                'p.status',
                $global_search
            );
    
            $this->db->group_end();
        }
    
        $summary = $this->db
            ->get()
            ->row_array();
    
        /*
         * Safety defaults
         */
        if (!$summary) {
            $summary = [
                'records' => 0,
                'total'   => 0,
                'paid'    => 0,
                'due'     => 0,
            ];
        }
    
        $response = [
            'summary' => [
                'records' => (int) ($summary['records'] ?? 0),
                'total'   => (float) ($summary['total'] ?? 0),
                'paid'    => (float) ($summary['paid'] ?? 0),
                'due'     => (float) ($summary['due'] ?? 0),
            ]
        ];
    
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Column index → DB field mapping
    |--------------------------------------------------------------------------
    */
    $columnMap = [
        1 => 'purchases.id',
        2 => 'purchases.date',
        3 => 'suppliers.name',
        4 => 'purchases.total',
        5 => 'purchases.paid',
        6 => '(purchases.total - purchases.paid)',
        7 => 'purchases.status',
        8 => 'purchases.received',
    ];

    /*
    |--------------------------------------------------------------------------
    | Original Main Query
    |--------------------------------------------------------------------------
    |
    | ဒီ Select ကို မူလအလုပ်လုပ်ခဲ့တဲ့ပုံစံအတိုင်းထားပါတယ်။
    |
    */
    $this->datatables
        ->select(
        'purchases.id,
         purchases.date,
         suppliers.name,
         purchases.total,
         purchases.paid,
         (tec_purchases.total - tec_purchases.paid) as balance,
         purchases.status,
         purchases.received'
        )
        ->from('purchases')
        ->join(
            'suppliers',
            'suppliers.id = purchases.supplier_id',
            'left'
        );

    /* Purchase List filters - server-side paging နှင့်အတူ အသုံးပြုရန် */
    if ($supplier_filter !== '') {
        $this->datatables->where('suppliers.name', $supplier_filter);
    }
    if ($status_filter === 'notpaid') {
        $this->datatables->where("(purchases.status='partial' OR purchases.status='due')");
    } elseif ($status_filter !== '') {
        $this->datatables->where('purchases.status', $status_filter);
    }
    if ($received_filter !== '') {
        $this->datatables->where('purchases.received', $received_filter);
    }

    /*
    |--------------------------------------------------------------------------
    | Supplier Due Tab Only
    |--------------------------------------------------------------------------
    |
    | ပုံမှန် Purchase List request မှာ due_request မပါပါ။
    | Supplier Due Tab request မှာ due_request = 1 ပါလာပါမယ်။
    |
    */
    $due_request = (int) $this->input->post('due_request');
    $supplier_id = (int) $this->input->post('supplier_id');

    $status_filter = trim(
        (string) $this->input->post('status_filter', true)
    );

    /*
     * Supplier Due Tab မှလာတဲ့ request ကိုပဲ filter လုပ်ပါမယ်။
     * ပုံမှန် Purchase List ကို မထိခိုက်ပါ။
     */
    if ($due_request === 1) {

        /*
         * Supplier ရွေးထားလျှင် သူ့စာရင်းသာယူပါ။
         */
        if ($supplier_id > 0) {
            $this->datatables->where(
                'purchases.supplier_id',
                $supplier_id
            );
        } else {
            /*
             * Supplier မရွေးရသေးလျှင် စာရင်းမထုတ်ပါ။
             */
            $this->datatables->where(
                'purchases.id',
                0
            );
        }

        /*
         * Partial နှင့် Due စာရင်းများသာယူပါ။
         *
         * လက်ရှိ KLSPOS Datatables library နဲ့ကိုက်အောင်
         * where() ကို argument တစ်ခုပဲသုံးထားပါတယ်။
         */
        if ($status_filter === 'notpaid') {
            $this->datatables->where(
                "(purchases.status='partial'
                OR purchases.status='due')"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Existing Column Filters
    |--------------------------------------------------------------------------
    */
    if (!empty($_POST['columns'])) {
        foreach ($_POST['columns'] as $index => $col) {

            $value = trim(
                $col['search']['value'] ?? ''
            );

            if (
                $value === '' ||
                !isset($columnMap[$index])
            ) {
                continue;
            }

            /*
             * Exact match fields
             */
            if (
                in_array(
                    $columnMap[$index],
                    [
                        'purchases.received',
                        'suppliers.name'
                    ],
                    true
                )
            ) {
                $this->datatables->where(
                    $columnMap[$index],
                    $value
                );
            }

            /*
             * Not paid
             */
            elseif ($value === 'notpaid') {
                $this->datatables->where(
                    "(purchases.status='partial'
                    OR purchases.status='due')"
                );
            }

            /*
             * Date
             */
            elseif (
                $columnMap[$index] === 'purchases.date'
            ) {
                $this->datatables->like(
                    'purchases.date',
                    $value,
                    'after'
                );
            }

            /*
             * Everything else
             */
            else {
                $this->datatables->like(
                    $columnMap[$index],
                    $value
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Action Column
    |--------------------------------------------------------------------------
    */
    $this->datatables->add_column(
        'Actions',
        "<div class='text-center'>
            <div class='btn-group'>
                <button
                    type='button'
                    class='btn btn-primary dropdown-toggle'
                    data-toggle='dropdown'
                >
                    <i class='fa fa-cog'></i>
                    " . lang('actions') . "
                    <span class='caret'></span>
                </button>

                <ul class='dropdown-menu dropdown-menu-right'>
                    <li>
                        <a
                            href='" . site_url('purchases/view/$1') . "'
                            data-toggle='ajax-modal'
                        >
                            <i class='fa fa-eye'></i>
                            " . lang('view_purchase') . "
                        </a>
                    </li>

                    <li>
                        <a
                            href='" . site_url('purchases/payments/$1') . "'
                            data-toggle='ajax'
                        >
                            <i class='fa fa-money'></i>
                            " . lang('view_payments') . "
                        </a>
                    </li>

                    <li>
                        <a
                            href='" . site_url('purchases/receive/$1') . "'
                        >
                            <i class='fa fa-check'></i>
                            " . lang('receive') . "
                        </a>
                    </li>

                    <li>
                        <a
                            href='" . site_url('purchases/delete/$1') . "'
                            class='text-danger'
                            onclick=\"return confirm('" .
                                lang('alert_x_purchase') .
                            "')\"
                        >
                            <i class='fa fa-trash'></i>
                            " . lang('delete') . "
                        </a>
                    </li>
                </ul>
            </div>
        </div>",
        'id'
    );

    echo $this->datatables->generate();
}


    public function index()
    {
        
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['page_title'] = lang('purchases');
        $this->data['suppliers'] = $this->site->getAllSuppliers();
        $this->data['products']  = $this->reports_model->getAllProducts();
        $bc                       = [['link' => '#', 'page' => lang('purchases')]];
        $meta                     = ['page_title' => lang('purchases'), 'bc' => $bc];
        $this->page_construct('purchases/purchases', $this->data, $meta);
    }
    
    
    

    public function suggestions($id = null)
{
    if ($id) {
        $row = $this->site->getProductByID($id);

        if ($row) {
            $row->qty = 1;
            $row->unit_prices = $this->site->getProductUnitPrices($row->id);
            $row->unit_conversions = $this->site->getProductUnitConversions($row->id);
            $row->product_units = $this->site->getUnitsByProductID($row->id);

            $pr = [
                'id'     => str_replace('.', '', microtime(true)),
                'item_id'=> $row->id,
                'label'  => $row->name . ' (' . $row->code . ')',
                'row'    => $row
            ];

            echo json_encode($pr);
            exit;
        } else {
            echo json_encode(['id' => 0, 'label' => lang('no_match_found')]);
            exit;
        }
    }

    $term = $this->tec->parse_scale_barcode($this->input->get('term', true));
    if (is_array($term)) {
        $bqty   = $term['weight'] ?? null;
        $bprice = $term['price']  ?? null;
        $term   = $term['item_code'];
        $rows   = $this->purchases_model->getProductNames($term, null, true);
    } else {
        $bqty   = null;
        $bprice = null;
        $term   = $this->input->get('term', true);
        $rows   = $this->purchases_model->getProductNames($term);
    }

    if ($rows) {
        $pr = [];
        foreach ($rows as $row) {
            $row->qty = $bqty ?: ($bprice ? $bprice / $row->price : 1);
            $row->unit_prices = $this->site->getProductUnitPrices($row->id);
            $row->unit_conversions = $this->site->getProductUnitConversions($row->id);
            $row->product_units = $this->site->getUnitsByProductID($row->id);
            $units = $this->site->getUnitsByProductID($row->id);

            
            
            $pr[] = [
                'id'     => str_replace('.', '', microtime(true)),
                'item_id'=> $row->id,
                'label'  => $row->name . ' (' . $row->code . ')',
                'value'  => $row->name, // 👈 this is needed for jQuery UI
                'row'    => $row,
                'units'  => $units
            ];

        }
        echo json_encode($pr);
    } else {
        echo json_encode([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
    }
}


    public function view($id = null)
    {
        
        $this->data['purchase']   = $this->purchases_model->getPurchaseByID($id);
        $this->data['items']      = $this->purchases_model->getAllPurchaseItems($id);
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['page_title'] = lang('view_purchase');
        $this->load->view($this->theme . 'purchases/view', $this->data);
    }

    public function add_expensetype()
{
    if (!$this->Admin && !$this->Owner) {
        $this->session->set_flashdata(
            'error',
            lang('access_denied')
        );

        redirect('pos');
    }

    $this->form_validation->set_rules(
        'name',
        lang('name'),
        'required|trim'
    );

    if ($this->form_validation->run() === true) {
        $name = trim(
            $this->input->post('name', true)
        );

        /*
         * Name တူပြီးသား ရှိ/မရှိ စစ်ဆေးခြင်း
         */
        $name_exists = $this->db
            ->where('LOWER(name)', strtolower($name))
            ->count_all_results('tec_expensetype');

        if ($name_exists > 0) {
            $this->session->set_flashdata(
                'error',
                lang('expense_type_already_exists')
            );

            redirect('purchases/add_expensetype');
        }

        $data = [
            'code' => $this->purchases_model
                ->getNextExpenseTypeCode(),
            'name' => $name,
        ];

        if ($this->purchases_model->addExpenseType($data)) {
            log_message(
                'debug',
                'EXPENSE TYPE CREATE: Created successfully. Code: ' .
                $data['code']
            );

            $this->session->set_flashdata(
                'message',
                lang('expense_type_added')
            );

            redirect('purchases/expensetype');
        }

        $db_error = $this->db->error();

        log_message(
            'error',
            'EXPENSE TYPE CREATE: Insert failed. Error: ' .
            json_encode(
                $db_error,
                JSON_UNESCAPED_UNICODE
            )
        );

        $this->session->set_flashdata(
            'error',
            lang('expense_type_add_failed')
        );

        redirect('purchases/add_expensetype');
    }

    if ($this->input->post('create')) {
        $this->session->set_flashdata(
            'error',
            validation_errors()
        );

        redirect('purchases/add_expensetype');
    }

    $this->data['auto_code'] = $this->purchases_model
        ->getNextExpenseTypeCode();

    $this->data['error'] = validation_errors()
        ? validation_errors()
        : $this->session->flashdata('error');

    $this->data['page_title'] = lang('add_expensetype');

    $bc = [
        [
            'link' => site_url('purchases/expensetype'),
            'page' => lang('expense_types'),
        ],
        [
            'link' => '#',
            'page' => lang('add_expensetype'),
        ],
    ];

    $meta = [
        'page_title' => lang('add_expensetype'),
        'bc' => $bc,
    ];

    $this->page_construct(
        'purchases/add_expensetype',
        $this->data,
        $meta
    );
}

    public function expensetype()
    {
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['expensetype'] = $this->site->getAllExpenseType();
        $this->data['page_title'] = lang('expensetype');
        $bc                       = [['link' => '#', 'page' => lang('expensetype')]];
        $meta                     = ['page_title' => lang('expensetype'), 'bc' => $bc];
        $this->page_construct('purchases/expensetype', $this->data, $meta);
    }

    public function get_expensetype()
{
    $this->load->library('datatables');

    $this->datatables
        ->select('id, code, name')
        ->from('expensetype');

    $this->datatables->add_column(
        'Actions',
        "
        <div class='text-center'>
            <div class='btn-group'>
                <button
                    type='button'
                    class='btn btn-primary dropdown-toggle'
                    data-toggle='dropdown'
                    aria-haspopup='true'
                    aria-expanded='false'
                >
                    <i class='fa fa-cog'></i>
                    " . lang('actions') . "
                    <span class='caret'></span>
                </button>

                <ul class='dropdown-menu dropdown-menu-right'>
                    <li>
                        <a
                            href='" .
                                site_url(
                                    'purchases/edit_expensetype/$1'
                                ) .
                            "'
                            class='tip'
                            title='" . lang('edit') . "'
                        >
                            <i class='fa fa-edit'></i>
                            " . lang('edit') . "
                        </a>
                    </li>

                    <li>
                        <a
                            href='" .
                                site_url(
                                    'purchases/delete_expensetype/$1'
                                ) .
                            "'
                            class='tip text-danger'
                            title='" . lang('delete') . "'
                            onclick=\"return confirm('" .
                                lang('confirm_delete_expense_type') .
                            "');\"
                        >
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

    public function edit_expensetype($id = null)
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('name', lang('category_name'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'code' => $this->input->post('code'), 
            'name' => $this->input->post('name')
        ];

        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateCategory($id, $data)) {
            $this->session->set_flashdata('message', lang('category_updated'));
            redirect('purchases/expensetype');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['category']   = $this->site->getExpenseTypeByID($id);
            $this->data['page_title'] = lang('edit_expensetype');
            $bc                       = [['link' => site_url('purchases/expensetype'), 'page' => lang('expensetype')], ['link' => '#', 'page' => lang('edit_expensetype')]];
            $meta                     = ['page_title' => lang('edit_expensetype'), 'bc' => $bc];
            $this->page_construct('purchases/edit_expensetype', $this->data, $meta);
        }
    }

    public function get_product_units($product_id)
    {

        // Fetch units for the product_id
        $units = $this->site->get_units_by_product($product_id);

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($units);
    }

    public function getProductCost($product_id)
{
    $product = $this->purchases_model->getProductByID($product_id);
    if ($product) {
        echo json_encode(['cost' => $product->cost]);
    } else {
        echo json_encode(['cost' => 0]);
    }
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
                $config['allowed_types'] = 'jpg|jpeg|png|gif|webp|pdf';
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

        if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment)) {
            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $purchase                = $this->purchases_model->getPurchaseByID($id);
            $this->data['inv']   = $purchase;

            $this->load->view($this->theme . 'purchases/add_payment', $this->data);
        }
    }

    public function payments($id = null)
        {
            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
            $this->load->view($this->theme . 'purchases/payments', $this->data);
        }
    
    
    public function view_received($id = null)
    {
        if ($this->input->get('id')) {
            $id = (int) $this->input->get('id');
        };
     
        $id = (int) $id;
     
        if ($id <= 0) {
            show_404();
            return;
        };
     
        $purchase = $this->purchases_model->getPurchaseByID($id);
     
        if (!$purchase) {
            show_404();
            return;
        };
     
        /*
         * Only fully received purchases (received = 1) may be viewed here.
         * Partial (2) or not-received (0) purchases should use the
         * existing "Receive" page instead.
         */
        if ((int) $purchase->received !== 1) {
     
            $this->session->set_flashdata(
                'error',
                'ဤအဝယ်ဘောင်ချာအတွက် ပစ္စည်းအားလုံး လက်ခံပြီးမှသာ ' .
                'ဒီစာမျက်နှာကို ကြည့်ရှုနိုင်ပါသည်။'
            );
     
            redirect($_SERVER['HTTP_REFERER'] ?? 'purchases');
            return;
        };
     
        $this->data['purchase'] = $purchase;
        $this->data['stores'] = $this->site->getAllStores();
        // $this->data['items'] = $this->purchases_model->getPurchaseReceiptHistory($id); //receved item sistory
        $this->data['items'] = $this->purchases_model->getPurchaseReceiveItems($id); //purchase items
        
        $this->load->view(
            $this->theme . 'purchases/view_received_products',
            $this->data
        );
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

        if ($this->purchases_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect('purchases');
        }
    }

    public function edit_payment($id = null)
{
    if (!$this->Admin) {
        $this->session->set_flashdata('error', lang('access_denied'));
        redirect($_SERVER['HTTP_REFERER'] ?? 'purchases');
        return;
    }

    // =========================================================
    // Payment ID
    // =========================================================
    if ($this->input->get('id')) {
        $id = (int) $this->input->get('id');
    }

    $id = (int) $id;

    if ($id <= 0) {
        show_error('Invalid Payment ID');
        return;
    }

    // =========================================================
    // Existing Payment
    // =========================================================
    $existing = $this->purchases_model->getPaymentByID($id);

    if (!$existing) {
        show_error('Payment not found');
        return;
    }

    $purchase_id = (int) $existing->purchase_id;

    // =========================================================
    // Purchase
    // =========================================================
    $inv = $this->db
        ->get_where('tec_purchases', [
            'id' => $purchase_id
        ])
        ->row();

    if (!$inv) {
        show_error('Purchase not found');
        return;
    }

    // =========================================================
    // Validation
    // =========================================================
    $this->form_validation->set_rules(
        'date',
        lang('date'),
        'trim|required'
    );

    $this->form_validation->set_rules(
        'amount-paid',
        lang('amount'),
        'trim|required|numeric|greater_than[0]'
    );

    $this->form_validation->set_rules(
        'paid_by',
        lang('paid_by'),
        'trim|required'
    );

    // =========================================================
    // Submit
    // =========================================================
    if ($this->form_validation->run() == true) {

        $new_amount = (float) $this->input->post('amount-paid');

        // =====================================================
        // Other Payments Total
        // Current payment ကို မထည့်ဘဲ စုမယ်
        // =====================================================
        $other_payment = $this->db
            ->select('COALESCE(SUM(amount), 0) AS total_paid', false)
            ->where('purchase_id', $purchase_id)
            ->where('id !=', $id)
            ->get('tec_ppayments')
            ->row();

        $other_paid = $other_payment
            ? (float) $other_payment->total_paid
            : 0;

        /*
         * ဥပမာ
         * Purchase Total = 34,000
         * Other Payments = 1,000
         *
         * ဒီ Payment ကို အများဆုံး 33,000 အထိ
         * ပြင်ခွင့်ပေးမယ်။
         */
        $max_amount = (float) $inv->total - $other_paid;

        if ($max_amount < 0) {
            $max_amount = 0;
        }

        // =====================================================
        // Prevent Over Payment
        // =====================================================
        if ($new_amount > ($max_amount + 0.00001)) {

            $this->data['error'] =
                'ပေးချေမည့်ပမာဏသည် ပေးရန်ရှိသည့်ငွေ ' .
                $this->tec->formatMoney($max_amount) .
                ' ထက် မကျော်ရပါ။';

            $this->data['payment'] = $existing;
            $this->data['inv']     = $inv;

            $this->load->view(
                $this->theme . 'purchases/edit_payment',
                $this->data
            );

            return;
        }

        // =====================================================
        // Payment Data
        // =====================================================
        $payment = [

            'purchase_id' => $purchase_id,

            'date' => $this->input->post(
                'date',
                true
            ),

            /*
             * Reference ကို Edit Page မှာ hidden field ဖြင့်
             * ပို့ထားရင် အသစ်ကိုယူမယ်။
             * မပို့ထားရင် အဟောင်းကိုဆက်ထားမယ်။
             */
            'reference' => $this->input->post('reference', true)
                ? $this->input->post('reference', true)
                : $existing->reference,

            'amount' => $new_amount,

            'paid_by' => $this->input->post(
                'paid_by',
                true
            ),

            'cheque_no' => $this->input->post(
                'cheque_no',
                true
            ),

            'gc_no' => $this->input->post(
                'gift_card_no',
                true
            ),

            'note' => $this->input->post(
                'note',
                true
            ),

            'updated_by' => $this->session->userdata(
                'user_id'
            ),

            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // =====================================================
        // Voucher / Attachment Upload
        // =====================================================
        if (
            isset($_FILES['userfile']) &&
            !empty($_FILES['userfile']['name']) &&
            isset($_FILES['userfile']['size']) &&
            (int) $_FILES['userfile']['size'] > 0
        ) {

            $this->load->library('upload');

            $upload_path = FCPATH . 'files/';

            if (!is_dir($upload_path)) {
                @mkdir($upload_path, 0755, true);
            }

            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|gif|webp|pdf',
                'max_size'      => 5120,
                'encrypt_name'  => true,
                'overwrite'     => false,
            ];

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('userfile')) {

                $this->data['error'] =
                    $this->upload->display_errors(
                        '<div>',
                        '</div>'
                    );

                $this->data['payment'] = $existing;
                $this->data['inv']     = $inv;

                $this->load->view(
                    $this->theme . 'purchases/edit_payment',
                    $this->data
                );

                return;
            }

            $upload_data = $this->upload->data();

            $payment['attachment'] =
                $upload_data['file_name'];
        }

        // =====================================================
        // Transaction
        // =====================================================
        $this->db->trans_start();

        // Update payment
        $updated = $this->purchases_model->updatePayment(
            $id,
            $payment
        );

        // =====================================================
        // Recalculate Purchase Paid
        // =====================================================
        $payment_total_row = $this->db
            ->select('COALESCE(SUM(amount), 0) AS paid_total', false)
            ->where('purchase_id', $purchase_id)
            ->get('tec_ppayments')
            ->row();

        $paid_total = $payment_total_row
            ? (float) $payment_total_row->paid_total
            : 0;

        $purchase_total = (float) $inv->total;

        // Safety
        if ($paid_total < 0) {
            $paid_total = 0;
        }

        // =====================================================
        // Payment Status
        // =====================================================
        if ($paid_total <= 0) {

            $status = 'due';

        } elseif ($paid_total >= $purchase_total) {

            $paid_total = $purchase_total;
            $status = 'paid';

        } else {

            $status = 'partial';
        }

        // =====================================================
        // Update Purchase
        // =====================================================
        $this->db
            ->where('id', $purchase_id)
            ->update('tec_purchases', [
                'paid'   => $paid_total,
                'status' => $status,
            ]);

        $this->db->trans_complete();

        // =====================================================
        // Result
        // =====================================================
        if (
            $this->db->trans_status() === false
        ) {

            log_message(
                'error',
                'Payment update failed. Payment ID: ' .
                $id .
                ' | Purchase ID: ' .
                $purchase_id
            );

            $this->session->set_flashdata(
                'error',
                'Payment update failed'
            );

            redirect(
                $_SERVER['HTTP_REFERER'] ?? 'purchases'
            );

            return;
        }

        log_message(
            'info',
            'Payment updated successfully. Payment ID: ' .
            $id .
            ' | Purchase ID: ' .
            $purchase_id .
            ' | Amount: ' .
            $new_amount .
            ' | User ID: ' .
            $this->session->userdata('user_id')
        );

        $this->session->set_flashdata(
            'message',
            lang('payment_updated')
        );

        redirect($_SERVER['HTTP_REFERER'] ?? 'purchases');
        return;
    }

    // =========================================================
    // Load Edit Modal
    // =========================================================
    $this->data['error'] =
        validation_errors()
            ? validation_errors()
            : $this->session->flashdata('error');

    $this->data['payment'] = $existing;

    /*
     * ဒီ $inv က အရေးကြီးပါတယ်။
     * Edit Page မှာ
     *
     * စုစုပေါင်း
     * ပေးဆောင်ပြီး
     * ပေးရန်ကျန်ငွေ
     *
     * တွက်ဖို့လိုပါတယ်။
     */
    $this->data['inv'] = $inv;

    $this->load->view(
        $this->theme . 'purchases/edit_payment',
        $this->data
    );
}




public function bulk_pay_due()
{
    $amount = (float) $this->input->post('amount');
    $ids    = $this->input->post('purchase_ids');

    if ($amount <= 0 || empty($ids)) {
        echo json_encode(['status' => 'error']);
        return;
    }

    $this->db->where_in('id', $ids);
    $this->db->order_by('date', 'ASC');
    $purchases = $this->db->get('tec_purchases')->result();

    foreach ($purchases as $p) {

        if ($amount <= 0) break;

        $due = $p->total - $p->paid;
        if ($due <= 0) continue;

        if ($amount >= $due) {
            // FULL PAID
            $payment_amount = $due;
            $new_paid = $p->total;
            $new_status = 'paid';
            $amount -= $due;
        } else {
            // PARTIAL PAID
            $payment_amount = $amount;
            $new_paid = $p->paid + $amount;
            $new_status = 'partial';
            $amount = 0;
        }

        // Update purchases table
        $this->db->where('id', $p->id)
                 ->update('tec_purchases', [
                     'paid'   => $new_paid,
                     'status' => $new_status
                 ]);

        // Insert into ppayments table
        $payment_data = [
            'purchase_id' => $p->id,
            'date'        => date('Y-m-d H:i:s'),
            'amount'      => $payment_amount,
            'paid_by'     => $this->input->post('paid_by') ?? 'cash',
            'created_by'  => $this->session->userdata('user_id')
        ];
        $this->db->insert('ppayments', $payment_data);
    }

    echo json_encode(['status' => 'success']);
}

public function delete_expensetype($id = null)
{
    if (!$this->Admin && !$this->Owner) {
        $this->session->set_flashdata(
            'error',
            lang('access_denied')
        );

        redirect('pos');
    }

    $id = (int) $id;

    if ($id <= 0) {
        $this->session->set_flashdata(
            'error',
            lang('expense_type_not_found')
        );

        redirect('purchases/expensetype');
    }

    $expense_type = $this->purchases_model
        ->getExpenseTypeById($id);

    if (!$expense_type) {
        log_message(
            'error',
            'EXPENSE TYPE DELETE: Record not found. ID: ' .
            $id
        );

        $this->session->set_flashdata(
            'error',
            lang('expense_type_not_found')
        );

        redirect('purchases/expensetype');
    }

    /*
     * အသုံးစရိတ်မှတ်တမ်းတွေမှာ ဒီ Expense Type ကို
     * အသုံးပြုထားပြီးသားဆိုရင် မဖျက်ရန်။
     */
    if (
        $this->purchases_model
            ->isExpenseTypeInUse($id)
    ) {
        log_message(
            'error',
            'EXPENSE TYPE DELETE: Record is in use. ID: ' .
            $id
        );

        $this->session->set_flashdata(
            'error',
            lang('expense_type_in_use')
        );

        redirect('purchases/expensetype');
    }

    $deleted = $this->purchases_model
        ->deleteExpenseType($id);

    if (!$deleted) {
        $db_error = $this->db->error();

        log_message(
            'error',
            'EXPENSE TYPE DELETE: Database delete failed. ID: ' .
            $id .
            ', DB Error: ' .
            json_encode(
                $db_error,
                JSON_UNESCAPED_UNICODE
            )
        );

        $this->session->set_flashdata(
            'error',
            lang('expense_type_delete_failed')
        );

        redirect('purchases/expensetype');
    }

    log_message(
        'debug',
        'EXPENSE TYPE DELETE: Deleted successfully. ID: ' .
        $id .
        ', Code: ' .
        $expense_type->code .
        ', Name: ' .
        $expense_type->name .
        ', User ID: ' .
        $this->session->userdata('user_id')
    );

    $this->session->set_flashdata(
        'message',
        lang('expense_type_deleted')
    );

    redirect('purchases/expensetype');
}

public function quick_add_expense_category()
{
    $this->output->set_content_type('application/json');

    $respond = function ($statusCode, array $payload) {
        $payload['csrf_hash'] = $this->security->get_csrf_hash();

        return $this->output
            ->set_status_header($statusCode)
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
    };

    if (!$this->input->is_ajax_request()) {
        return $respond(400, [
            'status'  => 'error',
            'message' => lang('invalid_request'),
        ]);
    }

    $name = trim((string) $this->input->post('name', true));
    $code = trim((string) $this->input->post('code', true));

    if ($name === '') {
        return $respond(422, [
            'status'  => 'error',
            'message' => lang('category_name_required'),
        ]);
    }

    /*
     * Return an existing category instead of creating a duplicate.
     * Most MySQL *_ci collations compare this case-insensitively.
     */
    $existing = $this->db
        ->select('id, code, name')
        ->where('name', $name)
        ->limit(1)
        ->get('expensetype')
        ->row();

    if ($existing) {
        return $respond(200, [
            'status'   => 'success',
            'existing' => true,
            'message'  => lang('category_already_exists_selected'),
            'category' => [
                'id'   => (int) $existing->id,
                'code' => (string) $existing->code,
                'name' => (string) $existing->name,
            ],
        ]);
    }

    /*
     * Generate a code automatically when the user leaves it blank.
     */
    if ($code === '') {
        $code = 'EXP-' . date('ymdHis') . '-' . mt_rand(10, 99);
    }

    $codeExists = $this->db
        ->select('id')
        ->where('code', $code)
        ->limit(1)
        ->get('expensetype')
        ->row();

    if ($codeExists) {
        return $respond(422, [
            'status'  => 'error',
            'message' => lang('category_code_already_exists'),
        ]);
    }

    $data = [
        'code' => $code,
        'name' => $name,
    ];

    if (!$this->db->insert('expensetype', $data)) {
        log_message(
            'error',
            'Quick add expense category failed: ' .
            json_encode($this->db->error(), JSON_UNESCAPED_UNICODE)
        );

        return $respond(500, [
            'status'  => 'error',
            'message' => lang('category_save_failed'),
        ]);
    }

    $categoryId = (int) $this->db->insert_id();

    return $respond(200, [
        'status'   => 'success',
        'existing' => false,
        'message'  => lang('category_saved_successfully'),
        'category' => [
            'id'   => $categoryId,
            'code' => $code,
            'name' => $name,
        ],
    ]);
}

public function product_unit_data($product_id = null)
{
    $this->output->set_content_type('application/json');

    $product_id = (int) $product_id;

    if ($product_id <= 0) {
        return $this->output->set_output(json_encode([
            'status'  => 'error',
            'message' => 'Invalid product ID.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    $product = $this->db
        ->select(
            'p.id, p.code, p.name, p.base_unit_id, ' .
            'p.is_dual_unit, p.secondary_unit_id, ' .
            'u.name AS base_unit_name, u.code AS base_unit_code, ' .
            'su.name AS secondary_unit_name, su.code AS secondary_unit_code',
            false
        )
        ->from('products p')
        ->join('product_units u', 'u.id = p.base_unit_id', 'left')
        ->join('product_units su', 'su.id = p.secondary_unit_id', 'left')
        ->where('p.id', $product_id)
        ->get()
        ->row();

    if (!$product) {
        return $this->output->set_output(json_encode([
            'status'  => 'error',
            'message' => 'Product not found.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    $rows = $this->db
        ->select(
            'c.unit_id, c.operator, c.operation_value, ' .
            'u.name AS unit_name, u.code AS unit_code',
            false
        )
        ->from('product_unit_conversions c')
        ->join('product_units u', 'u.id = c.unit_id', 'left')
        ->where('c.product_id', $product_id)
        ->order_by('c.operation_value', 'ASC')
        ->order_by('c.id', 'ASC')
        ->get()
        ->result();

    $units = [];
    $has_base_row = false;

    foreach ($rows as $row) {
        $unit_id = (int) $row->unit_id;

        if ($unit_id <= 0) {
            continue;
        }

        $operator = $row->operator === '/' ? '/' : '*';
        $operation_value = (float) $row->operation_value;

        if ($operation_value <= 0) {
            $operation_value = 1;
        }

        if ($unit_id === (int) $product->base_unit_id) {
            $has_base_row = true;
            $operator = '*';
            $operation_value = 1;
        }

        $units[] = [
            'id'              => $unit_id,
            'unit_id'         => $unit_id,
            'name'            => (string) $row->unit_name,
            'unit_name'       => (string) $row->unit_name,
            'code'            => (string) $row->unit_code,
            'unit_code'       => (string) $row->unit_code,
            'operator'        => $operator,
            'operation_value' => $operation_value,
            'multiplier'      => $operator === '/'
                ? (1 / $operation_value)
                : $operation_value,
        ];
    }

    /*
     * Older products may not have a saved 1:1 base-unit row yet.
     * Add it to the JSON response so the Purchase Unit dropdown always
     * includes the base unit.
     */
    if (!$has_base_row && (int) $product->base_unit_id > 0) {
        array_unshift($units, [
            'id'              => (int) $product->base_unit_id,
            'unit_id'         => (int) $product->base_unit_id,
            'name'            => (string) $product->base_unit_name,
            'unit_name'       => (string) $product->base_unit_name,
            'code'            => (string) $product->base_unit_code,
            'unit_code'       => (string) $product->base_unit_code,
            'operator'        => '*',
            'operation_value' => 1,
            'multiplier'      => 1,
        ]);
    }

    return $this->output->set_output(json_encode([
        'status'     => 'success',
        'product_id' => (int) $product->id,
        'product'    => [
            'id'   => (int) $product->id,
            'code' => (string) $product->code,
            'name' => (string) $product->name,
        ],
        'base_unit' => [
            'id'   => (int) $product->base_unit_id,
            'name' => (string) $product->base_unit_name,
            'code' => (string) $product->base_unit_code,
        ],
        'is_dual_unit' => (int) $product->is_dual_unit === 1,
        'secondary_unit' => [
            'id'   => (int) $product->secondary_unit_id,
            'name' => (string) $product->secondary_unit_name,
            'code' => (string) $product->secondary_unit_code,
        ],
        'units' => $units,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

/*
|--------------------------------------------------------------------------
| 1. NEW PAGE - Purchase Receive
|--------------------------------------------------------------------------
| Purchases.php ထဲမှာ ထည့်ပါ။
*/
public function receive($id = null)
{
    if ($this->input->get('id')) {
        $id = (int) $this->input->get('id');
    }

    $id = (int) $id;

    if ($id <= 0) {
        $this->session->set_flashdata(
            'error',
            'Invalid Purchase ID'
        );

        redirect('purchases');
        return;
    }

    $purchase = $this->purchases_model
        ->getPurchaseByID($id);

    if (!$purchase) {
        $this->session->set_flashdata(
            'error',
            'Purchase not found'
        );

        redirect('purchases');
        return;
    }

    $items = $this->purchases_model
        ->getPurchaseReceiveItems($id);

    if (empty($items)) {
        $this->session->set_flashdata(
            'error',
            'ဒီဘောင်ချာတွင် ပစ္စည်းမရှိပါ။'
        );

        redirect('purchases');
        return;
    }

    $this->data['purchase'] = $purchase;
    $this->data['items']    = $items;

    /*
     * ပစ္စည်းလက်ခံသည့်နေရာ ပြောင်းရွေးနိုင်ရန်
     */
    $this->data['stores'] = $this->site
        ->getAllStores();

    /* Header က flash message ကိုပြပြီးသားဖြစ်လို့ body မှာ ထပ်မပြပါ။ */
    $this->data['error'] = validation_errors()
        ? validation_errors()
        : null;

    $this->data['page_title'] =
        'ပစ္စည်းလက်ခံရန်';

    $bc = [
        [
            'link' => site_url('purchases'),
            'page' => lang('purchases')
        ],
        [
            'link' => '#',
            'page' => 'ပစ္စည်းလက်ခံရန်'
        ]
    ];

    $meta = [
        'page_title' => 'ပစ္စည်းလက်ခံရန်',
        'bc'         => $bc
    ];

    /*
     * Popup မဟုတ်တော့ပါ။
     * Normal ERP page အဖြစ်ဖွင့်ပါမယ်။
     *
     * View:
     * themes/YOUR_THEME/views/purchases/receive.php
     */
    $this->page_construct(
        'purchases/receive',
        $this->data,
        $meta
    );
}


/*
|--------------------------------------------------------------------------
| 2. SAVE - Partial Purchase Receive
|--------------------------------------------------------------------------
| လက်ရှိ receive_products() function ကို ဒီ function နဲ့အစားထိုးပါ။
*/
public function receive_products()
{
    $purchase_id = (int) $this->input->post(
        'purchase_id'
    );

    if ($purchase_id <= 0) {
        $this->session->set_flashdata(
            'error',
            'Purchase ID မမှန်ပါ။'
        );

        redirect('purchases');
        return;
    }

    $back_url = 'purchases/receive/' . $purchase_id;


    // =========================================================
    // Purchase
    // =========================================================
    $purchase = $this->purchases_model
        ->getPurchaseByID($purchase_id);

    if (!$purchase) {
        $this->session->set_flashdata(
            'error',
            'Purchase မတွေ့ပါ။'
        );

        redirect('purchases');
        return;
    }

    if ((int) $purchase->received === 1) {
        $this->session->set_flashdata(
            'message',
            'ဒီဘောင်ချာမှ ပစ္စည်းအားလုံး လက်ခံပြီးဖြစ်ပါသည်။'
        );

        redirect('purchases');
        return;
    }


    // =========================================================
    // ပစ္စည်းလက်ခံသည့်နေရာ
    // =========================================================
    $receive_store_id = (int) $this->input->post(
        'receive_store_id'
    );

    if ($receive_store_id <= 0) {
        $this->session->set_flashdata(
            'error',
            'ပစ္စည်းလက်ခံသည့်နေရာ ရွေးပါ။'
        );

        redirect($back_url);
        return;
    }

    /*
     * User POST က store id ကိုယုံပြီး မသုံးပါ။
     * DB မှာတကယ်ရှိ/မရှိ အရင်စစ်ပါ။
     */
    $receive_store = $this->db
        ->where('id', $receive_store_id)
        ->get('stores')
        ->row();

    if (!$receive_store) {
        $this->session->set_flashdata(
            'error',
            'ရွေးချယ်ထားသည့် ပစ္စည်းလက်ခံသည့်နေရာ မတွေ့ပါ။'
        );

        redirect($back_url);
        return;
    }


    // =========================================================
    // Receive Date
    // datetime-local:
    // 2026-08-12T19:30
    // =========================================================
    $received_at = trim(
        (string) $this->input->post(
            'received_at',
            true
        )
    );

    if ($received_at === '') {
        $this->session->set_flashdata(
            'error',
            'လက်ခံသည့်ရက်စွဲ ရွေးပါ။'
        );

        redirect($back_url);
        return;
    }

    $receive_timestamp = strtotime($received_at);

    if ($receive_timestamp === false) {
        $this->session->set_flashdata(
            'error',
            'လက်ခံသည့်ရက်စွဲ မမှန်ပါ။'
        );

        redirect($back_url);
        return;
    }

    $received_at = date(
        'Y-m-d H:i:s',
        $receive_timestamp
    );


    // =========================================================
    // Note
    // =========================================================
    $note = trim(
        (string) $this->input->post(
            'receive_note',
            true
        )
    );


    // =========================================================
    // Qty
    // =========================================================
    $posted_qty = $this->input->post(
        'receive_qty'
    );

    if (!is_array($posted_qty)) {
        $posted_qty = [];
    }

    $receive_map = [];

    foreach ($posted_qty as $item_id => $qty) {

        $item_id = (int) $item_id;
        $qty     = trim((string) $qty);

        if (
            $item_id <= 0 ||
            $qty === ''
        ) {
            continue;
        }

        if (!is_numeric($qty)) {
            $this->session->set_flashdata(
                'error',
                'လက်ခံမည့်အရေအတွက်တွင် ဂဏန်းသာ ထည့်ပါ။'
            );

            redirect($back_url);
            return;
        }

        $qty = (float) $qty;

        if ($qty < 0) {
            $this->session->set_flashdata(
                'error',
                'လက်ခံမည့်အရေအတွက်သည် 0 ထက် မငယ်ရပါ။'
            );

            redirect($back_url);
            return;
        }

        if ($qty > 0) {
            $receive_map[$item_id] = $qty;
        }
    }

    if (empty($receive_map)) {
        $this->session->set_flashdata(
            'error',
            'လက်ခံမည့် ပစ္စည်းအရေအတွက် တစ်ခုခု ထည့်ပါ။'
        );

        redirect($back_url);
        return;
    }


    // =========================================================
    // Debug
    // =========================================================
    log_message(
        'debug',
        'PARTIAL RECEIVE POST => ' .
        print_r([
            'purchase_id'      => $purchase_id,
            'receive_store_id' => $receive_store_id,
            'received_at'      => $received_at,
            'receive_map'      => $receive_map,
            'user_id'          => $this->session->userdata(
                'user_id'
            )
        ], true)
    );


    // =========================================================
    // Save
    // =========================================================
    
    $result = $this->purchases_model
        ->receivePurchaseItems(
            $purchase_id,
            $receive_map,
            $received_at,
            $note,
            (int) $this->session->userdata('user_id'),
            $receive_store_id
        );


    if (
        empty($result) ||
        empty($result['status'])
    ) {
        $message =
            !empty($result['message'])
                ? $result['message']
                : 'ပစ္စည်းလက်ခံမှု မအောင်မြင်ပါ။';

        log_message(
            'error',
            'PARTIAL RECEIVE FAILED => ' .
            $message
        );

        $this->session->set_flashdata(
            'error',
            $message
        );

        redirect($back_url);
        return;
    }


    // =========================================================
    // Success
    // =========================================================
    log_message(
        'info',
        'PARTIAL RECEIVE SUCCESS => ' .
        print_r($result, true)
    );

    $this->session->set_flashdata(
        'message',
        'ပစ္စည်းလက်ခံမှု အောင်မြင်ပါသည်။'
    );

    /*
     * အကုန်လက်ခံပြီးပြီဆို Purchase List ပြန်သွားမယ်။
     * Partial ဖြစ်သေးရင် ဒီ Receive Page မှာပဲ
     * ကျန်အရေအတွက်ကို ဆက်ပြမယ်။
     */
    if (
        isset($result['received_status']) &&
        (int) $result['received_status'] === 1
    ) {
        redirect(
            site_url('purchases') .
            '?app=1&app_lang=' .
            rawurlencode(
                $this->input->post('app_lang', true)
                ?: 'myanmar'
            )
        );
        return;
    }

    redirect(
        site_url('purchases/receive/' . $purchase_id) .
        '?app=1&app_lang=' .
        rawurlencode(
            $this->input->post('app_lang', true)
            ?: 'myanmar'
        )
    );
}



/*
|--------------------------------------------------------------------------
| Reports.php
| FIXED get_product_purchase()
|--------------------------------------------------------------------------
|
| အဓိကပြင်ထားတာ:
| - purchase_items.primary_unit ရှိရင် အဲဒီ unit name သုံးမယ်
| - primary_unit မရှိ/0/NULL ဖြစ်ရင် products.base_unit_id ကို fallback သုံးမယ်
| - secondary unit လည်း products.secondary_unit_id ကို fallback သုံးမယ်
|
| JSON example:
| {
|   "primary_qty": 7,
|   "primary_unit_name": "ထုပ်",
|   "secondary_qty": 0,
|   "secondary_unit_name": ""
| }
|--------------------------------------------------------------------------
*/
public function get_product_purchase($v = null)
{
    $product_id = $this->input->get('product');

    if ($product_id === null || $product_id === '') {
        $product_id = $this->input->post('product');
    }

    $start_date = $this->input->get_post('start_date');
    $end_date   = $this->input->get_post('end_date');

    /*
     * Mobile app WebView mode ကို AJAX request မှတစ်ဆင့် ဆက်ထိန်းထားရန်။
     * Product trace link တွင် ဒီ query ကို ပြန်ပေါင်းပေးထားသောကြောင့်
     * app အပြင် browser အသစ်သို့ ထွက်မသွားတော့ပါ။
     */
    $is_app_mode =
        (int) $this->input->get_post('app') === 1 ||
        (int) $this->input->get_post('mobile') === 1;

    $app_language = trim(
        (string) $this->input->get_post('app_lang', true)
    );

    if ($app_language === '') {
        $app_language = 'myanmar';
    }

    $app_query = $is_app_mode
        ? '?app=1&app_lang=' . rawurlencode($app_language)
        : '';


    $this->db->select("
        p.id AS product_id,
        p.code AS product_code,
        p.name AS product_name,

        SUM(COALESCE(pi.primary_qty, 0))
            AS total_primary_qty,

        SUM(COALESCE(pi.secondary_qty, 0))
            AS total_secondary_qty,

        /*
         * Purchase item မှာ unit ရှိရင် အဲဒါသုံးမယ်။
         * မရှိရင် Product master ရဲ့ base unit ကို fallback သုံးမယ်။
         */
        MAX(
            COALESCE(
                NULLIF(TRIM(piu.name), ''),
                NULLIF(TRIM(pbu.name), '')
            )
        ) AS primary_unit_name,

        /*
         * Secondary unit fallback
         */
        MAX(
            COALESCE(
                NULLIF(TRIM(siu.name), ''),
                NULLIF(TRIM(psu.name), '')
            )
        ) AS secondary_unit_name,

        SUM(COALESCE(pi.subtotal, 0))
            AS total_amount,

        MAX(pur.date)
            AS last_purchase_date
    ", false);


    $this->db->from('tec_purchase_items pi');

    $this->db->join(
        'tec_purchases pur',
        'pur.id = pi.purchase_id',
        'inner'
    );

    $this->db->join(
        'tec_products p',
        'p.id = pi.product_id',
        'inner'
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase item units
    |--------------------------------------------------------------------------
    */
    $this->db->join(
        'tec_product_units piu',
        'piu.id = pi.primary_unit',
        'left'
    );

    $this->db->join(
        'tec_product_units siu',
        'siu.id = pi.secondary_unit',
        'left'
    );


    /*
    |--------------------------------------------------------------------------
    | Product master fallback units
    |--------------------------------------------------------------------------
    */
    $this->db->join(
        'tec_product_units pbu',
        'pbu.id = p.base_unit_id',
        'left'
    );

    $this->db->join(
        'tec_product_units psu',
        'psu.id = p.secondary_unit_id',
        'left'
    );


    /*
     * returned status ရှိမှ filter လုပ်လိုပါက ဒီ condition ဆက်ထားပါ။
     */
    $this->db->where('pur.status !=', 'returned');


    if (!empty($start_date)) {
        $this->db->where(
            'pur.date >=',
            $start_date
        );
    }

    if (!empty($end_date)) {
        $this->db->where(
            'pur.date <=',
            $end_date
        );
    }


    if (!empty($product_id)) {
        $this->db->where(
            'pi.product_id',
            (int) $product_id
        );
    }


    $this->db->group_by([
        'p.id',
        'p.code',
        'p.name'
    ]);

    $this->db->order_by(
        'MAX(pur.date)',
        'DESC',
        false
    );


    $query = $this->db->get();


    /*
    |--------------------------------------------------------------------------
    | Query error log
    |--------------------------------------------------------------------------
    */
    if (!$query) {
        $db_error = $this->db->error();

        log_message(
            'error',
            'get_product_purchase DB ERROR => ' .
            print_r($db_error, true)
        );

        echo json_encode([
            'data'  => [],
            'error' => $db_error['message'] ?? 'Database error'
        ]);

        return;
    }


    $data = [];
    $i = 1;


    foreach ($query->result() as $row) {

        $primary_unit_name = trim(
            (string) $row->primary_unit_name
        );

        $secondary_unit_name = trim(
            (string) $row->secondary_unit_name
        );


        $data[] = [

            'sr_no' => $i++,

            'product_id' =>
                (int) $row->product_id,

            'product_code' =>
                $row->product_code,

            'product_name' =>
                "<a href='" .
                html_escape(
                    site_url(
                    'stocktransfers/trace/' .
                    (int) $row->product_id
                    ) .
                    $app_query
                ) .
                "' class='tip' title='" .
                html_escape(lang('trace')) .
                "'>" .
                html_escape($row->product_name) .
                "</a>",


            /*
             * View JS က ဒီ 4 fields ကို သုံးမယ်။
             */
            'primary_qty' =>
                (float) $row->total_primary_qty,

            'primary_unit_name' =>
                $primary_unit_name,

            'secondary_qty' =>
                (float) $row->total_secondary_qty,

            'secondary_unit_name' =>
                $secondary_unit_name,


            'total_amount' =>
                (float) $row->total_amount,

            'date' =>
                $row->last_purchase_date,
        ];
    }


    echo json_encode(
        [
            'data' => $data
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
}


    public function deletePurchaseReceipt($receipt_id)
    {
        $receipt_id = (int) $receipt_id;

        if ($receipt_id <= 0) {
            return [
                'status' => false,
                'message' => 'Receipt ID မမှန်ပါ။'
            ];
        }

        $receipt = $this->getPurchaseReceiptByID($receipt_id);

        if (!$receipt) {
            return [
                'status' => false,
                'message' => 'Receive history မတွေ့ပါ။'
            ];
        }

        $item = $this->db
            ->where('purchase_id', (int) $receipt->purchase_id)
            ->where('product_id', (int) $receipt->product_id)
            ->get('purchase_items', 1)
            ->row();

        if (!$item) {
            return [
                'status' => false,
                'message' => 'ဆက်စပ် Purchase item မတွေ့ပါ။'
            ];
        }

        /*
        * IMPORTANT:
        * Change this field name if your receipt table uses
        * another quantity column.
        */
        $received_qty = (float) $receipt->quantity;

        if ($received_qty <= 0) {
            return [
                'status' => false,
                'message' => 'လက်ခံအရေအတွက် မမှန်ပါ။'
            ];
        }

        $this->db->trans_begin();

        // ---------------------------------------------------------
        // 1. Reduce received quantity in purchase_items
        // ---------------------------------------------------------

        $new_received_qty =
            (float) $item->received_primary_qty - $received_qty;

        if ($new_received_qty < 0) {
            $new_received_qty = 0;
        }

        $this->db
            ->where('id', (int) $item->id)
            ->update(
                'purchase_items',
                [
                    'received_primary_qty' => $new_received_qty
                ]
            );

        // ---------------------------------------------------------
        // 2. Reverse stock
        // ---------------------------------------------------------

        $this->setStoreQuantity(
            (int) $receipt->purchase_id,
            (int) $receipt->product_id,
            (int) $item->store_id,
            0 - $received_qty
        );

        // ---------------------------------------------------------
        // 3. Delete receipt history
        // ---------------------------------------------------------

        $this->db
            ->where('id', $receipt_id)
            ->delete('purchase_receipts');

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return [
                'status' => false,
                'message' => 'Receive history ဖျက်ရာတွင် အမှားဖြစ်ပါသည်။'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => 'Receive history ဖျက်ပြီးပါပြီ။'
        ];
    }

    public function updatePurchaseReceipt($receipt_id, $new_qty)
    {
        $receipt_id = (int) $receipt_id;
        $new_qty = (float) $new_qty;

        if ($receipt_id <= 0 || $new_qty <= 0) {
            return [
                'status' => false,
                'message' => 'အချက်အလက် မမှန်ပါ။'
            ];
        }

        $receipt = $this->getPurchaseReceiptByID($receipt_id);

        if (!$receipt) {
            return [
                'status' => false,
                'message' => 'Receive history မတွေ့ပါ။'
            ];
        }

        $item = $this->db
            ->where('purchase_id', (int) $receipt->purchase_id)
            ->where('product_id', (int) $receipt->product_id)
            ->get('purchase_items', 1)
            ->row();

        if (!$item) {
            return [
                'status' => false,
                'message' => 'Purchase item မတွေ့ပါ။'
            ];
        }

        $old_qty = (float) $receipt->quantity;

        $difference = $new_qty - $old_qty;

        // Cannot receive more than ordered quantity
        $ordered_qty = (float) $item->primary_qty;

        $current_received = (float) $item->received_primary_qty;

        $new_total_received =
            $current_received + $difference;

        if ($new_total_received < 0) {
            return [
                'status' => false,
                'message' => 'လက်ခံအရေအတွက် 0 ထက်နည်း၍မရပါ။'
            ];
        }

        if ($new_total_received > $ordered_qty) {
            return [
                'status' => false,
                'message' => 'လက်ခံအရေအတွက်သည် မှာယူထားသောအရေအတွက်ထက် မကျော်ရပါ။'
            ];
        }

        $this->db->trans_begin();

        // Update purchase item received qty
        $this->db
            ->where('id', (int) $item->id)
            ->update(
                'purchase_items',
                [
                    'received_primary_qty' => $new_total_received
                ]
            );

        // Update stock by DIFFERENCE
        if ($difference != 0) {
            $this->setStoreQuantity(
                (int) $receipt->purchase_id,
                (int) $receipt->product_id,
                (int) $item->store_id,
                $difference
            );
        }

        // Update receipt
        $this->db
            ->where('id', $receipt_id)
            ->update(
                'purchase_receipts',
                [
                    'quantity' => $new_qty
                ]
            );

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return [
                'status' => false,
                'message' => 'Receive history update မအောင်မြင်ပါ။'
            ];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => 'Receive history ပြင်ဆင်ပြီးပါပြီ။'
        ];
    }

    public function delete_purchase_item()
    {
        $item_id = (int) $this->input->post('item_id');

        if ($item_id <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Purchase item ID မမှန်ပါ။'
            ]);
            return;
        }

        $result = $this->purchases_model->deletePurchaseItem($item_id);

        echo json_encode($result);
    }

    public function receive_history()
    {
        $purchase_id = (int) $this->input->post('purchase_id');
        $product_id  = (int) $this->input->post('product_id');

        if ($purchase_id <= 0 || $product_id <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Invalid parameters.'
            ]);
            return;
        }

        $product = $this->db
            ->select('name')
            ->where('id', $product_id)
            ->get('products', 1)
            ->row();

        $this->data['history'] = $this->purchases_model
            ->getPurchaseReceiveHistory($purchase_id, $product_id);
        $this->data['product_name'] = $product ? $product->name : '-';

        $this->load->view(
            $this->theme . 'purchases/receive_history_modal',
            $this->data
        );
    }

    public function delete_receive_history()
    {
        $receipt_id = (int) $this->input->post('receipt_id');

        if ($receipt_id <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Receipt ID မမှန်ပါ။'
            ]);
            return;
        }

        $result = $this->purchases_model
            ->deletePurchaseReceipt($receipt_id);

        echo json_encode($result);
    }

    public function update_receive_history()
    {
        $receipt_id = (int) $this->input->post('receipt_id');
        $quantity   = (float) $this->input->post('quantity');

        if ($receipt_id <= 0 || $quantity <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'အချက်အလက် မမှန်ပါ။'
            ]);
            return;
        }

        $result = $this->purchases_model
            ->updatePurchaseReceipt(
                $receipt_id,
                $quantity
            );

        echo json_encode($result);
    }
}
