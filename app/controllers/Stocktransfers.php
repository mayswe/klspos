<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Stocktransfers extends MY_Controller {

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('Stocktransfer_model');
        $this->load->model('purchases_model');
    }

    public function index()
{
    if (!$this->loggedIn) {
        redirect('login');
    }

    $this->data['page_title'] = lang('stock_transfers');
    $bc = [['link' => '#', 'page' => lang('stock_transfers')]];
    $meta = ['page_title' => lang('stock_transfers'), 'bc' => $bc];
    $this->page_construct('stocktransfers/index', $this->data, $meta);
}


    public function add()
{
    $this->form_validation->set_rules('product_id[]', lang('product'), 'required');
    $this->form_validation->set_rules('from_location[]', lang('from_location'), 'required');
    $this->form_validation->set_rules('to_location[]', lang('to_location'), 'required');
    $this->form_validation->set_rules('primary_qty[]', lang('primary_qty'), 'required');
    log_message('debug', 'POST DATA: ' . print_r($_POST, true));

    if ($this->form_validation->run() == true) {

        $product_ids     = $this->input->post('product_id');
        $from_store_ids  = $this->input->post('from_location');
        $to_store_ids    = $this->input->post('to_location');
        $primary_qtys    = $this->input->post('primary_qty');
        $secondary_qtys  = $this->input->post('secondary_qty');
        $date            = $this->input->post('date');

        $reference = 'TRF' . date('YmdHis');
        $success = true;

        foreach ($product_ids as $i => $product_id) {
            if (!$product_id || (!$primary_qtys[$i] && !$secondary_qtys[$i])) {
                continue;
            }

            $item = [
                'product_id'     => $product_id,
                'primary_qty'    => $primary_qtys[$i],
                'secondary_qty'  => $secondary_qtys[$i] ?: 0
            ];

            $from_store_id = $from_store_ids[$i];
            $to_store_id   = $to_store_ids[$i];

            if (!$this->Stocktransfer_model->transfer_stock($from_store_id, $to_store_id, [$item], $reference, $date)) {
                $success = false;
            }
        }

        if ($success) {
            $this->session->set_flashdata('message', lang('stock_transfer_success'));
            redirect($this->mobilePageUrl('stocktransfers'));
        } else {
            $this->session->set_flashdata('error', lang('something_went_wrong'));
            redirect('stocktransfers/add');
        }

    } else {
        $this->data['error']   = validation_errors() ?: $this->session->flashdata('error');
        $this->data['stores']  = $this->site->getAllStores();
        $this->data['products']= $this->site->getAllProducts();
        $this->data['page_title'] = lang('add_stock_transfer');
        $meta = ['page_title' => lang('add_stock_transfer')];
        $this->page_construct('stocktransfers/add', $this->data, $meta);
    }
}


public function get_transfers()
{
    $this->load->library('datatables');

    $this->datatables->select("
        tec_stock_movements.id,
        tec_products.code as product_code,
        tec_products.name as product_name,
        sfrom.name as from_store,
        sto.name as to_store,
        tec_stock_movements.movement_type,
        tec_stock_movements.qty_base,
        tec_stock_movements.qty_secondary,
        tec_stock_movements.qty_primary,
        tec_stock_movements.created_at
    ");

    $this->datatables->from('tec_stock_movements');

    // joins
    $this->datatables->join('tec_transfers', 'tec_transfers.id = tec_stock_movements.transfer_id', 'left');
    $this->datatables->join('tec_products', 'tec_products.id = tec_stock_movements.product_id', 'left');
    $this->datatables->join('tec_stores sfrom', 'sfrom.id = tec_stock_movements.from_store_id', 'left');
    $this->datatables->join('tec_stores sto', 'sto.id = tec_stock_movements.to_store_id', 'left');
    $this->datatables->join('tec_stock_batches', 'tec_stock_batches.id = tec_stock_movements.batch_id', 'left');

    // group by transfer_id
    $this->datatables->group_by('tec_stock_movements.transfer_id');

    // only transfers
    $this->datatables->where('tec_stock_movements.movement_type', 'transfer');
    $this->datatables->where('tec_stock_movements.qty_base >', 0);

    // actions column
    $this->datatables->add_column(
        'Actions',
        "<div class='text-center'>
            <div class='btn-group'>
                <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                    <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
                </button>
                <ul class='dropdown-menu dropdown-menu-right'>
                   <li>
                        <a class='tip' title='" . $this->lang->line('edit') . "' href='" . site_url('stocktransfers/edit/$1') . "'>
                            <i class='fa fa-edit'></i> " . $this->lang->line('edit') . "
                        </a>
                    </li>
                    <li>
                        <a href='" . site_url('stocktransfers/delete/$1') . "' 
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

    $this->datatables->unset_column('id');
    echo $this->datatables->generate();
}





public function view($id = null)
{
    if (!$id) {
        $this->session->set_flashdata('error', lang('invalid_transfer_id'));
        redirect($this->mobilePageUrl('stocktransfers'));
    }

    $this->data['transfer'] = $this->Stocktransfer_model->getTransferByID($id);
    if (!$this->data['transfer']) {
        $this->session->set_flashdata('error', lang('transfer_not_found'));
        redirect($this->mobilePageUrl('stocktransfers'));
    }

    $this->data['items'] = $this->Stocktransfer_model->getTransferItems($id);

    $this->data['page_title'] = lang('view_transfer');
    $bc = [
        ['link' => site_url('stocktransfers'), 'page' => lang('stock_transfers')],
        ['link' => '#', 'page' => lang('view_transfer')]
    ];
    $meta = ['page_title' => lang('view_transfer'), 'bc' => $bc];
    $this->page_construct('stocktransfers/view', $this->data, $meta);
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
                'row'    => $row,
                'units' => $units  // 👈 add this line
            ];
        }
        echo json_encode($pr);
    } else {
        echo json_encode([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
    }
}

public function delete($id = null)
{
    if (DEMO) {
        $this->session->set_flashdata('error', lang('disabled_in_demo'));
        redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
    }

    if ($this->input->get('id')) {
        $id = $this->input->get('id');
    }

    $this->db->trans_start();

    // 1️⃣ Find the transfer batch (must be a destination "Transfer" batch)
    $transfer_batch = $this->db->get_where('tec_stock_batches', [
        'id'       => $id,
        'batch_no' => 'Transfer'
    ])->row();

    if ($transfer_batch) {
        $product_id  = $transfer_batch->product_id;
        $qty_base    = $transfer_batch->qty_base;
        $to_store_id = $transfer_batch->store_id;

        // 2️⃣ Find the IN movement (this batch’s log)
        $in_movement = $this->db->get_where('tec_stock_movements', [
            'batch_id'      => $transfer_batch->id,
            'product_id'    => $product_id,
            'to_store_id'   => $to_store_id,
            'movement_type' => 'transfer',
            'qty_base >'    => 0
        ])->row();

        if ($in_movement) {
            $from_store_id = $in_movement->from_store_id;

            // 3️⃣ Find the OUT movement (source batch log)
            $out_movement = $this->db->get_where('tec_stock_movements', [
                'product_id'    => $product_id,
                'from_store_id' => $from_store_id,
                'to_store_id'   => $to_store_id,
                'movement_type' => 'transfer',
                'qty_base <'    => 0
            ])->row();

            if ($out_movement) {
                // 4️⃣ Restore stock to the original source batch
                $this->db->where('id', $out_movement->batch_id)
                         ->set('qty_base', "qty_base + {$qty_base}", FALSE)
                         ->update('tec_stock_batches');

                // Delete OUT movement log
                $this->db->delete('tec_stock_movements', ['id' => $out_movement->id]);
            }

            // Delete IN movement log
            $this->db->delete('tec_stock_movements', ['id' => $in_movement->id]);
        }

        // 5️⃣ Delete the destination transfer batch
        $this->db->delete('tec_stock_batches', ['id' => $transfer_batch->id]);
    }

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
        $this->session->set_flashdata('error', lang('delete_failed'));
    } else {
        $this->session->set_flashdata('message', lang('stock_deleted'));
    }

    redirect($this->mobilePageUrl('stocktransfers'));
}

public function get_stock_qty()
{
    if ($this->input->is_ajax_request()) {
        $product_id = $this->input->post('product_id');
        $store_id   = $this->input->post('store_id');

        $qty = $this->site->getProductStock($product_id, $store_id); 
        // ^ you likely already have such a method (if not, I can help write it)

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['qty' => $qty]));
    }
}

public function trace($product_id = null)
{
    if (!$this->loggedIn) {
        redirect('login');
    }

    if (!$product_id) {
        show_error("Product ID required");
    }

    $this->load->model('products_model');

    $this->data['product'] = $this->products_model->getProductByID($product_id);
    $this->data['product_id'] = $product_id; // ✅ Pass ID to view
    
    // Get the conversion for this product's secondary unit
    $conversion = $this->db
        ->select('operation_value')
        ->from('tec_product_unit_conversions')
        ->where('product_id', $product_id)
        ->where('operation_value >', 1) // 2 = secondary unit
        ->get()
        ->row();
    
    $this->data['small_units_per_base'] = $conversion ? floatval($conversion->operation_value) : 1;
    
     $this->data['sh_balance'] = $this->db
    ->select('SUM(qty_base) as total_base, SUM(qty_secondary) as total_secondary, SUM(qty_primary) as total_primary')
    ->from('stock_batches')
    ->where('product_id', $product_id)
    ->where('store_id', 1)
    ->get()
    ->row();
    
    $this->data['wh_balance'] = $this->db
    ->select('SUM(qty_base) as total_base, SUM(qty_secondary) as total_secondary, SUM(qty_primary) as total_primary')
    ->from('stock_batches')
    ->where('product_id', $product_id)
    ->where('store_id', 2)
    ->get()
    ->row();
    
    // ✅ Get opening stock (date + qty) with date filter
    $opening = $this->db
        ->select('created_at, qty_base, qty_secondary')
        ->from('stock_movements')
        ->where('product_id', $product_id)
        ->where('movement_type', 'Opening')
        ->where('created_at >=', '2026-02-06 00:00:00')
        ->order_by('created_at', 'DESC')
        ->limit(1)
        ->get()
        ->row();
    
    $this->data['opening_date'] = $opening ? $opening->created_at : null;
    $this->data['opening_bqty'] = $opening ? $opening->qty_base : null;
    $this->data['opening_sqty'] = $opening ? $opening->qty_secondary : null;


    $this->data['page_title'] = lang('stock_transfers');
    $bc   = [['link' => '#', 'page' => lang('stock_transfers')]];
    $meta = ['page_title' => lang('stock_transfers'), 'bc' => $bc];
    $this->page_construct('stocktransfers/trace', $this->data, $meta);
}


public function get_product_trace($product_id)
{
    $product_id = (int) $product_id;

    $sql = "
        (
            -- 🔁 Stock Movements (EXCLUDE purchase)
            SELECT 
                sm.created_at AS date,
                sm.movement_type,
                sfrom.name AS from_store,
                sto.name AS to_store,
                sm.qty_base,
                sm.qty_secondary,
                NULL AS sale_id,
                '-' AS party_name
            FROM tec_stock_movements sm
            LEFT JOIN tec_stores sfrom ON sfrom.id = sm.from_store_id
            LEFT JOIN tec_stores sto   ON sto.id   = sm.to_store_id
            WHERE sm.product_id = {$product_id}
              AND sm.movement_type != 'purchase'
        )

        UNION ALL

        (
            -- 🛒 Sales / Preorders
            SELECT
                s.date AS date,
                CASE 
                    WHEN s.is_preorder = 1 THEN 'preorder'
                    ELSE 'sale'
                END AS movement_type,
                NULL AS from_store,
                NULL AS to_store,
                SUM(
                    CASE 
                        WHEN s.is_preorder = 1 THEN si.preorder_qty
                        ELSE si.quantity
                    END
                ) AS qty_base,
                NULL AS qty_secondary,
                s.id AS sale_id,
                c.name AS party_name
            FROM tec_sales s
            JOIN tec_sale_items si ON si.sale_id = s.id
            LEFT JOIN tec_customers c ON c.id = s.customer_id
            WHERE si.product_id = {$product_id}
            GROUP BY s.id, s.date, s.is_preorder, c.name
        )

        UNION ALL

        (
            -- 🧾 Purchases (SINGLE source of truth)
            SELECT
                p.date AS date,
                'purchase' AS movement_type,
                NULL AS from_store,
                NULL AS to_store,
                SUM(pi.primary_qty) AS qty_base,
                NULL AS qty_secondary,
                p.id AS sale_id,
                sup.name AS party_name
            FROM tec_purchases p
            JOIN tec_purchase_items pi ON pi.purchase_id = p.id
            LEFT JOIN tec_suppliers sup ON sup.id = p.supplier_id
            WHERE pi.product_id = {$product_id}
            GROUP BY p.id, p.date, sup.name
            HAVING SUM(pi.primary_qty) > 0
        )

        ORDER BY date DESC
    ";

    $data = $this->db->query($sql)->result_array();

    echo json_encode([
        "draw"            => intval($this->input->get("draw")),
        "recordsTotal"    => count($data),
        "recordsFiltered" => count($data),
        "data"            => $data
    ]);
}










}
