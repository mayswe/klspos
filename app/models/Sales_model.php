<?php
 if (!defined('BASEPATH')) {
     exit('No direct script access allowed');
 }

class Sales_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPayment($data = [])
    {
        if ($this->db->insert('payments', $data)) {
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCard($data['gc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['gc_no']]);
            }
            $this->syncSalePayments($data['sale_id']);
            return true;
        }
        return false;
    }

    /**
     * Create the ERP audit table used by controlled sale deletion.
     *
     * The application database user normally has CREATE permission in this
     * installation. If it does not, deletion is deliberately blocked so that
     * an ERP document can never disappear without an audit record.
     */
    public function ensureSalesAuditLogTable()
    {
        if ($this->db->table_exists('erp_audit_logs')) {
            return true;
        }

        $table = $this->db->dbprefix('erp_audit_logs');
        $sql = "
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `module` VARCHAR(50) NOT NULL,
                `action` VARCHAR(80) NOT NULL,
                `entity_type` VARCHAR(50) NOT NULL,
                `entity_id` BIGINT UNSIGNED NOT NULL,
                `reference_no` VARCHAR(191) NULL,
                `result` VARCHAR(30) NOT NULL,
                `reason` TEXT NULL,
                `before_data` LONGTEXT NULL,
                `after_data` LONGTEXT NULL,
                `user_id` INT UNSIGNED NULL,
                `store_id` INT UNSIGNED NULL,
                `ip_address` VARCHAR(45) NULL,
                `user_agent` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_erp_audit_entity` (`entity_type`, `entity_id`),
                KEY `idx_erp_audit_created` (`created_at`),
                KEY `idx_erp_audit_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $created = $this->db->query($sql);

        if (!$created) {
            log_message(
                'error',
                '[SALE DELETE AUDIT] Could not create ERP audit table.'
            );
        }

        return (bool) $created;
    }

    private function encodeSalesAuditData($data)
    {
        $encoded = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_PARTIAL_OUTPUT_ON_ERROR
        );

        return $encoded !== false ? $encoded : '{}';
    }

    /** Do not copy full card/gift-card numbers into the ERP audit payload. */
    private function maskSalesAuditIdentifier($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $last_four = substr($value, -4);
        return str_repeat('*', max(0, strlen($value) - 4)) . $last_four;
    }

    private function buildPaymentAuditSnapshot($payment, $sale)
    {
        $payment_data = is_object($payment)
            ? get_object_vars($payment)
            : (array) $payment;

        foreach (['cc_cvv', 'cc_cvv2', 'cc_cvc'] as $secret_field) {
            unset($payment_data[$secret_field]);
        }

        if (isset($payment_data['cc_no'])) {
            $payment_data['cc_no'] = $this->maskSalesAuditIdentifier(
                $payment_data['cc_no']
            );
        }

        if (isset($payment_data['gc_no'])) {
            $payment_data['gc_no'] = $this->maskSalesAuditIdentifier(
                $payment_data['gc_no']
            );
        }

        return [
            'payment' => $payment_data,
            'sale'    => $sale,
        ];
    }

    private function writeSalesAuditLog(
        $sale,
        $result,
        $reason,
        array $context,
        array $before_data,
        array $after_data = []
    ) {
        if (!$this->ensureSalesAuditLogTable()) {
            return false;
        }

        $entity_id = isset($context['entity_id'])
            ? (int) $context['entity_id']
            : ($sale ? (int) $sale->id : 0);

        $reference_no = isset($context['reference_no'])
            ? (string) $context['reference_no']
            : ($sale && isset($sale->reference_no)
                ? (string) $sale->reference_no
                : null);

        return (bool) $this->db->insert('erp_audit_logs', [
            'module'        => 'sales',
            'action'        => (string) ($context['action'] ?? 'delete_sale'),
            'entity_type'   => (string) ($context['entity_type'] ?? 'sale'),
            'entity_id'     => $entity_id,
            'reference_no'  => $reference_no,
            'result'        => (string) $result,
            'reason'        => (string) $reason,
            'before_data'   => $this->encodeSalesAuditData($before_data),
            'after_data'    => $this->encodeSalesAuditData($after_data),
            'user_id'       => isset($context['user_id'])
                ? (int) $context['user_id']
                : null,
            'store_id'      => isset($context['store_id'])
                ? (int) $context['store_id']
                : null,
            'ip_address'    => substr(
                (string) ($context['ip_address'] ?? ''),
                0,
                45
            ),
            'user_agent'    => substr(
                (string) ($context['user_agent'] ?? ''),
                0,
                255
            ),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    private function getSaleReturnCount($sale_id)
    {
        $sale_id = (int) $sale_id;

        if (
            $this->db->table_exists('return_sales') &&
            $this->db->field_exists('sale_id', 'return_sales')
        ) {
            return (int) $this->db
                ->where('sale_id', $sale_id)
                ->count_all_results('return_sales');
        }

        if (
            $this->db->table_exists('return_items') &&
            $this->db->field_exists('sale_id', 'return_items')
        ) {
            return (int) $this->db
                ->where('sale_id', $sale_id)
                ->count_all_results('return_items');
        }

        return 0;
    }

    private function getSaleStockItemCount($sale_id)
    {
        return (int) $this->db
            ->from('sale_items AS si')
            ->join('products AS p', 'p.id = si.product_id', 'inner')
            ->where('si.sale_id', (int) $sale_id)
            ->where('si.quantity >', 0)
            ->where_in('p.type', ['standard', 'combo'])
            ->count_all_results();
    }

    private function getUntracedSaleStockItemIds($sale_id)
    {
        $sale_id = (int) $sale_id;

        if (!$this->db->table_exists('cogs_logs')) {
            $rows = $this->db
                ->select('si.id')
                ->from('sale_items AS si')
                ->join('products AS p', 'p.id = si.product_id', 'inner')
                ->where('si.sale_id', $sale_id)
                ->where('si.quantity >', 0)
                ->where_in('p.type', ['standard', 'combo'])
                ->get()
                ->result();

            return array_map(function ($row) {
                return (int) $row->id;
            }, $rows);
        }

        $rows = $this->db
            ->select('si.id, COUNT(cl.id) AS cogs_count', false)
            ->from('sale_items AS si')
            ->join('products AS p', 'p.id = si.product_id', 'inner')
            ->join(
                'cogs_logs AS cl',
                'cl.sale_id = si.sale_id AND cl.sale_item_id = si.id',
                'left'
            )
            ->where('si.sale_id', $sale_id)
            ->where('si.quantity >', 0)
            ->where_in('p.type', ['standard', 'combo'])
            ->group_by('si.id')
            ->having('COUNT(cl.id) = 0', null, false)
            ->get()
            ->result();

        return array_map(function ($row) {
            return (int) $row->id;
        }, $rows);
    }

    private function getSaleCogsCount($sale_id)
    {
        if (!$this->db->table_exists('cogs_logs')) {
            return 0;
        }

        return (int) $this->db
            ->where('sale_id', (int) $sale_id)
            ->count_all_results('cogs_logs');
    }

    private function buildSaleDeleteSnapshot(
        $sale,
        array $items,
        $payment_count,
        $return_count,
        $cogs_count
    ) {
        $delivery_logs = [];

        if ($this->db->table_exists('sale_delivery_logs')) {
            $delivery_logs = $this->db
                ->where('sale_id', (int) $sale->id)
                ->order_by('id', 'ASC')
                ->get('sale_delivery_logs')
                ->result();
        }

        return [
            'sale'          => $sale,
            'items'         => $items,
            'payment_count' => (int) $payment_count,
            'return_count'  => (int) $return_count,
            'cogs_count'    => (int) $cogs_count,
            'delivery_logs' => $delivery_logs,
        ];
    }

    /**
     * ERP-safe controlled deletion.
     *
     * Rules:
     * - Payment records must be reversed/deleted first.
     * - Sales returns must be reversed first.
     * - A stock-bearing delivered sale must have COGS trace rows.
     * - FIFO restoration, dependent-row cleanup and the success audit row are
     *   committed in one database transaction.
     */
    public function deleteInvoiceERP($id, array $context = [])
    {
        $id = (int) $id;
        $reason = trim((string) ($context['reason'] ?? ''));
        $context['entity_id'] = $id;

        $response = [
            'success' => false,
            'code'    => 'delete_failed',
            'message' => 'အရောင်းဘောင်ချာကို ဖျက်၍မရပါ။',
        ];

        if (!$this->ensureSalesAuditLogTable()) {
            $response['code'] = 'audit_log_unavailable';
            $response['message'] =
                'Audit log သိမ်း၍မရသောကြောင့် အရောင်းဘောင်ချာကို မဖျက်ပါ။';
            return $response;
        }

        if ($reason === '') {
            $response['code'] = 'delete_reason_required';
            $response['message'] =
                'ဖျက်ရသည့်အကြောင်းပြချက်ကို ထည့်ပေးပါ။';

            $this->writeSalesAuditLog(
                null,
                'blocked',
                '',
                $context,
                ['sale_id' => $id, 'error' => 'delete_reason_required']
            );

            return $response;
        }

        $sale = $this->getSaleByID($id);

        if (!$sale) {
            $response['code'] = 'sale_not_found';
            $response['message'] = 'အရောင်းဘောင်ချာကို ရှာမတွေ့ပါ။';

            $this->writeSalesAuditLog(
                null,
                'blocked',
                $reason,
                $context,
                ['sale_id' => $id, 'error' => 'sale_not_found']
            );

            return $response;
        }

        $items = $this->getAllSaleItems($id);
        $items = $items ? $items : [];
        $payment_count = (int) $this->db
            ->where('sale_id', $id)
            ->count_all_results('payments');
        $return_count = $this->getSaleReturnCount($id);
        $cogs_count = $this->getSaleCogsCount($id);
        $stock_item_count = $this->getSaleStockItemCount($id);
        $untraced_stock_item_ids = $this->getUntracedSaleStockItemIds($id);

        $snapshot = $this->buildSaleDeleteSnapshot(
            $sale,
            $items,
            $payment_count,
            $return_count,
            $cogs_count
        );

        if ($payment_count > 0) {
            $response['code'] = 'payments_exist';
            $response['message'] =
                'ဤအရောင်းဘောင်ချာတွင် ငွေလက်ခံမှုမှတ်တမ်းရှိနေသောကြောင့် ' .
                'ငွေလက်ခံမှုကို အရင် Reverse/ဖျက်ပေးပါ။';

            $this->writeSalesAuditLog(
                $sale,
                'blocked',
                $reason,
                $context,
                $snapshot,
                ['blocker' => 'payments_exist']
            );

            log_message(
                'warning',
                '[SALE DELETE BLOCKED] Payments exist. Sale ID: ' . $id .
                ' | Payment Count: ' . $payment_count
            );

            return $response;
        }

        if ($return_count > 0) {
            $response['code'] = 'returns_exist';
            $response['message'] =
                'ဤအရောင်းဘောင်ချာတွင် Sales Return မှတ်တမ်းရှိနေသောကြောင့် ' .
                'Return ကို အရင် Reverse လုပ်ပေးပါ။';

            $this->writeSalesAuditLog(
                $sale,
                'blocked',
                $reason,
                $context,
                $snapshot,
                ['blocker' => 'returns_exist']
            );

            log_message(
                'warning',
                '[SALE DELETE BLOCKED] Returns exist. Sale ID: ' . $id .
                ' | Return Count: ' . $return_count
            );

            return $response;
        }

        if (
            $stock_item_count > 0 &&
            ($cogs_count <= 0 || !empty($untraced_stock_item_ids))
        ) {
            $response['code'] = 'stock_trace_missing';
            $response['message'] =
                'Stock ထုတ်ထားသောမှတ်တမ်းနှင့် FIFO COGS trace မကိုက်ညီသဖြင့် ' .
                'အရောင်းဘောင်ချာကို မဖျက်ပါ။';

            $this->writeSalesAuditLog(
                $sale,
                'blocked',
                $reason,
                $context,
                $snapshot,
                [
                    'blocker' => 'stock_trace_missing',
                    'untraced_sale_item_ids' => $untraced_stock_item_ids,
                ]
            );

            log_message(
                'error',
                '[SALE DELETE BLOCKED] Missing COGS trace. Sale ID: ' . $id .
                ' | Stock Item Count: ' . $stock_item_count .
                ' | Untraced Sale Item IDs: ' .
                implode(',', $untraced_stock_item_ids)
            );

            return $response;
        }

        $this->db->trans_begin();

        try {
            $sales_table = $this->db->dbprefix('sales');
            $locked_sale = $this->db->query(
                "SELECT * FROM `{$sales_table}` WHERE `id` = ? FOR UPDATE",
                [$id]
            )->row();

            if (!$locked_sale) {
                throw new Exception('Sale disappeared before deletion.');
            }

            /* Re-check dependencies after locking the sale. */
            $locked_payment_count = (int) $this->db
                ->where('sale_id', $id)
                ->count_all_results('payments');
            $locked_return_count = $this->getSaleReturnCount($id);

            if ($locked_payment_count > 0 || $locked_return_count > 0) {
                throw new Exception(
                    'Sale dependencies changed during deletion.'
                );
            }

            $this->load->model('pos_model');
            $stock_result = $this->pos_model
                ->restoreSaleStockFromCogs(
                    $id,
                    isset($locked_sale->store_id)
                        ? (int) $locked_sale->store_id
                        : null
                );

            if (empty($stock_result['success'])) {
                throw new Exception(
                    (string) ($stock_result['message'] ??
                        'Sale stock restoration failed.')
                );
            }

            if (
                $this->db->table_exists('sale_delivery_cogs') &&
                !$this->db->delete('sale_delivery_cogs', ['sale_id' => $id])
            ) {
                throw new Exception('Sale delivery COGS mapping cleanup failed.');
            }

            if (
                $this->db->table_exists('sale_delivery_logs') &&
                !$this->db->delete('sale_delivery_logs', ['sale_id' => $id])
            ) {
                throw new Exception('Sale delivery history cleanup failed.');
            }

            if (
                $this->db->table_exists('cogs_logs') &&
                !$this->db->delete('cogs_logs', ['sale_id' => $id])
            ) {
                throw new Exception('COGS cleanup failed.');
            }

            if (!$this->db->delete('sale_items', ['sale_id' => $id])) {
                throw new Exception('Sale item cleanup failed.');
            }

            if (!$this->db->delete('sales', ['id' => $id])) {
                throw new Exception('Sale delete failed.');
            }

            $audit_saved = $this->writeSalesAuditLog(
                $sale,
                'success',
                $reason,
                $context,
                $snapshot,
                [
                    'stock_restoration' => $stock_result,
                    'sale_deleted'      => true,
                ]
            );

            if (!$audit_saved) {
                throw new Exception('ERP audit log insert failed.');
            }

            if ($this->db->trans_status() === false) {
                throw new Exception('Database transaction failed.');
            }

            $this->db->trans_commit();

            log_message(
                'info',
                '[SALE DELETE SUCCESS] Sale ID: ' . $id .
                ' | Reference: ' . (string) ($sale->reference_no ?? '') .
                ' | User ID: ' . (int) ($context['user_id'] ?? 0) .
                ' | Store ID: ' . (int) ($context['store_id'] ?? 0) .
                ' | Restored Base: ' .
                    (float) ($stock_result['restored_base'] ?? 0) .
                ' | Restored Secondary: ' .
                    (float) ($stock_result['restored_secondary'] ?? 0) .
                ' | IP: ' . (string) ($context['ip_address'] ?? '') .
                ' | Reason: ' . $reason
            );

            return [
                'success' => true,
                'code'    => 'sale_deleted',
                'message' => 'အရောင်းဘောင်ချာကို အောင်မြင်စွာ ဖျက်ပြီးပါပြီ။',
                'stock'   => $stock_result,
            ];
        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->writeSalesAuditLog(
                $sale,
                'failed',
                $reason,
                $context,
                $snapshot,
                ['error' => $e->getMessage()]
            );

            log_message(
                'error',
                '[SALE DELETE FAILED] Sale ID: ' . $id .
                ' | Reference: ' . (string) ($sale->reference_no ?? '') .
                ' | User ID: ' . (int) ($context['user_id'] ?? 0) .
                ' | IP: ' . (string) ($context['ip_address'] ?? '') .
                ' | Error: ' . $e->getMessage()
            );

            $response['code'] = 'transaction_failed';
            $response['message'] =
                'Stock နှင့်ဆက်စပ်မှတ်တမ်းများကို လုံခြုံစွာပြန်လှန်၍ ' .
                'မရသောကြောင့် အရောင်းဘောင်ချာကို မဖျက်ပါ။';

            return $response;
        }
    }

    /** Keep legacy callers on the same protected ERP workflow. */
    public function deleteInvoice($id)
    {
        $result = $this->deleteInvoiceERP((int) $id, [
            'reason'     => 'Legacy delete request',
            'user_id'    => (int) $this->session->userdata('user_id'),
            'store_id'   => (int) $this->session->userdata('store_id'),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ]);

        return !empty($result['success']);
    }


    public function deleteOpenedSale($id)
    {
        if ($this->db->delete('suspended_items', ['suspend_id' => $id]) && $this->db->delete('suspended_sales', ['id' => $id])) {
            return true;
        }
        return false;
    }

    /**
     * Reverse a sales payment under the same ERP audit policy.
     */
    public function reversePaymentERP($id, array $context = [])
    {
        $id = (int) $id;
        $reason = trim((string) ($context['reason'] ?? ''));
        $context['action'] = 'reverse_sale_payment';
        $context['entity_type'] = 'sale_payment';
        $context['entity_id'] = $id;

        $response = [
            'success' => false,
            'code'    => 'payment_reverse_failed',
            'message' => 'ငွေလက်ခံမှုကို Reverse လုပ်၍မရပါ။',
        ];

        if (!$this->ensureSalesAuditLogTable()) {
            $response['code'] = 'audit_log_unavailable';
            $response['message'] =
                'Audit log သိမ်း၍မရသောကြောင့် ငွေလက်ခံမှုကို မဖျက်ပါ။';
            return $response;
        }

        if ($reason === '') {
            $response['code'] = 'reverse_reason_required';
            $response['message'] =
                'Reverse လုပ်ရသည့်အကြောင်းပြချက်ကို ထည့်ပေးပါ။';

            $this->writeSalesAuditLog(
                null,
                'blocked',
                '',
                $context,
                ['payment_id' => $id, 'error' => 'reverse_reason_required']
            );

            return $response;
        }

        $payment = $this->getPaymentByID($id);

        if (!$payment || empty($payment->sale_id)) {
            $response['code'] = 'payment_not_found';
            $response['message'] = 'Sales payment ကို ရှာမတွေ့ပါ။';

            $this->writeSalesAuditLog(
                null,
                'blocked',
                $reason,
                $context,
                ['payment_id' => $id, 'error' => 'payment_not_found']
            );

            return $response;
        }

        $sale = $this->getSaleByID((int) $payment->sale_id);

        if (!$sale) {
            $response['code'] = 'sale_not_found';
            $response['message'] =
                'Payment နှင့်ချိတ်ဆက်ထားသော အရောင်းဘောင်ချာကို ရှာမတွေ့ပါ။';

            $this->writeSalesAuditLog(
                null,
                'blocked',
                $reason,
                $context,
                $this->buildPaymentAuditSnapshot($payment, null),
                ['blocker' => 'sale_not_found']
            );

            return $response;
        }

        $context['reference_no'] = isset($payment->reference)
            ? (string) $payment->reference
            : (string) ($sale->reference_no ?? '');

        $snapshot = $this->buildPaymentAuditSnapshot($payment, $sale);

        if (
            strtolower((string) ($payment->paid_by ?? '')) === 'stripe' &&
            !empty($payment->transaction_id)
        ) {
            $response['code'] = 'gateway_refund_required';
            $response['message'] =
                'Stripe payment ဖြစ်သောကြောင့် Gateway Refund အရင်လုပ်ပြီးမှ ' .
                'local payment record ကို Reverse လုပ်နိုင်ပါသည်။';

            $this->writeSalesAuditLog(
                $sale,
                'blocked',
                $reason,
                $context,
                $snapshot,
                ['blocker' => 'gateway_refund_required']
            );

            log_message(
                'warning',
                '[SALE PAYMENT REVERSE BLOCKED] Gateway refund required. ' .
                'Payment ID: ' . $id .
                ' | Sale ID: ' . (int) $payment->sale_id
            );

            return $response;
        }

        $this->db->trans_begin();

        try {
            $payments_table = $this->db->dbprefix('payments');
            $locked_payment = $this->db->query(
                "SELECT * FROM `{$payments_table}` WHERE `id` = ? FOR UPDATE",
                [$id]
            )->row();

            if (!$locked_payment) {
                throw new Exception('Payment disappeared before reversal.');
            }

            if ((int) $locked_payment->sale_id !== (int) $sale->id) {
                throw new Exception('Payment sale link changed before reversal.');
            }

            /* The success audit must reflect the row actually being reversed. */
            $snapshot = $this->buildPaymentAuditSnapshot(
                $locked_payment,
                $sale
            );

            if (
                strtolower((string) ($locked_payment->paid_by ?? '')) ===
                'gift_card'
            ) {
                $gc = $this->site->getGiftCard($locked_payment->gc_no);

                if (!$gc) {
                    throw new Exception('Gift card not found during reversal.');
                }

                $gift_card_updated = $this->db->update(
                    'gift_cards',
                    [
                        'balance' =>
                            (float) $gc->balance +
                            (float) $locked_payment->amount,
                    ],
                    ['card_no' => $locked_payment->gc_no]
                );

                if (!$gift_card_updated) {
                    throw new Exception('Gift card balance reversal failed.');
                }
            }

            if (!$this->db->delete('payments', ['id' => $id])) {
                throw new Exception('Payment row delete failed.');
            }

            if (!$this->syncSalePayments((int) $locked_payment->sale_id)) {
                throw new Exception('Sale payment status sync failed.');
            }

            $audit_saved = $this->writeSalesAuditLog(
                $sale,
                'success',
                $reason,
                $context,
                $snapshot,
                [
                    'payment_reversed' => true,
                    'gift_card_restored' =>
                        strtolower((string) ($locked_payment->paid_by ?? '')) ===
                        'gift_card',
                ]
            );

            if (!$audit_saved || $this->db->trans_status() === false) {
                throw new Exception('Payment reversal audit/transaction failed.');
            }

            $this->db->trans_commit();

            log_message(
                'info',
                '[SALE PAYMENT REVERSE SUCCESS] Payment ID: ' . $id .
                ' | Sale ID: ' . (int) $locked_payment->sale_id .
                ' | Amount: ' . (float) $locked_payment->amount .
                ' | Method: ' . (string) $locked_payment->paid_by .
                ' | User ID: ' . (int) ($context['user_id'] ?? 0) .
                ' | IP: ' . (string) ($context['ip_address'] ?? '') .
                ' | Reason: ' . $reason
            );

            return [
                'success' => true,
                'code'    => 'payment_reversed',
                'message' => 'ငွေလက်ခံမှုကို Reverse လုပ်ပြီးပါပြီ။',
            ];
        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->writeSalesAuditLog(
                $sale,
                'failed',
                $reason,
                $context,
                $snapshot,
                ['error' => $e->getMessage()]
            );

            log_message(
                'error',
                '[SALE PAYMENT REVERSE FAILED] Payment ID: ' . $id .
                ' | Sale ID: ' . (int) $payment->sale_id .
                ' | User ID: ' . (int) ($context['user_id'] ?? 0) .
                ' | Error: ' . $e->getMessage()
            );

            return $response;
        }
    }

    /** Keep legacy callers on the protected payment-reversal workflow. */
    public function deletePayment($id)
    {
        $result = $this->reversePaymentERP((int) $id, [
            'reason'     => 'Legacy payment delete request',
            'user_id'    => (int) $this->session->userdata('user_id'),
            'store_id'   => (int) $this->session->userdata('store_id'),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ]);

        return !empty($result['success']);
    }

    /**
     * Create the Sales delivery history and its exact COGS mapping tables.
     * Delivery is blocked if these audit-support tables cannot be created.
     */
    public function ensureSalesDeliveryTables()
    {
        $delivery_table = $this->db->dbprefix('sale_delivery_logs');
        $mapping_table = $this->db->dbprefix('sale_delivery_cogs');

        $delivery_sql = "
            CREATE TABLE IF NOT EXISTS `{$delivery_table}` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `sale_id` BIGINT UNSIGNED NOT NULL,
                `sale_item_id` BIGINT UNSIGNED NOT NULL,
                `product_id` BIGINT UNSIGNED NULL,
                `quantity` DECIMAL(25,4) NOT NULL DEFAULT 0,
                `delivered_at` DATETIME NOT NULL,
                `note` VARCHAR(500) NULL,
                `store_id` INT UNSIGNED NULL,
                `stock_deducted` TINYINT(1) NOT NULL DEFAULT 0,
                `created_by` INT UNSIGNED NULL,
                `created_at` DATETIME NOT NULL,
                `updated_by` INT UNSIGNED NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_sale_delivery_sale` (`sale_id`),
                KEY `idx_sale_delivery_item` (`sale_item_id`),
                KEY `idx_sale_delivery_date` (`delivered_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $mapping_sql = "
            CREATE TABLE IF NOT EXISTS `{$mapping_table}` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `delivery_id` BIGINT UNSIGNED NOT NULL,
                `cogs_log_id` BIGINT UNSIGNED NOT NULL,
                `sale_id` BIGINT UNSIGNED NOT NULL,
                `sale_item_id` BIGINT UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_sale_delivery_cogs` (`delivery_id`, `cogs_log_id`),
                KEY `idx_sale_delivery_cogs_sale` (`sale_id`),
                KEY `idx_sale_delivery_cogs_item` (`sale_item_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $ok = $this->db->query($delivery_sql) &&
            $this->db->query($mapping_sql) &&
            $this->ensureSalesAuditLogTable();

        if (!$ok) {
            log_message(
                'error',
                '[SALE DELIVERY SCHEMA] Required delivery/audit tables are unavailable.'
            );
        }

        return (bool) $ok;
    }

    private function getEffectiveSaleItemDelivery($item)
    {
        $ordered = (float) (($item->preorder_qty ?? 0) > 0
            ? $item->preorder_qty
            : ($item->quantity ?? 0));
        $fulfilled = max(0, (float) ($item->fulfilled_qty ?? 0));

        /* Compatibility with invoices completed by the previous all-at-once flow. */
        if (
            $fulfilled <= 0 &&
            (float) ($item->preorder_qty ?? 0) > 0 &&
            (float) ($item->quantity ?? 0) >= $ordered
        ) {
            $fulfilled = $ordered;
        }

        return [
            'ordered'   => max(0, $ordered),
            'delivered' => min(max(0, $ordered), $fulfilled),
            'remaining' => max(0, $ordered - $fulfilled),
        ];
    }

    public function getSaleDeliveryItems($sale_id)
    {
        $rows = $this->db
            ->select(
                'si.*, p.code AS product_code, p.name AS product_name, ' .
                'p.type AS product_type, u.name AS unit_name',
                false
            )
            ->from('sale_items AS si')
            ->join('products AS p', 'p.id = si.product_id', 'left')
            ->join('product_units AS u', 'u.id = si.unit_id', 'left')
            ->where('si.sale_id', (int) $sale_id)
            ->order_by('si.id', 'ASC')
            ->get()
            ->result();

        foreach ($rows as $row) {
            $qty = $this->getEffectiveSaleItemDelivery($row);
            $row->ordered_qty = $qty['ordered'];
            $row->delivered_qty = $qty['delivered'];
            $row->remaining_qty = $qty['remaining'];
        }

        return $rows;
    }

    public function getSaleDeliveryStatus($sale_id)
    {
        $items = $this->getSaleDeliveryItems((int) $sale_id);
        $ordered = 0.0;
        $delivered = 0.0;

        foreach ($items as $item) {
            $ordered += (float) $item->ordered_qty;
            $delivered += (float) $item->delivered_qty;
        }

        $status = 0;

        if ($ordered > 0 && $delivered + 0.000001 >= $ordered) {
            $status = 1;
        } elseif ($delivered > 0) {
            $status = 2;
        }

        return [
            'status'    => $status,
            'ordered'   => $ordered,
            'delivered' => min($ordered, $delivered),
            'remaining' => max(0, $ordered - $delivered),
            'items'     => $items,
        ];
    }

    public function getSaleDeliveryHistory($sale_id, $sale_item_id = null)
    {
        if (!$this->ensureSalesDeliveryTables()) {
            return [];
        }

        $this->db
            ->select(
                'dl.*, p.code AS product_code, p.name AS product_name, ' .
                'u.username AS created_by_name',
                false
            )
            ->from('sale_delivery_logs AS dl')
            ->join('products AS p', 'p.id = dl.product_id', 'left')
            ->join('users AS u', 'u.id = dl.created_by', 'left')
            ->where('dl.sale_id', (int) $sale_id)
            ->order_by('dl.delivered_at', 'DESC')
            ->order_by('dl.id', 'DESC');

        if ($sale_item_id !== null) {
            $this->db->where('dl.sale_item_id', (int) $sale_item_id);
        }

        return $this->db->get()->result();
    }

    public function getSaleDeliveryByID($delivery_id)
    {
        if (!$this->ensureSalesDeliveryTables()) {
            return false;
        }

        return $this->db
            ->where('id', (int) $delivery_id)
            ->get('sale_delivery_logs', 1)
            ->row();
    }

    private function getLastSaleCogsID($sale_id, $sale_item_id)
    {
        if (!$this->db->table_exists('cogs_logs')) {
            return 0;
        }

        $row = $this->db
            ->select_max('id', 'max_id')
            ->where('sale_id', (int) $sale_id)
            ->where('sale_item_id', (int) $sale_item_id)
            ->get('cogs_logs')
            ->row();

        return $row ? (int) $row->max_id : 0;
    }

    private function mapNewSaleDeliveryCogs(
        $delivery_id,
        $sale_id,
        $sale_item_id,
        $after_cogs_id
    ) {
        if (!$this->db->table_exists('cogs_logs')) {
            return 0;
        }

        $rows = $this->db
            ->select('id')
            ->where('sale_id', (int) $sale_id)
            ->where('sale_item_id', (int) $sale_item_id)
            ->where('id >', (int) $after_cogs_id)
            ->order_by('id', 'ASC')
            ->get('cogs_logs')
            ->result();

        foreach ($rows as $row) {
            if (!$this->db->insert('sale_delivery_cogs', [
                'delivery_id' => (int) $delivery_id,
                'cogs_log_id' => (int) $row->id,
                'sale_id' => (int) $sale_id,
                'sale_item_id' => (int) $sale_item_id,
                'created_at' => date('Y-m-d H:i:s'),
            ])) {
                throw new Exception('Delivery COGS mapping insert failed.');
            }
        }

        return count($rows);
    }

    private function deductSaleDeliveryStock(
        $sale,
        $item,
        $quantity,
        $delivery_id
    ) {
        /* Normal sales already deduct stock at invoicing time. */
        if ((float) ($item->preorder_qty ?? 0) <= 0) {
            return [
                'stock_deducted' => false,
                'cogs_count' => 0,
            ];
        }

        $product = $this->site->getProductByID((int) $item->product_id);

        if (!$product || $product->type === 'service') {
            return [
                'stock_deducted' => false,
                'cogs_count' => 0,
            ];
        }

        $this->load->model('pos_model');
        $last_cogs_id = $this->getLastSaleCogsID(
            (int) $sale->id,
            (int) $item->id
        );
        $stock_ok = true;

        if ($product->type === 'standard') {
            $stock_ok = $this->pos_model->deduct_stock(
                (int) $item->product_id,
                (int) $item->unit_id,
                (float) $quantity,
                (int) $sale->id,
                (int) $item->id,
                (int) $sale->store_id
            );
        } elseif ($product->type === 'combo') {
            $combo_items = $this->pos_model->getComboItemsByPID(
                (int) $product->id
            );

            if (empty($combo_items)) {
                throw new Exception('Combo product has no component items.');
            }

            foreach ($combo_items as $combo_item) {
                $component = $this->site->getProductByID(
                    (int) $combo_item->id
                );

                if (!$component || $component->type !== 'standard') {
                    continue;
                }

                $component_qty =
                    (float) $combo_item->qty * (float) $quantity;
                $component_unit_id = (int) (
                    $component->base_unit_id ??
                    ($component->unit_id ?? 0)
                );

                $stock_ok = $this->pos_model->deduct_stock(
                    (int) $component->id,
                    $component_unit_id,
                    $component_qty,
                    (int) $sale->id,
                    (int) $item->id,
                    (int) $sale->store_id
                );

                if (!$stock_ok) {
                    break;
                }
            }
        }

        if (!$stock_ok) {
            throw new Exception('Insufficient stock for delivery.');
        }

        $cogs_count = $this->mapNewSaleDeliveryCogs(
            (int) $delivery_id,
            (int) $sale->id,
            (int) $item->id,
            $last_cogs_id
        );

        if ($cogs_count <= 0) {
            throw new Exception('Delivery stock was not linked to FIFO COGS.');
        }

        return [
            'stock_deducted' => true,
            'cogs_count' => $cogs_count,
        ];
    }

    private function reverseSaleDeliveryStock($delivery, $sale)
    {
        $delivery_id = (int) $delivery->id;

        if (!(int) $delivery->stock_deducted) {
            $this->db->delete(
                'sale_delivery_cogs',
                ['delivery_id' => $delivery_id]
            );

            return [
                'success' => true,
                'cogs_count' => 0,
                'restored_base' => 0,
                'restored_secondary' => 0,
            ];
        }

        $mappings = $this->db
            ->select('cogs_log_id')
            ->where('delivery_id', $delivery_id)
            ->order_by('id', 'ASC')
            ->get('sale_delivery_cogs')
            ->result();
        $cogs_ids = array_map(function ($row) {
            return (int) $row->cogs_log_id;
        }, $mappings);

        if (empty($cogs_ids)) {
            throw new Exception('Delivery FIFO mapping is missing.');
        }

        $this->load->model('pos_model');
        $restore = $this->pos_model->restoreSaleStockFromCogs(
            (int) $sale->id,
            (int) $sale->store_id,
            $cogs_ids
        );

        if (empty($restore['success'])) {
            throw new Exception(
                (string) ($restore['message'] ??
                    'Delivery stock restoration failed.')
            );
        }

        if (
            !$this->db->where_in('id', $cogs_ids)->delete('cogs_logs') ||
            !$this->db->delete(
                'sale_delivery_cogs',
                ['delivery_id' => $delivery_id]
            )
        ) {
            throw new Exception('Delivery FIFO cleanup failed.');
        }

        return $restore;
    }

    private function syncSaleDeliveryFlag($sale_id)
    {
        $items = $this->getSaleDeliveryItems((int) $sale_id);
        $has_preorder = false;
        $preorder_pending = false;

        foreach ($items as $item) {
            if ((float) ($item->preorder_qty ?? 0) <= 0) {
                continue;
            }

            $has_preorder = true;

            if ((float) $item->remaining_qty > 0.000001) {
                $preorder_pending = true;
                break;
            }
        }

        if (!$has_preorder) {
            return true;
        }

        return (bool) $this->db->update(
            'sales',
            ['is_preorder' => $preorder_pending ? 1 : 0],
            ['id' => (int) $sale_id]
        );
    }

    public function recordSaleDelivery(
        $sale_id,
        array $delivery_map,
        $delivered_at,
        $note,
        array $context = []
    ) {
        $sale_id = (int) $sale_id;
        $context['action'] = 'deliver_sale_items';
        $context['entity_type'] = 'sale';
        $context['entity_id'] = $sale_id;
        $reason = trim((string) ($context['reason'] ?? 'Product delivery'));

        $response = [
            'success' => false,
            'code' => 'delivery_failed',
            'message' => 'ပစ္စည်းပို့ဆောင်မှုကို သိမ်း၍မရပါ။',
        ];

        if (!$this->ensureSalesDeliveryTables()) {
            $response['code'] = 'delivery_log_unavailable';
            $response['message'] =
                'Delivery log သိမ်း၍မရသောကြောင့် ပစ္စည်းမပို့ပါ။';
            return $response;
        }

        $sale = $this->getSaleByID($sale_id);

        if (!$sale) {
            $response['code'] = 'sale_not_found';
            $response['message'] = 'အရောင်းဘောင်ချာကို ရှာမတွေ့ပါ။';
            return $response;
        }

        $before = $this->getSaleDeliveryStatus($sale_id);
        $this->db->trans_begin();

        try {
            $sales_table = $this->db->dbprefix('sales');
            $items_table = $this->db->dbprefix('sale_items');
            $locked_sale = $this->db->query(
                "SELECT * FROM `{$sales_table}` WHERE `id` = ? FOR UPDATE",
                [$sale_id]
            )->row();
            $locked_items = $this->db->query(
                "SELECT * FROM `{$items_table}` WHERE `sale_id` = ? FOR UPDATE",
                [$sale_id]
            )->result();

            if (!$locked_sale || empty($locked_items)) {
                throw new Exception('Sale/items disappeared before delivery.');
            }

            $item_map = [];

            foreach ($locked_items as $locked_item) {
                $item_map[(int) $locked_item->id] = $locked_item;
            }

            $created_deliveries = [];

            foreach ($delivery_map as $sale_item_id => $quantity) {
                $sale_item_id = (int) $sale_item_id;
                $quantity = (float) $quantity;

                if ($quantity <= 0) {
                    continue;
                }

                if (!isset($item_map[$sale_item_id])) {
                    throw new Exception('Invalid sale item in delivery request.');
                }

                $item = $item_map[$sale_item_id];
                $qty = $this->getEffectiveSaleItemDelivery($item);

                if ($quantity > $qty['remaining'] + 0.000001) {
                    throw new Exception(
                        'Delivery quantity exceeds the remaining quantity.'
                    );
                }

                if (!$this->db->insert('sale_delivery_logs', [
                    'sale_id' => $sale_id,
                    'sale_item_id' => $sale_item_id,
                    'product_id' => (int) $item->product_id,
                    'quantity' => $quantity,
                    'delivered_at' => $delivered_at,
                    'note' => $note,
                    'store_id' => (int) $locked_sale->store_id,
                    'stock_deducted' => 0,
                    'created_by' => (int) ($context['user_id'] ?? 0),
                    'created_at' => date('Y-m-d H:i:s'),
                ])) {
                    throw new Exception('Delivery history insert failed.');
                }

                $delivery_id = (int) $this->db->insert_id();
                $stock = $this->deductSaleDeliveryStock(
                    $locked_sale,
                    $item,
                    $quantity,
                    $delivery_id
                );

                if (!$this->db->update(
                    'sale_delivery_logs',
                    ['stock_deducted' => !empty($stock['stock_deducted']) ? 1 : 0],
                    ['id' => $delivery_id]
                )) {
                    throw new Exception('Delivery stock flag update failed.');
                }

                $new_delivered = $qty['delivered'] + $quantity;
                $item_update = ['fulfilled_qty' => $new_delivered];

                if ((float) ($item->preorder_qty ?? 0) > 0) {
                    $item_update['quantity'] = $new_delivered;
                }

                if (!$this->db->update(
                    'sale_items',
                    $item_update,
                    ['id' => $sale_item_id, 'sale_id' => $sale_id]
                )) {
                    throw new Exception('Sale item delivery quantity update failed.');
                }

                $item->fulfilled_qty = $new_delivered;

                if (isset($item_update['quantity'])) {
                    $item->quantity = $new_delivered;
                }

                $created_deliveries[] = [
                    'delivery_id' => $delivery_id,
                    'sale_item_id' => $sale_item_id,
                    'quantity' => $quantity,
                    'stock' => $stock,
                ];
            }

            if (empty($created_deliveries)) {
                throw new Exception('No positive delivery quantity was provided.');
            }

            if (!$this->syncSaleDeliveryFlag($sale_id)) {
                throw new Exception('Sale delivery status sync failed.');
            }

            $after = $this->getSaleDeliveryStatus($sale_id);
            $audit_saved = $this->writeSalesAuditLog(
                $sale,
                'success',
                $reason,
                $context,
                [
                    'delivery_status' => $before,
                    'requested_quantities' => $delivery_map,
                ],
                [
                    'delivery_status' => $after,
                    'created_deliveries' => $created_deliveries,
                ]
            );

            if (!$audit_saved || $this->db->trans_status() === false) {
                throw new Exception('Delivery transaction/audit failed.');
            }

            $this->db->trans_commit();

            log_message(
                'info',
                '[SALE DELIVERY SUCCESS] Sale ID: ' . $sale_id .
                ' | Deliveries: ' . count($created_deliveries) .
                ' | User ID: ' . (int) ($context['user_id'] ?? 0) .
                ' | Store ID: ' . (int) $sale->store_id
            );

            return [
                'success' => true,
                'code' => 'delivery_saved',
                'message' => 'ပစ္စည်းပို့ဆောင်မှု အောင်မြင်စွာ သိမ်းပြီးပါပြီ။',
                'delivery_status' => $after,
            ];
        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->writeSalesAuditLog(
                $sale,
                'failed',
                $reason,
                $context,
                ['delivery_status' => $before],
                ['error' => $e->getMessage()]
            );

            log_message(
                'error',
                '[SALE DELIVERY FAILED] Sale ID: ' . $sale_id .
                ' | User ID: ' . (int) ($context['user_id'] ?? 0) .
                ' | Error: ' . $e->getMessage()
            );

            if (
                $e->getMessage() ===
                'Delivery quantity exceeds the remaining quantity.'
            ) {
                $response['message'] =
                    'ပို့မည့်အရေအတွက်သည် ပို့ရန်ကျန်အရေအတွက်ထက် မကျော်ရပါ။';
            } elseif (
                $e->getMessage() === 'Insufficient stock for delivery.'
            ) {
                $response['code'] = 'insufficient_stock';
                $response['message'] =
                    'ပို့ဆောင်ရန် Stock အရေအတွက် မလုံလောက်ပါ။';
            } elseif (
                $e->getMessage() ===
                'Delivery stock was not linked to FIFO COGS.'
            ) {
                $response['code'] = 'fifo_mapping_failed';
                $response['message'] =
                    'FIFO Stock မှတ်တမ်း မပြည့်စုံသဖြင့် ပို့ဆောင်မှုကို မသိမ်းပါ။';
            }

            return $response;
        }
    }

    public function updateSaleDelivery(
        $delivery_id,
        $new_quantity,
        array $context = []
    ) {
        $delivery_id = (int) $delivery_id;
        $new_quantity = (float) $new_quantity;
        $context['action'] = 'update_sale_delivery';
        $context['entity_type'] = 'sale_delivery';
        $context['entity_id'] = $delivery_id;
        $reason = trim((string) ($context['reason'] ??
            'Delivery history quantity updated'));

        $response = [
            'success' => false,
            'code' => 'delivery_update_failed',
            'message' => 'ပို့ဆောင်မှုမှတ်တမ်းကို ပြင်၍မရပါ။',
        ];

        if (
            !$this->ensureSalesDeliveryTables() ||
            $delivery_id <= 0 ||
            $new_quantity <= 0
        ) {
            return $response;
        }

        $delivery = $this->getSaleDeliveryByID($delivery_id);
        $sale = $delivery
            ? $this->getSaleByID((int) $delivery->sale_id)
            : false;

        if (!$delivery || !$sale) {
            $response['code'] = 'delivery_not_found';
            $response['message'] = 'ပို့ဆောင်မှုမှတ်တမ်းကို ရှာမတွေ့ပါ။';
            return $response;
        }

        $this->db->trans_begin();

        try {
            $delivery_table = $this->db->dbprefix('sale_delivery_logs');
            $sales_table = $this->db->dbprefix('sales');
            $items_table = $this->db->dbprefix('sale_items');
            $locked_delivery = $this->db->query(
                "SELECT * FROM `{$delivery_table}` WHERE `id` = ? FOR UPDATE",
                [$delivery_id]
            )->row();
            $locked_sale = $this->db->query(
                "SELECT * FROM `{$sales_table}` WHERE `id` = ? FOR UPDATE",
                [(int) $delivery->sale_id]
            )->row();
            $item = $this->db->query(
                "SELECT * FROM `{$items_table}` WHERE `id` = ? AND `sale_id` = ? FOR UPDATE",
                [(int) $delivery->sale_item_id, (int) $delivery->sale_id]
            )->row();

            if (!$locked_delivery || !$locked_sale || !$item) {
                throw new Exception('Delivery relationship changed before update.');
            }

            $qty = $this->getEffectiveSaleItemDelivery($item);
            $old_quantity = (float) $locked_delivery->quantity;
            $delivered_without_this = max(0, $qty['delivered'] - $old_quantity);
            $new_total_delivered = $delivered_without_this + $new_quantity;

            if ($new_total_delivered > $qty['ordered'] + 0.000001) {
                throw new Exception('Updated delivery exceeds ordered quantity.');
            }

            $before = [
                'delivery' => $locked_delivery,
                'item_delivery' => $qty,
            ];
            $restore = $this->reverseSaleDeliveryStock(
                $locked_delivery,
                $locked_sale
            );
            $stock = $this->deductSaleDeliveryStock(
                $locked_sale,
                $item,
                $new_quantity,
                $delivery_id
            );
            $item_update = ['fulfilled_qty' => $new_total_delivered];

            if ((float) ($item->preorder_qty ?? 0) > 0) {
                $item_update['quantity'] = $new_total_delivered;
            }

            if (
                !$this->db->update(
                    'sale_items',
                    $item_update,
                    ['id' => (int) $item->id]
                ) ||
                !$this->db->update(
                    'sale_delivery_logs',
                    [
                        'quantity' => $new_quantity,
                        'stock_deducted' =>
                            !empty($stock['stock_deducted']) ? 1 : 0,
                        'updated_by' => (int) ($context['user_id'] ?? 0),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    ['id' => $delivery_id]
                ) ||
                !$this->syncSaleDeliveryFlag((int) $sale->id)
            ) {
                throw new Exception('Delivery update database write failed.');
            }

            $after_status = $this->getSaleDeliveryStatus((int) $sale->id);
            $audit_saved = $this->writeSalesAuditLog(
                $sale,
                'success',
                $reason,
                $context,
                $before,
                [
                    'new_quantity' => $new_quantity,
                    'stock_restoration' => $restore,
                    'new_stock_deduction' => $stock,
                    'delivery_status' => $after_status,
                ]
            );

            if (!$audit_saved || $this->db->trans_status() === false) {
                throw new Exception('Delivery update transaction/audit failed.');
            }

            $this->db->trans_commit();

            log_message(
                'info',
                '[SALE DELIVERY UPDATE SUCCESS] Delivery ID: ' . $delivery_id .
                ' | Sale ID: ' . (int) $sale->id .
                ' | Old Qty: ' . $old_quantity .
                ' | New Qty: ' . $new_quantity .
                ' | User ID: ' . (int) ($context['user_id'] ?? 0)
            );

            return [
                'success' => true,
                'code' => 'delivery_updated',
                'message' => 'ပို့ဆောင်မှုမှတ်တမ်း ပြင်ပြီးပါပြီ။',
                'delivery_status' => $after_status,
            ];
        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->writeSalesAuditLog(
                $sale,
                'failed',
                $reason,
                $context,
                ['delivery' => $delivery],
                ['error' => $e->getMessage()]
            );

            log_message(
                'error',
                '[SALE DELIVERY UPDATE FAILED] Delivery ID: ' . $delivery_id .
                ' | Error: ' . $e->getMessage()
            );

            if ($e->getMessage() === 'Updated delivery exceeds ordered quantity.') {
                $response['message'] =
                    'ပြင်ပြီးနောက် ပို့ပြီးစုစုပေါင်းသည် ရောင်းထားသောအရေအတွက်ထက် မကျော်ရပါ။';
            } elseif (
                $e->getMessage() === 'Insufficient stock for delivery.'
            ) {
                $response['code'] = 'insufficient_stock';
                $response['message'] =
                    'ပြင်ထားသောအရေအတွက်အတွက် Stock မလုံလောက်ပါ။';
            }

            return $response;
        }
    }

    public function deleteSaleDelivery($delivery_id, array $context = [])
    {
        $delivery_id = (int) $delivery_id;
        $context['action'] = 'delete_sale_delivery';
        $context['entity_type'] = 'sale_delivery';
        $context['entity_id'] = $delivery_id;
        $reason = trim((string) ($context['reason'] ??
            'Delivery history deleted'));

        $response = [
            'success' => false,
            'code' => 'delivery_delete_failed',
            'message' => 'ပို့ဆောင်မှုမှတ်တမ်းကို ဖျက်၍မရပါ။',
        ];

        if (!$this->ensureSalesDeliveryTables() || $delivery_id <= 0) {
            return $response;
        }

        $delivery = $this->getSaleDeliveryByID($delivery_id);
        $sale = $delivery
            ? $this->getSaleByID((int) $delivery->sale_id)
            : false;

        if (!$delivery || !$sale) {
            $response['code'] = 'delivery_not_found';
            $response['message'] = 'ပို့ဆောင်မှုမှတ်တမ်းကို ရှာမတွေ့ပါ။';
            return $response;
        }

        $this->db->trans_begin();

        try {
            $delivery_table = $this->db->dbprefix('sale_delivery_logs');
            $sales_table = $this->db->dbprefix('sales');
            $items_table = $this->db->dbprefix('sale_items');
            $locked_delivery = $this->db->query(
                "SELECT * FROM `{$delivery_table}` WHERE `id` = ? FOR UPDATE",
                [$delivery_id]
            )->row();
            $locked_sale = $this->db->query(
                "SELECT * FROM `{$sales_table}` WHERE `id` = ? FOR UPDATE",
                [(int) $delivery->sale_id]
            )->row();
            $item = $this->db->query(
                "SELECT * FROM `{$items_table}` WHERE `id` = ? AND `sale_id` = ? FOR UPDATE",
                [(int) $delivery->sale_item_id, (int) $delivery->sale_id]
            )->row();

            if (!$locked_delivery || !$locked_sale || !$item) {
                throw new Exception('Delivery relationship changed before delete.');
            }

            $qty = $this->getEffectiveSaleItemDelivery($item);
            $new_total_delivered = max(
                0,
                $qty['delivered'] - (float) $locked_delivery->quantity
            );
            $restore = $this->reverseSaleDeliveryStock(
                $locked_delivery,
                $locked_sale
            );
            $item_update = ['fulfilled_qty' => $new_total_delivered];

            if ((float) ($item->preorder_qty ?? 0) > 0) {
                $item_update['quantity'] = $new_total_delivered;
            }

            if (
                !$this->db->update(
                    'sale_items',
                    $item_update,
                    ['id' => (int) $item->id]
                ) ||
                !$this->db->delete(
                    'sale_delivery_logs',
                    ['id' => $delivery_id]
                ) ||
                !$this->syncSaleDeliveryFlag((int) $sale->id)
            ) {
                throw new Exception('Delivery delete database write failed.');
            }

            $after_status = $this->getSaleDeliveryStatus((int) $sale->id);
            $audit_saved = $this->writeSalesAuditLog(
                $sale,
                'success',
                $reason,
                $context,
                [
                    'delivery' => $locked_delivery,
                    'item_delivery' => $qty,
                ],
                [
                    'delivery_deleted' => true,
                    'stock_restoration' => $restore,
                    'delivery_status' => $after_status,
                ]
            );

            if (!$audit_saved || $this->db->trans_status() === false) {
                throw new Exception('Delivery delete transaction/audit failed.');
            }

            $this->db->trans_commit();

            log_message(
                'info',
                '[SALE DELIVERY DELETE SUCCESS] Delivery ID: ' . $delivery_id .
                ' | Sale ID: ' . (int) $sale->id .
                ' | Qty: ' . (float) $delivery->quantity .
                ' | User ID: ' . (int) ($context['user_id'] ?? 0)
            );

            return [
                'success' => true,
                'code' => 'delivery_deleted',
                'message' => 'ပို့ဆောင်မှုမှတ်တမ်း ဖျက်ပြီးပါပြီ။',
                'delivery_status' => $after_status,
            ];
        } catch (Exception $e) {
            $this->db->trans_rollback();

            $this->writeSalesAuditLog(
                $sale,
                'failed',
                $reason,
                $context,
                ['delivery' => $delivery],
                ['error' => $e->getMessage()]
            );

            log_message(
                'error',
                '[SALE DELIVERY DELETE FAILED] Delivery ID: ' . $delivery_id .
                ' | Error: ' . $e->getMessage()
            );

            return $response;
        }
    }

    public function getAllSaleItems($sale_id)
    {
        $j = "(SELECT id, code, name, tax_method from {$this->db->dbprefix('products')}) P";
        $this->db->select("sale_items.*,
            (CASE WHEN {$this->db->dbprefix('sale_items')}.product_code IS NULL THEN {$this->db->dbprefix('products')}.code ELSE {$this->db->dbprefix('sale_items')}.product_code END) as product_code,
            (CASE WHEN {$this->db->dbprefix('sale_items')}.product_name IS NULL THEN {$this->db->dbprefix('products')}.name ELSE {$this->db->dbprefix('sale_items')}.product_name END) as product_name,
            {$this->db->dbprefix('products')}.tax_method as tax_method", false)
        ->join('products', 'products.id=sale_items.product_id', 'left outer')
        ->order_by('sale_items.id');
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSalePayments($sale_id)
    {
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getComboItemsByPID($product_id)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.quantity as quantity')
        ->join('products', 'products.code=combo_items.item_code', 'left')
        ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCustomerByID($id)
    {
        $q = $this->db->get_where('customers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

   

    public function getSalePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function syncSalePayments($id)
    {
        $sale     = $this->getSaleByID($id);
        $payments = $this->getSalePayments($id);
        $paid     = 0;
        if ($payments) {
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
        }
        $status = $paid <= 0 ? 'due' : ($sale->grand_total <= $paid ? 'paid' : 'partial');
        if ($this->db->update('sales', ['paid' => $paid, 'status' => $status], ['id' => $id])) {
            return true;
        }

        return false;
    }

    public function updatePayment($id, $data = [])
    {
        $payment = $this->getPaymentByID($id);
        if ($payment->paid_by == 'gift_card') {
            $gc = $this->site->getGiftCard($payment->gc_no);
            $this->db->update('gift_cards', ['balance' => ($gc->balance + $payment->amount)], ['card_no' => $payment->gc_no]);
        }
        if ($this->db->update('payments', $data, ['id' => $id])) {
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCard($data['gc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['gc_no']]);
            }
            $this->syncSalePayments($data['sale_id']);
            return true;
        }
        return false;
    }

    public function updateStatus($id, $status)
    {
        if ($this->db->update('sales', ['status' => $status], ['id' => $id])) {
            return true;
        }
        return false;
    }




 public function getSaleByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

public function getProductStock($product_id)
{
    $row = $this->db->select('quantity')->get_where('products', ['id' => $product_id])->row();
    return $row ? (int)$row->quantity : 0;
}

public function markPreorderDelivered($sale_id)
{
    $this->db->where('id', $sale_id)->update('sales', ['is_preorder' => 0]);
}

public function reduceStock($product_id, $qty)
{
    $this->db->set('quantity', 'quantity-'.$qty, FALSE)
             ->where('id', $product_id)
             ->update('products');
}

public function get_unit_ratio($product_id, $unit_id) {
    $row = $this->db->get_where('tec_product_unit_conversions', [
        'product_id' => $product_id,
        'unit_id'    => $unit_id
    ])->row();

    if (!$row) return 1;  // fallback

    if ($row->operator == '*' || $row->operator == 'x') {
        return $row->operation_value;
    }

    // You can extend here if you add / or + later
    return 1;
}

public function get_other_unit_ratio($product_id, $current_unit_id)
{
    // Find the conversion row that is NOT the current unit_id
    $other = $this->db
        ->where('product_id', $product_id)
        ->where('unit_id !=', $current_unit_id)
        ->get('tec_product_unit_conversions')
        ->row();

    if ($other) {
        return $other->operation_value;
    }

    return 0; // fallback
}

}
