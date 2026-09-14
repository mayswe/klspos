<?php
// Run from the project root: php tests/isolated_inventory.php
// No business rows are copied. Only a uniquely named disposable schema is written.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', dirname(__DIR__) . '/lib/');
define('APPPATH', dirname(__DIR__) . '/app/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function show_error($message) { throw new RuntimeException((string) $message); }
class CI_Model { public function __construct() {} }
require APPPATH . 'config/database.php';
$sourceConfig = $db['default'];
$sourceName = $sourceConfig['database'];
$testName = 'klspos_test_' . bin2hex(random_bytes(6));
if (!preg_match('/^klspos_test_[a-f0-9]{12}$/D', $testName) || $testName === $sourceName) {
    throw new RuntimeException('Unsafe test database name');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$admin = new mysqli($sourceConfig['hostname'], $sourceConfig['username'], $sourceConfig['password'], $sourceName);
function identifier($value) { return '`' . str_replace('`', '``', $value) . '`'; }
$created = false;
$connection = null;
$passes = 0;
function verify($condition, $description) {
    global $passes;
    if (!$condition) { throw new RuntimeException('FAIL: ' . $description); }
    $passes++;
    echo 'PASS: ' . $description . PHP_EOL;
}
try {
    // CREATE without IF NOT EXISTS prevents reuse of any existing database.
    $admin->query('CREATE DATABASE ' . identifier($testName));
    $created = true;
    $tables = $admin->query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'')->fetch_all();
    foreach ($tables as $table) {
        $admin->query('CREATE TABLE ' . identifier($testName) . '.' . identifier($table[0])
            . ' LIKE ' . identifier($sourceName) . '.' . identifier($table[0]));
    }
    $admin->select_db($testName);
    require dirname(__DIR__) . '/scripts/lib/receipt_schema.php';
    upgrade_receipt_schema($admin, $sourceConfig['dbprefix']);
    $testConfig = $sourceConfig;
    $testConfig['database'] = $testName;
    $testConfig['db_debug'] = false;
    $testConfig['pconnect'] = false;
    require BASEPATH . 'database/DB.php';
    $connection = DB($testConfig, true);
    verify($connection->query('SELECT DATABASE() AS name')->row()->name === $testName, 'model connection is isolated');
    $connection->query("SET SESSION sql_mode = ''");
    require APPPATH . 'models/Products_model.php';
    require APPPATH . 'models/Purchases_model.php';
    $products = new Products_model();
    $products->db = $connection;
    $purchases = new Purchases_model();
    $purchases->db = $connection;

    // Supply schema-required fields with synthetic values, not production rows.
    $fixture = function ($table, array $overrides) use ($connection) {
        $data = [];
        foreach ($connection->query('SHOW COLUMNS FROM ' . identifier($connection->dbprefix($table)))->result_array() as $column) {
            if (strpos($column['Extra'], 'auto_increment') !== false || $column['Default'] !== null || $column['Null'] === 'YES') { continue; }
            $type = strtolower($column['Type']);
            if (preg_match('/int|decimal|float|double|bit/', $type)) { $value = 0; }
            elseif (preg_match('/date|timestamp/', $type)) { $value = '2026-09-13 10:00:00'; }
            elseif (preg_match("/^enum\('([^']+)'/", $type, $match)) { $value = $match[1]; }
            else { $value = 'test'; }
            $data[$column['Field']] = $value;
        }
        return array_merge($data, $overrides);
    };
    $insert = function ($table, array $values) use ($connection, $fixture) {
        if (!$connection->insert($table, $fixture($table, $values))) { throw new RuntimeException('Fixture insert failed: ' . $table); }
        return $connection->insert_id();
    };
    $productData = $fixture('products', ['code'=>'QA-DELETE', 'name'=>'Isolated delete fixture', 'category_id'=>1, 'base_unit_id'=>1, 'quantity'=>0]);
    verify($products->addProduct($productData), 'save a synthetic product');
    $deleteId = $connection->insert_id();
    verify($products->deleteProduct($deleteId), 'delete unused synthetic product');
    verify(!$connection->get_where('products', ['id'=>$deleteId])->row(), 'deleted product is absent');

    $productId = $insert('products', array_merge($productData, ['code'=>'QA-RECEIVE', 'name'=>'Isolated receive fixture']));
    $storeId = $insert('stores', ['name'=>'Isolated QA Store', 'code'=>'QA']);
    $purchaseId = $insert('purchases', ['reference'=>'QA-RECEIVE', 'store_id'=>$storeId, 'received'=>0, 'total'=>100, 'paid'=>0]);
    $itemId = $insert('purchase_items', ['purchase_id'=>$purchaseId, 'product_id'=>$productId,
        'quantity'=>10, 'primary_qty'=>10, 'primary_unit'=>1, 'net_unit_cost'=>10, 'subtotal'=>100,
        'received_primary_qty'=>0, 'received_secondary_qty'=>0, 'secondary_qty'=>0, 'delivery'=>0]);
    $receive = function ($quantity) use ($purchases, $purchaseId, $itemId) {
        return $purchases->receivePurchaseItems($purchaseId, [$itemId=>$quantity], '2026-09-13 10:00:00', 'Isolated test', 0);
    };
    $result = $receive(4);
    verify(!empty($result['status']), 'receive first four units');
    verify((int) $result['received_status'] !== 1, 'partial receipt leaves purchase incomplete');
    $item = $connection->get_where('purchase_items', ['id'=>$itemId])->row();
    verify((float) $item->received_primary_qty === 4.0, 'partial received quantity persisted');
    $result = $receive(7);
    verify(empty($result['status']), 'reject receipt exceeding remaining quantity');
    verify((float) $connection->get_where('purchase_items', ['id'=>$itemId])->row()->received_primary_qty === 4.0, 'failed receipt does not change item quantity');
    $result = $receive(6);
    verify(!empty($result['status']) && (int) $result['received_status'] === 1, 'receive remaining six units and complete purchase');
    $stock = $connection->select_sum('qty_base')->get_where('stock_batches', ['product_id'=>$productId])->row();
    verify((float) $stock->qty_base === 10.0, 'stock batches contain exactly ten received units');
    verify(empty($receive(1)['status']), 'reject duplicate receive after completion');
    verify($connection->where('purchase_id',$purchaseId)->count_all_results('purchase_receipts') === 2, 'two receipt history records only');
    $receipt = $connection->where('purchase_id',$purchaseId)->order_by('id','DESC')->get('purchase_receipts',1)->row();
    verify((int)$receipt->stock_batch_id > 0, 'new receipt records its exact stock batch');
    $changed = $purchases->updatePurchaseReceipt($receipt->id, 5);
    verify(!empty($changed['status']), 'decrease an unused receipt');
    verify((float)$connection->select_sum('qty_base')->get_where('stock_batches',['product_id'=>$productId])->row()->qty_base === 9.0, 'decrease reverses one stock unit');
    $changed = $purchases->updatePurchaseReceipt($receipt->id, 7);
    verify(empty($changed['status']), 'edit cannot exceed ordered quantity');
    $changed = $purchases->updatePurchaseReceipt($receipt->id, 6);
    verify(!empty($changed['status']), 'increase receipt back within ordered quantity');
    $deleted = $purchases->deletePurchaseReceipt($receipt->id);
    $afterStock = (float) $connection->select_sum('qty_base')->get_where('stock_batches', ['product_id'=>$productId])->row()->qty_base;
    $afterReceived = (float) $connection->get_where('purchase_items', ['id'=>$itemId])->row()->received_primary_qty;
    verify(!empty($deleted['status']) && $afterStock === 4.0 && $afterReceived === 4.0, 'receipt delete reverses stock and received quantity');
    verify(!$connection->get_where('purchase_receipts',['id'=>$receipt->id])->row(), 'receipt history row is deleted');
    verify((int)$connection->get_where('purchases',['id'=>$purchaseId])->row()->received === 2, 'delete restores partial purchase status');
    verify((float)$connection->select_sum('qty_base')->get_where('stock_movements',['product_id'=>$productId])->row()->qty_base === 4.0, 'signed movement audit reconciles to remaining stock');

    $dualProduct = $insert('products', array_merge($productData, ['code'=>'QA-DUAL','base_unit_id'=>1,'is_dual_unit'=>1,'secondary_unit_id'=>3]));
    $insert('product_unit_conversions',['product_id'=>$dualProduct,'unit_id'=>2,'operation_value'=>24,'operator'=>'*']);
    $dualPurchase = $insert('purchases',['reference'=>'QA-DUAL','store_id'=>$storeId,'received'=>0,'total'=>200,'paid'=>0]);
    $dualItem = $insert('purchase_items',['purchase_id'=>$dualPurchase,'product_id'=>$dualProduct,'quantity'=>10,
        'primary_qty'=>10,'primary_unit'=>2,'secondary_qty'=>20,'secondary_unit'=>3,'net_unit_cost'=>10,'subtotal'=>100,
        'delivery'=>100,'received_primary_qty'=>0,'received_secondary_qty'=>0]);
    $received = $purchases->receivePurchaseItems($dualPurchase,[$dualItem=>4],'2026-09-13 10:00:00','Dual test',0);
    verify(!empty($received['status']), 'receive converted primary and secondary units');
    $dualReceipt = $connection->get_where('purchase_receipts',['purchase_item_id'=>$dualItem])->row();
    $dualBatch = $connection->get_where('stock_batches',['id'=>$dualReceipt->stock_batch_id])->row();
    verify((float)$dualBatch->qty_base === 96.0 && (float)$dualBatch->qty_secondary === 8.0, 'receive uses 24 base units per selected unit');
    // Changing today's conversion must not change the historical receipt factor.
    $connection->where('product_id',$dualProduct)->update('product_unit_conversions',['operation_value'=>30]);
    $edited = $purchases->updatePurchaseReceipt($dualReceipt->id,3);
    verify(!empty($edited['status']), 'edit dual-unit receipt');
    $after = $connection->get_where('stock_batches',['id'=>$dualReceipt->stock_batch_id])->row();
    verify((float)$after->qty_base === 72.0 && (float)$after->qty_secondary === 6.0, 'correction preserves historical conversion');
    verify((float)$after->delivery === 30.0 && (float)$after->cost_per_base === (float)$dualBatch->cost_per_base, 'delivery scales while landed unit cost stays unchanged');
    verify((float)$connection->get_where('purchase_items',['id'=>$dualItem])->row()->received_secondary_qty === 6.0, 'secondary received quantity reconciles');

    $insert('cogs_logs',['product_id'=>$dualProduct,'batch_id'=>$after->id,'sale_id'=>1,'sale_item_id'=>1,'qty_base'=>1]);
    $blocked = $purchases->deletePurchaseReceipt($dualReceipt->id);
    verify(empty($blocked['status']), 'sold stock cannot be reversed');
    verify((float)$connection->get_where('stock_batches',['id'=>$after->id])->row()->qty_base === 72.0, 'rejected reversal leaves stock unchanged');
    $firstReceipt = $connection->get_where('purchase_receipts',['purchase_item_id'=>$itemId])->row();
    $connection->where('id',$firstReceipt->id)->update('purchase_receipts',['stock_batch_id'=>null]);
    $connection->where('batch_id',$firstReceipt->stock_batch_id)->where('movement_type','purchase')
        ->update('stock_movements',['purchase_receipt_id'=>null]);
    $originalBatch = $connection->get_where('stock_batches',['id'=>$firstReceipt->stock_batch_id])->row_array();
    unset($originalBatch['id']);
    $duplicateBatch = $insert('stock_batches',$originalBatch);
    verify(empty($purchases->updatePurchaseReceipt($firstReceipt->id,3)['status']), 'ambiguous legacy batch is rejected');
    $connection->where('id',$duplicateBatch)->delete('stock_batches');
    verify(!empty($purchases->updatePurchaseReceipt($firstReceipt->id,3)['status']), 'unique unused legacy receipt can be corrected');
    verify((int)$connection->get_where('purchase_receipts',['id'=>$firstReceipt->id])->row()->stock_batch_id === (int)$firstReceipt->stock_batch_id, 'legacy correction persists exact batch link');
    $connection->where('batch_id',$firstReceipt->stock_batch_id)->where('movement_type','purchase')
        ->update('stock_movements',['purchase_receipt_id'=>999999]);
    verify(empty($purchases->updatePurchaseReceipt($firstReceipt->id,2)['status']), 'conflicting original receipt link is rejected');
    $connection->where('batch_id',$firstReceipt->stock_batch_id)->where('movement_type','purchase')
        ->update('stock_movements',['purchase_receipt_id'=>$firstReceipt->id]);
    $insert('stock_movements',['batch_id'=>$firstReceipt->stock_batch_id,'product_id'=>$productId,'movement_type'=>'transfer','qty_base'=>1,'from_store_id'=>$storeId,'to_store_id'=>2]);
    verify(empty($purchases->updatePurchaseReceipt($firstReceipt->id,2)['status']), 'transferred stock cannot be corrected');
    verify(empty($purchases->deletePurchaseSafely($purchaseId)['status']), 'purchase with receipts cannot be deleted');
    verify($connection->get_where('purchases',['id'=>$purchaseId])->num_rows() === 1, 'blocked purchase remains');
    verify(empty($purchases->deletePurchaseItem($itemId)['status']), 'direct item deletion cannot leave stale invoice totals');
    verify($connection->get_where('purchase_items',['id'=>$itemId])->num_rows() === 1, 'blocked item remains');
    verify(empty($purchases->deletePurchaseSafely(999999)['status']), 'missing purchase deletion fails cleanly');
    $cleanPurchase = $insert('purchases',['reference'=>'QA-DELETE','store_id'=>$storeId,'received'=>0,'paid'=>0,'total'=>10]);
    $cleanItem = $insert('purchase_items',['purchase_id'=>$cleanPurchase,'product_id'=>$productId,'primary_qty'=>1,'quantity'=>1,'received_primary_qty'=>0,'received_secondary_qty'=>0]);
    $payment = $insert('ppayments',['purchase_id'=>$cleanPurchase,'amount'=>10]);
    verify(empty($purchases->deletePurchaseSafely($cleanPurchase)['status']), 'payment rows block deletion even with stale paid header');
    $connection->where('id',$payment)->delete('ppayments');
    $connection->where('id',$cleanPurchase)->update('purchases',['paid'=>10]);
    verify(empty($purchases->deletePurchaseSafely($cleanPurchase)['status']), 'paid header blocks deletion without payment rows');
    $connection->where('id',$cleanPurchase)->update('purchases',['paid'=>0]);
    $connection->where('id',$cleanItem)->update('purchase_items',['received_primary_qty'=>1]);
    verify(empty($purchases->deletePurchaseSafely($cleanPurchase)['status']), 'orphan received quantities block deletion');
    $connection->where('id',$cleanItem)->update('purchase_items',['received_primary_qty'=>0]);
    $orphanBatch = $insert('stock_batches',['purchase_id'=>$cleanPurchase,'product_id'=>$productId,'store_id'=>$storeId,'qty_base'=>1,'qty_primary'=>1,'qty_secondary'=>0]);
    verify(empty($purchases->deletePurchaseSafely($cleanPurchase)['status']), 'remaining stock without receipt blocks deletion');
    $connection->where('id',$orphanBatch)->update('stock_batches',['qty_base'=>0,'qty_primary'=>0]);
    $audit = $insert('stock_movements',['purchase_id'=>$cleanPurchase,'batch_id'=>$orphanBatch,'product_id'=>$productId,'movement_type'=>'adjustment','qty_base'=>-1]);
    verify(!empty($purchases->deletePurchaseSafely($cleanPurchase)['status']), 'unpaid unreceived purchase can be deleted');
    verify(!$connection->get_where('purchase_items',['id'=>$cleanItem])->row(), 'successful deletion removes purchase items');
    verify($connection->get_where('stock_movements',['id'=>$audit])->num_rows() === 1, 'purchase deletion preserves stock movement audit');
    verify($connection->get_where('stock_batches',['id'=>$orphanBatch])->num_rows() === 1, 'purchase deletion preserves zero-balance audit batch');
    $editPurchase = $insert('purchases',['reference'=>'QA-EDIT','store_id'=>$storeId,'received'=>0,'paid'=>0,'total'=>99,'delivery'=>0]);
    $editItem = $insert('purchase_items',['purchase_id'=>$editPurchase,'product_id'=>$productId,'primary_qty'=>1,'received_primary_qty'=>0,'received_secondary_qty'=>0]);
    $editRows = [
        ['product_id'=>$productId,'primary_qty'=>2,'primary_unit'=>1,'net_unit_cost'=>10],
        ['product_id'=>$dualProduct,'primary_qty'=>1,'primary_unit'=>2,'secondary_qty'=>2,'secondary_unit'=>3,'net_unit_cost'=>20]
    ];
    verify($purchases->updatePurchase($editPurchase,['delivery'=>10,'total'=>99999],$editRows), 'edit unreceived purchase with two unit types');
    $editHeader = $connection->get_where('purchases',['id'=>$editPurchase])->row();
    verify((float)$editHeader->total === 50.0, 'edit recomputes primary cost plus delivery once and ignores submitted total');
    $savedRows = $connection->where('purchase_id',$editPurchase)->order_by('id')->get('purchase_items')->result();
    verify((float)$savedRows[0]->delivery === 6.67 && (float)$savedRows[1]->delivery === 3.33, 'delivery allocation reconciles rounding residue');
    verify(!$connection->get_where('stock_batches',['purchase_id'=>$editPurchase])->row(), 'editing does not create received stock');
    verify(!$purchases->updatePurchase($editPurchase,['delivery'=>10],[]), 'empty edit rejected');
    $invalidRows = $editRows; $invalidRows[1]['product_id'] = 999999;
    verify(!$purchases->updatePurchase($editPurchase,['delivery'=>10],$invalidRows), 'invalid later product rejects whole edit');
    verify($connection->get_where('purchase_items',['id'=>$savedRows[0]->id])->num_rows() === 1, 'failed edit preserves original item ids');
    verify((float)$connection->get_where('purchases',['id'=>$editPurchase])->row()->total === 50.0, 'failed edit preserves header total');
    verify(!$purchases->updatePurchase($purchaseId,['delivery'=>0],$editRows), 'receipt-backed purchase edit blocked');
    $editPayment = $insert('ppayments',['purchase_id'=>$editPurchase,'amount'=>5]);
    verify(!$purchases->updatePurchase($editPurchase,['delivery'=>0],$editRows), 'payment-backed purchase edit blocked');
    $connection->where('id',$editPayment)->delete('ppayments');
    verify(!$purchases->updatePurchase($editPurchase,['received'=>1],$editRows), 'edit cannot bypass receive workflow');
    verify($purchases->updatePurchase($editPurchase,['delivery'=>10],[$editRows[0]]), 'remove one item through complete purchase edit');
    verify((float)$connection->get_where('purchases',['id'=>$editPurchase])->row()->total === 30.0, 'item removal recalculates invoice total');
    verify((float)$connection->get_where('purchase_items',['purchase_id'=>$editPurchase])->row()->delivery === 10.0, 'remaining item gets full delivery allocation');
    require __DIR__ . '/purchase_edit_controller.php';
    echo 'RESULT: ' . $passes . ' checks passed.' . PHP_EOL;
} finally {
    if ($connection) { $connection->close(); }
    // Drop only the fresh random database created by this invocation.
    if ($created) { $admin->query('DROP DATABASE ' . identifier($testName)); echo "Disposable database removed.\n"; }
    $admin->close();
}
