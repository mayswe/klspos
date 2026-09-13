# KLSPOS

Existing PHP / CodeIgniter POS application with desktop pages and mobile views enabled by `?app=1`.

## Local setup

The working Laragon installation uses PHP 8.1 and MySQL. Open http://localhost/klspos/.

For a new checkout, copy `app/config/config.example.php`, `database.example.php`, and `google_local.example.php` to their corresponding filenames without `.example`. Set the local database credentials, a random encryption key, and Google client secret. Existing local configuration files are ignored by Git and should not be overwritten.

Install PHP dependencies using `composer install`. Restore the database and required uploads separately from a trusted backup. Git does not back up database contents, uploads, logs, or private configuration. Preserve required writable runtime directories (`app/logs`, `app/cache`, `uploads/captcha`, `files/backups`, and `update/cache`). Bundled legacy libraries in `app/third_party` are tracked because the application depends on them.

Google OAuth must authorize `http://localhost/klspos/auth/google_callback` for the configured client. Login supports username/email and password, plus Google for existing active accounts.

## Development baseline

The initial commit records the working application after local login fixes. Next, inventory mobile pages, choose a reference design, and standardize shared mobile components while checking desktop behavior.

Use `git status` and `git diff` before commits. Never commit secrets or business data. No remote repository is configured by local initialization.
`nCopy app/config/rest.example.php to app/config/rest.php for a new checkout and configure REST authentication as needed.
