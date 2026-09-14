# Isolated inventory integration checks

Run `php tests/isolated_inventory.php` from the project root.

The runner reads local database connection settings, creates a fresh random `klspos_test_<hex>` database, copies table structures only, and inserts synthetic fixtures. The real Products and Purchases models execute against the isolated connection. The test database is removed in `finally`, including when assertions fail. Database creation privileges are required. Never point business traffic at a test database.

68 checks cover product creation/deletion, partial and complete receive, over-receive rejection, duplicate receipt rejection, stock totals, receipt history, receipt quantity increases/decreases/deletion, signed movement reconciliation, historical unit conversion, secondary quantities, delivery costs, legacy batch matching and rejection of sold/transferred stock or conflicting receipt links.

This is model/database integration testing, not a browser end-to-end test. `CREATE TABLE LIKE` preserves columns and indexes but does not reproduce foreign keys or triggers. No live business rows are copied. Browser save/delete operations against the live database are not tested here.

Receipt quantity correction and deletion reverse unused stock within a database transaction. Sold/transferred or otherwise changed batches are rejected. Legacy receipts require a unique matching batch; ambiguous matches require manual investigation. Purchase deletion also checks receipt history, payment rows/header, received quantities and remaining stock under a transaction. It preserves zero-balance stock batches and movement audit records. Direct item deletion is explicitly disabled because it bypasses invoice total and delivery allocation recalculation; a complete item-edit workflow still needs separate verification.

For an existing local installation, run `php scripts/migrate_receipt_links.php --database=<configured-database-name>`. This idempotent CLI migration adds nullable `purchase_receipts.stock_batch_id` and `stock_movements.purchase_receipt_id` columns. It does not alter stock quantities or backfill uncertain historical associations. New receipts save exact links automatically.

Purchase Edit follow-up: edits of unpaid, unreceived purchases validate all replacement lines before writing and commit header/items together. Totals use primary quantity times selected-unit cost plus header delivery once; delivery is allocated by primary quantity with rounding residue on the last line. Receipt/payment-backed and unreconciled legacy stock purchases are blocked. Empty/invalid edits preserve original records. Edit form totals, payment autofill and unit-change handling were corrected. 68 isolated model/controller checks pass; browser end-to-end edit submission remains untested.

The controller integration include exercises real Purchase Edit POST parsing, model writes and success redirects in desktop/mobile modes. Framework input, validation, session and redirect services are stubbed; this does not exercise authentication or browser rendering. The mobile edit form now preserves app/language query parameters on submission.
