<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }

        $this->load->library('form_validation');
        $this->load->model('products_model');
        $this->load->model('adjustment_model');
    }

    private function app_redirect($path)
    {
        $is_app_mode = $this->input->get('app') == 1 || $this->input->post('app') == 1 || $this->input->get('mobile') == 1 || $this->input->post('mobile') == 1;

        if ($is_app_mode) {
            $app_language = $this->input->get('app_lang', true) ?: $this->input->post('app_lang', true);
            $path .= (strpos($path, '?') === false ? '?' : '&') . 'app=1';

            if ($app_language) {
                $path .= '&app_lang=' . rawurlencode($app_language);
            }
        }

        redirect($path);
    }

    public function add()
    {
        $this->form_validation->set_rules('code', lang('product_code'), 'trim|is_unique[products.code]|min_length[2]|max_length[50]|required|alpha_numeric');
        $this->form_validation->set_rules('name', lang('product_name'), 'required');
        $this->form_validation->set_rules('brand_id', 'Brand', 'trim|integer');
        $this->form_validation->set_rules('category', lang('category'), 'required');
        $this->form_validation->set_rules('price', lang('product_price'), 'required|is_numeric');

        if ($this->input->post('type') != 'service') {
            $this->form_validation->set_rules('cost', lang('product_cost'), 'required|is_numeric');
            $this->form_validation->set_rules('unit', lang('unit'), 'required');
        }

        $this->form_validation->set_rules('product_tax', lang('product_tax'), 'required|is_numeric');
        $this->form_validation->set_rules('alert_quantity', lang('alert_quantity'), 'is_numeric');

        if ($this->form_validation->run() == true) {

            $data = [
                'type' => $this->input->post('type'),
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'category_id' => $this->input->post('category'),
                'base_unit_id' => $this->input->post('unit'),
                'price' => $this->input->post('price'),
                'cost' => $this->input->post('cost'),
                'tax' => $this->input->post('product_tax'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'details' => $this->input->post('details'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'created_by' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            /* Brand ကို text မသိမ်းဘဲ brands table ၏ ID ကိုသာ သိမ်းမည်။ */
            if ($this->db->field_exists('brand_id', 'products')) {
                $brand_id = (int) $this->input->post('brand_id');
                $data['brand_id'] = $brand_id > 0 ? $brand_id : null;
            }

            $store_quantities = [];

            $store_quantities = [];

            $opening_qty_base = $this->input->post('opening_qty_base') !== null
                ? (float) $this->input->post('opening_qty_base')
                : 0;

            $opening_store_id = $this->input->post('opening_store_id');

            if (!$opening_store_id) {
                $opening_store_id = $this->session->userdata('store_id')
                    ? $this->session->userdata('store_id')
                    : 1;
            }

            if ($this->Settings->multi_store) {
                $stores = $this->site->getAllStores();

                foreach ($stores as $store) {
                    $qty = 0;

                    // Opening Stock ထည့်ထားတဲ့ store ကိုပဲ quantity ထည့်မယ်
                    if ((int) $store->id == (int) $opening_store_id) {
                        $qty = $opening_qty_base;
                    }

                    $store_quantities[] = [
                        'store_id' => $store->id,
                        'quantity' => $qty,
                        'price' => $this->input->post('price') ? $this->input->post('price') : 0,
                    ];
                }
            } else {
                $store_quantities[] = [
                    'store_id' => 1,
                    'quantity' => $opening_qty_base,
                    'price' => $this->input->post('price') ? $this->input->post('price') : 0,
                ];
            }

            $items = [];

            if ($this->input->post('type') == 'combo') {
                $combo_item_code = $this->input->post('combo_item_code');
                $combo_item_quantity = $this->input->post('combo_item_quantity');

                if (!empty($combo_item_code) && is_array($combo_item_code)) {
                    foreach ($combo_item_code as $r => $code) {
                        if (isset($combo_item_quantity[$r]) && $code != '') {
                            $items[] = [
                                'item_code' => $code,
                                'quantity' => $combo_item_quantity[$r],
                            ];
                        }
                    }
                }
            }

            if (!empty($_FILES['userfile']['name']) && $_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path'] = 'uploads/';
                $config['allowed_types'] = 'gif|jpg|jpeg|png|webp';
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('userfile')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    $this->app_redirect('products/add');
                }

                $photo = $this->upload->file_name;
                $data['image'] = $photo;

                $this->load->library('image_lib');

                $resize_config['image_library'] = 'gd2';
                $resize_config['source_image'] = 'uploads/' . $photo;
                $resize_config['new_image'] = 'uploads/thumbs/' . $photo;
                $resize_config['maintain_ratio'] = true;
                $resize_config['width'] = 110;
                $resize_config['height'] = 110;

                $this->image_lib->clear();
                $this->image_lib->initialize($resize_config);

                if (!$this->image_lib->resize()) {
                    $this->session->set_flashdata('error', $this->image_lib->display_errors());
                    $this->app_redirect('products/add');
                }
            }

            $add_result = $this->products_model->addProduct($data, $store_quantities, $items);

            if ($add_result) {

                /*
                 * Important:
                 * addProduct() က insert_id မပြန်ပေးဘဲ true ပဲပြန်ပေးနိုင်လို့
                 * product_id ကို product code နဲ့ ပြန်ရှာထားပါတယ်။
                 */
                if (is_numeric($add_result)) {
                    $product_id = (int) $add_result;
                } else {
                    $product = $this->db
                        ->select('id')
                        ->where('code', $data['code'])
                        ->get('products')
                        ->row();

                    $product_id = $product ? (int) $product->id : 0;
                }

                if ($product_id > 0) {
                    $this->save_unit_conversions_from_product_form($product_id);
                    $this->save_opening_stock_from_product_form($product_id);
                }

                $this->session->set_flashdata('message', lang('product_added'));

                if ($this->input->post('save_action') == 'add_another') {
                    $this->app_redirect('products/add');
                }

                $this->app_redirect('products');
            }
        }

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['stores'] = $this->site->getAllStores();
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['units'] = $this->site->getAllUnits();
        $this->data['brands'] = $this->db->table_exists('brands')
            ? $this->db->order_by('name', 'ASC')->get('brands')->result()
            : [];
        $this->data['page_title'] = lang('add_product');

        $bc = [
            ['link' => site_url('products'), 'page' => lang('products')],
            ['link' => '#', 'page' => lang('add_product')],
        ];

        $meta = [
            'page_title' => lang('add_product'),
            'bc' => $bc,
        ];

        $this->page_construct('products/add', $this->data, $meta);
    }

    public function barcode($product_code = null)
    {
        if ($this->input->get('code')) {
            $product_code = $this->input->get('code');
        }
        $data['product_details'] = $this->products_model->getProductByCode($product_code);
        $data['img'] = "<img src='" . base_url() . "index.php?products/gen_barcode&code={$product_code}' alt='{$product_code}' />";
        $this->load->view('barcode', $data);
    }

    public function delete($id = null)
{
    if (DEMO) {
        $this->session->set_flashdata(
            'error',
            lang('disabled_in_demo')
        );

        redirect($_SERVER['HTTP_REFERER'] ?? 'products');
    }

    if ($this->input->get('id')) {
        $id = $this->input->get('id', true);
    }

    $id = (int) $id;

    if (!$this->Admin) {
        $this->session->set_flashdata(
            'error',
            lang('access_denied')
        );

        redirect('pos');
    }

    if ($id <= 0) {
        $this->session->set_flashdata(
            'error',
            'ကုန်ပစ္စည်းအမှတ် မမှန်ကန်ပါ။'
        );

        redirect('products');
    }

    if ($this->products_model->deleteProduct($id)) {
        $this->session->set_flashdata(
            'message',
            lang('product_deleted')
        );

        redirect('products');
    }

    $error = $this->products_model->delete_error;

    if (
        isset($error['code']) &&
        (int) $error['code'] === 1451
    ) {
        $this->session->set_flashdata(
            'error',
            'ဤကုန်ပစ္စည်းကို အရောင်း၊ အဝယ်၊ Stock သို့မဟုတ် ကုန်ကျစရိတ်မှတ်တမ်းများတွင် အသုံးပြုပြီးသားဖြစ်သောကြောင့် ဖျက်၍မရပါ။'
        );
    } else {
        $this->session->set_flashdata(
            'error',
            'ကုန်ပစ္စည်းကို ဖျက်၍မရပါ။ သက်ဆိုင်ရာ စာရင်းများတွင် အသုံးပြုထားခြင်းရှိမရှိ စစ်ဆေးပါ။'
        );
    }

    redirect('products');
}

public function edit($id = null)
{
    /*
    |--------------------------------------------------------------------------
    | Product ID
    |--------------------------------------------------------------------------
    */
    if ($this->input->get('id')) {
        $id = $this->input->get('id', true);
    }

    $id = (int) $id;

    /*
    |--------------------------------------------------------------------------
    | Mobile/App Mode
    |--------------------------------------------------------------------------
    | GET သို့မဟုတ် POST မှ app=1 ရလာလျှင် App mode ဖြစ်သည်။
    */
    $is_app_mode =
        (int) $this->input->post('app') === 1 ||
        (int) $this->input->get('app') === 1;

    $app_language = trim((string) (
        $this->input->post('app_lang', true)
        ?: $this->input->get('app_lang', true)
        ?: 'myanmar'
    ));

    /*
    |--------------------------------------------------------------------------
    | Redirect URLs
    |--------------------------------------------------------------------------
    */
    $products_url = 'products';
    $edit_url = 'products/edit/' . $id;

    if ($is_app_mode) {
        $app_query =
            '?app=1&app_lang=' .
            rawurlencode($app_language);

        $products_url .= $app_query;
        $edit_url .= $app_query;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Product
    |--------------------------------------------------------------------------
    */
    $product = $this->site->getProductByID($id);

    if (!$product) {
        $this->session->set_flashdata(
            'error',
            lang('product_not_found')
        );

        redirect($products_url);
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    | မူလ Product Code ကို မပြောင်းထားလျှင် is_unique မစစ်ပါ။
    */
    $code_rules =
        'trim|min_length[2]|max_length[50]|required|alpha_numeric';

    $posted_code = $this->input->post('code');

    if (
        $posted_code !== null &&
        trim((string) $posted_code) !==
            trim((string) $product->code)
    ) {
        $code_rules .= '|is_unique[products.code]';
    }

    $this->form_validation->set_rules(
        'code',
        lang('product_code'),
        $code_rules
    );

    $this->form_validation->set_rules(
        'name',
        lang('product_name'),
        'required'
    );

    $this->form_validation->set_rules(
        'brand_id',
        'Brand',
        'trim|integer'
    );

    $this->form_validation->set_rules(
        'category',
        lang('category'),
        'required'
    );

    /*
     * Add/Edit View က Base Unit ကို "unit" အမည်ဖြင့် POST လုပ်သည်။
     */
    $this->form_validation->set_rules(
        'unit',
        lang('unit'),
        'required|integer'
    );

    $this->form_validation->set_rules(
        'product_tax',
        lang('product_tax'),
        'required|is_numeric'
    );

    $this->form_validation->set_rules(
        'alert_quantity',
        lang('alert_quantity'),
        'is_numeric'
    );

    $this->form_validation->set_rules(
        'is_dual_unit',
        'Dual Unit',
        'required|in_list[0,1]'
    );

    /* Dual ဖြစ်လျှင် Count Unit လိုပြီး Base Unit နှင့် မတူရပါ။ */
    if ((int) $this->input->post('is_dual_unit') === 1) {
        $this->form_validation->set_rules(
            'secondary_unit_id',
            'Independent Count Unit',
            'required|integer|differs[unit]'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */
    if ($this->form_validation->run() === true) {

        $base_unit_id = (int) $this->input->post('unit');
        $is_dual_unit = (int) $this->input->post('is_dual_unit') === 1;
        $secondary_unit_id = $is_dual_unit
            ? (int) $this->input->post('secondary_unit_id')
            : null;

        $data = [
            'type' =>
                $this->input->post('type'),

            'code' =>
                trim((string) $this->input->post('code')),

            'name' =>
                $this->input->post('name'),

            'category_id' =>
                $this->input->post('category'),

            'base_unit_id' =>
                $base_unit_id,

            'secondary_unit_id' =>
                $secondary_unit_id,

            'is_dual_unit' =>
                $is_dual_unit ? 1 : 0,

            'price' =>
                $this->input->post('price'),

            'cost' =>
                $this->input->post('cost'),

            'tax' =>
                $this->input->post('product_tax'),

            'tax_method' =>
                $this->input->post('tax_method'),

            'alert_quantity' =>
                $this->input->post('alert_quantity'),

            'details' =>
                $this->input->post('details'),

            'barcode_symbology' =>
                $this->input->post('barcode_symbology'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Brand
        |--------------------------------------------------------------------------
        | products table မှာ brand_id column ရှိမှ update လုပ်မည်။
        */
        if (
            $this->input->post('brand_id') !== null &&
            $this->db->field_exists(
                'brand_id',
                'products'
            )
        ) {
            $brand_id =
                (int) $this->input->post('brand_id');

            $data['brand_id'] =
                $brand_id > 0
                    ? $brand_id
                    : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Store Quantities
        |--------------------------------------------------------------------------
        | Product Edit သည် stock adjustment မဟုတ်ပါ။ လက်ရှိ stock ကို
        | updateStoreStock() သို့ ပြန်ပို့လျှင် purchase stock အပေါ် ထပ်ပေါင်းပြီး
        | quantity ၂ ဆဖြစ်သွားမည်။ Stock ကို Purchase / Opening Stock /
        | Adjustment flow များမှသာ ပြင်ရမည်။
        */
        $store_quantities = [];

        /*
        |--------------------------------------------------------------------------
        | Combo Items
        |--------------------------------------------------------------------------
        */
        $items = [];

        if ($this->input->post('type') === 'combo') {

            $combo_codes =
                $this->input->post(
                    'combo_item_code'
                );

            $combo_qtys =
                $this->input->post(
                    'combo_item_quantity'
                );

            if (
                is_array($combo_codes) &&
                is_array($combo_qtys)
            ) {
                $combo_count = min(
                    count($combo_codes),
                    count($combo_qtys)
                );

                for (
                    $i = 0;
                    $i < $combo_count;
                    $i++
                ) {
                    if (
                        $combo_codes[$i] !== '' &&
                        $combo_qtys[$i] !== ''
                    ) {
                        $items[] = [
                            'item_code' =>
                                $combo_codes[$i],

                            'quantity' =>
                                $combo_qtys[$i],
                        ];
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Unit Conversions
        |--------------------------------------------------------------------------
        */
        $conversions = [];

        $unit_ids =
            $this->input->post(
                'conversion_unit_id'
            );

        $operators =
            $this->input->post('operator');

        $values =
            $this->input->post(
                'operation_value'
            );

        if (
            is_array($unit_ids) &&
            is_array($operators) &&
            is_array($values)
        ) {

            $conversion_count =
                count($unit_ids);

            for (
                $i = 0;
                $i < $conversion_count;
                $i++
            ) {
                $conversion_unit_id = isset($unit_ids[$i])
                    ? (int) $unit_ids[$i]
                    : 0;
                $operator = isset($operators[$i])
                    ? trim((string) $operators[$i])
                    : '';
                $operation_value = isset($values[$i])
                    ? (float) $values[$i]
                    : 0;

                if (
                    $conversion_unit_id <= 0 ||
                    $conversion_unit_id === $base_unit_id ||
                    !in_array($operator, ['*', '/'], true) ||
                    $operation_value <= 0 ||
                    (
                        $is_dual_unit &&
                        $conversion_unit_id === $secondary_unit_id
                    )
                ) {
                    continue;
                }

                /* Unit တစ်ခုကို conversion တစ်ကြောင်းသာ သိမ်းမည်။ */
                $conversions[$conversion_unit_id] = [
                    'unit_id' => $conversion_unit_id,
                    'operator' => $operator,
                    'operation_value' => $operation_value,
                ];
            }
        }

        $conversions = array_values($conversions);
        $data['has_unit_conversion'] = !empty($conversions) ? 1 : 0;

        /*
        |--------------------------------------------------------------------------
        | Product Photo
        |--------------------------------------------------------------------------
        | Photo အသစ်မရွေးလျှင် null ဖြစ်ပြီး Model က လက်ရှိ photo ကို
        | ဆက်ထားမည်။
        */
        $photo = null;

        if (
            isset($_FILES['userfile']) &&
            !empty($_FILES['userfile']['name'])
        ) {
            $this->load->library('upload');

            $upload_config = [
                'upload_path' =>
                    'uploads/',

                'allowed_types' =>
                    'gif|jpg|jpeg|png',

                'overwrite' =>
                    false,

                'encrypt_name' =>
                    true,
            ];

            $this->upload->initialize(
                $upload_config
            );

            if (
                !$this->upload
                    ->do_upload('userfile')
            ) {
                $upload_error =
                    $this->upload
                        ->display_errors();

                $this->session
                    ->set_flashdata(
                        'error',
                        $upload_error
                    );

                redirect($edit_url);
                return;
            }

            $upload_data =
                $this->upload->data();

            $photo =
                $upload_data['file_name'];

            /*
             * Thumbnail
             */
            $this->load->library(
                'image_lib'
            );

            $image_config = [
                'image_library' =>
                    'gd2',

                'source_image' =>
                    'uploads/' . $photo,

                'new_image' =>
                    'uploads/thumbs/' . $photo,

                'maintain_ratio' =>
                    true,

                'width' =>
                    110,

                'height' =>
                    110,
            ];

            $this->image_lib->initialize(
                $image_config
            );

            if (
                !$this->image_lib->resize()
            ) {
                log_message(
                    'error',
                    'Product thumbnail creation failed. ' .
                    'Product ID: ' . $id .
                    ' | Error: ' .
                    $this->image_lib->display_errors(
                        '',
                        ''
                    )
                );
            }

            $this->image_lib->clear();
        }

        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */
        $updated =
            $this->products_model
                ->updateProduct(
                    $id,
                    $data,
                    $store_quantities,
                    $items,
                    $photo,
                    $conversions
                );

        if ($updated) {

            $this->session->set_flashdata(
                'message',
                lang('product_updated')
            );

            /*
             * App mode ဖြစ်လျှင်:
             * products?app=1&app_lang=myanmar
             *
             * Normal Web ဖြစ်လျှင်:
             * products
             */
            redirect($products_url);
            return;
        }

        $this->session->set_flashdata(
            'error',
            lang('product_not_updated')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Existing Data for Edit View
    |--------------------------------------------------------------------------
    */
    $this->data['product'] =
        $product;

    $this->data['stores'] =
        $this->site->getAllStores();

    $this->data['stores_quantities'] =
        $this->Settings->multi_store
            ? $this->products_model
                ->getStoresQuantity($id)
            : $this->products_model
                ->getStoreQuantity($id);

    $this->data['categories'] =
        $this->site->getAllCategories();

    $this->data['units'] =
        $this->site->getAllUnits();

    $this->data['brands'] =
        $this->db->table_exists('brands')
            ? $this->db
                ->order_by('name', 'ASC')
                ->get('brands')
                ->result()
            : [];

    $this->data['product_conversions'] =
        $this->products_model
            ->getProductConversions($id);

    $this->data['product_unit_prices'] =
        $this->products_model
            ->getProductUnitPrices($id);

    $this->data['page_title'] =
        lang('edit_product');

    $this->data['error'] =
        validation_errors()
            ? validation_errors()
            : $this->session
                ->flashdata('error');

    /*
    |--------------------------------------------------------------------------
    | Page Construct
    |--------------------------------------------------------------------------
    */
    $bc = [
        [
            'link' =>
                site_url($products_url),

            'page' =>
                lang('products'),
        ],
        [
            'link' => '#',
            'page' => lang('edit_product'),
        ],
    ];

    $meta = [
        'page_title' =>
            lang('edit_product'),

        'bc' =>
            $bc,
    ];

    $this->page_construct(
        'products/edit',
        $this->data,
        $meta
    );
}



    public function gen_barcode($product_code = null, $bcs = 'code128', $height = 60, $text = 1)
    {
        return $this->tec->barcode($product_code, $bcs, $height, $text);
    }

    public function get_products($store_id)
{
    $this->load->library('datatables');

    $store_id = (int) $store_id;

    /*
     * =========================================================
     * Custom AND Filters
     * View column indexes:
     *
     * 0 = ID
     * 1 = Image
     * 2 = Code
     * 3 = Name
     * 4 = Category
     * 5 = Base Quantity
     * 6 = Base Unit
     * 7 = Purchase Price
     * 8 = Selling Price
     * 9 = Actions
     * =========================================================
     */

    $dt_columns = $this->input->post('columns', true);

    $code_filter     = '';
    $name_filter     = '';
    $quantity_filter = '';
    $unit_filter     = '';

    if (is_array($dt_columns)) {

        // Code
        if (isset($dt_columns[2]['search']['value'])) {
            $code_filter = trim(
                (string) $dt_columns[2]['search']['value']
            );
        }

        // Name
        if (isset($dt_columns[3]['search']['value'])) {
            $name_filter = trim(
                (string) $dt_columns[3]['search']['value']
            );
        }

        // Base Quantity
        if (isset($dt_columns[5]['search']['value'])) {
            $quantity_filter = trim(
                (string) $dt_columns[5]['search']['value']
            );
        }

        // Base Unit
        if (isset($dt_columns[6]['search']['value'])) {
            $unit_filter = trim(
                (string) $dt_columns[6]['search']['value']
            );
        }
    }


    /*
     * Older DataTables version fallback
     */
    if ($code_filter === '') {
        $code_filter = trim(
            (string) $this->input->post('sSearch_2', true)
        );
    }

    if ($name_filter === '') {
        $name_filter = trim(
            (string) $this->input->post('sSearch_3', true)
        );
    }

    if ($quantity_filter === '') {
        $quantity_filter = trim(
            (string) $this->input->post('sSearch_5', true)
        );
    }

    if ($unit_filter === '') {
        $unit_filter = trim(
            (string) $this->input->post('sSearch_6', true)
        );
    }


    /*
     * =========================================================
     * IMPORTANT
     *
     * CI DataTables library က column index ကို
     * SQL SELECT column index နဲ့ မှားပြီး auto-filter
     * မလုပ်စေရန် custom filter columns ကို clear လုပ်ထားပါတယ်။
     * =========================================================
     */

    foreach (array(2, 3, 5, 6) as $index) {

        if (
            isset($_POST['columns'][$index]['search']['value'])
        ) {
            $_POST['columns'][$index]['search']['value'] = '';
        }

        $legacy_key = 'sSearch_' . $index;

        if (isset($_POST[$legacy_key])) {
            $_POST[$legacy_key] = '';
        }
    }


    /*
     * =========================================================
     * Stock SUM
     * =========================================================
     */

    $stock_sql = "
        SELECT
            product_id,
            SUM(IFNULL(qty_base, 0)) AS qty_base,
            SUM(IFNULL(qty_secondary, 0)) AS qty_secondary
        FROM tec_stock_batches
        WHERE store_id = {$store_id}
        GROUP BY product_id
    ";


    /*
     * =========================================================
     * Main Query
     * =========================================================
     */

    $this->datatables
        ->select("
            tec_products.id AS pid,
            tec_products.image,
            tec_products.code,
            tec_products.name AS pname,
            tec_products.type,
            tec_categories.name AS cname,

            tec_products.is_dual_unit,
            tec_products.base_unit_id,
            tec_products.secondary_unit_id,

            pu1.name AS base_unit_name,
            pu2.name AS secondary_unit_name,

            IFNULL(stock.qty_base, 0) AS base_quantity,
            IFNULL(stock.qty_secondary, 0) AS secondary_quantity,

            tec_products.tax,
            tec_products.tax_method,
            tec_products.cost,

            /*
             * Base unit selling price.
             * product_unit_prices တွင် သိမ်းထားသောဈေးကို ဦးစားပေးပြီး
             * မရှိသေးပါက products.price ကို fallback သုံးမည်။
             */
            COALESCE(
                (
                    SELECT pup.price
                    FROM tec_product_unit_prices pup
                    WHERE pup.product_id = tec_products.id
                      AND pup.unit_id = tec_products.base_unit_id
                    ORDER BY pup.id DESC
                    LIMIT 1
                ),
                tec_products.price,
                0
            ) AS selling_price,

            (
                SELECT sb2.cost_per_base
                FROM tec_stock_batches sb2
                WHERE sb2.product_id = tec_products.id
                  AND sb2.store_id = {$store_id}
                ORDER BY sb2.id DESC
                LIMIT 1
            ) AS base_price,

            (
                SELECT sb3.cost_per_base
                FROM tec_stock_batches sb3
                WHERE sb3.product_id = tec_products.id
                  AND sb3.store_id = {$store_id}
                ORDER BY sb3.id DESC
                LIMIT 1
            ) AS secondary_price,

            tec_products.barcode_symbology
        ", false)

        ->from('tec_products')

        ->join(
            'tec_categories',
            'tec_categories.id = tec_products.category_id',
            'left'
        )

        ->join(
            'tec_product_units AS pu1',
            'pu1.id = tec_products.base_unit_id',
            'left'
        )

        ->join(
            'tec_product_units AS pu2',
            'pu2.id = tec_products.secondary_unit_id',
            'left'
        )

        ->join(
            "({$stock_sql}) AS stock",
            'stock.product_id = tec_products.id',
            'left',
            false
        );


    /*
     * =========================================================
     * TRUE AND SEARCH
     * =========================================================
     *
     * Code
     * AND Name
     * AND Quantity
     * AND Unit
     *
     * Empty filter ကို မထည့်ပါ။
     */

    if ($code_filter !== '') {

        $this->datatables->where(
            'tec_products.code LIKE',
            '%' . $code_filter . '%'
        );
    }


    if ($name_filter !== '') {

        $this->datatables->where(
            'tec_products.name LIKE',
            '%' . $name_filter . '%'
        );
    }


    if ($quantity_filter !== '') {

        $this->datatables->where(
            'CAST(IFNULL(stock.qty_base, 0) AS CHAR) LIKE',
            '%' . $quantity_filter . '%'
        );
    }


    if ($unit_filter !== '') {

        $this->datatables->where(
            'pu1.name LIKE',
            '%' . $unit_filter . '%'
        );
    }


    /*
     * =========================================================
     * Actions
     * =========================================================
     */

    $this->datatables->add_column(
        'Actions',
        "
        <div class='text-center'>
            <div class='btn-group'>

                <button type='button'
                        class='btn btn-primary dropdown-toggle'
                        data-toggle='dropdown'>
                    <i class='fa fa-cog'></i>
                    " . lang('actions') . "
                    <span class='caret'></span>
                </button>

                <ul class='dropdown-menu dropdown-menu-right'>

                    <li>
                        <a href='" . site_url('products/edit/$1') . "'
                           class='tip'
                           title='" . lang('edit') . "'>
                            <i class='fa fa-edit'></i>
                            " . lang('edit') . "
                        </a>
                    </li>

                    <li>
                        <a href='" . site_url('products/view/$1') . "'
                           class='tip'
                           title='" . lang('view') . "'>
                            <i class='fa fa-eye'></i>
                            " . lang('view') . "
                        </a>
                    </li>

                    <li>
                        <a href='" . base_url('uploads/$2') . "'
                           class='tip image'
                           id='$4 ($3)'
                           title='" . lang('view_image') . "'
                           target='_blank'>
                            <i class='fa fa-image'></i>
                            " . lang('view_image') . "
                        </a>
                    </li>

                    <li>
                        <a href='" . site_url('products/delete/$1') . "'
                           class='tip'
                           title='" . lang('delete') . "'>
                            <i class='fa fa-trash'></i>
                            " . lang('delete') . "
                        </a>
                    </li>

                </ul>

            </div>
        </div>
        ",
        'pid, image, code, pname, barcode_symbology'
    );


    $this->datatables
        ->unset_column('pid')
        ->unset_column('barcode_symbology');


    echo $this->datatables->generate();
}








    public function import()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                redirect('pos');
            }

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');

                $config['upload_path'] = 'uploads/';
                $config['allowed_types'] = 'csv';
                $config['max_size'] = '500';
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect('products/import');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle = fopen('uploads/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                array_shift($arrResult);

                $keys = ['code', 'name', 'cost', 'tax', 'price', 'category'];

                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                if (sizeof($final) > 1001) {
                    $this->session->set_flashdata('error', lang('more_than_allowed'));
                    redirect('products/import');
                }

                foreach ($final as $csv_pr) {
                    if ($this->products_model->getProductByCode($csv_pr['code'])) {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $csv_pr['code'] . '). ' . lang('code_already_exist'));
                        redirect('products/import');
                    }
                    if (!is_numeric($csv_pr['tax'])) {
                        $this->session->set_flashdata('error', lang('check_product_tax') . ' (' . $csv_pr['tax'] . '). ' . lang('tax_not_numeric'));
                        redirect('products/import');
                    }
                    if (!($category = $this->site->getCategoryByCode($csv_pr['category']))) {
                        $this->session->set_flashdata('error', lang('check_category') . ' (' . $csv_pr['category'] . '). ' . lang('category_x_exist'));
                        redirect('products/import');
                    }
                    $data[] = [
                        'type' => 'standard',
                        'code' => $csv_pr['code'],
                        'name' => $csv_pr['name'],
                        'cost' => $csv_pr['cost'],
                        'tax' => $csv_pr['tax'],
                        'price' => $csv_pr['price'],
                        'category_id' => $category->id,
                    ];
                }
                //print_r($data); die();
            }
        }

        if ($this->form_validation->run() == true && $this->products_model->add_products($data)) {
            $this->session->set_flashdata('message', lang('products_added'));
            redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['page_title'] = lang('import_products');
            $bc = [['link' => site_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('import_products')]];
            $meta = ['page_title' => lang('import_products'), 'bc' => $bc];
            $this->page_construct('products/import', $this->data, $meta);
        }
    }

    public function index()
    {
        $stores = $this->site->getAllStores();
        if ($this->input->get('store_id') && !$this->session->userdata('has_store_id')) {
            $this->data['store'] = $this->site->getStoreByID($this->input->get('store_id', true));
        } elseif ($this->session->userdata('store_id')) {
            $this->data['store'] = $this->site->getStoreByID($this->session->userdata('store_id'));
        } else {
            $this->data['store'] = current($stores);
        }
        $this->data['stores'] = $stores;
        $this->data['units'] = $this->site->getAllUnits();
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('products');
        $bc = [['link' => '#', 'page' => lang('products')]];
        $meta = ['page_title' => lang('products'), 'bc' => $bc];
        $this->page_construct('products/index', $this->data, $meta);
    }


    public function price()
    {
        $stores = $this->site->getAllStores();
        if ($this->input->get('store_id') && !$this->session->userdata('has_store_id')) {
            $this->data['store'] = $this->site->getStoreByID($this->input->get('store_id', true));
        } elseif ($this->session->userdata('store_id')) {
            $this->data['store'] = $this->site->getStoreByID($this->session->userdata('store_id'));
        } else {
            $this->data['store'] = current($stores);
        }
        $this->data['stores'] = $stores;
        $this->data['units'] = $this->site->getAllUnits();
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('price');
        $bc = [['link' => '#', 'page' => lang('price')]];
        $meta = ['page_title' => lang('price'), 'bc' => $bc];
        $this->page_construct('products/price', $this->data, $meta);
    }


    public function print_barcodes()
    {
        $limit = 10;
        $this->load->helper('pagination');
        $page = $this->input->get('page');
        $total = $this->products_model->products_count();
        $info = ['page' => $page, 'total' => ceil($total / $limit)];
        $pagination = pagination('products/print_barcodes', $total, $limit, true);
        $products = $this->products_model->fetch_products($limit, (!empty($page) ? (($page - 1) * $limit) : 0));
        $r = 1;
        $html = '';
        $html .= '<table class="table table-bordered table-centered mb0">
        <tbody><tr>';
        foreach ($products as $pr) {
            if ($r != 1) {
                $rw = (bool) ($r & 1);
                $html .= $rw ? '</tr><tr>' : '';
            }
            $html .= '<td><h4>' . $this->Settings->site_name . '</h4><strong>' . $pr->name . '</strong><br>' . $this->product_barcode($pr->code, $pr->barcode_symbology, 60) . '<br><span class="price">' . lang('price') . ': ' . $this->Settings->currency_prefix . ' ' . $this->tec->formatMoney($pr->price) . '</span></td>';
            $r++;
        }
        $html .= '</tr></tbody>
        </table>';
        $this->data['links'] = $pagination;
        $this->data['html'] = $html;
        $this->data['page_title'] = lang('print_barcodes');
        $this->load->view($this->theme . 'products/print_barcodes', $this->data);
    }

    public function print_labels()
    {
        $limit = 10;
        $this->load->helper('pagination');
        $page = $this->input->get('page');
        $total = $this->products_model->products_count();
        $info = ['page' => $page, 'total' => ceil($total / $limit)];
        $pagination = pagination('products/print_labels', $total, $limit, true);
        $products = $this->products_model->fetch_products($limit, (!empty($page) ? (($page - 1) * $limit) : 0));
        $html = '';
        foreach ($products as $pr) {
            $html .= '<div class="text-center labels break-after"><strong>' . $pr->name . '</strong><br>' . $this->product_barcode($pr->code, $pr->barcode_symbology, 25) . '<br><span class="price">' . lang('price') . ': ' . $this->Settings->currency_prefix . ' ' . $this->tec->formatMoney($pr->price) . '</span></div>';
        }
        $this->data['links'] = $pagination;
        $this->data['html'] = $html;
        $this->data['page_title'] = lang('print_labels');
        $this->load->view($this->theme . 'products/print_labels', $this->data);
    }

    public function product_barcode($product_code = null, $bcs = 'code128', $height = 60)
    {
        if ($this->input->get('code')) {
            $product_code = $this->input->get('code');
        }
        return $this->tec->barcode($product_code, $bcs, $height);
    }

    public function single_barcode($product_id = null)
    {
        $product = $this->site->getProductByID($product_id);

        $html = '';
        $html .= '<table class="table table-bordered table-centered mb0">
        <tbody><tr>';
        if ($product->quantity > 0) {
            for ($r = 1; $r <= $product->quantity; $r++) {
                if ($r != 1) {
                    $rw = (bool) ($r & 1);
                    $html .= $rw ? '</tr><tr>' : '';
                }
                $html .= '<td><h4>' . $this->Settings->site_name . '</h4><strong>' . $product->name . '</strong><br>' . $this->product_barcode($product->code, $product->barcode_symbology, 60) . ' <br><span class="price">' . lang('price') . ': ' . $this->Settings->currency_prefix . ' ' . $this->tec->formatMoney($product->price) . '</span></td>';
            }
        } else {
            for ($r = 1; $r <= 10; $r++) {
                if ($r != 1) {
                    $rw = (bool) ($r & 1);
                    $html .= $rw ? '</tr><tr>' : '';
                }
                $html .= '<td><h4>' . $this->Settings->site_name . '</h4><strong>' . $product->name . '</strong><br>' . $this->product_barcode($product->code, $product->barcode_symbology, 60) . ' <br><span class="price">' . lang('price') . ': ' . $this->Settings->currency_prefix . ' ' . $this->tec->formatMoney($product->price) . '</span></td>';
            }
        }
        $html .= '</tr></tbody>
        </table>';

        $this->data['html'] = $html;
        $this->data['page_title'] = lang('print_barcodes') . ' (' . $product->name . ')';
        $this->load->view($this->theme . 'products/single_barcode', $this->data);
    }

    public function single_label($product_id = null, $warehouse_id = null)
    {
        $product = $this->site->getProductByID($product_id);
        $html = '';
        if ($product->quantity > 0) {
            for ($r = 1; $r <= $product->quantity; $r++) {
                $html .= '<div class="text-center labels"><strong>' . $product->name . '</strong><br>' . $this->product_barcode($product->code, $product->barcode_symbology, 25) . ' <br><span class="price">' . lang('price') . ': ' . $this->Settings->currency_prefix . ' ' . $this->tec->formatMoney($product->price) . '</span></div>';
            }
        } else {
            for ($r = 1; $r <= 10; $r++) {
                $html .= '<div class="text-center labels"><strong>' . $product->name . '</strong><br>' . $this->product_barcode($product->code, $product->barcode_symbology, 25) . ' <br><span class="price">' . lang('price') . ': ' . $this->Settings->currency_prefix . ' ' . $this->tec->formatMoney($product->price) . '</span></div>';
            }
        }
        $this->data['html'] = $html;
        $this->data['page_title'] = lang('print_labels') . ' (' . $product->name . ')';
        $this->load->view($this->theme . 'products/single_label', $this->data);
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);

        $rows = $this->products_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $pr[] = ['id' => str_replace('.', '', microtime(true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row];
            }
            echo json_encode($pr);
        } else {
            echo json_encode([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function view($id = null)
{
    $id = (int) $id;

    $product = $this->site->getProductByID($id);

    if (!$product) {
        $this->session->set_flashdata(
            'error',
            'Product not found.'
        );

        redirect('products');
    }

    $this->data['product'] = $product;

    $this->data['category'] =
        $this->site->getCategoryByID($product->category_id);

    $this->data['unit'] =
        $this->site->getUnitByID($product->base_unit_id);

    // Unit Conversion
    $this->data['unit_conversions'] =
        $this->products_model->getProductUnitConversions($id);

    // Unit Selling Prices
    $this->data['unit_prices'] =
        $this->products_model->getProductUnitPrices($id);

    // Created By
    $this->data['creator'] = null;

    if (!empty($product->created_by)) {
        $this->data['creator'] =
            $this->products_model->getUserByID($product->created_by);
    }

    // Combo
    $this->data['combo_items'] =
        $product->type == 'combo'
            ? $this->products_model->getComboItemsByPID($id)
            : null;

    // Purchase Batch History
    $this->data['batches'] =
        $this->products_model->getProductPurchaseBatches($id);
        
    // Current Stock from Stock Batches
    $store_id = $this->session->userdata('store_id');
    
    $this->data['current_stock'] =
        $this->products_model->getProductCurrentStock(
            $id,
            $store_id
        );

    $this->load->view(
        $this->theme . 'products/view',
        $this->data
    );
}

    public function units()
    {
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['units'] = $this->products_model->getAllUnits();
        $this->data['page_title'] = lang('unit');
        $bc = [['link' => '#', 'page' => lang('unit')]];
        $meta = ['page_title' => lang('unit'), 'bc' => $bc];
        $this->page_construct('products/units', $this->data, $meta);
    }

    public function add_unit()
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->form_validation->set_rules('name', lang('name'), 'required');

        if ($this->form_validation->run() == true) {

            $data = [
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
            ];

            if ($this->products_model->insertUnit($data)) {
                $this->session->set_flashdata('message', lang('unit_added'));
                redirect('products/units');
            } else {
                $this->session->set_flashdata(
                    'error',
                    'Unit ထည့်သွင်းလို့မရပါ။ Name သို့မဟုတ် Code ထပ်နေပါသည်။'
                );
                redirect('products/add_unit');
            }
        }

        $this->data['error'] = validation_errors()
            ? validation_errors()
            : $this->session->flashdata('error');

        $this->data['page_title'] = lang('add_unit');

        $bc = [
            ['link' => site_url('products'), 'page' => lang('units')],
            ['link' => '#', 'page' => lang('add_unit')]
        ];

        $meta = [
            'page_title' => lang('add_unit'),
            'bc' => $bc
        ];

        $this->page_construct('products/add_unit', $this->data, $meta);
    }

    public function edit_unit($id = null)
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('name', lang('name'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
            ];

        }

        if ($this->form_validation->run() == true && $this->products_model->updateUnit($id, $data)) {
            $this->session->set_flashdata('message', lang('unit_updated'));
            redirect('products/units');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['units'] = $this->products_model->getUnitByID($id);
            $this->data['page_title'] = lang('edit_unit');
            $bc = [['link' => site_url('units'), 'page' => lang('unit')], ['link' => '#', 'page' => lang('edit_unit')]];
            $meta = ['page_title' => lang('edit_unit'), 'bc' => $bc];
            $this->page_construct('products/edit_unit', $this->data, $meta);
        }
    }


    public function delete_unit($id = null)
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

        if (!$id) {
            $this->session->set_flashdata('error', 'Unit ID မရှိပါ။');
            redirect('products/units');
        }

        if ($this->products_model->deleteUnit($id)) {
            $this->session->set_flashdata('message', lang('unit_deleted'));
        } else {
            $this->session->set_flashdata(
                'error',
                'ဒီ Unit ကို ဖျက်လို့မရပါ။ Product / Purchase / Sale ထဲမှာ အသုံးပြုထားနိုင်ပါတယ်။'
            );
        }

        redirect('products/units');
    }

    public function get_units()
    {
        $this->load->library('datatables');
        $this->datatables->select('id, name, code');
        $this->datatables->from('product_units');
        $this->datatables->add_column(
            'Actions',
            "<div class='text-center'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-right'>
                        
                        <li>
                            <a href='#' class='tip edit-unit' data-id='$1' data-name=\"$2\" data-code=\"$3\" title='" . lang('edit') . "'>
                                <i class='fa fa-edit'></i> " . lang('edit') . "
                            </a>
                        </li>
                        <li>
                            <a href='" . site_url('products/delete_unit/$1') . "' class='tip text-danger' title='" . lang('delete') . "' onClick=\"return confirm('" . lang('alert_x_category') . "')\">
                                <i class='fa-solid fa-trash'></i> " . lang('delete') . "
                            </a>
                        </li>
                    </ul>
                </div>
            </div>",
            'id, name, code'
        );
        $this->datatables->unset_column('id');
        echo $this->datatables->generate();
    }

    public function adjustments()
    {

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['adjustments'] = $this->adjustment_model->getAdjustments();
        $this->data['warehouses'] = $this->site->getAllWarehouses(); // optional dropdown
        $this->data['stores'] = $this->db->get('tec_stores')->result();
        $this->data['products'] = $this->db->get('tec_products')->result();
        $this->data['page_title'] = lang('adjustments');
        $bc = [['link' => '#', 'page' => lang('adjustments')]];
        $meta = ['page_title' => lang('adjustments'), 'bc' => $bc];
        $this->page_construct('products/adjustments', $this->data, $meta);
    }

    public function adjustments_add()
    {
        if (!$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->form_validation->set_rules('product_id', lang('product'), 'required');
        $this->form_validation->set_rules('store_id', lang('warehouse'), 'required');
        $this->form_validation->set_rules('qty_base', lang('quantity'), 'required|numeric');
        $this->form_validation->set_rules('adjustment_type', lang('adjustment_type'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'product_id' => $this->input->post('product_id'),
                'store_id' => $this->input->post('store_id'),
                'qty_base' => $this->input->post('qty_base'),
                'qty_secondary' => $this->input->post('qty_secondary'),
                'adjustment_type' => $this->input->post('adjustment_type'),
                'note' => $this->input->post('note'),
                'date' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id')
            ];

            if ($this->adjustment_model->addAdjustment($data)) {
                $this->adjust_stock_fifo($data['product_id'], $data['store_id'], $data['qty_base'], $data['qty_secondary']);

                $this->session->set_flashdata('message', lang('product_adjustment_added'));
                redirect('products/adjustments');
            } else {
                $this->session->set_flashdata('error', lang('adjustment_save_failed'));
                redirect('products/adjustments_add');
            }
        } else {
            // load data for form
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['stores'] = $this->db->get('tec_stores')->result();
            $this->data['products'] = $this->db->get('tec_products')->result();
            $this->data['page_title'] = lang('add_product_adjustment');

            $bc = [
                ['link' => site_url('adjustments'), 'page' => lang('product_adjustments')],
                ['link' => '#', 'page' => lang('add_product_adjustment')]
            ];
            $meta = ['page_title' => lang('add_product_adjustment'), 'bc' => $bc];

            $this->page_construct('products/adjustments_add', $this->data, $meta);
        }
    }


    public function get_warehouses_by_store($store_id)
    {
        $warehouses = $this->db->where('store_id', $store_id)->get('tec_warehouses')->result();
        echo json_encode($warehouses);
    }

    private function adjust_stock_fifo($product_id, $store_id, $qty_base, $qty_secondary)
    {
        // Ensure quantities are numeric
        $qty_base = (float) $qty_base;
        $qty_secondary = (float) $qty_secondary;

        // Get batches in FIFO order (oldest first)
        $batches = $this->db->order_by('id', 'asc')
            ->where('product_id', $product_id)
            ->where('store_id', $store_id)
            ->where('qty_base >', 0)
            ->get('tec_stock_batches')
            ->result();

        foreach ($batches as $batch) {

            if ($qty_base <= 0 && $qty_secondary <= 0)
                break;

            // Convert batch quantities to float
            $batch_base = (float) $batch->qty_base;
            $batch_secondary = (float) $batch->qty_secondary;

            // Deduct base quantity
            $base_deduct_qty = min($qty_base, $batch_base);
            $base_new_balance = $batch_base - $base_deduct_qty;

            // Deduct secondary quantity
            $second_deduct_qty = min($qty_secondary, $batch_secondary);
            $second_new_balance = $batch_secondary - $second_deduct_qty;

            // Update batch
            $this->db->where('id', $batch->id)->update('tec_stock_batches', [
                'qty_base' => $base_new_balance,
                'qty_secondary' => $second_new_balance,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Decrement remaining quantities
            $qty_base -= $base_deduct_qty;
            $qty_secondary -= $second_deduct_qty;
        }

        // Optional: warn if stock was insufficient
        if ($qty_base > 0 || $qty_secondary > 0) {
            log_message('error', "Stock not enough for product_id {$product_id} in store {$store_id}. Remaining base: {$qty_base}, secondary: {$qty_secondary}");
        }
    }


    public function get_adjustments()
    {
        $this->load->library('datatables');

        $this->datatables
            ->select('tec_product_adjustments.id, tec_product_adjustments.date, tec_products.name as product_name, 
        tec_stores.name as warehouse_name, tec_product_adjustments.qty_base, tec_product_adjustments.qty_secondary, tec_product_adjustments.adjustment_type, 
        tec_product_adjustments.note, tec_users.username')
            ->from('tec_product_adjustments')
            ->join('tec_products', 'tec_products.id=tec_product_adjustments.product_id', 'left')
            ->join('tec_stores', 'tec_stores.id=tec_product_adjustments.store_id', 'left')
            ->join('tec_users', 'tec_users.id = tec_product_adjustments.created_by', 'left')
            ->add_column(
                'Actions',
                "<div class='text-center'>
            <div class='btn-group'>
                <button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                    <i class='fa fa-cog'></i> " . lang('actions') . " <span class='caret'></span>
                </button>
                <ul class='dropdown-menu dropdown-menu-right'>
                    
                    <li>
                            <a href='" . site_url('products/adjustments_delete/$1') . "' class='tip text-danger' title='" . lang('delete') . "' onClick=\"return confirm('" . lang('alert_x_category') . "')\">
                                <i class='fa-solid fa-trash'></i> " . lang('delete') . "
                            </a>
                    </li>
                </ul>
            </div>
        </div>",
                'id,date,product_id,store_id,quantity,adjustment_type,note'
            );


        echo $this->datatables->generate();
    }





    public function adjustments_delete($id = null)
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

        if ($this->products_model->deleteAdjustment($id)) {
            $this->session->set_flashdata('message', lang('unit_deleted'));
            redirect('products/adjustments');
        }
    }

    public function adjustments_edit()
    {
        $id = $this->input->post('id');
        if (!$id) {
            $this->session->set_flashdata('error', lang('invalid_id'));
            redirect('products/adjustments');
        }

        $this->form_validation->set_rules('product_id', lang('product'), 'required');
        $this->form_validation->set_rules('store_id', lang('warehouse'), 'required');
        $this->form_validation->set_rules('quantity', lang('quantity'), 'required|numeric');
        $this->form_validation->set_rules('adjustment_type', lang('adjustment_type'), 'required');

        if ($this->form_validation->run() === TRUE) {
            // Step 1: Get the existing adjustment
            $old = $this->db->get_where('tec_product_adjustments', ['id' => $id])->row();

            if (!$old) {
                $this->session->set_flashdata('error', lang('adjustment_not_found'));
                redirect('products/adjustments');
            }

            // Step 2: Revert old adjustment (add quantity back)
            $this->db->where('product_id', $old->product_id);
            $this->db->where('store_id', $old->store_id);
            $this->db->set('quantity', 'quantity + ' . floatval($old->quantity), false);
            $this->db->update('tec_product_store_qty');

            // Step 3: Get new values
            $data = [
                'product_id' => $this->input->post('product_id'),
                'store_id' => $this->input->post('store_id'),
                'quantity' => $this->input->post('quantity'),
                'adjustment_type' => $this->input->post('adjustment_type'),
                'note' => $this->input->post('note'),
            ];

            // Step 4: Apply new adjustment (subtract quantity)
            $this->db->where('product_id', $data['product_id']);
            $this->db->set('quantity', 'quantity - ' . floatval($data['quantity']), false);
            $this->db->update('tec_product_store_qty');

            // Step 5: Update the adjustment record
            $this->db->where('id', $id);
            if ($this->db->update('tec_product_adjustments', $data)) {
                $this->session->set_flashdata('message', lang('adjustment_updated'));
            } else {
                $this->session->set_flashdata('error', lang('update_failed'));
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
        }

        redirect('products/adjustments');
    }



    public function getUnitsByPID($product_id)
    {
        $this->db->select('p.id as product_id, p.name as product_name, 
                       u1.id as base_unit_id, u1.name as base_unit_name,
                       u2.id as secondary_unit_id, u2.name as secondary_unit_name');
        $this->db->from('products p');
        $this->db->join('product_units u1', 'u1.id = p.base_unit_id', 'left');
        $this->db->join('product_units u2', 'u2.id = p.secondary_unit_id', 'left');
        $this->db->where('p.id', $product_id);
        $q = $this->db->get();

        $units = [];
        if ($q->num_rows() > 0) {
            $row = $q->row();
            if ($row->base_unit_id) {
                $units[] = ['id' => $row->base_unit_id, 'name' => $row->base_unit_name];
            }
            if ($row->secondary_unit_id) {
                $units[] = ['id' => $row->secondary_unit_id, 'name' => $row->secondary_unit_name];
            }
        }
        echo json_encode($units);
    }

    public function closing()
    {
        // Get date from POST or default to today
        $date = $this->input->post('date');
        if (!$date) {
            $date = date('Y-m-d'); // today
        }

        // Prepare display date
        $this->data['display_date'] = $date;

        // Load stores
        $this->data['stores'] = $this->site->getAllStores();

        // Page title
        $this->data['page_title'] = lang('closing_balance');

        // Load view
        $bc = [['link' => site_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('closing_balance')]];
        $meta = ['page_title' => lang('closing_balance'), 'bc' => $bc];
        $this->page_construct('products/closing', $this->data, $meta);
    }


    public function get_closing_balances($v = null)
    {
        $this->load->library('datatables');

        // Use get() for URL parameters
        $date = $this->input->get('date');
        $store_id = $this->input->get('store_id');

        // If no date provided, default to today
        if (empty($date)) {
            $date = date('Y-m-d');
        }

        $this->datatables
            ->select("
            tec_closing_balances.date,
            tec_stores.name as store_name,
            tec_products.code as product_code,
            tec_products.name as product_name,
            tec_closing_balances.qty_base,
            tec_closing_balances.qty_secondary
        ")
            ->from('tec_closing_balances')
            ->join('tec_stores', 'tec_stores.id = tec_closing_balances.store_id', 'left')
            ->join('tec_products', 'tec_products.id = tec_closing_balances.product_id', 'left')
            ->where('tec_closing_balances.date', $date);

        if ($store_id) {
            $this->datatables->where('tec_closing_balances.store_id', $store_id);
        }

        echo $this->datatables->generate();
    }

    private function save_unit_conversions_from_product_form($product_id)
    {
        $conversion_units = $this->input->post('conversion_unit_id');
        $operators = $this->input->post('operator');
        $operation_values = $this->input->post('operation_value');

        if (empty($conversion_units) || !is_array($conversion_units)) {
            return true;
        }

        foreach ($conversion_units as $i => $unit_id) {
            if ($unit_id == '' || !isset($operation_values[$i]) || $operation_values[$i] == '') {
                continue;
            }

            $conv_data = [
                'product_id' => $product_id,
                'unit_id' => $unit_id,
                'operator' => isset($operators[$i]) ? $operators[$i] : '*',
                'operation_value' => $operation_values[$i],
            ];

            $this->db->insert('product_unit_conversions', $conv_data);
        }

        return true;
    }

    private function save_opening_stock_from_product_form($product_id)
    {
        $qty_base = (float) $this->input->post('opening_qty_base');
        $qty_secondary = (float) $this->input->post('opening_qty_secondary');

        if ($qty_base <= 0 && $qty_secondary <= 0) {
            return true;
        }

        $store_id = $this->input->post('opening_store_id');

        if (!$store_id) {
            $store_id = $this->session->userdata('store_id') ? $this->session->userdata('store_id') : 1;
        }

        $opening_date = $this->input->post('opening_date') ? $this->input->post('opening_date') : date('Y-m-d');

        $opening_cost = $this->input->post('opening_cost');

        if ($opening_cost === '' || $opening_cost === null) {
            $opening_cost = $this->input->post('cost');
        }

        $data = [
            'product_id' => $product_id,
            'store_id' => $store_id,
            'batch_no' => 'Opening',
            'qty_base' => $qty_base,
            'qty_secondary' => $qty_secondary,
            'cost_per_base' => (float) $opening_cost,
        ];

        if ($this->db->field_exists('date', 'stock_batches')) {
            $data['date'] = $opening_date;
        }

        if ($this->db->field_exists('created_at', 'stock_batches')) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        if ($this->db->field_exists('updated_at', 'stock_batches')) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->db->insert('stock_batches', $data);
    }

    public function generate_code()
    {
        if (!$this->loggedIn) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $last = $this->db
            ->select('id')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('products')
            ->row();

        $next_id = $last ? ((int) $last->id + 1) : 1;
        $code = 'P' . date('ymd') . str_pad($next_id, 4, '0', STR_PAD_LEFT);

        while ($this->db->where('code', $code)->count_all_results('products') > 0) {
            $next_id++;
            $code = 'P' . date('ymd') . str_pad($next_id, 4, '0', STR_PAD_LEFT);
        }

        echo json_encode([
            'status' => 'success',
            'code' => $code,
        ]);
    }

    public function check_code()
    {
        if (!$this->loggedIn) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ]);
            return;
        }

        $code = trim($this->input->post('code', true));

        if ($code == '') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Code is required',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ]);
            return;
        }

        $exists = $this->db
            ->where('code', $code)
            ->count_all_results('products') > 0;

        echo json_encode([
            'status' => 'success',
            'exists' => $exists,
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
    }

    public function quick_add_category()
    {
        if (!$this->loggedIn) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ]);
            return;
        }

        $name = trim($this->input->post('name', true));
        $code = trim($this->input->post('code', true));

        if ($name == '') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Category name is required',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ]);
            return;
        }
        
        // DUPLICATE CATEORY CHECK
        $existing_category = $this->db
            ->where('name', $name)
            ->get('categories')
            ->row();
    
        if ($existing_category) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Category name "' . $name . '" already exists',
                'csrf_hash' => $this->security->get_csrf_hash(),
            ]);
            return;
        }

        if ($code == '') {
            $code = 'CAT' . date('ymdHis');
        }

        $data = [];

        if ($this->db->field_exists('code', 'categories')) {
            $data['code'] = $code;
        }

        if ($this->db->field_exists('name', 'categories')) {
            $data['name'] = $name;
        }

        if ($this->db->field_exists('created_at', 'categories')) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        if ($this->db->field_exists('updated_at', 'categories')) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->insert('categories', $data);
        $id = $this->db->insert_id();

        echo json_encode([
            'status' => 'success',
            'id' => $id,
            'name' => $name,
            'code' => $code,
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
    }

    public function quick_add_brand()
    {
        $this->output->set_content_type('application/json');

        $respond = function ($status, $message = '', $extra = []) {
            return $this->output->set_output(json_encode(array_merge([
                'status'    => $status,
                'message'   => $message,
                'csrf_hash' => $this->security->get_csrf_hash(),
            ], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        };

        if (!$this->loggedIn) {
            return $respond('error', 'Unauthorized');
        }

        if (!$this->Admin && !$this->Owner) {
            return $respond('error', 'Brand အသစ်ထည့်ရန် Admin သို့မဟုတ် Owner ခွင့်ပြုချက်လိုအပ်သည်။');
        }

        if (!$this->db->table_exists('brands')) {
            return $respond(
                'error',
                'Brand table မရှိသေးပါ။ add_brand_to_products.sql ကို အရင် run ပါ။'
            );
        }

        $name = trim((string) $this->input->post('name', true));
        $name = preg_replace('/\s+/u', ' ', $name);
        $code = strtoupper(trim((string) $this->input->post('code', true)));
        $code = preg_replace('/[^A-Z0-9_-]/', '', $code);

        if ($name === '') {
            return $respond('error', 'Brand အမည် ဖြည့်ပါ။');
        }

        if (mb_strlen($name, 'UTF-8') > 100) {
            return $respond('error', 'Brand အမည်သည် စာလုံး ၁၀၀ ထက်မပိုရပါ။');
        }

        /* Database collation က case-insensitive ဖြစ်သောကြောင့် Coca/Coca ကို တူတူစစ်နိုင်သည်။ */
        $existing_brand = $this->db
            ->where('name', $name)
            ->limit(1)
            ->get('brands')
            ->row();

        if ($existing_brand) {
            return $respond(
                'error',
                'ဤ Brand ရှိပြီးသားဖြစ်ပါသည်။ Dropdown မှ ရွေးချယ်ပါ။',
                [
                    'existing_id'   => (int) $existing_brand->id,
                    'existing_name' => $existing_brand->name,
                ]
            );
        }

        if ($code === '') {
            $ascii_name = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
            $code = $ascii_name !== ''
                ? substr($ascii_name, 0, 20)
                : 'BRAND' . date('ymdHis');
        }

        $base_code = $code;
        $suffix = 1;

        while ($this->db->where('code', $code)->count_all_results('brands') > 0) {
            $suffix++;
            $code = substr($base_code, 0, 42) . '-' . $suffix;
        }

        $data = [
            'name' => $name,
            'code' => $code,
        ];

        if ($this->db->field_exists('status', 'brands')) {
            $data['status'] = 1;
        }

        if ($this->db->field_exists('created_by', 'brands')) {
            $data['created_by'] = (int) $this->session->userdata('user_id');
        }

        if ($this->db->field_exists('created_at', 'brands')) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        if (!$this->db->insert('brands', $data)) {
            return $respond('error', 'Brand သိမ်း၍မရပါ။ ထပ်မံကြိုးစားပါ။');
        }

        return $respond('success', 'Brand သိမ်းပြီးပါပြီ။', [
            'id'   => (int) $this->db->insert_id(),
            'name' => $name,
            'code' => $code,
        ]);
    }

    public function quick_add_unit()
    {
        if (!$this->loggedIn) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                    'csrf_hash' => $this->security->get_csrf_hash(),
                ]));
            return;
        }

        /*
         * Important:
         * If CodeIgniter dbprefix = tec_ ဆိုရင် product_units လို့ရေးတာနဲ့ tec_product_units ကိုသွားနိုင်ပါတယ်။
         * ဒါပေမယ့် May ပြောတဲ့ table name က tec_product_units ဖြစ်လို့ ဒီမှာ table ကို auto detect လုပ်ထားပါတယ်။
         */
        if ($this->db->table_exists('product_units')) {
            $unit_table = 'product_units';
        } elseif ($this->db->table_exists('tec_product_units')) {
            $unit_table = 'tec_product_units';
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Unit table not found. Expected product_units or tec_product_units.',
                    'csrf_hash' => $this->security->get_csrf_hash(),
                ]));
            return;
        }

        $name = trim($this->input->post('name', true));
        $code = trim($this->input->post('code', true));

        if ($name == '') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Unit name is required',
                    'csrf_hash' => $this->security->get_csrf_hash(),
                ]));
            return;
        }

        if ($code == '') {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 5));

            if ($code == '') {
                $code = 'UNIT';
            }

            $code = $code . rand(10, 99);
        }

        // Duplicate name check
        if ($this->db->field_exists('name', $unit_table)) {
            $exists = $this->db
                ->where('LOWER(name)', strtolower($name))
                ->count_all_results($unit_table);

            if ($exists > 0) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'This unit already exists.',
                        'csrf_hash' => $this->security->get_csrf_hash(),
                    ]));
                return;
            }
        }

        // Duplicate code check
        if ($this->db->field_exists('code', $unit_table)) {
            $code_exists = $this->db
                ->where('code', $code)
                ->count_all_results($unit_table);

            if ($code_exists > 0) {
                $code = $code . rand(10, 99);
            }
        }

        $data = [];

        if ($this->db->field_exists('name', $unit_table)) {
            $data['name'] = $name;
        }

        if ($this->db->field_exists('code', $unit_table)) {
            $data['code'] = $code;
        }

        if ($this->db->field_exists('created_at', $unit_table)) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        if ($this->db->field_exists('updated_at', $unit_table)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        if (empty($data)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'No valid fields found in unit table.',
                    'csrf_hash' => $this->security->get_csrf_hash(),
                ]));
            return;
        }

        $insert = $this->db->insert($unit_table, $data);

        if (!$insert) {
            $db_error = $this->db->error();

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => isset($db_error['message']) ? $db_error['message'] : 'Unit insert failed.',
                    'csrf_hash' => $this->security->get_csrf_hash(),
                ]));
            return;
        }

        $id = $this->db->insert_id();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'csrf_hash' => $this->security->get_csrf_hash(),
            ]));
    }
    

    
public function unit_conversions($product_id = null)
{
    $product_id = (int) $product_id;

    if ($product_id <= 0) {
        show_404();
    }

    $products_table    = $this->db->dbprefix('products');
    $units_table       = $this->db->dbprefix('product_units');
    $conversions_table = $this->db->dbprefix('product_unit_conversions');

    // =========================================================
    // PRODUCT
    // =========================================================
    $product = $this->db
        ->select(
            'p.id,
             p.code,
             p.name,
             p.price,
             p.base_unit_id,
             p.secondary_unit_id,
             p.has_unit_conversion,
             p.is_dual_unit,
             bu.name AS base_unit_name,
             bu.code AS base_unit_code,
             su.name AS secondary_unit_name,
             su.code AS secondary_unit_code',
            false
        )
        ->from($products_table . ' p')
        ->join(
            $units_table . ' bu',
            'bu.id = p.base_unit_id',
            'left'
        )
        ->join(
            $units_table . ' su',
            'su.id = p.secondary_unit_id',
            'left'
        )
        ->where('p.id', $product_id)
        ->get()
        ->row();

    if (!$product) {
        show_404();
    }

    // =========================================================
    // APP MODE
    // =========================================================
    $is_app_mode =
        $this->input->get('app') == 1 ||
        $this->input->post('app') == 1 ||
        $this->input->get('mobile') == 1 ||
        $this->input->post('mobile') == 1;

    $selected_language =
        isset($this->Settings) &&
        isset($this->Settings->selected_language)
            ? $this->Settings->selected_language
            : 'english';

    $app_language =
        $this->input->get('app_lang', true) ?:
        (
            $this->input->post('app_lang', true) ?:
            $selected_language
        );

    $app_query = $is_app_mode
        ? '?app=1&app_lang=' . rawurlencode($app_language)
        : '';

    // =========================================================
    // SAVE
    // =========================================================
    if ($this->input->method(true) === 'POST') {

        $base_unit_id = (int) $this->input->post('base_unit_id');
        $is_dual_unit = (int) $product->is_dual_unit === 1;
        $secondary_unit_id = $is_dual_unit
            ? (int) $this->input->post('secondary_unit_id')
            : 0;

        /*
         * User enters:
         *
         * unit_id[]:
         *   ကတ်
         *   ဘူး
         *   ဖာ
         *
         * chain_qty[]:
         *   10
         *   10
         *   10
         *
         * Meaning:
         *   1 ကတ် = 10 လုံး
         *   1 ဘူး = 10 ကတ်
         *   1 ဖာ = 10 ဘူး
         *
         * DB stores final base multiplier:
         *   ကတ် = 10
         *   ဘူး = 100
         *   ဖာ = 1000
         */

        $unit_ids  = (array) $this->input->post('unit_id');
        $chain_qty = (array) $this->input->post('chain_qty');

        // =====================================================
        // VALIDATE BASE UNIT
        // =====================================================
        $base_unit_exists = false;

        if ($base_unit_id > 0) {

            $base_unit_exists =
                $this->db
                    ->where('id', $base_unit_id)
                    ->count_all_results($units_table) > 0;
        }

        if (!$base_unit_exists) {

            $this->session->set_flashdata(
                'error',
                'အခြေခံယူနစ်ကို ရွေးချယ်ပါ။'
            );

            redirect(
                'products/unit_conversions/' .
                $product_id .
                $app_query
            );
        }

        if ($is_dual_unit) {
            $secondary_unit_exists =
                $secondary_unit_id > 0 &&
                $secondary_unit_id !== $base_unit_id &&
                $this->db
                    ->where('id', $secondary_unit_id)
                    ->count_all_results($units_table) > 0;

            if (!$secondary_unit_exists) {
                $this->session->set_flashdata(
                    'error',
                    'သီးခြားရေတွက်မည့်ယူနစ် (လုံး/ခွေ) ကို ရွေးချယ်ပါ။'
                );

                redirect(
                    'products/unit_conversions/' .
                    $product_id .
                    $app_query
                );
            }
        }

        // =====================================================
        // PREPARE DB ROWS
        // =====================================================
        $rows = [];

        $used_unit_ids = [
            $base_unit_id => true
        ];

        if ($is_dual_unit && $secondary_unit_id > 0) {
            // Independent count unit must never enter the weight chain.
            $used_unit_ids[$secondary_unit_id] = true;
        }

        /*
         * Base Unit
         *
         * 1 လုံး = 1 လုံး
         */
        $rows[] = [
            'product_id'      => $product_id,
            'unit_id'         => $base_unit_id,
            'operator'        => '*',
            'operation_value' => 1.00,
        ];

        /*
         * Running multiplier.
         *
         * Base = 1
         *
         * ကတ်:
         * 1 × 10 = 10
         *
         * ဘူး:
         * 10 × 10 = 100
         *
         * ဖာ:
         * 100 × 10 = 1000
         */
        $current_base_multiplier = 1;

        $row_count = max(
            count($unit_ids),
            count($chain_qty)
        );

        // =====================================================
        // VALIDATE + CONVERT CHAIN
        // =====================================================
        for ($i = 0; $i < $row_count; $i++) {

            $unit_id = isset($unit_ids[$i])
                ? (int) $unit_ids[$i]
                : 0;

            $qty = isset($chain_qty[$i])
                ? (float) $chain_qty[$i]
                : 0;

            // Empty row
            if ($unit_id <= 0) {
                continue;
            }

            // Base unit cannot be linked again
            if ($unit_id === $base_unit_id) {

                $this->session->set_flashdata(
                    'error',
                    'အခြေခံယူနစ်ကို ဆက်စပ်ယူနစ်အဖြစ် ထပ်မထည့်ရပါ။'
                );

                redirect(
                    'products/unit_conversions/' .
                    $product_id .
                    $app_query
                );
            }

            if ($is_dual_unit && $unit_id === $secondary_unit_id) {
                $this->session->set_flashdata(
                    'error',
                    'သီးခြားရေတွက်မည့်ယူနစ်ကို အလေးချိန်ပြောင်းလဲမှုအဖြစ် မထည့်ရပါ။'
                );

                redirect(
                    'products/unit_conversions/' .
                    $product_id .
                    $app_query
                );
            }

            // Duplicate unit
            if (isset($used_unit_ids[$unit_id])) {

                $this->session->set_flashdata(
                    'error',
                    'တူညီသောယူနစ်ကို နှစ်ကြိမ်ထည့်ထားပါသည်။'
                );

                redirect(
                    'products/unit_conversions/' .
                    $product_id .
                    $app_query
                );
            }

            // Quantity must be > 0
            if ($qty <= 0) {

                $this->session->set_flashdata(
                    'error',
                    'ယူနစ်တစ်ခုတွင် ပါဝင်သောအရေအတွက်ကို သုညထက်ကြီးသော ဂဏန်းဖြင့် ဖြည့်ပါ။'
                );

                redirect(
                    'products/unit_conversions/' .
                    $product_id .
                    $app_query
                );
            }

            // Confirm unit exists
            $unit_exists =
                $this->db
                    ->where('id', $unit_id)
                    ->count_all_results($units_table) > 0;

            if (!$unit_exists) {

                $this->session->set_flashdata(
                    'error',
                    'ရွေးထားသောယူနစ်တစ်ခု မမှန်ကန်ပါ။'
                );

                redirect(
                    'products/unit_conversions/' .
                    $product_id .
                    $app_query
                );
            }

            // =================================================
            // CHAIN -> BASE MULTIPLIER
            // =================================================
            $current_base_multiplier =
                $current_base_multiplier * $qty;

            $rows[] = [
                'product_id' =>
                    $product_id,

                'unit_id' =>
                    $unit_id,

                'operator' =>
                    '*',

                /*
                 * IMPORTANT:
                 *
                 * Store FINAL BASE multiplier.
                 *
                 * Example:
                 *
                 * ကတ် = 10
                 * ဘူး = 100
                 * ဖာ = 1000
                 */
                'operation_value' =>
                    round(
                        $current_base_multiplier,
                        4
                    ),
            ];

            $used_unit_ids[$unit_id] = true;

        }

        // =====================================================
        // FLAGS
        // =====================================================
        $has_extra_conversion =
            count($rows) > 1
                ? 1
                : 0;

        // =====================================================
        // SAVE TRANSACTION
        // =====================================================
        $this->db->trans_begin();

        // Remove old definitions
        $this->db
            ->where('product_id', $product_id)
            ->delete($conversions_table);

        // Save new definitions
        if (!empty($rows)) {

            $this->db->insert_batch(
                $conversions_table,
                $rows
            );
        }

        // =====================================================
        // UPDATE PRODUCT
        // =====================================================
        $this->db
            ->where('id', $product_id)
            ->update(
                $products_table,
                [
                    'base_unit_id' =>
                        $base_unit_id,

                    'has_unit_conversion' =>
                        $has_extra_conversion,

                    'is_dual_unit' =>
                        $is_dual_unit ? 1 : 0,

                    'secondary_unit_id' =>
                        $is_dual_unit
                            ? $secondary_unit_id
                            : null,
                ]
            );

        // =====================================================
        // TRANSACTION RESULT
        // =====================================================
        if ($this->db->trans_status() === false) {

            $db_error = $this->db->error();

            $this->db->trans_rollback();

            log_message(
                'error',
                'Unit conversion save failed => ' .
                print_r($db_error, true)
            );

            $this->session->set_flashdata(
                'error',
                'ယူနစ်သတ်မှတ်ချက်များ သိမ်းရာတွင် အမှားဖြစ်ပေါ်ခဲ့ပါသည်။'
            );

        } else {

            $this->db->trans_commit();

            $this->session->set_flashdata(
                'message',
                'ယူနစ်အဆင့်များကို အောင်မြင်စွာ သိမ်းပြီးပါပြီ။'
            );
        }

        redirect(
            'products/unit_conversions/' .
            $product_id .
            $app_query
        );
    }

    // =========================================================
    // ALL UNITS
    // =========================================================
    $this->data['units'] =
        $this->db
            ->select('id, name, code')
            ->from($units_table)
            ->order_by('name', 'ASC')
            ->get()
            ->result();

    // =========================================================
    // EXISTING CONVERSIONS
    //
    // IMPORTANT:
    // Sort by final multiplier:
    //
    // 10
    // 100
    // 1000
    // =========================================================
    $conversions =
        $this->db
            ->select(
                'c.id,
                 c.product_id,
                 c.unit_id,
                 c.operator,
                 c.operation_value,
                 u.name AS unit_name,
                 u.code AS unit_code',
                false
            )
            ->from($conversions_table . ' c')
            ->join(
                $units_table . ' u',
                'u.id = c.unit_id',
                'left'
            )
            ->where(
                'c.product_id',
                $product_id
            )
            ->where(
                'c.unit_id !=',
                (int) $product->base_unit_id
            )
            ->order_by(
                'c.operation_value',
                'ASC'
            )
            ->get()
            ->result();

    /*
     * Convert DB final multiplier back to
     * easy human chain quantity.
     *
     * DB:
     * ကတ် = 10
     * ဘူး = 100
     * ဖာ = 1000
     *
     * UI:
     * ကတ် = 10 လုံး
     * ဘူး = 10 ကတ်
     * ဖာ = 10 ဘူး
     */
    $previous_multiplier = 1;

    foreach ($conversions as $conversion) {

        $final_multiplier =
            (float) $conversion->operation_value;

        if (
            $previous_multiplier > 0 &&
            $final_multiplier > 0
        ) {

            $conversion->chain_qty =
                $final_multiplier /
                $previous_multiplier;

        } else {

            $conversion->chain_qty = 1;
        }

        $previous_multiplier =
            $final_multiplier;
    }

    // =========================================================
    // VIEW DATA
    // =========================================================
    $this->data['product'] =
        $product;

    $this->data['conversions'] =
        $conversions;

    $this->data['is_app_mode'] =
        $is_app_mode;

    $this->data['app_language'] =
        $app_language;

    $this->data['app_query'] =
        $app_query;

    $this->data['error'] =
        validation_errors()
            ?: $this->session->flashdata('error');

    $this->data['message'] =
        $this->session->flashdata('message');

    $this->data['page_title'] =
        'ကုန်ပစ္စည်းထုပ်ပိုးပုံ သတ်မှတ်ရန်';

    // =========================================================
    // BREADCRUMB
    // =========================================================
    $bc = [
        [
            'link' =>
                site_url('products'),

            'page' =>
                lang('products')
        ],
        [
            'link' => '#',

            'page' =>
                'ထုပ်ပိုးပုံ သတ်မှတ်ရန်'
        ],
    ];

    $meta = [
        'page_title' =>
            $this->data['page_title'],

        'bc' =>
            $bc,
    ];

    // =========================================================
    // VIEW
    // =========================================================
    $this->page_construct(
        'products/unit_conversions',
        $this->data,
        $meta
    );
}

public function selling_prices($product_id = null)
{
    $product_id = (int) $product_id;

    if ($product_id <= 0) {
        show_404();
    }

    $products_table    = $this->db->dbprefix('products');
    $units_table       = $this->db->dbprefix('product_units');
    $conversions_table = $this->db->dbprefix('product_unit_conversions');
    $prices_table      = $this->db->dbprefix('product_unit_prices');

    $product = $this->db
        ->select(
            'p.id, p.code, p.name, p.cost, p.price, p.base_unit_id, ' .
            'bu.name AS base_unit_name, bu.code AS base_unit_code',
            false
        )
        ->from($products_table . ' p')
        ->join($units_table . ' bu', 'bu.id = p.base_unit_id', 'left')
        ->where('p.id', $product_id)
        ->get()
        ->row();

    if (!$product) {
        show_404();
    }
    
    /*
     * =========================================================
     * Get current / latest base purchase cost
     * =========================================================
     *
     * Priority:
     * 1. Latest stock batch cost_per_base
     * 2. products.cost fallback
    */
    
    $latest_batch = $this->db
        ->select('cost_per_base')
        ->from('tec_stock_batches')
        ->where('product_id', $product_id)
        ->where('cost_per_base IS NOT NULL', null, false)
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get()
        ->row();
    
    if (
        $latest_batch &&
        is_numeric($latest_batch->cost_per_base)
    ) {
        $product->cost = (float) $latest_batch->cost_per_base;
    } else {
        $product->cost = is_numeric($product->cost)
            ? (float) $product->cost
            : 0;
    }

    $is_app_mode =
        $this->input->get('app') == 1 ||
        $this->input->post('app') == 1 ||
        $this->input->get('mobile') == 1 ||
        $this->input->post('mobile') == 1;

    $selected_language =
        isset($this->Settings) &&
        isset($this->Settings->selected_language)
            ? $this->Settings->selected_language
            : 'english';

    $app_language =
        $this->input->get('app_lang', true) ?:
        ($this->input->post('app_lang', true) ?: $selected_language);

    $app_query = $is_app_mode
        ? '?app=1&app_lang=' . rawurlencode($app_language)
        : '';

    if (!$this->db->table_exists('product_unit_prices')) {
        $this->session->set_flashdata(
            'error',
            'product_unit_prices table မရှိသေးပါ။ Database table ကို စစ်ဆေးပါ။'
        );

        redirect('products' . $app_query);
    }

    /*
     * Build the list of units that are allowed to receive a selling price:
     * 1. The product's base unit
     * 2. Every linked unit from product_unit_conversions
     */
    $unit_rows = [];
    $allowed_unit_ids = [];

    if ((int) $product->base_unit_id > 0) {
        $base_row = new stdClass();
        $base_row->unit_id         = (int) $product->base_unit_id;
        $base_row->unit_name       = $product->base_unit_name ?: '-';
        $base_row->unit_code       = $product->base_unit_code ?: '';
        $base_row->operator        = '*';
        $base_row->operation_value = 1;
        $base_row->is_base         = 1;

        $unit_rows[] = $base_row;
        $allowed_unit_ids[(int) $product->base_unit_id] = true;
    }

    if ($this->db->table_exists($conversions_table)) {
        $linked_units = $this->db
            ->select(
                'c.unit_id, c.operator, c.operation_value, ' .
                'u.name AS unit_name, u.code AS unit_code',
                false
            )
            ->from($conversions_table . ' c')
            ->join($units_table . ' u', 'u.id = c.unit_id', 'left')
            ->where('c.product_id', $product_id)
            ->order_by('c.id', 'ASC')
            ->get()
            ->result();

        foreach ($linked_units as $linked_unit) {
            $linked_unit_id = (int) $linked_unit->unit_id;

            if (
                $linked_unit_id <= 0 ||
                isset($allowed_unit_ids[$linked_unit_id])
            ) {
                continue;
            }

            $linked_unit->is_base = 0;
            $unit_rows[] = $linked_unit;
            $allowed_unit_ids[$linked_unit_id] = true;
        }
    }

    if (empty($unit_rows)) {
        $this->session->set_flashdata(
            'error',
            'ဤကုန်ပစ္စည်းအတွက် အခြေခံယူနစ် မသတ်မှတ်ရသေးပါ။'
        );

        redirect('products' . $app_query);
    }

    $request_method = strtoupper(
        (string) $this->input->server('REQUEST_METHOD', true)
    );

    if ($request_method === 'POST') {
        $posted_unit_ids = (array) $this->input->post('unit_id');
        $posted_prices   = (array) $this->input->post('unit_price');
        $save_action     = trim((string) $this->input->post('save_action', true));

        $rows_to_save = [];
        $saved_unit_ids = [];
        $base_price = null;
        $validation_error = '';

        $row_count = max(count($posted_unit_ids), count($posted_prices));

        for ($i = 0; $i < $row_count; $i++) {
            $unit_id = isset($posted_unit_ids[$i])
                ? (int) $posted_unit_ids[$i]
                : 0;

            $raw_price = isset($posted_prices[$i])
                ? trim((string) $posted_prices[$i])
                : '';

            $normalised_price = str_replace([',', ' '], '', $raw_price);

            if (
                $unit_id <= 0 ||
                !isset($allowed_unit_ids[$unit_id]) ||
                isset($saved_unit_ids[$unit_id])
            ) {
                continue;
            }

            if (
                $normalised_price === '' ||
                !is_numeric($normalised_price) ||
                (float) $normalised_price < 0
            ) {
                $validation_error =
                    'ရောင်းဈေးအားလုံးကို သုည သို့မဟုတ် သုညထက်ကြီးသော ဂဏန်းဖြင့် ဖြည့်ပါ။';
                break;
            }

            $price = round((float) $normalised_price, 4);

            $rows_to_save[] = [
                'product_id' => $product_id,
                'unit_id'    => $unit_id,
                'price'      => $price,
            ];

            $saved_unit_ids[$unit_id] = true;

            if ($unit_id === (int) $product->base_unit_id) {
                $base_price = $price;
            }
        }

        if ($validation_error === '' && $base_price === null) {
            $validation_error = 'အခြေခံယူနစ် ရောင်းဈေးကို ဖြည့်ပါ။';
        }

        if ($validation_error !== '') {
            $this->session->set_flashdata('error', $validation_error);

            redirect(
                'products/selling_prices/' .
                $product_id .
                $app_query
            );
        }

        $this->db->trans_begin();

        $submitted_unit_ids = [];

        foreach ($rows_to_save as $price_row) {
            $unit_id = (int) $price_row['unit_id'];
            $submitted_unit_ids[] = $unit_id;

            $existing_row = $this->db
                ->select('id')
                ->from($prices_table)
                ->where('product_id', $product_id)
                ->where('unit_id', $unit_id)
                ->limit(1)
                ->get()
                ->row();

            if ($existing_row) {
                $this->db
                    ->where('id', (int) $existing_row->id)
                    ->update(
                        $prices_table,
                        ['price' => $price_row['price']]
                    );
            } else {
                $this->db->insert(
                    $prices_table,
                    [
                        'product_id' => $product_id,
                        'unit_id'    => $unit_id,
                        'price'      => $price_row['price'],
                    ]
                );
            }
        }

        if (!empty($submitted_unit_ids)) {
            $this->db
                ->where('product_id', $product_id)
                ->where_not_in('unit_id', $submitted_unit_ids)
                ->delete($prices_table);
        }

        $this->db
            ->where('id', $product_id)
            ->update(
                $products_table,
                ['price' => $base_price]
            );

        if ($this->db->trans_status() === false) {
            $db_error = $this->db->error();
            $this->db->trans_rollback();

            log_message(
                'error',
                'Selling price save failed for product ' .
                $product_id .
                ': ' .
                (!empty($db_error['message'])
                    ? $db_error['message']
                    : 'Unknown database error')
            );

            $error_message =
                'ရောင်းဈေးများ သိမ်းရာတွင် အမှားတစ်ခုဖြစ်ပေါ်ခဲ့ပါသည်။';

            if (!empty($db_error['message'])) {
                $error_message .=
                    '<br><small>' .
                    html_escape($db_error['message']) .
                    '</small>';
            }

            $this->session->set_flashdata(
                'error',
                $error_message
            );

            redirect(
                'products/selling_prices/' .
                $product_id .
                $app_query
            );
        }

        $this->db->trans_commit();

        $this->session->set_flashdata(
            'message',
            'ရောင်းဈေးများကို အောင်မြင်စွာ သိမ်းပြီးပါပြီ။'
        );

        if ($save_action === 'stay') {
            redirect(
                'products/selling_prices/' .
                $product_id .
                $app_query
            );
        }

        redirect('products' . $app_query);
    }

    $existing_prices = $this->db
        ->select('unit_id, price')
        ->from($prices_table)
        ->where('product_id', $product_id)
        ->get()
        ->result();

    $price_map = [];

    foreach ($existing_prices as $price_row) {
        $price_map[(int) $price_row->unit_id] = (float) $price_row->price;
    }

    foreach ($unit_rows as $unit_row) {

    $unit_id = (int) $unit_row->unit_id;


    // =====================================================
    // SELLING PRICE
    // =====================================================
    if (array_key_exists($unit_id, $price_map)) {

        $unit_row->selling_price =
            (float) $price_map[$unit_id];

    } elseif (
        $unit_id ===
        (int) $product->base_unit_id
    ) {

        $unit_row->selling_price =
            (float) $product->price;

    } else {

        $unit_row->selling_price = 0;
    }


    // =====================================================
    // CONVERSION DATA
    // =====================================================
    $operation_value =
        is_numeric($unit_row->operation_value)
            ? (float) $unit_row->operation_value
            : 1;


    $operator =
        !empty($unit_row->operator)
            ? trim((string) $unit_row->operator)
            : '*';


    $base_cost =
        is_numeric($product->cost)
            ? (float) $product->cost
            : 0;


    // =====================================================
    // REAL BASE UNIT NAME
    //
    // Example:
    // လုံး
    // ခု
    // ပုလင်း
    // kg
    // pcs
    // =====================================================
    $base_unit_name =
        !empty($product->base_unit_name)
            ? $product->base_unit_name
            : 'အခြေခံယူနစ်';


    // =====================================================
    // BASE UNIT
    // =====================================================
    if ((int) $unit_row->is_base === 1) {

        $unit_row->estimated_cost =
            $base_cost;


        $unit_row->conversion_text =
            '1 ' .
            ($unit_row->unit_name ?: $base_unit_name);

    }


    // =====================================================
    // DIVISION
    // =====================================================
    elseif (
        $operator === '/' &&
        $operation_value > 0
    ) {

        $unit_row->estimated_cost =
            $base_cost / $operation_value;


        $formatted_value =
            rtrim(
                rtrim(
                    number_format(
                        $operation_value,
                        4,
                        '.',
                        ''
                    ),
                    '0'
                ),
                '.'
            );


        $unit_row->conversion_text =
            '1 ' .
            ($unit_row->unit_name ?: '-') .
            ' = 1 ÷ ' .
            $formatted_value .
            ' ' .
            $base_unit_name;

    }


    // =====================================================
    // MULTIPLICATION
    // =====================================================
    else {

        $unit_row->estimated_cost =
            $base_cost *
            $operation_value;


        $formatted_value =
            rtrim(
                rtrim(
                    number_format(
                        $operation_value,
                        4,
                        '.',
                        ''
                    ),
                    '0'
                ),
                '.'
            );


        $unit_row->conversion_text =
            '1 ' .
            ($unit_row->unit_name ?: '-') .
            ' = ' .
            $formatted_value .
            ' ' .
            $base_unit_name;
    }
}

    $this->data['product']      = $product;
    $this->data['unit_rows']    = $unit_rows;
    $this->data['is_app_mode']  = $is_app_mode;
    $this->data['app_language'] = $app_language;
    $this->data['app_query']    = $app_query;
    $this->data['error']        =
        validation_errors() ?: $this->session->flashdata('error');
    $this->data['message']      = $this->session->flashdata('message');
    $this->data['page_title']   = 'ရောင်းဈေးထည့်ရန်';

    $bc = [
        ['link' => site_url('products'), 'page' => lang('products')],
        ['link' => '#', 'page' => 'ရောင်းဈေးထည့်ရန်'],
    ];

    $meta = [
        'page_title' => $this->data['page_title'],
        'bc'         => $bc,
    ];

    $this->page_construct(
        'products/selling_prices',
        $this->data,
        $meta
    );
}
}
