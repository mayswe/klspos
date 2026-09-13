<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<style>
/* =========================================================
   KLSPOS ERP - Purchase View Modal
   ========================================================= */

.purchase-view-modal {
    width: 920px;
    max-width: calc(100% - 30px);
    margin: 25px auto;
    font-size: 16px;
}

.purchase-view-modal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 20px 55px rgba(15, 23, 42, 0.25);
}

/* =========================================================
   Modal Header
   ========================================================= */

.purchase-view-modal .purchase-modal-header {
    position: relative;
    min-height: 64px;
    padding: 17px 118px 17px 22px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.purchase-view-modal .modal-title {
    margin: 0;
    color: #1e293b;
    font-size: 18px;
    font-weight: 700;
    line-height: 30px;
}

.purchase-view-modal .purchase-modal-actions {
    position: absolute;
    top: 15px;
    right: 16px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.purchase-view-modal .purchase-modal-action {
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

.purchase-view-modal .purchase-modal-action:hover,
.purchase-view-modal .purchase-modal-action:focus {
    border-color: #94a3b8;
    background: #f1f5f9;
    color: #1e293b;
    outline: none;
}

.purchase-view-modal .purchase-modal-close:hover,
.purchase-view-modal .purchase-modal-close:focus {
    border-color: #fecaca;
    background: #fef2f2;
    color: #dc2626;
}

/* =========================================================
   Modal Body
   ========================================================= */

.purchase-view-modal .modal-body {
    padding: 20px 22px 24px;
    background: #f8fafc;
    height: auto;
}

/* =========================================================
   Purchase Information
   ========================================================= */

.purchase-view-modal .purchase-info-card {
    margin-bottom: 18px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    background: #ffffff;
}

.purchase-view-modal .purchase-info-table {
    width: 100%;
    margin: 0;
    border: 0;
    border-collapse: collapse;
}

.purchase-view-modal .purchase-info-table tr:last-child td {
    border-bottom: 0;
}

.purchase-view-modal .purchase-info-table td {
    padding: 11px 14px;
    border-top: 0 !important;
    border-right: 0 !important;
    border-bottom: 1px solid #edf2f7;
    border-left: 0 !important;
    vertical-align: middle;
    color: #334155;
    font-size: 14px;
    line-height: 1.6;
}

.purchase-view-modal .purchase-info-table .purchase-info-label {
    width: 205px;
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
}

.purchase-view-modal .purchase-info-table .purchase-info-value {
    background: #ffffff;
    color: #1e293b;
    font-weight: 500;
    overflow-wrap: anywhere;
}

.purchase-view-modal .purchase-attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
}

.purchase-view-modal .purchase-attachment-link:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

/* =========================================================
   Purchase Items
   ========================================================= */

.purchase-view-modal .purchase-items-card {
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    background: #ffffff;
}

.purchase-view-modal .purchase-items-scroll {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.purchase-view-modal .purchase-items-table {
    width: 100%;
    min-width: 780px;
    margin: 0;
    border: 0;
    border-collapse: collapse;
    table-layout: auto;
}

.purchase-view-modal .purchase-items-table th {
    padding: 12px 10px;
    border-top: 0 !important;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #dbe3ec;
    border-left: 0 !important;
    background: #f1f5f9;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.5;
    vertical-align: middle;
    white-space: nowrap;
}

.purchase-view-modal .purchase-items-table th:last-child {
    border-right: 0;
}

.purchase-view-modal .purchase-items-table td {
    padding: 12px 10px;
    border-top: 0 !important;
    border-right: 1px solid #edf2f7;
    border-bottom: 2px solid #edf2f7;
    border-left: 0 !important;
    background: #ffffff;
    color: #334155;
    font-size: 16px;
    line-height: 1.55;
    vertical-align: middle;
}

.purchase-view-modal .purchase-items-table td:last-child {
    border-right: 0;
}

.purchase-view-modal .purchase-items-table tbody tr:last-child td {
    border-bottom: 0;
}

.purchase-view-modal .purchase-items-table tbody tr:hover td {
    background: #f8fafc;
}

.purchase-view-modal .purchase-item-id {
    width: 58px;
    text-align: center;
}

.purchase-view-modal .purchase-product-name {
    min-width: 210px;
    color: #1e293b;
    font-weight: 600;
}

.purchase-view-modal .purchase-product-code {
    display: inline-block;
    margin-left: 4px;
    color: #64748b;
    font-size: 12px;
    font-weight: 500;
}

.purchase-view-modal .purchase-number-cell {
    min-width: 115px;
    white-space: nowrap;
}

.purchase-view-modal .purchase-item-subtotal {
    color: #1d4ed8;
    font-weight: 700;
}

/* =========================================================
   Grand Total
   ========================================================= */

.purchase-view-modal .purchase-grand-total {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 35px;
    min-height: 58px;
    padding: 13px 16px;
    border-top: 1px solid #bfdbfe;
    background: #eff6ff;
}

.purchase-view-modal .purchase-grand-total-label {
    color: #475569;
    font-size: 15px;
    font-weight: 700;
}

.purchase-view-modal .purchase-grand-total-value {
    min-width: 150px;
    color: #1e3a8a;
    font-size: 18px;
    font-weight: 800;
    text-align: right;
    white-space: nowrap;
}

/* =========================================================
   Tablet / Mobile
   ========================================================= */

@media (max-width: 991px) {

    .purchase-view-modal {
        width: calc(100% - 20px);
        max-width: none;
        height: calc(100vh - 20px);
        height: calc(100dvh - 20px);
        margin: 10px auto;
        font-size: 16px;
    }

    .purchase-view-modal .modal-content {
        height: 100%;
        display: flex;
        flex-direction: column;
        border-radius: 13px;
    }

    .purchase-view-modal .purchase-modal-header {
        flex: 0 0 auto;
        min-height: 58px;
        padding: 13px 105px 13px 16px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        z-index: 10;
    }

    .purchase-view-modal .modal-title {
        font-size: 16px;
        line-height: 32px;
    }

    .purchase-view-modal .purchase-modal-actions {
        top: 12px;
        right: 12px;
    }

    .purchase-view-modal .purchase-modal-action {
        width: 34px;
        height: 34px;
        line-height: 32px;
    }

    .purchase-view-modal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        padding: 14px;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Information rows become mobile cards */

    .purchase-view-modal .purchase-info-card {
        margin-bottom: 14px;
        border-radius: 11px;
    }

    .purchase-view-modal .purchase-info-table,
    .purchase-view-modal .purchase-info-table tbody,
    .purchase-view-modal .purchase-info-table tr,
    .purchase-view-modal .purchase-info-table td {
        display: block;
        width: 100%;
    }

    .purchase-view-modal .purchase-info-table tr {
        padding: 10px 13px;
        border-bottom: 1px solid #edf2f7;
    }

    .purchase-view-modal .purchase-info-table tr:last-child {
        border-bottom: 0;
    }

    .purchase-view-modal .purchase-info-table td {
        padding: 0;
        border: 0 !important;
    }

    .purchase-view-modal .purchase-info-table .purchase-info-label {
        width: 100%;
        margin-bottom: 3px;
        background: transparent;
        color: #64748b;
        font-size: 16px;
        font-weight: 600;
    }

    .purchase-view-modal .purchase-info-table .purchase-info-value {
        background: transparent;
        color: #1e293b;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.6;
    }

    /* Items become ERP mobile cards */

    .purchase-view-modal .purchase-items-card {
        overflow: visible;
        border: 0;
        background: transparent;
    }

    .purchase-view-modal .purchase-items-scroll {
        overflow: visible;
    }

    .purchase-view-modal .purchase-items-table {
        display: block;
        width: 100%;
        min-width: 0;
        border: 0;
    }

    .purchase-view-modal .purchase-items-table thead {
        display: none;
    }

    .purchase-view-modal .purchase-items-table tbody {
        display: block;
        width: 100%;
    }

    .purchase-view-modal .purchase-items-table tbody tr {
        position: relative;
        display: block;
        width: 100%;
        margin-bottom: 12px;
        padding: 12px 14px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }

    .purchase-view-modal .purchase-items-table tbody tr:hover td {
        background: transparent;
    }

    .purchase-view-modal .purchase-items-table tbody td {
        display: flex;
        width: 100%;
        min-width: 0;
        min-height: 38px;
        padding: 8px 0;
        border-top: 0 !important;
        border-right: 0 !important;
        border-bottom: 1px dashed #e2e8f0;
        border-left: 0 !important;
        background: transparent;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        text-align: right !important;
        overflow-wrap: anywhere;
    }

    .purchase-view-modal .purchase-items-table tbody td::before {
        content: attr(data-label);
        flex: 0 0 43%;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
        text-align: left;
    }

    .purchase-view-modal .purchase-items-table tbody td:last-child {
        border-bottom: 0;
    }

    .purchase-view-modal .purchase-items-table tbody .purchase-product-name {
        display: block;
        padding: 0 0 11px;
        border-bottom: 1px solid #e2e8f0 !important;
        color: #1e293b;
        font-size: 15px;
        font-weight: 700;
        text-align: left !important;
    }

    .purchase-view-modal .purchase-items-table tbody .purchase-product-name::before {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: 11px;
        font-weight: 600;
    }

    .purchase-view-modal .purchase-product-code {
        display: block;
        margin: 3px 0 0;
        font-size: 12px;
    }

    .purchase-view-modal .purchase-item-id {
        width: 100%;
        text-align: right !important;
    }

    .purchase-view-modal .purchase-item-subtotal {
        color: #1d4ed8;
        font-size: 15px;
        font-weight: 800;
    }

    /* Sticky grand total */

    .purchase-view-modal .purchase-grand-total {
        position: sticky;
        bottom: -14px;
        z-index: 20;
        justify-content: space-between;
        gap: 15px;
        min-height: 60px;
        margin: 4px -14px -14px;
        padding: 14px 16px;
        border-top: 0;
        background: #1e3a8a;
        box-shadow: 0 -4px 15px rgba(15, 23, 42, 0.18);
    }

    .purchase-view-modal .purchase-grand-total-label,
    .purchase-view-modal .purchase-grand-total-value {
        color: #ffffff;
    }

    .purchase-view-modal .purchase-grand-total-label {
        font-size: 14px;
    }

    .purchase-view-modal .purchase-grand-total-value {
        min-width: 0;
        font-size: 18px;
    }
}

/* Very small mobile */

@media (max-width: 575px) {

    .purchase-view-modal {
        width: 100%;
        height: 100vh;
        height: 100dvh;
        margin: 0;
        font-size: 16px;
    }

    .purchase-view-modal .modal-content {
        border-radius: 0;
    }

    .purchase-view-modal .modal-body {
        padding: 10px;
    }

    .purchase-view-modal .purchase-items-table tbody tr {
        padding: 11px 12px;
    }

    .purchase-view-modal .purchase-items-table tbody td {
        gap: 12px;
    }

    .purchase-view-modal .purchase-items-table tbody td::before {
        flex-basis: 46%;
    }

    .purchase-view-modal .purchase-grand-total {
        bottom: -10px;
        margin: 3px -10px -10px;
    }
}

/* =========================================================
   Print
   ========================================================= */

@media print {

    body * {
        visibility: hidden !important;
    }

    .purchase-view-modal,
    .purchase-view-modal * {
        visibility: visible !important;
    }

    .purchase-view-modal {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        font-size: 16px;
    }

    .purchase-view-modal .modal-content {
        height: auto !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .purchase-view-modal .purchase-modal-actions {
        display: none !important;
    }

    .purchase-view-modal .modal-body {
        height: auto !important;
        padding: 15px !important;
        overflow: visible !important;
        background: #ffffff !important;
    }

    .purchase-view-modal .purchase-items-table {
        display: table !important;
        min-width: 100% !important;
    }

    .purchase-view-modal .purchase-items-table thead {
        display: table-header-group !important;
    }

    .purchase-view-modal .purchase-items-table tbody {
        display: table-row-group !important;
    }

    .purchase-view-modal .purchase-items-table tbody tr {
        display: table-row !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .purchase-view-modal .purchase-items-table tbody td {
        display: table-cell !important;
        width: auto !important;
        min-height: 0 !important;
        padding: 8px !important;
        border-right: 1px solid #dddddd !important;
        border-bottom: 1px solid #dddddd !important;
        text-align: inherit !important;
    }

    .purchase-view-modal .purchase-items-table tbody td::before {
        display: none !important;
    }

    .purchase-view-modal .purchase-grand-total {
        position: static !important;
        margin: 0 !important;
        background: #eeeeee !important;
        box-shadow: none !important;
    }

    .purchase-view-modal .purchase-grand-total-label,
    .purchase-view-modal .purchase-grand-total-value {
        color: #000000 !important;
    }

    .modal-backdrop {
        display: none !important;
    }
}
/* =========================================================
   KLSPOS Purchase Modal - Tablet/Mobile Final Fix
   လက်ရှိ CSS အောက်ဆုံးတွင် ထည့်ပါ
   ========================================================= */

/* Myanmar text rendering */
.purchase-view-modal,
.purchase-view-modal table,
.purchase-view-modal button {
    font-family: "Pyidaungsu", "Noto Sans Myanmar", "Myanmar Text",
                 Arial, sans-serif;
}

.purchase-view-modal .purchase-info-label,
.purchase-view-modal .purchase-info-value,
.purchase-view-modal .purchase-items-table td,
.purchase-view-modal .purchase-items-table td::before {
    line-height: 1.9 !important;
}

/* =========================================================
   Tablet Mode: 576px - 991px
   Label / Value ကို ဘေးချင်းကပ်ပြမည်
   ========================================================= */

@media (min-width: 576px) and (max-width: 991px) {

    .purchase-view-modal {
        width: calc(100% - 20px);
        max-width: 760px;
        height: auto !important;
        max-height: none !important;
        font-size: 16px;
    }
    
    .purchase-view-modal .modal-content {
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
    }
    
    .purchase-view-modal .modal-body {
        padding: 12px 14px;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        overflow: visible !important;
    }

    /* Purchase Information */

    .purchase-view-modal .purchase-info-table {
        display: table !important;
        width: 100% !important;
    }

    .purchase-view-modal .purchase-info-table tbody {
        display: table-row-group !important;
        width: 100% !important;
    }

    .purchase-view-modal .purchase-info-table tr {
        display: table-row !important;
        width: 100% !important;
        padding: 0 !important;
        border-bottom: 0 !important;
    }

    .purchase-view-modal .purchase-info-table td {
        display: table-cell !important;
        width: auto !important;
        padding: 11px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #edf2f7 !important;
    }

    .purchase-view-modal .purchase-info-table tr:last-child td {
        border-bottom: 0 !important;
    }

    .purchase-view-modal .purchase-info-table .purchase-info-label {
        margin: 0 !important;
        border-right: 1px solid #edf2f7 !important;
        background: #f8fafc !important;
        color: #64748b;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.9 !important;
        white-space: normal;
    }

    .purchase-view-modal .purchase-info-table .purchase-info-value {
        background: #ffffff !important;
        color: #1e293b;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.9 !important;
        overflow-wrap: anywhere;
    }

    /* Product card */

    .purchase-view-modal .purchase-items-table tbody tr {
        margin-bottom: 10px;
        padding: 13px 15px;
    }

    .purchase-view-modal .purchase-items-table tbody td {
        min-height: 40px;
        padding: 8px 0 !important;
        gap: 20px;
        line-height: 1.8 !important;
        border-bottom: 1px dashed #1c1d1e !important;
    }

    .purchase-view-modal .purchase-items-table tbody td::before {
        flex: 0 0 36%;
        max-width: 36%;
        font-size: 16px;
        line-height: 1.9 !important;
        overflow-wrap: anywhere;
    }

    /* Product name row */

    .purchase-view-modal .purchase-items-table tbody .purchase-product-name {
        display: table-cell !important;
        min-height: auto;
        padding: 2px 0 13px !important;
        color: #1e293b;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.5 !important;
        text-align: left !important;
    
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .purchase-view-modal
    .purchase-items-table
    tbody
    .purchase-product-name::before {
        display: block !important;
        max-width: 100%;
        margin-bottom: 8px !important;
        color: #64748b;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.9 !important;
    }

    .purchase-view-modal .purchase-product-code {
        display: inline !important;
        margin: 0 0 0 4px !important;
        color: #64748b;
        font-size: 16px;
        font-weight: 500;
        line-height: 1.5 !important;
        white-space: nowrap !important;
    }

    .purchase-view-modal .purchase-item-id {
        min-height: 35px !important;
    }

    .purchase-view-modal .purchase-item-subtotal {
        font-size: 16px;
    }

    /* Total bar */

    .purchase-view-modal .purchase-grand-total {
        min-height: 58px;
        padding: 12px 18px;
    }
}

/* =========================================================
   Mobile Mode: 575px and below
   ========================================================= */

@media (max-width: 575px) {

    .purchase-view-modal .purchase-modal-header {
        padding-left: 13px;
    }

    .purchase-view-modal .modal-title {
        font-size: 15px;
        line-height: 1.9;
    }

    .purchase-view-modal .purchase-info-table tr {
        padding: 10px 12px !important;
    }

    .purchase-view-modal .purchase-info-table .purchase-info-label {
        margin-bottom: 5px !important;
        font-size: 16px;
        line-height: 1.9 !important;
    }

    .purchase-view-modal .purchase-info-table .purchase-info-value {
        font-size: 13px;
        line-height: 1.9 !important;
    }

    .purchase-view-modal .purchase-items-table tbody tr {
        padding: 12px;
    }

    .purchase-view-modal .purchase-items-table tbody td {
        min-height: 40px;
        padding: 8px 0 !important;
        line-height: 1.9 !important;
    }

    .purchase-view-modal .purchase-items-table tbody td::before {
        flex: 0 0 44%;
        max-width: 44%;
        padding-right: 7px;
        line-height: 1.9 !important;
        overflow-wrap: anywhere;
    }

    .purchase-view-modal .purchase-items-table tbody .purchase-product-name {
        padding: 2px 0 13px !important;
        font-size: 14px;
        line-height: 2 !important;
        overflow-wrap: anywhere;
    }

    .purchase-view-modal
    .purchase-items-table
    tbody
    .purchase-product-name::before {
        margin-bottom: 9px !important;
        line-height: 1.9 !important;
    }

    .purchase-view-modal .purchase-product-code {
        margin-top: 5px !important;
        line-height: 1.7 !important;
    }
}

/* =========================================================
   In-app Purchase Voucher Viewer
   ========================================================= */
.purchase-attachment-viewer {
    position: fixed;
    inset: 0;
    z-index: 13000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 12px;
}

.purchase-attachment-viewer.is-open {
    display: flex;
}

.purchase-attachment-viewer .attachment-viewer-backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    background: rgba(15, 23, 42, .82);
}

.purchase-attachment-viewer .attachment-viewer-panel {
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

.purchase-attachment-viewer .attachment-viewer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px 10px 16px;
    background: #fff;
}

.purchase-attachment-viewer .attachment-viewer-title {
    margin: 0;
    color: #1e293b;
    font-size: 17px;
    font-weight: 800;
}

.purchase-attachment-viewer .attachment-viewer-close {
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

.purchase-attachment-viewer .attachment-viewer-close i {
    font-size: 17px;
}

.purchase-attachment-viewer .attachment-viewer-body {
    display: flex;
    min-height: 220px;
    max-height: calc(100vh - 88px);
    align-items: center;
    justify-content: center;
    overflow: auto;
    padding: 10px;
    background: #111827;
}

.purchase-attachment-viewer .attachment-viewer-image,
.purchase-attachment-viewer .attachment-viewer-frame {
    display: none;
    max-width: 100%;
    border: 0;
    background: #fff;
}

.purchase-attachment-viewer .attachment-viewer-image {
    width: auto;
    height: auto;
    max-height: calc(100vh - 110px);
    object-fit: contain;
}

.purchase-attachment-viewer .attachment-viewer-frame {
    width: 100%;
    height: calc(100vh - 115px);
}

.purchase-attachment-viewer .attachment-viewer-error {
    display: none;
    padding: 18px;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    text-align: center;
}

@media (max-width: 575px) {
    .purchase-attachment-viewer {
        padding: 7px;
    }

    .purchase-attachment-viewer .attachment-viewer-panel {
        max-height: calc(100vh - 14px);
    }

    .purchase-attachment-viewer .attachment-viewer-close {
        min-width: 78px;
        min-height: 40px;
        padding: 6px 9px;
    }
}
</style>

<div class="modal-dialog purchase-view-modal" role="document">

    <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header purchase-modal-header">

            <h4 class="modal-title" id="myModalLabel">
                <?= html_escape(lang('purchase')); ?>
                #<?= (int) $purchase->id; ?>
            </h4>

            <div class="purchase-modal-actions">

                <button
                    type="button"
                    class="purchase-modal-action"
                    onclick="window.print();"
                    title="<?= html_escape(lang('print')); ?>"
                    aria-label="<?= html_escape(lang('print')); ?>"
                >
                    <i class="fa fa-print"></i>
                </button>

                <button
                    type="button"
                    class="purchase-modal-action purchase-modal-close"
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

            <!-- Purchase Information -->
            <div class="purchase-info-card">

                <table class="table purchase-info-table">
                    <tbody>

                        <tr>
                            <td class="purchase-info-label">
                                <?= html_escape(lang('date')); ?>
                            </td>

                           <td class="purchase-info-value">
                                <?php
                                    $date = new DateTime($purchase->date);
                                ?>
                            
                                <?= $date->format('d M Y (D)'); ?><br>
                                <?= $date->format('h:i A'); ?>
                            </td>
                            <td class="purchase-info-label">
                                <?= html_escape(lang('supplier')); ?>
                            </td>

                            <td class="purchase-info-value">
                                <?= !empty($purchase->supplier_name)
                                    ? html_escape($purchase->supplier_name)
                                    : '—'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="purchase-info-label">
                                <?= html_escape(lang('store')); ?>
                            </td>

                            <td class="purchase-info-value">
                                <?= !empty($purchase->store_name)
                                    ? html_escape($purchase->store_name)
                                    : '—'; ?>
                            </td>
                             <td class="purchase-info-label">
                                <?= html_escape(lang('transportation_charges')); ?>
                            </td>

                            <td class="purchase-info-value">
                                <?= $this->tec->formatMoney($purchase->delivery ?? 0); ?>
                            </td>
                        </tr>

                        <?php if (!empty($purchase->attachment)): ?>
                            <tr>
                                <td class="purchase-info-label">
                                    <?= html_escape(lang('attachment')); ?>
                                </td>

                                <td class="purchase-info-value" colspan="3">

                                    <a
                                        href="<?= base_url('uploads/' . rawurlencode($purchase->attachment)); ?>"
                                        target="_self"
                                        class="purchase-attachment-link"
                                        aria-label="<?= html_escape(lang('attachment')); ?>"
                                    >
                                        <i class="fa fa-paperclip"></i>

                                        <span>
                                            <?= html_escape($purchase->attachment); ?>
                                        </span>
                                    </a>

                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($purchase->note)): ?>
                            <tr>
                                <td class="purchase-info-label">
                                    <?= html_escape(lang('note')); ?>
                                </td>

                                <td class="purchase-info-value" colspan="3">
                                    <?= nl2br(html_escape($purchase->note)); ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>

            </div>

            <!-- Purchase Items -->
            <div class="purchase-items-card">

                <div class="purchase-items-scroll">

                    <table class="table purchase-items-table">

                        <thead>
                            <tr>
                                <th>
                                    <?= html_escape(lang('product')); ?>
                                </th>

                                <th class="text-center">
                                    <?= html_escape(lang('purchase_qty')); ?>
                                </th>

                                <th class="text-center">
                                    <?= html_escape(lang('unit_convert')); ?>
                                </th>

                                <th class="text-right">
                                    <?= html_escape(lang('unit_cost_per_prodcut')); ?>
                                    
                                </th>

                                <th class="text-right">
                                    <?= html_escape(lang('subtotal')); ?>
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($items)): ?>
                                <?php
                                    $no = 1;
                                ?>
                                <?php foreach ($items as $item): ?>

                                    <tr>
                                        <td
                                            class="purchase-product-name"
                                            data-label="<?= html_escape(lang('product')); ?>"
                                        >
                                            <?= html_escape($item->product_name); ?>

                                            <?php if (!empty($item->product_code)): ?>
                                                <span class="purchase-product-code">
                                                    (<?= html_escape($item->product_code); ?>)
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td
                                            class="text-center purchase-number-cell"
                                            data-label="<?= html_escape(lang('buy_quantity')); ?>"
                                        >
                                            <?= rtrim(
                                                rtrim(
                                                    number_format(
                                                        $this->tec->formatQuantity($item->primary_qty ?? 0),
                                                        2,
                                                        '.',
                                                        ','
                                                    ),
                                                    '0'
                                                ),
                                                '.'
                                            ); ?>
                                            <?php if (!empty($item->primary_unit_name)) { ?>
                                                <?= html_escape(
                                                    $item->primary_unit_name
                                                ); ?>
                    
                                            <?php } ?>
                                           
                                        </td>

                                        <td
                                            class="text-center purchase-packaging-cell"
                                            data-label="<?= html_escape(lang('packaging')); ?>"
                                        >
                                            <?php
                                                $primary_unit_id = (int) ($item->primary_unit ?? 0);
                                                $base_unit_id    = (int) ($item->base_unit_id ?? 0);

                                                $primary_unit_name = trim(
                                                    (string) ($item->primary_unit_name ?? '')
                                                );

                                                $base_unit_name = trim(
                                                    (string) ($item->base_unit_name ?? '')
                                                );

                                                $unit_convert = (float) ($item->unit_convert ?? 0);

                                                $has_real_conversion =
                                                    $primary_unit_id > 0 &&
                                                    $base_unit_id > 0 &&
                                                    $primary_unit_id !== $base_unit_id &&
                                                    $unit_convert > 0 &&
                                                    $primary_unit_name !== '' &&
                                                    $base_unit_name !== '';
                                            ?>

                                            <?php if ($has_real_conversion) { ?>

                                                1
                                                <?= html_escape($primary_unit_name); ?>

                                                =

                                                <?= $this->tec->formatNumber($unit_convert); ?>
                                                <?= html_escape($base_unit_name); ?>

                                            <?php } elseif ($primary_unit_name !== '') { ?>

                                                1
                                                <?= html_escape($primary_unit_name); ?>

                                            <?php } else { ?>

                                                -

                                            <?php } ?>
                                        </td>

                                        <td
                                            class="text-right purchase-price-cell"
                                            data-label="<?= html_escape(lang('unit_price')); ?>"
                                        >
                                            <?= $this->tec->formatMoney($item->net_unit_cost ?? 0); ?>
                                        </td>

                                        <td
                                            class="text-right purchase-number-cell purchase-item-subtotal"
                                            data-label="<?= html_escape(lang('subtotal')); ?>"
                                        >
                                            <?= $this->tec->formatMoney($item->subtotal ?? 0); ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td
                                        colspan="5"
                                        class="text-center"
                                        style="padding:25px;"
                                    >
                                        <?= html_escape(lang('no_data_available')); ?>
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <!-- Grand Total -->
                <div class="purchase-grand-total">

                    <span class="purchase-grand-total-label">
                        <?= html_escape(lang('total')); ?>
                    </span>

                    <span class="purchase-grand-total-value">
                        <?= $this->tec->formatMoney($purchase->total ?? 0); ?>
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>

<div
    id="purchaseAttachmentViewer"
    class="purchase-attachment-viewer"
    aria-hidden="true"
>
    <button
        type="button"
        class="attachment-viewer-backdrop"
        data-purchase-attachment-close="1"
        aria-label="ပိတ်ရန်"
    ></button>

    <div
        class="attachment-viewer-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="purchaseAttachmentViewerTitle"
    >
        <div class="attachment-viewer-header">
            <h4
                class="attachment-viewer-title"
                id="purchaseAttachmentViewerTitle"
            >
                ဘောင်ချာပုံ
            </h4>

            <button
                type="button"
                class="attachment-viewer-close"
                data-purchase-attachment-close="1"
            >
                <i class="fa fa-times"></i>
                <span>ပိတ်ရန်</span>
            </button>
        </div>

        <div class="attachment-viewer-body">
            <img
                id="purchaseAttachmentViewerImage"
                class="attachment-viewer-image"
                src=""
                alt="ဘောင်ချာပုံ"
            >

            <iframe
                id="purchaseAttachmentViewerFrame"
                class="attachment-viewer-frame"
                src="about:blank"
                title="ဘောင်ချာဖိုင်"
            ></iframe>

            <div
                id="purchaseAttachmentViewerError"
                class="attachment-viewer-error"
            >
                ဘောင်ချာပုံကို ဖွင့်၍မရပါ။
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" charset="UTF-8">
(function ($) {
    function closePurchaseAttachmentViewer() {
        var $viewer = $('#purchaseAttachmentViewer');

        $viewer
            .removeClass('is-open')
            .attr('aria-hidden', 'true');

        $('#purchaseAttachmentViewerImage')
            .hide()
            .attr('src', '');

        $('#purchaseAttachmentViewerFrame')
            .hide()
            .attr('src', 'about:blank');

        $('#purchaseAttachmentViewerError').hide();
    }

    $(document)
        .off('click.purchaseAttachmentOpen', '.purchase-attachment-link')
        .on(
            'click.purchaseAttachmentOpen',
            '.purchase-attachment-link',
            function (event) {
                var href = $(this).attr('href') || '';
                var $viewer = $('#purchaseAttachmentViewer');
                var $image = $('#purchaseAttachmentViewerImage');
                var $frame = $('#purchaseAttachmentViewerFrame');
                var $error = $('#purchaseAttachmentViewerError');

                if (!href || !$viewer.length) {
                    return;
                }

                event.preventDefault();

                $image.hide().attr('src', '');
                $frame.hide().attr('src', 'about:blank');
                $error.hide();

                if (/\.pdf(?:[?#]|$)/i.test(href)) {
                    $frame.attr('src', href).show();
                } else {
                    $image
                        .off('error.purchaseAttachmentViewer')
                        .on('error.purchaseAttachmentViewer', function () {
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
                        .find('.attachment-viewer-close')
                        .trigger('focus');
                }, 50);
            }
        );

    $(document)
        .off(
            'click.purchaseAttachmentClose',
            '[data-purchase-attachment-close]'
        )
        .on(
            'click.purchaseAttachmentClose',
            '[data-purchase-attachment-close]',
            closePurchaseAttachmentViewer
        );

    $(document)
        .off('keydown.purchaseAttachmentViewer')
        .on('keydown.purchaseAttachmentViewer', function (event) {
            if (
                event.key === 'Escape' &&
                $('#purchaseAttachmentViewer').hasClass('is-open')
            ) {
                closePurchaseAttachmentViewer();
            }
        });
})(jQuery);
</script>
