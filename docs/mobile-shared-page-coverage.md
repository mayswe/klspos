# Shared mobile page coverage

These views use the shared mobile shell only with app=1/mobile=1; desktop rendering remains unchanged. Existing dedicated mobile views are retained.

- `purchases/expenses`
- `purchases/edit_expense`
- `purchases/add_expensetype`
- `purchases/edit_expensetype`
- `customers/add`
- `customers/edit`
- `customers/customergroupadd`
- `suppliers/add`
- `suppliers/edit`
- `categories/add`
- `categories/edit`
- `stocktransfers/add`
- `stocktransfers/edit`
- `openingstock/edit`
- `products/adjustments_add`
- `warehouses/index`
- `warehouses/add`
- `warehouses/edit`
- `currencies/index`
- `currencies/add`
- `currencies/edit`
- `container_boxes/index`
- `container_boxes/add`
- `container_boxes/edit`
- `shippings/index`
- `shippings/add`
- `shippings/edit`
- `gift_cards/index`
- `gift_cards/add`
- `gift_cards/edit`
- `settings/index`
- `customers/customergroup`
- `categories/import`
- `depreciation/create`
- `products/add_unit`
- `products/edit_unit`
- `products/import`
- `products/selling_prices`
- `products/unit_conversions`
- `suppliers/advances`
- `suppliers/add_advance`
- `suppliers/edit_advance`
- `suppliers/opening`
- `suppliers/add_opening`
- `suppliers/edit_opening`
- `settings/add_store`
- `settings/edit_store`
- `settings/printers`
- `settings/add_printer`
- `settings/edit_printer`
- `reports/container_box`
- `reports/customer_order_report`
- `reports/customers`
- `reports/daily`
- `reports/dailysales`
- `reports/dailyspurchases`
- `reports/investment`
- `reports/monthly`
- `reports/payments`
- `reports/product_summary`
- `reports/products`
- `reports/profit_report`
- `reports/profitloss`
- `reports/purchasesupplier`
- `reports/registers`
- `reports/stocks`
- `reports/supplieradvances`
- `reports/top`
- `reports/warehouse_stock`
- `sales/opened`

Browser verified at 390px: Expenses list (loaded records/search), Customer Add, Supplier Add, Stock Transfer Add, Stock Report, Store Add. Expense Add navigation and form actions preserve app/language. Other mapped pages share styling but are not individually browser-verified. Print/detail views, dashboard, authentication and maintenance screens are not covered by this mapping.

Expenses search verified with a Burmese term: 18 matching records out of 1,127. 320px and 390px document widths do not overflow. Desktop Expenses does not load shared mobile markup/CSS. All 15 changed PHP files pass syntax checks and the 68 existing isolated inventory/controller checks pass. No business rows were changed.

Visual alignment: shared mobile pages now match observed Products computed styles: Source Sans Pro body 16px, title 24px/700, table heading 15px/800, table cell 16px, primary #0f766e, text #172b3a, table text #334155 and table header #f8fafc. Expenses loaded rows and styles were verified at 390px. Date cells remain unwrapped within the horizontal table scroller.

Actions now use Products listing markup/classes and shared mobile-actions.css rather than expanded dropdown CSS. At 390px browser comparison verified 52x46px icon links, 7px gaps, 18px icons and identical edit/view/delete background colors. Legends below tables reuse Products icon meanings and adapt to actual authorized actions. Original anchors (including modal attributes and delete confirmation) are retained; DataTables redraws are handled.

Dedicated mobile action follow-up: Categories, Customers, Suppliers, Stock Transfers, Opening Stock, Units and Users now load the Products-standard renderer and legend. Browser verified loaded action rows/legends on the first six routes; Users is code-checked only. Renderer preserves original links and modal/delete attributes and recognizes edit actions by route, class, title or pencil icon. Broader remaining dedicated listings (including Purchases/Sales) still need individual review.
