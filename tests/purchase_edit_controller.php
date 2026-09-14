<?php
// Included only by the isolated inventory runner. Real controller/model, stubbed framework services.
if (PHP_SAPI !== 'cli' || !isset($connection, $testName)) { exit(1); }
class MY_Controller {}
class EditRedirect extends RuntimeException {}
function lang($key) { return $key; }
function site_url($path) { return 'http://localhost/klspos/' . $path; }
function redirect($path) { throw new EditRedirect($path); }
require APPPATH . 'controllers/Purchases.php';
$controller = (new ReflectionClass('Purchases'))->newInstanceWithoutConstructor();
$controller->purchases_model = $purchases;
$controller->db = $connection;
$controller->Settings = (object)['selected_language'=>'english'];
$controller->form_validation = new class {
    public function set_rules(...$args) {}
    public function run() { return true; }
};
$controller->session = new class {
    public $values = [];
    public function set_userdata($key,$value) { $this->values[$key] = $value; }
    public function set_flashdata($key,$value) { $this->values[$key] = $value; }
};
$controller->input = new class {
    public $query = [];
    public function get($key,$clean = false) { return $this->query[$key] ?? null; }
    public function post($key,$clean = false) { return $_POST[$key] ?? null; }
};
$originalPost = $_POST;
try {
    foreach ([false,true] as $mobile) {
        $controller->input->query = $mobile ? ['app'=>1,'app_lang'=>'myanmar'] : [];
        $_POST = ['date'=>'2026-09-13 10:00','reference'=>'QA-CONTROLLER','supplier'=>0,'store'=>$storeId,
            'note'=>'Synthetic controller save','product_id'=>[$productId],'primary_qty'=>[3],
            'primary_unit'=>[1],'secondary_qty'=>[''],'secondary_unit'=>[''],'cost'=>[12],
            'delivery'=>9,'received'=>0,'advance_deducted'=>0];
        $destination = null;
        try { $controller->edit($editPurchase); } catch (EditRedirect $result) { $destination = $result->getMessage(); }
        verify($destination === site_url('purchases') . ($mobile ? '?app=1&app_lang=myanmar' : ''), 'controller save preserves '.($mobile?'mobile language':'desktop').' destination');
        verify((float)$connection->get_where('purchases',['id'=>$editPurchase])->row()->total === 45.0, 'controller POST saves recalculated invoice total');
        verify($controller->session->values['message'] === 'purchase_updated', 'controller reports completed save');
    }
} finally { $_POST = $originalPost; }
