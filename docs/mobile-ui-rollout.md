# Mobile UI rollout

Products list is the first shared-style reference. Browser access was restored by correcting the .git directory owner. Products, Categories, Customers and Suppliers were inspected in the in-app browser; Phone layout checks were run at 390px and list-page checks at 320px. Save/delete/receive transactions were not submitted.

## Shared design

- Styles: themes/default/assets/css/mobile-ui.css; opt in using the kls-mobile-ui content class.
- Teal primary, neutral background, white cards, 14px card radius, 44px form and pagination targets.
- Preserve app=1 and app_lang for mobile navigation. Preserve existing permissions, pricing and data operations.
- Products legacy styles moved to products-mobile.css without changing their cascade; shared styles load afterward.
- Desktop index_web.php is unchanged. All 27 inventoried mobile view roots opt into the shared stylesheet. Page-specific legacy CSS remains in place.

## Existing mobile views

| View | Rollout |
| --- | --- |
| themes/default/views/auth/index_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/categories/index_mobile.php | Shared style applied; data loading and 320px layout checked |
| themes/default/views/customers/index_mobile.php | Shared style applied; data loading and 320px layout checked |
| themes/default/views/depreciation/index_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/openingstock/add_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/openingstock/index_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/products/add_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/products/adjustments_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/products/closing_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/products/edit_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/products/index_mobile.php | Shared style pilot; browser layout and name filtering checked |
| themes/default/views/products/price_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/products/units_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/purchases/add_expense_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/purchases/add_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/purchases/expensetype_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/purchases/purchases_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/purchases/receive_mobile.php | 390px receive form and read-only history dialog checked |
| themes/default/views/reports/alerts_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/reports/expenses_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/reports/profit_loss_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/reports/purchases_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/reports/sales_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/sales/sales_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/settings/stores_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/stocktransfers/index_mobile.php | Shared style applied; 390px initial render checked |
| themes/default/views/suppliers/index_mobile.php | Shared style applied; data loading and 320px layout checked |

## Validation limits

- All 27 mobile PHP views pass syntax validation.
- Checked initial render, shared stylesheet presence, and page-level overflow for report, stock and administrative routes at 390px.
- Product name filtering and Sales Report filter-panel toggle were exercised.
- Product add/edit forms were inspected without submitting data.
- Tables intentionally retain horizontal scrolling to preserve all columns.
- No complete transaction workflow test or automated visual regression suite has been run.

## Receive flow follow-up

- Receive date/time and store fields stack on phones; summary cards use the shared surface.
- Invalid-ID, validation and success redirects preserve app/mobile mode and selected language; desktop requests remain desktop.
- Six mocked invalid-ID redirect cases passed without a database connection.
- Existing receive record 2288 and its history dialog were inspected without modifying stock.
- Empty Product Add submission stayed on the form and displayed required-field errors.
- Successful save/delete/receive mutations still require isolated test data for full end-to-end verification.

## Isolated integration result

62 model/database checks pass via php tests/isolated_inventory.php. Test schemas contain synthetic data only and are removed after each run. Unused receipt deletion/quantity correction now adjusts the exact batch, item received totals, purchase status and signed stock movements atomically. Historical unit conversion and landed unit cost are preserved. Sold/transferred stock, conflicting links and ambiguous legacy matches are rejected. These checks do not replace browser end-to-end transaction testing; see tests/README.md.

The local receipt-link migration adds two nullable metadata columns without changing business quantities. New receipts store exact batch and movement associations. Existing receipts are matched conservatively when first corrected.


## Purchase deletion follow-up

The controller now delegates deletion to the model. Receipt/payment history, received quantities and remaining stock block deletion. Unpaid, unreceived purchases may be deleted while stock audit rows are retained. Direct item deletion fails explicitly instead of leaving stale stock and invoice totals. 62 isolated integration checks and PHP syntax checks pass. Browser transaction testing and complete Purchase Edit recalculation verification remain outstanding.

Purchase Edit follow-up: edits of unpaid, unreceived purchases validate all replacement lines before writing and commit header/items together. Totals use primary quantity times selected-unit cost plus header delivery once; delivery is allocated by primary quantity with rounding residue on the last line. Receipt/payment-backed and unreconciled legacy stock purchases are blocked. Empty/invalid edits preserve original records. Edit form totals, payment autofill and unit-change handling were corrected. 62 isolated model checks pass; browser end-to-end edit submission remains untested.

Controller save follow-up: 68 isolated checks pass, including real controller POST parsing, total persistence, success messaging and desktop/mobile-language redirects with stubbed framework services. Edit form action preserves app mode. Browser was at /login, so authenticated browser save remains pending.

Authenticated browser follow-up: inspected purchase 2288 without saving. Increasing primary quantity 60 to 61 at cost 7,900 displayed 481,900; adding delivery 100 displayed 482,000. Reload restored original quantity 60, delivery 0 and total 474,000. Corrected the edit form to show actual paid 474,000 and Received status, with an explanatory notice and disabled Save for paid/received purchases. Successful browser submission against disposable data remains outstanding; 68 isolated model/controller checks cover persistence.

Mobile Purchase Edit follow-up: app-only shared styling and a Purchases back link added. Verified at 390px without document overflow; record 2288 displays primary 60 lb, secondary 360 pieces, total/paid 474,000 and Received with Save disabled. Back navigation and View/Payments/Delete links preserve app=1 and app_lang. Desktop edit does not load the new stylesheet and retains correct unit selections. Browser checks were read-only; successful browser save remains untested.
