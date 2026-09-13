<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index()
    {
        $search = $this->input->get('search');
        $limit = $this->input->get('limit') ?? 20;
        $page = $this->input->get('page') ?? 1;
        $offset = ($page - 1) * $limit;

        // ✅ Changed COALESCE so the actual unit price maps directly to "price"
        $this->db->select("
            p.id,
            p.name,
            p.image,
            p.code,
            p.category_id,
            COALESCE(pup1.price, p.price) as price, 
            p.cost,           
            p.base_unit_id,
            p.secondary_unit_id,
            p.is_dual_unit,
            u1.name as base_unit_name,       
            u2.name as secondary_unit_name,  
            pup2.price as secondary_unit_price,
            IFNULL(SUM(sb.qty_base), 0) as qty_base,
            IFNULL(SUM(sb.qty_secondary), 0) as qty_secondary
        ");
        $this->db->from('products p');
        $this->db->join('tec_stock_batches sb', 'sb.product_id = p.id AND (sb.qty_base > 0 OR sb.qty_secondary > 0)', 'left');
        $this->db->join('tec_product_units u1', 'u1.id = p.base_unit_id', 'left');
        $this->db->join('tec_product_units u2', 'u2.id = p.secondary_unit_id', 'left');

        // JOIN to get the base unit price 
        $this->db->join('tec_product_unit_prices pup1', 'pup1.product_id = p.id AND pup1.unit_id = p.base_unit_id', 'left');

        // JOIN to get the secondary unit price 
        $this->db->join('tec_product_unit_prices pup2', 'pup2.product_id = p.id AND pup2.unit_id = p.secondary_unit_id', 'left');

        $this->db->where('p.type', 'standard');

        if ($search) {
            $this->db->group_start()
                ->like('p.name', $search)
                ->or_like('p.code', $search)
                ->group_end();
        }

        $this->db->group_by('p.id');
        $this->db->limit($limit, $offset);

        $products = $this->db->get()->result();
        $this->attachUnitOptions($products);

        return $this->response(true, "Product list", $products);
    }

    /**
     * Return every selectable unit configured for each product.
     *
     * The products table stores only the base and secondary unit IDs. Any
     * additional units are stored in product_unit_conversions, with prices in
     * product_unit_prices, so the mobile POS must receive the combined list.
     */
    private function attachUnitOptions(&$products)
    {
        if (empty($products)) {
            return;
        }

        $product_ids = [];
        foreach ($products as $product) {
            $product_ids[] = (int) $product->id;
        }
        $product_ids = array_values(array_unique(array_filter($product_ids)));

        if (empty($product_ids)) {
            return;
        }

        $options_by_product = [];
        $add_option = function ($product_id, $unit_id, $name, $price = null, $operator = null, $operation_value = null) use (&$options_by_product) {
            $product_id = (int) $product_id;
            $unit_id = (int) $unit_id;
            $name = trim((string) $name);

            if ($product_id <= 0 || $unit_id <= 0 || $name === '') {
                return;
            }

            if (!isset($options_by_product[$product_id])) {
                $options_by_product[$product_id] = [];
            }

            foreach ($options_by_product[$product_id] as &$option) {
                if ((int) $option['id'] !== $unit_id) {
                    continue;
                }

                if (($option['price'] === null || (float) $option['price'] === 0.0) && $price !== null && $price !== '') {
                    $option['price'] = (float) $price;
                }
                if ($option['operator'] === null && $operator !== null) {
                    $option['operator'] = $operator;
                }
                if ($option['operation_value'] === null && $operation_value !== null) {
                    $option['operation_value'] = (float) $operation_value;
                }
                unset($option);
                return;
            }
            unset($option);

            $options_by_product[$product_id][] = [
                'id' => $unit_id,
                'unit_id' => $unit_id,
                'name' => $name,
                'unit_name' => $name,
                'price' => ($price === null || $price === '') ? null : (float) $price,
                'operator' => $operator,
                'operation_value' => $operation_value === null ? null : (float) $operation_value,
            ];
        };

        // Base units are always first in the list.
        foreach ($products as $product) {
            $add_option($product->id, $product->base_unit_id, $product->base_unit_name, $product->price, '*', 1);
        }

        // Conversion rows contain every additional unit, including products
        // with three or more selectable units.
        $conversion_rows = $this->db
            ->select('puc.product_id, puc.unit_id, puc.operator, puc.operation_value, u.name as unit_name')
            ->from('tec_product_unit_conversions puc')
            ->join('tec_product_units u', 'u.id = puc.unit_id', 'left')
            ->where_in('puc.product_id', $product_ids)
            ->order_by('puc.product_id', 'ASC')
            ->order_by('puc.operation_value', 'ASC')
            ->order_by('puc.id', 'ASC')
            ->get()
            ->result();

        foreach ($conversion_rows as $row) {
            $add_option(
                $row->product_id,
                $row->unit_id,
                $row->unit_name,
                null,
                $row->operator,
                $row->operation_value
            );
        }

        // Add prices to the conversion rows and include any priced unit that
        // does not have a conversion row yet.
        $price_rows = $this->db
            ->select('pup.product_id, pup.unit_id, pup.price, u.name as unit_name')
            ->from('tec_product_unit_prices pup')
            ->join('tec_product_units u', 'u.id = pup.unit_id', 'left')
            ->where_in('pup.product_id', $product_ids)
            ->order_by('pup.product_id', 'ASC')
            ->order_by('pup.id', 'ASC')
            ->get()
            ->result();

        foreach ($price_rows as $row) {
            $add_option($row->product_id, $row->unit_id, $row->unit_name, $row->price);
        }

        foreach ($products as $product) {
            $options = $options_by_product[(int) $product->id] ?? [];

            // Keep the product's secondary unit available even if older data
            // has no conversion or price row for it.
            $secondary_exists = false;
            foreach ($options as $option) {
                if ((int) $option['id'] === (int) $product->secondary_unit_id) {
                    $secondary_exists = true;
                    break;
                }
            }
            if (!$secondary_exists) {
                $add_option($product->id, $product->secondary_unit_id, $product->secondary_unit_name, $product->secondary_unit_price);
            }

            $product->unit_options = $options_by_product[(int) $product->id] ?? [];
        }
    }

    public function show($id)
    {
        $product = $this->db
            ->where('id', $id)
            ->where('tenant_id', $this->user->tenant_id)
            ->get('products')
            ->row();

        if (!$product) {
            return $this->response(false, "Product not found");
        }

        // Fetch unit prices
        $unit_prices = $this->db->where('product_id', $id)->get('tec_product_unit_prices')->result();
        $unit_price_data = [];
        foreach ($unit_prices as $up) {
            $unit_price_data[$up->unit_id] = (float) $up->price;
        }
        $product->unit_prices = $unit_price_data;

        // ✅ OVERRIDE the main price with the base unit price if it exists
        if (isset($unit_price_data[$product->base_unit_id])) {
            $product->price = $unit_price_data[$product->base_unit_id];
        }

        // Fetch unit conversions
        $unit_conversions = $this->db->where('product_id', $id)->get('tec_product_unit_conversions')->result();
        $unit_conversion_data = [];
        if ($unit_conversions) {
            foreach ($unit_conversions as $uc) {
                $unit_conversion_data[$uc->unit_id] = [
                    'operator' => $uc->operator,
                    'value' => (float) $uc->operation_value
                ];
            }
        }
        $product->unit_conversions = $unit_conversion_data;

        // Fetch stock
        $stock = $this->db
            ->select_sum('quantity')
            ->where('product_id', $id)
            ->get('stock_batches')
            ->row()
            ->quantity ?? 0;

        $product->stock = $stock;

        return $this->response(true, "Product detail", $product);
    }

    public function batches($product_id)
    {
        $batches = $this->db
            ->where('product_id', $product_id)
            ->where('quantity >', 0)
            ->order_by('created_at', 'ASC')
            ->get('stock_batches')
            ->result();

        return $this->response(true, "Available batches", $batches);
    }

    public function getProductPurchaseBatches($product_id = null)
    {
        if (!$product_id) {
            $product_id = $this->input->get('product_id'); // or post()
        }

        if (!$product_id) {
            echo json_encode([
                'status' => false,
                'message' => 'Product ID is required'
            ]);
            return;
        }

        $this->db->select('
            p.id AS id,
            p.date AS date,
            s.name AS supplier_name,
            pi.primary_qty AS qty_base,
            pi.secondary_qty AS qty_secondary,
            pi.net_unit_cost AS cost_per_base
        ');

        $this->db->from('tec_purchases p');
        $this->db->join('tec_purchase_items pi', 'pi.purchase_id = p.id', 'inner');
        $this->db->join('tec_suppliers s', 's.id = p.supplier_id', 'left');

        $this->db->where('pi.product_id', $product_id);

        $this->db->order_by('p.date', 'desc');
        $this->db->limit(10);

        $data = $this->db->get()->result();

        echo json_encode([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getProductStock($product_id = null)
    {
        if (!$product_id) {
            $product_id = $this->input->get('product_id');
        }

        if (!$product_id) {
            echo json_encode([
                'status' => false,
                'message' => 'Product ID is required'
            ]);
            return;
        }

        $this->db->select('
            sb.store_id,
            st.name AS store_name,
            SUM(sb.qty_base) AS total_qty_base,
            SUM(sb.qty_secondary) AS total_qty_secondary,
            cost_per_base,
            cost_per_second,
            primary_unit_id, 
            secondary_unit_id
        ');

        $this->db->from('tec_stock_batches sb');

        $this->db->join('tec_stores st', 'st.id = sb.store_id', 'left');

        $this->db->where('sb.product_id', $product_id);

        $this->db->group_by('sb.store_id');

        $this->db->order_by('sb.store_id', 'ASC');

        $data = $this->db->get()->result();

        echo json_encode([
            'status' => true,
            'data' => $data
        ]);
    }
}
