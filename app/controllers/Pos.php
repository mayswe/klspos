<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }
        $this->load->helper('pos');
        $this->load->model('pos_model');
        $this->load->library('form_validation');
        $this->digital_file_types = 'zip|pdf|doc|docx|xls|xlsx|jpg|png|gif';
    }

    public function ajaxproducts($category_id = null, $return = null)
    {
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } elseif (!$category_id) {
            $category_id = $this->Settings->default_category;
        }
        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }
        if ($this->input->get('tcp') == 1) {
            $tcp = true;
        } else {
            $tcp = false;
        }

        $products = $this->pos_model->fetch_products($category_id, $this->Settings->pro_limit, $page);
        $pro      = 1;
        $prods    = '<div>';
        if ($products) {
            if ($this->Settings->bsty == 1) {
                foreach ($products as $product) {
                    $count = $product->id;
                    if ($count < 10) {
                        $count = '0' . ($count / 100) * 100;
                    }
                    if ($category_id < 10) {
                        $category_id = '0' . ($category_id / 100) * 100;
                    }
                    $prods .= '<button type="button" data-name="' . $product->name . '" id="product-' . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' class=\"btn btn-name btn-default btn-flat product\">" . $product->name . '</button>';
                    $pro++;
                }
            } elseif ($this->Settings->bsty == 2) {
                foreach ($products as $product) {
                    $count = $product->id;
                    if ($count < 10) {
                        $count = '0' . ($count / 100) * 100;
                    }
                    if ($category_id < 10) {
                        $category_id = '0' . ($category_id / 100) * 100;
                    }
                    $prods .= '<button type="button" data-name="' . $product->name . '" id="product-' . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' class=\"btn btn-img btn-flat product\"><img src=\"" . base_url() . 'uploads/thumbs/' . $product->image . '" alt="' . $product->name . '" style="width: 110px; height: 110px;"></button>';
                    $pro++;
                }
            } elseif ($this->Settings->bsty == 3) {
                foreach ($products as $product) {
                    $count = $product->id;
                    if ($count < 10) {
                        $count = '0' . ($count / 100) * 100;
                    }
                    if ($category_id < 10) {
                        $category_id = '0' . ($category_id / 100) * 100;
                    }
                    $prods .= '<button type="button" data-name="' . $product->name . '" id="product-' . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' class=\"btn btn-both btn-flat product\"><span class=\"bg-img\"><img src=\"" . base_url() . 'uploads/thumbs/' . $product->image . '" alt="' . $product->name . '" style="width: 100px; height: 100px;"></span><span><span>' . $product->name . '</span></span></button>';
                    $pro++;
                }
            }
        } else {
            $prods .= '<h4 class="text-center text-info" style="margin-top:50px;">' . lang('category_is_empty') . '</h4>';
        }

        $prods .= '</div>';

        if (!$return) {
            if (!$tcp) {
                echo $prods;
            } else {
                $category_products = $this->pos_model->products_count($category_id);
                header('Content-Type: application/json');
                echo json_encode(['products' => $prods, 'tcp' => $category_products]);
            }
        } else {
            return $prods;
        }
    }

   public function close_register($user_id = null)
{
    // Get logged-in user & store
    $user_id  = $this->session->userdata('user_id');
    $store_id = $this->session->userdata('store_id');

    // Get today's open register
    $today = date('Y-m-d');
    $register = $this->pos_model->getRegisterByDate($store_id, $today);

    if (!$register) {
        $this->session->set_flashdata('error', lang('no_open_register_for_today'));
        redirect('pos');
    }

    $rid = $register->id;
    $cash_in_hand = $register->cash_in_hand;

    // --- Form validation ---
    $this->form_validation->set_rules('note', lang('note'), 'trim');

    if ($this->form_validation->run() == true) {

        // ✅ Use full-day calculations, not from register open time
        $cashsales = $this->pos_model->getTodayCashSales($store_id);
        $ccsales   = $this->pos_model->getTodayCCSales($store_id);
        $chsales   = $this->pos_model->getTodayChSales($store_id);
        $expenses  = $this->pos_model->getTodayExpenses($store_id);

        // --- Calculate final cash ---
        $total_cash = ($cashsales->paid ? ($cashsales->paid + $cash_in_hand) : $cash_in_hand);
        $total_cash -= ($expenses->total ? $expenses->total : 0);

        // --- Prepare data for closing ---
        $data = [
            'closed_at'                => date('Y-m-d H:i:s'),
            'total_cash'               => $total_cash,
            'total_cheques'            => $chsales->total_cheques ?? 0,
            'total_cc_slips'           => $ccsales->total_cc_slips ?? 0,
            'total_cash_submitted'     => $this->input->post('total_cash_submitted') ?? $total_cash,
            'total_cheques_submitted'  => $this->input->post('total_cheques_submitted') ?? 0,
            'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted') ?? 0,
            'note'                     => $this->input->post('note'),
            'status'                   => 'close',
            'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
            'closed_by'                => $user_id,
        ];

        // --- Save and clear session ---
        if ($this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->unset_userdata(['register_id', 'cash_in_hand', 'register_open_time']);
            $this->session->set_flashdata('message', lang('register_closed'));
            redirect('welcome');
        }
    }

    
    $this->data['cash_in_hand']       = $cash_in_hand;
    $this->data['cashsales']          = $this->pos_model->getTodayCashSales($store_id);
    $this->data['ccsales']            = $this->pos_model->getTodayCCSales($store_id);
    $this->data['chsales']            = $this->pos_model->getTodayChSales($store_id);
    $this->data['expenses']           = $this->pos_model->getTodayExpenses($store_id);
    $this->data['users']              = $this->tec->getUsers();
    $this->data['suspended_bills']    = $this->pos_model->getSuspendedsales($user_id);
    $this->data['register_open_time'] = $register->date;
    // --- If validation fails or first time open the page ---
    $this->data['total_cash'] = ($this->data['cashsales']->paid ? ($this->data['cashsales']->paid + $cash_in_hand) : $cash_in_hand)
                            - ($this->data['expenses']->total ? $this->data['expenses']->total : 0);

    $this->load->view($this->theme . 'pos/close_register', $this->data);
}





    public function email_receipt($sale_id = null, $to = null)
    {
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        if (!$sale_id || !$to) {
            die();
        }

        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getSaleByID($sale_id);
        $this->tec->view_rights($inv->created_by);
        $this->load->helper('text');
        $this->data['rows']       = $this->pos_model->getAllSaleItems($sale_id);
        $this->data['customer']   = $this->pos_model->getCustomerByID($inv->customer_id);
        $this->data['inv']        = $inv;
        $this->data['sid']        = $sale_id;
        $this->data['noprint']    = null;
        $this->data['page_title'] = lang('invoice');
        $this->data['modal']      = false;
        $this->data['payments']   = $this->pos_model->getAllSalePayments($sale_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);

        $receipt = $this->load->view($this->theme . 'pos/view', $this->data, true);
        $message = preg_replace('#\<!-- start -->(.+)\<!-- end -->#Usi', '', $receipt);
        $subject = lang('email_subject') . ' - ' . $this->Settings->site_name;

        try {
            if ($this->tec->send_email($to, $subject, $message)) {
                echo json_encode(['msg' => lang('email_success')]);
            } else {
                echo json_encode(['msg' => lang('email_failed')]);
            }
        } catch (Exception $e) {
            echo json_encode(['msg' => $e->getMessage()]);
        }
    }

    public function get_product($code = null)
{
    if ($this->input->get('code')) {
        $code = $this->input->get('code');
    }

    $combo_items = false;
    if ($product = $this->pos_model->getProductByCode($code)) {

        // Exchange rate
        $exchange = $this->pos_model->getCurrencyRate('MYR');
        $myr_rate = $exchange ? $exchange->exchange_rate : 1;

        unset($product->cost, $product->details);
        $pos_store_id = $this->session->userdata('store_id');
        $product->available_qty = $this->pos_model->getProductAvailableQty(
            $product->id,
            $pos_store_id
        );
        $product->qty_secondary = $this->pos_model->getProductAvailableSecondQty(
            $product->id,
            $pos_store_id
        );
        $product->qty       = 1;
        $product->primary_qty = 0;
        $product->secondary_qty = 0;
        $product->comment   = '';
        $product->discount  = '0';

        // Price in POS currency
        $product_price_mmk        = $product->price > 0 ? $product->price : $product->price;
        $product->price           = round($product_price_mmk / $myr_rate, 2);
        $product->real_unit_price = $product->price;
        $product->unit_price      = $product->tax ? ($product->price + (($product->price * $product->tax) / 100)) : $product->price;

        // Combo support
        if ($product->type == 'combo') {
            $combo_items = $this->pos_model->getComboItemsByPID($product->id);
        }

        // ✅ Fetch unit prices
        $unit_prices = $this->pos_model->getProductUnitPrices($product->id);
        $unit_price_data = [];
        foreach ($unit_prices as $up) {
            $unit_price_data[$up->unit_id] = round($up->price / $myr_rate, 2);
        }

        // ✅ Fetch unit conversions
        $unit_conversions = $this->pos_model->getProductUnitConversions($product->id);
        $unit_conversion_data = [];
        foreach ($unit_conversions as $uc) {
            $unit_conversion_data[$uc->unit_id] = [
                'operator' => $uc->operator,
                'value'    => (float) $uc->operation_value
            ];
        }

        // ✅ Fetch stock batches (NEW)
        $batches = $this->pos_model->getProductBatches($product->id, $pos_store_id);
        $batch_data = [];
        foreach ($batches as $b) {
            $batch_data[] = [
                'batch_id'      => $b->id,
                'expiry'        => $b->expiry_date,
                'available_qty' => (float) $b->qty_base,
                'qty_secondary' => (float) $b->qty_secondary,
                'cost'          => (float) $b->cost,
            ];
        }

        echo json_encode([
            'id'               => str_replace('.', '', microtime(true)),
            'item_id'          => $product->id,
            'label'            => $product->name . ' (' . $product->code . ')',
            'row'              => $product,
            'combo_items'      => $combo_items,
            'unit_prices'      => $unit_price_data,
            'unit_conversions' => $unit_conversion_data,
            'batches'          => $batch_data  // ✅ added batch stock info
        ]);
    } else {
        echo null;
    }
}


private function getPosUnitData($product_id)
{
    $exchange = $this->pos_model->getCurrencyRate('MYR');
    $myr_rate = ($exchange && $exchange->exchange_rate > 0)
        ? $exchange->exchange_rate
        : 1;

    $unit_price_data = [];
    foreach ($this->pos_model->getProductUnitPrices($product_id) as $unit_price) {
        $unit_price_data[$unit_price->unit_id] = round(
            $unit_price->price / $myr_rate,
            2
        );
    }

    $unit_conversion_data = [];
    foreach ($this->pos_model->getProductUnitConversions($product_id) as $conversion) {
        $unit_conversion_data[$conversion->unit_id] = [
            'operator' => $conversion->operator,
            'value' => (float) $conversion->operation_value,
        ];
    }

    return [
        'unit_prices' => $unit_price_data,
        'unit_conversions' => $unit_conversion_data,
    ];
}




public function index($sid = null, $eid = null)
    {
        log_message('error', 'POS INDEX CALLED at ' . date('Y-m-d H:i:s'));
        $store_id = $this->session->userdata('store_id');
        $today    = date('Y-m-d');
        
        // Get open register for the store
        $register = $this->pos_model->getRegisterByDate($store_id, $today);
        if (!$register) {
            $this->session->set_flashdata('warning', lang('register_not_open'));
            redirect('pos/open_register'); // force to open register
        }
        
        // Save register info to session
        if (!$this->session->userdata('register_id')) {
            $this->session->set_userdata([
                'register_id'        => $register->id,
                'cash_in_hand'       => $register->cash_in_hand,
                'register_open_time' => $register->date,
            ]);
        }

        if (!$this->Settings->multi_store) {
            $this->session->set_userdata('store_id', 1);
        }
        if (!$this->session->userdata('store_id')) {
            $this->session->set_flashdata('warning', lang('please_select_store'));
            redirect($this->Settings->multi_store ? 'stores' : 'welcome');
        }
        // --- PREORDER FLAG ---
        $is_preorder = $this->input->post('is_preorder') ? 1 : 0;

        if ($this->input->get('hold')) {
            $sid = $this->input->get('hold');
        }
        if ($this->input->get('edit')) {
            $eid = $this->input->get('edit');
        }
        if ($this->input->post('eid')) {
            $eid = $this->input->post('eid');
        }
        if ($this->input->post('did')) {
            $did = $this->input->post('did');
        } else {
            $did = null;
        }
        if ($eid && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER'] ?? 'pos');
        }
        if (!$this->Settings->default_customer) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            redirect('settings');
        }
        if (!$this->session->userdata('register_id')) {
            if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
                $register_data = ['register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date];
                $this->session->set_userdata($register_data);
            } else {
                $this->session->set_flashdata('warning', lang('register_not_open'));
                redirect('pos/open_register');
            }
        }

        $suspend = $this->input->post('suspend') ? true : false;

        $this->form_validation->set_rules('customer_id', lang('customer'), 'trim|required|numeric');

        log_message('debug', json_encode($this->form_validation->error_array()));

        if ($this->form_validation->run() == true) {
            $quantity  = 'quantity';
            $product   = 'product';
            $unit_cost = 'unit_cost';
            $tax_rate  = 'tax_rate';

            $date             = $eid ? $this->input->post('date') : date('Y-m-d H:i:s');
            $customer_id      = $this->input->post('customer_id');
            $customer_details = $this->pos_model->getCustomerByID($customer_id);
            $customer         = $customer_details->name;
            $note             = $this->tec->clear_tags($this->input->post('spos_note'));

            $total            = 0;
            $product_tax      = 0;
            $order_tax        = 0;
            $product_discount = 0;
            $order_discount   = 0;
            $percentage       = '%';
            $i                = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id         = $_POST['product_id'][$r];
                $unit_id         = $_POST['unit_id'][$r];
                $real_unit_price = $this->tec->formatDecimal($_POST['real_unit_price'][$r]);
                $item_quantity   = $_POST['quantity'][$r];
                $sale_item_id    = $_POST['sale_item_id'][$r] ?? null;
                $posted_primary_qty = $_POST['primary_qty'][$r] ?? null;
                $posted_secondary_qty = $_POST['secondary_qty'][$r] ?? null;
                $item_comment    = $_POST['item_comment'][$r];
                $item_discount   = $_POST['product_discount'][$r] ?? '0';

                if (isset($item_id) && isset($real_unit_price) && isset($item_quantity)) {
                    $product_details = $this->site->getProductByID($item_id,$store_id);
                    if ($product_details) {
                        $product_name = $product_details->name;
                        $product_code = $product_details->code;
                        $product_cost = $product_details->cost;
                    } else {
                        $product_name = $_POST['product_name'][$r];
                        $product_code = $_POST['product_code'][$r];
                        $product_cost = 0;
                    }

                    $product_cost = $product_details->cost;
                    $myr_rate = $this->site->getExchangeRate('MYR');
                    if (!$myr_rate) { $myr_rate = 1; }
                    $myr_cost = $product_cost / $myr_rate;
                    $product_cost = $this->tec->formatDecimal($myr_cost, 4);

                    $is_dual_product =
                        !empty($product_details->is_dual_unit) &&
                        !empty($product_details->secondary_unit_id);
                    $primary_qty = null;
                    $secondary_qty = null;

                    if ($is_dual_product) {
                        if (
                            !is_numeric($posted_primary_qty) ||
                            !is_numeric($posted_secondary_qty)
                        ) {
                            $this->session->set_flashdata(
                                'error',
                                'Dual Unit အတွက် အလေးချိန်နှင့် အရေအတွက်ကို မှန်ကန်စွာ ဖြည့်ပါ။'
                            );
                            redirect($eid ? 'pos/?edit=' . $eid : 'pos');
                        }

                        $primary_qty = max(0, (float) $posted_primary_qty);
                        $secondary_qty = max(0, (float) $posted_secondary_qty);

                        if ($primary_qty <= 0 && $secondary_qty <= 0) {
                            $this->session->set_flashdata(
                                'error',
                                'Dual Unit ပစ္စည်းတွင် အလေးချိန် သို့မဟုတ် အရေအတွက် တစ်ခုခု ဖြည့်ရပါမည်။'
                            );
                            redirect($eid ? 'pos/?edit=' . $eid : 'pos');
                        }

                        if ((int) $unit_id === (int) $product_details->secondary_unit_id) {
                            if ($primary_qty <= 0 || $secondary_qty <= 0) {
                                $this->session->set_flashdata(
                                    'error',
                                    'အရေအတွက်ယူနစ်ဖြင့် ရောင်းပါက Actual Weight နှင့် Count နှစ်ခုလုံး ဖြည့်ပါ။'
                                );
                                redirect($eid ? 'pos/?edit=' . $eid : 'pos');
                            }
                            $item_quantity = $secondary_qty;
                        } else {
                            $base_factor = $this->pos_model->getBaseQuantityFactor(
                                $item_id,
                                $unit_id,
                                $product_details->base_unit_id
                            );

                            if ($base_factor === null || $primary_qty <= 0) {
                                $this->session->set_flashdata(
                                    'error',
                                    'ရွေးထားသော အလေးချိန်ယူနစ်၏ conversion သို့မဟုတ် Actual Weight မမှန်ပါ။'
                                );
                                redirect($eid ? 'pos/?edit=' . $eid : 'pos');
                            }

                            // primary_qty is always stored in the product base unit.
                            $item_quantity = $primary_qty / $base_factor;
                        }

                        $item_quantity = $this->tec->formatDecimal($item_quantity, 6);
                    }
                    
                    if (!$this->Settings->overselling && !$is_preorder) {

                        log_message('debug', '🚫 Overselling check active | Product ID: ' . $product_details->id);

                        if ($product_details->type == 'standard' && $is_dual_product) {
                            $available_primary = $this->pos_model->getProductAvailableQty(
                                $item_id,
                                $store_id
                            );
                            $available_secondary = $this->pos_model->getProductAvailableSecondQty(
                                $item_id,
                                $store_id
                            );

                            if ($eid && $sale_item_id) {
                                $previous_stock = $this->pos_model->getSaleItemStockQuantities(
                                    $sale_item_id,
                                    $eid,
                                    $item_id
                                );
                                $available_primary += $previous_stock->qty_base;
                                $available_secondary += $previous_stock->qty_secondary;
                            }

                            if (
                                $primary_qty > $available_primary + 0.000001 ||
                                $secondary_qty > $available_secondary + 0.000001
                            ) {
                                $this->session->set_flashdata(
                                    'error',
                                    lang('quantity_low') . ' (' .
                                    lang('name') . ': ' . $product_details->name . ' | ' .
                                    'Weight: ' . $primary_qty . '/' . $available_primary . ' | ' .
                                    'Count: ' . $secondary_qty . '/' . $available_secondary . ')'
                                );
                                redirect($eid ? 'pos/?edit=' . $eid : 'pos');
                            }

                        } elseif ($product_details->type == 'standard') {
                            $unit_id = $unit_id ?? $product_details->base_unit_id;
                            $adjusted_quantity = $product_details->qty_base;

                            log_message('debug', '📦 Base stock qty: ' . $product_details->qty_base . ' | Unit ID: ' . $unit_id);

                            if ($eid) {
                                // Get previous quantity sold for this item in the sale being edited
                                $prev_sale_item = $this->pos_model->getSaleItem($eid, $item_id, $unit_id);
                                log_message('debug', '✍️ Editing Sale ID: ' . $eid . ' | Previous Sale Item: ' . print_r($prev_sale_item, true));

                                if ($prev_sale_item) {
                                    $adjusted_quantity += $prev_sale_item->quantity; // add back previously sold qty
                                    log_message('debug', '🔁 Adjusted qty after adding previous sale qty: ' . $adjusted_quantity);
                                }
                            }

                            // Check if unit is not base unit
                            if ($unit_id != $product_details->base_unit_id) {
                                $conversion = $this->site->getUnitConversion($product_details->id, $unit_id);
                                log_message('debug', '🔄 Unit Conversion: ' . print_r($conversion, true));

                                if ($conversion && $conversion->operation_value > 0) {
                                    if ($conversion->operator == '*') {
                                        $adjusted_quantity *= $conversion->operation_value;
                                    } elseif ($conversion->operator == '/') {
                                        $adjusted_quantity /= $conversion->operation_value;
                                    }
                                    log_message('debug', '📏 Adjusted qty after unit conversion: ' . $adjusted_quantity);
                                }
                            }

                            log_message('debug', '🧮 Final adjusted qty: ' . $adjusted_quantity . ' | Ordered qty: ' . $item_quantity);

                            if ($adjusted_quantity < $item_quantity) {
                                $this->session->set_flashdata('error', lang('quantity_low') . ' (' .
                                    lang('name') . ': ' . $product_details->name . ' | ' .
                                    lang('ordered') . ': ' . $item_quantity . ' | ' .
                                    lang('available') . ': ' . $adjusted_quantity.'/' . $unit_id .'!='. $product_details->base_unit_id .
                                    ')');
                                log_message('error', '❌ Insufficient stock for product ID ' . $product_details->id);
                                redirect('pos');
                            }

                        } elseif ($product_details->type == 'combo') {
                            log_message('debug', '🧩 Checking combo product: ' . $product_details->name);
                            $combo_items = $this->pos_model->getComboItemsByPID($product->id);
                            log_message('debug', 'Combo items: ' . print_r($combo_items, true));

                            foreach ($combo_items as $combo_item) {
                                $cpr = $this->site->getProductByID($combo_item->id);
                                log_message('debug', '📦 Combo Component: ' . $cpr->name . ' | Available: ' . $cpr->quantity . ' | Required: ' . ($item_quantity * $combo_item->qty));

                                if ($cpr->quantity < $item_quantity * $combo_item->qty) {
                                    $this->session->set_flashdata('error', lang('quantity_low') . ' (' .
                                        lang('name') . ': ' . $cpr->name . ' | ' .
                                        lang('ordered') . ': ' . $item_quantity . ' x ' . $combo_item->qty . ' = ' . $item_quantity * $combo_item->qty . ' | ' .
                                        lang('available') . ': ' . $cpr->quantity .
                                        ') ' . $product_details->name);
                                    log_message('error', '❌ Combo stock insufficient for component: ' . $cpr->name);
                                    redirect('pos');
                                }
                            }
                        }
                    }


                    $unit_price = $real_unit_price;

                    $pr_discount = 0;
                    if (isset($item_discount)) {
                        $discount = $item_discount;
                        $dpos     = strpos($discount, $percentage);
                        if ($dpos !== false) {
                            $pds         = explode('%', $discount);
                            $pr_discount = $this->tec->formatDecimal((($unit_price * (float)($pds[0])) / 100), 4);
                        } else {
                            $pr_discount = $this->tec->formatDecimal($discount);
                        }
                    }
                    $unit_price       = $this->tec->formatDecimal(($unit_price - $pr_discount), 4);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->tec->formatDecimal(($pr_discount * $item_quantity), 4);
                    $product_discount += $pr_item_discount;

                    $pr_item_tax = 0;
                    $item_tax    = 0;
                    $tax         = '';
                    if (isset($product_details->tax) && $product_details->tax != 0) {
                        if ($product_details && $product_details->tax_method == 1) {
                            $item_tax = $this->tec->exlusiveTax($unit_price, $product_details->tax);
                            $tax      = $product_details->tax . '%';
                        } else {
                            $item_tax = $this->tec->inclusiveTax($unit_price, $product_details->tax);
                            $tax      = $product_details->tax . '%';
                            $item_net_price -= $item_tax;
                        }

                        $pr_item_tax = $this->tec->formatDecimal(($item_tax * $item_quantity), 4);
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->tec->formatDecimal((($item_net_price * $item_quantity) + $pr_item_tax), 4);

                    

                    $product_item = [
                        'product_id'      => $item_id,
                        'unit_id'         => $unit_id,
                        'quantity'        => $item_quantity,
                        'unit_price'      => $unit_price,
                        'net_unit_price'  => $item_net_price,
                        'discount'        => $item_discount,
                        'comment'         => $item_comment,
                        'item_discount'   => $pr_item_discount,
                        'tax'             => $tax,
                        'item_tax'        => $pr_item_tax,
                        'subtotal'        => $subtotal,
                        'real_unit_price' => $real_unit_price,
                        'cost'            => $product_cost,
                        'product_code'    => $product_code,
                        'product_name'    => $product_name,
                        'preorder_qty'    => $is_preorder ? $item_quantity : 0,
                        'fulfilled_qty'   => 0,
                    ];

                    if (!$suspend) {
                        // Keep the exact Dual Unit quantities entered at POS.
                        // `quantity` remains unchanged for price/delivery logic.
                        $product_item['qty_base'] = $is_dual_product ? $primary_qty : 0;
                        $product_item['qty_secondary'] = $is_dual_product ? $secondary_qty : 0;
                    }

                    if ($is_dual_product) {
                        // These aliases are still required by Pos_model for the
                        // existing FIFO stock-deduction logic. Pos_model may
                        // remove them before inserting sale_items; qty_base and
                        // qty_secondary above are the persistent sale columns.
                        $product_item['primary_qty'] = $primary_qty;
                        $product_item['secondary_qty'] = $secondary_qty;
                    }

                    $products[] = $product_item;

                    $total += $this->tec->formatDecimal(($item_net_price * $item_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
                // } else {
            //     krsort($products);
            }

            if ($this->input->post('order_discount')) {
                $order_discount_id = $this->input->post('order_discount');
                $opos              = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods            = explode('%', $order_discount_id);
                    $order_discount = $this->tec->formatDecimal(((($total + $product_tax) * (float)($ods[0])) / 100), 4);
                } else {
                    $order_discount = $this->tec->formatDecimal($order_discount_id);
                }
            } else {
                $order_discount_id = null;
            }
            $total_discount = $this->tec->formatDecimal(($order_discount + $product_discount), 4);

            if ($this->input->post('order_tax')) {
                $order_tax_id = $this->input->post('order_tax');
                $opos         = strpos($order_tax_id, $percentage);
                if ($opos !== false) {
                    $ots       = explode('%', $order_tax_id);
                    $order_tax = $this->tec->formatDecimal(((($total + $product_tax - $order_discount) * (float)($ots[0])) / 100), 4);
                } else {
                    $order_tax = $this->tec->formatDecimal($order_tax_id);
                }
            } else {
                $order_tax_id = null;
                $order_tax    = 0;
            }

            $total_tax   = $this->tec->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->tec->formatDecimal(($total + $total_tax - $order_discount), 4);
            $paid        = $this->input->post('amount') ? $this->input->post('amount') : 0;
            $round_total = $this->tec->roundNumber($grand_total, $this->Settings->rounding);
            $rounding    = $this->tec->formatDecimal(($round_total - $grand_total));
            if (!$suspend && $customer_details->id == 1 && $this->tec->formatDecimal($paid) < $this->tec->formatDecimal($round_total)) {
                $this->session->set_flashdata('error', lang('select_customer_for_due'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            if (!$eid) {
                $status = 'due';
                if ($this->tec->formatDecimal($round_total) <= $this->tec->formatDecimal($paid)) {
                    $status = 'paid';
                } elseif ($this->tec->formatDecimal($round_total) > $this->tec->formatDecimal($paid) && $paid > 0) {
                    $status = 'partial';
                }
            }

            

            $data = ['date'         => $date,
                'customer_id'       => $customer_id,
                'customer_name'     => $customer,
                'total'             => $this->tec->formatDecimal($total, 4),
                'product_discount'  => $this->tec->formatDecimal($product_discount, 4),
                'order_discount_id' => $order_discount_id,
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $this->tec->formatDecimal($product_tax, 4),
                'order_tax_id'      => $order_tax_id,
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'grand_total'       => $grand_total,
                'total_items'       => $this->input->post('total_items'),
                'total_quantity'    => $this->input->post('total_quantity'),
                'rounding'          => $rounding,
                'paid'              => $paid,
                'status'            => $status,
                'created_by'        => $this->session->userdata('user_id'),
                'note'              => $note,
                'hold_ref'          => $this->input->post('hold_ref'),
            ];

            // attach preorder info
            if ($is_preorder) {
                $data['is_preorder']     = 1;
                $data['preorder_status'] = 'pending';
            }

            if (!$eid) {
                $data['store_id'] = $this->session->userdata('store_id');
            }

            if (!$eid && !$suspend && $paid) {
                if ($this->input->post('paying_gift_card_no')) {
                    $gc = $this->pos_model->getGiftCardByNO($this->input->post('paying_gift_card_no'));
                    if (!$gc || $gc->balance < $amount) {
                        $this->session->set_flashdata('error', lang('incorrect_gift_card'));
                        redirect('pos');
                    }
                }
                $amount  = $this->tec->formatDecimal(($paid > $grand_total ? ($paid - $this->input->post('balance_amount')) : $paid), 4);
                $payment = [
                    'date'        => $date,
                    'amount'      => $amount,
                    'customer_id' => $customer_id,
                    'paid_by'     => $this->input->post('paid_by'),
                    'cheque_no'   => $this->input->post('cheque_no'),
                    'cc_no'       => $this->input->post('cc_no'),
                    'gc_no'       => $this->input->post('paying_gift_card_no'),
                    'cc_holder'   => $this->input->post('cc_holder'),
                    'cc_month'    => $this->input->post('cc_month'),
                    'cc_year'     => $this->input->post('cc_year'),
                    'cc_type'     => $this->input->post('cc_type'),
                    'cc_cvv2'     => $this->input->post('cc_cvv2'),
                    'created_by'  => $this->session->userdata('user_id'),
                    'store_id'    => $this->session->userdata('store_id'),
                    'note'        => $this->input->post('payment_note'),
                    'pos_paid'    => $this->tec->formatDecimal($this->input->post('amount'), 4),
                    'pos_balance' => $this->tec->formatDecimal($this->input->post('balance_amount'), 4),
                ];
                $data['paid'] = $amount;
            } else {
                $payment = [];
            }

            //$this->tec->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && !empty($products)) {
            if ($suspend) {
                unset($data['status'], $data['rounding']);
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('rmspos', 1);
                    $this->session->set_flashdata('message', lang('sale_saved_to_opened_bill'));
                    redirect('pos');
                } else {
                    $this->session->set_flashdata('error', lang('action_failed'));
                    redirect('pos/' . $did);
                }
            } elseif ($eid) {
                unset($data['status'], $data['paid']);
                if (!$this->Admin) {
                    unset($data['date']);
                }
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = $this->session->userdata('user_id');
                $data['is_preorder'] = (int) $this->input->post('is_preorder');

                if ($this->pos_model->updateSale($eid, $data, $products)) {
                    $this->session->set_userdata('rmspos', 1);
                    $this->session->set_flashdata('message', lang('sale_updated'));
                    redirect('sales');
                } else {
                    $this->session->set_flashdata('error', lang('action_failed'));
                    redirect('pos/?edit=' . $eid);
                }
            } else {
                
                if ($this->input->post('sale_token') != $this->session->userdata('sale_token')) {
                    log_message('error', 'Duplicate submission blocked');
                    redirect('pos');
                }
                
                $this->session->unset_userdata('sale_token');
                
                $data['is_preorder'] = (int) $this->input->post('is_preorder');

                if ($data['is_preorder']) {
                    $data['preorder_status'] = 'pending';
                }
                
            log_message('debug', 'FILES DATA: ' . print_r($_FILES, true));
            
                $attachment = null;
                
                if ($_FILES['attachment']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path']   = 'files/';
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = 2048;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('attachment')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $photo                 = $this->upload->file_name;
                    $payment['attachment'] = $photo;
                }
                
                log_message('error', 'POST TOKEN: ' . $this->input->post('sale_token'));
                log_message('error', 'SESSION TOKEN: ' . $this->session->userdata('sale_token'));
                
                if ($sale = $this->pos_model->addSale($data, $products, $payment, $did)) {
                    $this->session->set_userdata('rmspos', 1);
                    $msg = lang('sale_added');
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('message', $msg);
                    $redirect_to = $this->Settings->after_sale_page ? 'pos' : 'pos/view/' . $sale['sale_id'];
                    if ($this->Settings->auto_print) {
                        if (!$this->Settings->remote_printing) {
                            $this->print_receipt($sale['sale_id'], true);
                        } elseif ($this->Settings->remote_printing == 2) {
                            $redirect_to .= '?print=' . $sale['sale_id'];
                        }
                    }
                    redirect($redirect_to);
                } else {
                    $this->session->set_flashdata('error', lang('action_failed'));
                    redirect('pos');
                }
            }
        } else {
            if (isset($sid) && !empty($sid)) {
                $suspended_sale = $this->pos_model->getSuspendedSaleByID($sid);
                $inv_items      = $this->pos_model->getSuspendedSaleItems($sid);
                if (is_array($inv_items)) {
                    krsort($inv_items);
                }

                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row       = json_decode('{}');
                        $row->id   = 0;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->tax  = 0;
                    }
                    $row->price           = $item->net_unit_price + ($item->item_discount / $item->quantity);
                    $row->unit_price      = $item->unit_price     + ($item->item_discount / $item->quantity)     + ($item->item_tax / $item->quantity);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->discount        = $item->discount;
                    $row->qty             = $item->quantity;
                    if (!empty($row->is_dual_unit) && !empty($row->secondary_unit_id)) {
                        $row->primary_qty = (float) ($item->qty_base ?? $item->primary_qty ?? 0);
                        $row->secondary_qty = (float) ($item->qty_secondary ?? $item->secondary_qty ?? 0);
                    }
                    $row->mhs             = 200;
                    $row->comment         = $item->comment;
                    $delivery = $this->pos_model->getProductDelivery($row->id, $store_id);
                    $cost = $this->pos_model->getProductCost($row->id, $store_id);
                    
                    $secondcost = $this->pos_model->getSecondCost($row->id, $store_id);
                    $row->second_cost = $secondcost;
                    
                    $row->delivery = $delivery;
                    $row->cost = $cost;
                    
                    
                    $row->ordered         = $item->quantity;
                    $combo_items          = false;
                    $ri                   = $this->Settings->item_addition ? $row->id : $c;
                    $unit_data = $this->getPosUnitData($row->id);
                    $pr[$ri] = [
                        'id' => $c,
                        'item_id' => $row->id,
                        'label' => $row->name . ' (' . $row->code . ')',
                        'row' => $row,
                        'combo_items' => $combo_items,
                        'unit_prices' => $unit_data['unit_prices'],
                        'unit_conversions' => $unit_data['unit_conversions'],
                    ];
                    $c++;
                }
                $this->data['items']        = json_encode($pr);
                $this->data['sid']          = $sid;
                $this->data['suspend_sale'] = $suspended_sale;
                $this->data['message']      = lang('suspended_sale_loaded');
            }

            if (isset($eid) && !empty($eid)) {
                $sale      = $this->pos_model->getSaleByID($eid);
                $inv_items = $this->pos_model->getAllSaleItems($eid);
                
                // Add debug log
                log_message('debug', 'Sale Items for EID '.$eid.': ' . print_r($inv_items, true));

                krsort($inv_items);
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row = json_decode('{}');
                    }
                    $row->price           = $item->net_unit_price;
                    $row->unit_price      = $item->unit_price;
                    $row->real_unit_price = $item->real_unit_price;
                    $row->discount        = $item->discount;
                    $row->qty = !empty($item->quantity) ? $item->quantity : $item->preorder_qty;
                    $row->order_qty = ($item->quantity > 0) ? $item->quantity : $item->preorder_qty;

                    if (!empty($row->is_dual_unit) && !empty($row->secondary_unit_id)) {
                        // New sales keep both values directly on sale_items.
                        // Fall back to cogs_logs for old sales created before
                        // the Dual Unit columns were added.
                        $saved_primary_qty = isset($item->qty_base)
                            ? (float) $item->qty_base
                            : 0;
                        $saved_secondary_qty = isset($item->qty_secondary)
                            ? (float) $item->qty_secondary
                            : 0;

                        if ($saved_primary_qty <= 0 && $saved_secondary_qty <= 0) {
                            $stock_quantities = $this->pos_model->getSaleItemStockQuantities(
                                $item->id,
                                $eid,
                                $item->product_id
                            );
                            $saved_primary_qty = (float) $stock_quantities->qty_base;
                            $saved_secondary_qty = (float) $stock_quantities->qty_secondary;
                        }

                        $row->primary_qty = $saved_primary_qty;
                        $row->secondary_qty = $saved_secondary_qty;
                        $row->qty = $this->pos_model->getProductAvailableQty(
                            $row->id,
                            $store_id
                        ) + $row->primary_qty;
                        $row->available_qty = $row->qty;
                        $row->qty_secondary = $this->pos_model->getProductAvailableSecondQty(
                            $row->id,
                            $store_id
                        ) + $row->secondary_qty;
                    }
                    $row->sale_item_id = $item->id;


                    $row->mhs             = 300;
                    $row->comment         = $item->comment;
                    $row->unit_id         = $item->unit_id;
                    $delivery = $this->pos_model->getProductDelivery($row->id, $store_id);
                    $cost = $this->pos_model->getProductCost($row->id, $store_id);
                    
                    $secondcost = $this->pos_model->getSecondCost($row->id, $store_id);
                    $row->second_cost = $secondcost;

                    $row->delivery = $delivery;
                    $row->cost = $cost;
                    $combo_items          = false;
                    $row->quantity += $item->quantity;
                    if ($row->type == 'combo') {
                        $combo_items = $this->pos_model->getComboItemsByPID($row->id);
                        foreach ($combo_items as $combo_item) {
                            $combo_item->quantity += ($combo_item->qty * $item->quantity);
                        }
                    }
                    log_message('debug', '✍️ Editing Sale ID: ' . $eid . ' | Previous Sale Item: ' . print_r($item->quantity, true));
                    log_message('debug', '✍️ Editing Sale ID: ' . $eid . ' | Previous Sale Item: ' . print_r($item->preorder_qty, true));
                    $ri      = $this->Settings->item_addition ? $row->id : $c;
                    $unit_data = $this->getPosUnitData($row->id);
                    $pr[$ri] = [
                        'id' => $c,
                        'item_id' => $row->id,
                        'label' => $row->name . ' (' . $row->code . ')',
                        'row' => $row,
                        'combo_items' => $combo_items,
                        'unit_prices' => $unit_data['unit_prices'],
                        'unit_conversions' => $unit_data['unit_conversions'],
                    ];
                    $c++;
                }
                $this->data['items']   = json_encode($pr);
                $this->data['eid']     = $eid;
                $this->data['sale']    = $sale;
                $this->data['message'] = lang('sale_loaded');
            }
            $this->data['error']           = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['reference_note']  = isset($sid) && !empty($sid) ? $suspended_sale->hold_ref : (isset($eid) && !empty($eid) ? $sale->hold_ref : null);
            $this->data['sid']             = isset($sid) && !empty($sid) ? $sid : 0;
            $this->data['eid']             = isset($eid) && !empty($eid) ? $eid : 0;
            $this->data['customers']       = $this->site->getAllCustomers();
            $this->data['product_units']   = $this->site->getProductUnits();
            $this->data['base_units']      = $this->site->getBaseUnits();
            $this->data['tcp']             = $this->pos_model->products_count($this->Settings->default_category);
            $this->data['products']        = $this->ajaxproducts($this->Settings->default_category, 1);
            $this->data['categories']      = $this->site->getAllCategories();
            $this->data['message']         = $this->session->flashdata('message');
            $this->data['suspended_sales'] = $this->site->getUserSuspenedSales();

            $this->data['printer'] = $this->site->getPrinterByID($this->Settings->printer);
            $printers              = [];
            if (!empty($order_printers = json_decode($this->Settings->order_printers))) {
                foreach ($order_printers as $printer_id) {
                    $printers[] = $this->site->getPrinterByID($printer_id);
                }
            }
            $this->data['order_printers'] = $printers;

            if ($saleid = $this->input->get('print', true)) {
                if ($inv = $this->pos_model->getSaleByID($saleid)) {
                    if ($this->session->userdata('store_id') != $inv->store_id) {
                        $this->session->set_flashdata('error', lang('access_denied'));
                        redirect('pos');
                    }
                    $this->tec->view_rights($inv->created_by, false, 'pos');
                    $this->load->helper('text');
                    $this->data['rows']       = $this->pos_model->getAllSaleItems($saleid);
                    $this->data['customer']   = $this->pos_model->getCustomerByID($inv->customer_id);
                    $this->data['store']      = $this->site->getStoreByID($inv->store_id);
                    $this->data['inv']        = $inv;
                    $this->data['print']      = $saleid;
                    $this->data['payments']   = $this->pos_model->getAllSalePayments($saleid);
                    $this->data['created_by'] = $this->site->getUser($inv->created_by);
                }
            }
            
            $this->data['page_title'] = lang('pos');
            $bc                       = [['link' => '#', 'page' => lang('pos')]];
            $meta                     = ['page_title' => lang('pos'), 'bc' => $bc];
            $this->session->set_userdata('sale_token', md5(uniqid()));
            $this->load->view($this->theme . 'pos/index', $this->data, $meta);
        }
    }


    public function language($lang = false)
    {
        if ($this->input->get('lang')) {
            $lang = $this->input->get('lang');
        }
        //$this->load->helper('cookie');
        $folder        = 'app/language/';
        $languagefiles = scandir($folder);
        if (in_array($lang, $languagefiles)) {
            $cookie = [
                'name'   => 'language',
                'value'  => $lang,
                'expire' => '31536000',
                'prefix' => 'spos_',
                'secure' => false,
            ];

            $this->input->set_cookie($cookie);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function open_drawer()
    {
        $printer = $this->site->getPrinterByID($this->Settings->printer);
        $this->load->library('escpos');
        $this->escpos->load($printer);
        $this->escpos->open_drawer();
    }

    public function open_register()
{
    $store_id = $this->session->userdata('store_id');
    if (!$store_id) {
        $this->session->set_flashdata('warning', lang('please_select_store'));
        redirect('stores');
    }

    $today = date('Y-m-d');

    // Check if store already has an open register today
    $register = $this->pos_model->getRegisterByDate($store_id, $today);
    if ($register) {
        $this->session->set_flashdata('message', lang('register_already_open'));
        redirect('pos');
    }

    // --- Get cash_in_hand from yesterday's closed register ---
    $yesterday_register = $this->pos_model->getYesterdayClosedRegister($store_id);
    if ($yesterday_register && $yesterday_register->total_cash_submitted) {
        $cash_in_hand = $yesterday_register->total_cash_submitted;
    } else {
        $cash_in_hand = 0; // or default value if no yesterday register
    }

    $data = [
        'date'          => date('Y-m-d H:i:s'),
        'cash_in_hand'  => $cash_in_hand,
        'user_id'       => $this->session->userdata('user_id'),
        'store_id'      => $store_id,
        'status'        => 'open',
    ];

    if ($this->pos_model->openRegister($data)) {
        $this->session->set_flashdata('message', lang('welcome_to_pos'));
        redirect('pos');
    } else {
        $this->session->set_flashdata('error', lang('action_failed'));
        redirect('pos');
    }
}





    public function p($bo = 'order')
    {
        $date             = date('Y-m-d H:i:s');
        $customer_id      = $this->input->post('customer_id');
        $customer_details = $this->pos_model->getCustomerByID($customer_id);
        $customer         = $customer_details->name;
        $note             = $this->tec->clear_tags($this->input->post('spos_note'));

        $total            = 0;
        $product_tax      = 0;
        $order_tax        = 0;
        $product_discount = 0;
        $order_discount   = 0;
        $percentage       = '%';
        $i                = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
        for ($r = 0; $r < $i; $r++) {
            $item_id         = $_POST['product_id'][$r];
            $unit_id         = $_POST['unit_id'][$r];
            $real_unit_price = $this->tec->formatDecimal($_POST['real_unit_price'][$r]);
            $item_quantity   = $_POST['quantity'][$r];
            $item_comment    = $_POST['item_comment'][$r];
            $item_ordered    = $_POST['item_was_ordered'][$r];
            $item_discount   = $_POST['product_discount'][$r] ?? '0';

            if (isset($item_id) && isset($real_unit_price) && isset($item_quantity)) {
                $product_details = $this->site->getProductByID($item_id);
                if ($product_details) {
                    $product_name = $product_details->name;
                    $product_code = $product_details->code;
                    $product_cost = $product_details->cost;
                } else {
                    $product_name = $_POST['product_name'][$r];
                    $product_code = $_POST['product_code'][$r];
                    $product_cost = 0;
                }
                
                if (!$this->Settings->overselling && !$is_preorder) {
                    if ($product_details->type == 'standard') {
                        $unit_id = $unit_id ?? $product_details->unit_id;
                        $adjusted_quantity = $product_details->quantity;

                        // Check if unit is not base unit
                        if ($unit_id != $product_details->unit_id) {
                            $conversion = $this->site->getUnitConversion($product_details->id, $unit_id);

                            if ($conversion && $conversion->operation_value > 0) {
                                if ($conversion->operator == '*') {
                                    $adjusted_quantity *= $conversion->operation_value;
                                } elseif ($conversion->operator == '/') {
                                    $adjusted_quantity /= $conversion->operation_value;
                                }
                            }
                        }

                        if ($adjusted_quantity < $item_quantity) {
                            $this->session->set_flashdata('error', lang('mhs') . lang('quantity_low') . ' (' .
                                lang('name') . ': ' . $product_details->name . ' | ' .
                                lang('ordered') . ': ' . $item_quantity . ' | ' .
                                lang('available') . ': ' . $product_details->quantity .
                                ')');
                            redirect('pos');
                        }
                    } elseif ($product_details->type == 'combo') {
                        $combo_items = $this->pos_model->getComboItemsByPID($product->id);
                        foreach ($combo_items as $combo_item) {
                            $cpr = $this->site->getProductByID($combo_item->id);
                            if ($cpr->quantity < $item_quantity) {
                                $this->session->set_flashdata('error', lang('quantity_low') . ' (' .
                                    lang('name') . ': ' . $cpr->name . ' | ' .
                                    lang('ordered') . ': ' . $item_quantity . ' x ' . $combo_item->qty . ' = ' . $item_quantity * $combo_item->qty . ' | ' .
                                    lang('available') . ': ' . $cpr->quantity .
                                    ') ' . $product_details->name);
                                redirect('pos');
                            }
                        }
                    }
                }
                
                $unit_price = $real_unit_price;

                $pr_discount = 0;
                if (isset($item_discount)) {
                    $discount = $item_discount;
                    $dpos     = strpos($discount, $percentage);
                    if ($dpos !== false) {
                        $pds         = explode('%', $discount);
                        $pr_discount = $this->tec->formatDecimal((($unit_price * (float)($pds[0])) / 100), 4);
                    } else {
                        $pr_discount = $this->tec->formatDecimal($discount);
                    }
                }
                $unit_price       = $this->tec->formatDecimal(($unit_price - $pr_discount), 4);
                $item_net_price   = $unit_price;
                $pr_item_discount = $this->tec->formatDecimal(($pr_discount * $item_quantity), 4);
                $product_discount += $pr_item_discount;

                $pr_item_tax = 0;
                $item_tax    = 0;
                $tax         = '';
                if (isset($product_details->tax) && $product_details->tax != 0) {
                    if ($product_details && $product_details->tax_method == 1) {
                        $item_tax = $this->tec->formatDecimal(((($unit_price) * $product_details->tax) / 100), 4);
                        $tax      = $product_details->tax . '%';
                    } else {
                        $item_tax = $this->tec->formatDecimal(((($unit_price) * $product_details->tax) / (100 + $product_details->tax)), 4);
                        $tax      = $product_details->tax . '%';
                        $item_net_price -= $item_tax;
                    }

                    $pr_item_tax = $this->tec->formatDecimal(($item_tax * $item_quantity), 4);
                }

                $product_tax += $pr_item_tax;
                $subtotal = (($item_net_price * $item_quantity) + $pr_item_tax);

                $products[] = (object) [
                    'product_id'      => $item_id,
                    'unit_id'         => $unit_id,
                    'quantity'        => $item_quantity,
                    'unit_price'      => $unit_price,
                    'net_unit_price'  => $item_net_price,
                    'discount'        => $item_discount,
                    'comment'         => $item_comment,
                    'item_discount'   => $pr_item_discount,
                    'tax'             => $tax,
                    'item_tax'        => $pr_item_tax,
                    'subtotal'        => $subtotal,
                    'real_unit_price' => $real_unit_price,
                    'cost'            => $product_cost,
                    'product_code'    => $product_code,
                    'product_name'    => $product_name,
                    'ordered'         => $item_ordered,
                ];

                $total += $item_net_price * $item_quantity;
            }
        }
        if (empty($products)) {
            $this->form_validation->set_rules('product', lang('order_items'), 'required');
        } else {
            krsort($products);
        }

        if ($this->input->post('order_discount')) {
            $order_discount_id = $this->input->post('order_discount');
            $opos              = strpos($order_discount_id, $percentage);
            if ($opos !== false) {
                $ods            = explode('%', $order_discount_id);
                $order_discount = $this->tec->formatDecimal(((($total + $product_tax) * (float)($ods[0])) / 100), 4);
            } else {
                $order_discount = $this->tec->formatDecimal($order_discount_id);
            }
        } else {
            $order_discount_id = null;
        }
        $total_discount = $this->tec->formatDecimal(($order_discount + $product_discount), 4);

        if ($this->input->post('order_tax')) {
            $order_tax_id = $this->input->post('order_tax');
            $opos         = strpos($order_tax_id, $percentage);
            if ($opos !== false) {
                $ots       = explode('%', $order_tax_id);
                $order_tax = $this->tec->formatDecimal(((($total + $product_tax - $order_discount) * (float)($ots[0])) / 100), 4);
            } else {
                $order_tax = $this->tec->formatDecimal($order_tax_id);
            }
        } else {
            $order_tax_id = null;
            $order_tax    = 0;
        }

        $total_tax   = $this->tec->formatDecimal(($product_tax + $order_tax), 4);
        $grand_total = $this->tec->formatDecimal(($this->tec->formatDecimal($total) + $total_tax - $order_discount), 4);
        $paid        = 0;
        $round_total = $this->tec->roundNumber($grand_total, $this->Settings->rounding);
        $rounding    = $this->tec->formatDecimal(($round_total - $grand_total));

        $data = (object) ['date' => $date,
            'customer_id'        => $customer_id,
            'customer_name'      => $customer,
            'total'              => $this->tec->formatDecimal($total),
            'product_discount'   => $this->tec->formatDecimal($product_discount, 4),
            'order_discount_id'  => $order_discount_id,
            'order_discount'     => $order_discount,
            'total_discount'     => $total_discount,
            'product_tax'        => $this->tec->formatDecimal($product_tax, 4),
            'order_tax_id'       => $order_tax_id,
            'order_tax'          => $order_tax,
            'total_tax'          => $total_tax,
            'grand_total'        => $grand_total,
            'total_items'        => $this->input->post('total_items'),
            'total_quantity'     => $this->input->post('total_quantity'),
            'rounding'           => $rounding,
            'paid'               => $paid,
            'created_by'         => $this->session->userdata('user_id'),
            'note'               => $note,
            'hold_ref'           => $this->input->post('hold_ref'),
        ];

        // $this->tec->print_arrays($data, $products);
        $store      = $this->site->getStoreByID($this->session->userdata('store_id'));
        $created_by = $this->site->getUser($this->session->userdata('user_id'));

        if ($bo == 'bill') {
            $printer = $this->site->getPrinterByID($this->Settings->printer);
            $this->load->library('escpos');
            $this->escpos->load($printer);
            $this->escpos->print_receipt($store, $data, $products, false, $created_by, false, true);
        } else {
            $order_printers = json_decode($this->Settings->order_printers);
            $this->load->library('escpos');
            foreach ($order_printers as $printer_id) {
                $printer = $this->site->getPrinterByID($printer_id);
                $this->escpos->load($printer);
                $this->escpos->print_order($store, $data, $products, $created_by);
            }
        }
    }

    public function print_receipt($id, $open_drawer = false)
    {
        $sale       = $this->pos_model->getSaleByID($id);
        $items      = $this->pos_model->getAllSaleItems($id);
        $payments   = $this->pos_model->getAllSalePayments($id);
        $store      = $this->site->getStoreByID($sale->store_id);
        $created_by = $this->site->getUser($sale->created_by);
        $printer    = $this->site->getPrinterByID($this->Settings->printer);
        $this->load->library('escpos');
        $this->escpos->load($printer);
        $this->escpos->print_receipt($store, $sale, $items, $payments, $created_by, $open_drawer);
    }

    public function print_register($re = null)
    {
        if ($this->session->userdata('register_id')) {
            $register    = $this->pos_model->registerData();
            $ccsales     = $this->pos_model->getRegisterCCSales();
            $cashsales   = $this->pos_model->getRegisterCashSales();
            $chsales     = $this->pos_model->getRegisterChSales();
            $other_sales = $this->pos_model->getRegisterOtherSales();
            $gcsales     = $this->pos_model->getRegisterGCSales();
            $stripesales = $this->pos_model->getRegisterStripeSales();
            $totalsales  = $this->pos_model->getRegisterSales();
            $expenses    = $this->pos_model->getRegisterExpenses();
            $user        = $this->site->getUser();

            $total_cash = $cashsales->paid ? ($cashsales->paid + $register->cash_in_hand) : $register->cash_in_hand;
            $total_cash -= ($expenses->total ? $expenses->total : 0);
            $info = [
                (object) ['label' => lang('opened_at'), 'value' => $this->tec->hrld($register->date)],
                (object) ['label' => lang('cash_in_hand'), 'value' => $register->cash_in_hand],
                (object) ['label' => lang('user'), 'value' => $user->first_name . ' ' . $user->last_name . ' (' . $user->email . ')'],
                (object) ['label' => lang('printed_at'),  'value' => $this->tec->hrld(date('Y-m-d H:i:s'))],
            ];

            $reg_totals = [
                (object) ['label' => lang('cash_sale'), 'value' => $this->tec->formatMoney($cashsales->paid ? $cashsales->paid : '0.00') . ' (' . $this->tec->formatMoney($cashsales->total ? $cashsales->total : '0.00') . ')'],
                (object) ['label' => lang('ch_sale'), 'value' => $this->tec->formatMoney($chsales->paid ? $chsales->paid : '0.00') . ' (' . $this->tec->formatMoney($chsales->total ? $chsales->total : '0.00') . ')'],
                (object) ['label' => lang('gc_sale'),  'value' => $this->tec->formatMoney($gcsales->paid ? $gcsales->paid : '0.00') . ' (' . $this->tec->formatMoney($gcsales->total ? $gcsales->total : '0.00') . ')'],
                (object) ['label' => lang('cc_sale'),  'value' => $this->tec->formatMoney($ccsales->paid ? $ccsales->paid : '0.00') . ' (' . $this->tec->formatMoney($ccsales->total ? $ccsales->total : '0.00') . ')'],
                (object) ['label' => lang('stripe'),  'value' => $this->tec->formatMoney($stripesales->paid ? $stripesales->paid : '0.00') . ' (' . $this->tec->formatMoney($stripesales->total ? $stripesales->total : '0.00') . ')'],
                (object) ['label' => lang('other_sale'),  'value' => $this->tec->formatMoney($other_sales->paid ? $other_sales->paid : '0.00') . ' (' . $this->tec->formatMoney($other_sales->total ? $other_sales->total : '0.00') . ')'],
                (object) ['label' => 'line',  'value' => ''],
                (object) ['label' => lang('total_sales'),  'value' => $this->tec->formatMoney($totalsales->paid ? $totalsales->paid : '0.00') . ' (' . $this->tec->formatMoney($totalsales->total ? $totalsales->total : '0.00') . ')'],
                (object) ['label' => lang('cash_in_hand'),  'value' => $this->tec->formatMoney($register->cash_in_hand)],
                (object) ['label' => lang('expenses'),  'value' => $this->tec->formatMoney($expenses->total ? $expenses->total : '0.00')],
                (object) ['label' => 'line',  'value' => ''],
                (object) ['label' => lang('total_cash'),  'value' => $this->tec->formatMoney($total_cash)],
            ];

            $data = (object) [
                'printer' => $this->Settings->local_printers ? '' : json_encode($printer),
                'logo'    => !empty($store->logo) ? base_url('uploads/' . $store->logo) : '',
                'heading' => lang('register_details'),
                'info'    => $info,
                'totals'  => $reg_totals,
            ];

            // $this->tec->print_arrays($data);
            if ($re == 1) {
                return $data;
            } elseif ($re == 2) {
                echo json_encode($data);
            } else {
                $printer = $this->site->getPrinterByID($this->Settings->printer);
                $this->load->library('escpos');
                $this->escpos->load($printer);
                $this->escpos->print_data($data);
                echo json_encode(true);
            }
        } else {
            echo json_encode(false);
        }
    }

    public function promotions()
    {
        $this->load->view($this->theme . 'promotions', $this->data);
    }

    public function receipt_img()
    {
        $data     = $this->input->post('img', true);
        $filename = date('Y-m-d-H-i-s-') . uniqid() . '.png';
        $cd       = !empty($this->input->post('cd')) ? true : false;
        $imgData  = str_replace(' ', '+', $data);
        $imgData  = base64_decode($imgData);
        file_put_contents('files/receipts/' . $filename, $imgData);
        $printer = $this->site->getPrinterByID($this->Settings->printer);
        $this->load->library('escpos');
        $this->escpos->load($printer);
        $this->escpos->print_img($filename, $cd);
        echo 'Printed Image  files/receipts/' . $filename;
        exit;
    }

    public function register_details()
    {
        $register_open_time        = $this->session->userdata('register_open_time');
        $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales']     = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales']   = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales']     = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['other_sales'] = $this->pos_model->getRegisterOtherSales($register_open_time);
        $this->data['gcsales']     = $this->pos_model->getRegisterGCSales($register_open_time);
        $this->data['stripesales'] = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['totalsales']  = $this->pos_model->getRegisterSales($register_open_time);
        $this->data['expenses']    = $this->pos_model->getRegisterExpenses($register_open_time);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function registers()
    {
        $this->data['error']     = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc                      = [['link' => base_url(), 'page' => lang('home')], ['link' => site_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('open_registers')]];
        $meta                    = ['page_title' => lang('open_registers'), 'bc' => $bc];
        $this->page_construct('pos/registers', $this->data, $meta);
    }

    public function shortcuts()
    {
        $this->load->view($this->theme . 'pos/shortcuts', $this->data);
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return false;
        }
        $this->load->model('stripe_payments');
        return $this->stripe_payments->get_balance();
    }

    public function suggestions()
{
    $term = $this->tec->parse_scale_barcode($this->input->get('term', true));
    $store_id = $this->input->get('store_id') ?? 1; // Use the store_id if passed, else default 1

    if (is_array($term)) {
        $bqty   = $term['weight'] ?? null;
        $bprice = $term['price']  ?? null;
        $term   = $term['item_code'];
        $rows   = $this->pos_model->getProductNames($term, null, true);
    }

    if (!$rows) {
        $bqty   = null;
        $bprice = null;
        $term   = $this->input->get('term', true);
        $rows   = $this->pos_model->getProductNames($term);
    }

    $pr = [];

    if ($rows) {
        // Get MYR exchange rate
        $exchange = $this->pos_model->getCurrencyRate('MYR');
        $myr_rate = ($exchange && $exchange->exchange_rate > 0) ? $exchange->exchange_rate : 1;

        foreach ($rows as $row) {
            unset($row->cost, $row->details);

            // ✅ Get quantity from stock batches and movements
            $available_qty = $this->pos_model->getProductAvailableQty($row->id, $store_id);

            // ✅ Default secondary qty null
            $qty_secondary = $this->pos_model->getProductAvailableSecondQty($row->id, $store_id);

            $delivery = $this->pos_model->getProductDelivery($row->id, $store_id);
            $cost = $this->pos_model->getProductCost($row->id, $store_id);
            
            $secondcost = $this->pos_model->getSecondCost($row->id, $store_id);
            $row->second_cost = $secondcost;

            $row->delivery = $delivery;
            $row->cost = $cost;


            // If barcode has weight/price, use it; else use available quantity
            $row->qty = $bqty ?: ($bprice ? $bprice / $row->store_price : $available_qty);
            $row->order_qty = 0;
            $row->mhs = 100;
            $row->available_qty = $available_qty;
            $row->qty_secondary = $qty_secondary; // ✅ Added field
            $row->primary_qty = !empty($row->is_dual_unit) && $bqty
                ? (float) $bqty
                : 0;
            $row->secondary_qty = 0;
            
            // ✅ Get quantity from stock batches and movements
            $whbaseqty = $this->pos_model->getProductAvailableQty($row->id, 2);

            // ✅ Default secondary qty null
            $whsecondqty = $this->pos_model->getProductAvailableSecondQty($row->id, 2);
            
            
            $row->whbaseqty = $whbaseqty;
            $row->whsecondqty = $whsecondqty; // ✅ Added field

            log_message('debug', "Product {$row->id}: whbaseqty={$whbaseqty}, whsecondqty={$whsecondqty}");

            $row->comment  = '';
            $row->discount = '0';

            // Convert base price to MYR
            $product_price_mmk = $row->store_price > 0 ? $row->store_price : $row->price;
            $row->price        = round($product_price_mmk / $myr_rate, 2);

            $row->real_unit_price = $row->price;
            $row->unit_price      = $row->tax ? ($row->price + (($row->price * $row->tax) / 100)) : $row->price;

            // Combo items
            $combo_items = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getComboItemsByPID($row->id);
            }

            // Unit prices
            $unit_prices = $this->pos_model->getProductUnitPrices($row->id);
            $unit_price_data = [];
            foreach ($unit_prices as $up) {
                $unit_price_data[$up->unit_id] = round($up->price / $myr_rate, 2);
            }

            // Unit conversions
            $unit_conversions = $this->pos_model->getProductUnitConversions($row->id);
            $unit_conversion_data = [];
            foreach ($unit_conversions as $uc) {
                $converted_stock = $available_qty;
                if ($uc->operator == "*") {
                    $converted_stock = $available_qty / $uc->operation_value;
                } elseif ($uc->operator == "/") {
                    $converted_stock = $available_qty * $uc->operation_value;
                }

                $unit_conversion_data[$uc->unit_id] = [
                    'operator' => $uc->operator,
                    'value'    => (float) $uc->operation_value,
                    'stock'    => $converted_stock
                ];
            }

            $pr[] = [
                'id'               => str_replace('.', '', microtime(true)),
                'item_id'          => $row->id,
                'label'            => $row->name . ' (' . $row->code . ')',
                'row'              => $row,
                'combo_items'      => $combo_items,
                'unit_prices'      => $unit_price_data,
                'unit_conversions' => $unit_conversion_data
            ];
        }

        echo json_encode($pr);
    } else {
        echo json_encode([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
    }
}




    public function today_sale()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales']     = $this->pos_model->getTodayCCSales();
        $this->data['cashsales']   = $this->pos_model->getTodayCashSales();
        $this->data['chsales']     = $this->pos_model->getTodayChSales();
        $this->data['other_sales'] = $this->pos_model->getTodayOtherSales();
        $this->data['gcsales']     = $this->pos_model->getTodayGCSales();
        $this->data['stripesales'] = $this->pos_model->getTodayStripeSales();
        $this->data['totalsales']  = $this->pos_model->getTodaySales();
        // $this->data['expenses'] = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function validate_gift_card($no)
    {
        if ($gc = $this->pos_model->getGiftCardByNO(urldecode($no))) {
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

    public function view($sale_id = null, $noprint = null)
    {
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getSaleByID($sale_id);
        if (!$this->session->userdata('store_id')) {
            $this->session->set_flashdata('warning', lang('please_select_store'));
            redirect('stores');
        } elseif ($this->session->userdata('store_id') != $inv->store_id) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('welcome');
        }
        $this->tec->view_rights($inv->created_by);
        $this->load->helper('text');
        $this->data['rows']       = $this->pos_model->getAllSaleItems($sale_id);
        $this->data['customer']   = $this->pos_model->getCustomerByID($inv->customer_id);
        $this->data['store']      = $this->site->getStoreByID($inv->store_id);
        $this->data['inv']        = $inv;
        $this->data['is_preorder'] = $inv->is_preorder;
        $this->data['sid']        = $sale_id;
        $this->data['noprint']    = $noprint;
        $this->data['modal']      = $noprint ? true : false;
        $this->data['payments']   = $this->pos_model->getAllSalePayments($sale_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['printer']    = $this->site->getPrinterByID($this->Settings->printer);
        $this->data['store']      = $this->site->getStoreByID($inv->store_id);
        $this->data['page_title'] = lang('invoice');
        $this->load->view($this->theme . 'pos/' . ($this->Settings->remote_printing != 1 && $this->Settings->print_img ? 'eview' : 'view'), $this->data);
    }

    public function view_bill()
    {
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }
}
