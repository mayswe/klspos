<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends MY_Controller
{
    
    public function add_closing_bulk()
    {
    $today = date('Y-m-d');
    $stores = $this->site->getAllStores();

    foreach ($stores as $store) {
        // get all products that exist in this store’s batches
        $products = $this->db->select('DISTINCT(product_id)')
            ->where('store_id', $store->id)
            ->get('tec_stock_batches')
            ->result();

        foreach ($products as $p) {
            $stock = $this->db->select("
                        COALESCE(SUM(qty_base),0) as qty_base,
                        COALESCE(SUM(qty_secondary),0) as qty_secondary
                    ")
                    ->where('product_id', $p->product_id)
                    ->where('store_id', $store->id)
                    ->get('tec_stock_batches')
                    ->row();

            $data = [
                'store_id'      => $store->id,
                'product_id'    => $p->product_id,
                'date'          => $today,
                'qty_base'      => $stock->qty_base,
                'qty_secondary' => $stock->qty_secondary,
            ];

            // check if exists for today
            $exists = $this->db->get_where('tec_closing_balances', [
                'store_id'   => $store->id,
                'product_id' => $p->product_id,
                'date'       => $today
            ])->row();

            if ($exists) {
                $this->db->where('id', $exists->id)->update('tec_closing_balances', $data);
            } else {
                $this->db->insert('tec_closing_balances', $data);
            }
            }
        }

        echo "Closing balances saved successfully on " . date('Y-m-d H:i:s');
    }

} // ← This closing brace was missing
