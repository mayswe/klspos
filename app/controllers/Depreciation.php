<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Depreciation extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            redirect('login');
        }
        $this->load->library('form_validation');
        $this->load->model('depreciation_model');
    }
    
    public function index()
    {
        
        $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['page_title'] = lang('depreciation');
        $bc                       = [['link' => '#', 'page' => lang('depreciation')]];
        $meta                     = ['page_title' => lang('depreciation'), 'bc' => $bc];
        $this->page_construct('depreciation/index', $this->data, $meta);
    }


    public function create()
    {
        if (!$this->Admin && !$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            redirect('pos');
        }

        $this->load->helper('security');

        // Form validation
        $this->form_validation->set_rules('asset_name', lang('asset_name'), 'required');
        $this->form_validation->set_rules('purchase_cost', lang('purchase_cost'), 'required|numeric');
        $this->form_validation->set_rules('useful_life', lang('useful_life_years'), 'required|numeric');
        $this->form_validation->set_rules('depreciation_amount', lang('depreciation_amount'), 'required|numeric');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $date = trim($this->input->post('purchase_date')) ?: date('Y-m-d');

            $data = [
                'asset_name'          => $this->input->post('asset_name'),
                'purchase_date'       => $date,
                'purchase_cost'       => $this->input->post('purchase_cost'),
                'useful_life'         => $this->input->post('useful_life'),
                'method'              => $this->input->post('method'),
                'depreciation_amount' => $this->input->post('depreciation_amount'),
                'note'                => $this->input->post('note', true),
                'created_by'          => $this->session->userdata('user_id'),
                'store_id'            => $this->session->userdata('store_id'),
            ];

            // File upload
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
        } elseif ($this->input->post('create')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->depreciation_model->addDepreciation($data)) {
            $this->session->set_flashdata('message', lang('depreciation_added'));
            redirect('depreciation/index');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['page_title'] = lang('add_depreciation');
            $bc                       = [['link' => site_url('depreciation'), 'page' => lang('depreciation')], ['link' => '#', 'page' => lang('add_depreciation')]];
            $meta                     = ['page_title' => lang('add_depreciation'), 'bc' => $bc];
            $this->page_construct('depreciation/create', $this->data, $meta);
        }
    }
    
    public function get_depreciation_expenses()
{
    $this->load->library('datatables');

    $this->datatables
        ->select("tec_depreciation_expenses.id as id, 
                  tec_depreciation_expenses.asset_name, 
                  tec_depreciation_expenses.purchase_date, 
                  tec_depreciation_expenses.purchase_cost, 
                  tec_depreciation_expenses.useful_life, 
                  tec_depreciation_expenses.method, 
                  tec_depreciation_expenses.depreciation_amount, 
                  tec_depreciation_expenses.note, 
                  tec_users.first_name as user, 
                  tec_depreciation_expenses.created_at")
        ->from("tec_depreciation_expenses")
        ->join("tec_users", "tec_users.id = tec_depreciation_expenses.created_by", "left");

    // Add action buttons (Edit/Delete)
    $this->datatables->add_column("Actions",
        "<div class=\"text-center\">
            <a href='" . site_url('depreciation/edit_depreciation/$1') . "' class='tip btn btn-primary btn-xs' title='" . lang("edit") . "'>
                <i class='fa fa-edit'></i>
            </a>
            <a href='" . site_url('depreciation/delete_depreciation/$1') . "' class='tip btn btn-danger btn-xs' title='" . lang("delete_depreciation") . "' onclick=\"return confirm('" . lang("confirm_delete_depreciation") . "')\">
                <i class='fa fa-trash'></i>
            </a>
        </div>", "id");

    echo $this->datatables->generate();
}



public function edit_depreciation($id = null)
{
    if (!$this->Admin && !$this->Owner) {
        $this->session->set_flashdata('error', lang('access_denied'));
        redirect('pos');
    }

    $id = (int) $id;

    if ($id <= 0) {
        $this->session->set_flashdata(
            'error',
            lang('depreciation_not_found')
        );
        redirect('depreciation');
    }

    $depreciation = $this->depreciation_model
        ->getDepreciationById($id);

    if (!$depreciation) {
        log_message(
            'error',
            'DEPRECIATION EDIT: Record not found. ID: ' . $id
        );

        $this->session->set_flashdata(
            'error',
            lang('depreciation_not_found')
        );

        redirect('depreciation');
    }

    /*
     * Mobile မှာ store_id session မပါလာလျှင်
     * Store 1 ကို default အသုံးပြုမည်။
     */
    $store_id = (int) $this->session->userdata('store_id');

    if ($store_id <= 0) {
        $store_id = 1;
        $this->session->set_userdata('store_id', $store_id);

        log_message(
            'error',
            'DEPRECIATION EDIT: Session store_id missing. ' .
            'Default store_id 1 assigned. User ID: ' .
            $this->session->userdata('user_id')
        );
    }

    /*
     * လက်ရှိ Store ၏ record မဟုတ်လျှင်
     * ဝင်ပြင်ခွင့်မပေးရန်။
     */
    if (
        isset($depreciation->store_id) &&
        (int) $depreciation->store_id !== $store_id
    ) {
        log_message(
            'error',
            'DEPRECIATION EDIT: Store access denied. Record ID: ' .
            $id .
            ', Record Store: ' .
            $depreciation->store_id .
            ', Session Store: ' .
            $store_id
        );

        $this->session->set_flashdata(
            'error',
            lang('access_denied')
        );

        redirect('depreciation');
    }

    $this->load->helper('security');

    $this->form_validation->set_rules(
        'asset_name',
        lang('asset_name'),
        'required|trim'
    );

    $this->form_validation->set_rules(
        'purchase_date',
        lang('purchase_date'),
        'required|trim'
    );

    $this->form_validation->set_rules(
        'purchase_cost',
        lang('purchase_cost'),
        'required|numeric'
    );

    $this->form_validation->set_rules(
        'useful_life',
        lang('useful_life_years'),
        'required|numeric'
    );

    $this->form_validation->set_rules(
        'method',
        lang('method'),
        'required|trim'
    );

    $this->form_validation->set_rules(
        'depreciation_amount',
        lang('depreciation_amount'),
        'required|numeric'
    );

    $this->form_validation->set_rules(
        'userfile',
        lang('attachment'),
        'xss_clean'
    );

    $validation_result = $this->form_validation->run();

    if ($validation_result === true) {
        $purchase_date = trim(
            $this->input->post('purchase_date', true)
        );

        $data = [
            'asset_name' => $this->input->post(
                'asset_name',
                true
            ),
            'purchase_date' => $purchase_date,
            'purchase_cost' => $this->input->post(
                'purchase_cost'
            ),
            'useful_life' => $this->input->post(
                'useful_life'
            ),
            'method' => $this->input->post(
                'method',
                true
            ),
            'depreciation_amount' => $this->input->post(
                'depreciation_amount'
            ),
            'note' => $this->input->post('note', true),
            'store_id' => $store_id,
        ];

        /*
         * File အသစ်ရွေးထားမှသာ Attachment ပြောင်းမည်။
         * File မရွေးလျှင် Attachment အဟောင်း မပျောက်ပါ။
         */
        if (
            isset($_FILES['userfile']) &&
            isset($_FILES['userfile']['size']) &&
            (int) $_FILES['userfile']['size'] > 0
        ) {
            $this->load->library('upload');

            $config = [
                'upload_path' => FCPATH . 'uploads/',
                'allowed_types' => $this->allowed_types,
                'max_size' => 2000,
                'overwrite' => false,
                'encrypt_name' => true,
            ];

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('userfile')) {
                $upload_error = strip_tags(
                    $this->upload->display_errors()
                );

                log_message(
                    'error',
                    'DEPRECIATION EDIT: Upload failed. ID: ' .
                    $id .
                    ', Error: ' .
                    $upload_error
                );

                $this->session->set_flashdata(
                    'error',
                    $upload_error
                );

                redirect(
                    'depreciation/edit_depreciation/' . $id
                );
            }

            $upload_data = $this->upload->data();
            $data['attachment'] = $upload_data['file_name'];
        }

        log_message(
            'debug',
            'DEPRECIATION EDIT: Updating record. ID: ' .
            $id .
            ', Data: ' .
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );

        $updated = $this->depreciation_model
            ->updateDepreciation($id, $data);

        if ($updated) {
            log_message(
                'debug',
                'DEPRECIATION EDIT: Update successful. ID: ' .
                $id
            );

            $this->session->set_flashdata(
                'message',
                lang('depreciation_updated')
            );

            redirect('depreciation');
        }

        $db_error = $this->db->error();

        log_message(
            'error',
            'DEPRECIATION EDIT: Database update failed. ID: ' .
            $id .
            ', DB Error: ' .
            json_encode(
                $db_error,
                JSON_UNESCAPED_UNICODE
            )
        );

        $this->session->set_flashdata(
            'error',
            lang('depreciation_update_failed')
        );

        redirect(
            'depreciation/edit_depreciation/' . $id
        );
    }

    if ($this->input->post('update')) {
        log_message(
            'error',
            'DEPRECIATION EDIT: Validation failed. ID: ' .
            $id .
            ', Errors: ' .
            strip_tags(validation_errors())
        );

        $this->session->set_flashdata(
            'error',
            validation_errors()
        );

        redirect(
            'depreciation/edit_depreciation/' . $id
        );
    }

    $this->data['depreciation'] = $depreciation;

    $this->data['error'] = validation_errors()
        ? validation_errors()
        : $this->session->flashdata('error');

    $this->data['page_title'] = lang(
        'edit_depreciation'
    );

    $bc = [
        [
            'link' => site_url('depreciation'),
            'page' => lang('depreciation'),
        ],
        [
            'link' => '#',
            'page' => lang('edit_depreciation'),
        ],
    ];

    $meta = [
        'page_title' => lang('edit_depreciation'),
        'bc' => $bc,
    ];

    $this->page_construct(
        'depreciation/edit_depreciation',
        $this->data,
        $meta
    );
}

public function delete_depreciation($id = null)
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
            lang('depreciation_not_found')
        );

        redirect('depreciation');
    }

    $depreciation = $this->depreciation_model
        ->getDepreciationById($id);

    if (!$depreciation) {
        log_message(
            'error',
            'DEPRECIATION DELETE: Record not found. ID: ' .
            $id
        );

        $this->session->set_flashdata(
            'error',
            lang('depreciation_not_found')
        );

        redirect('depreciation');
    }

    /*
     * Mobile session မှာ store_id မရှိလျှင်
     * Store 1 ကို default အသုံးပြုမည်။
     */
    $store_id = (int) $this->session->userdata('store_id');

    if ($store_id <= 0) {
        $store_id = 1;
        $this->session->set_userdata('store_id', $store_id);

        log_message(
            'error',
            'DEPRECIATION DELETE: Session store_id missing. ' .
            'Default store_id 1 assigned. User ID: ' .
            $this->session->userdata('user_id')
        );
    }

    /*
     * တခြား Store က record ကို ဖျက်လို့မရအောင် စစ်ဆေးခြင်း
     */
    if (
        isset($depreciation->store_id) &&
        (int) $depreciation->store_id !== $store_id
    ) {
        log_message(
            'error',
            'DEPRECIATION DELETE: Store access denied. Record ID: ' .
            $id .
            ', Record Store: ' .
            $depreciation->store_id .
            ', Session Store: ' .
            $store_id
        );

        $this->session->set_flashdata(
            'error',
            lang('access_denied')
        );

        redirect('depreciation');
    }

    /*
     * Database record ဖျက်ခြင်း
     */
    $deleted = $this->depreciation_model
        ->deleteDepreciation($id);

    if (!$deleted) {
        $db_error = $this->db->error();

        log_message(
            'error',
            'DEPRECIATION DELETE: Database deletion failed. ID: ' .
            $id .
            ', DB Error: ' .
            json_encode(
                $db_error,
                JSON_UNESCAPED_UNICODE
            )
        );

        $this->session->set_flashdata(
            'error',
            lang('depreciation_delete_failed')
        );

        redirect('depreciation');
    }

    /*
     * Database ဖျက်ပြီးမှ Attachment ဖိုင်ကို ဖျက်ခြင်း
     */
    if (!empty($depreciation->attachment)) {
        $attachment = basename($depreciation->attachment);
        $file_path  = FCPATH . 'uploads/' . $attachment;

        if (is_file($file_path)) {
            if (!@unlink($file_path)) {
                log_message(
                    'error',
                    'DEPRECIATION DELETE: Record deleted, but attachment ' .
                    'could not be removed. ID: ' .
                    $id .
                    ', File: ' .
                    $file_path
                );
            }
        }
    }

    log_message(
        'debug',
        'DEPRECIATION DELETE: Record deleted successfully. ID: ' .
        $id .
        ', User ID: ' .
        $this->session->userdata('user_id')
    );

    $this->session->set_flashdata(
        'message',
        lang('depreciation_deleted')
    );

    redirect('depreciation');
}
}
