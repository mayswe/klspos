<?php
// Explicit local database selection is required; no stock quantities are changed.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', dirname(__DIR__) . '/lib/');
define('APPPATH', dirname(__DIR__) . '/app/');
define('ENVIRONMENT', 'development');
require APPPATH . 'config/database.php';
$config = $db['default'];
$options = getopt('', ['database:']);
if (($options['database'] ?? '') !== $config['database']) {
    fwrite(STDERR, "Specify --database with the exact configured database name.\n");
    exit(1);
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection = new mysqli($config['hostname'], $config['username'], $config['password'], $config['database']);
require __DIR__ . '/lib/receipt_schema.php';
upgrade_receipt_schema($connection, $config['dbprefix']);
echo "Receipt link columns verified. No business quantities changed.\n";
$connection->close();
