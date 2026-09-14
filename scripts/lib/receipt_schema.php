<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Shared idempotent schema upgrade; caller supplies an explicitly selected database.
function upgrade_receipt_schema(mysqli $db, string $prefix): void
{
    foreach (['purchase_receipts'=>['stock_batch_id','INT NULL'],
        'stock_movements'=>['purchase_receipt_id','BIGINT UNSIGNED NULL']] as $table=>$spec) {
        $name = '`' . str_replace('`','``',$prefix.$table) . '`';
        $columns = array_column($db->query('SHOW COLUMNS FROM '.$name)->fetch_all(MYSQLI_ASSOC),'Field');
        if (!in_array($spec[0],$columns,true)) {
            $db->query('ALTER TABLE '.$name.' ADD COLUMN `'.$spec[0].'` '.$spec[1]);
        }
    }
}
