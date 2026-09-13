<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Shippings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addShipping($data, $items)
    {
        if ($this->db->insert('shippings', $data)) {
            $shipping_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['shipping_id'] = $shipping_id;
                if ($this->db->insert('shipping_items', $item)) {
                    if ($data['received']) {
                        $this->setStoreQuantity($item['product_id'], $data['store_id'], $item['quantity']);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function deleteShipping($id)
    {
        $shipping = $this->getShippingByID($id);
        if ($shipping->received) {
            $oitems = $this->getAllShippingItems($id);
            foreach ($oitems as $oitem) {
                if ($product = $this->site->getProductByID($oitem->product_id)) {
                    $this->setStoreQuantity($oitem->product_id, $shipping->store_id, (0 - $oitem->quantity));
                }
            }
        }
        if ($this->db->delete('shippings', ['id' => $id]) && $this->db->delete('shipping_items', ['shipping_id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllShippingItems($shipping_id)
    {
        $this->db->select('shipping_items.*, products.code as product_code, products.name as product_name')
            ->join('products', 'products.id=shipping_items.product_id', 'left')
            ->group_by('shipping_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('shipping_items', ['shipping_id' => $shipping_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 10, $strict = false)
    {
        if ($strict) {
            $this->db->where('code', $term);
        } else {
            if ($this->db->dbdriver == 'sqlite3') {
                $this->db->where("type != 'combo' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  (name || ' (' || code || ')') LIKE '%" . $term . "%')");
            } else {
                $this->db->where("type != 'combo' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
            }
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

    public function getShippingByID($id)
    {
        $q = $this->db->get_where('shippings', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStoreQuantity($product_id, $store_id)
    {
        $q = $this->db->get_where('product_store_qty', ['product_id' => $product_id, 'store_id' => $store_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function setStoreQuantity($product_id, $store_id, $quantity)
    {
        if ($store_qty = $this->getStoreQuantity($product_id, $store_id)) {
            $this->db->update('product_store_qty', ['quantity' => ($store_qty->quantity + $quantity)], ['product_id' => $product_id, 'store_id' => $store_id]);
        } else {
            $this->db->insert('product_store_qty', ['product_id' => $product_id, 'store_id' => $store_id, 'quantity' => $quantity]);
        }
    }

    

    public function updateShipping($id, $data = null, $items = [])
    {
        $shipping = $this->getShippingByID($id);
        if ($shipping->received) {
            $oitems = $this->getAllShippingItems($id);
            foreach ($oitems as $oitem) {
                if ($product = $this->site->getProductByID($oitem->product_id)) {
                    $this->setStoreQuantity($oitem->product_id, $shipping->store_id, (0 - $oitem->quantity));
                }
            }
        }
        if ($this->db->update('shippings', $data, ['id' => $id]) && $this->db->delete('shipping_items', ['shipping_id' => $id])) {
            foreach ($items as $item) {
                $item['shipping_id'] = $id;
                if ($this->db->insert('shipping_items', $item)) {
                    if ($data['received'] && $product = $this->site->getProductByID($item['product_id'])) {
                        $this->setStoreQuantity($item['product_id'], $shipping->store_id, $item['quantity']);
                    }
                }
            }
            return true;
        }
        return false;
    }
    
}
