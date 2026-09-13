<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>
<?php
$total_amount   = !empty($inv) ? (float) $inv->total : 0;
$paid_amount    = !empty($inv) ? (float) $inv->paid : 0;
$balance_amount = max(0, $total_amount - $paid_amount);
?>

<style>
/* =========================================================
   KLSPOS ERP - View Payments Modal
   ========================================================= */

.payment-view-modal {
    width: 920px;
    max-width: calc(100% - 30px);
    margin: 25px auto;
}

.payment-view-modal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 20px 55px rgba(15, 23, 42, 0.25);
}

/* =========================================================
   Modal Header
   ========================================================= */

.payment-view-modal .payment-modal-header {
    position: relative;
    min-height: 64px;
    padding: 17px 118px 17px 22px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.payment-view-modal .modal-title {
    margin: 0;
    color: #1e293b;
    font-size: 18px;
    font-weight: 700;
    line-height: 30px;
}

.payment-view-modal .payment-modal-actions {
    position: absolute;
    top: 15px;
    right: 16px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.payment-view-modal .payment-modal-action {
    width: 34px;
    height: 34px;
    padding: 0;
    border: 1px solid #dbe3ec;
    border-radius: 8px;
    background: #f8fafc;
    color: #64748b;
    font-size: 15px;
    line-height: 32px;
    text-align: center;
    opacity: 1;
    transition: all 0.18s ease;
}

.payment-view-modal .payment-modal-action:hover,
.payment-view-modal .payment-modal-action:focus {
    border-color: #94a3b8;
    background: #f1f5f9;
    color: #1e293b;
    outline: none;
}

.payment-view-modal .payment-modal-close:hover,
.payment-view-modal .payment-modal-close:focus {
    border-color: #fecaca;
    background: #fef2f2;
    color: #dc2626;
}

/* =========================================================
   Modal Body
   ========================================================= */

.payment-view-modal .modal-body {
    padding: 20px 22px 24px;
    background: #f8fafc;
}

/* =========================================================
   Payment Summary
   ========================================================= */

.payment-view-modal .payment-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 12px;
}

.payment-view-modal .payment-summary-item {
    padding: 14px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
}

.payment-view-modal .payment-summary-label {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.6;
}

.payment-view-modal .payment-summary-value {
    display: block;
    color: #1e293b;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.5;
    white-space: nowrap;
}

.payment-view-modal .payment-summary-item.is-paid {
    border-color: #bbf7d0;
    background: #f0fdf4;
}

.payment-view-modal .payment-summary-item.is-paid .payment-summary-value {
    color: #15803d;
}

.payment-view-modal .payment-summary-item.is-balance {
    border-color: #fecaca;
    background: #fef2f2;
}

.payment-view-modal .payment-summary-item.is-balance .payment-summary-value {
    color: #dc2626;
}

.payment-view-modal .payment-add-wrap {
    margin-bottom: 15px;
}

.payment-view-modal .payment-add-button {
    display: flex;
    width: 100%;
    min-height: 42px;
    padding: 9px 14px;
    border: 1px solid #16a34a;
    border-radius: 9px;
    background: #16a34a;
    color: #ffffff !important;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
    align-items: center;
    justify-content: center;
    gap: 7px;
}

.payment-view-modal .payment-add-button:hover,
.payment-view-modal .payment-add-button:focus {
    border-color: #15803d;
    background: #15803d;
    color: #ffffff !important;
    text-decoration: none !important;
    outline: none;
}

/* =========================================================
   Payment Table
   ========================================================= */

.payment-view-modal .payment-table-card {
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    background: #ffffff;
}

.payment-view-modal .payment-table-wrap {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.payment-view-modal .payment-table {
    width: 100%;
    min-width: 850px;
    margin: 0;
    border: 0;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: auto;
}

.payment-view-modal .payment-table thead th {
    padding: 12px 11px;
    border-top: 0 !important;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #dbe3ec;
    border-left: 0 !important;
    background: #f1f5f9;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.7;
    vertical-align: middle;
    white-space: nowrap;
}

.payment-view-modal .payment-table thead th:last-child {
    border-right: 0;
}

.payment-view-modal .payment-table tbody td {
    padding: 12px 11px;
    border-top: 0 !important;
    border-right: 1px solid #edf2f7;
    border-bottom: 1px solid #edf2f7;
    border-left: 0 !important;
    background: #ffffff;
    color: #334155;
    font-size: 14px;
    line-height: 1.7;
    vertical-align: middle;
}

.payment-view-modal .payment-table tbody td:last-child {
    border-right: 0;
}

.payment-view-modal .payment-table tbody tr:last-child td {
    border-bottom: 0;
}

.payment-view-modal .payment-table tbody tr:hover td {
    background: #f8fafc;
}

/* =========================================================
   Payment Cells
   ========================================================= */

.payment-view-modal .payment-date-cell {
    min-width: 175px;
    color: #334155;
    font-weight: 600;
}

.payment-view-modal .payment-reference-cell {
    min-width: 145px;
    color: #1e293b;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.payment-view-modal .payment-amount-cell {
    min-width: 135px;
    color: #1d4ed8;
    font-weight: 800;
    white-space: nowrap;
}

.payment-view-modal .payment-method-cell {
    min-width: 115px;
    color: #475569;
    font-weight: 600;
}

.payment-view-modal .payment-action-cell {
    width: 205px;
    min-width: 205px;
    text-align: center;
}

/* =========================================================
   Attachment
   ========================================================= */

.payment-view-modal .payment-amount-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.payment-view-modal .payment-attachment {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    background: #eff6ff;
    color: #2563eb;
    text-decoration: none;
    vertical-align: middle;
    transition: all 0.18s ease;
}

.payment-view-modal .payment-attachment:hover,
.payment-view-modal .payment-attachment:focus {
    border-color: #93c5fd;
    background: #dbeafe;
    color: #1d4ed8;
    text-decoration: none;
}

/* =========================================================
   Edit / Delete Buttons
   ========================================================= */

.payment-view-modal .payment-row-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    width: 100%;
}

.payment-view-modal .payment-row-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-width: 84px;
    min-height: 34px;
    padding: 7px 11px;
    border: 1px solid transparent;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.5;
    text-decoration: none !important;
    white-space: nowrap;
    transition: all 0.18s ease;
}

.payment-view-modal .payment-row-button i {
    font-size: 13px;
}

/* Edit Button */

.payment-view-modal .payment-edit-button {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #1d4ed8;
}

.payment-view-modal .payment-edit-button:hover,
.payment-view-modal .payment-edit-button:focus {
    border-color: #93c5fd;
    background: #dbeafe;
    color: #1e40af;
    text-decoration: none;
    outline: none;
}

/* Delete Button */

.payment-view-modal .payment-delete-button {
    border-color: #fecaca;
    background: #fef2f2;
    color: #dc2626;
}

.payment-view-modal .payment-delete-button:hover,
.payment-view-modal .payment-delete-button:focus {
    border-color: #fca5a5;
    background: #fee2e2;
    color: #b91c1c;
    text-decoration: none;
    outline: none;
}

/* =========================================================
   Empty State
   ========================================================= */

.payment-view-modal .payment-empty-cell {
    padding: 35px 20px !important;
    color: #64748b !important;
    text-align: center !important;
}

.payment-view-modal .payment-empty-icon {
    display: block;
    margin-bottom: 9px;
    color: #94a3b8;
    font-size: 30px;
}

/* =========================================================
   Tablet / Mobile Card Mode
   ========================================================= */

@media (max-width: 991px) {

    .payment-view-modal {
        width: calc(100% - 30px);
        max-width: 760px;
        height: auto;
        max-height: calc(100vh - 30px);
        max-height: calc(100dvh - 30px);
        margin: 15px auto;
    }

    .payment-view-modal .modal-content {
        display: flex;
        flex-direction: column;
        height: auto;
        min-height: 0;
        max-height: calc(100vh - 30px);
        max-height: calc(100dvh - 30px);
        border-radius: 13px;
    }

    .payment-view-modal .payment-modal-header {
        flex: 0 0 auto;
        min-height: 58px;
        padding: 13px 105px 13px 16px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        z-index: 20;
    }

    .payment-view-modal .modal-title {
        font-size: 16px;
        line-height: 32px;
    }

    .payment-view-modal .payment-modal-actions {
        top: 12px;
        right: 12px;
    }

    .payment-view-modal .payment-modal-action {
        width: 34px;
        height: 34px;
        line-height: 32px;
    }

    .payment-view-modal .modal-body {
        flex: 0 1 auto;
        min-height: 0;
        max-height: calc(100vh - 95px);
        max-height: calc(100dvh - 95px);
        padding: 14px;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .payment-view-modal .payment-summary-grid {
        gap: 10px;
        margin-bottom: 10px;
    }

    /* Table to cards */

    .payment-view-modal .payment-table-card {
        overflow: visible;
        border: 0;
        background: transparent;
    }

    .payment-view-modal .payment-table-wrap {
        overflow: visible;
    }

    .payment-view-modal .payment-table,
    .payment-view-modal .payment-table tbody {
        display: block;
        width: 100%;
        min-width: 0;
    }

    .payment-view-modal .payment-table thead {
        display: none;
    }

    .payment-view-modal .payment-table tbody tr {
        display: block;
        width: 100%;
        margin-bottom: 12px;
        padding: 13px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #ffffff;
        box-shadow: 0 2px 7px rgba(15, 23, 42, 0.05);
    }

    .payment-view-modal .payment-table tbody tr:last-child {
        margin-bottom: 0;
    }

    .payment-view-modal .payment-table tbody tr:hover td {
        background: transparent;
    }

    .payment-view-modal .payment-table tbody td {
        display: flex;
        width: 100%;
        min-width: 0;
        min-height: 40px;
        padding: 8px 0;
        border-top: 0 !important;
        border-right: 0 !important;
        border-bottom: 1px dashed #e2e8f0;
        border-left: 0 !important;
        background: transparent;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        line-height: 1.9;
        text-align: right !important;
        overflow-wrap: anywhere;
    }

    .payment-view-modal .payment-table tbody td::before {
        content: attr(data-label);
        flex: 0 0 36%;
        max-width: 36%;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.9;
        text-align: left;
        overflow-wrap: anywhere;
    }

    .payment-view-modal .payment-table tbody td:last-child {
        border-bottom: 0;
    }

    .payment-view-modal .payment-date-cell {
        color: #1e293b;
        font-weight: 700;
    }

    .payment-view-modal .payment-reference-cell {
        color: #334155;
        font-weight: 600;
    }

    .payment-view-modal .payment-amount-cell {
        color: #1d4ed8;
        font-size: 16px;
        font-weight: 800;
    }

    .payment-view-modal .payment-method-cell {
        color: #475569;
        font-weight: 600;
    }

    /* Action buttons in card */

    .payment-view-modal .payment-action-cell {
        width: 100%;
        min-width: 0;
        padding-top: 12px !important;
    }

    .payment-view-modal .payment-row-actions {
        width: auto;
        justify-content: flex-end;
    }

    .payment-view-modal .payment-row-button {
        min-width: 95px;
        min-height: 36px;
        padding: 8px 13px;
    }

    /* Empty card */

    .payment-view-modal .payment-empty-row {
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .payment-view-modal .payment-empty-cell {
        display: block !important;
        padding: 35px 20px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 11px !important;
        background: #ffffff !important;
        text-align: center !important;
    }

    .payment-view-modal .payment-empty-cell::before {
        display: none;
    }
}

/* =========================================================
   Small Mobile
   ========================================================= */

@media (max-width: 575px) {

    .payment-view-modal {
        width: 100%;
        max-width: 100%;
        max-height: 100vh;
        max-height: 100dvh;
        margin: 0;
    }

    .payment-view-modal .modal-content {
        max-height: 100vh;
        max-height: 100dvh;
        border-radius: 0;
    }

    .payment-view-modal .payment-modal-header {
        padding-left: 13px;
    }

    .payment-view-modal .modal-title {
        font-size: 15px;
        line-height: 1.9;
    }

    .payment-view-modal .modal-body {
        max-height: calc(100vh - 58px);
        max-height: calc(100dvh - 58px);
        padding: 10px;
    }

    .payment-view-modal .payment-summary-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .payment-view-modal .payment-summary-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
    }

    .payment-view-modal .payment-summary-label {
        margin-bottom: 0;
        font-size: 12px;
    }

    .payment-view-modal .payment-summary-value {
        font-size: 15px;
        text-align: right;
    }

    .payment-view-modal .payment-table tbody tr {
        padding: 12px;
    }

    .payment-view-modal .payment-table tbody td {
        gap: 12px;
        padding: 8px 0;
    }

    .payment-view-modal .payment-table tbody td::before {
        flex-basis: 42%;
        max-width: 42%;
        padding-right: 5px;
    }

    .payment-view-modal .payment-date-cell {
        font-size: 13px;
    }

    .payment-view-modal .payment-amount-cell {
        font-size: 15px;
    }

    .payment-view-modal .payment-action-cell {
        display: block !important;
        padding-top: 13px !important;
        text-align: left !important;
    }

    .payment-view-modal .payment-action-cell::before {
        display: block;
        max-width: 100%;
        margin-bottom: 9px;
        text-align: left;
    }

    .payment-view-modal .payment-row-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        width: 100%;
    }

    .payment-view-modal .payment-row-button {
        width: 100%;
        min-width: 0;
        min-height: 38px;
        padding: 8px 9px;
    }
}

/* =========================================================
   Myanmar Font
   ========================================================= */

.payment-view-modal,
.payment-view-modal table,
.payment-view-modal button,
.payment-view-modal a {
    font-family:
        "Pyidaungsu",
        "Noto Sans Myanmar",
        "Myanmar Text",
        Arial,
        sans-serif;
}

/* =========================================================
   Print
   ========================================================= */

@media print {

    body * {
        visibility: hidden !important;
    }

    .payment-view-modal,
    .payment-view-modal * {
        visibility: visible !important;
    }

    .payment-view-modal {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: none !important;
        margin: 0 !important;
    }

    .payment-view-modal .modal-content {
        height: auto !important;
        max-height: none !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .payment-view-modal .payment-modal-actions,
    .payment-view-modal .payment-action-cell,
    .payment-view-modal .payment-table thead th:last-child {
        display: none !important;
    }

    .payment-view-modal .modal-body {
        height: auto !important;
        max-height: none !important;
        padding: 15px !important;
        overflow: visible !important;
        background: #ffffff !important;
    }

    .payment-view-modal .payment-table-card {
        border: 1px solid #dddddd !important;
    }

    .payment-view-modal .payment-table {
        display: table !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    .payment-view-modal .payment-table thead {
        display: table-header-group !important;
    }

    .payment-view-modal .payment-table tbody {
        display: table-row-group !important;
    }

    .payment-view-modal .payment-table tbody tr {
        display: table-row !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .payment-view-modal .payment-table tbody td {
        display: table-cell !important;
        width: auto !important;
        min-height: 0 !important;
        padding: 8px !important;
        border-right: 1px solid #dddddd !important;
        border-bottom: 1px solid #dddddd !important;
        text-align: inherit !important;
    }

    .payment-view-modal .payment-table tbody td::before {
        display: none !important;
    }

    .payment-view-modal .payment-attachment {
        border: 0 !important;
    }

    .modal-backdrop {
        display: none !important;
    }
}
</style>

<style>
/* =========================================================
   Compact Payment Rows - Date | Amount | Edit | Delete
   ========================================================= */
.payment-view-modal .payment-table {
    min-width: 0 !important;
    width: 100% !important;
    table-layout: fixed !important;
}

.payment-view-modal .payment-col-date {
    width: 39%;
}

.payment-view-modal .payment-col-amount {
    width: 25%;
}

.payment-view-modal .payment-col-edit,
.payment-view-modal .payment-col-delete {
    width: 18%;
}

.payment-view-modal .payment-date-cell,
.payment-view-modal .payment-amount-cell,
.payment-view-modal .payment-edit-cell,
.payment-view-modal .payment-delete-cell {
    min-width: 0 !important;
    padding: 11px 9px !important;
    vertical-align: middle !important;
}

.payment-view-modal .payment-date-cell {
    white-space: nowrap;
    font-size: 13px;
}

.payment-view-modal .payment-amount-cell {
    white-space: nowrap;
    font-size: 14px;
}

.payment-view-modal .payment-edit-cell,
.payment-view-modal .payment-delete-cell {
    text-align: center !important;
}

.payment-view-modal .payment-row-button {
    width: auto;
    min-width: 74px;
    min-height: 34px;
    padding: 6px 9px;
}

/* Keep every payment on ONE horizontal row on tablet/mobile */
@media (max-width: 991px) {
    .payment-view-modal .payment-table-card {
        overflow: hidden !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
    }

    .payment-view-modal .payment-table-wrap {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    .payment-view-modal .payment-table {
        display: table !important;
        width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed !important;
    }

    .payment-view-modal .payment-table thead {
        display: table-header-group !important;
    }

    .payment-view-modal .payment-table tbody {
        display: table-row-group !important;
        width: auto !important;
    }

    .payment-view-modal .payment-table tbody tr {
        display: table-row !important;
        width: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: #ffffff !important;
        box-shadow: none !important;
    }

    .payment-view-modal .payment-table tbody td {
        display: table-cell !important;
        width: auto !important;
        min-width: 0 !important;
        min-height: 0 !important;
        padding: 10px 7px !important;
        border-right: 1px solid #edf2f7 !important;
        border-bottom: 1px solid #edf2f7 !important;
        background: #ffffff !important;
        text-align: inherit !important;
        line-height: 1.5 !important;
        vertical-align: middle !important;
    }

    .payment-view-modal .payment-table tbody td:last-child {
        border-right: 0 !important;
    }

    .payment-view-modal .payment-table tbody td::before {
        display: none !important;
        content: none !important;
    }

    .payment-view-modal .payment-date-cell {
        font-size: 12px !important;
        font-weight: 700;
    }

    .payment-view-modal .payment-amount-cell {
        font-size: 14px !important;
        font-weight: 800;
    }

    .payment-view-modal .payment-row-button {
        display: inline-flex !important;
        width: auto !important;
        min-width: 68px !important;
        min-height: 34px !important;
        padding: 6px 7px !important;
        font-size: 11px !important;
        gap: 4px !important;
    }
}

@media (max-width: 575px) {
    .payment-view-modal .payment-col-date {
        width: 39%;
    }

    .payment-view-modal .payment-col-amount {
        width: 25%;
    }

    .payment-view-modal .payment-col-edit,
    .payment-view-modal .payment-col-delete {
        width: 18%;
    }

    .payment-view-modal .payment-table thead th {
        padding: 9px 4px !important;
        font-size: 11px !important;
        text-align: center;
    }

    .payment-view-modal .payment-table thead th:first-child {
        text-align: left;
    }

    .payment-view-modal .payment-table thead th:nth-child(2) {
        text-align: right;
    }

    .payment-view-modal .payment-table tbody td {
        padding: 9px 4px !important;
    }

    .payment-view-modal .payment-date-cell {
        font-size: 11px !important;
        white-space: normal !important;
        line-height: 1.45 !important;
    }

    .payment-view-modal .payment-amount-cell {
        font-size: 12px !important;
    }

    .payment-view-modal .payment-row-button {
        min-width: 0 !important;
        width: 100% !important;
        min-height: 32px !important;
        padding: 5px 3px !important;
        font-size: 10px !important;
        gap: 3px !important;
    }

    .payment-view-modal .payment-row-button i {
        font-size: 11px !important;
    }
}

@media print {
    .payment-view-modal .payment-edit-cell,
    .payment-view-modal .payment-delete-cell,
    .payment-view-modal .payment-table thead th:nth-child(3),
    .payment-view-modal .payment-table thead th:nth-child(4) {
        display: none !important;
    }
}

/* =========================================================
   In-app Payment Voucher Viewer
   ========================================================= */
.payment-voucher-viewer {
    position: fixed;
    inset: 0;
    z-index: 13000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.payment-voucher-viewer.is-open {
    display: flex;
}

.payment-voucher-viewer .voucher-viewer-backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    background: rgba(15, 23, 42, .82);
}

.payment-voucher-viewer .voucher-viewer-panel {
    position: relative;
    z-index: 1;
    display: flex;
    width: 920px;
    max-width: 100%;
    max-height: calc(100vh - 24px);
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, .22);
    border-radius: 14px;
    background: #0f172a;
    box-shadow: 0 24px 70px rgba(0, 0, 0, .48);
}

.payment-voucher-viewer .voucher-viewer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px 10px 16px;
    background: #fff;
}

.payment-voucher-viewer .voucher-viewer-title {
    margin: 0;
    color: #1e293b;
    font-size: 17px;
    font-weight: 800;
}

.payment-voucher-viewer .voucher-viewer-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-width: 92px;
    min-height: 42px;
    padding: 7px 12px;
    border: 1px solid #fecaca;
    border-radius: 9px;
    background: #fff1f2;
    color: #dc2626;
    font-size: 15px;
    font-weight: 800;
}

.payment-voucher-viewer .voucher-viewer-close i {
    font-size: 17px;
}

.payment-voucher-viewer .voucher-viewer-body {
    display: flex;
    min-height: 220px;
    max-height: calc(100vh - 88px);
    align-items: center;
    justify-content: center;
    overflow: auto;
    padding: 10px;
    background: #111827;
}

.payment-voucher-viewer .voucher-viewer-image,
.payment-voucher-viewer .voucher-viewer-frame {
    display: none;
    max-width: 100%;
    border: 0;
    background: #fff;
}

.payment-voucher-viewer .voucher-viewer-image {
    width: auto;
    height: auto;
    max-height: calc(100vh - 110px);
    object-fit: contain;
}

.payment-voucher-viewer .voucher-viewer-frame {
    width: 100%;
    height: calc(100vh - 115px);
}

.payment-voucher-viewer .voucher-viewer-error {
    display: none;
    padding: 18px;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    text-align: center;
}

@media (max-width: 575px) {
    .payment-voucher-viewer {
        padding: 7px;
    }

    .payment-voucher-viewer .voucher-viewer-panel {
        max-height: calc(100vh - 14px);
    }

    .payment-voucher-viewer .voucher-viewer-close {
        min-width: 78px;
        min-height: 40px;
        padding: 6px 9px;
    }
}
</style>

<div class="modal-dialog modal-lg payment-view-modal" role="document">

    <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header payment-modal-header">

            <h4 class="modal-title" id="myModalLabel">
                <?= html_escape(lang('view_payments')); ?>
            </h4>

            <div class="payment-modal-actions">

                <!-- Print -->
                <button
                    type="button"
                    class="payment-modal-action"
                    onclick="window.print();"
                    title="<?= html_escape(lang('print')); ?>"
                    aria-label="<?= html_escape(lang('print')); ?>"
                >
                    <i class="fa fa-print"></i>
                </button>

                <!-- Close -->
                <button
                    type="button"
                    class="payment-modal-action payment-modal-close"
                    data-dismiss="modal"
                    title="<?= html_escape(lang('close')); ?>"
                    aria-label="<?= html_escape(lang('close')); ?>"
                >
                    <i class="fa fa-times"></i>
                </button>

            </div>

        </div>

        <!-- Modal Body -->
        <div class="modal-body">

            <!-- Payment Summary -->
            <div class="payment-summary-grid">

                <div class="payment-summary-item">
                    <span class="payment-summary-label">
                        <?= html_escape(lang('total')); ?>
                    </span>
                    <span class="payment-summary-value">
                        <?= $this->tec->formatMoney($total_amount); ?>
                    </span>
                </div>

                <div class="payment-summary-item is-paid">
                    <span class="payment-summary-label">
                        <?= html_escape(lang('paid')); ?>
                    </span>
                    <span class="payment-summary-value">
                        <?= $this->tec->formatMoney($paid_amount); ?>
                    </span>
                </div>

                <div class="payment-summary-item is-balance">
                    <span class="payment-summary-label">
                        <?= html_escape(lang('pay_balance')); ?>
                    </span>
                    <span class="payment-summary-value">
                        <?= $this->tec->formatMoney($balance_amount); ?>
                    </span>
                </div>

            </div>

            <?php if (!empty($inv) && $balance_amount > 0): ?>
                <div class="payment-add-wrap">
                    <a
                        href="<?= site_url('purchases/add_payment/' . (int) $inv->id . '/' . (int) $inv->supplier_id); ?>"
                        class="payment-add-button"
                        data-toggle="ajax"
                        title="<?= html_escape(lang('add_payment')); ?>"
                    >
                        <i class="fa fa-plus-circle"></i>
                        <span><?= html_escape(lang('add_payment')); ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Payments -->
            <div class="payment-table-card">

                <div class="payment-table-wrap">

                    <table
                        id="CompTable"
                        cellpadding="0"
                        cellspacing="0"
                        border="0"
                        class="table payment-table"
                    >

                        <thead>
                            <tr>
                                <th class="payment-col-date">
                                    <?= html_escape(lang('date')); ?>
                                </th>

                                <th class="payment-col-amount text-right">
                                    <?= html_escape(lang('amount')); ?>
                                </th>
                                <th class="payment-col-amount text-right">
                                    ဘောင်ချာပုံ
                                </th>
                                <th class="payment-col-edit text-center">
                                    <?= html_escape(lang('edit')); ?>
                                </th>

                                <th class="payment-col-delete text-center">
                                    <?= html_escape(lang('delete')); ?>
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($payments)): ?>

                                <?php foreach ($payments as $payment): ?>

                                    <tr class="row<?= (int) $payment->id; ?>">

                                        <!-- Date -->
                                        <td class="payment-date-cell">
                                            <?= html_escape($this->tec->hrld($payment->date)); ?>
                                        </td>

                                        <!-- Amount -->
                                        <td class="payment-amount-cell text-right">
                                            <?= $this->tec->formatMoney($payment->amount ?? 0); ?>
                                        </td>
                                        
                                        <td class="payment-voucher-cell text-center">

                                            <?php if (!empty($payment->attachment)): ?>
                                        
                                                <a
                                                    href="<?= base_url(
                                                        'files/' . rawurlencode($payment->attachment)
                                                    ); ?>"
                                                    class="payment-voucher has-file"
                                                    title="View voucher"
                                                    aria-label="View voucher"
                                                >
                                                    <i class="fa fa-paperclip"></i>
                                                    <span>Voucher</span>
                                                </a>
                                        
                                            <?php else: ?>
                                        
                                                <span
                                                    class="payment-voucher"
                                                    title="No voucher"
                                                >
                                                    <i class="fa fa-paperclip"></i>
                                                    <span>No file</span>
                                                </span>
                                        
                                            <?php endif; ?>
                                        
                                        </td>

                                        <!-- Edit -->
                                        <td class="payment-edit-cell text-center">
                                            <a
                                                href="<?= site_url('purchases/edit_payment/' . (int) $payment->id); ?>"
                                                class="payment-row-button payment-edit-button tip"
                                                data-toggle="ajax"
                                                title="<?= html_escape(lang('edit')); ?>"
                                            >
                                                <i class="fa fa-edit"></i>
                                                <span><?= html_escape(lang('edit')); ?></span>
                                            </a>
                                        </td>

                                        <!-- Delete -->
                                        <td class="payment-delete-cell text-center">
                                            <a
                                                href="<?= site_url('purchases/delete_payment/' . (int) $payment->id); ?>"
                                                class="payment-row-button payment-delete-button payment-delete-link tip"
                                                data-confirm="<?= html_escape(lang('alert_x_payment')); ?>"
                                                title="<?= html_escape(lang('delete')); ?>"
                                            >
                                                <i class="fa fa-trash"></i>
                                                <span><?= html_escape(lang('delete')); ?></span>
                                            </a>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr class="payment-empty-row">
                                    <td colspan="4" class="payment-empty-cell">
                                        <i class="fa fa-inbox payment-empty-icon"></i>
                                        <?= html_escape(lang('no_data_available')); ?>
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<div
    id="paymentVoucherViewer"
    class="payment-voucher-viewer"
    aria-hidden="true"
>
    <button
        type="button"
        class="voucher-viewer-backdrop"
        data-payment-voucher-close="1"
        aria-label="ပိတ်ရန်"
    ></button>

    <div
        class="voucher-viewer-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="paymentVoucherViewerTitle"
    >
        <div class="voucher-viewer-header">
            <h4
                class="voucher-viewer-title"
                id="paymentVoucherViewerTitle"
            >
                ဘောင်ချာပုံ
            </h4>

            <button
                type="button"
                class="voucher-viewer-close"
                data-payment-voucher-close="1"
            >
                <i class="fa fa-times"></i>
                <span>ပိတ်ရန်</span>
            </button>
        </div>

        <div class="voucher-viewer-body">
            <img
                id="paymentVoucherViewerImage"
                class="voucher-viewer-image"
                src=""
                alt="ဘောင်ချာပုံ"
            >

            <iframe
                id="paymentVoucherViewerFrame"
                class="voucher-viewer-frame"
                src="about:blank"
                title="ဘောင်ချာဖိုင်"
            ></iframe>

            <div
                id="paymentVoucherViewerError"
                class="voucher-viewer-error"
            >
                ဘောင်ချာပုံကို ဖွင့်၍မရပါ။
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" charset="UTF-8">
$(document).ready(function () {

    function closePaymentVoucherViewer() {
        var $viewer = $('#paymentVoucherViewer');

        $viewer
            .removeClass('is-open')
            .attr('aria-hidden', 'true');

        $('#paymentVoucherViewerImage')
            .hide()
            .attr('src', '');

        $('#paymentVoucherViewerFrame')
            .hide()
            .attr('src', 'about:blank');

        $('#paymentVoucherViewerError').hide();
    }

    /*
     * Voucher ကို app အတွင်း popup viewer ဖြင့်ပြပါမည်။
     */
    $(document)
        .off('click.paymentVoucherInApp', '.payment-voucher.has-file')
        .on(
            'click.paymentVoucherInApp',
            '.payment-voucher.has-file',
            function (e) {
                var href = $(this).attr('href') || '';
                var $viewer = $('#paymentVoucherViewer');
                var $image = $('#paymentVoucherViewerImage');
                var $frame = $('#paymentVoucherViewerFrame');
                var $error = $('#paymentVoucherViewerError');

                if (!href || !$viewer.length) {
                    return;
                }

                e.preventDefault();

                $image.hide().attr('src', '');
                $frame.hide().attr('src', 'about:blank');
                $error.hide();

                if (/\.pdf(?:[?#]|$)/i.test(href)) {
                    $frame.attr('src', href).show();
                } else {
                    $image
                        .off('error.paymentVoucherViewer')
                        .on('error.paymentVoucherViewer', function () {
                            $(this).hide();
                            $error.show();
                        })
                        .attr('src', href)
                        .show();
                }

                $viewer
                    .addClass('is-open')
                    .attr('aria-hidden', 'false');

                window.setTimeout(function () {
                    $viewer
                        .find('.voucher-viewer-close')
                        .trigger('focus');
                }, 50);

                return false;
            }
        );

    $(document)
        .off(
            'click.paymentVoucherClose',
            '[data-payment-voucher-close]'
        )
        .on(
            'click.paymentVoucherClose',
            '[data-payment-voucher-close]',
            closePaymentVoucherViewer
        );

    $(document)
        .off('keydown.paymentVoucherViewer')
        .on('keydown.paymentVoucherViewer', function (event) {
            if (
                event.key === 'Escape' &&
                $('#paymentVoucherViewer').hasClass('is-open')
            ) {
                closePaymentVoucherViewer();
            }
        });

    /*
     * Delete Payment Confirmation
     */
    $(document)
        .off('click.paymentDelete', '.payment-delete-link')
        .on('click.paymentDelete', '.payment-delete-link', function (e) {

            var message = $(this).attr('data-confirm');

            if (!message) {
                message = 'Are you sure?';
            }

            if (!window.confirm(message)) {
                e.preventDefault();
                return false;
            }

            return true;
        });

});
</script>
