<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Products_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_products($data = [])
    {
        if ($this->db->insert_batch('products', $data)) {
            return true;
        }
        return false;
    }

    public function addProduct($data, $items = [])
    {
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
    
            // Insert combo items (if any)
            if (!empty($items)) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }
    
            return true;
        }
        return false;
    }


    public function deleteProduct($id)
{
    $id = (int) $id;
    $this->delete_error = [];

    if ($id <= 0) {
        $this->delete_error = [
            'code'    => 0,
            'message' => 'Invalid product ID'
        ];

        return false;
    }

    /*
     * COGS Logs ထဲတွင် အသုံးပြုပြီးသားရှိမရှိ ကြိုတင်စစ်ဆေးခြင်း
     *
     * dbprefix = tec_ သတ်မှတ်ထားပါက cogs_logs ဟုပဲရေးပါ။
     */
    $used_in_cogs = $this->db
        ->where('product_id', $id)
        ->count_all_results('cogs_logs');

    if ($used_in_cogs > 0) {
        $this->delete_error = [
            'code'    => 1451,
            'message' => 'Product is already used in COGS logs.'
        ];

        return false;
    }

    $original_db_debug = $this->db->db_debug;
    $this->db->db_debug = false;

    $this->db->trans_begin();

    try {
        // Product Unit Conversion များဖျက်ရန်
        $result = $this->db
            ->where('product_id', $id)
            ->delete('product_unit_conversions');

        if (!$result) {
            $error = $this->db->error();

            throw new Exception(
                !empty($error['message'])
                    ? $error['message']
                    : 'Unable to delete product unit conversions.',
                !empty($error['code']) ? (int) $error['code'] : 0
            );
        }

        // Product Unit Price များဖျက်ရန်
        $result = $this->db
            ->where('product_id', $id)
            ->delete('product_unit_prices');

        if (!$result) {
            $error = $this->db->error();

            throw new Exception(
                !empty($error['message'])
                    ? $error['message']
                    : 'Unable to delete product unit prices.',
                !empty($error['code']) ? (int) $error['code'] : 0
            );
        }

        // Main Product ဖျက်ရန်
        $result = $this->db
            ->where('id', $id)
            ->delete('products');

        if (!$result) {
            $error = $this->db->error();

            throw new Exception(
                !empty($error['message'])
                    ? $error['message']
                    : 'Unable to delete product.',
                !empty($error['code']) ? (int) $error['code'] : 0
            );
        }

        if ($this->db->trans_status() === false) {
            $error = $this->db->error();

            throw new Exception(
                !empty($error['message'])
                    ? $error['message']
                    : 'Product delete transaction failed.',
                !empty($error['code']) ? (int) $error['code'] : 0
            );
        }

        $this->db->trans_commit();
        $this->db->db_debug = $original_db_debug;

        return true;

    } catch (Throwable $e) {
        $this->db->trans_rollback();
        $this->db->db_debug = $original_db_debug;

        $error_code = (int) $e->getCode();

        /*
         * mysqli_sql_exception မှာ MySQL error code 1451 ရနိုင်သည်။
         * Code 0 ဖြစ်နေပါက error message ထဲမှ FK error ကို ထပ်စစ်မည်။
         */
        if (
            $error_code === 1451 ||
            stripos($e->getMessage(), 'foreign key constraint fails') !== false ||
            stripos($e->getMessage(), 'Cannot delete or update a parent row') !== false
        ) {
            $error_code = 1451;
        }

        $this->delete_error = [
            'code'    => $error_code,
            'message' => $e->getMessage()
        ];

        log_message(
            'error',
            'Product delete failed. Product ID: ' . $id .
            ' | Error code: ' . $error_code .
            ' | Error: ' . $e->getMessage()
        );

        return false;
    }
}


    

    public function deleteAdjustment($id)
    {
        // 1️⃣ Get the adjustment first
        $adjustment = $this->db->get_where('product_adjustments', ['id' => $id])->row();
        if (!$adjustment) {
            return false; // Not found
        }

        // 2️⃣ Roll back stock
        $this->rollback_stock_fifo(
            $adjustment->product_id,
            $adjustment->store_id,
            $adjustment->qty_base, // base
            $adjustment->qty_secondary  // secondary
        );

        // 3️⃣ Delete the adjustment
        if ($this->db->delete('product_adjustments', ['id' => $id])) {
            return true;
        }

        return false;
    }

    private function rollback_stock_fifo($product_id, $store_id, $qty_base, $qty_secondary)
{
    $qty_base = (float)$qty_base;
    $qty_secondary = (float)$qty_secondary;

    // Get batches in FIFO order (oldest first)
    $batches = $this->db->order_by('id', 'asc')
                        ->where('product_id', $product_id)
                        ->where('store_id', $store_id)
                        ->get('tec_stock_batches')
                        ->result();

    foreach ($batches as $batch) {

        if ($qty_base <= 0 && $qty_secondary <= 0) break;

        $batch_base = (float)$batch->qty_base;
        $batch_secondary = (float)$batch->qty_secondary;

        // Add back quantities
        $base_add_qty = min($qty_base, PHP_INT_MAX); // just in case
        $second_add_qty = min($qty_secondary, PHP_INT_MAX);

        $new_base = $batch_base + $base_add_qty;
        $new_secondary = $batch_secondary + $second_add_qty;

        $this->db->where('id', $batch->id)->update('tec_stock_batches', [
            'qty_base' => $new_base,
            'qty_secondary' => $new_secondary,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $qty_base -= $base_add_qty;
        $qty_secondary -= $second_add_qty;
    }

    // Optional: if still remaining qty, create a new batch
    if ($qty_base > 0 || $qty_secondary > 0) {
        $this->db->insert('tec_stock_batches', [
            'product_id' => $product_id,
            'store_id' => $store_id,
            'qty_base' => $qty_base,
            'qty_secondary' => $qty_secondary,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}


    

    public function fetch_products($limit, $start = null, $category_id = null)
    {
        $this->db->select('name, code, barcode_symbology, price')
        ->limit($limit, $start)->order_by('code', 'asc');
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getComboItemsByPID($product_id)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name')
        ->join('products', 'products.code=combo_items.item_code', 'left')
        ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 10)
    {
        if ($this->db->dbdriver == 'sqlite3') {
            $this->db->where("type != 'combo' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  (name || ' (' || code || ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("type != 'combo' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStoreQuantity($product_id, $store_id = null)
    {
        if (!$store_id) {
            $store_id = $this->session->userdata('store_id') ? $this->session->userdata('store_id') : 1;
        }
    
        $q = $this->db->select('SUM(qty_base) as qty_base, SUM(qty_primary) as qty_primary, SUM(qty_secondary) as qty_secondary')
                      ->from('stock_batches')
                      ->where('product_id', $product_id)
                      ->where('store_id', $store_id)
                      ->get();
    
        if ($q->num_rows() > 0) {
            return $q->row(); // returns summed quantities
        }
        return false;
    }


    public function getStoresQuantity($product_id)
    {
        $q = $this->db->select('store_id, 
                                SUM(qty_base) as qty_base, 
                                SUM(qty_primary) as qty_primary, 
                                SUM(qty_secondary) as qty_secondary')
                      ->from('stock_batches')
                      ->where('product_id', $product_id)
                      ->group_by('store_id')
                      ->get();
    
        if ($q->num_rows() > 0) {
            return $q->result(); // returns array of rows per store
        }
        return false;
    }


    public function products_count($category_id = null)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
            return $this->db->count_all_results('products');
        }
        return $this->db->count_all('products');
    }

    public function setStoreQuantity($data)
    {
        // Always insert as new batch
        $batchData = [
            'product_id'    => $data['product_id'],
            'store_id'      => $data['store_id'],
            'qty_base'      => $data['qty_base'],
            'qty_primary'   => isset($data['qty_primary']) ? $data['qty_primary'] : 0,
            'qty_secondary' => isset($data['qty_secondary']) ? $data['qty_secondary'] : 0,
            'cost_per_base' => isset($data['price']) ? $data['price'] : 0,
            'batch_no'      => isset($data['batch_no']) ? $data['batch_no'] : 'Manual',
            'date'          => date('Y-m-d'),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s')
        ];
    
        $this->db->insert('stock_batches', $batchData);
    }


    public function updatePrice($data = [])
    {
        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }


public function updateProduct(
    $id,
    $data = [],
    $store_quantities = [],
    $items = [],
    $photo = null,
    $conversions = null
) {
    $id = (int) $id;
    if ($id <= 0) {
        return false;
    }

    $current_product = $this->db
        ->select('id, type, base_unit_id, secondary_unit_id, is_dual_unit')
        ->where('id', $id)
        ->get('products')
        ->row();

    if (!$current_product) {
        return false;
    }

    if ($photo) {
        $data['image'] = $photo;
    }

    $base_unit_id = (int) (
        $data['base_unit_id'] ??
        $current_product->base_unit_id
    );
    $is_dual_unit = array_key_exists('is_dual_unit', $data)
        ? (int) $data['is_dual_unit'] === 1
        : (int) $current_product->is_dual_unit === 1;
    $secondary_unit_id = $is_dual_unit
        ? (int) ($data['secondary_unit_id'] ?? $current_product->secondary_unit_id)
        : null;

    if (
        $base_unit_id <= 0 ||
        (
            $is_dual_unit &&
            (
                $secondary_unit_id <= 0 ||
                $secondary_unit_id === $base_unit_id
            )
        )
    ) {
        log_message(
            'error',
            '[PRODUCT UPDATE] Invalid Dual Unit configuration. Product ID: ' . $id
        );
        return false;
    }

    $data['base_unit_id'] = $base_unit_id;
    $data['is_dual_unit'] = $is_dual_unit ? 1 : 0;
    $data['secondary_unit_id'] = $secondary_unit_id ?: null;

    /*
     * null means the caller did not submit conversion editing, so preserve the
     * existing rows. An empty array means the caller intentionally removed all.
     */
    $clean_conversions = null;
    if ($conversions !== null) {
        $clean_conversions = [];

        foreach ((array) $conversions as $conversion) {
            $unit_id = (int) ($conversion['unit_id'] ?? 0);
            $operator = trim((string) ($conversion['operator'] ?? ''));
            $operation_value = (float) ($conversion['operation_value'] ?? 0);

            if (
                $unit_id <= 0 ||
                $unit_id === $base_unit_id ||
                !in_array($operator, ['*', '/'], true) ||
                $operation_value <= 0 ||
                ($is_dual_unit && $unit_id === $secondary_unit_id)
            ) {
                continue;
            }

            $clean_conversions[$unit_id] = [
                'unit_id' => $unit_id,
                'operator' => $operator,
                'operation_value' => $operation_value,
            ];
        }

        $clean_conversions = array_values($clean_conversions);
        $data['has_unit_conversion'] = !empty($clean_conversions) ? 1 : 0;
    }

    $allowed_fields = [
        'type', 'name', 'code', 'barcode_symbology', 'category_id',
        'cost', 'price', 'tax', 'tax_method',
        'alert_quantity', 'details', 'image', 'brand_id',
        'has_unit_conversion', 'is_dual_unit', 'base_unit_id',
        'secondary_unit_id',
    ];
    $data = array_intersect_key($data, array_flip($allowed_fields));

    $this->db->trans_begin();

    if (!$this->db->update('products', $data, ['id' => $id])) {
        $this->db->trans_rollback();
        return false;
    }

    /* Changing away from combo must also remove the old combo rows. */
    if (array_key_exists('type', $data)) {
        $this->db->delete('combo_items', ['product_id' => $id]);

        if ($data['type'] === 'combo') {
            foreach ((array) $items as $item) {
                if (empty($item['item_code']) || !is_numeric($item['quantity'])) {
                    continue;
                }

                $item['product_id'] = $id;
                if (!$this->db->insert('combo_items', $item)) {
                    $this->db->trans_rollback();
                    return false;
                }
            }
        }
    }

    if ($clean_conversions !== null) {
        $this->db->delete('product_unit_conversions', ['product_id' => $id]);

        foreach ($clean_conversions as $conversion) {
            $conversion['product_id'] = $id;
            if (!$this->db->insert('product_unit_conversions', $conversion)) {
                $this->db->trans_rollback();
                return false;
            }
        }
    }

    /*
     * The minimal Edit page does not post unit-price rows. Preserve existing
     * prices in that case; replace them only when the fields were submitted.
     */
    $unit_price_unit_ids = $this->input->post('unit_price_unit_id');
    $unit_price_values = $this->input->post('unit_price_value');

    if ($unit_price_unit_ids !== null || $unit_price_values !== null) {
        if (!is_array($unit_price_unit_ids) || !is_array($unit_price_values)) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->delete('product_unit_prices', ['product_id' => $id]);
        $price_count = min(count($unit_price_unit_ids), count($unit_price_values));

        for ($i = 0; $i < $price_count; $i++) {
            $unit_id = (int) $unit_price_unit_ids[$i];
            $price = $unit_price_values[$i];

            if ($unit_id <= 0 || !is_numeric($price) || (float) $price < 0) {
                continue;
            }

            if (!$this->db->insert('product_unit_prices', [
                'product_id' => $id,
                'unit_id' => $unit_id,
                'price' => (float) $price,
            ])) {
                $this->db->trans_rollback();
                return false;
            }
        }
    }

    foreach ((array) $store_quantities as $store_qty) {
        if (empty($store_qty['store_id'])) {
            continue;
        }

        $stock_updated = $this->updateStoreStock(
            $id,
            (int) $store_qty['store_id'],
            $store_qty['quantity'] ?? 0,
            $store_qty['price'] ?? 0
        );

        if ($stock_updated === false) {
            $this->db->trans_rollback();
            return false;
        }
    }

    if ($this->db->trans_status() === false) {
        $this->db->trans_rollback();
        return false;
    }

    $this->db->trans_commit();
    return true;
}




/**
 * Update store stock safely using stock_batches & stock_movements
 */
private function updateStoreStock($product_id, $store_id, $new_qty, $price)
{
    // Get all batches for this product in this store
    $batches = $this->db->get_where('stock_batches', ['product_id' => $product_id, 'store_id' => $store_id])->result();
    $total_existing_qty = array_sum(array_column($batches, 'quantity'));

    $diff = $new_qty - $total_existing_qty;
    if ($diff == 0) return; // No change

    // Insert stock movement
    $movement_type = $diff > 0 ? 'adjustment_in' : 'adjustment_out';
    $this->db->insert('stock_movements', [
        'product_id' => $product_id,
        'store_id'   => $store_id,
        'quantity'   => $diff,
        'price'      => $price,
        'type'       => $movement_type,
        'date'       => date('Y-m-d H:i:s')
    ]);

    // Adjust batch: if increase, create new batch; if decrease, deduct FIFO
    if ($diff > 0) {
        $this->db->insert('stock_batches', [
            'product_id' => $product_id,
            'store_id'   => $store_id,
            'quantity'   => $diff,
            'price'      => $price,
            'date'       => date('Y-m-d H:i:s')
        ]);
    } else {
        $this->deductFromBatches($product_id, $store_id, abs($diff));
    }
}

/**
 * Deduct quantity from stock batches using FIFO
 */
private function deductFromBatches($product_id, $store_id, $qty)
{
    $batches = $this->db->order_by('date', 'ASC')
                        ->get_where('stock_batches', ['product_id' => $product_id, 'store_id' => $store_id])
                        ->result();

    foreach ($batches as $batch) {
        if ($qty <= 0) break;

        if ($batch->quantity <= $qty) {
            $qty -= $batch->quantity;
            $this->db->delete('stock_batches', ['id' => $batch->id]);
        } else {
            $new_qty = $batch->quantity - $qty;
            $this->db->update('stock_batches', ['quantity' => $new_qty], ['id' => $batch->id]);
            $qty = 0;
        }
    }
}



    
    
    public function get_exchange_rate($currency_code)
    {
        $this->db->where('currency_code', $currency_code);
        $query = $this->db->get('tec_currencies');
        return $query->row();
    }

    public function addCategory($data)
    {
        if ($this->db->insert('expensetype', $data)) {
            return true;
        }
        return false;
    }

    public function getProductBatches($product_id)
    {
        $this->db->select('
            sb.*,
            p.id AS purchase_id,
            s.name AS supplier_name
        ');
        $this->db->from('stock_batches sb');
        $this->db->join('tec_purchases p', 'p.id = sb.purchase_id', 'left');
        $this->db->join('tec_suppliers s', 's.id = p.supplier_id', 'left');
        $this->db->where('sb.product_id', $product_id);
    
        // ✅ Only batches linked to a purchase
        $this->db->where('sb.purchase_id IS NOT NULL', null, false);
    
        $this->db->order_by('sb.id', 'desc');
    
        return $this->db->get()->result();
    }



    public function getAllUnits()
    {
        $q = $this->db->get('product_units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUnitByID($id)
    {
        $q = $this->db->get_where('product_units', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

   
    
    public function insertUnit($data)
{
    if (empty($data['name'])) {
        return false;
    }

    // Name duplicate check
    $name_exists = $this->db
        ->where('name', $data['name'])
        ->count_all_results('tec_product_units');

    if ($name_exists > 0) {
        return false;
    }

    // Code duplicate check
    if (!empty($data['code'])) {
        $code_exists = $this->db
            ->where('code', $data['code'])
            ->count_all_results('tec_product_units');

        if ($code_exists > 0) {
            return false;
        }
    }

    return $this->db->insert('tec_product_units', $data);
}

    public function updateUnit($id, $data) {
        return $this->db->where('id', $id)->update('tec_product_units', $data);
    }

    public function getProductConversions($product_id)
{
    return $this->db->where('product_id', $product_id)
                    ->get('tec_product_unit_conversions')
                    ->result();
}
    

// Get single product by ID
    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

public function save_daily_closing_balance($date = null) {
    
        $date = $date ?? date('Y-m-d');

        // Get all stores
        $stores = $this->db->select('id')->from('tec_stores')->get()->result();

        foreach($stores as $store) {
            $store_id = $store->id;

            // Get closing stock per product for this store
            $this->db->select('product_id, SUM(qty_base) as qty_base, SUM(qty_secondary) as qty_secondary');
            $this->db->from('tec_stock_batches');
            $this->db->where('store_id', $store_id);
            $this->db->group_by('product_id');
            $products = $this->db->get()->result();

            foreach($products as $p) {
                // Check if today's closing already exists
                $exists = $this->db->get_where('daily_product_closing', [
                    'store_id' => $store_id,
                    'product_id' => $p->product_id,
                    'date' => $date
                ])->row();

                $data = [
                    'store_id' => $store_id,
                    'product_id' => $p->product_id,
                    'date' => $date,
                    'qty_base' => $p->qty_base,
                    'qty_secondary' => $p->qty_secondary
                ];

                if($exists) {
                    // Update existing record
                    $this->db->update('daily_product_closing', $data, ['id' => $exists->id]);
                } else {
                    // Insert new record
                    $this->db->insert('daily_product_closing', $data);
                }
            }
        }

        return true;
    }

public function deleteUnit($id)
{
    if (!$id) {
        return false;
    }

    // Unit ကို product ထဲမှာ အသုံးပြုထားရင် မဖျက်ပါနဲ့
    $used = $this->db
        ->where('unit', $id)
        ->or_where('base_unit_id', $id)
        ->or_where('secondary_unit_id', $id)
        ->count_all_results('tec_products');

    if ($used > 0) {
        return false;
    }

    return $this->db->delete('tec_product_units', ['id' => $id]);
}


public function getProductUnitConversions($product_id)
{
    return $this->db
        ->select('
            puc.id,
            puc.product_id,
            puc.unit_id,
            puc.operator,
            puc.operation_value,
            u.name AS unit_name,
            u.code AS unit_code
        ')
        ->from('product_unit_conversions puc')
        ->join('product_units u', 'u.id = puc.unit_id', 'left')
        ->where('puc.product_id', $product_id)
        ->order_by('puc.operation_value', 'ASC')
        ->get()
        ->result();
}


public function getProductUnitPrices($product_id)
{
    return $this->db
        ->select('
            pup.id,
            pup.product_id,
            pup.unit_id,
            pup.price,
            u.name AS unit_name,
            u.code AS unit_code
        ')
        ->from('product_unit_prices pup')
        ->join('product_units u', 'u.id = pup.unit_id', 'left')
        ->where('pup.product_id', $product_id)
        ->get()
        ->result();
}


public function getUserByID($user_id)
{
    if (!$user_id) {
        return null;
    }

    return $this->db
        ->where('id', $user_id)
        ->get('users')
        ->row();
}

public function getProductPurchaseBatches($product_id)
{
    return $this->db
        ->select('
            sb.id,
            sb.product_id,
            sb.store_id,
            sb.batch_no,
            sb.expiry_date,
            sb.qty_base,
            sb.qty_secondary,
            sb.cost_per_base,
            sb.cost_per_second,
            sb.secondary_unit_id,
            sb.qty_primary,
            sb.primary_unit_id,
            sb.date,
            sb.delivery,
            sb.purchase_id,
            sb.unit_convert,
            u.name AS primary_unit_name,
            su.name AS secondary_unit_name
        ')
        ->from('stock_batches sb')
        ->join('product_units u', 'u.id = sb.primary_unit_id', 'left')
        ->join('product_units su', 'su.id = sb.secondary_unit_id', 'left')
        ->where('sb.product_id', $product_id)
        ->order_by('sb.date', 'DESC')
        ->get()
        ->result();
}

public function getProductCurrentStock($product_id, $store_id = null)
{
    $this->db
        ->select_sum('qty_base', 'current_stock')
        ->where('product_id', $product_id);

    if (!empty($store_id)) {
        $this->db->where('store_id', $store_id);
    }

    $row = $this->db
        ->get('stock_batches')
        ->row();

    return $row ? (float) $row->current_stock : 0;
}

}
