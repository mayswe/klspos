<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>
<style>
.receive-history-modal{width:700px;max-width:calc(100% - 30px);margin:30px auto}.receive-history-modal .modal-content{overflow:hidden;border:0;border-radius:14px;background:#fff;box-shadow:0 20px 55px rgba(15,23,42,.25)}.receive-history-header{display:flex;align-items:center;justify-content:space-between;min-height:68px;padding:14px 18px;border-bottom:1px solid #e2e8f0}.receive-history-title{margin:0;color:#1e293b;font-size:18px;font-weight:700}.receive-history-product{margin-top:3px;color:#64748b;font-size:12px}.receive-history-close{display:flex;align-items:center;justify-content:center;width:34px;height:34px;padding:0;border:1px solid #dbe3ec;border-radius:8px;background:#f8fafc;color:#64748b;cursor:pointer}.receive-history-body{padding:18px;background:#f8fafc}.receive-history-table-wrap{overflow:hidden;border:1px solid #e2e8f0;border-radius:10px;background:#fff}.receive-history-table{width:100%;border-collapse:separate;border-spacing:0}.receive-history-table th,.receive-history-table td{padding:11px 12px;border-bottom:1px solid #edf2f7;color:#334155;font-size:13px}.receive-history-table th{background:#f1f5f9;font-size:12px;font-weight:700;text-align:left}.receive-history-empty{padding:35px 20px!important;text-align:center!important;color:#64748b!important}.receive-history-empty i,.receive-history-empty span{display:block}.history-actions{display:inline-flex;gap:5px}.history-action{width:31px;height:31px;border:1px solid transparent;border-radius:6px;cursor:pointer}.history-edit{color:#2563eb;background:#eff6ff}.history-delete{color:#dc2626;background:#fff5f5}@media(max-width:575px){.receive-history-modal{width:calc(100% - 20px);margin:10px auto}.receive-history-body{padding:12px}.receive-history-table th,.receive-history-table td{padding:9px 7px;font-size:12px}}
</style>
<div class="modal-dialog receive-history-modal">

    <div class="modal-content">

        <div class="receive-history-header">

            <div>
                <h4 class="receive-history-title">
                    လက်ခံမှတ်တမ်း
                </h4>

                <div class="receive-history-product">
                    <?= html_escape($product_name ?? '-'); ?>
                </div>
            </div>

            <button
                type="button"
                class="receive-history-close"
                data-dismiss="modal"
            >
                <i class="fa fa-times"></i>
            </button>

        </div>


        <div class="receive-history-body">

            <div class="receive-history-table-wrap">

                <table class="receive-history-table">

                    <thead>
                        <tr>

                            <th>
                                ရက်စွဲ
                            </th>

                            <th class="text-right">
                                လက်ခံအရေအတွက်
                            </th>

                            <th class="text-center">
                                Action
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($history)): ?>

                            <?php foreach ($history as $row): ?>

                                <tr>

                                    <td>
                                        <?= html_escape(
                                            $row->date
                                                ?? $row->received_at
                                                ?? '-'
                                        ); ?>
                                    </td>

                                    <td class="text-right">

                                        <?= $this->tec->formatDecimal(
                                            $row->quantity ?? 0
                                        ); ?>

                                    </td>

                                    <td class="text-center">

                                        <div class="history-actions">

                                            <button
                                                type="button"
                                                class="history-action history-edit js-edit-receive-history"
                                                data-id="<?= (int) $row->id; ?>"
                                                data-quantity="<?= html_escape((string) ($row->quantity ?? 0)); ?>"
                                            >
                                                <i class="fa fa-pencil"></i>
                                            </button>

                                            <button
                                                type="button"
                                                class="history-action history-delete js-delete-receive-history"
                                                data-id="<?= (int) $row->id; ?>"
                                            >
                                                <i class="fa fa-trash"></i>
                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="3"
                                    class="receive-history-empty"
                                >
                                    <i class="fa fa-history"></i>

                                    <span>
                                        လက်ခံမှတ်တမ်း မရှိပါ။
                                    </span>
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>
